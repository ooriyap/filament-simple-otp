<?php

use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use OoriyaP\FilamentSimpleOtp\Drivers\ArrayDriver;
use OoriyaP\FilamentSimpleOtp\Resources\AdminResource;
use OoriyaP\FilamentSimpleOtp\Pages\Login;
use OoriyaP\FilamentSimpleOtp\Services\OtpService;
use OoriyaP\FilamentSimpleOtp\SimpleOtpPlugin;
use OoriyaP\FilamentSimpleOtp\Tests\TestCase;

beforeEach(function () {
    ArrayDriver::reset();
    $plugin = SimpleOtpPlugin::make()
        ->guard('web')
        ->driver(new ArrayDriver)
        ->otpCode(length: 6, expiresIn: 120)
        ->rateLimit(attempts: 5, decaySeconds: 60)
        ->resendLimit(attempts: 3, decaySeconds: 300);
});

it('configures plugin properties fluently', function () {
    $plugin = SimpleOtpPlugin::make()
        ->guard('custom_guard')
        ->otpCode(length: 4, expiresIn: 300)
        ->rateLimit(attempts: 3, decaySeconds: 120)
        ->resendLimit(attempts: 2, decaySeconds: 600);

    expect($plugin->getGuard())->toBe('custom_guard')
        ->and($plugin->getOtpLength())->toBe(4)
        ->and($plugin->getOtpExpiresIn())->toBe(300)
        ->and($plugin->getRateLimitAttempts())->toBe(3)
        ->and($plugin->getRateLimitDecaySeconds())->toBe(120)
        ->and($plugin->getResendLimitAttempts())->toBe(2)
        ->and($plugin->getResendLimitDecaySeconds())->toBe(600);
});

it('generates zero-padded OTP codes correctly', function () {
    $service = new OtpService();
    $code = $service->generateCode(6);

    expect(strlen($code))->toBe(6)
        ->and(ctype_digit($code))->toBeTrue();
});

it('sends and verifies OTP via OtpService and ArrayDriver', function () {
    $service = new OtpService();
    $driver = new ArrayDriver();

    $sendResult = $service->sendOtp('09123456789', $driver, length: 6, expiresIn: 120);

    expect($sendResult['success'])->toBeTrue();

    $code = ArrayDriver::getLastCodeFor('09123456789');

    expect($code)->not()->toBeNull()
        ->and(strlen($code))->toBe(6);

    $verifyResult = $service->verifyOtp('09123456789', $code);

    expect($verifyResult['success'])->toBeTrue();
});

it('rejects invalid OTP via OtpService', function () {
    $service = new OtpService();
    $driver = new ArrayDriver();

    $service->sendOtp('09123456789', $driver, length: 6, expiresIn: 120);

    $verifyResult = $service->verifyOtp('09123456789', '999999');

    expect($verifyResult['success'])->toBeFalse()
        ->and($verifyResult['message'])->toBe(__('filament-simple-otp::service.code_incorrect'));
});

it('enforces resend limits on OtpService', function () {
    $service = new OtpService();
    $driver = new ArrayDriver();

    $res1 = $service->sendOtp('09998887766', $driver, resendAttempts: 2);
    $res2 = $service->sendOtp('09998887766', $driver, resendAttempts: 2);
    $res3 = $service->sendOtp('09998887766', $driver, resendAttempts: 2);

    expect($res1['success'])->toBeTrue()
        ->and($res2['success'])->toBeTrue()
        ->and($res3['success'])->toBeFalse();
});

it('groups admin resource row actions in an action group', function () {
    $table = AdminResource::table(Table::make());
    $actions = $table->getActions();

    expect($actions)->toHaveCount(1)
        ->and($actions[0])->toBeInstanceOf(\Filament\Actions\ActionGroup::class);
});
