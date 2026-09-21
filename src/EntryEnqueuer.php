<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

use Iniznet\Mahout\Assets\Contracts\ManifestSource;
use Iniznet\Mahout\Assets\Exception\EntryContextMismatch;
use Iniznet\Mahout\Assets\Exception\FileUnreadable;
use Iniznet\Mahout\Assets\Exception\InvalidEntriesPayload;
use Iniznet\Mahout\Kernel\Diagnostics;
use Iniznet\Mahout\Kernel\Level;

/**
 * Resolves one context's entries and registers them.
 *
 * The order is fixed: filter the declared list, fire before_enqueue, register
 * and enqueue each script module (and its stylesheets), set translations after
 * registration, then fire registered with the handles.
 */
final readonly class EntryEnqueuer
{
    public function __construct(
        private EntryList $entries,
        private ManifestSource $manifest,
        private DevMode $devMode,
        private Diagnostics $diagnostics,
        private string $baseUrl,
    ) {
    }

    /**
     * @return list<string> the handles registered, script modules first
     */
    public function enqueue(EntryContext $context): array
    {
        if (!$this->manifest->readable()) {
            return $this->withoutManifest($context);
        }

        $manifest = Manifest::fromJson($this->manifest->contents());
        $entries = $this->entries($context);

        \do_action(Hooks::BEFORE_ENQUEUE, $context);

        $handles = [];
        foreach ($entries as $entry) {
            $handles = \array_merge($handles, $this->register($manifest->resolve($entry, $this->baseUrl)));
        }

        \do_action(Hooks::REGISTERED, $handles);

        return $handles;
    }

    /**
     * Development fails loudly; production records a warning and serves nothing.
     * Neither degrades: no entry is substituted, no URL is guessed.
     *
     * @return list<string>
     */
    private function withoutManifest(EntryContext $context): array
    {
        $path = $this->manifest->path();

        \do_action(Hooks::MANIFEST_MISSING, $path, $context);

        $this->diagnostics->log(
            level: Level::Warning,
            message: 'asset manifest missing',
            context: ['path' => $path, 'context' => $context],
        );

        if ($this->devMode->enabled) {
            throw FileUnreadable::at($path);
        }

        return [];
    }

    /**
     * @return list<EntryPoint>
     */
    private function entries(EntryContext $context): array
    {
        $filtered = \apply_filters(Hooks::ENTRIES, $this->entries->forContext($context), $context);

        if (!\is_array($filtered)) {
            throw InvalidEntriesPayload::notAList();
        }

        $entries = [];
        foreach ($filtered as $entry) {
            if (!$entry instanceof EntryPoint) {
                throw InvalidEntriesPayload::notAnEntryPoint();
            }

            if ($entry->context !== $context) {
                throw EntryContextMismatch::forEntry($entry->handle, $context, $entry->context);
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * @return list<string>
     */
    private function register(ResolvedEntry $entry): array
    {
        \wp_register_script_module($entry->handle, $entry->url, $entry->dependencies, $entry->version);

        if (null !== $entry->translations && 'default' !== $entry->translations->domain) {
            \wp_set_script_module_translations(
                $entry->handle,
                $entry->translations->domain,
                $entry->translations->path,
            );
        }

        \wp_enqueue_script_module($entry->handle);

        $handles = [$entry->handle];
        foreach ($entry->styles as $style) {
            \wp_enqueue_style($style->handle, $style->url, [], $style->version);
            $handles[] = $style->handle;
        }

        return $handles;
    }
}
