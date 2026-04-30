<?php

namespace Tests\Unit\Models;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_formatted_price_attribute(): void
    {
        $service = Service::factory()->make(['price' => 1500000]);
        $this->assertSame('Rp 1.500.000', $service->formatted_price);
    }

    public function test_formatted_price_small(): void
    {
        $service = Service::factory()->make(['price' => 350000]);
        $this->assertSame('Rp 350.000', $service->formatted_price);
    }

    public function test_service_belongs_to_user(): void
    {
        $user    = User::factory()->create();
        $service = Service::factory()->create(['user_id' => $user->id]);
        $this->assertTrue($service->user->is($user));
    }

    public function test_inactive_service(): void
    {
        $service = Service::factory()->inactive()->make();
        $this->assertFalse($service->is_active);
    }
}
