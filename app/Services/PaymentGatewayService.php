<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaymentGatewayService
{
    public static function generateSignature(?array $payload = null): array
    {
        $timestamp = time();

        $message = empty($payload)
            ? $timestamp . config('payment_gateway.license_key')
            : $timestamp . json_encode($payload);

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

    public static function headers(?array $payload = null): array
    {
        $auth = self::generateSignature($payload);

        return [
            'X-LICENSE-KEY' => config('payment_gateway.license_key'),
            'X-TIMESTAMP'   => $auth['timestamp'],
            'X-SIGNATURE'   => $auth['signature'],
            'Accept'        => 'application/json',
        ];
    }

    public static function client(?array $payload = null)
    {
        return Http::timeout(300)
            ->withHeaders(
                self::headers($payload)
            );
    }
}
