<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\AmbassadorHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Transaction Query
        $transactionQuery = Transaction::query()
            ->where('user_id', $user->id)
            ->select([
                'id',
                'user_id',
                'amount',
                'wallet',
                'method',
                'status',
                'created_at',
                DB::raw("'transaction' as source")
            ]);

        // Ambassador Query
        $ambassadorQuery = AmbassadorHistory::query()
            ->where('user_id', $user->id)
            ->select([
                'id',
                'user_id',
                'amount',
                'wallet',
                'method',
                'status',
                'created_at',
                DB::raw("'ambassador' as source")
            ]);

        // Common Filters
        if ($request->filled('wallet')) {
            $transactionQuery->where('wallet', $request->wallet);
            $ambassadorQuery->where('wallet', $request->wallet);
        }

        if ($request->filled('status')) {
            $transactionQuery->where('status', $request->status);
            $ambassadorQuery->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $transactionQuery->where('method', $request->method);
            $ambassadorQuery->where('method', $request->method);
        }

        if ($request->filled('date_start')) {
            $transactionQuery->whereDate('created_at', '>=', $request->date_start);
            $ambassadorQuery->whereDate('created_at', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $transactionQuery->whereDate('created_at', '<=', $request->date_end);
            $ambassadorQuery->whereDate('created_at', '<=', $request->date_end);
        }

        // Search only on Transaction table
        if ($request->filled('search')) {
            $search = $request->search;

            $transactionQuery->where(function ($q) use ($search) {
                $q->where('txn_id', 'like', "%{$search}%")
                  ->orWhere('kids_username', 'like', "%{$search}%");
            });
        }

        // Merge Queries
        $unionQuery = $transactionQuery->unionAll($ambassadorQuery);

        $perPage = (int) $request->get('per_page', 20);

        $paginated = DB::query()
            ->fromSub($unionQuery, 'histories')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $data = collect($paginated->items())->map(function ($item) {
            $item = (array) $item;

            // transaction table only field
            unset($item['confirmation_code']);

            return $item;
        });

        return response()->json([
            'status' => true,
            'message' => 'User transactions fetched successfully',
            'data' => $data,
            'pagination' => [
                'page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ]
        ]);
    }
}
