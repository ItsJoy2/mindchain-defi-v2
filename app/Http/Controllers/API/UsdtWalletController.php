<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\LiquidityPoolService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class UsdtWalletController extends Controller
{
    protected LiquidityPoolService $liquidityPoolService;

    public function __construct(
        LiquidityPoolService $liquidityPoolService
    ) {
        $this->liquidityPoolService = $liquidityPoolService;
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'wallet' => ['required','string',
                Rule::in(['MIND','MUSD','BMIND','USDT',]),
            ],

            'amount' => ['required','numeric','gt:0', ],
        ]);

        try {

            $pool = $this->liquidityPoolService->invest(
                auth()->id(),
                $validated['wallet'],
                (float) $validated['amount']
            );

            return response()->json([
                'status' => true,
                'message' => 'Liquidity Pool investment created successfully.',
                'data' => [
                    'id' => $pool->id,
                    'wallet' => $pool->wallet,
                    'invested_amount' => $pool->invested_amount,
                    'reward_percentage' => $pool->reward_percentage,
                    'reward_amount' => $pool->reward_amount,
                    'total_return_amount' => $pool->total_return_amount,
                    'lock_days' => $pool->lock_days,
                    'invested_at' => $pool->invested_at,
                    'release_at' => $pool->release_at,
                    'status' => $pool->status,
                ],
            ], 201);

        } catch (ValidationException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to create liquidity investment.',
            ], 500);
        }
    }

    public function liquidityHistory(Request $request)
    {
        try {

            $pools = \App\Models\LiquidityPool::where(
                    'user_id',
                    auth()->id()
                )
                ->latest('id')
                ->paginate(20);

            return response()->json([
                'status' => true,
                'message' => 'Liquidity Pool history retrieved successfully.',
                'data' => $pools->items(),
                'pagination' => [
                    'current_page' => $pools->currentPage(),
                    'last_page' => $pools->lastPage(),
                    'per_page' => $pools->perPage(),
                    'total' => $pools->total(),
                ],
            ]);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to retrieve liquidity history.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
