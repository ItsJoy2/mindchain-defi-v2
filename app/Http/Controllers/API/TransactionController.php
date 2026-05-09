<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Transaction::where('user_id', $user->id);

        // Filters
        if ($request->filled('wallet')) {
            $query->where('wallet', $request->wallet);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('txn_id', 'like', "%{$search}%")
                  ->orWhere('kids_username', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 20);

        $paginated = $query->latest()->paginate($perPage);

        // hide unwanted field
        $data = $paginated->getCollection()->makeHidden([
            'confirmation_code'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User transactions fetched successfully',
            'data' => $data,

            // simplified pagination
            'pagination' => [
                'page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ]
        ]);
    }
}
