<?php

namespace OoriyaP\FilamentSimpleOtp;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Support\Facades\App;
use OoriyaP\FilamentSimpleOtp\Contracts\OtpDriverContract;
use OoriyaP\FilamentSimpleOtp\Drivers\LogDriver;
use OoriyaP\FilamentSimpleOtp\Drivers\SmsIrDriver;
use OoriyaP\FilamentSimpleOtp\Pages\EditProfile;
use OoriyaP\FilamentSimpleOtp\Pages\Login;
use OoriyaP\FilamentSimpleOtp\Resources\AdminResource;

class SimpleOtpPlugin implements Plugin
{
    protected ?int $otpLength = null;

    protected ?int $otpExpiresIn = null;

    protected ?int $rateLimitAttempts = null;

    protected ?int $rateLimitDecaySeconds = null;

    protected ?int $resendLimitAttempts = null;

    protected ?int $resendLimitDecaySeconds = null;

    protected string $guard = 'web';

    protected ?string $adminModel = null;

    protected string $loginPage = Login::class;

    protected bool $hasProfilePage = true;

    protected string $profilePageClass = EditProfile::class;

    protected bool $hasAdminResource = true;

    protected string $adminResourceClass = AdminResource::class;

    protected mixed $driver = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        $panel = Filament::getCurrentPanel() ?? Filament::getPanel('admin');

        /** @var static */
        return $panel->getPlugin('simple-otp');
    }

    public function getId(): string
    {
        return 'simple-otp';
    }

    public function otpCode(?int $length = null, ?int $expiresIn = null): static
    {
        if ($length !== null) {
            $this->otpLength = $length;
        }
        if ($expiresIn !== null) {
            $this->otpExpiresIn = $expiresIn;
        }

        return $this;
    }

    public function rateLimit(?int $attempts = null, ?int $decaySeconds = null): static
    {
        if ($attempts !== null) {
            $this->rateLimitAttempts = $attempts;
        }
        if ($decaySeconds !== null) {
            $this->rateLimitDecaySeconds = $decaySeconds;
        }

        return $this;
    }

    public function resendLimit(?int $attempts = null, ?int $decaySeconds = null): static
    {
        if ($attempts !== null) {
            $this->resendLimitAttempts = $attempts;
        }
        if ($decaySeconds !== null) {
            $this->resendLimitDecaySeconds = $decaySeconds;
        }

        return $this;
    }

    public function guard(string $guard): static
    {
        $this->guard = $guard;

        return $this;
    }

    public function adminModel(string $modelClass): static
    {
        $this->adminModel = $modelClass;

        return $this;
    }

    public function userModel(string $modelClass): static
    {
        return $this->adminModel($modelClass);
    }

    public function driver(mixed $driver): static
    {
        $this->driver = $driver;

        return $this;
    }

    public function otpDriver(mixed $driver): static
    {
        return $this->driver($driver);
    }

    public function smsDriver(mixed $driver): static
    {
        return $this->driver($driver);
    }

    public function loginPage(string $loginPage): static
    {
        $this->loginPage = $loginPage;

        return $this;
    }

    public function profilePage(bool $enabled = true, string $pageClass = EditProfile::class): static
    {
        $this->hasProfilePage = $enabled;
        $this->profilePageClass = $pageClass;

        return $this;
    }

    public function adminResource(bool $enabled = true, string $resourceClass = AdminResource::class): static
    {
        $this->hasAdminResource = $enabled;
        $this->adminResourceClass = $resourceClass;

        return $this;
    }

    public function getOtpLength(): int
    {
        return $this->otpLength ?? (int) config('filament-simple-otp.otp.length', 6);
    }

    public function getOtpExpiresIn(): int
    {
        return $this->otpExpiresIn ?? (int) config('filament-simple-otp.otp.expires_in', 120);
    }

    public function getRateLimitAttempts(): int
    {
        return $this->rateLimitAttempts ?? (int) config('filament-simple-otp.rate_limit.attempts', 5);
    }

    public function getRateLimitDecaySeconds(): int
    {
        return $this->rateLimitDecaySeconds ?? (int) config('filament-simple-otp.rate_limit.decay_seconds', 60);
    }

    public function getResendLimitAttempts(): int
    {
        return $this->resendLimitAttempts ?? (int) config('filament-simple-otp.resend_limit.attempts', 3);
    }

    public function getResendLimitDecaySeconds(): int
    {
        return $this->resendLimitDecaySeconds ?? (int) config('filament-simple-otp.resend_limit.decay_seconds', 300);
    }

    public function getGuard(): string
    {
        return $this->guard;
    }

    public function getAdminModel(): string
    {
        return $this->adminModel ?? (string) config('filament-simple-otp.admin_model', 'App\\Models\\User');
    }

    public function getUserModel(): string
    {
        return $this->getAdminModel();
    }

    public function getLoginPage(): string
    {
        return $this->loginPage;
    }

    public function resolveOtpDriver(): OtpDriverContract
    {
        if ($this->driver instanceof OtpDriverContract) {
            return $this->driver;
        }

        if ($this->driver instanceof Closure) {
            return call_user_func($this->driver);
        }

        if (is_string($this->driver) && class_exists($this->driver)) {
            return app($this->driver);
        }

        if (App::environment('local', 'testing')) {
            return app(LogDriver::class);
        }

        return app(SmsIrDriver::class);
    }

    public function resolveSmsDriver(): OtpDriverContract
    {
        return $this->resolveOtpDriver();
    }

    public function register(Panel $panel): void
    {
        $panel
            ->authGuard($this->getGuard())
            ->login($this->getLoginPage());

        if ($this->hasProfilePage) {
            $panel->profile($this->profilePageClass, isSimple: false);
        }

        if ($this->hasAdminResource) {
            $panel->resources([
                $this->adminResourceClass,
            ]);
        }
    }

    public function boot(Panel $panel): void {}
}
