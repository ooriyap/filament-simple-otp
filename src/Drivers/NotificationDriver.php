<?php

namespace OoriyaP\FilamentSimpleOtp\Drivers;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use OoriyaP\FilamentSimpleOtp\Contracts\OtpDriverContract;

class NotificationDriver implements OtpDriverContract
{
    public function sendOtp(string $mobile, string $code, array $customData = []): bool
    {
        // همچنین لاگ استاندارد جهت اطمینان ذخیره می‌شود
        Log::info("OTP Notification sent to {$mobile}: Code [{$code}]");

        // ارسال Filament Toast Notification
        Notification::make()
            ->title(__('filament-simple-otp::login.login.notifications.demo_otp_title', ['code' => $code]))
            ->body(__('filament-simple-otp::login.login.notifications.demo_otp_body', ['mobile' => $mobile]))
            ->warning()
            ->persistent()
            ->send();

        return true;
    }
}
