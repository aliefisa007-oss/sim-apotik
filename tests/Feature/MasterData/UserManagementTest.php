<?php

namespace Tests\Feature\MasterData;

use App\Livewire\MasterData\User\Index as UserIndex;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function owner(): User
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('owner');

        return $owner;
    }

    public function test_non_owner_cannot_view_user_management(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        Livewire::test(UserIndex::class)->assertForbidden();
    }

    public function test_owner_can_view_user_management(): void
    {
        $this->actingAs($this->owner());

        Livewire::test(UserIndex::class)->assertOk();
    }

    public function test_owner_can_create_user_with_role(): void
    {
        $this->actingAs($this->owner());

        Livewire::test(UserIndex::class)
            ->call('openCreate')
            ->set('name', 'Kasir Baru')
            ->set('email', 'kasir.baru@sim-apotik.test')
            ->set('password', 'password123')
            ->set('role', 'kasir')
            ->call('save')
            ->assertHasNoErrors();

        $created = User::where('email', 'kasir.baru@sim-apotik.test')->firstOrFail();
        $this->assertTrue($created->hasRole('kasir'));
        $this->assertTrue($created->is_active);
    }

    public function test_owner_cannot_deactivate_own_account(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner);

        Livewire::test(UserIndex::class)
            ->call('openEdit', $owner->id)
            ->set('is_active', false)
            ->call('save')
            ->assertHasErrors('is_active');

        $this->assertTrue($owner->fresh()->is_active);
    }

    public function test_owner_cannot_strip_own_owner_role(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner);

        Livewire::test(UserIndex::class)
            ->call('openEdit', $owner->id)
            ->set('role', 'admin')
            ->call('save')
            ->assertHasErrors('role');

        $this->assertTrue($owner->fresh()->hasRole('owner'));
    }

    public function test_owner_can_edit_another_user_role_and_status(): void
    {
        $this->actingAs($this->owner());

        $target = User::factory()->create(['is_active' => true]);
        $target->assignRole('kasir');

        Livewire::test(UserIndex::class)
            ->call('openEdit', $target->id)
            ->set('role', 'gudang')
            ->set('is_active', false)
            ->call('save')
            ->assertHasNoErrors();

        $fresh = $target->fresh();
        $this->assertTrue($fresh->hasRole('gudang'));
        $this->assertFalse($fresh->hasRole('kasir'));
        $this->assertFalse($fresh->is_active);
    }

    public function test_password_left_blank_on_edit_does_not_change_password(): void
    {
        $this->actingAs($this->owner());

        $target = User::factory()->create();
        $target->assignRole('kasir');
        $originalHash = $target->password;

        Livewire::test(UserIndex::class)
            ->call('openEdit', $target->id)
            ->set('name', 'Nama Diubah')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($originalHash, $target->fresh()->password);
    }
}
