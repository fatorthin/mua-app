<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Services\ServiceIndex;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_service_index_renders(): void
    {
        $this->actingAs($this->user);
        Livewire::test(ServiceIndex::class)->assertOk();
    }

    public function test_service_can_be_created(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ServiceIndex::class)
            ->call('openCreate')
            ->assertSet('showModal', true)
            ->set('name', 'Bridal Makeup Premium')
            ->set('price', '2000000')
            ->set('duration', '180')
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('services', [
            'user_id' => $this->user->id,
            'name'    => 'Bridal Makeup Premium',
            'price'   => 2000000,
        ]);
    }

    public function test_service_create_validates_required_fields(): void
    {
        $this->actingAs($this->user);

        // duration has a default of '60' so it won't fail; only name and price are truly empty
        Livewire::test(ServiceIndex::class)
            ->call('openCreate')
            ->set('duration', '') // clear the default
            ->call('save')
            ->assertHasErrors(['name', 'price', 'duration']);
    }

    public function test_service_create_validates_price_non_negative(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ServiceIndex::class)
            ->call('openCreate')
            ->set('name', 'Test')
            ->set('price', '-100')
            ->set('duration', '60')
            ->call('save')
            ->assertHasErrors(['price']);
    }

    public function test_service_can_be_edited(): void
    {
        $service = Service::factory()->create(['user_id' => $this->user->id, 'name' => 'Old Name']);

        $this->actingAs($this->user);

        Livewire::test(ServiceIndex::class)
            ->call('openEdit', $service->id)
            ->assertSet('showModal', true)
            ->assertSet('name', 'Old Name')
            ->set('name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('services', ['id' => $service->id, 'name' => 'New Name']);
    }

    public function test_service_can_be_deleted(): void
    {
        $service = Service::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user);

        Livewire::test(ServiceIndex::class)
            ->call('delete', $service->id);

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_service_only_shows_own_services(): void
    {
        $otherUser    = User::factory()->create();
        $otherService = Service::factory()->create(['user_id' => $otherUser->id, 'name' => 'Other Service']);
        $myService    = Service::factory()->create(['user_id' => $this->user->id, 'name' => 'My Service']);

        $this->actingAs($this->user);
        Livewire::test(ServiceIndex::class)
            ->assertSee('My Service')
            ->assertDontSee('Other Service');
    }

    public function test_service_duration_minimum_15(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ServiceIndex::class)
            ->call('openCreate')
            ->set('name', 'Quick')
            ->set('price', '100000')
            ->set('duration', '10')
            ->call('save')
            ->assertHasErrors(['duration']);
    }
}
