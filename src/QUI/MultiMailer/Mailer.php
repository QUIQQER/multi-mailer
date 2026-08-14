<?php

namespace QUI\MultiMailer;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use QUI;
use QUI\Mail\Log;

use function rtrim;

class Mailer
{
    /**
     * @return array<string, string|int>
     */
    public static function getMainMailerConfig(): array
    {
        $config = QUI::conf('mail');

        $server = [
            'MAILFrom' => (string)$config['MAILFrom'],
            'MAILFromText' => (string)$config['MAILFromText'],
            'MAILReplyTo' => '',
            'server' => '',
            'port' => '',
            'auth' => '',
            'username' => '',
            'password' => '',
            'security' => '',
            'debug' => '',
            'secureSSL_verify_peer' => '',
            'secureSSL_verify_peer_name' => '',
            'secureSSL_allow_self_signed' => '',
            'isFallbackServer' => 0
        ];

        if (isset($config['SMTP']) && $config['SMTP']) {
            $server['server'] = (string)$config['SMTPServer'];
            $server['auth'] = (int)$config['SMTPAuth'];
            $server['username'] = (string)$config['SMTPUser'];
            $server['password'] = (string)$config['SMTPPass'];

            if (!empty($config['SMTPPort'])) {
                $server['port'] = (int)$config['SMTPPort'];
            }

            if (!empty($config['SMTPDebug'])) {
                $server['debug'] = (int)$config['SMTPDebug'];
            }

            if (isset($config['SMTPSecure'])) {
                switch ($config['SMTPSecure']) {
                    case "tls":
                    case "ssl":
                        $server['security'] = (string)$config['SMTPSecure'];
                        break;
                }
            }

            $server['secureSSL_verify_peer'] = (int)$config['SMTPSecureSSL_verify_peer'];
            $server['secureSSL_verify_peer_name'] = (int)$config['SMTPSecureSSL_verify_peer_name'];
            $server['secureSSL_allow_self_signed'] = (int)$config['SMTPSecureSSL_allow_self_signed'];
        }

        return $server;
    }

    /**
     * @return array<int, array<string, string|int>>
     */
    public static function getList(bool $withFallbackServer = false): array
    {
        try {
            $Config = QUI::getPackage('quiqqer/multi-mailer')->getConfig();
        } catch (QUI\Exception $exception) {
            QUI\System\Log::addError($exception->getMessage());
            return [];
        }

        $config = $Config?->toArray() ?? [];
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
            'secureSSL_allow_self_signed',
            'isFallbackServer'
        ];

        foreach ($config as $section => $params) {
            if (!str_starts_with($section, 'server-')) {
                continue;
            }

            if (
                $withFallbackServer === false
                && isset($params['isFallbackServer'])
                && $params['isFallbackServer'] == 1
            ) {
                continue;
            }

            foreach ($needles as $needle) {
                if (!isset($params[$needle])) {
                    $params[$needle] = '';
                }

                if ($needle === 'isFallbackServer' && empty($params[$needle])) {
                    $params[$needle] = 0;
                }
            }

            $servers[] = $params;
        }

        return $servers;
    }

    /**
     * @return array<int, array<string, string|int>>
     */
    public static function getFallBackServerList(): array
    {
        $servers = self::getList(true);
        $servers = array_filter($servers, function ($server) {
            return $server['isFallbackServer'] == 1;
        });

        return $servers;
    }

    /**
     * @param array<string, string|int> $serverData
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
                'verify_peer' => (int)$serverData['secureSSL_verify_peer'],
                'verify_peer_name' => (int)$serverData['secureSSL_verify_peer_name'],
                'allow_self_signed' => (int)$serverData['secureSSL_allow_self_signed']
            ]
        ];

        if (!empty($serverData['server'])) {
            $mail->Host = (string)$serverData['server'];
        }

        if (!empty($serverData['port'])) {
            $mail->Port = (int)$serverData['port'];
        }

        if (!empty($serverData['auth'])) {
            $mail->SMTPAuth = true;

            if (!empty($serverData['username'])) {
                $mail->Username = (string)$serverData['username'];
            }

            if (!empty($serverData['password'])) {
                $mail->Password = (string)$serverData['password'];
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
                $mail->setFrom(
                    (string)$serverData['MAILFrom'],
                    (string)$serverData['MAILFromText']
                );
            } else {
                $mail->setFrom((string)$serverData['MAILFrom']);
            }
        }

        if (!empty($serverData['MAILReplyTo'])) {
            $mail->addReplyTo((string)$serverData['MAILReplyTo']);
        }

        if (!$mail->SMTPAuth) {
            $mail->SMTPSecure = '';
        }

        if (!empty($serverData['debug'])) {
            $mail->SMTPDebug = (int)$serverData['debug'];

            $mail->Debugoutput = static function ($str, $level): void {
                Log::write(rtrim($str));
            };
        }

        return $mail;
    }

    //region PHPMailer

    public static function getRandomPHPMailer(
        bool $includeFallbackServer = false,
        ?PHPMailer $ExcludedMailer = null
    ): ?PHPMailer {
        $servers = self::getList($includeFallbackServer);
        $servers[] = self::getMainMailerConfig();

        if ($ExcludedMailer) {
            $servers = array_values(array_filter(
                $servers,
                static fn(array $server): bool => !self::isSameServer($server, $ExcludedMailer)
            ));
        }

        if ($servers === []) {
            return null;
        }

        $server = $servers[array_rand($servers)];

        try {
            return self::parseMailServerDataToPhpMailer($server);
        } catch (\Exception $exception) {
            Log::write($exception->getMessage());
        }

        return null;
    }

    /**
     * Returns only fallback servers
     *
     * @return PHPMailer|null
     */
    public static function getRandomFallbackPHPMailer(): ?PHPMailer
    {
        $servers = array_values(self::getFallBackServerList());

        if ($servers === []) {
            Log::write('multi-mailer error: no fallback server configured');
            return null;
        }

        $server = $servers[array_rand($servers)];

        try {
            return self::parseMailServerDataToPhpMailer($server);
        } catch (\Throwable $exception) {
            Log::write($exception->getMessage());
        }

        return null;
    }

    //endregion

    public static function retryWithNextServer(PHPMailer $PhpMailer): bool
    {
        try {
            $NewRandom = Mailer::getRandomPHPMailer(true, $PhpMailer);

            if (!$NewRandom) {
                Log::write('multi-mailer error - retry error: no more servers');
                return false;
            }

            $PhpMailer->Host = $NewRandom->Host;
            $PhpMailer->Port = $NewRandom->Port;
            $PhpMailer->Username = $NewRandom->Username;
            $PhpMailer->Password = $NewRandom->Password;
            $PhpMailer->SMTPAuth = $NewRandom->SMTPAuth;
            $PhpMailer->SMTPSecure = $NewRandom->SMTPSecure;
            $PhpMailer->SMTPDebug = $NewRandom->SMTPDebug;

            $PhpMailer->setFrom($NewRandom->From, $NewRandom->FromName);

            $PhpMailer->clearReplyTos();
            $PhpMailer->addReplyTo($NewRandom->From, $NewRandom->FromName);

            // retry with new mailer settings
            $PhpMailer->send();

            return true;
        } catch (\Exception $exception) {
            Log::write('multi-mailer error - retry error: ' . $exception->getMessage());
        }

        return false;
    }

    /**
     * @param array<string, mixed> $server
     */
    private static function isSameServer(array $server, PHPMailer $PhpMailer): bool
    {
        $host = empty($server['server']) ? 'localhost' : (string)$server['server'];
        $port = empty($server['port']) ? 25 : (int)$server['port'];
        $username = empty($server['username']) ? '' : (string)$server['username'];

        return $host === $PhpMailer->Host
            && $port === $PhpMailer->Port
            && $username === $PhpMailer->Username;
    }
}
