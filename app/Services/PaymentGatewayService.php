<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaymentGatewayService
{
    public static function generateSignature(array $payload = []): array
    {
        $timestamp = time();

        $signature = hash_hmac(
            'sha256',
            $timestamp . json_encode($payload),
            config('payment_gateway.secret')
        );

        return [
            'timestamp' => $timestamp,
            'signature' => $signature,
        ];
    }

    public static function headers(array $payload = []): array
    {
        $auth = self::generateSignature($payload);

        return [
            'X-LICENSE-KEY' => config('payment_gateway.license_key'),
            'X-TIMESTAMP'   => $auth['timestamp'],
            'X-SIGNATURE'   => $auth['signature'],
        ];
    }

    public static function client(array $payload = [])
    {
        return Http::timeout(20)
            ->withHeaders(
                self::headers($payload)
            );
    }
}
