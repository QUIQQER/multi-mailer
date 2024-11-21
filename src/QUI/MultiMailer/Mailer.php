<?php

namespace QUI\MultiMailer;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use QUI;

use QUI\Mail\Log;

use function explode;
use function rtrim;

class Mailer
{
    public static function getList(): array
    {
        try {
            $Config = QUI::getPackage('quiqqer/multi-mailer')->getConfig();
        } catch (QUI\Exception $exception) {
            QUI\System\Log::addError($exception->getMessage());
            return [];
        }

        $config = $Config->toArray();
        $servers = [];

        $needles = [
            'MAILFrom',
            'MAILFromText',
            'MAILReplyTo',
            'server',
            'port',
            'auth',
            'username',
            'password',
            'security',
            'debug',
            'secureSSL_verify_peer',
            'secureSSL_verify_peer_name',
            'secureSSL_allow_self_signed'
        ];

        foreach ($config as $section => $params) {
            if (!str_starts_with($section, 'server-')) {
                continue;
            }

            foreach ($needles as $needle) {
                if (!isset($params[$needle])) {
                    $params[$needle] = '';
                }
            }

            $servers[] = $params;
        }

        return $servers;
    }

    /**
     * @throws Exception
     */
    public static function parseMailServerDataToPhpMailer(array $serverData = []): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';

        /**
         * These options are set regardless of the "SMTPSecure" setting
         * because PHPMailer may try to establish a secure connection if the mail
         * server supports it regardless of the "SMTPSecure" setting.
         */
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => (int)$serverData['secureSSL_verify_peer'] ?? 0,
                'verify_peer_name' => (int)$serverData['secureSSL_verify_peer_name'] ?? 0,
                'allow_self_signed' => (int)$serverData['secureSSL_allow_self_signed'] ?? 0
            ]
        ];

        if (!empty($serverData['server'])) {
            $mail->Host = $serverData['server'];
        }

        if (!empty($serverData['port'])) {
            $mail->Port = $serverData['port'];
        }

        if (!empty($serverData['auth'])) {
            $mail->SMTPAuth = true;

            if (!empty($serverData['username'])) {
                $mail->Username = $serverData['username'];
            }

            if (!empty($serverData['password'])) {
                $mail->Username = $serverData['password'];
            }
        }

        if (!empty($serverData['security'])) {
            switch ($serverData['security']) {
                case 'ssl':
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    break;

                case 'tls':
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    break;
            }
        }

        if (!empty($serverData['MAILFrom'])) {
            if (!empty($serverData['MAILFromText'])) {
                $mail->setFrom($serverData['MAILFrom'], $serverData['MAILFromText']);
            } else {
                $mail->setFrom($serverData['MAILFrom']);
            }
        }

        if (!empty($serverData['MAILReplyTo'])) {
            $mail->addReplyTo($serverData['MAILReplyTo']);
        }

        if (!empty($config['debug'])) {
            $mail->SMTPDebug = (int)$serverData['debug'];

            $mail->Debugoutput = static function ($str, $level): void {
                Log::write(rtrim($str));
            };
        }

        return $mail;
    }

    public static function getRandomPHPMailer(): ?PHPMailer
    {
        $servers = self::getList();

        if (empty($servers)) {
            return null;
        }

        $rand = rand(0, count($servers) - 1);

        try {
            return self::parseMailServerDataToPhpMailer($servers[$rand]);
        } catch (\Exception $exception) {
            QUI\System\Log::addError($exception->getMessage());
        }

        return null;
    }
}
