<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

/**
 * The build-time size check's result: every entry over its ceiling, or none.
 */
final readonly class BudgetVerdict
{
    /**
     * @param list<BudgetBreach> $breaches
     */
    public function __construct(public array $breaches)
    {
    }

    public function withinBudget(): bool
    {
        return [] === $this->breaches;
    }

    public function summary(): string
    {
        if ([] === $this->breaches) {
            return 'every entry is within budget';
        }

        return \implode('; ', \array_map(
            static fn (BudgetBreach $breach): string => $breach->describe(),
            $this->breaches,
        ));
    }
}
