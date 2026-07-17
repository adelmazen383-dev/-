<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Customer;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        // Cache dashboard stats for 5 minutes
        $stats = Cache::remember('dashboard_stats', 300, function () {
            $contractStats = Contract::query()->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'signed' THEN 1 ELSE 0 END) as signed,
                SUM(CASE WHEN status IN ('draft','sent','viewed') THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'signed' THEN rent_amount ELSE 0 END) as total_rent_signed,
                SUM(rent_amount) as total_rent_all
            ")->first();

            return [
                'totalContracts'   => $contractStats->total,
                'signedContracts'  => $contractStats->signed,
                'pendingContracts' => $contractStats->pending,
                'rejectedContracts'=> $contractStats->rejected,
                'cancelledContracts'=> $contractStats->cancelled,
                'totalRentSigned'  => $contractStats->total_rent_signed ?? 0,
                'totalRentAll'     => $contractStats->total_rent_all ?? 0,
                'totalCustomers'   => Customer::count(),
            ];
        });

        // Expiring contracts (within 30 days) - always fresh
        $expiringContracts = Contract::where('status', 'signed')
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->with('customer')
            ->orderBy('end_date')
            ->get();

        // Recent contracts - always fresh
        $recentContracts = Contract::with('customer')->latest()->take(5)->get();

        // Monthly chart data (last 6 months)
        $monthlyData = $this->getMonthlyChartData();

        return view('dashboard', array_merge($stats, [
            'recentContracts'   => $recentContracts,
            'expiringContracts' => $expiringContracts,
            'monthlyData'       => $monthlyData,
        ]));
    }

    private function getMonthlyChartData(): array
    {
        $months = [];
        $signed = [];
        $created = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->translatedFormat('M Y');

            $signed[] = Contract::where('status', 'signed')
                ->whereYear('signed_at', $date->year)
                ->whereMonth('signed_at', $date->month)
                ->count();

            $created[] = Contract::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        return [
            'labels'  => $months,
            'signed'  => $signed,
            'created' => $created,
        ];
    }
}
