<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Contract;

use Iniznet\Mahout\Assets\Exception\FileUnreadable;
use Iniznet\Mahout\Assets\Internal\FileManifestSource;
use Iniznet\Mahout\Assets\Tests\TestCase;

/**
 * The production ManifestSource: readable, contents and the unreadable failure.
 *
 * @internal
 */
final class ManifestSourceContractTest extends TestCase
{
    public function testAReadableManifestReportsItsPathAndContents(): void
    {
        $path = \dirname(__DIR__, 2).'/fixtures/manifest/build.json';
        $source = new FileManifestSource($path);

        self::assertTrue($source->readable());
        self::assertSame($path, $source->path());
        self::assertStringContainsString('src/front.ts', $source->contents());
    }

    public function testAnUnreadableManifestIsNotReadableAndItsContentsThrow(): void
    {
        $source = new FileManifestSource(\dirname(__DIR__, 2).'/fixtures/manifest/absent.json');

        self::assertFalse($source->readable());

        $this->expectException(FileUnreadable::class);

        $source->contents();
    }
}
