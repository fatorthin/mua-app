<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Bookings\BookingCreate;
use App\Livewire\Bookings\BookingIndex;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Service $service;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user    = User::factory()->create();
        $this->service = Service::factory()->create(['user_id' => $this->user->id, 'price' => 500000, 'duration' => 90]);
        $this->client  = Client::factory()->create(['user_id' => $this->user->id]);
    }

    // ── BookingIndex ────────────────────────────────────────────────────────

    public function test_booking_index_renders(): void
    {
        $this->actingAs($this->user);
        Livewire::test(BookingIndex::class)->assertOk();
    }

    public function test_booking_index_shows_user_bookings(): void
    {
        $booking = Booking::factory()->create([
            'user_id'   => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $this->actingAs($this->user);
        Livewire::test(BookingIndex::class)
            ->assertSee($this->client->name);
    }

    public function test_booking_index_does_not_show_other_users_bookings(): void
    {
        $other   = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $other->id]);

        $this->actingAs($this->user);
        Livewire::test(BookingIndex::class)
            ->assertDontSee($booking->client->name);
    }

    public function test_booking_can_be_confirmed(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'pending',
        ]);

        $this->actingAs($this->user);
        Livewire::test(BookingIndex::class)
            ->call('confirmBooking', $booking->id);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'confirmed']);
    }

    public function test_booking_can_be_completed(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'confirmed',
        ]);

        $this->actingAs($this->user);
        Livewire::test(BookingIndex::class)
            ->call('completeBooking', $booking->id);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'completed']);
    }

    public function test_booking_delete_removes_booking_and_invoice(): void
    {
        $booking = Booking::factory()->create(['user_id' => $this->user->id]);
        $invoice = Invoice::factory()->create(['booking_id' => $booking->id]);

        $this->actingAs($this->user);
        Livewire::test(BookingIndex::class)
            ->call('delete', $booking->id);

        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_booking_search_filters_by_client_name(): void
    {
        $clientA = Client::factory()->create(['user_id' => $this->user->id, 'name' => 'Siti Nurbaya']);
        $clientB = Client::factory()->create(['user_id' => $this->user->id, 'name' => 'Dewi Sartika']);
        Booking::factory()->create(['user_id' => $this->user->id, 'client_id' => $clientA->id]);
        Booking::factory()->create(['user_id' => $this->user->id, 'client_id' => $clientB->id]);

        $this->actingAs($this->user);
        Livewire::test(BookingIndex::class)
            ->set('search', 'Siti')
            ->assertSee('Siti Nurbaya')
            ->assertDontSee('Dewi Sartika');
    }

    public function test_booking_status_filter(): void
    {
        $pending   = Booking::factory()->create(['user_id' => $this->user->id, 'status' => 'pending',   'client_id' => $this->client->id]);
        $confirmed = Booking::factory()->create(['user_id' => $this->user->id, 'status' => 'confirmed', 'client_id' => $this->client->id]);

        $this->actingAs($this->user);
        Livewire::test(BookingIndex::class)
            ->set('statusFilter', 'pending')
            ->assertSeeHtml('Menunggu');
    }

    // ── BookingCreate ───────────────────────────────────────────────────────

    public function test_booking_create_renders(): void
    {
        $this->actingAs($this->user);
        Livewire::test(BookingCreate::class)->assertOk();
    }

    public function test_booking_create_with_existing_client(): void
    {
        $this->actingAs($this->user);

        Livewire::test(BookingCreate::class)
            ->set('client_id', (string) $this->client->id)
            ->set('service_id', (string) $this->service->id)
            ->set('booking_date', now()->addDays(5)->format('Y-m-d'))
            ->set('booking_time', '10:00')
            ->set('location', 'Studio Rina')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('bookings.index'));

        $this->assertDatabaseHas('bookings', [
            'user_id'   => $this->user->id,
            'client_id' => $this->client->id,
            'status'    => 'pending',
            'price'     => 500000,
        ]);
    }

    public function test_booking_create_auto_creates_invoice(): void
    {
        $this->actingAs($this->user);

        Livewire::test(BookingCreate::class)
            ->set('client_id', (string) $this->client->id)
            ->set('service_id', (string) $this->service->id)
            ->set('booking_date', now()->addDays(5)->format('Y-m-d'))
            ->set('booking_time', '14:00')
            ->call('save');

        $booking = Booking::where('user_id', $this->user->id)->first();
        $this->assertNotNull($booking);
        $this->assertDatabaseHas('invoices', [
            'booking_id' => $booking->id,
            'status'     => 'unpaid',
            'total'      => 500000,
        ]);
    }

    public function test_booking_create_with_new_client(): void
    {
        $this->actingAs($this->user);

        Livewire::test(BookingCreate::class)
            ->set('newClient', true)
            ->set('new_client_name', 'Ayu Putri')
            ->set('new_client_phone', '081234567890')
            ->set('new_client_email', 'ayu@test.com')
            ->set('service_id', (string) $this->service->id)
            ->set('booking_date', now()->addDays(3)->format('Y-m-d'))
            ->set('booking_time', '09:00')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clients', ['user_id' => $this->user->id, 'name' => 'Ayu Putri']);
        $this->assertDatabaseHas('bookings', ['user_id' => $this->user->id, 'status' => 'pending']);
    }

    public function test_booking_create_validates_required_fields(): void
    {
        $this->actingAs($this->user);

        Livewire::test(BookingCreate::class)
            ->call('save')
            ->assertHasErrors(['service_id', 'booking_date', 'booking_time']);
    }

    public function test_booking_create_rejects_past_date(): void
    {
        $this->actingAs($this->user);

        Livewire::test(BookingCreate::class)
            ->set('client_id', (string) $this->client->id)
            ->set('service_id', (string) $this->service->id)
            ->set('booking_date', now()->subDays(1)->format('Y-m-d'))
            ->set('booking_time', '10:00')
            ->call('save')
            ->assertHasErrors(['booking_date']);
    }

    public function test_booking_create_detects_conflict(): void
    {
        // Create an existing booking at 10:00 for 90 minutes
        Booking::factory()->create([
            'user_id'      => $this->user->id,
            'client_id'    => $this->client->id,
            'service_id'   => $this->service->id,
            'booking_date' => now()->addDays(5)->setTimeFromTimeString('10:00:00'),
            'duration'     => 90,
            'status'       => 'confirmed',
        ]);

        $this->actingAs($this->user);

        // Try to book at 10:30 which overlaps
        Livewire::test(BookingCreate::class)
            ->set('client_id', (string) $this->client->id)
            ->set('service_id', (string) $this->service->id)
            ->set('booking_date', now()->addDays(5)->format('Y-m-d'))
            ->set('booking_time', '10:30')
            ->call('save')
            ->assertHasErrors(['booking_date']);
    }

    public function test_booking_create_allows_non_overlapping_time(): void
    {
        // Create existing booking at 10:00 for 90 minutes (ends 11:30)
        Booking::factory()->create([
            'user_id'      => $this->user->id,
            'client_id'    => $this->client->id,
            'service_id'   => $this->service->id,
            'booking_date' => now()->addDays(5)->setTimeFromTimeString('10:00:00'),
            'duration'     => 90,
            'status'       => 'confirmed',
        ]);

        $this->actingAs($this->user);

        // Book at 12:00 — no overlap
        Livewire::test(BookingCreate::class)
            ->set('client_id', (string) $this->client->id)
            ->set('service_id', (string) $this->service->id)
            ->set('booking_date', now()->addDays(5)->format('Y-m-d'))
            ->set('booking_time', '12:00')
            ->call('save')
            ->assertHasNoErrors();
    }
}
