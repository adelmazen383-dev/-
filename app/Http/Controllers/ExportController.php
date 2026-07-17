<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    /**
     * Export contracts as CSV.
     */
    public function contracts(Request $request)
    {
        $query = Contract::with('customer')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $contracts = $query->get();

        $csvData = $this->generateCsv(
            ['رقم العقد', 'العميل', 'رقم الهوية', 'تاريخ البداية', 'تاريخ النهاية', 'الإيجار', 'طريقة الدفع', 'الحالة', 'تاريخ الإنشاء'],
            $contracts->map(fn($c) => [
                $c->contract_number,
                $c->customer->name ?? '—',
                $c->customer->national_id ?? '—',
                $c->start_date?->format('Y-m-d'),
                $c->end_date?->format('Y-m-d'),
                $c->rent_amount,
                $c->payment_method instanceof \App\Enums\PaymentMethod ? $c->payment_method->label() : $c->payment_method,
                $c->status instanceof \App\Enums\ContractStatus ? $c->status->label() : $c->status,
                $c->created_at?->format('Y-m-d H:i'),
            ])->toArray()
        );

        return Response::make($csvData, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="contracts_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Export customers as CSV.
     */
    public function customers()
    {
        $customers = Customer::withCount('contracts')->latest()->get();

        $csvData = $this->generateCsv(
            ['الاسم', 'رقم الهوية', 'الجوال', 'البريد', 'العنوان', 'عدد العقود', 'تاريخ التسجيل'],
            $customers->map(fn($c) => [
                $c->name,
                $c->national_id,
                $c->phone,
                $c->email ?? '—',
                $c->address ?? '—',
                $c->contracts_count,
                $c->created_at?->format('Y-m-d'),
            ])->toArray()
        );

        return Response::make($csvData, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="customers_' . date('Y-m-d') . '.csv"',
        ]);
    }

    private function generateCsv(array $headers, array $rows): string
    {
        $output = chr(0xEF) . chr(0xBB) . chr(0xBF); // UTF-8 BOM for Excel
        $output .= implode(',', $headers) . "\n";

        foreach ($rows as $row) {
            $output .= implode(',', array_map(function ($field) {
                return '"' . str_replace('"', '""', $field ?? '') . '"';
            }, $row)) . "\n";
        }

        return $output;
    }
}
