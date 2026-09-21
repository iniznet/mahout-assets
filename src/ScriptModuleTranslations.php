<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

use Iniznet\Mahout\Assets\Exception\InvalidScriptModuleTranslations;

/**
 * The text domain and translation directory a script module needs when its
 * domain is not 'default' or its files live outside the standard location.
 */
final readonly class ScriptModuleTranslations
{
    public function __construct(
        public string $domain,
        public string $path,
    ) {
        if ('' === $domain) {
            throw InvalidScriptModuleTranslations::emptyDomain();
        }

        if ('' === $path) {
            throw InvalidScriptModuleTranslations::emptyPath();
        }
    }
}
