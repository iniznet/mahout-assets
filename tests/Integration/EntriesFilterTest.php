<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Integration;

use Iniznet\Mahout\Assets\AssetsConfig;
use Iniznet\Mahout\Assets\AssetsProvider;
use Iniznet\Mahout\Assets\Contracts\ManifestSource;
use Iniznet\Mahout\Assets\DevMode;
use Iniznet\Mahout\Assets\EntryContext;
use Iniznet\Mahout\Assets\EntryList;
use Iniznet\Mahout\Assets\EntryPoint;
use Iniznet\Mahout\Assets\Exception\EntryContextMismatch;
use Iniznet\Mahout\Assets\Exception\InvalidEntriesPayload;
use Iniznet\Mahout\Assets\Hooks;
use Iniznet\Mahout\Assets\Tests\Fixtures\InMemoryManifestSource;
use Iniznet\Mahout\Assets\Tests\TestCase;
use Iniznet\Mahout\Kernel\Container;
use Iniznet\Mahout\Kernel\Diagnostics;

/**
 * The mahout/assets/entries filter receives the entry list and the context.
 *
 * @internal
 */
final class EntriesFilterTest extends TestCase
{
    private const string BASE_URL = 'https://example.test/build';

    /** @var list<string> */
    private const array HANDLES = ['howdah-front', 'howdah-admin', 'howdah-added'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->forget(self::HANDLES);
    }

    protected function tearDown(): void
    {
        $this->forget(self::HANDLES);
        parent::tearDown();
    }

    public function testAFilterThatAddsAnAdminEntryHasItEnqueued(): void
    {
        $receivedContext = null;
        $receivedHandles = [];
        \add_filter(Hooks::ENTRIES, static function (array $entries, EntryContext $context) use (&$receivedContext, &$receivedHandles): array {
            $receivedContext = $context;
            foreach ($entries as $entry) {
                if ($entry instanceof EntryPoint) {
                    $receivedHandles[] = $entry->handle;
                }
            }

            if (EntryContext::Admin === $context) {
                $entries[] = new EntryPoint(handle: 'howdah-added', source: 'src/admin.ts', context: EntryContext::Admin);
            }

            return $entries;
        }, 10, 2);

        $this->boot([
            ['handle' => 'howdah-admin', 'source' => 'src/admin.ts', 'context' => 'admin'],
        ]);

        $this->onAdminScreen();
        \do_action(Hooks::ADMIN_ENQUEUE_SCRIPTS, 'index.php');

        self::assertSame(EntryContext::Admin, $receivedContext);
        self::assertSame(['howdah-admin'], $receivedHandles);
        self::assertNotNull(\wp_script_modules()->get_registered('howdah-added'));
        self::assertContains('howdah-added', \wp_script_modules()->get_queue());
    }

    public function testAFilterThatReturnsAnEntryForAnotherContextIsRefused(): void
    {
        \add_filter(Hooks::ENTRIES, static function (array $entries): array {
            $entries[] = new EntryPoint(handle: 'howdah-added', source: 'src/admin.ts', context: EntryContext::Admin);

            return $entries;
        }, 10, 1);

        $this->boot([]);

        $this->expectException(EntryContextMismatch::class);
        $this->expectExceptionMessage('belongs to context "admin"');

        \do_action(Hooks::WP_ENQUEUE_SCRIPTS);
    }

    public function testAFilterThatReturnsSomethingElseIsRefused(): void
    {
        \add_filter(Hooks::ENTRIES, static fn (array $entries): string => 'nope');

        $this->boot([]);

        $this->expectException(InvalidEntriesPayload::class);
        $this->expectExceptionMessage('must return a list of entries');

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
