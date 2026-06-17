<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DepositJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class DepositController extends Controller
{
    public function createDeposit(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:1',
                'wallet' => 'required'
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

            $gatewayData = [
                'webhook_url' => 'https://api.mindchainwallet.com/api/check-deposit',
                'amount' => $request->amount
            ];

            $gatewayData = array_merge(
                $gatewayData,
                $wallets[$request->wallet]
            );

            $payload = json_encode($gatewayData);

            $timestamp = time();

            $signature = hash_hmac(
                'sha256',
                $timestamp . $payload,
                config('payment_gateway.secret')
            );

            $response = Http::withHeaders([
                'X-LICENSE-KEY' => config('payment_gateway.license_key'),
                'X-TIMESTAMP' => $timestamp,
                'X-SIGNATURE' => $signature,
            ])->post(
                config('payment_gateway.api_url') . '/api/create_invoice',
                $gatewayData
            );

            $result = $response->json();

            if (!$response->successful() || !$result['status']) {

                return response()->json([
                    'status' => false,
                    'message' => $result['message'] ?? 'Gateway error'
                ]);
            }

            $depositJob = DepositJob::create([
                'user_id'          => $user->id,
                'invoice_id'       => $result['data']['invoice_id'],
                'amount'           => $request->amount,
                'wallet'           => $request->wallet,
                'chain_id'         => $wallets[$request->wallet]['chain_id'],
                'type'             => $wallets[$request->wallet]['type'],
                'contract_address' => $wallets[$request->wallet]['contract_address'] ?? null,
                'wallet_address'   => $result['data']['address'],
                'gateway_response' => $result['data'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Invoice created successfully',
                'data' => [
                    'address' => $depositJob->wallet_address,
                    'amount' => $depositJob->amount,
                    'wallet' => $depositJob->wallet,
                    'status' => $depositJob->status,
                    'created_at' => $depositJob->created_at->toDateTimeString()
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
