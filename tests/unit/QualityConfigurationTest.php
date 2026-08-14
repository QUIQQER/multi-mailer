<?php

declare(strict_types=1);

namespace QUI\MultiMailer\Tests\Unit;

use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function json_decode;
use function trim;

use const JSON_THROW_ON_ERROR;

final class QualityConfigurationTest extends TestCase
{
    private const PACKAGE_ROOT = __DIR__ . '/../..';

    public function testComposerMetadataUsesCurrentPackageStandards(): void
    {
        $json = file_get_contents(self::PACKAGE_ROOT . '/composer.json');
        self::assertNotFalse($json);

        $composer = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('quiqqer/multi-mailer', $composer['name']);
        self::assertSame('quiqqer-module', $composer['type']);
        self::assertSame('https://www.quiqqer.com', $composer['homepage']);
        self::assertSame('GPL-3.0-or-later', $composer['license']);
        self::assertSame('^8.2', $composer['require']['php']);
        self::assertSame('^2', $composer['require']['quiqqer/core']);
        self::assertSame(
            'PCSG - Computer & Internet Service OHG',
            $composer['authors'][0]['name']
        );
        self::assertArrayNotHasKey('version', $composer);
    }

    public function testCiSupportsPhp82ThroughPhp85(): void
    {
        $ci = file_get_contents(self::PACKAGE_ROOT . '/.gitlab-ci.yml');
        self::assertNotFalse($ci);

        self::assertStringNotContainsString('php-81:', $ci);
        self::assertStringContainsString('php-82: true', $ci);
        self::assertStringContainsString('php-83: true', $ci);
        self::assertStringContainsString('php-84: true', $ci);
        self::assertStringContainsString('php-85: true', $ci);
        self::assertStringNotContainsString('phpunit-php8.', $ci);
    }

    public function testPhpStanUsesSupportedPhpVersionRangeAndEmptyBaseline(): void
    {
        $configuration = file_get_contents(self::PACKAGE_ROOT . '/phpstan.dist.neon');
        $baseline = file_get_contents(self::PACKAGE_ROOT . '/phpstan-baseline.neon');

        self::assertNotFalse($configuration);
        self::assertNotFalse($baseline);
        self::assertStringContainsString('min: 80200', $configuration);
        self::assertStringContainsString('max: 80509', $configuration);
        self::assertSame('', trim($baseline));
    }
}
