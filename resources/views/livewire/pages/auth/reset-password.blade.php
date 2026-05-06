<?php

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    #[Locked]
    public string $token = '';
    public string $phone = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $phone = $this->normalizePhoneWith62((string) request()->string('phone'));
        $this->phone = $phone;

        if ($phone !== '') {
            $this->email = User::whereIn('phone', $this->phoneCandidates($phone))->value('email') ?? '';
        } else {
            $this->email = (string) request()->string('email');
        }
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $normalizedPhone = $this->normalizePhoneWith62($this->phone);
        $this->email = User::whereIn('phone', $this->phoneCandidates($normalizedPhone))->value('email') ?? '';

        if ($this->email === '') {
            $this->addError('phone', 'Nomor WhatsApp tidak ditemukan.');
            return;
        }

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function ($user) {
                $user
                    ->forceFill([
                        'password' => Hash::make($this->password),
                        'remember_token' => Str::random(60),
                    ])
                    ->save();

                event(new PasswordReset($user));
            },
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('phone', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }

    private function normalizePhoneWith62(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '62' . ltrim($digits, '0');
        }

        return '62' . $digits;
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
}; ?>

<div>
    <form wire:submit="resetPassword">
        <!-- WhatsApp Number -->
        <div>
            <x-input-label for="phone" :value="__('No. WhatsApp')" />
            <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full" type="text" inputmode="numeric"
                name="phone" required autofocus autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password"
                required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                type="password" name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</div>
