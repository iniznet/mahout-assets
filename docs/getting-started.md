# Getting started

## Install

```bash
composer require iniznet/mahout-assets:^1.0
```

Requires PHP 8.4 and WordPress 7.1 or later, and depends on
`iniznet/mahout-kernel`.

## Ship your first entry

The composition root declares an `AssetsConfig`, names `AssetsProvider`,
and boots the kernel. Nothing is registered at file scope and nothing is read
from the filesystem at construction:

```php
use Iniznet\Mahout\Assets\AssetsConfig;
use Iniznet\Mahout\Assets\AssetsProvider;
use Iniznet\Mahout\Assets\DevMode;
use Iniznet\Mahout\Assets\EntryList;
use Iniznet\Mahout\Assets\Internal\FileManifestSource;
use Iniznet\Mahout\Kernel\Kernel;

$kernel = Kernel::inWordPress();

$kernel->service(new AssetsConfig(
    manifest: new FileManifestSource(get_theme_file_path('build/manifest.json')),
    entries: EntryList::fromDeclarations([
        [
            'handle' => 'howdah-app',
            'source' => 'src/front.ts',
            'context' => 'front',
            'dependencies' => ['@wordpress/interactivity'],
            'domain' => 'howdah',
            'translations_path' => get_theme_file_path('languages'),
        ],
    ]),
    baseUrl: get_theme_file_uri('build'),
    devMode: DevMode::fromConstant(),
));

$kernel->provider(AssetsProvider::class);
$kernel->boot();
```

The entry is enqueued by its context's core hook: `wp_enqueue_scripts` for
`front`, `admin_enqueue_scripts` for `admin`,
`enqueue_block_editor_assets` for `editor`. An entry that names no context
is refused — there is no default.

## Development mode

Development mode is one explicit constant, never a network probe:

```php
define('MAHOUT_ASSETS_DEV', true);
```

Defined and true, a missing manifest throws; otherwise a missing manifest
records a `warning`, fires `mahout/assets/manifest_missing` and serves
nothing — the theme runs correctly with no build, which is why the build is
never required for correctness.

## The build-time size gate

```bash
composer budget:check
```

Measures every manifest entry's gzipped CSS and JavaScript against per-route
ceilings (50 KB and 60 KB by default) and exits non-zero over budget.

## Failure modes

| Symptom | Cause |
|---|---|
| An entry is never enqueued | its `context` key is absent or misspelled — there is no default context |
| A missing manifest throws in development | `MAHOUT_ASSETS_DEV` is defined and true, and the build has not run |
| `budget:check` fails | an entry exceeds its per-route ceiling — shrink the entry or re-declare the ceiling explicitly |
