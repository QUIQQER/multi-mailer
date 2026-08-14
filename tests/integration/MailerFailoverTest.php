<?php

declare(strict_types=1);

namespace QUI\MultiMailer\Tests\Integration;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\Config;
use QUI\MultiMailer\Mailer;

final class MailerFailoverTest extends TestCase
{
    private ?Config $Config = null;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $serverSections = [];

    protected function setUp(): void
    {
        $this->Config = QUI::getPackage('quiqqer/multi-mailer')->getConfig();

        if (!$this->Config) {
            return;
        }

        foreach ($this->Config->toArray() as $section => $values) {
            if (!is_string($section) || !str_starts_with($section, 'server-')) {
                continue;
            }

            $this->serverSections[$section] = $values;
            $this->Config->del($section);
        }
    }

    protected function tearDown(): void
    {
        if (!$this->Config) {
            return;
        }

        foreach ($this->Config->toArray() as $section => $values) {
            if (is_string($section) && str_starts_with($section, 'server-')) {
                $this->Config->del($section);
            }
        }

        foreach ($this->serverSections as $section => $values) {
            $this->Config->setSection($section, $values);
        }
    }

    public function testMissingFallbackServerReturnsNull(): void
    {
        self::assertSame([], Mailer::getFallBackServerList());
        self::assertNull(Mailer::getRandomFallbackPHPMailer());
    }

    public function testFailedMainServerIsExcludedFromRetry(): void
    {
        $FailedMailer = Mailer::parseMailServerDataToPhpMailer(Mailer::getMainMailerConfig());

        self::assertNull(Mailer::getRandomPHPMailer(true, $FailedMailer));
        self::assertFalse(Mailer::retryWithNextServer($FailedMailer));
    }
}
