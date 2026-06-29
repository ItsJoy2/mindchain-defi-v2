<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $DashboardData = [
            'totalUsers' => User::where('is_admin', 0)->count(),

            'activeUsers' => User::where('is_admin', 0)->where('status', 1)->count(),

            'inactiveUsers' => User::where('is_admin', 0)->where('status', 0)->count(),

            'blockedUsers' => User::where('is_admin', 0)->where('is_block', 1)->count(),

            'mindDeposit' => Transaction::where('method', 'Deposit')->where('status', 'Approved')->where('wallet', 'MIND')->sum('amount'),

            'musdDeposit' => Transaction::where('method', 'Deposit')->where('status', 'Approved')->where('wallet', 'MUSD')->sum('amount'),

            'bmindDeposit' => Transaction::where('method', 'Deposit')->where('status', 'Approved')->where('wallet', 'BMIND')->sum('amount'),

            'usdtDeposit' => Transaction::where('method', 'Deposit')->where('status', 'Approved')->where('wallet', 'USDT')->sum('amount'),
        ];

        return view('admin.dashboard', compact('DashboardData'));
    }
}
