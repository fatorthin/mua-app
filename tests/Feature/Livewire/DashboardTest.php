<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Dashboard;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_renders(): void
    {
        $this->actingAs($this->user);
        Livewire::test(Dashboard::class)->assertOk();
    }

    public function test_dashboard_shows_correct_total_clients(): void
    {
        Client::factory()->count(4)->create(['user_id' => $this->user->id]);
        // Another user's clients — should not count
        $other = User::factory()->create();
        Client::factory()->count(10)->create(['user_id' => $other->id]);

        $this->actingAs($this->user);
        Livewire::test(Dashboard::class)
            ->assertViewHas('stats', fn($stats) => $stats['total_clients'] === 4);
    }

    public function test_dashboard_shows_correct_total_services(): void
    {
        Service::factory()->count(3)->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user);
        Livewire::test(Dashboard::class)
            ->assertViewHas('stats', fn($stats) => $stats['total_services'] === 3);
    }

    public function test_dashboard_shows_correct_total_bookings(): void
    {
        $client  = Client::factory()->create(['user_id' => $this->user->id]);
        $service = Service::factory()->create(['user_id' => $this->user->id]);
        Booking::factory()->count(5)->create([
            'user_id'   => $this->user->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
        ]);

        $this->actingAs($this->user);
        Livewire::test(Dashboard::class)
            ->assertViewHas('stats', fn($stats) => $stats['total_bookings'] === 5);
    }

    public function test_dashboard_shows_pending_invoices_count(): void
    {
        $client  = Client::factory()->create(['user_id' => $this->user->id]);
        $booking = Booking::factory()->create(['user_id' => $this->user->id, 'client_id' => $client->id]);
        Invoice::factory()->count(2)->create(['booking_id' => $booking->id, 'status' => 'unpaid']);

        $this->actingAs($this->user);
        Livewire::test(Dashboard::class)
            ->assertViewHas('stats', fn($stats) => $stats['pending_invoices'] === 2);
    }

    public function test_dashboard_upcoming_bookings_only_shows_own(): void
    {
        $client   = Client::factory()->create(['user_id' => $this->user->id]);
        $booking  = Booking::factory()->create([
            'user_id'      => $this->user->id,
            'client_id'    => $client->id,
            'status'       => 'confirmed',
            'booking_date' => now()->addDay(),
        ]);

        $other        = User::factory()->create();
        $otherClient  = Client::factory()->create(['user_id' => $other->id]);
        $otherBooking = Booking::factory()->create([
            'user_id'      => $other->id,
            'client_id'    => $otherClient->id,
            'status'       => 'confirmed',
            'booking_date' => now()->addDay(),
        ]);

        $this->actingAs($this->user);
        Livewire::test(Dashboard::class)
            ->assertViewHas('upcomingBookings', function ($upcoming) use ($booking, $otherBooking) {
                return $upcoming->contains('id', $booking->id)
                    && ! $upcoming->contains('id', $otherBooking->id);
            });
    }
}
