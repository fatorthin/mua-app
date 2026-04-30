<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Invoices\InvoiceIndex;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_invoice_index_renders(): void
    {
        $this->actingAs($this->user);
        Livewire::test(InvoiceIndex::class)->assertOk();
    }

    public function test_invoice_index_shows_own_invoices(): void
    {
        $booking = Booking::factory()->create(['user_id' => $this->user->id]);
        $invoice = Invoice::factory()->create([
            'booking_id'     => $booking->id,
            'invoice_number' => 'INV-20260101-0001',
        ]);

        $this->actingAs($this->user);
        Livewire::test(InvoiceIndex::class)
            ->assertSee('INV-20260101-0001');
    }

    public function test_invoice_index_does_not_show_other_users_invoices(): void
    {
        $other   = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $other->id]);
        $invoice = Invoice::factory()->create([
            'booking_id'     => $booking->id,
            'invoice_number' => 'INV-SECRET-9999',
        ]);

        $this->actingAs($this->user);
        Livewire::test(InvoiceIndex::class)
            ->assertDontSee('INV-SECRET-9999');
    }

    public function test_mark_paid_changes_status(): void
    {
        $booking = Booking::factory()->create(['user_id' => $this->user->id]);
        $invoice = Invoice::factory()->create(['booking_id' => $booking->id, 'status' => 'unpaid']);

        $this->actingAs($this->user);
        Livewire::test(InvoiceIndex::class)
            ->call('markPaid', $invoice->id);

        $this->assertDatabaseHas('invoices', [
            'id'     => $invoice->id,
            'status' => 'paid',
        ]);
        $this->assertNotNull(Invoice::find($invoice->id)->paid_at);
    }

    public function test_mark_paid_sets_paid_at_date(): void
    {
        $booking = Booking::factory()->create(['user_id' => $this->user->id]);
        $invoice = Invoice::factory()->create(['booking_id' => $booking->id, 'status' => 'unpaid']);

        $this->actingAs($this->user);
        Livewire::test(InvoiceIndex::class)
            ->call('markPaid', $invoice->id);

        $fresh = Invoice::find($invoice->id);
        $this->assertNotNull($fresh->paid_at);
        $this->assertSame(now()->toDateString(), $fresh->paid_at->toDateString());
    }

    public function test_mark_paid_cannot_affect_other_users_invoice(): void
    {
        $other   = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $other->id]);
        $invoice = Invoice::factory()->create(['booking_id' => $booking->id, 'status' => 'unpaid']);

        $this->actingAs($this->user);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(InvoiceIndex::class)
            ->call('markPaid', $invoice->id);
    }

    public function test_status_filter_shows_only_unpaid(): void
    {
        $booking1 = Booking::factory()->create(['user_id' => $this->user->id]);
        $booking2 = Booking::factory()->create(['user_id' => $this->user->id]);
        Invoice::factory()->create(['booking_id' => $booking1->id, 'status' => 'unpaid',  'invoice_number' => 'INV-UNPAID-0001']);
        Invoice::factory()->create(['booking_id' => $booking2->id, 'status' => 'paid',    'invoice_number' => 'INV-PAID-0002']);

        $this->actingAs($this->user);
        Livewire::test(InvoiceIndex::class)
            ->set('statusFilter', 'unpaid')
            ->assertSee('INV-UNPAID-0001')
            ->assertDontSee('INV-PAID-0002');
    }
}
