<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Contract;
use App\Services\WhatsAppService;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $contract;
    public $tries = 3;

    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    public function handle(WhatsAppService $whatsAppService)
    {
        if (in_array($this->contract->status, ['cancelled', 'signed', 'rejected'])) {
            return; // No need to send
        }

        $link = url('/sign/' . $this->contract->verification_token);
        
        $whatsAppService->sendContractLink(
            $this->contract->customer->phone,
            $this->contract->contract_number,
            $link
        );

        if ($this->contract->status == 'draft') {
            $this->contract->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);
        }
    }
}
