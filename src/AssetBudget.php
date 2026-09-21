<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

use Iniznet\Mahout\Assets\Exception\InvalidAssetBudget;

/**
 * The per-route asset ceilings: CSS and JavaScript, in gzipped bytes.
 *
 * The defaults are the corpus budgets — 50 KB of CSS and 60 KB of JavaScript
 * per route. A budget is a positive integer; zero or less is a declaration
 * error, not a strict budget.
 */
final readonly class AssetBudget
{
    private const int DEFAULT_CSS_BYTES = 51_200;
    private const int DEFAULT_JS_BYTES = 61_440;

    public function __construct(
        public int $cssBytes,
        public int $jsBytes,
    ) {
        if ($cssBytes < 1) {
            throw InvalidAssetBudget::forKind('CSS', $cssBytes);
        }

        if ($jsBytes < 1) {
            throw InvalidAssetBudget::forKind('JavaScript', $jsBytes);
        }
    }

    public static function default(): self
    {
        return new self(self::DEFAULT_CSS_BYTES, self::DEFAULT_JS_BYTES);
    }
}
