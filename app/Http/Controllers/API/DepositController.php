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
            ];

            if (!isset($wallets[$request->wallet])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid wallet selected'
                ]);
            }

            $walletConfig = $wallets[$request->wallet];

            $gatewayData = array_merge([
                'webhook_url' => url('/api/check-deposit'),
                'amount'      => $request->amount,
            ], $walletConfig);

            /*
            |--------------------------------------------------------------------------
            | PAYMENT GATEWAY REQUEST (FIXED)
            |--------------------------------------------------------------------------
            */

            $response = PaymentGatewayService::client()
                ->post(
                    config('payment_gateway.api_url') . '/api/create_invoice',
                    $gatewayData
                );

            if (!$response->successful()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gateway request failed',
                    'error'   => $response->body()
                ]);
            }

            $result = $response->json();

            if (!isset($result['status']) || !$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'] ?? 'Gateway error'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | SAVE DEPOSIT JOB
            |--------------------------------------------------------------------------
            */

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
                'status' => true,
                'message' => 'Invoice created successfully',
                'data' => [
                    'invoice_id' => $depositJob->invoice_id,
                    'address'    => $depositJob->wallet_address,
                    'amount'     => $depositJob->amount,
                    'wallet'     => $depositJob->wallet,
                    'status'     => $depositJob->status,
                    'created_at' => $depositJob->created_at->toDateTimeString()
                ]
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
