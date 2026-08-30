<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * '/' mengarahkan ke dashboard (lihat routes/web.php) — ini aplikasi
     * internal, bukan produk dengan landing page publik.
     */
    public function test_the_application_redirects_root_to_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
