<?php

namespace App\Services;

use App\Models\LiquidityPool;
use App\Models\LiquiditySetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LiquidityPoolService
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function invest(
        int $userId,
        string $wallet,
        float $amount
    ): LiquidityPool {

        return DB::transaction(function () use (
            $userId,
            $wallet,
            $amount
        ) {

            $user = User::where('id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'user' => 'User not found.',
                ]);
            }

            Transaction::where('user_id', $userId)
                ->where('wallet', $wallet)
                ->lockForUpdate()
                ->get();

            $setting = LiquiditySetting::where('status', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (!$setting) {
                throw ValidationException::withMessages([
                    'liquidity' => 'Liquidity Pool is currently unavailable.',
                ]);
            }

            $minimumAmount = (float) $setting->min_amount;

            if ($amount < $minimumAmount) {
                throw ValidationException::withMessages([
                    'amount' => "Minimum liquidity contribution is {$minimumAmount}.",
                ]);
            }

            if (!$this->walletService->hasBalance(
                $userId,
                $wallet,
                $amount
            )) {
                throw ValidationException::withMessages([
                    'amount' => "Insufficient {$wallet} balance.",
                ]);
            }

            $rewardPercentage = (float) $setting->reward_percentage;

            $rewardAmount = round(
                $amount * ($rewardPercentage / 100),
                8
            );

            $totalReturnAmount = round(
                $amount + $rewardAmount,
                8
            );

            $lockDays = (int) $setting->lock_days;

            $investedAt = now();

            $releaseAt = $investedAt->copy()
                ->addDays($lockDays);


            $debited = $this->walletService->debit(
                $userId,
                $wallet,
                $amount,
                'Liquidity Pool',
                "Liquidity Pool investment of {$amount} {$wallet}",
                'Approved'
            );

            if (!$debited) {
                throw ValidationException::withMessages([
                    'amount' => "Unable to debit {$wallet} balance.",
                ]);
            }

            $pool = LiquidityPool::create([
                'user_id'             => $userId,
                'wallet'              => $wallet,
                'invested_amount'     => $amount,
                'reward_percentage'   => $rewardPercentage,
                'reward_amount'       => $rewardAmount,
                'total_return_amount' => $totalReturnAmount,
                'lock_days'           => $lockDays,
                'invested_at'         => $investedAt,
                'release_at'          => $releaseAt,
                'status'              => 'Active',
                'description'         => "Liquidity Pool investment for {$lockDays} days.",
            ]);

            return $pool;
        }, 3);
    }
}
