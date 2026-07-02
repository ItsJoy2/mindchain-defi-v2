<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AmbassadorHistory;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->search);
        $wallet = $request->wallet;

        $query = Transaction::query()
            ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
            ->select([
                'transactions.*',
                'users.user_name',
                'users.email',
            ]);

        // Search Filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.user_name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('transactions.method', 'like', "%{$search}%")
                    ->orWhere('transactions.description', 'like', "%{$search}%")
                    ->orWhere('transactions.txn_id', 'like', "%{$search}%");
            });
        }

        // Wallet Filter
        if (!empty($wallet)) {
            $query->where('transactions.wallet', $wallet);
        }

        $histories = $query->orderByDesc('transactions.id')->paginate(20)->withQueryString();

        return view(
            'admin.pages.transactions.index',
            compact('histories', 'search', 'wallet')
        );
    }

    public function ambassadorHistory(Request $request)
    {
        $search = trim($request->search);
        $wallet = $request->wallet;

        $query = AmbassadorHistory::query()
            ->leftJoin('users', 'users.id', '=', 'ambassador_histories.user_id')
            ->select([
                'ambassador_histories.*',
                'users.user_name',
                'users.email',
            ]);

        // Search by Username or Email only
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.user_name', 'like', "%{$search}%")
                ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        // Wallet Filter
        if (!empty($wallet)) {
            $query->where('ambassador_histories.wallet', $wallet);
        }

        $histories = $query
            ->orderByDesc('ambassador_histories.id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.pages.transactions.ambassador-history',
            compact('histories', 'search', 'wallet')
        );
    }
}
