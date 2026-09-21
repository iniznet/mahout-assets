<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

/**
 * One declared entry bound to its built assets: the JavaScript module and every
 * stylesheet the manifest declares for it.
 */
final readonly class ResolvedEntry
{
    /**
     * @param list<string>        $dependencies script module identifiers
     * @param list<ResolvedAsset> $styles       built stylesheets, in manifest order
     */
    public function __construct(
        public string $handle,
        public string $file,
        public string $url,
        public string $version,
        public EntryContext $context,
        public array $dependencies,
        public array $styles,
        public ?ScriptModuleTranslations $translations,
    ) {
    }
}
