<?php

declare(strict_types=1);

namespace QUI\MultiMailer\Tests\Unit;

use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;
use QUI;
use QUI\Config;
use QUI\Mail\Queue;
use QUI\MultiMailer\EventHandler;
use QUI\MultiMailer\Mailer as MultiMailer;
use RuntimeException;

final class EventHandlerTest extends TestCase
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

    public function testInitializesPhpMailer(): void
    {
        self::assertInstanceOf(PHPMailer::class, EventHandler::onGetPhpMailerInitStart());
    }

    public function testQueueErrorWithoutPhpMailerStopsRetry(): void
    {
        $Queue = $this->createStub(Queue::class);

        self::assertFalse(EventHandler::onMailQueueSendError(
            $Queue,
            null,
            new RuntimeException('temporary SMTP failure')
        ));
    }

    public function testRejectedQueueRecipientStopsRetry(): void
    {
        $Queue = $this->createStub(Queue::class);

        self::assertFalse(EventHandler::onMailQueueSendError(
            $Queue,
            new PHPMailer(true),
            new RuntimeException('Recipient address rejected: blocked')
        ));
    }

    public function testRejectedDirectRecipientStopsRetry(): void
    {
        $Mailer = $this->createStub(QUI\Mail\Mailer::class);

        self::assertFalse(EventHandler::onMailSendError(
            $Mailer,
            new PHPMailer(true),
            new RuntimeException('Recipient address rejected: blocked')
        ));
    }

    public function testTemporaryErrorsAttemptFailover(): void
    {
        $FailedMailer = MultiMailer::parseMailServerDataToPhpMailer(MultiMailer::getMainMailerConfig());
        $Queue = $this->createStub(Queue::class);
        $Mailer = $this->createStub(QUI\Mail\Mailer::class);
        $Exception = new RuntimeException('temporary SMTP failure');

        self::assertFalse(EventHandler::onMailQueueSendError($Queue, $FailedMailer, $Exception));
        self::assertFalse(EventHandler::onMailSendError($Mailer, $FailedMailer, $Exception));
    }
}
