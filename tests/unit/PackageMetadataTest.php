<?php

declare(strict_types=1);

namespace QUI\MultiMailer\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

use function file_get_contents;
use function getimagesize;

final class PackageMetadataTest extends TestCase
{
    private const PACKAGE_ROOT = __DIR__ . '/../..';

    public function testPackageMetadataReferencesExistingLogoAndCurrentLicense(): void
    {
        $Package = new SimpleXMLElement(
            (string)file_get_contents(self::PACKAGE_ROOT . '/package.xml')
        );

        self::assertSame(
            'URL_OPT_DIR/quiqqer/multi-mailer/bin/images/Logo.png',
            (string)$Package->package->image['src']
        );
        self::assertFileExists(self::PACKAGE_ROOT . '/bin/images/Logo.png');
        self::assertSame(
            'GPL-3.0-or-later',
            (string)$Package->package->copyright->license
        );

        $readme = file_get_contents(self::PACKAGE_ROOT . '/README.md');
        self::assertNotFalse($readme);
        self::assertStringContainsString('GPL-3.0-or-later', $readme);
    }

    public function testPackageDescriptionUsesTheCorrectLanguages(): void
    {
        $Locale = new SimpleXMLElement(
            (string)file_get_contents(self::PACKAGE_ROOT . '/locale.xml')
        );
        $Locale->registerXPathNamespace(
            'locale',
            'https://doc.quiqqer.com/xml/quiqqer-quiqqer-locale.xsd'
        );
        $descriptions = $Locale->xpath(
            '//locale:locale[@name="package.description"]'
        );

        self::assertNotFalse($descriptions);
        self::assertCount(1, $descriptions);
        self::assertSame(
            'Zuverlässiger Mailversand mit automatischer Ausfallsicherung.',
            (string)$descriptions[0]->de
        );
        self::assertSame(
            'Reliable mail delivery system with automatic failover.',
            (string)$descriptions[0]->en
        );
    }

    public function testFallbackDescriptionProvidesAnEnglishTranslation(): void
    {
        $Locale = new SimpleXMLElement(
            (string)file_get_contents(self::PACKAGE_ROOT . '/locale.xml')
        );
        $Locale->registerXPathNamespace(
            'locale',
            'https://doc.quiqqer.com/xml/quiqqer-quiqqer-locale.xsd'
        );
        $descriptions = $Locale->xpath(
            '//locale:locale[@name="template.textIsFallbackServer.description"]'
        );

        self::assertNotFalse($descriptions);
        self::assertCount(1, $descriptions);
        self::assertStringContainsString(
            'This setting defines whether the mail server acts as a fallback server.',
            (string)$descriptions[0]->en
        );
        self::assertStringNotContainsString(
            'Diese Einstellung definiert',
            (string)$descriptions[0]->en
        );
    }

    /**
     * @return array<string, array{string, int, int}>
     */
    public static function requiredImageProvider(): array
    {
        return [
            'README header' => ['Readme.png', 1200, 600],
            'package logo' => ['Logo.png', 400, 300],
            'GitLab avatar' => ['Gitlab.png', 100, 100]
        ];
    }

    #[DataProvider('requiredImageProvider')]
    public function testRequiredImagesHaveExpectedDimensions(string $file, int $width, int $height): void
    {
        $size = getimagesize(self::PACKAGE_ROOT . '/bin/images/' . $file);

        self::assertNotFalse($size);
        self::assertSame($width, $size[0]);
        self::assertSame($height, $size[1]);
    }
}
