<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

/**
 * One parsed build manifest record, before it is bound to a declared entry.
 */
final readonly class ManifestEntry
{
    /**
     * @param list<string> $styles built CSS files, relative to the build root
     */
    public function __construct(
        public string $source,
        public string $file,
        public ?string $version,
        public array $styles,
    ) {
    }
}
