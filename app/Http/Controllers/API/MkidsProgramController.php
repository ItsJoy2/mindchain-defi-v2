<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\MkidsStakingSetting;
use App\Models\MkidsStakingProgram;
use App\Models\MkidsPurchaseHistory;
use App\Models\Transaction;
use App\Models\User;

use App\Services\WalletService;

class MkidsProgramController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index()
    {
        try {

            $user = Auth::user();

            $setting = MkidsStakingSetting::where('status', 1)->first();

            if (!$setting) {

                return response()->json([
                    'status' => false,
                    'message' => 'MKIDS staking settings not found'
                ], 404);
            }

            $usdtBalance = $this->walletService->getBalance($user->id, 'USDT');

            $musdBalance = $this->walletService->getBalance($user->id, 'MUSD');


            return response()->json([
                'status' => true,
                'message' => 'MKIDS staking data fetched successfully',

                'data' => [

                    'staking_amount' => number_format($setting->amount, 2),

                    'token_bonus' => number_format($setting->token_bonus, 2),

                    'wallet_balance' => [

                        'USDT' => number_format($usdtBalance, 2),

                        'MUSD' => number_format($musdBalance, 2),
                    ]
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function joinProgram(Request $request)
    {
        DB::beginTransaction();

        try {

            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'kids_name'        => 'required|string|max:255',
                'kids_username'    => 'required|string|max:255|unique:mkids_staking_programs,kids_username',
                'kids_father_name' => 'required|string|max:255',
                'kids_mother_name' => 'required|string|max:255',
                'dob'              => 'required|date',
                'age'              => 'required|integer',
                'kids_birth_place' => 'required|string|max:255',
                'country'          => 'required|string|max:255',
                'wallet'           => 'required|in:USDT,MUSD',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $setting = MkidsStakingSetting::where('status', 1)->first();

            if (!$setting) {
                return response()->json([
                    'status' => false,
                    'message' => 'MKIDS staking settings not found'
                ], 404);
            }

            if (!$this->walletService->hasBalance($user->id, $request->wallet, $setting->amount)) {

                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient balance in ' . $request->wallet
                ], 400);
            }

            $program = MkidsStakingProgram::create([
                'user_id'            => $user->id,
                'kids_name'          => $request->kids_name,
                'kids_username'      => $request->kids_username,
                'kids_father_name'   => $request->kids_father_name,
                'kids_mother_name'   => $request->kids_mother_name,
                'dob'                => $request->dob,
                'age'                => $request->age,
                'kids_birth_place'   => $request->kids_birth_place,
                'country'            => $request->country,
                'count'              => 1,
            ]);

            Transaction::create([
                'user_id'     => $user->id,
                'amount'      => -$setting->amount,
                'wallet'      => $request->wallet,
                'type'        => 'Debit',
                'method'      => 'MKIDS Program Membership',
                'status'      => 'Approved',
                'description' => -$setting->amount .'MKIDS Program Membership amount deducted',
            ]);

            MkidsPurchaseHistory::create([
                'user_id'     => $user->id,
                'amount'      => $setting->token_bonus,
                'type'        => 'Credit',
                'method'      => 'MKIDS Program Membership',
                'status'      => 'Approved',
                'description' => 'MKIDS Program Membership Token bonus credited',
            ]);

            $levels = [
                1 => $setting->level_1_bonus,
                2 => $setting->level_2_bonus,
                3 => $setting->level_3_bonus,
            ];

            $currentUser = $user->fresh();

            for ($level = 1; $level <= 3; $level++) {

                // sponsor id safe check
                if (!$currentUser->sponsor) {
                    break;
                }

                $sponsor = User::where('id', $currentUser->sponsor)->first();

                if (!$sponsor) {
                    break;
                }

                // check sponsor has program
                $hasProgram = MkidsStakingProgram::where('user_id', $sponsor->id)->exists();

                if ($hasProgram) {

                    $percent = match ($level) {
                        1 => $setting->level_1_bonus,
                        2 => $setting->level_2_bonus,
                        3 => $setting->level_3_bonus,
                    };

                    $amount = ($setting->amount * $percent) / 100;

                    Transaction::create([
                        'user_id'     => $sponsor->id,
                        'receiver_id' => $user->id,
                        'amount'      => $amount,
                        'wallet'      => $request->wallet,
                        'type'        => 'Credit',
                        'method'      => "Level {$level} Bonus",
                        'status'      => 'Approved',
                        'description' => "Level {$level} bonus from {$user->user_name} joining MKIDS program",
                    ]);
                }

                // move up the tree
                $currentUser = $sponsor;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'MKIDS program joined successfully',
                'data' => [
                    'id'                => $program->id,
                    'user_id'           => $program->user_id,
                    'kids_name'         => $program->kids_name,
                    'kids_username'     => $program->kids_username,
                    'kids_father_name'  => $program->kids_father_name,
                    'kids_mother_name'  => $program->kids_mother_name,
                    'dob'               => date('Y-m-d', strtotime($program->dob)),
                    'age'               => $program->age,
                    'kids_birth_place'  => $program->kids_birth_place,
                    'country'           => $program->country,
                    'count'             => $program->count,
                    'token_bonus'       => number_format($setting->token_bonus, 2),
                ]
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function rejoinProgram(Request $request)
    {
        DB::beginTransaction();

        try {

            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'program_id' => 'required|exists:mkids_staking_programs,id',
                'wallet'    => 'required|in:USDT,MUSD',
            ]);

            if ($validator->fails()) {

                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $program = MkidsStakingProgram::where('id', $request->program_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$program) {

                return response()->json([
                    'status' => false,
                    'message' => 'Program not found'
                ], 404);
            }

            $setting = MkidsStakingSetting::where('status', 1)->first();

            if (!$setting) {

                return response()->json([
                    'status' => false,
                    'message' => 'MKIDS staking settings not found'
                ], 404);
            }

            if (
                !$this->walletService->hasBalance(
                    $user->id,
                    $request->wallet,
                    $setting->amount
                )
            ) {

                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient balance in ' . $request->wallet
                ], 400);
            }

            $program->increment('count');

            Transaction::create([
                'user_id'     => $user->id,
                'amount'      => -$setting->amount,
                'wallet'      => $request->wallet,
                'type'        => 'Debit',
                'method'      => 'MKIDS Program Membership',
                'status'      => 'Approved',
                'description' => -$setting->amount . ' MKIDS Program Membership amount deducted',
            ]);

            MkidsPurchaseHistory::create([
                'user_id'     => $user->id,
                'amount'      => $setting->token_bonus,
                'type'        => 'Credit',
                'method'      => 'MKIDS Program Membership',
                'status'      => 'Approved',
                'description' => 'MKIDS Program Membership Token bonus credited',
            ]);

            $currentUser = $user->fresh();

            for ($level = 1; $level <= 3; $level++) {

                if (!$currentUser->sponsor) {
                    break;
                }

                $sponsor = User::where('id', $currentUser->sponsor)->first();

                if (!$sponsor) {
                    break;
                }

                $hasProgram = MkidsStakingProgram::where('user_id', $sponsor->id)
                    ->exists();

                if ($hasProgram) {

                    $percent = match ($level) {
                        1 => $setting->level_1_bonus,
                        2 => $setting->level_2_bonus,
                        3 => $setting->level_3_bonus,
                    };

                    $bonusAmount = ($setting->amount * $percent) / 100;

                    Transaction::create([
                        'user_id'     => $sponsor->id,
                        'receiver_id' => $user->id,
                        'amount'      => $bonusAmount,
                        'wallet'      => $request->wallet,
                        'type'        => 'Credit',
                        'method'      => "Level {$level} Bonus",
                        'status'      => 'Approved',
                        'description' => "Level {$level} bonus from {$user->user_name} joining MKIDS program",
                    ]);
                }

                $currentUser = $sponsor;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'MKIDS program rejoined successfully',
                'data' => [
                    'id'                => $program->id,
                    'user_id'           => $program->user_id,
                    'kids_name'         => $program->kids_name,
                    'kids_username'     => $program->kids_username,
                    'kids_father_name'  => $program->kids_father_name,
                    'kids_mother_name'  => $program->kids_mother_name,
                    'dob'               => date('Y-m-d', strtotime($program->dob)),
                    'age'               => $program->age,
                    'kids_birth_place'  => $program->kids_birth_place,
                    'country'           => $program->country,
                    'count'             => $program->count,
                    'token_bonus'       => number_format($setting->token_bonus, 2),
                ]
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function kidsHistory()
    {
        try {

            $user = Auth::user();

            $programs = MkidsStakingProgram::where('user_id', $user->id)
                ->latest()
                ->get();

            $data = $programs->map(function ($program) {

                return [

                    'id'                => $program->id,
                    'kids_name'         => $program->kids_name,
                    'kids_username'     => $program->kids_username,
                    'kids_father_name'  => $program->kids_father_name,
                    'kids_mother_name'  => $program->kids_mother_name,
                    'dob'               => date('Y-m-d', strtotime($program->dob)),
                    'age'               => $program->age,
                    'kids_birth_place'  => $program->kids_birth_place,
                    'country'           => $program->country,
                    'rejoin_count'      => $program->count,
                    'updated_at'        => $program->updated_at->format('Y-m-d h:i A'),
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'MKIDS history fetched successfully',
                'data' => $data
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
