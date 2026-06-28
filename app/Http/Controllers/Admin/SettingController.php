<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AngelSetting;
use App\Models\BmindStakingSetting;
use App\Models\EliteSetting;
use App\Models\EliteV2Setting;
use App\Models\MindStakingSetting;
use App\Models\MkidsStakingSetting;
use App\Models\MusdStakingSetting;
use App\Models\WalletIcon;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'angel'  => AngelSetting::first(),
            'elite'  => EliteSetting::first(),
            'eliteV2'=> EliteV2Setting::first(),
            'mind'   => MindStakingSetting::first(),
            'bmind'  => BmindStakingSetting::first(),
            'musd'   => MusdStakingSetting::first(),
            'mkids'  => MkidsStakingSetting::first(),
        ];

        return view('admin.pages.settings.index', compact('settings'));
    }
    private function updateSetting($model, Request $request)
    {
        $setting = $model::first();

        $setting->update(
            $request->except('_token')
        );

        return back()->with('success','Setting updated successfully.');
    }
    public function updateAngel(Request $request)
    {
        return $this->updateSetting(AngelSetting::class,$request);
    }

    public function updateElite(Request $request)
    {
        return $this->updateSetting(EliteSetting::class,$request);
    }

    public function updateEliteV2(Request $request)
    {
        return $this->updateSetting(EliteV2Setting::class,$request);
    }

    public function updateMind(Request $request)
    {
        return $this->updateSetting(MindStakingSetting::class,$request);
    }

    public function updateBMind(Request $request)
    {
        return $this->updateSetting(BmindStakingSetting::class,$request);
    }

    public function updateMusd(Request $request)
    {
        return $this->updateSetting(MusdStakingSetting::class,$request);
    }

    public function updateMkids(Request $request)
    {
        return $this->updateSetting(MkidsStakingSetting::class,$request);
    }


    //Wallet Icons Index
    public function walletIcons()
    {
        $walletIcons = WalletIcon::orderBy('key')->get();

        return view('admin.pages.settings.wallet-icons', compact('walletIcons'));
    }

    public function updateWalletIcons(Request $request)
    {
        foreach ($request->file('icons', []) as $id => $file) {

            if (!$file) {
                continue;
            }

            $icon = WalletIcon::find($id);

            if (!$icon) {
                continue;
            }

            // Delete old file
            if ($icon->value && file_exists(public_path($icon->value))) {
                unlink(public_path($icon->value));
            }

            $extension = $file->getClientOriginalExtension();

            $filename = strtolower($icon->key).'.'.$extension;

            $file->move(public_path('uploads/wallet-icons'), $filename);

            $icon->update([
                'value' => 'uploads/wallet-icons/'.$filename
            ]);
        }

        return back()->with('success','Wallet icons updated successfully.');
    }
}
