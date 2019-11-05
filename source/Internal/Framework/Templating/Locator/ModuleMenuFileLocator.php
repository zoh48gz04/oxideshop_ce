<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Framework\Templating\Locator;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Path\ModulePathResolverInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\State\ModuleStateServiceInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ModuleMenuFileLocator
 * @package OxidEsales\EshopCommunity\Internal\Framework\Templating\Locator
 */
class ModuleMenuFileLocator implements NavigationFileLocatorInterface
{
    /**
     * @var ModulePathResolverInterface
     */
    private $modulePathResolver;

    /**
     * @var ShopConfigurationDaoInterface
     */
    private $shopConfigurationDao;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $fileName = 'menu.xml';

    /**
     * @var ModuleStateServiceInterface
     */
    private $moduleStateService;

    /**
     * ModuleMenuFileLocator constructor.
     *
     * @param ModulePathResolverInterface   $modulePathResolver
     * @param ShopConfigurationDaoInterface $shopConfigurationDao
     * @param ContextInterface              $context
     * @param Filesystem                    $fileSystem
     * @param ModuleStateServiceInterface   $moduleStateService
     */
    public function __construct(ModulePathResolverInterface $modulePathResolver, ShopConfigurationDaoInterface $shopConfigurationDao, ContextInterface $context, Filesystem $fileSystem, ModuleStateServiceInterface $moduleStateService)
    {
        $this->modulePathResolver = $modulePathResolver;
        $this->shopConfigurationDao = $shopConfigurationDao;
        $this->context = $context;
        $this->fileSystem = $fileSystem;
        $this->moduleStateService = $moduleStateService;
    }

    /**
     * Returns a full path for a given file name.
     *
     * @return array An array of file paths
     *
     * @throws \Exception
     */
    public function locate()
    {
        $shopId = $this->context->getCurrentShopId();
        $activeModuleIds = $this->getActiveModuleIds($shopId);
        return $this->getActiveModuleMenuFiles($activeModuleIds, $shopId);
    }

    /**
     * @param array  $activeModuleIds
     * @param int    $shopId
     *
     * @return array
     */
    private function getActiveModuleMenuFiles(array $activeModuleIds, int $shopId): array
    {
        $menuFiles = [];
        foreach ($activeModuleIds as $activeModuleId) {
            $moduleMenuFile = $this->getModuleMenuFilePath($activeModuleId, $shopId);
            if ($this->fileSystem->exists($moduleMenuFile)) {
                $menuFiles[] = $moduleMenuFile;
            }
        }
        return $menuFiles;
    }

    /**
     * @param string $moduleId
     * @param int    $shopId
     *
     * @return string
     */
    private function getModuleMenuFilePath(string $moduleId, int $shopId): string
    {
        return $this->modulePathResolver->getFullModulePathFromConfiguration($moduleId, $shopId)
            . DIRECTORY_SEPARATOR . $this->fileName;
    }

    /**
     * @param int $shopId
     *
     * @return string[]
     */
    private function getActiveModuleIds(int $shopId): array
    {
        $activeModuleIds = [];
        $shopConfiguration = $this->shopConfigurationDao->get($shopId);
        foreach ($shopConfiguration->getModuleConfigurations() as $moduleConfiguration) {
            if ($this->moduleStateService->isActive($moduleConfiguration->getId(), $shopId)) {
                $activeModuleIds[] = $moduleConfiguration->getId();
            }
        }

        return $activeModuleIds;
    }
}
