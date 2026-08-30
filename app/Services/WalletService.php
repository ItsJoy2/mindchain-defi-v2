<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

// class WalletService
// {
//     // Get balance for a specific wallet
//     public function getBalance($userId, $wallet)
//     {
//         $query = Transaction::where('user_id', $userId)
//             ->where('wallet', $wallet);



//         if ($wallet === 'MIND') {

//             return $query
//                 ->whereIn('status', ['Approved', 'Pending'])
//                 ->whereNotIn('method', ['Kids Program Membership', 'MIND Marge Staking Received'])
//                 ->sum('amount');
//         }

//         if ($wallet === 'MUSD') {

//             return $query
//                 ->whereIn('status', ['Approved', 'Pending'])
//                 ->sum('amount');
//         }

//         if ($wallet === 'BMIND') {

//             return $query
//                 ->whereIn('status', ['Approved', 'Pending'])
//                 ->sum('amount');
//         }

//         if ($wallet === 'USDT') {

//             return $query
//                 ->whereIn('status', ['Approved', 'Pending'])
//                 ->sum('amount');
//         }

//         return 0;
//     }

//     //  Check if user has sufficient balance in a specific wallet
//     public function hasBalance($userId, $wallet, $amount)
//     {
//         return $this->getBalance($userId, $wallet) >= $amount;
//     }

//     // Get all wallet balances for a user
//     public function getAllBalances($userId)
//     {
//         return [
//             'MIND'  => $this->getBalance($userId, 'MIND'),
//             'MUSD'  => $this->getBalance($userId, 'MUSD'),
//             'BMIND' => $this->getBalance($userId, 'BMIND'),
//             'USDT'  => $this->getBalance($userId, 'USDT'),
//         ];
//     }
// }


class WalletService
{
    /**
     * Get balance for a specific wallet.
     */
    public function getBalance($userId, $wallet)
    {
        $query = Transaction::where('user_id', $userId)
            ->where('wallet', $wallet);

        if ($wallet === 'MIND') {

            return $query
                ->whereIn('status', ['Approved', 'Pending'])
                ->whereNotIn('method', [
                    'Kids Program Membership',
                    'MIND Marge Staking Received'
                ])
                ->sum('amount');
        }

        if (in_array($wallet, ['MUSD', 'BMIND', 'USDT'])) {

            return $query
                ->whereIn('status', ['Approved', 'Pending'])
                ->sum('amount');
        }

        return 0;
    }


    /**
     * Check if user has sufficient balance.
     *
     * IMPORTANT:
     * This method only checks balance.
     * For balance + debit, use debit() inside a DB transaction
     * with the user row locked using lockForUpdate().
     */
    public function hasBalance($userId, $wallet, $amount)
    {
        return $this->getBalance($userId, $wallet) >= $amount;
    }


    /**
     * Atomically check balance and create debit transaction.
     *
     * IMPORTANT:
     * The caller must already have:
     *
     * DB::beginTransaction();
     *
     * and must lock the user:
     *
     * User::where('id', $userId)->lockForUpdate()->first();
     *
     * This prevents concurrent requests from spending the same balance.
     */
    public function debit(
        $userId,
        $wallet,
        $amount,
        $method,
        $description,
        $status = 'Approved'
    ) {

        $amount = (float) $amount;

        if ($amount <= 0) {
            throw new \InvalidArgumentException(
                'Debit amount must be greater than zero.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Re-check balance immediately before debit
        |--------------------------------------------------------------------------
        */
        $balance = $this->getBalance(
            $userId,
            $wallet
        );

        /*
        |--------------------------------------------------------------------------
        | Final balance protection
        |--------------------------------------------------------------------------
        */
        if ($balance < $amount) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Create debit transaction
        |--------------------------------------------------------------------------
        */
        Transaction::create([
            'user_id'     => $userId,
            'wallet'      => $wallet,
            'amount'      => -$amount,
            'method'      => $method,
            'type'        => 'Debit',
            'status'      => $status,
            'description' => $description,
        ]);

        return true;
    }


    /**
     * Get all wallet balances for a user.
     */
    public function getAllBalances($userId)
    {
        return [
            'MIND'  => $this->getBalance($userId, 'MIND'),
            'MUSD'  => $this->getBalance($userId, 'MUSD'),
            'BMIND' => $this->getBalance($userId, 'BMIND'),
            'USDT'  => $this->getBalance($userId, 'USDT'),
        ];
    }
}
