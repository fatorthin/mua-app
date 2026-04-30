<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assertTrue($admin->isAdmin());
    }

    public function test_is_admin_returns_false_for_mua_role(): void
    {
        $mua = User::factory()->create(['role' => 'mua']);
        $this->assertFalse($mua->isAdmin());
    }

    public function test_user_has_many_services(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->services);
    }

    public function test_user_has_many_clients(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->clients);
    }

    public function test_user_has_many_bookings(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->bookings);
    }
}
