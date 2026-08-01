<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DepositJob;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepositController extends Controller
{
    public function createDeposit(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:1',
                'wallet' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ]);
            }

            $user = auth()->user();

            $wallets = [
                'USDT' => [
                    'chain_id' => 56,
                    'type' => 'token',
                    'token_name' => 'USDT',
                    'contract_address' => '0x55d398326f99059fF775485246999027B3197955'
                ],
                'BNB' => [
                    'chain_id' => 56,
                    'token_name' => 'BNB',
                    'type' => 'native'
                ],
                'MIND' => [
                    'chain_id' => 9996,
                    'token_name' => 'MIND',
                    'type' => 'native'
                ],
                'MUSD' => [
                    'chain_id' => 9996,
                    'type' => 'token',
                    'token_name' => 'MUSD',
                    'contract_address' => '0xaC264f337b2780b9fd277cd9C9B2149B43F87904'
                ],
                'BMIND' => [
                    'chain_id' => 9996,
                    'type' => 'token',
                    'token_name' => 'BMIND',
                    'contract_address' => '0x781Ee88b2558e5c9030C0d436de3F7eDD38d61A2'
                ],
            ];

            if (!isset($wallets[$request->wallet])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid wallet selected'
                ]);
            }

            $walletConfig = $wallets[$request->wallet];

            $gatewayData = array_merge([
                'webhook_url' => url("/api/check-deposit/{$user->id}"),
                'amount'      => $request->amount,
            ], $walletConfig);


            $response = PaymentGatewayService::client()
                ->post(
                    config('payment_gateway.api_url').'/api/v1/create-invoice',
                    PaymentGatewayService::payload($gatewayData)
                );

            if (!$response->successful()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Gateway request failed',
                    'error'   => $response->body(),
                ]);
            }

            $result = $response->json();

            if (!($result['status'] ?? false)) {
                return response()->json([
                    'status'  => false,
                    'message' => $result['message'] ?? 'Gateway error',
                ]);
            }

            $depositJob = DepositJob::create([
                'user_id'          => $user->id,
                'invoice_id'       => $result['data']['invoice_id'] ?? null,
                'amount'           => $request->amount,
                'wallet'           => $request->wallet,
                'chain_id'         => $walletConfig['chain_id'],
                'type'             => $walletConfig['type'],
                'contract_address' => $walletConfig['contract_address'] ?? null,
                'wallet_address'   => $result['data']['address'] ?? null,
                'status'           => 'Pending',
                'gateway_response' => $result['data'] ?? null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Invoice created successfully',
                'data'    => [
                    'invoice_id' => $depositJob->invoice_id,
                    'address'    => $depositJob->wallet_address,
                    'amount'     => $depositJob->amount,
                    'wallet'     => $depositJob->wallet,
                    'status'     => $depositJob->status,
                    'expires_at' => $depositJob->created_at->addMinutes(20)->timestamp,
                ]
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function statusShow($txHash)
    {
        if (empty($txHash)) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction hash is required',
                'data' => null,
            ], 422);
        }

        try {

            $params = PaymentGatewayService::auth([
                'txHash' => $txHash,
            ]);

            $paymentResponse = PaymentGatewayService::client()->get(
                rtrim(config('payment_gateway.api_url'), '/') . "/api/v1/payments/{$txHash}",
                $params
            );

            if (!$paymentResponse->successful()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Gateway request failed',
                    'error'   => $paymentResponse->body(),
                    'data'    => null,
                ], $paymentResponse->status());
            }

            $res = $paymentResponse->json();

            return response()->json([
                'status'  => true,
                'message' => 'Payment status fetched successfully',
                'data'    => [
                    'tx_hash'        => $txHash,
                    'invoice_id'     => $res['invoice_id'] ?? null,
                    'payment_status' => strtolower($res['payment_status'] ?? 'pending'),
                    'amount'         => $res['amount'] ?? null,
                    'wallet'         => $res['token'] ?? ($res['token_name'] ?? null),
                ],
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }
}
