<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $invoice_logo;
    public ?string $current_invoice_logo = null;
    public string $invoice_footer_notes = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->current_invoice_logo = Auth::user()->invoice_logo_path;
        $this->invoice_footer_notes = Auth::user()->invoice_footer_notes ?? '';
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'invoice_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'invoice_footer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($this->invoice_logo) {
            if ($user->invoice_logo_path) {
                Storage::disk('public')->delete($user->invoice_logo_path);
            }

            $validated['invoice_logo_path'] = $this->invoice_logo->store('invoice-logos', 'public');
        }

        unset($validated['invoice_logo']);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        $this->current_invoice_logo = $user->invoice_logo_path;
        $this->invoice_logo = null;

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6" enctype="multipart/form-data">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required
                autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full"
                required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="invoice_logo" :value="__('Logo MUA untuk Invoice')" />
            <input wire:model="invoice_logo" id="invoice_logo" name="invoice_logo" type="file" accept="image/*"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-pink-50 file:px-4 file:py-2 file:text-pink-700 hover:file:bg-pink-100">
            <x-input-error class="mt-2" :messages="$errors->get('invoice_logo')" />

            @if ($current_invoice_logo)
                <p class="mt-2 text-xs text-gray-500">Logo saat ini:</p>
                <img src="{{ Storage::url($current_invoice_logo) }}" alt="Logo invoice"
                    class="mt-2 h-20 w-auto rounded-md border border-gray-200 bg-white p-2">
            @endif
        </div>

        <div>
            <x-input-label for="invoice_footer_notes" :value="__('Keterangan Invoice (S&K, No. Rekening, dll)')" />
            <textarea wire:model="invoice_footer_notes" id="invoice_footer_notes" rows="4"
                placeholder="Contoh: Pembayaran via transfer BCA 1234567890 a.n. Studio MUA. Booking bersifat final."
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
            <p class="mt-1 text-xs text-gray-400">Teks ini akan ditampilkan di bagian bawah invoice.</p>
            <x-input-error class="mt-2" :messages="$errors->get('invoice_footer_notes')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
