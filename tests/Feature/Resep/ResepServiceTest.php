<?php

namespace Tests\Feature\Resep;

use App\Models\Obat;
use App\Models\Pasien;
use App\Models\Resep;
use App\Models\User;
use App\Services\ResepService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ResepServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_resep_generates_sequential_no_resep_and_menunggu_verifikasi(): void
    {
        $pasien = Pasien::factory()->create();
        $obat = Obat::factory()->create();
        $kasir = User::factory()->create();

        $resep = app(ResepService::class)->create(
            data: [
                'pasien_id' => $pasien->id,
                'nama_dokter' => 'dr. Budi',
                'no_sip_dokter' => null,
                'tanggal_resep' => now()->toDateString(),
            ],
            detailList: [
                ['obat_id' => $obat->id, 'jumlah_diresepkan' => 10, 'aturan_pakai' => '3x1'],
            ],
            createdBy: $kasir->id,
        );

        $this->assertStringStartsWith('RSP-' . now()->format('Ymd') . '-', $resep->no_resep);
        $this->assertSame(Resep::STATUS_MENUNGGU_VERIFIKASI, $resep->status);
        $this->assertCount(1, $resep->detail);
        $this->assertSame(0, $resep->detail->first()->jumlah_terlayani);
    }

    public function test_create_resep_tanpa_item_ditolak(): void
    {
        $pasien = Pasien::factory()->create();
        $kasir = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        app(ResepService::class)->create(
            data: ['pasien_id' => $pasien->id, 'nama_dokter' => 'dr. Budi', 'tanggal_resep' => now()->toDateString()],
            detailList: [],
            createdBy: $kasir->id,
        );
    }

    public function test_verify_by_non_apoteker_ditolak(): void
    {
        $resep = Resep::factory()->create();
        $bukanApoteker = User::factory()->create(); // no role assigned

        $this->expectException(InvalidArgumentException::class);

        app(ResepService::class)->verify($resep, $bukanApoteker->id);
    }

    public function test_verify_success_sets_status_and_apoteker(): void
    {
        $resep = Resep::factory()->create();
        $apoteker = User::factory()->create();
        $apoteker->assignRole('apoteker');

        $verified = app(ResepService::class)->verify($resep, $apoteker->id, 'Lengkap');

        $this->assertSame(Resep::STATUS_TERVERIFIKASI, $verified->status);
        $this->assertSame($apoteker->id, $verified->apoteker_verifikasi_id);
        $this->assertNotNull($verified->verified_at);
    }

    public function test_reject_sets_status_ditolak_dengan_alasan(): void
    {
        $resep = Resep::factory()->create();
        $apoteker = User::factory()->create();
        $apoteker->assignRole('apoteker');

        $rejected = app(ResepService::class)->reject($resep, $apoteker->id, 'Tulisan tidak terbaca');

        $this->assertSame(Resep::STATUS_DITOLAK, $rejected->status);
        $this->assertSame('Tulisan tidak terbaca', $rejected->catatan_verifikasi);
    }

    public function test_resep_yang_sudah_diverifikasi_tidak_bisa_diverifikasi_ulang(): void
    {
        $resep = Resep::factory()->terverifikasi()->create();
        $apoteker = User::factory()->create();
        $apoteker->assignRole('apoteker');

        $this->expectException(InvalidArgumentException::class);

        app(ResepService::class)->verify($resep, $apoteker->id);
    }
}
