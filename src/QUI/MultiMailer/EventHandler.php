<?php

namespace QUI\MultiMailer;

use PHPMailer\PHPMailer\PHPMailer;

class EventHandler
{
    public static function onGetPhpMailerInitStart(): PHPMailer
    {
        return Mailer::getRandomPHPMailer();
    }
}
