<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Internal;

use Iniznet\Mahout\Assets\Contracts\AssetSize;
use Iniznet\Mahout\Assets\Exception\FileUnreadable;

/**
 * The gzipped transfer size of a built asset, read from a build root.
 *
 * A budget is about bytes on the wire, so the file is compressed with the
 * level WordPress's own asset pipeline assumes.
 *
 * @internal
 */
final readonly class GzipFileSize implements AssetSize
{
    public function __construct(private string $root)
    {
    }

    public function bytes(string $file): int
    {
        $path = \rtrim($this->root, '/').'/'.\ltrim($file, '/');

        if (!\is_readable($path)) {
            throw FileUnreadable::at($path);
        }

        $contents = \file_get_contents($path);
        if (false === $contents) {
            throw FileUnreadable::at($path);
        }

        $compressed = \gzencode($contents, 9);
        if (false === $compressed) {
            throw FileUnreadable::at($path);
        }

        return \strlen($compressed);
    }
}
