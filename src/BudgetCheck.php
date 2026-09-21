<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

use Iniznet\Mahout\Assets\Contracts\AssetSize;

/**
 * The build-time size check. It measures every manifest entry and reports each
 * one over its ceiling; a breach fails the gate.
 */
final readonly class BudgetCheck
{
    public function __construct(private AssetSize $sizes)
    {
    }

    public function examine(Manifest $manifest, AssetBudget $budget): BudgetVerdict
    {
        $breaches = [];

        foreach ($manifest->entries() as $entry) {
            $javascript = $this->sizes->bytes($entry->file);
            if ($javascript > $budget->jsBytes) {
                $breaches[] = new BudgetBreach(
                    source: $entry->source,
                    kind: 'JavaScript',
                    measured: $javascript,
                    allowed: $budget->jsBytes,
                );
            }

            $stylesheet = 0;
            foreach ($entry->styles as $style) {
                $stylesheet += $this->sizes->bytes($style);
            }

            if ($stylesheet > $budget->cssBytes) {
                $breaches[] = new BudgetBreach(
                    source: $entry->source,
                    kind: 'CSS',
                    measured: $stylesheet,
                    allowed: $budget->cssBytes,
                );
            }
        }

        return new BudgetVerdict($breaches);
    }
}
