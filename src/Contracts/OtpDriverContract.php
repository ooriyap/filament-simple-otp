<?php

namespace OoriyaP\FilamentSimpleOtp\Contracts;

interface OtpDriverContract
{
    /**
     * Send an OTP code to the given recipient.
     *
     * @param  array<string, mixed>  $customData
     */
    public function sendOtp(string $mobile, string $code, array $customData = []): bool;
}
