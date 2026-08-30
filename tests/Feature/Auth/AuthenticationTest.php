<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login as LoginPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        Livewire::test(LoginPage::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        Livewire::test(LoginPage::class)
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    /**
     * Regresi untuk bug: sebelumnya rute login aktif memakai
     * AuthenticatedSessionController lama yang tidak mengecek is_active,
     * jadi user yang dinonaktifkan lewat Manajemen Pengguna tetap bisa
     * login. Sekarang login diarahkan ke App\Livewire\Auth\Login yang
     * menyertakan is_active di Auth::attempt().
     */
    public function test_deactivated_user_cannot_authenticate(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        Livewire::test(LoginPage::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
