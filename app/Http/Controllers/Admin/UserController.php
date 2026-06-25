<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AmbassadorHistory;
use App\Models\Transaction;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('is_admin', 0);

        // Search by email or username
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        // Active / Inactive filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('status', 1);
            }

            if ($request->status === 'inactive') {
                $query->where('status', 0);
            }
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.pages.users.index', compact('users'));
    }

    public function show(User $user, WalletService $walletService)
    {
        $wallets = $walletService->getAllBalances($user->id);

        $wallets['AMBASSADOR'] = AmbassadorHistory::where('user_id', $user->id)
            ->sum('amount');

        return view('admin.pages.users.show', compact(
            'user',
            'wallets'
        ));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'user_name' => 'required|unique:users,user_name,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'sponsor_id' => ['nullable','exists:users,id',Rule::notIn([$user->id]),
    ],
        ]);

        $user->update([
            'user_name' => $request->user_name,
            'email' => $request->email,
            'sponsor_id' => $request->sponsor_id,
        ]);

        return back()->with(
            'success',
            'User information updated successfully.'
        );
    }
    public function searchUsers(Request $request)
    {
        $search = $request->q;
        $userId = $request->user_id;

        $users = User::where('id', '!=', $userId)
            ->where(function ($query) use ($search) {
                $query->where('user_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get();

        return response()->json([
            'results' => $users->map(function ($user) {
                return [
                    'id'   => $user->id,
                    'text' => $user->user_name . ' (' . $user->email . ')',
                ];
            })
        ]);
    }
    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user->password = $request->password;
        $user->save();

        return back()->with(
            'success',
            'Password updated successfully.'
        );
    }

    public function adjustWallet(Request $request, User $user)
    {
        $request->validate([
            'wallet' => 'required|in:MIND,MUSD,USDT,BMIND,AMBASSADOR',
            'action' => 'required|in:add,deduct',
            'amount' => 'required|numeric|min:0.001',
        ]);

        $amount = $request->action === 'deduct'
            ? -abs($request->amount)
            : abs($request->amount);

        if ($request->wallet === 'AMBASSADOR') {

            AmbassadorHistory::create([
                'user_id'     => $user->id,
                'amount'      => $amount,
                'wallet'      => 'MIND',
                'type'        => $request->action === 'add' ? 'Credit' : 'Debit',
                'method'      => 'Balance Adjustment',
                'description' => $amount . ' MIND ' . ($request->action === 'add' ? 'added to' : 'deducted from') . ' by Administrator.',
                'status'      => 'Approved',
            ]);

        } else {

            Transaction::create([
                'user_id'     => $user->id,
                'amount'      => $amount,
                'wallet'      => $request->wallet,
                'type'        => $request->action === 'add' ? 'Credit' : 'Debit',
                'method'      => 'Balance Adjustment',
                'description' => $amount . ' ' . $request->wallet . ' ' . ($request->action === 'add' ? 'added to' : 'deducted from') . ' by Administrator.',
                'status'      => 'Approved',
            ]);
        }

        return back()->with(
            'success',
            "{$request->wallet} wallet adjusted successfully."
        );
    }
}
