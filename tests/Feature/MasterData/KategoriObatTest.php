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

        $data = ['nama' => $kategori->nama, 'parent_id' => $kategori->id];

        $request = new StoreKategoriObatRequest();
        $request->setRouteResolver(fn () => $this->fakeRoute($kategori));
        $request->merge($data);

        $validator = Validator::make($data, $request->rules());
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
        $data = ['nama' => $grandparent->nama, 'parent_id' => $child->id];

        $request = new StoreKategoriObatRequest();
        $request->setRouteResolver(fn () => $this->fakeRoute($grandparent));
        $request->merge($data);

        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('parent_id', $validator->errors()->toArray());
    }

    private function fakeRoute(KategoriObat $kategori)
    {
        return new class($kategori) {
            public function __construct(private KategoriObat $kategori) {}

            public function __invoke(): KategoriObat
            {
                return $this->kategori;
            }

            public function __get(string $name): mixed
            {
                return $this->kategori->{$name} ?? null;
            }

            public function parameter(string $name, mixed $default = null): mixed
            {
                return $this->kategori;
            }
        };
    }
}