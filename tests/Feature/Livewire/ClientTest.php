<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Clients\ClientIndex;
use App\Models\Booking;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_client_index_renders(): void
    {
        $this->actingAs($this->user);
        Livewire::test(ClientIndex::class)->assertOk();
    }

    public function test_client_can_be_created(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ClientIndex::class)
            ->call('openCreate')
            ->assertSet('showModal', true)
            ->set('name', 'Siti Nurbaya')
            ->set('phone', '081234567890')
            ->set('email', 'siti@example.com')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('clients', [
            'user_id' => $this->user->id,
            'name'    => 'Siti Nurbaya',
            'email'   => 'siti@example.com',
        ]);
    }

    public function test_client_create_validates_name_required(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ClientIndex::class)
            ->call('openCreate')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_client_create_validates_email_format(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ClientIndex::class)
            ->call('openCreate')
            ->set('name', 'Dewi')
            ->set('email', 'not-an-email')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_client_can_be_edited(): void
    {
        $client = Client::factory()->create(['user_id' => $this->user->id, 'name' => 'Old Name']);

        $this->actingAs($this->user);

        Livewire::test(ClientIndex::class)
            ->call('openEdit', $client->id)
            ->assertSet('name', 'Old Name')
            ->set('name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'New Name']);
    }

    public function test_client_can_be_deleted(): void
    {
        $client = Client::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user);

        Livewire::test(ClientIndex::class)
            ->call('delete', $client->id);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_client_search_filters_by_name(): void
    {
        Client::factory()->create(['user_id' => $this->user->id, 'name' => 'Siti Nurbaya']);
        Client::factory()->create(['user_id' => $this->user->id, 'name' => 'Dewi Sartika']);

        $this->actingAs($this->user);
        Livewire::test(ClientIndex::class)
            ->set('search', 'Siti')
            ->assertSee('Siti Nurbaya')
            ->assertDontSee('Dewi Sartika');
    }

    public function test_client_shows_booking_count(): void
    {
        $client = Client::factory()->create(['user_id' => $this->user->id]);
        Booking::factory()->count(3)->create([
            'user_id'   => $this->user->id,
            'client_id' => $client->id,
        ]);

        $this->actingAs($this->user);
        Livewire::test(ClientIndex::class)
            ->assertViewHas('clients', function ($clients) use ($client) {
                $found = $clients->firstWhere('id', $client->id);
                return $found && $found->bookings_count === 3;
            });
    }

    public function test_client_cannot_see_other_users_clients(): void
    {
        $other  = User::factory()->create();
        $secret = Client::factory()->create(['user_id' => $other->id, 'name' => 'Secret Client']);

        $this->actingAs($this->user);
        Livewire::test(ClientIndex::class)
            ->assertDontSee('Secret Client');
    }
}
