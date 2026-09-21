<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Unit;

use Iniznet\Mahout\Assets\EntryContext;
use Iniznet\Mahout\Assets\EntryPoint;
use Iniznet\Mahout\Assets\Exception\EntryNotInManifest;
use Iniznet\Mahout\Assets\Exception\ManifestMalformed;
use Iniznet\Mahout\Assets\Manifest;
use Iniznet\Mahout\Assets\Tests\TestCase;

/**
 * @internal
 */
final class ManifestTest extends TestCase
{
    private const string BASE_URL = 'https://example.test/build';

    public function testEveryDeclaredEntryResolvesToAUrlAndAVersion(): void
    {
        $manifest = $this->fixture();

        $front = $manifest->resolve(
            new EntryPoint(handle: 'howdah-front', source: 'src/front.ts', context: EntryContext::Front),
            self::BASE_URL,
        );
        self::assertSame('https://example.test/build/assets/front-1a2b3c4d.js', $front->url);
        self::assertSame('1a2b3c4d', $front->version);

        $admin = $manifest->resolve(
            new EntryPoint(handle: 'howdah-admin', source: 'src/admin.ts', context: EntryContext::Admin),
            self::BASE_URL,
        );
        self::assertSame('https://example.test/build/assets/admin-9c0d1e2f.js', $admin->url);
        self::assertSame('9c0d1e2f', $admin->version);

        $editor = $manifest->resolve(
            new EntryPoint(handle: 'howdah-editor', source: 'src/editor.ts', context: EntryContext::Editor),
            self::BASE_URL,
        );
        self::assertSame('https://example.test/build/assets/editor.js', $editor->url);
        self::assertSame('editor-explicit', $editor->version);
    }

    public function testTheDeclaredVersionWinsOverTheFileNameHash(): void
    {
        $manifest = Manifest::fromJson('{"src/editor.ts":{"file":"assets/editor-deadbeef.js","version":"built"}}');

        $resolved = $manifest->resolve(
            new EntryPoint(handle: 'howdah-editor', source: 'src/editor.ts', context: EntryContext::Editor),
            self::BASE_URL,
        );

        self::assertSame('built', $resolved->version);
    }

    public function testStylesResolveToHandlesUrlsAndVersions(): void
    {
        $manifest = $this->fixture();

        $front = $manifest->resolve(
            new EntryPoint(handle: 'howdah-front', source: 'src/front.ts', context: EntryContext::Front),
            self::BASE_URL,
        );

        self::assertCount(1, $front->styles);
        self::assertSame('howdah-front-style', $front->styles[0]->handle);
        self::assertSame('https://example.test/build/assets/front-5e6f7a8b.css', $front->styles[0]->url);
        self::assertSame('5e6f7a8b', $front->styles[0]->version);
    }

    public function testAnUndeclaredSourceIsRefused(): void
    {
        $this->expectException(EntryNotInManifest::class);
        $this->expectExceptionMessage('declares no entry for "src/missing.ts"');

        $this->fixture()->resolve(
            new EntryPoint(handle: 'howdah-missing', source: 'src/missing.ts', context: EntryContext::Front),
            self::BASE_URL,
        );
    }

    public function testAManifestWithoutAFileIsRefused(): void
    {
        $this->expectException(ManifestMalformed::class);
        $this->expectExceptionMessage('declares no file');

        Manifest::fromJson('{"src/front.ts":{}}');
    }

    public function testAManifestThatIsNotJsonIsRefused(): void
    {
        $this->expectException(ManifestMalformed::class);

        Manifest::fromJson('not json');
    }

    public function testANonStringVersionIsRefused(): void
    {
        $this->expectException(ManifestMalformed::class);
        $this->expectExceptionMessage('non-string version');

        Manifest::fromJson('{"src/front.ts":{"file":"assets/front.js","version":7}}');
    }

    public function testAnInvalidStylesListIsRefused(): void
    {
        $this->expectException(ManifestMalformed::class);
        $this->expectExceptionMessage('invalid css list');

        Manifest::fromJson('{"src/front.ts":{"file":"assets/front.js","css":"assets/front.css"}}');
    }

    public function testAnEntryThatIsNotAnObjectIsRefused(): void
    {
        $this->expectException(ManifestMalformed::class);
        $this->expectExceptionMessage('is not an object');

        Manifest::fromJson('{"src/front.ts":"assets/front.js"}');
    }

    private function fixture(): Manifest
    {
        $contents = \file_get_contents(\dirname(__DIR__, 2).'/fixtures/manifest/build.json');
        self::assertIsString($contents);

        return Manifest::fromJson($contents);
    }
}
