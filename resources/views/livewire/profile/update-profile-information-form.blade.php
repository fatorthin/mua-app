<?php

use App\Models\User;
use App\Services\WhatsAppService;
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
    public string $phone = '';
    public string $instagram = '';
    public string $tiktok = '';
    public string $whatsapp_device_id = '';
    public string $whatsapp_pair_phone = '';
    public string $whatsapp_test_phone = '';
    public ?string $whatsapp_device_status = null;
    public ?string $whatsapp_device_jid = null;
    public ?string $whatsapp_device_last_synced_at = null;
    public ?string $whatsapp_qr_link = null;
    public ?int $whatsapp_qr_duration = null;
    public ?string $whatsapp_pair_code = null;
    public ?string $whatsapp_feedback_message = null;
    public string $whatsapp_feedback_level = 'info';
    public $invoice_logo;
    public ?string $current_invoice_logo = null;
    public string $invoice_footer_notes = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->phone = Auth::user()->phone ?? '';
        $this->instagram = Auth::user()->instagram ?? '';
        $this->tiktok = Auth::user()->tiktok ?? '';
        $this->whatsapp_device_id = Auth::user()->whatsapp_device_id ?? '';
        $this->whatsapp_pair_phone = Auth::user()->phone ?? '';
        $this->whatsapp_test_phone = Auth::user()->phone ?? '';
        $this->hydrateWhatsappState(Auth::user());
        $this->current_invoice_logo = Auth::user()->invoice_logo_path;
        $this->invoice_footer_notes = Auth::user()->invoice_footer_notes ?? '';
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'tiktok' => ['nullable', 'string', 'max:255'],
            'whatsapp_device_id' => ['nullable', 'string', 'max:255'],
            'invoice_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'invoice_footer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['instagram'] = ltrim(trim((string) ($validated['instagram'] ?? '')), '@') ?: null;
        $validated['tiktok'] = ltrim(trim((string) ($validated['tiktok'] ?? '')), '@') ?: null;
        $validated['phone'] = trim((string) ($validated['phone'] ?? '')) ?: null;
        $validated['whatsapp_device_id'] = trim((string) ($validated['whatsapp_device_id'] ?? '')) ?: null;

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

    public function connectWhatsappQr(): void
    {
        $user = $this->persistRealtimeWhatsappFields();
        $service = app(WhatsAppService::class);

        if (!$user->whatsapp_device_id) {
            $created = $service->createDevice($user, $this->whatsapp_device_id);
            if (!$created['ok']) {
                $this->setWhatsappFeedback($created['message'] ?? 'Gagal membuat device WhatsApp.', 'error');

                return;
            }

            $user->refresh();
        }

        $result = $service->requestLoginQr($user->fresh());
        if (!$result['ok']) {
            $this->setWhatsappFeedback($result['message'] ?? 'Gagal membuat QR login.', 'error');

            return;
        }

        $this->whatsapp_qr_link = $result['qr_link'] ?? null;
        $this->whatsapp_qr_duration = $result['qr_duration'] ?? null;
        $this->whatsapp_pair_code = null;
        $this->whatsapp_device_id = $user->fresh()->whatsapp_device_id ?? '';
        $this->setWhatsappFeedback('QR login berhasil dibuat. Scan QR dengan WhatsApp Anda.', 'success');
    }

    public function requestWhatsappPairCode(): void
    {
        $validated = $this->validate([
            'whatsapp_pair_phone' => ['required', 'string', 'max:30'],
        ]);

        $user = $this->persistRealtimeWhatsappFields();
        $service = app(WhatsAppService::class);

        if (!$user->whatsapp_device_id) {
            $created = $service->createDevice($user, $this->whatsapp_device_id);
            if (!$created['ok']) {
                $this->setWhatsappFeedback($created['message'] ?? 'Gagal membuat device WhatsApp.', 'error');

                return;
            }

            $user->refresh();
        }

        $result = $service->requestPairingCode($user->fresh(), $validated['whatsapp_pair_phone']);
        if (!$result['ok']) {
            $this->setWhatsappFeedback($result['message'] ?? 'Gagal mengambil pair code.', 'error');

            return;
        }

        $this->whatsapp_pair_code = $result['pair_code'] ?? null;
        $this->whatsapp_qr_link = null;
        $this->setWhatsappFeedback('Pair code berhasil dibuat. Masukkan kode ini di aplikasi WhatsApp.', 'success');
    }

    public function refreshWhatsappStatus(): void
    {
        $user = $this->persistRealtimeWhatsappFields();
        $result = app(WhatsAppService::class)->refreshDeviceStatus($user);

        $user->refresh();
        $this->hydrateWhatsappState($user);

        $level = $result['ok'] ? 'success' : 'warning';
        $this->setWhatsappFeedback($result['message'] ?? 'Status device diperbarui.', $level);
    }

    public function sendWhatsappTest(): void
    {
        $validated = $this->validate([
            'whatsapp_test_phone' => ['required', 'string', 'max:30'],
        ]);

        $user = $this->persistRealtimeWhatsappFields();
        $result = app(WhatsAppService::class)->sendTestMessage($user, $validated['whatsapp_test_phone']);

        $this->setWhatsappFeedback($result['message'] ?? 'Percobaan kirim pesan selesai.', $result['ok'] ? 'success' : 'error');
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

    private function persistRealtimeWhatsappFields(): User
    {
        $user = Auth::user();

        $user
            ->forceFill([
                'phone' => trim($this->phone) !== '' ? trim($this->phone) : null,
                'whatsapp_device_id' => trim($this->whatsapp_device_id) !== '' ? trim($this->whatsapp_device_id) : null,
            ])
            ->save();

        return $user->fresh();
    }

    private function hydrateWhatsappState(User $user): void
    {
        $this->whatsapp_device_status = $user->whatsapp_device_status;
        $this->whatsapp_device_jid = $user->whatsapp_device_jid;
        $this->whatsapp_device_last_synced_at = $user->whatsapp_device_last_synced_at?->format('d M Y H:i');
    }

    private function setWhatsappFeedback(string $message, string $level = 'info'): void
    {
        $this->whatsapp_feedback_message = $message;
        $this->whatsapp_feedback_level = $level;
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
            <x-input-label for="phone" :value="__('No. WhatsApp')" />
            <div class="mt-1 flex">
                <span
                    class="inline-flex items-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">62</span>
                <x-text-input wire:model="phone" id="phone" name="phone" type="text"
                    class="block w-full rounded-l-none" autocomplete="tel" placeholder="8123456789" />
            </div>
            <p class="mt-1 text-xs text-gray-400">Nomor ini dipakai sebagai kontak WA yang tampil di invoice.</p>
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="instagram" :value="__('Username Instagram')" />
            <x-text-input wire:model="instagram" id="instagram" name="instagram" type="text"
                class="mt-1 block w-full" placeholder="Contoh: mua.studio" />
            <x-input-error class="mt-2" :messages="$errors->get('instagram')" />
        </div>

        <div>
            <x-input-label for="tiktok" :value="__('Username TikTok')" />
            <x-text-input wire:model="tiktok" id="tiktok" name="tiktok" type="text" class="mt-1 block w-full"
                placeholder="Contoh: muastudio.official" />
            <x-input-error class="mt-2" :messages="$errors->get('tiktok')" />
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Koneksi WhatsApp</h3>
                    <p class="text-xs text-gray-500">Buat device, scan QR atau pair code, lalu cek status koneksinya
                        dari sini.</p>
                </div>
                <div
                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium
                    {{ $whatsapp_device_status === 'logged_in' ? 'bg-green-100 text-green-700' : ($whatsapp_device_status ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-200 text-gray-600') }}">
                    Status: {{ $whatsapp_device_status ?: 'belum tersambung' }}
                </div>
            </div>

            <div class="grid gap-3 text-sm text-gray-600 sm:grid-cols-2">
                <div>
                    <span class="font-medium text-gray-800">Device ID:</span>
                    <span>{{ $whatsapp_device_id ?: '-' }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-800">JID:</span>
                    <span>{{ $whatsapp_device_jid ?: '-' }}</span>
                </div>
                <div class="sm:col-span-2">
                    <span class="font-medium text-gray-800">Last Sync:</span>
                    <span>{{ $whatsapp_device_last_synced_at ?: '-' }}</span>
                </div>
            </div>

            @if ($whatsapp_feedback_message)
                <div
                    class="rounded-lg px-3 py-2 text-sm
                    {{ $whatsapp_feedback_level === 'success' ? 'bg-green-50 text-green-700' : ($whatsapp_feedback_level === 'error' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700') }}">
                    {{ $whatsapp_feedback_message }}
                </div>
            @endif

            <div class="flex flex-wrap gap-2">
                <x-secondary-button type="button" wire:click="connectWhatsappQr">
                    Buka QR Login
                </x-secondary-button>
                <x-secondary-button type="button" wire:click="refreshWhatsappStatus">
                    Refresh Status
                </x-secondary-button>
            </div>

            @if ($whatsapp_qr_link)
                <div class="rounded-xl border border-dashed border-pink-200 bg-white p-4 text-center">
                    <img src="{{ $whatsapp_qr_link }}" alt="QR Login WhatsApp"
                        class="mx-auto h-56 w-56 rounded-lg border border-gray-200 object-contain">
                    <p class="mt-3 text-xs text-gray-500">Scan QR ini dari WhatsApp > Perangkat Tertaut. Berlaku
                        {{ $whatsapp_qr_duration ?? '-' }} detik.</p>
                </div>
            @endif

            <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                <div>
                    <x-input-label for="whatsapp_pair_phone" :value="__('Nomor WhatsApp akun yang akan dipairing')" />
                    <div class="mt-1 flex">
                        <span
                            class="inline-flex items-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">62</span>
                        <x-text-input wire:model="whatsapp_pair_phone" id="whatsapp_pair_phone"
                            name="whatsapp_pair_phone" type="text" class="block w-full rounded-l-none"
                            placeholder="8123456789" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('whatsapp_pair_phone')" />
                </div>
                <div>
                    <x-secondary-button type="button" wire:click="requestWhatsappPairCode">
                        Ambil Pair Code
                    </x-secondary-button>
                </div>
            </div>

            @if ($whatsapp_pair_code)
                <div class="rounded-xl border border-blue-200 bg-white p-4 text-center">
                    <p class="text-xs font-medium uppercase tracking-wide text-blue-500">Pair Code</p>
                    <p class="mt-2 text-3xl font-bold tracking-[0.3em] text-gray-900">{{ $whatsapp_pair_code }}</p>
                    <p class="mt-2 text-xs text-gray-500">Buka WhatsApp pada nomor tersebut, lalu masuk ke Perangkat
                        Tertaut > Tautkan dengan kode pasangan, kemudian masukkan kode ini secara manual.</p>
                </div>
            @endif

            <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                <div>
                    <x-input-label for="whatsapp_test_phone" :value="__('No. Tujuan Test Send')" />
                    <div class="mt-1 flex">
                        <span
                            class="inline-flex items-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">62</span>
                        <x-text-input wire:model="whatsapp_test_phone" id="whatsapp_test_phone"
                            name="whatsapp_test_phone" type="text" class="block w-full rounded-l-none"
                            placeholder="8123456789" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('whatsapp_test_phone')" />
                </div>
                <div>
                    <x-secondary-button type="button" wire:click="sendWhatsappTest">
                        Kirim Test
                    </x-secondary-button>
                </div>
            </div>
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
            <x-input-label for="invoice_footer_notes" :value="__('Info Pembayaran')" />
            <textarea wire:model="invoice_footer_notes" id="invoice_footer_notes" rows="4"
                placeholder="Contoh: Pembayaran via transfer BCA 1234567890 a.n. Studio MUA. Booking bersifat final."
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
            <p class="mt-1 text-xs text-gray-400">Teks ini akan tampil pada bagian Informasi Pembayaran di invoice.</p>
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
