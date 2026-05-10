<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AmbassadorHistory;
use App\Models\AngelWalletHistory;
use App\Models\BmindStakingHistory;
use App\Models\BmindTarget;
use App\Models\EliteV2StakingHistory;
use App\Models\MindStakingHistory;
use App\Models\MusdStakingHistory;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
public function index()
{
    try {

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $userId = $user->id;

        $wallets = [

            'mind_wallet' => Transaction::where('user_id', $userId)->where('wallet', 'MIND')->whereIn('status', ['Approved', 'Pending'])->where('method', '!=','Kids Program Membership')->sum('amount'),

            'bmind_wallet' => Transaction::where('user_id', $userId)->where('wallet', 'BMIND')->where('status', 'Approved')->sum('amount'),

            'musd_wallet' => Transaction::where('user_id', $userId)->where('wallet', 'MUSD')->whereIn('status', ['Approved', 'Pending'])->sum('amount'),

            'usdt_wallet' =>Transaction::where('user_id', $userId)->where('wallet', 'USDT')->where('status', 'Approved')->sum('amount'),

            'mind_staking' => MindStakingHistory::where('user_id', $userId)->sum('amount'),

            'bmind_staking' => BmindStakingHistory::where('user_id', $userId)->sum('amount'),

            'ambassador_wallet' => AmbassadorHistory::where('user_id', $userId)->sum('amount'),

            'musd_staking' => MusdStakingHistory::where('user_id', $userId)->sum('amount'),

            'angel_wallet' => AngelWalletHistory::where('user_id', $userId)->sum('amount'),

            'elite_club' => Transaction::where('user_id', $userId)->where('wallet', 'USDT')->where('method', 'Buy Elite Membership')->sum(DB::raw('ABS(amount)')),
            'elite_Club_v2' => number_format(EliteV2StakingHistory::where('user_id', $userId)->sum('amount'), 2),

            'mind_kids' => Transaction::where('user_id', $userId)->where('method', 'Kids Program Membership')->sum('amount'),

        ];


        $usdt_history = Transaction::where('user_id', $userId)
            ->where('wallet', 'USDT')
            ->latest()
            ->paginate(10, ['*'], 'usdt_page')
            ->map(function ($item) {

                return [
                    'txn_id'      => $item->txn_id,
                    'wallet'      => $item->wallet,
                    'method'      => $item->method,
                    'type'        => $item->type,
                    'amount'      => number_format($item->amount, 8),
                    'status'      => $item->status,
                    'description' => $item->description,
                    'date'        => $item->created_at->format('d M Y h:i A'),
                ];
            });

        $musd_history = Transaction::where('user_id', $userId)
            ->where('wallet', 'MUSD')
            ->latest()
            ->paginate(10, ['*'], 'musd_page')
            ->map(function ($item) {

                return [
                    'txn_id'      => $item->txn_id,
                    'wallet'      => $item->wallet,
                    'method'      => $item->method,
                    'type'        => $item->type,
                    'amount'      => number_format($item->amount, 8),
                    'status'      => $item->status,
                    'description' => $item->description,
                    'date'        => $item->created_at->format('d M Y h:i A'),
                ];
            });


        return response()->json([
            'status' => true,
            'message' => 'Dashboard data fetched successfully',

            'data' => [
                'wallets'      => $wallets,
                'usdt_history' => $usdt_history,
                'musd_history' => $musd_history,
                // 'bmind_target' => $bmindTarget,
            ]

        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}
