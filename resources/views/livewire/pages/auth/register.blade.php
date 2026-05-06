<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $name = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:20', 'unique:' . User::class . ',phone'],
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            ],
            [
                'name.required' => 'Nama lengkap wajib diisi.',
                'phone.required' => 'No. WhatsApp wajib diisi.',
                'phone.unique' => 'No. WhatsApp ini sudah terdaftar.',
                'password.required' => 'Password wajib diisi.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
                'password.min' => 'Password minimal :min karakter.',
                'password.letters' => 'Password harus mengandung huruf.',
                'password.mixed' => 'Password harus mengandung huruf besar dan kecil.',
                'password.numbers' => 'Password harus mengandung angka.',
                'password.symbols' => 'Password harus mengandung simbol.',
                'password.uncompromised' => 'Password ini terlalu umum, gunakan password lain.',
            ],
        );

        $normalizedPhone = $this->normalizePhoneWith62($validated['phone']);

        $validated['phone'] = $normalizedPhone;
        $validated['email'] = $normalizedPhone . '@wa.mua.local';

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
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
}; ?>

<div>
    {{-- Heading --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Buat Akun Baru</h2>
        <p class="text-sm text-gray-500 mt-1">Daftar untuk mulai menggunakan MUA Manager</p>
    </div>

    <form wire:submit="register" class="space-y-5">
        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input wire:model="name" id="name" type="text" name="name" required autofocus autocomplete="name"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 text-gray-800 placeholder-gray-400 transition"
                placeholder="Nama kamu" />
            @error('name')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
            <div
                class="flex rounded-xl border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-pink-500 focus-within:border-pink-500 bg-white transition">
                <span
                    class="px-3 flex items-center text-sm font-medium text-gray-500 bg-gray-100 border-r border-gray-300 select-none">62</span>
                <input wire:model="phone" id="phone" type="text" inputmode="numeric" name="phone" required
                    autocomplete="tel"
                    class="w-full min-w-0 px-4 py-2.5 border-0 focus:ring-0 focus:outline-none text-gray-800 placeholder-gray-400"
                    placeholder="81234567890" />
            </div>
            @error('phone')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div x-data="{ showPassword: false }">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div
                class="flex items-center rounded-xl border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-pink-500 focus-within:border-pink-500 bg-white transition">
                <input wire:model="password" id="password" x-bind:type="showPassword ? 'text' : 'password'"
                    name="password" required autocomplete="new-password"
                    class="w-full min-w-0 px-4 py-2.5 border-0 focus:ring-0 focus:outline-none text-gray-800 placeholder-gray-400"
                    placeholder="••••••••" />
                <button type="button" x-on:click="showPassword = !showPassword"
                    class="shrink-0 px-3 py-2 text-xs font-medium text-pink-600 hover:text-pink-700 border-l border-gray-200 bg-white">
                    <span x-text="showPassword ? 'Sembunyikan' : 'Tampilkan'"></span>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div x-data="{ showPasswordConfirmation: false }">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi
                Password</label>
            <div
                class="flex items-center rounded-xl border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-pink-500 focus-within:border-pink-500 bg-white transition">
                <input wire:model="password_confirmation" id="password_confirmation"
                    x-bind:type="showPasswordConfirmation ? 'text' : 'password'" name="password_confirmation" required
                    autocomplete="new-password"
                    class="w-full min-w-0 px-4 py-2.5 border-0 focus:ring-0 focus:outline-none text-gray-800 placeholder-gray-400"
                    placeholder="••••••••" />
                <button type="button" x-on:click="showPasswordConfirmation = !showPasswordConfirmation"
                    class="shrink-0 px-3 py-2 text-xs font-medium text-pink-600 hover:text-pink-700 border-l border-gray-200 bg-white">
                    <span x-text="showPasswordConfirmation ? 'Sembunyikan' : 'Tampilkan'"></span>
                </button>
            </div>
            @error('password_confirmation')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <button type="submit"
            class="w-full bg-pink-600 hover:bg-pink-700 active:bg-pink-800 text-white font-semibold py-3 rounded-xl transition focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2">
            Daftar Sekarang
        </button>

        {{-- Login link --}}
        <p class="text-center text-sm text-gray-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" wire:navigate class="text-pink-600 font-medium hover:underline">Masuk</a>
        </p>
    </form>
</div>
