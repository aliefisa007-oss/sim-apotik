<?php

namespace Tests\Feature\Inventory;

use App\Livewire\Inventory\StokOpname\Index as StokOpnameIndex;
use App\Livewire\Inventory\StokOpname\Show as StokOpnameShow;
use App\Models\BatchObat;
use App\Models\User;
use App\Services\StokOpnameService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StokOpnameLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_kasir_tidak_bisa_mulai_opname(): void
    {
        $this->actingAs($this->user('kasir'));

        Livewire::test(StokOpnameIndex::class)
            ->call('mulai')
            ->assertForbidden();
    }

    public function test_gudang_bisa_mulai_opname_dan_lanjut_hitung(): void
    {
        BatchObat::factory()->count(3)->create(['stok_saat_ini' => 20]);
        $gudang = $this->user('gudang');
        $this->actingAs($gudang);

        Livewire::test(StokOpnameIndex::class)->call('mulai');

        $opname = \App\Models\StokOpname::first();
        $this->assertNotNull($opname);
        $this->assertCount(3, $opname->detail);

        $detail = $opname->detail->first();

        Livewire::test(StokOpnameShow::class, ['opname' => $opname])
            ->set("stokFisikInput.{$detail->id}", 20)
            ->call('simpanHitung', $detail->id)
            ->assertHasNoErrors();

        $this->assertSame(20, $detail->fresh()->stok_fisik);
    }

    public function test_gudang_tidak_bisa_finalize_hanya_owner_admin(): void
    {
        BatchObat::factory()->create();
        $gudang = $this->user('gudang');
        $opname = app(StokOpnameService::class)->mulaiOpname($gudang->id);

        $this->actingAs($gudang);

        Livewire::test(StokOpnameShow::class, ['opname' => $opname])
            ->call('selesaikan')
            ->assertForbidden();
    }

    public function test_owner_bisa_finalize_setelah_semua_item_dihitung(): void
    {
        $batch = BatchObat::factory()->create(['stok_saat_ini' => 15]);
        $owner = $this->user('owner');
        $service = app(StokOpnameService::class);
        $opname = $service->mulaiOpname($owner->id);
        $service->catatHasilHitung($opname->detail->first()->id, 15, $owner->id);

        $this->actingAs($owner);

        Livewire::test(StokOpnameShow::class, ['opname' => $opname])
            ->call('selesaikan')
            ->assertHasNoErrors();

        $this->assertSame('selesai', $opname->fresh()->status);
    }
}
