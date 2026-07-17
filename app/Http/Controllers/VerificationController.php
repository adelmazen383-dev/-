<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $contract = null;
        if ($request->filled('contract_number')) {
            $contract = Contract::where('contract_number', $request->contract_number)
                                ->with('customer')
                                ->first();
        }

        return view('contracts.verify', compact('contract'));
    }
}
