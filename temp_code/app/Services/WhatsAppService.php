<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $token;

    public function __construct()
    {
        // WhatsApp Cloud API details
        $this->apiUrl = env('WHATSAPP_API_URL', 'https://graph.facebook.com/v17.0/YOUR_PHONE_NUMBER_ID/messages');
        $this->token = env('WHATSAPP_TOKEN');
    }

    public function sendContractLink(string $phone, string $contractNumber, string $link)
    {
        // Clean phone number (remove spaces, plus, etc.)
        $phone = preg_replace('/[^0-9]/', '', $phone);

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => 'contract_signature', // Ensure this template is approved in Meta Business
                'language' => ['code' => 'ar'],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $contractNumber],
                            ['type' => 'text', 'text' => $link],
                        ]
                    ]
                ]
            ]
        ];

        $response = Http::withToken($this->token)->post($this->apiUrl, $payload);

        if (!$response->successful()) {
            Log::error("WhatsApp send failed to {$phone}: " . $response->body());
            throw new \Exception("فشل إرسال رسالة الواتساب: " . $response->json('error.message', 'Unknown error'));
        }

        return $response->json();
    }
}
