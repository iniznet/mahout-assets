<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Unit;

use Iniznet\Mahout\Assets\EntryContext;
use Iniznet\Mahout\Assets\Exception\EntryContextMismatch;
use Iniznet\Mahout\Assets\Exception\EntryContextMissing;
use Iniznet\Mahout\Assets\Exception\EntryContextUnknown;
use Iniznet\Mahout\Assets\Exception\EntryNotInManifest;
use Iniznet\Mahout\Assets\Exception\EntryPointMalformed;
use Iniznet\Mahout\Assets\Exception\FileUnreadable;
use Iniznet\Mahout\Assets\Exception\InvalidAssetBudget;
use Iniznet\Mahout\Assets\Exception\InvalidEntriesPayload;
use Iniznet\Mahout\Assets\Exception\InvalidScriptModuleTranslations;
use Iniznet\Mahout\Assets\Exception\MahoutException;
use Iniznet\Mahout\Assets\Exception\ManifestMalformed;
use Iniznet\Mahout\Assets\Tests\TestCase;

/**
 * Every named constructor, its message and its typed context getter.
 *
 * @internal
 */
final class ExceptionTest extends TestCase
{
    public function testFileUnreadable(): void
    {
        $exception = FileUnreadable::at('/srv/build/manifest.json');

        self::assertInstanceOf(MahoutException::class, $exception);
        self::assertSame('/srv/build/manifest.json', $exception->path());
        self::assertStringContainsString('/srv/build/manifest.json', $exception->getMessage());
    }

    public function testManifestMalformed(): void
    {
        self::assertSame('bad', ManifestMalformed::because('bad')->reason());
        self::assertSame('entry not an object', ManifestMalformed::entryNotAnObject('a')->reason());
        self::assertSame('missing file', ManifestMalformed::missingFile('a')->reason());
        self::assertSame('invalid version', ManifestMalformed::invalidVersion('a')->reason());
        self::assertSame('invalid styles', ManifestMalformed::invalidStyles('a')->reason());
    }

    public function testEntryContextMissing(): void
    {
        $exception = EntryContextMissing::forEntry('howdah-app');

        self::assertSame('howdah-app', $exception->handle());
        self::assertStringContainsString('declares no context', $exception->getMessage());
    }

    public function testEntryContextUnknown(): void
    {
        $exception = EntryContextUnknown::forValue('footer', 'howdah-app');

        self::assertSame('footer', $exception->value());
        self::assertStringContainsString('unknown context', $exception->getMessage());
    }

    public function testEntryPointMalformed(): void
    {
        self::assertSame('', EntryPointMalformed::missingHandle()->handle());
        self::assertSame('howdah-app', EntryPointMalformed::missingSource('howdah-app')->handle());
        self::assertSame('howdah-app', EntryPointMalformed::invalidDependencies('howdah-app')->handle());
        self::assertSame('howdah-app', EntryPointMalformed::invalidTranslations('howdah-app')->handle());
    }

    public function testInvalidScriptModuleTranslations(): void
    {
        self::assertSame('domain', InvalidScriptModuleTranslations::emptyDomain()->field());
        self::assertSame('path', InvalidScriptModuleTranslations::emptyPath()->field());
    }

    public function testInvalidEntriesPayload(): void
    {
        self::assertStringContainsString('list of entries', InvalidEntriesPayload::notAList()->getMessage());
        self::assertStringContainsString('not an EntryPoint', InvalidEntriesPayload::notAnEntryPoint()->getMessage());
    }

    public function testEntryContextMismatch(): void
    {
        $exception = EntryContextMismatch::forEntry('howdah-app', EntryContext::Front, EntryContext::Admin);

        self::assertSame('howdah-app', $exception->handle());
        self::assertStringContainsString('belongs to context "admin"', $exception->getMessage());
    }

    public function testEntryNotInManifest(): void
    {
        $exception = EntryNotInManifest::forSource('src/missing.ts');

        self::assertSame('src/missing.ts', $exception->source());
        self::assertStringContainsString('src/missing.ts', $exception->getMessage());
    }

    public function testInvalidAssetBudget(): void
    {
        $exception = InvalidAssetBudget::forKind('CSS', 0);

        self::assertSame('CSS', $exception->kind());
        self::assertStringContainsString('positive number of bytes', $exception->getMessage());
    }
}
