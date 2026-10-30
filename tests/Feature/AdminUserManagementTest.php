<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_user_with_a_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Petugas Baru',
                'email' => 'petugas.baru@example.test',
                'role' => 'petugas',
                'is_active' => '1',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'petugas.baru@example.test')->firstOrFail();
        $this->assertSame('petugas', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('password-baru', $user->password));
    }

    public function test_admin_cannot_disable_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'admin',
                'is_active' => '0',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasErrors('account');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_inactive_user_cannot_log_in_or_keep_an_existing_session(): void
    {
        $user = User::factory()->create([
            'email' => 'nonaktif@example.test',
            'password' => 'password-lama',
            'is_active' => false,
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password-lama',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
