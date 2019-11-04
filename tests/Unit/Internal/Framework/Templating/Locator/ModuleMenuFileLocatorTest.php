<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal\Framework\Templating\Locator;

use org\bovigo\vfs\vfsStream;
use OxidEsales\EshopCommunity\Internal\Framework\Config\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Config\DataObject\ShopConfigurationSetting;
use OxidEsales\EshopCommunity\Internal\Framework\Dao\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Path\ModulePathResolverInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\Locator\ModuleMenuFileLocator;
use OxidEsales\EshopCommunity\Tests\Unit\Internal\ContextStub;
use Symfony\Component\Filesystem\Filesystem;

class ModuleMenuFileLocatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var vfsStream */
    private $vfsStreamDirectory;

    public function testLocate()
    {
        $fileName = 'menu.xml';
        $this->createModuleStructure($fileName);
        $locator = new ModuleMenuFileLocator(
            $this->getShopSettingsDaoMock(),
            $this->getModulePathResolverMock(),
            new Filesystem(),
            new ContextStub()
        );

        $expectedPath = $this->vfsStreamDirectory->url().'/modules/menuTestModule/' . $fileName;
        $this->assertSame([$expectedPath], $locator->locate());
    }

    public function testLocateWithoutAnyModuleActivated()
    {
        $fileName = 'menu.xml';
        $this->createModuleStructure($fileName);

        /** @var ShopConfigurationSettingDaoInterface $shopSettingDao */
        $shopSettingDao = $this->getMockBuilder(ShopConfigurationSettingDaoInterface::class)->getMock();
        $shopSettingDao->method('get')->willThrowException(new EntryDoesNotExistDaoException());

        $locator = new ModuleMenuFileLocator(
            $shopSettingDao,
            $this->getModulePathResolverMock(),
            new Filesystem(),
            new ContextStub()
        );

        $this->assertSame([], $locator->locate());
    }

    /**
     * @return ModulePathResolverInterface
     */
    private function getModulePathResolverMock()
    {
        $pathToModuleMenuXml = $this->vfsStreamDirectory->url() . '/modules/menuTestModule';
        $pathResolver = $this->getMockBuilder(ModulePathResolverInterface::class)->getMock();
        $pathResolver->method('getFullModulePathFromConfiguration')->willReturn($pathToModuleMenuXml);

        return $pathResolver;
    }

    /**
     * @return ShopConfigurationSettingDaoInterface
     */
    private function getShopSettingsDaoMock()
    {
        $shopSetting = $this->getMockBuilder(ShopConfigurationSetting::class)->getMock();
        $shopSetting->method('getValue')->willReturn(['moduleId']);

        $shopSettingDao = $this->getMockBuilder(ShopConfigurationSettingDaoInterface::class)->getMock();
        $shopSettingDao->method('get')->willReturn($shopSetting);
        return $shopSettingDao;
    }

    private function createModuleStructure($fileName)
    {
        $structure = [
            'modules' => [
                'menuTestModule' => [
                    $fileName => '*this is menu xml for test*'
                ]
            ]
        ];

        $this->vfsStreamDirectory = vfsStream::setup('root', null, $structure);
    }
}
