<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class LogMailSendingListener
{
    /**
     * Handle MessageSending event.
     */
    public function handleSending(MessageSending $event): void
    {
        $recipients = implode(', ', array_map(fn($addr) => $addr->getAddress(), $event->message->getTo()));
        $subject = $event->message->getSubject() ?? '(No Subject)';
        $host = config('mail.mailers.smtp.host', 'smtp.gmail.com');
        $port = config('mail.mailers.smtp.port', 587);

        $logMsg = "\n====================================================================\n"
            . "[SMTP MAIL LOG] SENDING EMAIL VIA SMTP ({$host}:{$port})\n"
            . "--------------------------------------------------------------------\n"
            . "  Recipient Email : {$recipients}\n"
            . "  Subject         : {$subject}\n"
            . "  Sending Status  : SENDING...\n"
            . "====================================================================\n";

        Log::info("SMTP Sending: Recipient={$recipients}, Subject={$subject}");

        if (php_sapi_name() === 'cli' || defined('STDERR')) {
            @file_put_contents('php://stdout', $logMsg);
        }
    }

    /**
     * Handle MessageSent event.
     */
    public function handleSent(MessageSent $event): void
    {
        $recipients = implode(', ', array_map(fn($addr) => $addr->getAddress(), $event->message->getTo()));
        $subject = $event->message->getSubject() ?? '(No Subject)';

        $logMsg = "--------------------------------------------------------------------\n"
            . "[SMTP MAIL LOG] EMAIL SENT SUCCESSFULLY!\n"
            . "  Recipient Email : {$recipients}\n"
            . "  Subject         : {$subject}\n"
            . "  Sending Status  : SUCCESS\n"
            . "  Message Details : Email delivered successfully to SMTP transport.\n"
            . "====================================================================\n\n";

        Log::info("SMTP Sent Success: Recipient={$recipients}, Subject={$subject}");

        if (php_sapi_name() === 'cli' || defined('STDERR')) {
            @file_put_contents('php://stdout', $logMsg);
        }
    }
}
