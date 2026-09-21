<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Integration;

use Iniznet\Mahout\Assets\AssetsConfig;
use Iniznet\Mahout\Assets\AssetsProvider;
use Iniznet\Mahout\Assets\Contracts\ManifestSource;
use Iniznet\Mahout\Assets\DevMode;
use Iniznet\Mahout\Assets\EntryList;
use Iniznet\Mahout\Assets\Hooks;
use Iniznet\Mahout\Assets\Tests\Fixtures\InMemoryManifestSource;
use Iniznet\Mahout\Assets\Tests\TestCase;
use Iniznet\Mahout\Kernel\Container;
use Iniznet\Mahout\Kernel\Diagnostics;

/**
 * The registration path: wp_register_script_module(), then
 * wp_enqueue_script_module(), then wp_set_script_module_translations().
 *
 * @internal
 */
final class ScriptModuleRegistrationTest extends TestCase
{
    private const string BASE_URL = 'https://example.test/build';

    /** @var list<string> */
    private const array HANDLES = ['howdah-front', 'howdah-translated', 'howdah-default'];

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

    public function testTheEntryRegistersAndEnqueuesThroughTheScriptModulesApi(): void
    {
        $this->boot([
            ['handle' => 'howdah-front', 'source' => 'src/front.ts', 'context' => 'front'],
        ]);

        \do_action(Hooks::WP_ENQUEUE_SCRIPTS);

        $registered = \wp_script_modules()->get_registered('howdah-front');
        self::assertNotNull($registered);
        self::assertSame('https://example.test/build/assets/front-1a2b3c4d.js', $registered['src']);
        self::assertSame('1a2b3c4d', $registered['version']);
        self::assertContains('howdah-front', \wp_script_modules()->get_queue());
    }

    public function testANonDefaultDomainReceivesScriptModuleTranslationsAfterRegistration(): void
    {
        $this->boot([
            [
                'handle' => 'howdah-translated',
                'source' => 'src/front.ts',
                'context' => 'front',
                'domain' => 'howdah',
                'translations_path' => '/srv/languages',
            ],
        ]);

        \do_action(Hooks::WP_ENQUEUE_SCRIPTS);

        $registered = \wp_script_modules()->get_registered('howdah-translated');
        self::assertNotNull($registered);
        self::assertSame('howdah', $registered['textdomain']);
        self::assertSame('/srv/languages', $registered['translations_path']);
    }

    public function testTheDefaultDomainDoesNotSetScriptModuleTranslations(): void
    {
        $this->boot([
            [
                'handle' => 'howdah-default',
                'source' => 'src/front.ts',
                'context' => 'front',
                'domain' => 'default',
                'translations_path' => '/srv/languages',
            ],
        ]);

        \do_action(Hooks::WP_ENQUEUE_SCRIPTS);

        $registered = \wp_script_modules()->get_registered('howdah-default');
        self::assertNotNull($registered);
        self::assertArrayNotHasKey('textdomain', $registered);
    }

    public function testTheRegisteredActionFiresWithTheHandles(): void
    {
        $observed = [];
        \add_action(Hooks::REGISTERED, static function (array $handles) use (&$observed): void {
            $observed = $handles;
        });

        $this->boot([
            ['handle' => 'howdah-front', 'source' => 'src/front.ts', 'context' => 'front'],
        ]);

        \do_action(Hooks::WP_ENQUEUE_SCRIPTS);

        self::assertSame(['howdah-front', 'howdah-front-style'], $observed);
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
