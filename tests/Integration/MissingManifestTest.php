<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Integration;

use Iniznet\Mahout\Assets\AssetsConfig;
use Iniznet\Mahout\Assets\AssetsProvider;
use Iniznet\Mahout\Assets\Contracts\ManifestSource;
use Iniznet\Mahout\Assets\DevMode;
use Iniznet\Mahout\Assets\EntryList;
use Iniznet\Mahout\Assets\Exception\FileUnreadable;
use Iniznet\Mahout\Assets\Hooks;
use Iniznet\Mahout\Assets\Tests\Fixtures\InMemoryManifestSource;
use Iniznet\Mahout\Assets\Tests\TestCase;
use Iniznet\Mahout\Kernel\Container;
use Iniznet\Mahout\Kernel\Diagnostics;
use Iniznet\Mahout\Kernel\Level;

/**
 * A missing manifest fails loudly in development and serves nothing in
 * production.
 *
 * @internal
 */
final class MissingManifestTest extends TestCase
{
    private const string BASE_URL = 'https://example.test/build';

    public function testProductionRecordsAWarningAndServesNothing(): void
    {
        $diagnostics = $this->diagnostics();
        $fired = false;
        \add_action(Hooks::MANIFEST_MISSING, static function () use (&$fired): void {
            $fired = true;
        });

        $this->boot(
            [['handle' => 'howdah-front', 'source' => 'src/front.ts', 'context' => 'front']],
            $diagnostics,
            new InMemoryManifestSource('/missing/manifest.json', '', false),
            DevMode::disabled(),
        );

        \do_action(Hooks::WP_ENQUEUE_SCRIPTS);

        self::assertTrue($fired);
        self::assertNull(\wp_script_modules()->get_registered('howdah-front'));
        self::assertNotContains('howdah-front', \wp_script_modules()->get_queue());

        $warnings = \array_filter(
            $diagnostics->records(),
            static fn ($record): bool => Level::Warning === $record->level
                && 'asset manifest missing' === $record->message,
        );
        self::assertCount(1, $warnings);
    }

    public function testDevelopmentFailsLoudly(): void
    {
        $this->boot(
            [['handle' => 'howdah-front', 'source' => 'src/front.ts', 'context' => 'front']],
            $this->diagnostics(),
            new InMemoryManifestSource('/missing/manifest.json', '', false),
            DevMode::enabled(),
        );

        $this->expectException(FileUnreadable::class);
        $this->expectExceptionMessage('/missing/manifest.json');

        \do_action(Hooks::WP_ENQUEUE_SCRIPTS);
    }

    /**
     * @param list<array<string, string|list<string>>> $declarations
     */
    private function boot(
        array $declarations,
        ?Diagnostics $diagnostics = null,
        ?ManifestSource $source = null,
        ?DevMode $devMode = null,
    ): Container {
        $container = new Container();
        $container->set($diagnostics ?? $this->diagnostics());
        $container->set(new AssetsConfig(
            manifest: $source ?? new InMemoryManifestSource('build/manifest.json', $this->manifestJson()),
            entries: EntryList::fromDeclarations($declarations),
            baseUrl: self::BASE_URL,
            devMode: $devMode ?? DevMode::disabled(),
        ));

        $provider = new AssetsProvider();
        $provider->register($container);
        $provider->boot($container);

        return $container;
    }

    private function manifestJson(): string
    {
        $contents = \file_get_contents(\dirname(__DIR__, 2).'/fixtures/manifest/build.json');
        self::assertIsString($contents);

        return $contents;
    }
}
