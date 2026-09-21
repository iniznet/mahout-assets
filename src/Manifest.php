<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

use Iniznet\Mahout\Assets\Exception\EntryNotInManifest;
use Iniznet\Mahout\Assets\Exception\ManifestMalformed;

/**
 * The parsed build manifest: a source path to a built file, a version and a
 * stylesheet list.
 *
 * It is framework-blind. It parses JSON, resolves a URL from a supplied base
 * and a version from the manifest or the content hash in the file name, and
 * names no WordPress function.
 */
final readonly class Manifest
{
    /**
     * @param array<string, ManifestEntry> $entries
     */
    private function __construct(private array $entries)
    {
    }

    /**
     * @throws ManifestMalformed when the JSON is not the documented shape
     */
    public static function fromJson(string $json): self
    {
        try {
            $decoded = \json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw ManifestMalformed::because($exception->getMessage());
        }

        if (!\is_array($decoded)) {
            throw ManifestMalformed::because('the manifest is not a JSON object');
        }

        $entries = [];
        foreach ($decoded as $source => $entry) {
            if (!\is_string($source)) {
                throw ManifestMalformed::because('a manifest key is not a string');
            }

            $entries[$source] = self::entry($source, $entry);
        }

        return new self($entries);
    }

    public function has(string $source): bool
    {
        return \array_key_exists($source, $this->entries);
    }

    /** @return list<ManifestEntry> */
    public function entries(): array
    {
        return \array_values($this->entries);
    }

    /**
     * Bind a declared entry to the assets the build emitted for it.
     *
     * @throws EntryNotInManifest when the source is absent from the manifest
     */
    public function resolve(EntryPoint $entry, string $baseUrl): ResolvedEntry
    {
        $manifestEntry = $this->entries[$entry->source] ?? null;
        if (null === $manifestEntry) {
            throw EntryNotInManifest::forSource($entry->source);
        }

        $version = $manifestEntry->version
            ?? self::hashedVersion($manifestEntry->file)
            ?? $manifestEntry->file;

        return new ResolvedEntry(
            handle: $entry->handle,
            file: $manifestEntry->file,
            url: self::url($baseUrl, $manifestEntry->file),
            version: $version,
            context: $entry->context,
            dependencies: $entry->dependencies,
            styles: self::styles($entry->handle, $manifestEntry->styles, $baseUrl, $version),
            translations: $entry->translations,
        );
    }

    /**
     * @throws ManifestMalformed
     */
    private static function entry(string $source, mixed $entry): ManifestEntry
    {
        if (!\is_array($entry)) {
            throw ManifestMalformed::entryNotAnObject($source);
        }

        $file = $entry['file'] ?? null;
        if (!\is_string($file) || '' === $file) {
            throw ManifestMalformed::missingFile($source);
        }

        $version = $entry['version'] ?? null;
        if (null !== $version && !\is_string($version)) {
            throw ManifestMalformed::invalidVersion($source);
        }

        $css = $entry['css'] ?? [];
        if (!\is_array($css)) {
            throw ManifestMalformed::invalidStyles($source);
        }

        $styles = [];
        foreach ($css as $style) {
            if (!\is_string($style) || '' === $style) {
                throw ManifestMalformed::invalidStyles($source);
            }

            $styles[] = $style;
        }

        return new ManifestEntry(source: $source, file: $file, version: $version, styles: $styles);
    }

    /**
     * @param list<string> $styles
     *
     * @return list<ResolvedAsset>
     */
    private static function styles(string $handle, array $styles, string $baseUrl, string $fallbackVersion): array
    {
        $resolved = [];
        foreach ($styles as $index => $style) {
            $resolved[] = new ResolvedAsset(
                handle: $handle.'-style'.(0 === $index ? '' : '-'.($index + 1)),
                file: $style,
                url: self::url($baseUrl, $style),
                version: self::hashedVersion($style) ?? $fallbackVersion,
            );
        }

        return $resolved;
    }

    private static function url(string $baseUrl, string $file): string
    {
        return \rtrim($baseUrl, '/').'/'.\ltrim($file, '/');
    }

    /**
     * The content hash Vite puts in a file name, when there is one.
     */
    private static function hashedVersion(string $file): ?string
    {
        $name = \pathinfo($file, PATHINFO_FILENAME);
        $separator = \strrpos($name, '-');
        if (false === $separator) {
            return null;
        }

        $candidate = \substr($name, $separator + 1);

        return 1 === \preg_match('/^[0-9A-Za-z_]{6,}$/', $candidate) ? $candidate : null;
    }
}
