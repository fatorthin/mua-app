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
            ->set('selectedServices', [
                ['service_id' => (string) $this->service->id, 'quantity' => 1, 'price' => '500000'],
            ])
            ->set('booking_date', now()->addDays(5)->format('Y-m-d'))
            ->set('booking_time', '10:00')
            ->set('location', 'Studio Rina')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('bookings.index'));

        $this->assertDatabaseHas('bookings', [
            'user_id'   => $this->user->id,
            'client_id' => $this->client->id,
            'status'    => 'confirmed',
            'price'     => 500000,
        ]);
    }

    public function test_booking_create_auto_creates_invoice(): void
    {
        $this->actingAs($this->user);

        Livewire::test(BookingCreate::class)
            ->set('client_id', (string) $this->client->id)
            ->set('selectedServices', [
                ['service_id' => (string) $this->service->id, 'quantity' => 1, 'price' => '500000'],
            ])
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
            ->set('selectedServices', [
                ['service_id' => (string) $this->service->id, 'quantity' => 1, 'price' => '500000'],
            ])
            ->set('booking_date', now()->addDays(3)->format('Y-m-d'))
            ->set('booking_time', '09:00')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clients', ['user_id' => $this->user->id, 'name' => 'Ayu Putri']);
        $this->assertDatabaseHas('bookings', ['user_id' => $this->user->id, 'status' => 'confirmed']);
    }

    public function test_booking_create_validates_required_fields(): void
    {
        $this->actingAs($this->user);

        Livewire::test(BookingCreate::class)
            ->set('selectedServices', [
                ['service_id' => '', 'quantity' => 1, 'price' => ''],
            ])
            ->call('save')
            ->assertHasErrors(['selectedServices.0.service_id', 'booking_date', 'booking_time']);
    }

    public function test_booking_create_rejects_past_date(): void
    {
        $this->actingAs($this->user);

        Livewire::test(BookingCreate::class)
            ->set('client_id', (string) $this->client->id)
            ->set('selectedServices', [
                ['service_id' => (string) $this->service->id, 'quantity' => 1, 'price' => '500000'],
            ])
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
            ->set('selectedServices', [
                ['service_id' => (string) $this->service->id, 'quantity' => 1, 'price' => '500000'],
            ])
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
            ->set('selectedServices', [
                ['service_id' => (string) $this->service->id, 'quantity' => 1, 'price' => '500000'],
            ])
            ->set('booking_date', now()->addDays(5)->format('Y-m-d'))
            ->set('booking_time', '12:00')
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_booking_index_can_dispatch_manual_reminder(): void
    {
        $booking = Booking::factory()->create([
            'user_id'   => $this->user->id,
            'client_id' => $this->client->id,
            'status'    => 'confirmed',
        ]);

        $this->actingAs($this->user);
        Livewire::test(BookingIndex::class)
            ->call('sendReminderNow', $booking->id)
            ->assertHasNoErrors();
    }

    public function test_booking_edit_updates_multi_items_and_invoice(): void
    {
        $service2 = Service::factory()->create(['user_id' => $this->user->id, 'price' => 200000, 'duration' => 30]);

        $booking = Booking::factory()->create([
            'user_id'      => $this->user->id,
            'client_id'    => $this->client->id,
            'service_id'   => $this->service->id,
            'price'        => 500000,
            'duration'     => 90,
            'booking_date' => now()->addDays(4)->setTime(10, 0),
        ]);

        $invoice = Invoice::factory()->create([
            'booking_id' => $booking->id,
            'subtotal'   => 500000,
            'total'      => 500000,
            'status'     => 'unpaid',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Bookings\BookingEdit::class, ['booking' => $booking])
            ->set('selectedServices', [
                ['service_id' => (string) $this->service->id, 'quantity' => 1, 'price' => '500000'],
                ['service_id' => (string) $service2->id, 'quantity' => 2, 'price' => '200000'],
            ])
            ->set('is_dp_paid', true)
            ->set('dp_amount', '300000')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('bookings.index'));

        $this->assertDatabaseHas('bookings', [
            'id'       => $booking->id,
            'price'    => 900000,
            'duration' => 150, // 90 + (30*2)
            'dp_amount'=> 300000,
        ]);

        $this->assertDatabaseHas('booking_items', [
            'booking_id' => $booking->id,
            'service_id' => $service2->id,
            'quantity'   => 2,
        ]);

        $this->assertDatabaseHas('invoices', [
            'id'       => $invoice->id,
            'subtotal' => 900000,
            'total'    => 600000, // 900000 - 300000
        ]);
    }

    public function test_booking_supports_transport_fee_and_updates_invoice(): void
    {
        $this->actingAs($this->user);

        Livewire::test(BookingCreate::class)
            ->set('client_id', (string) $this->client->id)
            ->set('selectedServices', [
                ['service_id' => (string) $this->service->id, 'quantity' => 1, 'price' => '500000'],
            ])
            ->set('transport_fee', '150000')
            ->set('is_dp_paid', true)
            ->set('dp_amount', '200000')
            ->set('booking_date', now()->addDays(6)->format('Y-m-d'))
            ->set('booking_time', '15:00')
            ->call('save')
            ->assertHasNoErrors();

        $booking = Booking::where('user_id', $this->user->id)->latest('id')->first();
        $this->assertNotNull($booking);
        $this->assertEquals(650000, (float) $booking->price); // 500000 + 150000
        $this->assertEquals(150000, (float) $booking->transport_fee);

        $this->assertDatabaseHas('invoices', [
            'booking_id' => $booking->id,
            'subtotal'   => 500000,
            'tax'        => 150000,
            'total'      => 450000, // 650000 - 200000
        ]);
    }
}
