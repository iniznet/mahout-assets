<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Unit;

use Iniznet\Mahout\Assets\AssetBudget;
use Iniznet\Mahout\Assets\Exception\InvalidAssetBudget;
use Iniznet\Mahout\Assets\Tests\TestCase;

/**
 * @internal
 */
final class AssetBudgetTest extends TestCase
{
    public function testTheDefaultBudgetIsTheCorpusBudget(): void
    {
        $budget = AssetBudget::default();

        self::assertSame(51_200, $budget->cssBytes);
        self::assertSame(61_440, $budget->jsBytes);
    }

    public function testAZeroCssBudgetIsRefused(): void
    {
        $this->expectException(InvalidAssetBudget::class);
        $this->expectExceptionMessage('CSS budget must be a positive number of bytes');

        new AssetBudget(cssBytes: 0, jsBytes: 61_440);
    }

    public function testANegativeJavascriptBudgetIsRefused(): void
    {
        $this->expectException(InvalidAssetBudget::class);
        $this->expectExceptionMessage('JavaScript budget must be a positive number of bytes');

        new AssetBudget(cssBytes: 51_200, jsBytes: -1);
    }
}
