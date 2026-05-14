<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PurchaseStaking;
use Illuminate\Http\Request;

class StakingHistoryController extends Controller
{
    public function index(Request $request)
    {
        try {

            $user = auth()->user();


            $perPage = (int) $request->get('per_page', 10);
            $wallet  = $request->get('wallet');

            $query = PurchaseStaking::where('user_id', $user->id);

            if ($wallet) {
                $query->where('wallet', $wallet);
            }

            $histories = $query->select([
                    'id',
                    'wallet',
                    'amount',
                    'duration',
                    'received_days',
                    'daily',
                    'status',
                    'created_at'
                ])
                ->orderByDesc('id')
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'Staking history retrieved successfully',
                'data' => collect($histories->items())->map(function ($row) {

                    return [
                        'id'             => $row->id,
                        'wallet'         => $row->wallet,
                        'amount'         => (float) $row->amount,
                        'duration'       => (int) $row->duration,
                        'received_days'  => (int) $row->received_days,
                        'daily'          => (float) $row->daily,
                        'status'         => $row->status == 1 ? 'Running' : 'Expired',
                        'created_at'     => $row->created_at->format('d M Y'),
                    ];
                }),

                'pagination' => [
                    'page'     => $histories->currentPage(),
                    'per_page' => $histories->perPage(),
                    'total'    => $histories->total(),
                ]

            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
