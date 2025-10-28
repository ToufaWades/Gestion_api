<?php

namespace App\Providers;

use App\Services\EmailMessageService;
use App\Services\TwilioMessageService;
use Illuminate\Support\ServiceProvider;
use App\Services\MessageServiceInterface;
use Twilio\Rest\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the MessageServiceInterface to a concrete implementation.
        // Default: TwilioMessageService (you can change to EmailMessageService or a custom implementation).
        $this->app->bind(MessageServiceInterface::class, function ($app) {
            // Use explicit account SID when possible (AC...), and optionally a Messaging Service SID (MG...)
            $accountSid = config('services.twilio.account_sid') ?: env('TWILIO_ACCOUNT_SID');
            $messagingServiceSid = config('services.twilio.messaging_service_sid') ?: env('TWILIO_MESSAGING_SID');
            // Backwards compat: TWILIO_SID may contain either an AC... or MG... value
            $legacySid = config('services.twilio.sid') ?: env('TWILIO_SID');
            $token = config('services.twilio.token') ?: env('TWILIO_TOKEN');
            $from = config('services.twilio.from') ?: env('TWILIO_FROM', '');

            // If accountSid not provided but legacy contains AC..., use it as accountSid
            if (empty($accountSid) && !empty($legacySid) && str_starts_with($legacySid, 'AC')) {
                $accountSid = $legacySid;
            }

            // If messagingServiceSid not provided but legacy contains MG..., use it
            if (empty($messagingServiceSid) && !empty($legacySid) && str_starts_with($legacySid, 'MG')) {
                $messagingServiceSid = $legacySid;
            }

            if (empty($accountSid) || empty($token)) {
                // Fallback to EmailMessageService if Twilio not configured properly
                return new EmailMessageService();
            }

            try {
                $client = new Client($accountSid, $token);
                return new TwilioMessageService($client, $from, $messagingServiceSid);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Twilio client construction failed: ' . $e->getMessage());
                // Fallback to EmailMessageService
                return new EmailMessageService();
            }
        });

        // Also register EmailMessageService if you want to resolve it directly by class.
        $this->app->singleton(EmailMessageService::class, function ($app) {
            return new EmailMessageService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
