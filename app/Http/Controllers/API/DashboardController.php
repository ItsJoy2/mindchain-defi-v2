<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AmbassadorWallet;
use App\Models\AngelWallet;
use App\Models\BannerSetting;
use App\Models\BmindStakingWallets;
use App\Models\BmindTarget;
use App\Models\BmindWallet;
use App\Models\BonusWallet;
use App\Models\CouponWallet;
use App\Models\MindWallet;
use App\Models\MKidsToken;
use App\Models\MusdWallet;
use App\Models\StakingWallet;
use App\Models\TokenRate;
use App\Models\TokenWallet;
use App\Models\TopbarInfo;
use App\Models\usdStaking;
use App\Models\UsdWallet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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

            //  PROFILE
            $profile = [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->user_name,
                'email' => $user->email,
                'email_verified' => $user->is_email_verified,
                'is_ambassador' => $user->ambassador,
                'is_merchant' => $user->merchant_status,
                'is_consultant' => $user->consultant,
                'is_elite_club' => $user->elite_club,
                'is_angel_club' => $user->angel_club,
                'kyc_status' => $user->kyc,
                'created_at' => $user->created_at,
            ];

            //  WALLET SUMMARY
            $wallets = [

                'mind_wallet' => number_format(MindWallet::where('user_id', $userId)->whereIn('status', ['awaiting', 'approve', 'pending'])->sum('amount'),2) . ' MIND',

                'bonus_wallet' => number_format(BonusWallet::where('user_id', $userId)->whereIn('status', ['awaiting', 'approved'])->sum('amount'),2) . ' BONUS',

                'token_wallet' => number_format(TokenWallet::where('user_id', $userId)->sum('amount'),2) . ' MIND',

                'mind_staking' => number_format(StakingWallet::where('user_id', $userId)->sum('amount'),2) . ' STAKE',

                'musd_wallet' => number_format(MusdWallet::where('user_id', $userId)->sum('amount'),2) . ' MUSD',

                'usdt_wallet' => number_format(UsdWallet::where('user_id', $userId)->whereIn('status', ['awaiting', 'approve'])->sum('amount'), 2) . ' USDT',

                'angel_wallet' => number_format(AngelWallet::where('user_id', $userId)->whereIn('status', ['awaiting', 'approve'])->sum('amount'),2) . ' MUSD',

                'angel_membership' => number_format(usdStaking::where('user_id', $userId)->where('method', '!=', 'Angel Membership ')->where('status', 'approve')->sum('amount'),2) . ' USDT',

                'bmind_wallet' => number_format(BmindWallet::where('user_id', $userId)->where('status', 'approved')->sum('amount'),2) . ' BMIND',

                'bmind_staking' => number_format(BmindStakingWallets::where('user_id', $userId)->where('status', 'Approved')->sum('amount'),2) . ' BMIND',

                'mkids_staking' => number_format(MKidsToken::where('user_id', $userId)->where('status', 'approve')->sum('amount'),2) . ' MKIDS',

                'ambassador_wallet' => number_format(AmbassadorWallet::where('user_id', $userId)->sum('amount'), 2) . ' MIND',

                'elite_club' => number_format(abs(UsdWallet::where('user_id', $userId)->where('method', 'Buy Elite Membership')->where('status', 'approve')->sum('amount')),2) . ' USDT',

                // 'coupon_wallet' => number_format(CouponWallet::where('user_id', $userId)->sum('amount'),2) . ' COUPON',
            ];

            //  HISTORY
            $history = [

                'usdt_wallet' => UsdWallet::where('user_id', $userId)
                    ->latest()
                    ->take(10)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'method' => $item->method,
                            'amount' => number_format($item->amount, 2) . ' USDT',
                            'type' => $item->type,
                            'description' => $item->description,
                            'date' => $item->created_at->format('d M Y h:i A'),
                        ];
                    }),

                'mind_wallet' => TokenWallet::where('user_id', $userId)
                    ->latest()
                    ->take(10)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'method' => $item->method,
                            'amount' => number_format($item->amount, 2) . ' MIND',
                            'type' => $item->type,
                            'description' => $item->description,
                            'date' => $item->created_at->format('d M Y h:i A'),
                        ];
                    }),
            ];

            //  TARGET
            $target = BmindTarget::where('user_id', $userId)->first();

            $bmindTarget = [
                'amount' => $target->amount ?? 0,
                'end_date' => $target ? Carbon::parse($target->end_date)->format('Y-m-d') : null,
            ];

            //  SETTINGS
            $settings = TokenRate::first();

            //  UI
            $ui = [
                'banner' => BannerSetting::first(),
                'topbar' => TopbarInfo::first(),
            ];

            //  MARKET
            $market = [
                'price' => 0.41,
                'change' => -4
            ];

            //  FINAL RESPONSE
            return response()->json([
                'status' => true,
                'message' => 'Dashboard data fetched successfully',

                'data' => [
                    'profile' => $profile,
                    'wallets' => $wallets,
                    'history' => $history,
                    'bmind_target' => $bmindTarget,
                    // 'market' => $market,
                    // 'settings' => $settings,
                    // 'ui' => $ui,
                    // 'extra' => [
                    //     'block' => 0
                    // ]
                ]

            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
