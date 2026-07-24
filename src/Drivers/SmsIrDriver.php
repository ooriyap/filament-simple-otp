<?php

namespace OoriyaP\FilamentSimpleOtp\Drivers;

use Cryptommer\Smsir\Classes\Smsir;
use Cryptommer\Smsir\Objects\Parameters;
use Illuminate\Support\Facades\Log;
use OoriyaP\FilamentSimpleOtp\Contracts\OtpDriverContract;
use Throwable;

class SmsIrDriver implements OtpDriverContract
{
    public function __construct(
        protected ?string $apiKey = null,
        protected ?int $templateId = null,
        protected ?string $parameterName = null
    ) {
        $this->apiKey = $apiKey ?? config('filament-simple-otp.smsir.api_key', config('services.smsir.api_key'));
        $this->templateId = $templateId ?? (int) config('filament-simple-otp.smsir.template_id', config('services.smsir.template_id', 100000));
        $this->parameterName = $parameterName ?? config('filament-simple-otp.smsir.parameter_name', 'Code');
    }

    public function sendOtp(string $mobile, string $code, array $customData = []): bool
    {
        if (empty($this->apiKey)) {
            Log::warning("SmsIrDriver: API Key is missing. Code for {$mobile}: {$code}");

            return true;
        }

        try {
            $smsir = app()->bound(Smsir::class)
                ? app(Smsir::class)
                : new Smsir(null, $this->apiKey);

            $paramName = $customData['parameter_name'] ?? $this->parameterName;
            $parameters = [new Parameters($paramName, (string) $code)];

            $response = $smsir->Send()->Verify($mobile, $this->templateId, $parameters);

            $data = (array) $response;

            $status = $data['Status'] ?? $data['status'] ?? $data['status_code'] ?? null;

            if ($status !== null && (int) $status === 1) {
                return true;
            }

            Log::warning('SmsIrDriver: OTP sent check failed.', [
                'mobile' => $mobile,
                'response' => $data,
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('SmsIrDriver error: '.$e->getMessage());

            return false;
        }
    }
}
