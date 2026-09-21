<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Fixtures\Architecture\Violations;

/**
 * A raw hook name at an emit site. It must fail mahout.arch.noRawHookName.
 */
final class RawHookName
{
    public function attach(): void
    {
        \add_action('howdah/assets/raw', static function (): void {
        });
    }
}
