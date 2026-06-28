<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AngelStaking;
use Illuminate\Http\Request;

class InvestmentHistoryController extends Controller
{
    public function angelStaking(Request $request)
    {
        $investments = AngelStaking::with('user')->latest()->paginate(20);

        return view('admin.pages.investment-history.angel-stakings', compact('investments'));
    }
}
