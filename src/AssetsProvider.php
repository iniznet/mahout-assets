<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

use Iniznet\Mahout\Kernel\Container;
use Iniznet\Mahout\Kernel\Contracts\ServiceProvider;
use Iniznet\Mahout\Kernel\Diagnostics;

/**
 * The composition-root entry point for the asset pipeline.
 *
 * A theme registers an AssetsConfig, then names this provider. Registering
 * builds the entry enqueuer; booting attaches one action per entry context.
 */
final class AssetsProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        $config = $container->get(AssetsConfig::class);

        $container->set($config->entries);
        $container->set(new EntryEnqueuer(
            entries: $config->entries,
            manifest: $config->manifest,
            devMode: $config->devMode,
            diagnostics: $container->get(Diagnostics::class),
            baseUrl: $config->baseUrl,
        ));
    }

    public function boot(Container $container): void
    {
        $enqueuer = $container->get(EntryEnqueuer::class);

        foreach (EntryContext::cases() as $context) {
            \add_action($context->hook(), $this->enqueue($enqueuer, $context), priority: 10, accepted_args: 0);
        }
    }

    private function enqueue(EntryEnqueuer $enqueuer, EntryContext $context): \Closure
    {
        return static function () use ($enqueuer, $context): void {
            $enqueuer->enqueue($context);
        };
    }
}
