<?php

namespace OoriyaP\FilamentSimpleOtp\Pages;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use OoriyaP\FilamentSimpleOtp\Services\OtpService;
use OoriyaP\FilamentSimpleOtp\SimpleOtpPlugin;

class Login extends BaseLogin
{
    #[Url]
    public string $loginMode = 'password';

    public string $mobile = '';

    public string $password = '';

    public string $otpCode = '';

    public bool $codeSent = false;

    public int $countdown = 0;

    public bool $remember = true;

    protected string $view = 'filament-simple-otp::login';

    public function getTitle(): string|Htmlable
    {
        return __('filament-simple-otp::login.login.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('filament-simple-otp::login.login.heading');
    }

    public function setLoginMode(string $mode): void
    {
        $this->loginMode = $mode;
        $this->resetErrorBag();
    }

    /**
     * Normalize Persian/Arabic numbers and remove non-digit characters.
     */
    protected function normalizeMobile(?string $mobile): string
    {
        if (blank($mobile)) {
            return '';
        }

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $normalized = str_replace($persian, $english, $mobile);
        $normalized = str_replace($arabic, $english, $normalized);
        $normalized = preg_replace('/[^0-9]/', '', $normalized) ?? '';

        if (Str::startsWith($normalized, '98') && strlen($normalized) === 12) {
            $normalized = '0'.substr($normalized, 2);
        }

        return $normalized;
    }

    /**
     * Find user by mobile and authorize panel access.
     *
     * @throws ValidationException
     */
    protected function findAndAuthorizeUser(string $mobile): Authenticatable
    {
        $plugin = SimpleOtpPlugin::get();
        $userModel = $plugin->getUserModel();

        $user = $userModel::where('mobile', $mobile)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'mobile' => __('filament-simple-otp::login.login.validation.user_not_found'),
            ]);
        }

        $panel = Filament::getCurrentPanel();

        if (method_exists($user, 'canAccessPanel') && ! $user->canAccessPanel($panel)) {
            throw ValidationException::withMessages([
                'mobile' => __('filament-simple-otp::login.login.validation.access_denied'),
            ]);
        }

        return $user;
    }

    public function sendOtpCode(): void
    {
        $this->mobile = $this->normalizeMobile($this->mobile);

        $this->validate([
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
        ], [
            'mobile.required' => __('filament-simple-otp::login.login.validation.mobile_required'),
            'mobile.regex' => __('filament-simple-otp::login.login.validation.mobile_regex'),
        ]);

        $this->findAndAuthorizeUser($this->mobile);

        $plugin = SimpleOtpPlugin::get();
        $otpService = app(OtpService::class);

        $result = $otpService->sendOtp(
            mobile: $this->mobile,
            driver: $plugin->resolveOtpDriver(),
            length: $plugin->getOtpLength(),
            expiresIn: $plugin->getOtpExpiresIn(),
            resendAttempts: $plugin->getResendLimitAttempts(),
            resendDecaySeconds: $plugin->getResendLimitDecaySeconds()
        );

        if (! $result['success']) {
            Notification::make()
                ->title(__('filament-simple-otp::login.login.notifications.send_error_title'))
                ->body($result['message'] ?? __('filament-simple-otp::login.login.notifications.send_error_body'))
                ->danger()
                ->send();

            return;
        }

        $this->codeSent = true;
        $this->countdown = $plugin->getResendLimitDecaySeconds();

        Notification::make()
            ->title(__('filament-simple-otp::login.login.notifications.code_sent_title'))
            ->body(__('filament-simple-otp::login.login.notifications.code_sent_body'))
            ->success()
            ->send();
    }

    public function loginWithOtp(): void
    {
        $this->mobile = $this->normalizeMobile($this->mobile);
        $this->otpCode = trim($this->otpCode);

        $plugin = SimpleOtpPlugin::get();

        $this->validate([
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
            'otpCode' => ['required', 'string', 'digits:'.$plugin->getOtpLength()],
        ], [
            'mobile.required' => __('filament-simple-otp::login.login.validation.mobile_required'),
            'mobile.regex' => __('filament-simple-otp::login.login.validation.mobile_regex'),
            'otpCode.required' => __('filament-simple-otp::login.login.validation.otp_required'),
            'otpCode.digits' => __('filament-simple-otp::login.login.validation.otp_digits', ['digits' => $plugin->getOtpLength()]),
        ]);

        $otpService = app(OtpService::class);
        $verifyResult = $otpService->verifyOtp(
            mobile: $this->mobile,
            code: $this->otpCode,
            maxAttempts: $plugin->getRateLimitAttempts(),
            decaySeconds: $plugin->getRateLimitDecaySeconds()
        );

        if (! $verifyResult['success']) {
            throw ValidationException::withMessages([
                'otpCode' => $verifyResult['message'] ?? __('filament-simple-otp::login.login.validation.otp_incorrect'),
            ]);
        }

        $user = $this->findAndAuthorizeUser($this->mobile);

        Filament::auth()->login($user, $this->remember);
        session()->regenerate();

        $this->redirect(Filament::getUrl());
    }

    public function loginWithPassword(): void
    {
        $this->mobile = $this->normalizeMobile($this->mobile);

        $this->validate([
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
            'password' => ['required', 'string'],
        ], [
            'mobile.required' => __('filament-simple-otp::login.login.validation.mobile_required'),
            'mobile.regex' => __('filament-simple-otp::login.login.validation.mobile_regex'),
            'password.required' => __('filament-simple-otp::login.login.validation.password_required'),
        ]);

        $rateKey = 'login-password-attempt:'.Str::lower($this->mobile).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($rateKey, maxAttempts: 5)) {
            $seconds = RateLimiter::availableIn($rateKey);

            throw ValidationException::withMessages([
                'mobile' => __('filament-simple-otp::login.login.validation.too_many_attempts', ['seconds' => $seconds]),
            ]);
        }

        $credentials = [
            'mobile' => $this->mobile,
            'password' => $this->password,
        ];

        if (! Filament::auth()->attempt($credentials, $this->remember)) {
            RateLimiter::hit($rateKey, decaySeconds: 300);

            throw ValidationException::withMessages([
                'mobile' => __('filament-simple-otp::login.login.validation.credentials_incorrect'),
            ]);
        }

        RateLimiter::clear($rateKey);
        session()->regenerate();

        $this->redirect(Filament::getUrl());
    }

    public function resetForm(): void
    {
        $this->codeSent = false;
        $this->otpCode = '';
        $this->countdown = 0;
    }
}
