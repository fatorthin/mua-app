<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $userId): void
    {
        /** @var User|null $actor */
        $actor = Auth::user();

        abort_unless($actor && $actor->isAdmin(), 403);

        $user = User::findOrFail($userId);

        if ($actor->id === $user->id && $user->is_active) {
            session()->flash('error', 'Akun admin yang sedang login tidak bisa dinonaktifkan.');
            return;
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        session()->flash('success', 'Status user berhasil diperbarui.');
    }

    public function mount(): void
    {
        /** @var User|null $actor */
        $actor = Auth::user();

        abort_unless($actor?->isAdmin(), 403);
    }

    public function render()
    {
        $users = User::withCount(['bookings', 'clients'])
            ->when($this->search, fn($q) => $q->where(function ($sub) {
                $sub->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.admin.user-index', compact('users'));
    }
}
