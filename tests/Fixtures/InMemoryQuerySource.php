<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Fixtures;

use Iniznet\Mahout\Kernel\Contracts\QuerySource;

/**
 * A query source that reports whatever a test declares, so Diagnostics needs no
 * database.
 *
 * @internal
 */
final class InMemoryQuerySource implements QuerySource
{
    /**
     * @param list<string> $statements
     */
    public function __construct(
        private int $count = 0,
        private array $statements = [],
    ) {
    }

    public function count(): int
    {
        return $this->count;
    }

    /** @return list<string> */
    public function statements(): array
    {
        return $this->statements;
    }
}
