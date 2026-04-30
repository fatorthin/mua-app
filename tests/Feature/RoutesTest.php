<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests that all protected routes redirect unauthenticated users to /login,
 * and render correctly for authenticated users.
 */
class RoutesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ── Unauthenticated redirects ───────────────────────────────────────────

    public function test_dashboard_redirects_guests(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_bookings_redirects_guests(): void
    {
        $this->get('/bookings')->assertRedirect('/login');
    }

    public function test_booking_create_redirects_guests(): void
    {
        $this->get('/bookings/create')->assertRedirect('/login');
    }

    public function test_clients_redirects_guests(): void
    {
        $this->get('/clients')->assertRedirect('/login');
    }

    public function test_services_redirects_guests(): void
    {
        $this->get('/services')->assertRedirect('/login');
    }

    public function test_invoices_redirects_guests(): void
    {
        $this->get('/invoices')->assertRedirect('/login');
    }

    public function test_admin_users_redirects_guests(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
    }

    // ── Authenticated access ────────────────────────────────────────────────

    public function test_dashboard_loads_for_authenticated_user(): void
    {
        $this->actingAs($this->user)->get('/dashboard')->assertOk();
    }

    public function test_bookings_index_loads(): void
    {
        $this->actingAs($this->user)->get('/bookings')->assertOk();
    }

    public function test_booking_create_loads(): void
    {
        $this->actingAs($this->user)->get('/bookings/create')->assertOk();
    }

    public function test_clients_index_loads(): void
    {
        $this->actingAs($this->user)->get('/clients')->assertOk();
    }

    public function test_services_index_loads(): void
    {
        $this->actingAs($this->user)->get('/services')->assertOk();
    }

    public function test_invoices_index_loads(): void
    {
        $this->actingAs($this->user)->get('/invoices')->assertOk();
    }

    // ── Booking edit ownership ──────────────────────────────────────────────

    public function test_booking_edit_loads_for_owner(): void
    {
        $booking = Booking::factory()->create(['user_id' => $this->user->id]);
        $this->actingAs($this->user)
            ->get("/bookings/{$booking->id}/edit")
            ->assertOk();
    }

    public function test_booking_edit_returns_403_for_other_user(): void
    {
        $other   = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $other->id]);

        $this->actingAs($this->user)
            ->get("/bookings/{$booking->id}/edit")
            ->assertForbidden();
    }

    // ── Admin access control ────────────────────────────────────────────────

    public function test_admin_users_returns_403_for_non_admin(): void
    {
        $this->actingAs($this->user)->get('/admin/users')->assertForbidden();
    }

    public function test_admin_users_loads_for_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }

    // ── Invoice PDF ownership ───────────────────────────────────────────────

    public function test_invoice_pdf_returns_403_for_other_user(): void
    {
        $other   = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $other->id]);
        $invoice = Invoice::factory()->create(['booking_id' => $booking->id]);

        $this->actingAs($this->user)
            ->get("/invoices/{$invoice->id}/pdf")
            ->assertForbidden();
    }

    public function test_invoice_pdf_returns_200_for_owner(): void
    {
        $booking = Booking::factory()->create(['user_id' => $this->user->id]);
        $invoice = Invoice::factory()->create(['booking_id' => $booking->id]);

        $this->actingAs($this->user)
            ->get("/invoices/{$invoice->id}/pdf")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_invoice_pdf_accessible_by_admin(): void
    {
        $admin   = User::factory()->admin()->create();
        $booking = Booking::factory()->create(['user_id' => $this->user->id]);
        $invoice = Invoice::factory()->create(['booking_id' => $booking->id]);

        $this->actingAs($admin)
            ->get("/invoices/{$invoice->id}/pdf")
            ->assertOk();
    }

    // ── Welcome page ────────────────────────────────────────────────────────

    public function test_welcome_page_loads(): void
    {
        $this->get('/')->assertOk()->assertSee('MUA Manager');
    }
}
