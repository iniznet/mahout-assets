<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Fixtures;

use Iniznet\Mahout\Assets\Contracts\AssetSize;

/**
 * Asset sizes a test declares, so the budget check needs no built files.
 *
 * @internal
 */
final readonly class InMemoryAssetSize implements AssetSize
{
    /**
     * @param array<string, int> $sizes
     */
    public function __construct(private array $sizes)
    {
    }

    public function bytes(string $file): int
    {
        return $this->sizes[$file] ?? 0;
    }
}
