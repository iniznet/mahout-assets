<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

/**
 * Every hook mahout-assets emits or observes. Names are declared once, here.
 *
 * The three core enqueue hooks are constants too: the rule that bans a raw hook
 * name at an emit site applies to a core hook as much as to a mahout one.
 */
final class Hooks
{
    /**
     * Core's front-end enqueue hook: the Front entry context.
     *
     * @since 1.0
     *
     * @action
     */
    public const string WP_ENQUEUE_SCRIPTS = 'wp_enqueue_scripts';

    /**
     * Core's admin enqueue hook: the Admin entry context.
     *
     * @since 1.0
     *
     * @action
     *
     * @param string $hookSuffix the current admin page's hook suffix
     */
    public const string ADMIN_ENQUEUE_SCRIPTS = 'admin_enqueue_scripts';

    /**
     * Core's block-editor enqueue hook: the Editor entry context.
     *
     * @since 1.0
     *
     * @action
     */
    public const string ENQUEUE_BLOCK_EDITOR_ASSETS = 'enqueue_block_editor_assets';

    /**
     * Filters the entry list resolved for one context, immediately before it is
     * registered. A filter may add, remove or reorder entries.
     *
     * @since 1.0
     *
     * @filter
     *
     * @param list<EntryPoint> $entries the entries declared for this context
     * @param EntryContext     $context the context being enqueued
     */
    public const string ENTRIES = 'mahout/assets/entries';

    /**
     * Fires after the entry list is filtered and before the first asset is
     * registered.
     *
     * @since 1.0
     *
     * @action
     *
     * @param EntryContext $context the context being enqueued
     */
    public const string BEFORE_ENQUEUE = 'mahout/assets/before_enqueue';

    /**
     * Fires after every entry in a context registered, with the handles that
     * were registered, in order.
     *
     * @since 1.0
     *
     * @action
     *
     * @param list<string> $handles the script module and style handles
     */
    public const string REGISTERED = 'mahout/assets/registered';

    /**
     * Fires when the build manifest cannot be read. Development rethrows after
     * this fires; production records a warning and serves nothing.
     *
     * @since 1.0
     *
     * @action
     *
     * @param string       $path    the manifest path that could not be read
     * @param EntryContext $context the context being enqueued
     */
    public const string MANIFEST_MISSING = 'mahout/assets/manifest_missing';
}
