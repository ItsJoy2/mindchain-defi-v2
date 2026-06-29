<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AngelStaking;
use App\Models\EliteStaking;
use App\Models\MkidsStakingProgram;
use App\Models\PurchaseStaking;
use Illuminate\Http\Request;

class InvestmentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseStaking::with('user');

        // Search by username or email
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by wallet
        if ($request->filled('wallet')) {
            $query->where('wallet', $request->wallet);
        }

        $investments = $query->latest()->paginate(20)->appends($request->query());

        return view('admin.pages.investment-history.purchase-staking', compact('investments'));
    }

    // Angel Investment history
    public function angelStaking(Request $request)
    {
        $query = AngelStaking::with('user');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $investments = $query->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('admin.pages.investment-history.angel-staking', compact('investments'));
    }
    public function eliteStaking(Request $request)
    {
        $query = EliteStaking::with('user');

        // Search by username or email
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Wallet filter
        if ($request->filled('wallet')) {
            $query->where('wallet', $request->wallet);
        }

        $investments = $query->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('admin.pages.investment-history.elite-staking', compact('investments'));
    }
    public function mkidsStaking(Request $request)
    {
        $query = MkidsStakingProgram::with('user');

        // Search by username, email, kid's name or kid's username
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where('kids_name', 'like', "%{$search}%")
                ->orWhere('kids_username', 'like', "%{$search}%")
                ->orWhereHas('user', function ($user) use ($search) {
                    $user->where('user_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });

            });
        }

        $investments = $query->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('admin.pages.investment-history.mkids-staking', compact('investments'));
    }
}
