<?php

namespace App\Services;

interface MessageServiceInterface
{
    /**
     * Send a message to the recipient.
     *
     * @param string $to Destination (phone number or email)
     * @param string $message Plain text message
     * @return bool true on success, false otherwise
     */
    public function sendMessage(string $to, string $message): bool;
}
