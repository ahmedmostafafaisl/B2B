<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TaqnyatSmsService
{
    protected $client;
    protected $apiKey;
    protected $senderName;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('TAQNYAT_SMS_API_KEY') ?? '1dc224c44e3a2950d88cdaafa9dbc9e3';
        $this->senderName = env('TAQNYAT_SMS_SENDER') ?? 'NAQI';
    }

    public function sendPaymentLink($phone, $link)
    {
        $url = 'https://api.taqnyat.sa/v1/messages';

        $data = [
            'recipients' => [$phone],
            'body' => "Your Payment link is: $link",
            'sender' => $this->senderName,
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            return [
                'success' => false,
                'message' => 'Failed to send OTP',
                'error'   => $e->getMessage(),
            ];
        }
    }
    public function sendOtp($phone, $otp)
    {
        $url = 'https://api.taqnyat.sa/v1/messages';

        $data = [
            'recipients' => [$phone],
            'body' => "Your OTP code is: $otp",
            'sender' => $this->senderName,
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            return [
                'success' => false,
                'message' => 'Failed to send OTP',
                'error'   => $e->getMessage(),
            ];
        }
    }
    // send pdf link
    public function sendPdfLink($phone, $link)
    {
        $url = 'https://api.taqnyat.sa/v1/messages';

        $data = [
            'recipients' => [$phone],
            'body' => "Your invoice link is: $link",
            'sender' => $this->senderName,
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            return [
                'success' => false,
                'message' => 'Failed to send invoice link',
                'error'   => $e->getMessage(),
            ];
        }
    }
}
