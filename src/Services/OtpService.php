<?php

namespace OoriyaP\FilamentSimpleOtp\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use OoriyaP\FilamentSimpleOtp\Contracts\OtpDriverContract;

class OtpService
{
    public function generateCode(int $length = 6): string
    {
        $min = (int) pow(10, $length - 1);
        $max = (int) pow(10, $length) - 1;

        return (string) random_int($min, $max);
    }

    public function canSend(string $mobile, int $resendAttempts = 3, int $resendDecaySeconds = 300): bool
    {
        $key = 'simple_otp_resend:'.$mobile;

        return ! RateLimiter::tooManyAttempts($key, $resendAttempts);
    }

    public function getResendAvailableIn(string $mobile): int
    {
        $key = 'simple_otp_resend:'.$mobile;

        return RateLimiter::availableIn($key);
    }

    /**
     * @return array{success: bool, message?: string, code?: string}
     */
    public function sendOtp(
        string $mobile,
        OtpDriverContract $driver,
        int $length = 6,
        int $expiresIn = 120,
        int $resendAttempts = 3,
        int $resendDecaySeconds = 300
    ): array {
        if (! $this->canSend($mobile, $resendAttempts, $resendDecaySeconds)) {
            $seconds = $this->getResendAvailableIn($mobile);

            return [
                'success' => false,
                'message' => __('filament-simple-otp::service.too_many_resends', ['seconds' => $seconds]),
            ];
        }

        $code = $this->generateCode($length);
        $cacheKey = 'simple_otp_code:'.$mobile;

        Cache::put($cacheKey, [
            'hash' => hash('sha256', $code),
            'attempts' => 0,
            'expires_at' => now()->addSeconds($expiresIn)->timestamp,
        ], $expiresIn);

        RateLimiter::hit('simple_otp_resend:'.$mobile, $resendDecaySeconds);

        $sent = $driver->sendOtp($mobile, $code);

        if (! $sent) {
            return [
                'success' => false,
                'message' => __('filament-simple-otp::service.send_failed'),
            ];
        }

        return [
            'success' => true,
            'code' => $code,
        ];
    }

    public function isVerifyRateLimited(string $mobile, int $maxAttempts = 5, int $decaySeconds = 60): bool
    {
        $key = 'simple_otp_verify:'.$mobile;

        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    public function getVerifyAvailableIn(string $mobile): int
    {
        $key = 'simple_otp_verify:'.$mobile;

        return RateLimiter::availableIn($key);
    }

    /**
     * @return array{success: bool, message?: string}
     */
    public function verifyOtp(
        string $mobile,
        string $code,
        int $maxAttempts = 5,
        int $decaySeconds = 60
    ): array {
        if ($this->isVerifyRateLimited($mobile, $maxAttempts, $decaySeconds)) {
            $seconds = $this->getVerifyAvailableIn($mobile);

            return [
                'success' => false,
                'message' => __('filament-simple-otp::service.too_many_attempts', ['seconds' => $seconds]),
            ];
        }

        $cacheKey = 'simple_otp_code:'.$mobile;
        $data = Cache::get($cacheKey);

        if (! $data || ! is_array($data)) {
            RateLimiter::hit('simple_otp_verify:'.$mobile, $decaySeconds);

            return [
                'success' => false,
                'message' => __('filament-simple-otp::service.code_expired'),
            ];
        }

        $data['attempts']++;
        Cache::put($cacheKey, $data, max(1, $data['expires_at'] - now()->timestamp));

        if ($data['attempts'] > $maxAttempts) {
            Cache::forget($cacheKey);
            RateLimiter::hit('simple_otp_verify:'.$mobile, $decaySeconds);

            return [
                'success' => false,
                'message' => __('filament-simple-otp::service.max_attempts_exceeded'),
            ];
        }

        $givenHash = hash('sha256', trim($code));

        if (! hash_equals($data['hash'], $givenHash)) {
            RateLimiter::hit('simple_otp_verify:'.$mobile, $decaySeconds);

            return [
                'success' => false,
                'message' => __('filament-simple-otp::service.code_incorrect'),
            ];
        }

        Cache::forget($cacheKey);
        RateLimiter::clear('simple_otp_verify:'.$mobile);

        return [
            'success' => true,
        ];
    }
}
