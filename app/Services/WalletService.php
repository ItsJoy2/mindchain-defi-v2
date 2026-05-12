<?php

namespace App\Services;

use App\Models\Transaction;

class WalletService
{
    // Get balance for a specific wallet
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

        if ($wallet === 'MUSD') {

            return $query
                ->whereIn('status', ['Approved', 'Pending'])
                ->sum('amount');
        }

        if ($wallet === 'BMIND') {

            return $query
                ->where('status', 'Approved')
                ->sum('amount');
        }

        if ($wallet === 'USDT') {

            return $query
                ->where('status', 'Approved')
                ->sum('amount');
        }

        return 0;
    }

    //  Check if user has sufficient balance in a specific wallet
    public function hasBalance($userId, $wallet, $amount)
    {
        return $this->getBalance($userId, $wallet) >= $amount;
    }

    // Get all wallet balances for a user
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
