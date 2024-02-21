<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Paypal
{
    private $secret;

    private $client_id;

    private $sandbox_url;

    private $live_url;

    private $mode;

    public function __construct()
    {
        $this->secret = config('app.services.paypal.secret');
        $this->client_id = config('app.services.paypal.client_id');
        $this->sandbox_url = config('app.services.paypal.sandbox_url');
        $this->live_url = config('app.services.paypal.live_url');
        $this->mode = config('app.services.paypal.mode');
    }

    public function init()
    {

        $headers = [
            'X-PAYPAL-SECURITY-CONTEXT' => '{"scopes":["https://api-m.paypal.com/v1/subscription/.*","https://uri.paypal.com/services/subscription","openid"]}',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Prefer' => 'return=representation',
            'PayPal-Request-Id' => Str::random(21),
        ];

        $url = $this->mode == 'sandbox' ? $this->sandbox_url : $this->live_url;

        $data = [
            'product_id' => 'PROD-XXCD1234QWER65782',
            'name' => 'Video Streaming Service Plan',
            'description' => 'Video Streaming Service basic plan',
            'status' => 'ACTIVE',
            'billing_cycles' => [
                [
                    'frequency' => [
                        'interval_unit' => 'MONTH',
                        'interval_count' => 1,
                    ],
                    'tenure_type' => 'TRIAL',
                    'sequence' => 1,
                    'total_cycles' => 2,
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => '3',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],

            ],
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'setup_fee_failure_action' => 'CONTINUE',
                'payment_failure_threshold' => 3,
            ],

        ];

         Http::withBasicAuth($this->client_id, $this->secret)->withHeaders($headers)->post($url, $data);

         
    }
}
