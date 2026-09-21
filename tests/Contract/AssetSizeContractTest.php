<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Contract;

use Iniznet\Mahout\Assets\Exception\FileUnreadable;
use Iniznet\Mahout\Assets\Internal\GzipFileSize;
use Iniznet\Mahout\Assets\Tests\TestCase;

/**
 * The production AssetSize: gzipped bytes, and the unreadable failure.
 *
 * @internal
 */
final class AssetSizeContractTest extends TestCase
{
    public function testItMeasuresTheGzippedSizeOfAFile(): void
    {
        $root = \dirname(__DIR__, 2).'/fixtures/budget/within';
        $sizes = new GzipFileSize($root);

        $bytes = $sizes->bytes('assets/front-1a2b3c4d.js');

        self::assertGreaterThan(0, $bytes);
    }

    public function testAnAbsentFileThrows(): void
    {
        $sizes = new GzipFileSize(\dirname(__DIR__, 2).'/fixtures/budget/within');

        $this->expectException(FileUnreadable::class);

        $sizes->bytes('assets/absent.js');
    }
}
