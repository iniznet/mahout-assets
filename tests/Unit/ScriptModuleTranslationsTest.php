<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Unit;

use Iniznet\Mahout\Assets\Exception\InvalidScriptModuleTranslations;
use Iniznet\Mahout\Assets\ScriptModuleTranslations;
use Iniznet\Mahout\Assets\Tests\TestCase;

/**
 * @internal
 */
final class ScriptModuleTranslationsTest extends TestCase
{
    public function testItHoldsTheDomainAndPath(): void
    {
        $translations = new ScriptModuleTranslations(domain: 'howdah', path: '/srv/languages');

        self::assertSame('howdah', $translations->domain);
        self::assertSame('/srv/languages', $translations->path);
    }

    public function testAnEmptyDomainIsRefused(): void
    {
        $this->expectException(InvalidScriptModuleTranslations::class);
        $this->expectExceptionMessage('text domain must not be empty');

        new ScriptModuleTranslations(domain: '', path: '/srv/languages');
    }

    public function testAnEmptyPathIsRefused(): void
    {
        $this->expectException(InvalidScriptModuleTranslations::class);
        $this->expectExceptionMessage('translations path must not be empty');

        new ScriptModuleTranslations(domain: 'howdah', path: '');
    }
}
