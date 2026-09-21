<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Contracts;

/**
 * The transfer size of one built asset, in bytes. The build-time budget check
 * reads sizes through this boundary so it can be measured without a build.
 */
interface AssetSize
{
    /**
     * @param string $file the asset path, relative to the build root
     *
     * @throws \Iniznet\Mahout\Assets\Exception\FileUnreadable when unreadable
     */
    public function bytes(string $file): int;
}
