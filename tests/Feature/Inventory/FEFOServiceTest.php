<?php

namespace Tests\Feature\Inventory;

use App\Exceptions\InsufficientStockException;
use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\User;
use App\Services\FEFOService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FEFOServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_terdekat_expired_digunakan_lebih_dulu(): void
    {
        $obat = Obat::factory()->create();

        $batchJauh = BatchObat::factory()->for($obat)->expiringInDays(180)->create(['stok_saat_ini' => 50, 'stok_awal' => 50]);
        $batchDekat = BatchObat::factory()->for($obat)->expiringInDays(10)->create(['stok_saat_ini' => 50, 'stok_awal' => 50]);

        $allocations = app(FEFOService::class)->previewAllocation($obat->id, 20);

        $this->assertCount(1, $allocations);
        $this->assertSame($batchDekat->id, $allocations[0]['batch_id']);
    }

    public function test_batch_expired_tidak_digunakan(): void
    {
        $obat = Obat::factory()->create();

        BatchObat::factory()->for($obat)->expired()->create(['stok_saat_ini' => 100, 'stok_awal' => 100]);
        $batchValid = BatchObat::factory()->for($obat)->expiringInDays(30)->create(['stok_saat_ini' => 20, 'stok_awal' => 20]);

        $allocations = app(FEFOService::class)->previewAllocation($obat->id, 10);

        $this->assertCount(1, $allocations);
        $this->assertSame($batchValid->id, $allocations[0]['batch_id']);
    }

    public function test_stok_batch_pertama_tidak_cukup_lanjut_batch_kedua(): void
    {
        $obat = Obat::factory()->create();

        $batch1 = BatchObat::factory()->for($obat)->expiringInDays(10)->create(['stok_saat_ini' => 5, 'stok_awal' => 5]);
        $batch2 = BatchObat::factory()->for($obat)->expiringInDays(20)->create(['stok_saat_ini' => 50, 'stok_awal' => 50]);

        $allocations = app(FEFOService::class)->previewAllocation($obat->id, 15);

        $this->assertCount(2, $allocations);
        $this->assertSame($batch1->id, $allocations[0]['batch_id']);
        $this->assertSame(5, $allocations[0]['jumlah']);
        $this->assertSame($batch2->id, $allocations[1]['batch_id']);
        $this->assertSame(10, $allocations[1]['jumlah']);
    }

    public function test_stok_tidak_cukup_melempar_exception(): void
    {
        $obat = Obat::factory()->create();
        BatchObat::factory()->for($obat)->create(['stok_saat_ini' => 5, 'stok_awal' => 5]);

        $this->expectException(InsufficientStockException::class);

        app(FEFOService::class)->previewAllocation($obat->id, 10);
    }

    public function test_deduct_mengurangi_stok_dan_mencatat_kartu_stok(): void
    {
        $obat = Obat::factory()->create();
        $user = User::factory()->create();

        $batch1 = BatchObat::factory()->for($obat)->expiringInDays(5)->create(['stok_saat_ini' => 5, 'stok_awal' => 5]);
        $batch2 = BatchObat::factory()->for($obat)->expiringInDays(15)->create(['stok_saat_ini' => 50, 'stok_awal' => 50]);

        $result = app(FEFOService::class)->deduct(
            obatId: $obat->id,
            jumlahDiminta: 15,
            jenisTransaksi: 'keluar_jual',
            userId: $user->id,
        );

        $this->assertSame(0, $batch1->fresh()->stok_saat_ini);
        $this->assertSame('habis', $batch1->fresh()->status);
        $this->assertSame(40, $batch2->fresh()->stok_saat_ini);

        $this->assertDatabaseHas('kartu_stok', [
            'batch_id' => $batch1->id,
            'jenis_transaksi' => 'keluar_jual',
            'jumlah' => -5,
            'saldo_sebelum' => 5,
            'saldo_sesudah' => 0,
        ]);
        $this->assertDatabaseHas('kartu_stok', [
            'batch_id' => $batch2->id,
            'jenis_transaksi' => 'keluar_jual',
            'jumlah' => -10,
            'saldo_sebelum' => 50,
            'saldo_sesudah' => 40,
        ]);
    }
}
