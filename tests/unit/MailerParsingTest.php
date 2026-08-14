<?php

declare(strict_types=1);

namespace QUI\MultiMailer\Tests\Unit;

use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use QUI\MultiMailer\Mailer;

final class MailerParsingTest extends TestCase
{
    public function testMapsCompleteServerConfiguration(): void
    {
        $Mailer = Mailer::parseMailServerDataToPhpMailer(array_replace(
            self::serverData(),
            [
                'MAILFrom' => 'sender@example.test',
                'MAILFromText' => 'Example Sender',
                'MAILReplyTo' => 'reply@example.test',
                'server' => 'smtp.example.test',
                'port' => '2525',
                'auth' => '1',
                'username' => 'smtp-user',
                'password' => 'secret',
                'security' => 'tls',
                'debug' => '2',
                'secureSSL_verify_peer' => '1',
                'secureSSL_verify_peer_name' => '1',
                'secureSSL_allow_self_signed' => '0'
            ]
        ));

        self::assertSame('smtp', $Mailer->Mailer);
        self::assertSame('UTF-8', $Mailer->CharSet);
        self::assertSame('smtp.example.test', $Mailer->Host);
        self::assertSame(2525, $Mailer->Port);
        self::assertTrue($Mailer->SMTPAuth);
        self::assertSame('smtp-user', $Mailer->Username);
        self::assertSame('secret', $Mailer->Password);
        self::assertSame(PHPMailer::ENCRYPTION_STARTTLS, $Mailer->SMTPSecure);
        self::assertSame('sender@example.test', $Mailer->From);
        self::assertSame('Example Sender', $Mailer->FromName);
        self::assertContains(['reply@example.test', ''], $Mailer->getReplyToAddresses());
        self::assertSame(2, $Mailer->SMTPDebug);
        self::assertIsCallable($Mailer->Debugoutput);
        self::assertSame([
            'ssl' => [
                'verify_peer' => 1,
                'verify_peer_name' => 1,
                'allow_self_signed' => 0
            ]
        ], $Mailer->SMTPOptions);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function securityProvider(): array
    {
        return [
            'SMTPS' => ['ssl', PHPMailer::ENCRYPTION_SMTPS],
            'STARTTLS' => ['tls', PHPMailer::ENCRYPTION_STARTTLS],
            'unknown value' => ['invalid', '']
        ];
    }

    #[DataProvider('securityProvider')]
    public function testMapsSupportedSecurityModes(string $security, string $expected): void
    {
        $Mailer = Mailer::parseMailServerDataToPhpMailer(array_replace(
            self::serverData(),
            [
                'auth' => '1',
                'security' => $security
            ]
        ));

        self::assertSame($expected, $Mailer->SMTPSecure);
    }

    public function testDisablesTransportSecurityWithoutAuthentication(): void
    {
        $Mailer = Mailer::parseMailServerDataToPhpMailer(array_replace(
            self::serverData(),
            ['security' => 'tls']
        ));

        self::assertFalse($Mailer->SMTPAuth);
        self::assertSame('', $Mailer->SMTPSecure);
    }

    /**
     * @return array<string, string>
     */
    private static function serverData(): array
    {
        return [
            'MAILFrom' => '',
            'MAILFromText' => '',
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
            'isFallbackServer' => '0'
        ];
    }
}
