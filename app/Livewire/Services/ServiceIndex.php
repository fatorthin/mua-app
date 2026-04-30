<?php

namespace App\Livewire\Services;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;

class ServiceIndex extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $description = '';
    public string $price = '';
    public string $duration = '60';
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'price'       => 'required|numeric|min:0',
            'duration'    => 'required|integer|min:15',
            'is_active'   => 'boolean',
        ];
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'description', 'price', 'duration', 'is_active']);
        $this->duration   = '60';
        $this->is_active  = true;
        $this->showModal  = true;
    }

    public function openEdit(int $id): void
    {
        $service = Service::where('user_id', auth()->id())->findOrFail($id);
        $this->editingId   = $id;
        $this->name        = $service->name;
        $this->description = $service->description ?? '';
        $this->price       = (string) $service->price;
        $this->duration    = (string) $service->duration;
        $this->is_active   = $service->is_active;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'user_id'     => auth()->id(),
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => $this->price,
            'duration'    => $this->duration,
            'is_active'   => $this->is_active,
        ];

        if ($this->editingId) {
            Service::where('user_id', auth()->id())->findOrFail($this->editingId)->update($data);
        } else {
            Service::create($data);
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'description', 'price', 'duration']);
    }

    public function delete(int $id): void
    {
        Service::where('user_id', auth()->id())->findOrFail($id)->delete();
    }

    public function render()
    {
        $services = Service::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.services.service-index', compact('services'));
    }
}
