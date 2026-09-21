<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Unit;

use Iniznet\Mahout\Assets\AssetBudget;
use Iniznet\Mahout\Assets\BudgetCheck;
use Iniznet\Mahout\Assets\Internal\GzipFileSize;
use Iniznet\Mahout\Assets\Manifest;
use Iniznet\Mahout\Assets\Tests\Fixtures\InMemoryAssetSize;
use Iniznet\Mahout\Assets\Tests\TestCase;

/**
 * @internal
 */
final class BudgetCheckTest extends TestCase
{
    public function testAWithinBudgetManifestPasses(): void
    {
        $verdict = (new BudgetCheck(new InMemoryAssetSize([
            'assets/front.js' => 1_000,
            'assets/front.css' => 500,
        ])))->examine(
            Manifest::fromJson('{"src/front.ts":{"file":"assets/front.js","css":["assets/front.css"]}}'),
            AssetBudget::default(),
        );

        self::assertTrue($verdict->withinBudget());
        self::assertSame([], $verdict->breaches);
        self::assertSame('every entry is within budget', $verdict->summary());
    }

    public function testAnOverBudgetEntryIsReported(): void
    {
        $verdict = (new BudgetCheck(new InMemoryAssetSize([
            'assets/front.js' => 65_000,
            'assets/front.css' => 52_000,
        ])))->examine(
            Manifest::fromJson('{"src/front.ts":{"file":"assets/front.js","css":["assets/front.css"]}}'),
            AssetBudget::default(),
        );

        self::assertFalse($verdict->withinBudget());
        self::assertCount(2, $verdict->breaches);
        self::assertSame('JavaScript', $verdict->breaches[0]->kind);
        self::assertStringContainsString('src/front.ts exceeds the JavaScript budget', $verdict->summary());
        self::assertStringContainsString('src/front.ts exceeds the CSS budget', $verdict->summary());
    }

    public function testTheCommittedWithinBudgetFixturePasses(): void
    {
        $root = \dirname(__DIR__, 2).'/fixtures/budget/within';

        $verdict = (new BudgetCheck(new GzipFileSize($root)))->examine(
            Manifest::fromJson($this->read($root.'/manifest.json')),
            AssetBudget::default(),
        );

        self::assertTrue($verdict->withinBudget());
    }

    public function testTheCommittedOverBudgetFixtureFails(): void
    {
        $root = \dirname(__DIR__, 2).'/fixtures/budget/over';

        $verdict = (new BudgetCheck(new GzipFileSize($root)))->examine(
            Manifest::fromJson($this->read($root.'/manifest.json')),
            new AssetBudget(cssBytes: 51_200, jsBytes: 64),
        );

        self::assertFalse($verdict->withinBudget());
        self::assertSame(1, \count($verdict->breaches));
    }

    private function read(string $path): string
    {
        $contents = \file_get_contents($path);
        self::assertIsString($contents);

        return $contents;
    }
}
