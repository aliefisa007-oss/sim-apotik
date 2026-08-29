<?php

namespace App\Services;

use App\Models\DetailResep;
use App\Models\Resep;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Mengelola siklus hidup resep: input -> verifikasi/tolak apoteker ->
 * (dipakai PenjualanService untuk dispensing) -> selesai.
 *
 * CATATAN SCOPE: verifikasi di sini adalah verifikasi ADMINISTRATIF
 * (kelengkapan resep, kecocokan pasien/dokter) — BUKAN pengecekan
 * interaksi obat, duplikasi terapi, atau kesesuaian dosis klinis.
 * Itu di luar scope Phase 6 dan TO BE VERIFIED apakah dibutuhkan modul
 * terpisah (mis. drug-interaction checker) di phase berikutnya.
 */
class ResepService
{
    /**
     * @param array<int, array{obat_id: int, jumlah_diresepkan: int, aturan_pakai?: ?string, catatan?: ?string}> $detailList
     */
    public function create(array $data, array $detailList, int $createdBy): Resep
    {
        if (empty($detailList)) {
            throw new InvalidArgumentException('Resep harus memiliki minimal 1 obat.');
        }

        return DB::transaction(function () use ($data, $detailList, $createdBy) {
            $data['no_resep'] = $this->generateNoResep();
            $data['created_by'] = $createdBy;
            $data['status'] = Resep::STATUS_MENUNGGU_VERIFIKASI;

            $resep = Resep::create($data);

            foreach ($detailList as $row) {
                DetailResep::create([
                    'resep_id' => $resep->id,
                    'obat_id' => $row['obat_id'],
                    'jumlah_diresepkan' => $row['jumlah_diresepkan'],
                    'jumlah_terlayani' => 0,
                    'aturan_pakai' => $row['aturan_pakai'] ?? null,
                    'catatan' => $row['catatan'] ?? null,
                ]);
            }

            return $resep->fresh(['detail.obat', 'pasien']);
        });
    }

    public function verify(Resep $resep, int $apotekerId, ?string $catatan = null): Resep
    {
        $this->assertPendingAndValidApoteker($resep, $apotekerId);

        $resep->update([
            'status' => Resep::STATUS_TERVERIFIKASI,
            'apoteker_verifikasi_id' => $apotekerId,
            'catatan_verifikasi' => $catatan,
            'verified_at' => now(),
        ]);

        return $resep->fresh(['detail.obat', 'pasien', 'apotekerVerifikasi']);
    }

    public function reject(Resep $resep, int $apotekerId, string $alasan): Resep
    {
        $this->assertPendingAndValidApoteker($resep, $apotekerId);

        $resep->update([
            'status' => Resep::STATUS_DITOLAK,
            'apoteker_verifikasi_id' => $apotekerId,
            'catatan_verifikasi' => $alasan,
            'verified_at' => now(),
        ]);

        return $resep->fresh(['detail.obat', 'pasien', 'apotekerVerifikasi']);
    }

    private function assertPendingAndValidApoteker(Resep $resep, int $apotekerId): void
    {
        if ($resep->status !== Resep::STATUS_MENUNGGU_VERIFIKASI) {
            throw new InvalidArgumentException(
                "Resep {$resep->no_resep} sudah diproses sebelumnya (status: {$resep->status})."
            );
        }

        $apoteker = User::find($apotekerId);
        if (!$apoteker || !$apoteker->hasRole('apoteker')) {
            throw new InvalidArgumentException('User yang memverifikasi harus memiliki role apoteker.');
        }
    }

    /**
     * Sequential, dikunci-transaksi seperti generateNoTransaksi di
     * PenjualanService & generateKodeObat di ObatService — pola yang
     * sama dipakai konsisten di seluruh project (§77).
     */
    private function generateNoResep(): string
    {
        $today = now()->format('Ymd');
        $prefix = "RSP-{$today}-";

        $last = Resep::where('no_resep', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('no_resep');

        $nextNumber = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . sprintf('%04d', $nextNumber);
    }
}
