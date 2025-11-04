<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class NotificationService
{
    public static function notify($client, $message)
    {
        // Simulation d'envoi (log)
        Log::info('Notification envoyée à ' . $client->email . ' / ' . $client->telephone . ': ' . $message);
    }

    /**
     * Simule l'envoi d'une notification (email/SMS) après une transaction.
     * @param int|string $clientId
     * @param string $message
     * @param mixed $transaction
     */
    public static function notifyWithTransaction($client, $message, $transaction = null)
    {
        Log::info('[Notification] ' . $client->email . ' / ' . $client->telephone . ': ' . $message, [
            'transaction' => $transaction
        ]);
    }
}
