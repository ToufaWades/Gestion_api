<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use App\Services\MessageServiceInterface;

class TwilioMessageService implements MessageServiceInterface
{
    /**
     * @param Client $client Twilio REST client (constructed with account SID and token)
     * @param string $from Default from phone number (optional)
     * @param string|null $messagingServiceSid Optional MessagingService SID (MG...)
     */
    public function __construct(private \Twilio\Rest\Client $client, private string $from, private ?string $messagingServiceSid = null)
    {}

    /**
     * {@inheritdoc}
     */
    public function sendMessage(string $to, string $message): bool
    {
        try {
            $params = ['body' => $message];

            // If a Messaging Service SID was provided prefer it (MG...)
            if (!empty($this->messagingServiceSid)) {
                $params['messagingServiceSid'] = $this->messagingServiceSid;
            } else {
                $params['from'] = $this->from;
            }

            $resp = $this->client->messages->create($to, $params);
            \Illuminate\Support\Facades\Log::info('Twilio message sent', ['sid' => $resp->sid ?? null, 'to' => $to]);

            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Twilio send error: ' . $e->getMessage());
            return false;
        }
    }
}
