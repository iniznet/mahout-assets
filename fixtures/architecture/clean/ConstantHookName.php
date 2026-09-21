<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Fixtures\Architecture\Clean;

use Iniznet\Mahout\Assets\Hooks;

/**
 * The nearest legal neighbour: the same emit site with a declared constant.
 */
final class ConstantHookName
{
    public function attach(): void
    {
        \add_action(Hooks::BEFORE_ENQUEUE, static function (): void {
        });
    }
}
