<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Architecture;

use Iniznet\Mahout\Assets\Tests\TestCase;

/**
 * The negative proof for the script-module rule: the package never filters a
 * script tag into a module. The Script Modules API owns that, so no
 * script_loader_tag (or wp_script_attributes) filter exists anywhere in the
 * repository.
 *
 * The scan skips vendor/, the git directory and this file, because this file
 * must contain the needle it searches for.
 *
 * @internal
 */
final class NoScriptLoaderTagTest extends TestCase
{
    /** @var list<string> */
    private const FORBIDDEN = ['script_loader_tag', 'wp_script_attributes'];

    public function testNoScriptTagFilterExistsInTheRepository(): void
    {
        $root = \dirname(__DIR__, 2);
        $self = \realpath(__FILE__);
        $offenders = [];

        foreach ($this->codeFiles($root) as $file) {
            if ($self === \realpath($file)) {
                continue;
            }

            $contents = \file_get_contents($file);
            if (false === $contents) {
                continue;
            }

            foreach (self::FORBIDDEN as $needle) {
                if (\str_contains($contents, $needle)) {
                    $offenders[] = $file.' ('.$needle.')';
                }
            }
        }

        self::assertSame([], $offenders, 'A script tag filter exists: '.\implode(', ', $offenders));
    }

    /**
     * @return list<string>
     */
    private function codeFiles(string $root): array
    {
        $found = [];
        foreach (['src', 'bin', 'tests', 'fixtures'] as $directory) {
            foreach ($this->files($root.'/'.$directory) as $file) {
                $found[] = $file;
            }
        }

        return $found;
    }

    /**
     * @return list<string>
     */
    private function files(string $root): array
    {
        if (!\is_dir($root)) {
            return [];
        }

        $found = [];
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($items as $item) {
            if (!$item instanceof \SplFileInfo || !$item->isFile()) {
                continue;
            }

            $path = \str_replace('\\', '/', $item->getPathname());
            if (\str_contains($path, '/vendor/')
                || \str_contains($path, '/.git/')
                || \str_contains($path, '/node_modules/')
                || \str_contains($path, '/coverage/')
                || \str_contains($path, '/dist/')
                || \str_contains($path, '/build/')
                || \str_contains($path, 'cache')
            ) {
                continue;
            }

            $found[] = $item->getPathname();
        }

        return $found;
    }
}
