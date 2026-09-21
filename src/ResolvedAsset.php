<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

/**
 * One built asset resolved to a handle, a local file, a URL and a version.
 */
final readonly class ResolvedAsset
{
    public function __construct(
        public string $handle,
        public string $file,
        public string $url,
        public string $version,
    ) {
    }
}
