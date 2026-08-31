<?php

namespace Tests\Feature\Inventory;

use App\Models\BatchObat;
use App\Models\StokOpname;
use App\Models\User;
use App\Services\StockService;
use App\Services\StokOpnameService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class StokOpnameServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function gudangUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('gudang');

        return $user;
    }

    private function ownerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        return $user;
    }

    public function test_mulai_opname_snapshot_semua_batch_aktif_dan_habis_kecuali_expired(): void
    {
        $aktif = BatchObat::factory()->create(['stok_saat_ini' => 50, 'status' => BatchObat::STATUS_AKTIF]);
        $habis = BatchObat::factory()->create(['stok_saat_ini' => 0, 'status' => BatchObat::STATUS_HABIS]);
        $expired = BatchObat::factory()->expired()->create(['status' => BatchObat::STATUS_EXPIRED]);

        $opname = app(StokOpnameService::class)->mulaiOpname($this->gudangUser()->id);

        $batchIds = $opname->detail->pluck('batch_obat_id')->all();

        $this->assertContains($aktif->id, $batchIds);
        $this->assertContains($habis->id, $batchIds);
        $this->assertNotContains($expired->id, $batchIds);
        $this->assertStringStartsWith('OPN-'.now()->format('Ym').'-', $opname->kode_opname);
    }

    public function test_tidak_bisa_mulai_opname_baru_kalau_masih_ada_yang_berjalan(): void
    {
        $service = app(StokOpnameService::class);
        BatchObat::factory()->create();

        $service->mulaiOpname($this->gudangUser()->id);

        $this->expectException(InvalidArgumentException::class);
        $service->mulaiOpname($this->gudangUser()->id);
    }

    public function test_tidak_bisa_selesaikan_kalau_masih_ada_item_belum_dihitung(): void
    {
        BatchObat::factory()->count(2)->create();
        $service = app(StokOpnameService::class);
        $user = $this->ownerUser();

        $opname = $service->mulaiOpname($user->id);

        // Cuma catat hasil hitung untuk satu dari dua item.
        $service->catatHasilHitung($opname->detail->first()->id, $opname->detail->first()->stok_sistem, $user->id);

        $this->expectException(RuntimeException::class);
        $service->selesaikanOpname($opname->id, $user->id, app(StockService::class));
    }

    public function test_selesaikan_opname_menyesuaikan_stok_riil_hanya_untuk_item_yang_selisih(): void
    {
        $sesuai = BatchObat::factory()->create(['stok_saat_ini' => 30]);
        $kurang = BatchObat::factory()->create(['stok_saat_ini' => 30]);

        $service = app(StokOpnameService::class);
        $user = $this->ownerUser();
        $opname = $service->mulaiOpname($user->id);

        $detailSesuai = $opname->detail->firstWhere('batch_obat_id', $sesuai->id);
        $detailKurang = $opname->detail->firstWhere('batch_obat_id', $kurang->id);

        // Sesuai: stok fisik sama dengan stok sistem (30).
        $service->catatHasilHitung($detailSesuai->id, 30, $user->id);
        // Kurang: stok fisik cuma 25, selisih -5.
        $service->catatHasilHitung($detailKurang->id, 25, $user->id, 'Kemasan rusak, dimusnahkan sebelumnya');

        $result = $service->selesaikanOpname($opname->id, $user->id, app(StockService::class));

        $this->assertSame(StokOpname::STATUS_SELESAI, $result->status);
        $this->assertSame($user->id, $result->diselesaikan_oleh);

        $this->assertSame(30, $sesuai->fresh()->stok_saat_ini);
        $this->assertSame(25, $kurang->fresh()->stok_saat_ini);

        // Kartu stok HANYA tercatat untuk batch yang benar-benar selisih.
        $this->assertDatabaseMissing('kartu_stok', ['batch_id' => $sesuai->id, 'jenis_transaksi' => 'penyesuaian']);
        $this->assertDatabaseHas('kartu_stok', ['batch_id' => $kurang->id, 'jenis_transaksi' => 'penyesuaian', 'jumlah' => -5]);
    }

    public function test_batalkan_opname_tidak_mengubah_stok_riil(): void
    {
        $batch = BatchObat::factory()->create(['stok_saat_ini' => 40]);
        $service = app(StokOpnameService::class);
        $user = $this->gudangUser();

        $opname = $service->mulaiOpname($user->id);
        $detail = $opname->detail->first();
        $service->catatHasilHitung($detail->id, 10, $user->id);

        $result = $service->batalkanOpname($opname->id);

        $this->assertSame(StokOpname::STATUS_DIBATALKAN, $result->status);
        $this->assertSame(40, $batch->fresh()->stok_saat_ini);
    }

    public function test_catat_hasil_hitung_menolak_stok_fisik_negatif(): void
    {
        BatchObat::factory()->create();
        $service = app(StokOpnameService::class);
        $user = $this->gudangUser();
        $opname = $service->mulaiOpname($user->id);

        $this->expectException(InvalidArgumentException::class);
        $service->catatHasilHitung($opname->detail->first()->id, -1, $user->id);
    }
}
