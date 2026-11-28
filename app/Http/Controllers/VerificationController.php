<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ProviderContract;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $contract = null;
        $lesseeName = null;

        if ($request->filled('contract_number')) {
            $number = trim((string) $request->contract_number);

            $contract = Contract::where('contract_number', $number)
                ->with('customer')
                ->first();

            if ($contract) {
                $lesseeName = $contract->customer->name ?? '—';
            } else {
                $providerContract = ProviderContract::where('contract_number', $number)->first();
                if ($providerContract) {
                    $contract = $providerContract;
                    $lesseeName = $providerContract->lessee_name ?: '—';
                }
            }
        }

        return view('contracts.verify', compact('contract', 'lesseeName'));
    }
}
