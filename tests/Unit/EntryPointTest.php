<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Unit;

use Iniznet\Mahout\Assets\EntryContext;
use Iniznet\Mahout\Assets\EntryPoint;
use Iniznet\Mahout\Assets\Exception\EntryContextMissing;
use Iniznet\Mahout\Assets\Exception\EntryContextUnknown;
use Iniznet\Mahout\Assets\Exception\EntryPointMalformed;
use Iniznet\Mahout\Assets\Tests\TestCase;

/**
 * @internal
 */
final class EntryPointTest extends TestCase
{
    public function testADeclarationResolvesEveryField(): void
    {
        $entry = EntryPoint::fromArray([
            'handle' => 'howdah-app',
            'source' => 'src/front.ts',
            'context' => 'front',
            'dependencies' => ['@wordpress/interactivity'],
            'domain' => 'howdah',
            'translations_path' => '/srv/languages',
        ]);

        self::assertSame('howdah-app', $entry->handle);
        self::assertSame('src/front.ts', $entry->source);
        self::assertSame(EntryContext::Front, $entry->context);
        self::assertSame(['@wordpress/interactivity'], $entry->dependencies);
        self::assertNotNull($entry->translations);
        self::assertSame('howdah', $entry->translations->domain);
        self::assertSame('/srv/languages', $entry->translations->path);
    }

    public function testADeclarationWithoutADependenciesKeyDefaultsToNone(): void
    {
        $entry = EntryPoint::fromArray([
            'handle' => 'howdah-app',
            'source' => 'src/front.ts',
            'context' => 'front',
        ]);

        self::assertSame([], $entry->dependencies);
        self::assertNull($entry->translations);
    }

    public function testADeclarationWithoutAContextIsRefused(): void
    {
        $this->expectException(EntryContextMissing::class);
        $this->expectExceptionMessage('declares no context');

        EntryPoint::fromArray([
            'handle' => 'howdah-app',
            'source' => 'src/front.ts',
        ]);
    }

    public function testADeclarationWithAnUnknownContextIsRefused(): void
    {
        $this->expectException(EntryContextUnknown::class);
        $this->expectExceptionMessage('unknown context "footer"');

        EntryPoint::fromArray([
            'handle' => 'howdah-app',
            'source' => 'src/front.ts',
            'context' => 'footer',
        ]);
    }

    public function testADeclarationWithoutAHandleIsRefused(): void
    {
        $this->expectException(EntryPointMalformed::class);
        $this->expectExceptionMessage('no non-empty handle');

        EntryPoint::fromArray(['source' => 'src/front.ts', 'context' => 'front']);
    }

    public function testADeclarationWithoutASourceIsRefused(): void
    {
        $this->expectException(EntryPointMalformed::class);
        $this->expectExceptionMessage('declares no manifest source');

        EntryPoint::fromArray(['handle' => 'howdah-app', 'context' => 'front']);
    }

    public function testNonStringDependenciesAreRefused(): void
    {
        $this->expectException(EntryPointMalformed::class);
        $this->expectExceptionMessage('not script module identifiers');

        /** @var array<string, string|list<string>> $declaration */
        $declaration = [
            'handle' => 'howdah-app',
            'source' => 'src/front.ts',
            'context' => 'front',
            'dependencies' => ['@wordpress/interactivity', 42],
        ];

        EntryPoint::fromArray($declaration);
    }

    public function testADomainWithoutAPathIsRefused(): void
    {
        $this->expectException(EntryPointMalformed::class);
        $this->expectExceptionMessage('text domain without a translations path');

        EntryPoint::fromArray([
            'handle' => 'howdah-app',
            'source' => 'src/front.ts',
            'context' => 'front',
            'domain' => 'howdah',
        ]);
    }
}
