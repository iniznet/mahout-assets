<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

/**
 * One measured asset over its ceiling.
 */
final readonly class BudgetBreach
{
    public function __construct(
        public string $source,
        public string $kind,
        public int $measured,
        public int $allowed,
    ) {
    }

    public function describe(): string
    {
        return \sprintf(
            '%s exceeds the %s budget: %d > %d gzipped bytes',
            $this->source,
            $this->kind,
            $this->measured,
            $this->allowed,
        );
    }
}
