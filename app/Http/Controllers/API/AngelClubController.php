<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\AngelSetting;
use App\Models\AngelStaking;
use App\Models\Transaction;
use App\Services\WalletService;


class AngelClubController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }
    public function index()
    {
        try {

            $angel = AngelSetting::first();

            return response()->json([
                'status' => true,
                'message' => 'Angel settings fetched successfully',

                'data' => [

                    'membership_fee' => number_format($angel->membership_fee ?? 0, 2),
                    'apy'           => number_format($angel->apy ?? 0, 0),
                    'daily_bonus'   => isset($angel->membership_fee, $angel->apy)? number_format((($angel->membership_fee * $angel->apy / 100) / 365), 2) : number_format(0, 2),


                    // 'angel' => [
                    //     'membership_fee' => $angel->membership_fee ?? 0,
                    //     'total_member'  => $angel->total_member ?? 0,
                    //     'duration'      => $angel->duration ?? 0,
                    //     'apy'           => $angel->apy ?? 0,
                    //     'daily_bonus'   => isset($angel->membership_fee, $angel->apy)
                    //         ? (($angel->membership_fee * $angel->apy / 100) / 365)
                    //         : 0,
                    //     'level_1_bonus' => $angel->level_1_bonus ?? 0,
                    //     'level_2_bonus' => $angel->level_2_bonus ?? 0,
                    //     'level_3_bonus' => $angel->level_3_bonus ?? 0,
                    //     'status'        => $angel->status ?? 0,
                    // ]
                ]

            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function joinAngel(Request $request)
    {
        try {

            $user = Auth::user();

            // Already joined check
            if ($user->angel_club == 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Already joined Angel Club'
                ], 400);
            }

            // Get settings
            $setting = AngelSetting::where('status', 1)->first();

            if (!$setting) {
                return response()->json([
                    'status' => false,
                    'message' => 'Angel settings not found'
                ], 400);
            }

            // Total member limit check
            // $totalJoined = User::where('angel_club', 1)->count();

            // if ($totalJoined >= $setting->total_member) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Angel Club limit reached'
            //     ], 400);
            // }

            $fee = $setting->membership_fee;

            // Wallet balance check (MUSD)
            if (!$this->walletService->hasBalance($user->id, 'MUSD', $fee)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient MUSD balance'
                ], 402);
            }

            DB::beginTransaction();

            // Deduct MUSD
            Transaction::create([
                'user_id' => $user->id,
                'amount' => -$fee,
                'wallet' => 'MUSD',
                'type' => 'Debit',
                'method' => 'Angel Membership',
                'status' => 'Approved',
                'description' => 'Angel membership purchase'
            ]);

            $dailyBonus = $fee * ($setting->apy / 100)/365;
            // Angel staking create
            AngelStaking::create([
                'user_id' => $user->id,
                'amount' => $fee,
                'duration' => $setting->duration,
                'daily_bonus' => $dailyBonus,
                'received_days' => 0,
                'status' => 1
            ]);

            // Update user status
            $user->angel_club = 1;
            $user->save();

            // Referral bonus distribution
            $sponsor = $user->sponsor;

            if ($sponsor) {

                $level1 = $setting->level_1_bonus ?? 0;
                $level2 = $setting->level_2_bonus ?? 0;
                $level3 = $setting->level_3_bonus ?? 0;

                // Level 1
                Transaction::create([
                    'user_id' => $sponsor->id,
                    'amount' => $fee * ($level1 / 100),
                    'wallet' => 'MUSD',
                    'type' => 'Credit',
                    'method' => 'Angel Level 1 Bonus',
                    'status' => 'Approved',
                    'description' => 'Level 1 bonus from ' . $user->user_name
                ]);

                // Level 2
                if ($sponsor->sponsor) {

                    $lvl2 = $sponsor->sponsor;

                    Transaction::create([
                        'user_id' => $lvl2->id,
                        'amount' => $fee * ($level2 / 100),
                        'wallet' => 'MUSD',
                        'type' => 'Credit',
                        'method' => 'Angel Level 2 Bonus',
                        'status' => 'Approved',
                        'description' => 'Level 2 bonus from ' . $user->user_name
                    ]);

                    // Level 3
                    if ($lvl2->sponsor) {

                        $lvl3 = $lvl2->sponsor;

                        Transaction::create([
                            'user_id' => $lvl3->id,
                            'amount' => $fee * ($level3 / 100),
                            'wallet' => 'MUSD',
                            'type' => 'Credit',
                            'method' => 'Angel Level 3 Bonus',
                            'status' => 'Approved',
                            'description' => 'Level 3 bonus from ' . $user->user_name
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Successfully joined Angel Club'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
