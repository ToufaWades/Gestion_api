<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\MessageServiceInterface;

class EmailMessageService implements MessageServiceInterface
{
    /**
     * Send a plain email using Laravel's Mail facade.
     *
     * This is intentionally simple (Mail::raw). For production, use Mailable classes.
     */
    public function sendMessage(string $to, string $message): bool
    {
        try {
            Mail::raw($message, function ($m) use ($to) {
                $m->to($to)->subject('Notification');
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Email send error: ' . $e->getMessage());
            return false;
        }
    }
}
