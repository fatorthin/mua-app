<?php

namespace App\Livewire\Pricelists;

use App\Models\Pricelist;
use App\Services\WhatsAppService;
use Livewire\Component;
use Livewire\WithPagination;

class PricelistIndex extends Component
{
    use WithPagination;

    public string $search = '';

    // WhatsApp Modal State
    public bool $showWaModal = false;
    public ?int $waPricelistId = null;
    public string $waRecipientPhone = '';
    public string $waCustomMessage = '';
    public ?string $waSelectedPricelistTitle = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function duplicate(int $id): void
    {
        $pricelist = Pricelist::with(['sections.items'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $newPricelist = $pricelist->replicate();
        $newPricelist->title = $pricelist->title . ' (Salinan)';
        $newPricelist->slug = null; // will auto-generate via booted
        $newPricelist->created_at = now();
        $newPricelist->updated_at = now();
        $newPricelist->save();

        foreach ($pricelist->sections as $section) {
            $newSection = $section->replicate();
            $newSection->pricelist_id = $newPricelist->id;
            $newSection->save();

            foreach ($section->items as $item) {
                $newItem = $item->replicate();
                $newItem->pricelist_section_id = $newSection->id;
                $newItem->save();
            }
        }

        session()->flash('success', 'Dokumen pricelist berhasil disalin.');
    }

    public function openSendWaModal(int $id): void
    {
        $pricelist = Pricelist::where('user_id', auth()->id())->findOrFail($id);
        $this->waPricelistId = $id;
        $this->waSelectedPricelistTitle = $pricelist->title;
        $this->waRecipientPhone = '';

        $user = auth()->user();
        $studioName = $user->studio_name ?: $user->name;
        $this->waCustomMessage = "Halo! ✨ Berikut adalah brosur *" . $pricelist->title . "* dari *" . $studioName . "*.\n\n" .
            "Anda juga dapat melihat paket lengkap secara interaktif di link berikut:\n" .
            $pricelist->public_url . "\n\n" .
            "Silakan tanyakan jika ada yang ingin dikonsultasikan. Terima kasih! 🌸";

        $this->showWaModal = true;
    }

    public function sendWa(WhatsAppService $service): void
    {
        $this->validate([
            'waRecipientPhone' => 'required|string|min:8|max:25',
            'waCustomMessage'  => 'nullable|string|max:1000',
        ], [
            'waRecipientPhone.required' => 'Nomor WhatsApp penerima wajib diisi.',
        ]);

        if (!$this->waPricelistId) return;

        $pricelist = Pricelist::where('user_id', auth()->id())->findOrFail($this->waPricelistId);
        $result = $service->sendPricelist(auth()->user(), $this->waRecipientPhone, $pricelist, $this->waCustomMessage);

        if ($result['ok']) {
            session()->flash('success', $result['message']);
            $this->showWaModal = false;
            $this->reset(['waPricelistId', 'waRecipientPhone', 'waCustomMessage', 'waSelectedPricelistTitle']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function delete(int $id): void
    {
        $pricelist = Pricelist::where('user_id', auth()->id())->findOrFail($id);
        $pricelist->delete();

        session()->flash('success', 'Dokumen pricelist berhasil dihapus.');
    }

    public function render()
    {
        $pricelists = Pricelist::where('user_id', auth()->id())
            ->with(['sections.items'])
            ->withCount(['sections', 'items'])
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(9);

        return view('livewire.pricelists.pricelist-index', compact('pricelists'));
    }
}
