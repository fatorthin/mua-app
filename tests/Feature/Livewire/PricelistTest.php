<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pricelists\PricelistBuilder;
use App\Livewire\Pricelists\PricelistIndex;
use App\Models\Pricelist;
use App\Models\PricelistItem;
use App\Models\PricelistSection;
use App\Models\Service;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class PricelistTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_pricelist_index_renders_successfully(): void
    {
        $this->actingAs($this->user);

        Pricelist::factory()->create(['user_id' => $this->user->id, 'title' => 'Wedding 2026']);

        Livewire::test(PricelistIndex::class)
            ->assertOk()
            ->assertSee('Wedding 2026');
    }

    public function test_pricelist_index_isolates_multi_tenant_data(): void
    {
        $otherUser = User::factory()->create();
        Pricelist::factory()->create(['user_id' => $otherUser->id, 'title' => 'Other MUA Pricelist']);

        $this->actingAs($this->user);

        Livewire::test(PricelistIndex::class)
            ->assertOk()
            ->assertDontSee('Other MUA Pricelist');
    }

    public function test_pricelist_can_be_duplicated(): void
    {
        $this->actingAs($this->user);

        $pricelist = Pricelist::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original Pricelist',
        ]);

        $section = PricelistSection::factory()->create([
            'pricelist_id' => $pricelist->id,
            'name' => 'Original Section',
        ]);

        PricelistItem::factory()->create([
            'pricelist_section_id' => $section->id,
            'name' => 'Original Item',
        ]);

        Livewire::test(PricelistIndex::class)
            ->call('duplicate', $pricelist->id);

        $this->assertDatabaseHas('pricelists', [
            'user_id' => $this->user->id,
            'title' => 'Original Pricelist (Salinan)',
        ]);

        $this->assertDatabaseHas('pricelist_sections', [
            'name' => 'Original Section',
        ]);

        $this->assertDatabaseHas('pricelist_items', [
            'name' => 'Original Item',
        ]);
    }

    public function test_pricelist_can_be_deleted(): void
    {
        $this->actingAs($this->user);

        $pricelist = Pricelist::factory()->create(['user_id' => $this->user->id]);
        $section = PricelistSection::factory()->create(['pricelist_id' => $pricelist->id]);
        $item = PricelistItem::factory()->create(['pricelist_section_id' => $section->id]);

        Livewire::test(PricelistIndex::class)
            ->call('delete', $pricelist->id);

        $this->assertDatabaseMissing('pricelists', ['id' => $pricelist->id]);
        $this->assertDatabaseMissing('pricelist_sections', ['id' => $section->id]);
        $this->assertDatabaseMissing('pricelist_items', ['id' => $item->id]);
    }

    public function test_builder_can_create_new_pricelist_with_sections_and_items(): void
    {
        $this->actingAs($this->user);

        Livewire::test(PricelistBuilder::class)
            ->set('title', 'Pricelist Rias Wisuda 2026')
            ->set('theme_template', 'luxury_gold')
            ->set('primary_color', '#d97706')
            ->set('sections', [
                [
                    'id' => null,
                    'name' => 'Graduation Look',
                    'description' => 'Tampil menawan saat wisuda',
                    'items' => [
                        [
                            'id' => null,
                            'service_id' => null,
                            'name' => 'Wisuda Makeup + Hijabdo',
                            'price' => 500000,
                            'duration_text' => '90 Menit',
                            'description' => 'Makeup tahan seharian',
                            'features' => ['Free softlens', 'Include bulu mata premium'],
                            'is_highlighted' => true,
                        ],
                    ],
                ],
            ])
            ->set('terms_conditions', [
                'DP 50% untuk kunci tanggal.',
            ])
            ->call('save')
            ->assertRedirect(route('pricelists.index'));

        $this->assertDatabaseHas('pricelists', [
            'user_id' => $this->user->id,
            'title' => 'Pricelist Rias Wisuda 2026',
            'theme_template' => 'luxury_gold',
        ]);

        $this->assertDatabaseHas('pricelist_sections', [
            'name' => 'Graduation Look',
        ]);

        $this->assertDatabaseHas('pricelist_items', [
            'name' => 'Wisuda Makeup + Hijabdo',
            'price' => 500000,
            'is_highlighted' => true,
        ]);
    }

    public function test_builder_can_import_service_from_catalog(): void
    {
        $this->actingAs($this->user);

        $service = Service::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Bridal Signature Makeup',
            'price' => 2500000,
            'duration' => 180,
            'is_active' => true,
        ]);

        $component = Livewire::test(PricelistBuilder::class)
            ->call('importServiceToItem', 0, $service->id);

        $sections = $component->get('sections');
        $this->assertNotEmpty($sections[0]['items']);
        
        $lastItem = end($sections[0]['items']);
        $this->assertEquals('Bridal Signature Makeup', $lastItem['name']);
        $this->assertEquals(2500000, $lastItem['price']);
        $this->assertEquals($service->id, $lastItem['service_id']);
    }

    public function test_builder_prevents_unauthorized_access(): void
    {
        $otherUser = User::factory()->create();
        $pricelist = Pricelist::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user);

        Livewire::test(PricelistBuilder::class, ['pricelist' => $pricelist])
            ->assertForbidden();
    }

    public function test_public_pricelist_microsite_renders_successfully(): void
    {
        $pricelist = Pricelist::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Paket Lamaran 2026',
            'slug' => 'paket-lamaran-2026',
            'is_public' => true,
        ]);

        PricelistSection::factory()->create([
            'pricelist_id' => $pricelist->id,
            'name' => 'Lamaran & Engagement',
        ]);

        $response = $this->get('/p/paket-lamaran-2026');
        $response->assertOk();
        $response->assertSee('Paket Lamaran 2026');
    }

    public function test_private_pricelist_cannot_be_viewed_publicly(): void
    {
        $pricelist = Pricelist::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Private Package',
            'slug' => 'private-package',
            'is_public' => false,
        ]);

        $response = $this->get('/p/private-package');
        $response->assertNotFound();
    }

    public function test_pdf_export_returns_pdf_stream(): void
    {
        $this->actingAs($this->user);

        $pricelist = Pricelist::factory()->create(['user_id' => $this->user->id]);
        $section = PricelistSection::factory()->create(['pricelist_id' => $pricelist->id]);
        PricelistItem::factory()->create(['pricelist_section_id' => $section->id]);

        $response = $this->get(route('pricelists.pdf', $pricelist));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_send_whatsapp_modal_submits_via_service(): void
    {
        $this->actingAs($this->user);

        $pricelist = Pricelist::factory()->create(['user_id' => $this->user->id]);

        $mockWa = Mockery::mock(WhatsAppService::class);
        $mockWa->shouldReceive('sendPricelist')
            ->once()
            ->andReturn(['ok' => true, 'message' => 'Pricelist berhasil dikirim.']);

        $this->app->instance(WhatsAppService::class, $mockWa);

        Livewire::test(PricelistIndex::class)
            ->call('openSendWaModal', $pricelist->id)
            ->assertSet('showWaModal', true)
            ->set('waRecipientPhone', '08123456789')
            ->call('sendWa')
            ->assertSet('showWaModal', false)
            ->assertHasNoErrors();
    }
}
