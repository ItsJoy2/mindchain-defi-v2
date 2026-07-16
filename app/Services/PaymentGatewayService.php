<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaymentGatewayService
{
    /**
     * Sort payload recursively
     */
    protected static function sortRecursive(array $array): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = self::sortRecursive($value);
            }
        }

        ksort($array);

        return $array;
    }

    /**
     * Generate timestamp + signature
     */
    public static function generateSignature(array $payload = []): array
    {
        $timestamp = time();

        $payload = self::sortRecursive($payload);

        $message = $timestamp . json_encode($payload, JSON_UNESCAPED_SLASHES);

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
    public static function headers(array $payload = []): array
    {
        $auth = self::generateSignature($payload);

        return [
            'X-MERCHANT-ID' => config('payment_gateway.merchant_id'),
            'X-TIMESTAMP'   => $auth['timestamp'],
            'X-SIGNATURE'   => $auth['signature'],
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * HTTP client
     */
    public static function client(array $payload = [])
    {
        return Http::timeout(50)
            ->retry(2, 100)
            ->withHeaders(self::headers($payload));
    }
}
