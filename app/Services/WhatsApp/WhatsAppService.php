<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected string $url = 'https://graph.facebook.com/v17.0/109567455104882/messages';
    protected string $templateUrl = 'https://graph.facebook.com/v17.0/109567455104882/message_templates';

    protected string $token;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token') ?? ''; // حط التوكن في config/services.php
    }

    public function sendTemplateMessage(string $to, string $templateName, array $parameters = [], string $lang = 'ar')
    {
        $payload = [
            "messaging_product" => "whatsapp",
            "recipient_type"    => "individual",
            "to"                => $to,
            "type"              => "template",
            "template"          => [
                "name"     => $templateName,
                "language" => [
                    "code" => $lang
                ],
                "components" => [
                    [
                        "type"       => "body",
                        "parameters" => $parameters
                    ]
                ]
            ]
        ];

        $response = Http::withToken($this->token)
            ->post($this->url, $payload);

        return $response->json();
    }

    public function getTemplates()
    {
        $response = Http::withToken($this->token)
            ->get($this->templateUrl);

        return $response->json();
    }
}
