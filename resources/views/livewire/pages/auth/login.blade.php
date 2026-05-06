<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="rounded-[1.75rem] border border-pink-100 bg-white p-6 shadow-[0_20px_60px_rgba(236,72,153,0.08)] sm:p-8">
        <div class="mb-6">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-pink-500">Masuk Dashboard</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-gray-900">Selamat datang kembali</h2>
            <p class="mt-2 text-sm leading-6 text-gray-500">Masuk untuk mengelola booking, invoice, dan aktivitas klien
                Anda dengan lebih cepat.</p>
        </div>

        <div class="mb-6 rounded-2xl border border-pink-100 bg-pink-50/70 p-4 text-sm text-gray-600">
            <p class="font-semibold text-gray-800">Akun demo</p>
            <p class="mt-1">No. WhatsApp: <span class="font-medium text-pink-600">6281234567890</span></p>
            <p>Password: <span class="font-medium text-pink-600">password</span></p>
        </div>

        <x-auth-session-status
            class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
            :status="session('status')" />

        <form wire:submit="login" class="space-y-5">
            <div>
                <x-input-label for="login" :value="__('No. WhatsApp / Email')" class="text-sm font-semibold text-gray-700" />
                <x-text-input wire:model="form.login" id="login"
                    class="mt-2 block w-full rounded-2xl border-pink-100 bg-pink-50/40 px-4 py-3 text-sm shadow-sm focus:border-pink-400 focus:ring-pink-400"
                    type="text" name="login" required autofocus autocomplete="username"
                    placeholder="Contoh: 6281234567890" />
                <x-input-error :messages="$errors->get('form.login')" class="mt-2" />
            </div>

            <div x-data="{ showPassword: false }">
                <div class="flex items-center justify-between gap-3">
                    <x-input-label for="password" :value="__('Password')" class="text-sm font-semibold text-gray-700" />
                    @if (Route::has('password.request'))
                        <a class="text-xs font-medium text-pink-600 hover:text-pink-700"
                            href="{{ route('password.request') }}" wire:navigate>
                            {{ __('Lupa password?') }}
                        </a>
                    @endif
                </div>

                <div
                    class="mt-2 flex items-center rounded-2xl border border-pink-100 bg-pink-50/40 overflow-hidden shadow-sm focus-within:border-pink-400 focus-within:ring-1 focus-within:ring-pink-400">
                    <input wire:model="form.password" id="password" x-bind:type="showPassword ? 'text' : 'password'"
                        class="w-full min-w-0 px-4 py-3 border-0 bg-transparent text-sm focus:ring-0 focus:outline-none"
                        name="password" required autocomplete="current-password" placeholder="Masukkan password" />
                    <button type="button" x-on:click="showPassword = !showPassword"
                        class="shrink-0 px-3 py-2 text-xs font-medium text-pink-600 hover:text-pink-700 border-l border-pink-100 bg-white/80">
                        <span x-text="showPassword ? 'Sembunyikan' : 'Tampilkan'"></span>
                    </button>
                </div>

                <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4 rounded-2xl bg-gray-50 px-4 py-3">
                <label for="remember" class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <input wire:model="form.remember" id="remember" type="checkbox"
                        class="rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500" name="remember">
                    <span>{{ __('Ingat saya') }}</span>
                </label>
                <span class="text-xs text-gray-400">Login aman untuk akses dashboard</span>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-pink-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:ring-offset-2">
                    {{ __('Masuk ke Dashboard') }}
                </button>
            </div>
        </form>

        <div class="mt-6 border-t border-pink-100 pt-5 text-center text-sm text-gray-500">
            Belum punya akun?
            <a href="{{ route('register') }}" wire:navigate class="font-semibold text-pink-600 hover:text-pink-700">
                Daftar gratis sekarang
            </a>
        </div>
    </div>
</div>
