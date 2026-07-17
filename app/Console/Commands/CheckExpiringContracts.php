<?php

namespace App\Console\Commands;

use App\Models\Contract;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiringContracts extends Command
{
    protected $signature = 'contracts:check-expiring {--days=30 : Number of days before expiry to warn}';
    protected $description = 'Check for contracts expiring within the specified number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $threshold = now()->addDays($days);

        $expiringContracts = Contract::where('status', 'signed')
            ->whereBetween('end_date', [now(), $threshold])
            ->with('customer')
            ->get();

        if ($expiringContracts->isEmpty()) {
            $this->info("لا توجد عقود تنتهي خلال {$days} يوم.");
            return self::SUCCESS;
        }

        $this->warn("⚠️  تم العثور على {$expiringContracts->count()} عقد تنتهي خلال {$days} يوم:");
        $this->newLine();

        $tableData = $expiringContracts->map(fn($c) => [
            $c->contract_number,
            $c->customer->name ?? '—',
            $c->end_date->format('Y-m-d'),
            $c->end_date->diffForHumans(),
            number_format($c->rent_amount) . ' ر.س',
        ])->toArray();

        $this->table(
            ['رقم العقد', 'العميل', 'تاريخ الانتهاء', 'المتبقي', 'الإيجار'],
            $tableData
        );

        Log::channel('daily')->warning('Expiring contracts detected', [
            'count' => $expiringContracts->count(),
            'days'  => $days,
            'contracts' => $expiringContracts->pluck('contract_number')->toArray(),
        ]);

        return self::SUCCESS;
    }
}
