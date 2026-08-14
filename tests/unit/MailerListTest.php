<?php

declare(strict_types=1);

namespace QUI\MultiMailer\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\Config;
use QUI\MultiMailer\Mailer;

final class MailerListTest extends TestCase
{
    private ?Config $Config = null;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $serverSections = [];

    protected function setUp(): void
    {
        $this->Config = QUI::getPackage('quiqqer/multi-mailer')->getConfig();
        self::assertNotNull($this->Config);

        foreach ($this->Config->toArray() as $section => $values) {
            if (!is_string($section) || !str_starts_with($section, 'server-')) {
                continue;
            }

            $this->serverSections[$section] = $values;
            $this->Config->del($section);
        }

        $this->Config->setSection('server-primary-test', [
            'server' => 'smtp-primary.example.test',
            'isFallbackServer' => 0
        ]);
        $this->Config->setSection('server-fallback-test', [
            'server' => 'smtp-fallback.example.test',
            'port' => '2525',
            'isFallbackServer' => 1
        ]);
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

    public function testDefaultListOmitsFallbackServersAndFillsMissingFields(): void
    {
        $servers = Mailer::getList();

        self::assertCount(1, $servers);
        self::assertSame('smtp-primary.example.test', $servers[0]['server']);
        self::assertSame('', $servers[0]['port']);
        self::assertSame('', $servers[0]['MAILFrom']);
        self::assertSame(0, $servers[0]['isFallbackServer']);
    }

    public function testCompleteListIncludesFallbackServers(): void
    {
        $servers = Mailer::getList(true);

        self::assertCount(2, $servers);
        self::assertSame('smtp-fallback.example.test', $servers[1]['server']);
        self::assertSame('2525', $servers[1]['port']);
        self::assertSame(1, $servers[1]['isFallbackServer']);
    }

    public function testFallbackListContainsOnlyFallbackServers(): void
    {
        $servers = array_values(Mailer::getFallBackServerList());

        self::assertCount(1, $servers);
        self::assertSame('smtp-fallback.example.test', $servers[0]['server']);
    }
}
