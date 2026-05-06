<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $phone = '';

    /**
     * Send a password reset link to the provided WhatsApp number.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $phone = $this->normalizePhoneWith62($this->phone);
        $user = User::whereIn('phone', $this->phoneCandidates($phone))->first();

        if ($user && $user->is_active) {
            $token = Password::broker()->createToken($user);
            $targetPhone = $this->normalizePhoneWith62((string) $user->phone);
            $resetUrl = route('password.reset', ['token' => $token, 'phone' => $targetPhone]);

            $sent = $this->sendViaWhatsapp($targetPhone, $resetUrl);
            if (! $sent['ok']) {
                Log::warning('Failed sending password reset via WhatsApp.', [
                    'user_id' => $user->id,
                    'phone' => $targetPhone,
                    'status' => $sent['status'] ?? null,
                    'response' => $sent['response'] ?? null,
                ]);
            }
        }

        $this->reset('phone');

        session()->flash('status', 'Jika nomor WhatsApp terdaftar, link reset password sudah dikirim melalui WhatsApp.');
    }

    private function normalizePhoneWith62(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '62' . ltrim($digits, '0');
        }

        return '62' . $digits;
    }

    private function toWhatsappJid(string $phone): string
    {
        return $phone . '@s.whatsapp.net';
    }

    /**
     * @return array<int, string>
     */
    private function phoneCandidates(string $normalizedPhone): array
    {
        $digits = preg_replace('/\D+/', '', $normalizedPhone) ?? '';

        if ($digits === '') {
            return [''];
        }

        if (str_starts_with($digits, '62')) {
            return array_values(array_unique([$digits, '0' . substr($digits, 2)]));
        }

        return array_values(array_unique([$digits, '62' . ltrim($digits, '0')]));
    }

    /**
     * @return array{ok: bool, status?: int, response?: string}
     */
    private function sendViaWhatsapp(string $phone, string $resetUrl): array
    {
        $url = rtrim((string) config('services.whatsapp_gateway.url'), '/');
        $auth = (string) config('services.whatsapp_gateway.auth');
        $deviceId = trim((string) config('services.whatsapp_gateway.device_id'));

        if ($url === '' || $auth === '') {
            return ['ok' => false, 'response' => 'Gateway URL/auth is empty'];
        }

        $parts = explode(':', $auth, 2);
        if (count($parts) !== 2) {
            return ['ok' => false, 'response' => 'Gateway auth format invalid'];
        }

        [$username, $password] = $parts;

        $message = "Halo,\n\nKami menerima permintaan reset password akun MUA Manager Anda.\nKlik link berikut untuk mengatur ulang password:\n" . $resetUrl . "\n\nJika Anda tidak merasa meminta reset password, abaikan pesan ini.";

        $response = Http::withBasicAuth($username, $password)
            ->withHeaders($deviceId !== '' ? ['X-Device-Id' => $deviceId] : [])
            ->acceptJson()
            ->timeout(12)
            ->post($url . '/send/message', [
                'phone' => $this->toWhatsappJid($phone),
                'message' => $message,
            ]);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'response' => $response->body(),
        ];
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Lupa password? Masukkan No. WhatsApp akun Anda. Jika nomor terdaftar, kami kirim link reset password melalui WhatsApp.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink">
        <!-- WhatsApp Number -->
        <div>
            <x-input-label for="phone" :value="__('No. WhatsApp')" />
            <div
                class="mt-1 flex rounded-lg border border-gray-300 overflow-hidden focus-within:ring-1 focus-within:ring-pink-500 focus-within:border-pink-500 bg-white">
                <span
                    class="px-3 flex items-center text-sm font-medium text-gray-500 bg-gray-100 border-r border-gray-300 select-none">62</span>
                <input wire:model="phone" id="phone"
                    class="w-full min-w-0 px-3 py-2 text-sm border-0 focus:ring-0 focus:outline-none" type="text"
                    inputmode="numeric" name="phone" required autofocus placeholder="81234567890" />
            </div>
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Kirim Link Reset via WhatsApp') }}
            </x-primary-button>
        </div>
    </form>
</div>
