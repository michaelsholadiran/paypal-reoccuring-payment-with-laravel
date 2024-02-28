<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

 class Paypal 
{

  private $live_url;

  private $sandbox_url;

  private $secret;

  private $client_id;

  private $mode;

  private $url;

  private $returnUrl;

  private $cancelUrl;

  function __construct() {
    $this->live_url = config('services.paypal.live_url');
    $this->sandbox_url = config('services.paypal.sandbox_url');
    $this->secret = config('services.paypal.secret');
    $this->client_id = config('services.paypal.client_id');
    $this->mode = config('services.paypal.mode');
    $this->url = $this->mode =="sandbox"? $this->sandbox_url:$this->live_url;
    $this->returnUrl = config('services.paypal.return_url');
    $this->cancelUrl = config('services.paypal.cancel_url');
  }

  function createSubscription(string $planId , string $userEmail) : array {
   
    // return [$this->client_id,$this->secret];
    $headers = [
        'Content-Type'=> 'application/json',
        'Accept'=> 'application/json',
        'PayPal-Request-Id'=> Str::random(21),
        'Prefer'=> 'return=representation',
    ];

    //2018-11-01T00:00:00Z
    
    $body = [
        "plan_id" => $planId,
        "subscriber" => [
            "email_address" => $userEmail
        ],
        "application_context" => [
            "brand_name" => config('app.name'),
            "locale" => "en-US",
            "shipping_preference" => "SET_PROVIDED_ADDRESS",
            "user_action" => "SUBSCRIBE_NOW",
            "payment_method" => [
                "payer_selected" => "PAYPAL",
                "payee_preferred" => "IMMEDIATE_PAYMENT_REQUIRED",
            ],
            "return_url" => $this->returnUrl,
            "cancel_url" => $this->cancelUrl,
        ],
    ];

    $response = Http::withBasicAuth($this->client_id, $this->secret)->withHeaders($headers)->post($this->url."/v1/billing/subscriptions",$body)->json();

    return $response;
  } 


  function showSubscriptionDetails(string $subscriptionId) : array {

    $headers = [
        'Content-Type'=> 'application/json',
        'Accept'=> 'application/json',
        'X-PAYPAL-SECURITY-CONTEXT' => '{"scopes":["https://api-m.paypal.com/v1/subscription/.*","https://uri.paypal.com/services/subscription","openid"]}'
    ];

    $response = Http::withBasicAuth($this->client_id, $this->secret)->withHeaders($headers)->get($this->url."/v1/billing/subscriptions/{$subscriptionId}")->json();

    return $response;
  }

  function cancelSubscription(string $subscriptionId) {
    $headers = [
      'Content-Type'=> 'application/json',
      'Accept'=> 'application/json',
      'X-PAYPAL-SECURITY-CONTEXT' => '{"scopes":["https://api-m.paypal.com/v1/subscription/.*","https://uri.paypal.com/services/subscription","openid"]}'
    ];

    $body = [
      "reason" => "Done with with the service, and totally satisfied" // this could be dynamic
    ];

    $response = Http::withBasicAuth($this->client_id, $this->secret)->withHeaders($headers)->post($this->url."/v1/billing/subscriptions/{$subscriptionId}/cancel",$body)->json();

    return $response;
  }
}