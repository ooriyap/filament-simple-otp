<?php

namespace OoriyaP\FilamentSimpleOtp\Drivers;

use Illuminate\Support\Facades\Log;
use OoriyaP\FilamentSimpleOtp\Contracts\OtpDriverContract;

class LogDriver implements OtpDriverContract
{
    public function sendOtp(string $mobile, string $code, array $customData = []): bool
    {
        Log::info("OTP dispatched to {$mobile}: Code [{$code}]");

        return true;
    }
}
