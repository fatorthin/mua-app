<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class ClientIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $notes = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'name'  => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'phone', 'email', 'notes']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $client = Client::where('user_id', auth()->id())->findOrFail($id);
        $this->editingId = $id;
        $this->name      = $client->name;
        $this->phone     = $client->phone ?? '';
        $this->email     = $client->email ?? '';
        $this->notes     = $client->notes ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'user_id' => auth()->id(),
            'name'    => $this->name,
            'phone'   => $this->phone,
            'email'   => $this->email,
            'notes'   => $this->notes,
        ];

        if ($this->editingId) {
            Client::where('user_id', auth()->id())->findOrFail($this->editingId)->update($data);
        } else {
            Client::create($data);
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        Client::where('user_id', auth()->id())->findOrFail($id)->delete();
    }

    public function render()
    {
        $clients = Client::where('user_id', auth()->id())
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->withCount('bookings')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.clients.client-index', compact('clients'));
    }
}
