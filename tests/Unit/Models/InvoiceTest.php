<?php

namespace Tests\Unit\Models;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_formatted_total_attribute(): void
    {
        $invoice = Invoice::factory()->make(['total' => 750000]);
        $this->assertSame('Rp 750.000', $invoice->formatted_total);
    }

    public function test_formatted_total_large_value(): void
    {
        $invoice = Invoice::factory()->make(['total' => 1500000]);
        $this->assertSame('Rp 1.500.000', $invoice->formatted_total);
    }

    public function test_status_label_unpaid(): void
    {
        $invoice = Invoice::factory()->make(['status' => 'unpaid']);
        $this->assertSame('Belum Dibayar', $invoice->status_label);
    }

    public function test_status_label_paid(): void
    {
        $invoice = Invoice::factory()->make(['status' => 'paid']);
        $this->assertSame('Sudah Dibayar', $invoice->status_label);
    }

    public function test_invoice_belongs_to_booking(): void
    {
        $invoice = Invoice::factory()->create();
        $this->assertNotNull($invoice->booking);
    }
}
