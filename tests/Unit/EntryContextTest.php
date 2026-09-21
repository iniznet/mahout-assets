<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Unit;

use Iniznet\Mahout\Assets\EntryContext;
use Iniznet\Mahout\Assets\Tests\TestCase;

/**
 * @internal
 */
final class EntryContextTest extends TestCase
{
    public function testEachContextMapsToItsCoreEnqueueHook(): void
    {
        self::assertSame('wp_enqueue_scripts', EntryContext::Front->hook());
        self::assertSame('admin_enqueue_scripts', EntryContext::Admin->hook());
        self::assertSame('enqueue_block_editor_assets', EntryContext::Editor->hook());
    }

    public function testTheContextSetIsClosed(): void
    {
        self::assertSame(['front', 'admin', 'editor'], array_column(EntryContext::cases(), 'value'));
    }
}
