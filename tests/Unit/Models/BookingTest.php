<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_formatted_price_attribute(): void
    {
        $booking = Booking::factory()->make(['price' => 1500000]);
        $this->assertSame('Rp 1.500.000', $booking->formatted_price);
    }

    public function test_status_label_pending(): void
    {
        $booking = Booking::factory()->make(['status' => 'pending']);
        $this->assertSame('Menunggu', $booking->status_label);
    }

    public function test_status_label_confirmed(): void
    {
        $booking = Booking::factory()->make(['status' => 'confirmed']);
        $this->assertSame('Dikonfirmasi', $booking->status_label);
    }

    public function test_status_label_completed(): void
    {
        $booking = Booking::factory()->make(['status' => 'completed']);
        $this->assertSame('Selesai', $booking->status_label);
    }

    public function test_status_label_cancelled(): void
    {
        $booking = Booking::factory()->make(['status' => 'cancelled']);
        $this->assertSame('Dibatalkan', $booking->status_label);
    }

    public function test_status_color_map(): void
    {
        $this->assertSame('yellow', Booking::factory()->make(['status' => 'pending'])->status_color);
        $this->assertSame('blue',   Booking::factory()->make(['status' => 'confirmed'])->status_color);
        $this->assertSame('green',  Booking::factory()->make(['status' => 'completed'])->status_color);
        $this->assertSame('red',    Booking::factory()->make(['status' => 'cancelled'])->status_color);
    }

    public function test_booking_belongs_to_user(): void
    {
        $user    = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $user->id]);
        $this->assertTrue($booking->user->is($user));
    }

    public function test_booking_belongs_to_client(): void
    {
        $user    = User::factory()->create();
        $client  = Client::factory()->create(['user_id' => $user->id]);
        $booking = Booking::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $this->assertTrue($booking->client->is($client));
    }

    public function test_booking_belongs_to_service(): void
    {
        $user    = User::factory()->create();
        $service = Service::factory()->create(['user_id' => $user->id]);
        $booking = Booking::factory()->create(['user_id' => $user->id, 'service_id' => $service->id]);
        $this->assertTrue($booking->service->is($service));
    }
}
