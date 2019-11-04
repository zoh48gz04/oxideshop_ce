<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Framework\Templating\Locator;

/**
 * Interface FileLocatorInterface
 * @package OxidEsales\EshopCommunity\Internal\Framework\Templating\Locator
 */
interface FileLocatorInterface
{
    /**
     * Returns a full path for a given file name.
     *
     * @param string $name The file name to locate
     *
     * @return string|array The full path to the file or an array of file paths
     */
    public function locate($name);
}
