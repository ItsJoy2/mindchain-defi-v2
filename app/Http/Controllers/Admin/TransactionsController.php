<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\AmbassadorHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionsController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::query()
            ->select([
                'id',
                'amount',
                'wallet',
                'type',
                'method',
                'description',
                'status',
                'created_at',
                DB::raw("'Transaction' as source"),
            ]);

        $ambassadors = AmbassadorHistory::query()
            ->select([
                'id',
                'amount',
                'wallet',
                'type',
                'method',
                'description',
                'status',
                'created_at',
                DB::raw("'Ambassador' as source"),
            ]);

        $histories = $transactions
            ->unionAll($ambassadors);

        $histories = DB::query()
            ->fromSub($histories, 'histories')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.pages.transactions.index', compact('histories'));
    }
}
