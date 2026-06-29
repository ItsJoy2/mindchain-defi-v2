<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletIcon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userStats = User::selectRaw("
            COUNT(CASE WHEN is_admin = 0 THEN 1 END) as totalUsers,
            COUNT(CASE WHEN is_admin = 0 AND status = 1 THEN 1 END) as activeUsers,
            COUNT(CASE WHEN is_admin = 0 AND status = 0 THEN 1 END) as inactiveUsers,
            COUNT(CASE WHEN is_admin = 0 AND is_block = 1 THEN 1 END) as blockedUsers
        ")->first();

        $depositStats = DB::table('transactions')
            ->selectRaw("
                SUM(CASE WHEN wallet = 'MIND'  THEN amount ELSE 0 END) as mindDeposit,
                SUM(CASE WHEN wallet = 'MUSD'  THEN amount ELSE 0 END) as musdDeposit,
                SUM(CASE WHEN wallet = 'BMIND' THEN amount ELSE 0 END) as bmindDeposit,
                SUM(CASE WHEN wallet = 'USDT'  THEN amount ELSE 0 END) as usdtDeposit
            ")
            ->where('method', 'Deposit')
            ->where('status', 'Approved')
            ->first();

        // Wallet Icons
        $walletIcons = WalletIcon::pluck('value', 'key');

        $DashboardData = [
            'totalUsers'    => $userStats->totalUsers,
            'activeUsers'   => $userStats->activeUsers,
            'inactiveUsers' => $userStats->inactiveUsers,
            'blockedUsers'  => $userStats->blockedUsers,

            'mindDeposit'   => $depositStats->mindDeposit ?? 0,
            'musdDeposit'   => $depositStats->musdDeposit ?? 0,
            'bmindDeposit'  => $depositStats->bmindDeposit ?? 0,
            'usdtDeposit'   => $depositStats->usdtDeposit ?? 0,
        ];

        return view('admin.dashboard', compact('DashboardData', 'walletIcons'));
    }
}
