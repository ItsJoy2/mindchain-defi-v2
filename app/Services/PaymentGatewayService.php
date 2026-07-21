<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaymentGatewayService
{
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

    protected static function generateSignature(array $payload = []): array
    {
        $merchantId = config('payment_gateway.merchant_id');
        $timestamp  = time();

        unset(
            $payload['merchant_id'],
            $payload['timestamp'],
            $payload['signature']
        );

        $payload = self::sortRecursive($payload);

        $message = $merchantId
            . $timestamp
            . json_encode($payload, JSON_UNESCAPED_SLASHES);

        $signature = hash_hmac(
            'sha256',
            $message,
            config('payment_gateway.secret')
        );

        return [
            'merchant_id' => $merchantId,
            'timestamp'   => $timestamp,
            'signature'   => $signature,
        ];
    }

    /**
     * POST request body
     */
    public static function payload(array $payload = []): array
    {
        return array_merge(
            $payload,
            self::generateSignature($payload)
        );
    }

    /**
     * GET query parameters
     */
    public static function auth(array $payload = []): array
    {
        return self::generateSignature($payload);
    }

    public static function client()
    {
        return Http::asJson()
            ->acceptJson()
            ->timeout(50)
            ->retry(2, 100);
    }
}
