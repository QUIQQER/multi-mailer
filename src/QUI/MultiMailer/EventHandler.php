<?php

namespace QUI\MultiMailer;

use PHPMailer\PHPMailer\PHPMailer;
use QUI\Mail\Queue;
use QUI\Mail\Log;
use Throwable;

class EventHandler
{
    public static function onGetPhpMailerInitStart(): ?PHPMailer
    {
        return Mailer::getRandomPHPMailer();
    }

    public static function onMailQueueSendError(
        Queue $MailQueue,
        ?PHPMailer $PhpMailer,
        Throwable $Exception
    ): bool {
        if (!$PhpMailer) {
            return false;
        }

        $message = $Exception->getMessage();

        if (str_contains($message, 'Recipient address rejected:')) {
            Log::write($Exception->getMessage());
            return false;
        }

        return Mailer::retryWithNextServer($PhpMailer);
    }

    public static function onMailSendError(
        \QUI\Mail\Mailer $Mailer,
        PHPMailer $PhpMailer,
        Throwable $Exception
    ): bool {
        $message = $Exception->getMessage();

        if (str_contains($message, 'Recipient address rejected:')) {
            Log::write($Exception->getMessage());
            return false;
        }

        return Mailer::retryWithNextServer($PhpMailer);
    }
}
