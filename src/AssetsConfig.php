<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

use Iniznet\Mahout\Assets\Contracts\ManifestSource;

/**
 * Everything the composition root tells the assets provider: where the build
 * manifest is, what was declared, where the built files are served from, and
 * whether development mode is on.
 *
 * The composition root registers it before the kernel boots; the provider
 * resolves it and builds the entry enqueuer. Nothing is read from the
 * filesystem at construction.
 */
final readonly class AssetsConfig
{
    public function __construct(
        public ManifestSource $manifest,
        public EntryList $entries,
        public string $baseUrl,
        public DevMode $devMode,
    ) {
    }
}
