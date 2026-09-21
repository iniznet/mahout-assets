<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

/**
 * Where an entry is served. Every entry declaration states one; there is no
 * default, so an entry that belongs nowhere cannot be declared.
 *
 * The context owns the core hook it enqueues on, which is the only mapping in
 * the package between an entry and a WordPress action.
 */
enum EntryContext: string
{
    case Front = 'front';
    case Admin = 'admin';
    case Editor = 'editor';

    /**
     * The core enqueue hook this context attaches to.
     */
    public function hook(): string
    {
        return match ($this) {
            self::Front => Hooks::WP_ENQUEUE_SCRIPTS,
            self::Admin => Hooks::ADMIN_ENQUEUE_SCRIPTS,
            self::Editor => Hooks::ENQUEUE_BLOCK_EDITOR_ASSETS,
        };
    }
}
