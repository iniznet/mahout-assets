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
 * Every entry context enqueues on its own core hook and never on another's.
 *
 * @internal
 */
final class EntryContextRoutingTest extends TestCase
{
    private const string BASE_URL = 'https://example.test/build';

    /** @var list<string> */
    private const array HANDLES = ['howdah-front', 'howdah-admin', 'howdah-editor', 'howdah-front-style'];

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

    public function testAFrontEntryEnqueuesOnTheFrontHookAndNoOtherContextsEntryDoes(): void
    {
        $this->boot($this->declarations());

        \do_action(Hooks::WP_ENQUEUE_SCRIPTS);

        self::assertNotNull(\wp_script_modules()->get_registered('howdah-front'));
        self::assertContains('howdah-front', \wp_script_modules()->get_queue());
        self::assertNull(\wp_script_modules()->get_registered('howdah-admin'));
        self::assertNull(\wp_script_modules()->get_registered('howdah-editor'));
    }

    public function testAnAdminEntryEnqueuesOnTheAdminHookAndNoOtherContextsEntryDoes(): void
    {
        $this->boot($this->declarations());

        $this->onAdminScreen();
        \do_action(Hooks::ADMIN_ENQUEUE_SCRIPTS, 'index.php');

        self::assertNotNull(\wp_script_modules()->get_registered('howdah-admin'));
        self::assertContains('howdah-admin', \wp_script_modules()->get_queue());
        self::assertNull(\wp_script_modules()->get_registered('howdah-front'));
        self::assertNull(\wp_script_modules()->get_registered('howdah-editor'));
    }

    public function testAnEditorEntryEnqueuesOnTheEditorHookAndNoOtherContextsEntryDoes(): void
    {
        $this->boot($this->declarations());

        \do_action(Hooks::ENQUEUE_BLOCK_EDITOR_ASSETS);

        self::assertNotNull(\wp_script_modules()->get_registered('howdah-editor'));
        self::assertContains('howdah-editor', \wp_script_modules()->get_queue());
        self::assertNull(\wp_script_modules()->get_registered('howdah-front'));
        self::assertNull(\wp_script_modules()->get_registered('howdah-admin'));
    }

    public function testTheFrontHookDoesNotRegisterAnAdminEntryAndTheReverse(): void
    {
        $this->boot($this->declarations());

        \do_action(Hooks::WP_ENQUEUE_SCRIPTS);
        self::assertNotContains('howdah-admin', \wp_script_modules()->get_queue());

        $this->forget(self::HANDLES);
        $this->onAdminScreen();
        \do_action(Hooks::ADMIN_ENQUEUE_SCRIPTS, 'index.php');
        self::assertNotContains('howdah-front', \wp_script_modules()->get_queue());
    }

    public function testTheManifestStylesheetIsEnqueuedWithItsEntry(): void
    {
        $this->boot($this->declarations());

        \do_action(Hooks::WP_ENQUEUE_SCRIPTS);

        self::assertTrue(\wp_style_is('howdah-front-style', 'enqueued'));
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

    /**
     * @return list<array<string, string|list<string>>>
     */
    private function declarations(): array
    {
        return [
            ['handle' => 'howdah-front', 'source' => 'src/front.ts', 'context' => 'front'],
            ['handle' => 'howdah-admin', 'source' => 'src/admin.ts', 'context' => 'admin'],
            ['handle' => 'howdah-editor', 'source' => 'src/editor.ts', 'context' => 'editor'],
        ];
    }
}
