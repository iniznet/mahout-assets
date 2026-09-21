<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

use Iniznet\Mahout\Assets\Exception\EntryContextMissing;
use Iniznet\Mahout\Assets\Exception\EntryContextUnknown;
use Iniznet\Mahout\Assets\Exception\EntryPointMalformed;

/**
 * One declared entry: the WordPress handle, the build manifest key it resolves
 * through, its context, its script-module dependencies and its translations.
 *
 * The context is a required constructor argument and a required declaration
 * key, mirroring the field layer's StorageTarget rule: there is no default and
 * no inference.
 */
final readonly class EntryPoint
{
    /**
     * @param list<string> $dependencies script module identifiers, not handles
     */
    public function __construct(
        public string $handle,
        public string $source,
        public EntryContext $context,
        public array $dependencies = [],
        public ?ScriptModuleTranslations $translations = null,
    ) {
    }

    /**
     * Build an entry from a configuration declaration.
     *
     * The declaration is the shape a theme writes in its entry list:
     *
     *     [
     *         'handle'            => 'howdah-app',
     *         'source'            => 'src/front.ts',
     *         'context'           => 'front',
     *         'dependencies'      => ['@wordpress/interactivity'],
     *         'domain'            => 'howdah',
     *         'translations_path' => '/path/to/languages',
     *     ]
     *
     * @param array<string, mixed> $declaration
     *
     * @throws EntryContextMissing when the declaration carries no context
     * @throws EntryContextUnknown when the context names no known context
     * @throws EntryPointMalformed when the declaration is otherwise incomplete
     */
    public static function fromArray(array $declaration): self
    {
        $handle = $declaration['handle'] ?? null;
        if (!\is_string($handle) || '' === $handle) {
            throw EntryPointMalformed::missingHandle();
        }

        $source = $declaration['source'] ?? null;
        if (!\is_string($source) || '' === $source) {
            throw EntryPointMalformed::missingSource($handle);
        }

        if (!\array_key_exists('context', $declaration)) {
            throw EntryContextMissing::forEntry($handle);
        }

        $value = $declaration['context'];
        $context = \is_string($value) ? EntryContext::tryFrom($value) : null;
        if (null === $context) {
            throw EntryContextUnknown::forValue(\is_string($value) ? $value : \gettype($value), $handle);
        }

        $declaredDependencies = $declaration['dependencies'] ?? [];
        if (!\is_array($declaredDependencies)) {
            throw EntryPointMalformed::invalidDependencies($handle);
        }

        $dependencies = [];
        foreach ($declaredDependencies as $dependency) {
            if (!\is_string($dependency)) {
                throw EntryPointMalformed::invalidDependencies($handle);
            }

            $dependencies[] = $dependency;
        }

        return new self(
            handle: $handle,
            source: $source,
            context: $context,
            dependencies: $dependencies,
            translations: self::translations($declaration, $handle),
        );
    }

    /**
     * @param array<string, mixed> $declaration
     */
    private static function translations(array $declaration, string $handle): ?ScriptModuleTranslations
    {
        $domain = $declaration['domain'] ?? null;
        $path = $declaration['translations_path'] ?? null;

        if (null === $domain && null === $path) {
            return null;
        }

        if (!\is_string($domain) || !\is_string($path)) {
            throw EntryPointMalformed::invalidTranslations($handle);
        }

        return new ScriptModuleTranslations(domain: $domain, path: $path);
    }
}
