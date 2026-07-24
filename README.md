# Filament Simple OTP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ooriyap/filament-simple-otp.svg?style=flat-square)](https://packagist.org/packages/ooriyap/filament-simple-otp)
[![Total Downloads](https://img.shields.io/packagist/dt/ooriyap/filament-simple-otp.svg?style=flat-square)](https://packagist.org/packages/ooriyap/filament-simple-otp)
[![License](https://img.shields.io/packagist/l/ooriyap/filament-simple-otp.svg?style=flat-square)](LICENSE)

A flexible, lightweight, and customizable OTP (One-Time Password) & Mobile Authentication plugin for **FilamentPHP**. Supports dynamic user models, SMS.ir integration, logging drivers, rate limiting, and built-in profile/admin management.

---

## Features

- 🔐 **OTP Mobile Authentication**: Seamless mobile-based login and password authentication for Filament panels.
- 📱 **Flexible SMS Drivers**: Built-in support for `SmsIrDriver`, `LogDriver`, `NotificationDriver`, `ArrayDriver`, or custom driver classes/closures.
- 🎯 **Dynamic User Model**: Bind to custom `User` or `Admin` Eloquent models dynamically.
- ⏱️ **Throttling & Rate Limiting**: Built-in brute-force protection for OTP verification and SMS resend requests.
- 👤 **Profile & Admin Management**: Built-in Edit Profile page and Admin Resource ready out of the box.
- 🌐 **Multilingual Support**: Fully localized for Persian (`fa`), English (`en`), and easily extendable.

---

## Requirements

- **PHP**: `^8.2`
- **Laravel**: `^10.0 | ^11.0 | ^12.0 | ^13.0`
- **Filament**: `^3.0 | ^4.0 | ^5.0`

---

## Installation

Install the package via Composer:

```bash
composer require ooriyap/filament-simple-otp
```

Publish and run package database migrations:

```bash
php artisan vendor:publish --tag="filament-simple-otp-migrations"
php artisan migrate
```

*(Optional)* Publish the configuration file:

```bash
php artisan vendor:publish --tag="filament-simple-otp-config"
```

*(Optional)* Publish translation files:

```bash
php artisan vendor:publish --tag="filament-simple-otp-translations"
```

---

## Usage & Configuration

Register `SimpleOtpPlugin` in your Filament Panel Provider (e.g. `AdminPanelProvider.php`):

```php
use OoriyaP\FilamentSimpleOtp\SimpleOtpPlugin;
use OoriyaP\FilamentSimpleOtp\Drivers\SmsIrDriver;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(
            SimpleOtpPlugin::make()
                ->adminModel(\App\Models\User::class)
                ->otpCode(length: 6, expiresIn: 180)
                ->rateLimit(attempts: 5, decaySeconds: 60)
                ->resendLimit(attempts: 3, decaySeconds: 60)
                ->driver(SmsIrDriver::class)
                ->profilePage(enabled: true)
                ->adminResource(enabled: true)
        );
}
```

---

## Driver Configuration

### SMS.ir Driver

Add your **SMS.ir** credentials to your `.env` file:

```env
SMSIR_API_KEY="your-smsir-api-key"
SMSIR_TEMPLATE_ID=100000
SMSIR_PARAMETER_NAME="Code"
```

### Log Driver (Development & Local Testing)

For local development or testing without consuming SMS credits, use `LogDriver::class`:

```php
use OoriyaP\FilamentSimpleOtp\Drivers\LogDriver;

SimpleOtpPlugin::make()
    ->driver(LogDriver::class)
```

The generated OTP verification code will be written directly to `storage/logs/laravel.log`.

### Custom Driver

Implement `OtpDriverContract` to create your own SMS gateway driver:

```php
use OoriyaP\FilamentSimpleOtp\Contracts\OtpDriverContract;

class KavenegarDriver implements OtpDriverContract
{
    public function sendOtp(string $mobile, string $code): bool
    {
        // Send SMS logic via your preferred provider
        return true;
    }
}
```

Then register it in the plugin:

```php
SimpleOtpPlugin::make()
    ->driver(KavenegarDriver::class)
```

---

## Configuration Reference

| Method | Description | Default |
| --- | --- | --- |
| `adminModel(string $modelClass)` | Set the authenticatable user model | `App\Models\User` |
| `otpCode(int $length, int $expiresIn)` | Configure OTP length and expiry duration (seconds) | `6`, `180` |
| `rateLimit(int $attempts, int $decaySeconds)` | Verification rate limit settings | `5` attempts / `60`s |
| `resendLimit(int $attempts, int $decaySeconds)` | Resend SMS rate limit settings | `3` attempts / `60`s |
| `driver(mixed $driver)` | Set OTP driver (`SmsIrDriver`, `LogDriver`, etc.) | Auto-detected |
| `profilePage(bool $enabled)` | Enable/disable built-in Edit Profile page | `true` |
| `adminResource(bool $enabled)` | Enable/disable built-in Admin Resource | `true` |

---

## Testing

Run tests using Pest:

```bash
vendor/bin/pest
```

---

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for details.
