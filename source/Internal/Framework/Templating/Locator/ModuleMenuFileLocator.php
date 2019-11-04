<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Framework\Templating\Locator;

use OxidEsales\EshopCommunity\Internal\Framework\Config\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Config\DataObject\ShopConfigurationSetting;
use OxidEsales\EshopCommunity\Internal\Framework\Dao\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Path\ModulePathResolverInterface;
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
     * @var ShopConfigurationSettingDaoInterface
     */
    private $shopConfigurationSettingDao;

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
     * ModuleMenuFileLocator constructor.
     *
     * @param ShopConfigurationSettingDaoInterface $shopConfigurationSettingDao
     * @param ModulePathResolverInterface          $modulePathResolver
     * @param Filesystem                           $fileSystem
     * @param ContextInterface                     $context
     */
    public function __construct(
        ShopConfigurationSettingDaoInterface $shopConfigurationSettingDao,
        ModulePathResolverInterface $modulePathResolver,
        Filesystem $fileSystem,
        ContextInterface $context
    ) {
        $this->shopConfigurationSettingDao = $shopConfigurationSettingDao;
        $this->modulePathResolver = $modulePathResolver;
        $this->context = $context;
        $this->fileSystem = $fileSystem;
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
        try {
            $activeModuleSettings = $this->getActiveModulesShopConfigurationSetting($shopId);
            $activeModuleIds = $activeModuleSettings->getValue();
            $menuFiles = $this->getActiveModuleMenuFiles($activeModuleIds, $shopId);
        } catch (EntryDoesNotExistDaoException $exception) {
            return [];
        }
        return $menuFiles;
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
     * @return ShopConfigurationSetting
     *
     * @throws EntryDoesNotExistDaoException
     */
    private function getActiveModulesShopConfigurationSetting(int $shopId): ShopConfigurationSetting
    {
        return $this->shopConfigurationSettingDao->get(
            ShopConfigurationSetting::ACTIVE_MODULES,
            $shopId
        );
    }
}
