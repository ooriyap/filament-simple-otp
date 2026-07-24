<?php

namespace OoriyaP\FilamentSimpleOtp\Drivers;

use OoriyaP\FilamentSimpleOtp\Contracts\OtpDriverContract;

class ArrayDriver implements OtpDriverContract
{
    /**
     * @var array<int, array{mobile: string, code: string, customData: array<string, mixed>}>
     */
    protected static array $sentMessages = [];

    public function sendOtp(string $mobile, string $code, array $customData = []): bool
    {
        static::$sentMessages[] = [
            'mobile' => $mobile,
            'code' => $code,
            'customData' => $customData,
        ];

        return true;
    }

    /**
     * @return array<int, array{mobile: string, code: string, customData: array<string, mixed>}>
     */
    public static function getSentMessages(): array
    {
        return static::$sentMessages;
    }

    public static function getLastCodeFor(string $mobile): ?string
    {
        foreach (array_reverse(static::$sentMessages) as $msg) {
            if ($msg['mobile'] === $mobile) {
                return $msg['code'];
            }
        }

        return null;
    }

    public static function reset(): void
    {
        static::$sentMessages = [];
    }
}
