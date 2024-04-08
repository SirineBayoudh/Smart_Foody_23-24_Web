<?php

namespace App\Service;

use Twilio\Rest\Client;

class TwilioService
{
    private $client;

    public function __construct(string $accountSid, string $authToken)
    {
        $this->client = new Client($accountSid, $authToken);
    }

    public function sendSMS(string $to, string $message, string $fromPhoneNumber): void
    {
        $this->client->messages->create(
            $to,
            [
                'from' => $fromPhoneNumber,
                'body' => $message,
            ]
        );
    }
}