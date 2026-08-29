<?php

namespace Tests\Feature\MasterData;

use App\Http\Requests\StoreKategoriObatRequest;
use App\Models\KategoriObat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class KategoriObatTest extends TestCase
{
    use RefreshDatabase;

    public function test_kategori_cannot_be_its_own_parent(): void
    {
        $kategori = KategoriObat::factory()->create();

        $request = new StoreKategoriObatRequest();
        $request->setRouteResolver(fn () => $this->fakeRoute($kategori));

        $validator = Validator::make(
            ['nama' => $kategori->nama, 'parent_id' => $kategori->id],
            $request->rules()
        );
        $request->withValidator($validator);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('parent_id', $validator->errors()->toArray());
    }

    public function test_kategori_chain_cannot_form_a_cycle(): void
    {
        $grandparent = KategoriObat::factory()->create();
        $parent = KategoriObat::factory()->create(['parent_id' => $grandparent->id]);
        $child = KategoriObat::factory()->create(['parent_id' => $parent->id]);

        // Attempting to set grandparent's parent to child would create a cycle.
        $request = new StoreKategoriObatRequest();
        $request->setRouteResolver(fn () => $this->fakeRoute($grandparent));

        $validator = Validator::make(
            ['nama' => $grandparent->nama, 'parent_id' => $child->id],
            $request->rules()
        );
        $request->withValidator($validator);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('parent_id', $validator->errors()->toArray());
    }

    private function fakeRoute(KategoriObat $kategori)
    {
        return new class($kategori) {
            public function __construct(private KategoriObat $kategori) {}
            public function __invoke() { return $this->kategori; }
            public function __get($name) { return $this->kategori->{$name} ?? null; }
        };
    }
}
