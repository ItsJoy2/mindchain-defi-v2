<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaymentGatewayService
{
    /**
     * Generate timestamp + signature
     */
    public static function generateSignature(): array
    {
        $timestamp = time();

        /**
         * IMPORTANT:
         * Must match middleware exactly:
         * hash_hmac('sha256', timestamp + license_key, secret)
         */
        $message = $timestamp . config('payment_gateway.license_key');

        $signature = hash_hmac(
            'sha256',
            $message,
            config('payment_gateway.secret')
        );

        return [
            'timestamp' => $timestamp,
            'signature' => $signature,
        ];
    }

    /**
     * Request headers for API
     */
    public static function headers(): array
    {
        $auth = self::generateSignature();

        return [
            'X-LICENSE-KEY' => config('payment_gateway.license_key'),
            'X-TIMESTAMP'   => $auth['timestamp'],
            'X-SIGNATURE'   => $auth['signature'],
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * HTTP client
     */
    public static function client()
    {
        return Http::timeout(50)
            ->retry(2, 100)
            ->withHeaders(self::headers());
    }
}
