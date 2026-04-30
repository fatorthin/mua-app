<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Admin\UserIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_index_renders_for_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);
        Livewire::test(UserIndex::class)->assertOk();
    }

    public function test_admin_user_index_aborts_for_non_admin(): void
    {
        $user = User::factory()->create(['role' => 'mua']);

        // Livewire abort_unless produces 403 response at the HTTP layer
        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_admin_sees_all_users(): void
    {
        $admin = User::factory()->admin()->create();
        $mua1  = User::factory()->create(['name' => 'Rina Studio']);
        $mua2  = User::factory()->create(['name' => 'Dewi Beauty']);

        $this->actingAs($admin);
        Livewire::test(UserIndex::class)
            ->assertSee('Rina Studio')
            ->assertSee('Dewi Beauty');
    }

    public function test_admin_user_search(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Rina Studio']);
        User::factory()->create(['name' => 'Dewi Beauty']);

        $this->actingAs($admin);
        Livewire::test(UserIndex::class)
            ->set('search', 'Rina')
            ->assertSee('Rina Studio')
            ->assertDontSee('Dewi Beauty');
    }
}
