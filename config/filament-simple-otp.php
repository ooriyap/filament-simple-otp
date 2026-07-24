<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS.ir Driver Configuration
    |--------------------------------------------------------------------------
    |
    | API credentials and template parameters for SMS.ir service integration.
    |
    */
    'smsir' => [
        'api_key' => env('SMSIR_API_KEY'),
        'template_id' => env('SMSIR_TEMPLATE_ID', 100000),
        'parameter_name' => env('SMSIR_PARAMETER_NAME', 'Code'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Code Settings
    |--------------------------------------------------------------------------
    |
    | 'length': Number of digits generated for the OTP verification code.
    | 'expires_in': Time duration (in seconds) the OTP code stays valid in cache.
    |
    */
    'otp' => [
        'length' => 6,
        'expires_in' => 180,
    ],

    /*
    |--------------------------------------------------------------------------
    | Verification Rate Limiting (Brute-Force Protection)
    |--------------------------------------------------------------------------
    |
    | 'attempts': Max invalid code entries allowed before temporarily locking verification.
    | 'decay_seconds': Lockout duration (in seconds) when max invalid attempts are exceeded.
    |
    */
    'rate_limit' => [
        'attempts' => 5,
        'decay_seconds' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Resend Throttling (Anti-Spam & Cost Control)
    |--------------------------------------------------------------------------
    |
    | 'attempts': Max SMS dispatch requests allowed per decay window.
    | 'decay_seconds': UI countdown timer & resend cooldown period (in seconds).
    |
    */
    'resend_limit' => [
        'attempts' => 3,
        'decay_seconds' => 60,
    ],
];
