<?php

namespace App\Services;

use App\Models\BatchObat;
use App\Models\HistoriHargaObat;
use App\Models\HjaConfig;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Semua kalkulasi HJA final HARUS lewat service ini (§9) — jangan hitung
 * harga jual di Blade/Livewire/frontend. UI hanya menampilkan breakdown
 * hasil calculate() untuk preview (§64), sedangkan penyimpanan hanya lewat
 * setHargaJual().
 *
 * Alur (§12): Harga Faktur → Diskon → Harga Netto → Pajak → Markup/Margin
 * → Pembulatan → Harga Jual Final.
 */
class HJAService
{
    public const METODE_MARKUP = 'markup';
    public const METODE_MARGIN = 'margin';

    public const ROUNDING_ROUND = 'round';
    public const ROUNDING_UP = 'round_up';
    public const ROUNDING_DOWN = 'round_down';

    /**
     * Kalkulasi murni, tanpa efek samping — aman dipanggil berkali-kali
     * untuk live preview di UI.
     *
     * @param array{
     *   harga_faktur: float,
     *   diskon_persen?: float,
     *   tax_percent?: float,
     *   harga_termasuk_pajak?: bool,
     *   metode?: string,
     *   persen_markup_margin?: float,
     *   rounding_method?: string,
     *   rounding_increment?: int,
     * } $input
     * @return array{
     *   harga_faktur: float, diskon_persen: float, harga_netto: float,
     *   tax_percent: float, harga_termasuk_pajak: bool, pajak_nominal: float,
     *   cost_basis: float, metode: string, persen_markup_margin: float,
     *   harga_sebelum_pembulatan: float, rounding_method: string,
     *   rounding_increment: int, harga_final: float, rounding_difference: float,
     * }
     */
    public function calculate(array $input): array
    {
        $config = HjaConfig::current();

        $hargaFaktur = (float) ($input['harga_faktur'] ?? throw new InvalidArgumentException('harga_faktur wajib diisi.'));
        $diskonPersen = (float) ($input['diskon_persen'] ?? 0);
        $taxPercent = (float) ($input['tax_percent'] ?? $config->default_tax_percent);
        $hargaTermasukPajak = (bool) ($input['harga_termasuk_pajak'] ?? $config->harga_beli_termasuk_pajak_default);
        $metode = $input['metode'] ?? $config->default_metode;
        $persen = (float) ($input['persen_markup_margin'] ?? (
            $metode === self::METODE_MARGIN ? $config->default_margin_percent : $config->default_markup_percent
        ));
        $roundingMethod = $input['rounding_method'] ?? $config->rounding_method;
        $roundingIncrement = (int) ($input['rounding_increment'] ?? $config->rounding_increment);

        $this->validateInputs($hargaFaktur, $diskonPersen, $taxPercent, $metode, $persen, $roundingIncrement);

        // 1. Harga Faktur -> Diskon -> Harga Netto (HNA)
        $hargaNetto = $hargaFaktur * (1 - $diskonPersen / 100);

        // 2. Pajak — jangan double tax: hanya tambahkan jika harga_netto
        // BELUM termasuk pajak.
        $pajakNominal = $hargaTermasukPajak ? 0.0 : $hargaNetto * ($taxPercent / 100);
        $costBasis = $hargaNetto + $pajakNominal;

        // 3. Markup atau Margin (dua formula berbeda, jangan disamakan — §12)
        $hargaSebelumPembulatan = $metode === self::METODE_MARGIN
            ? $costBasis / (1 - $persen / 100)
            : $costBasis * (1 + $persen / 100);

        // 4. Pembulatan
        $hargaFinal = $this->round($hargaSebelumPembulatan, $roundingMethod, $roundingIncrement);
        $roundingDifference = $hargaFinal - $hargaSebelumPembulatan;

        return [
            'harga_faktur' => round($hargaFaktur, 2),
            'diskon_persen' => $diskonPersen,
            'harga_netto' => round($hargaNetto, 2),
            'tax_percent' => $taxPercent,
            'harga_termasuk_pajak' => $hargaTermasukPajak,
            'pajak_nominal' => round($pajakNominal, 2),
            'cost_basis' => round($costBasis, 2),
            'metode' => $metode,
            'persen_markup_margin' => $persen,
            'harga_sebelum_pembulatan' => round($hargaSebelumPembulatan, 2),
            'rounding_method' => $roundingMethod,
            'rounding_increment' => $roundingIncrement,
            'harga_final' => round($hargaFinal, 2),
            'rounding_difference' => round($roundingDifference, 2),
        ];
    }

    /**
     * Hitung lalu simpan sebagai harga_jual batch + catat histori (audit).
     * Cost basis (harga_faktur) selalu diambil dari BatchObat::harga_beli
     * milik batch itu sendiri — inilah yang membuat HJA batch-aware (§11):
     * dua batch obat yang sama dengan harga beli berbeda akan menghasilkan
     * harga jual final yang berbeda pula, dan histori tetap terpisah per batch.
     */
    public function setHargaJual(BatchObat $batch, array $input, int $userId, ?string $alasan = null): BatchObat
    {
        $input['harga_faktur'] = (float) $batch->harga_beli;

        $breakdown = $this->calculate($input);

        return DB::transaction(function () use ($batch, $breakdown, $userId, $alasan) {
            $hargaLama = $batch->harga_jual;

            $batch->update(['harga_jual' => $breakdown['harga_final']]);

            HistoriHargaObat::create([
                'obat_id' => $batch->obat_id,
                'batch_id' => $batch->id,
                'harga_lama' => $hargaLama,
                'harga_baru' => $breakdown['harga_final'],
                'harga_faktur' => $breakdown['harga_faktur'],
                'diskon_persen' => $breakdown['diskon_persen'],
                'harga_netto' => $breakdown['harga_netto'],
                'tax_percent' => $breakdown['tax_percent'],
                'harga_termasuk_pajak' => $breakdown['harga_termasuk_pajak'],
                'cost_basis' => $breakdown['cost_basis'],
                'metode_hja' => $breakdown['metode'],
                'persen_markup_margin' => $breakdown['persen_markup_margin'],
                'harga_sebelum_pembulatan' => $breakdown['harga_sebelum_pembulatan'],
                'rounding_method' => $breakdown['rounding_method'],
                'rounding_increment' => $breakdown['rounding_increment'],
                'rounding_difference' => $breakdown['rounding_difference'],
                'alasan' => $alasan,
                'user_id' => $userId,
            ]);

            return $batch->fresh();
        });
    }

    private function round(float $value, string $method, int $increment): float
    {
        return match ($method) {
            self::ROUNDING_UP => ceil($value / $increment) * $increment,
            self::ROUNDING_DOWN => floor($value / $increment) * $increment,
            default => round($value / $increment) * $increment, // self::ROUNDING_ROUND
        };
    }

    private function validateInputs(
        float $hargaFaktur,
        float $diskonPersen,
        float $taxPercent,
        string $metode,
        float $persen,
        int $roundingIncrement,
    ): void {
        if ($hargaFaktur < 0) {
            throw new InvalidArgumentException('Harga faktur tidak boleh negatif.');
        }

        if ($diskonPersen < 0 || $diskonPersen >= 100) {
            throw new InvalidArgumentException('Diskon harus berada di antara 0% dan 100% (tidak termasuk 100%).');
        }

        if ($taxPercent < 0 || $taxPercent > 100) {
            throw new InvalidArgumentException('Persentase pajak harus berada di antara 0% dan 100%.');
        }

        if (!in_array($metode, [self::METODE_MARKUP, self::METODE_MARGIN], true)) {
            throw new InvalidArgumentException('Metode HJA harus markup atau margin.');
        }

        if ($metode === self::METODE_MARGIN && ($persen < 0 || $persen >= 100)) {
            // Margin >= 100% berarti pembagian dengan nol atau negatif — §66/§12.
            throw new InvalidArgumentException('Margin harus berada di antara 0% dan 100% (tidak termasuk 100%).');
        }

        if ($metode === self::METODE_MARKUP && $persen < 0) {
            throw new InvalidArgumentException('Markup tidak boleh negatif.');
        }

        if ($roundingIncrement <= 0) {
            throw new InvalidArgumentException('Kelipatan pembulatan harus lebih dari 0.');
        }
    }
}
