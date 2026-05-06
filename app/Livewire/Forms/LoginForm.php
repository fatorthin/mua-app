<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string')]
    public string $login = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        [$field, $value] = $this->resolveLoginField();

        if ($field === 'phone') {
            $candidates = $this->phoneCandidates($this->login);

            $activeUser = User::whereIn('phone', $candidates)
                ->where('is_active', true)
                ->first();

            if ($activeUser && Hash::check($this->password, $activeUser->password)) {
                Auth::login($activeUser, $this->remember);
                RateLimiter::clear($this->throttleKey());
                return;
            }

            $inactive = User::whereIn('phone', $candidates)
                ->where('is_active', false)
                ->first();

            if ($inactive && Hash::check($this->password, $inactive->password)) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'form.login' => 'User sudah di non aktifkan oleh admin, tolong hubungi admin untuk pengaktifan akunnya.',
                ]);
            }

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.login' => trans('auth.failed'),
            ]);
        }

        if (! Auth::attempt([
            $field => $value,
            'password' => $this->password,
            'is_active' => true,
        ], $this->remember)) {
            $inactive = User::where($field, $value)
                ->where('is_active', false)
                ->first();

            if ($inactive && Hash::check($this->password, $inactive->password)) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'form.login' => 'User sudah di non aktifkan oleh admin, tolong hubungi admin untuk pengaktifan akunnya.',
                ]);
            }

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        [, $value] = $this->resolveLoginField();

        return Str::transliterate(Str::lower($value) . '|' . request()->ip());
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function resolveLoginField(): array
    {
        $login = trim($this->login);

        if (str_contains($login, '@')) {
            return ['email', Str::lower($login)];
        }

        $digits = preg_replace('/\D+/', '', $login) ?? '';
        if (str_starts_with($digits, '62')) {
            $normalized = $digits;
        } elseif (str_starts_with($digits, '0')) {
            $normalized = '62' . ltrim($digits, '0');
        } else {
            $normalized = '62' . $digits;
        }

        return ['phone', $normalized];
    }

    /**
     * @return array<int, string>
     */
    protected function phoneCandidates(string $phone): array
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return [''];
        }

        if (str_starts_with($digits, '62')) {
            $local = '0' . substr($digits, 2);
            return array_values(array_unique([$digits, $local]));
        }

        if (str_starts_with($digits, '0')) {
            $normalized = '62' . ltrim($digits, '0');
            return array_values(array_unique([$digits, $normalized]));
        }

        return array_values(array_unique(['62' . $digits, '0' . $digits]));
    }
}
