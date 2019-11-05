<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal\Framework\Templating\Locator;

use org\bovigo\vfs\vfsStream;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ShopConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Path\ModulePathResolverInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\State\ModuleStateServiceInterface;
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
            $this->getModulePathResolverMock(),
            $this->getShopConfigurationDaoMock(),
            new ContextStub(),
            new Filesystem(),
            $this->getModuleStateServiceMock()
        );

        $expectedPath = $this->vfsStreamDirectory->url().'/modules/menuTestModule/' . $fileName;
        $this->assertSame([$expectedPath], $locator->locate());
    }

    public function testLocateWithoutAnyModuleActivated()
    {
        $fileName = 'menu.xml';
        $this->createModuleStructure($fileName);

        $shopConfigurationDao = $this->prophesize(ShopConfigurationDaoInterface::class);
        $shopConfigurationDao->get(1)->willReturn(new ShopConfiguration());
        $locator = new ModuleMenuFileLocator(
            $this->getModulePathResolverMock(),
            $shopConfigurationDao->reveal(),
            new ContextStub(),
            new Filesystem(),
            $this->getModuleStateServiceMock()
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

    private function getShopConfigurationDaoMock(): ShopConfigurationDaoInterface
    {
        $moduleConfiguration = new ModuleConfiguration();
        $moduleConfiguration
            ->setId('testModule')
            ->setPath('menuTestModule');

        $shopConfiguration = new ShopConfiguration();
        $shopConfiguration->addModuleConfiguration($moduleConfiguration);

        $dao = $this->prophesize(ShopConfigurationDaoInterface::class);
        $dao->get(1)->willReturn($shopConfiguration);

        return $dao->reveal();
    }

    private function getModuleStateServiceMock(): ModuleStateServiceInterface
    {
        $moduleStateService = $this->prophesize(ModuleStateServiceInterface::class);
        $moduleStateService->isActive('testModule', 1)->willReturn(true);
        return $moduleStateService->reveal();
    }

    private function createModuleStructure($fileName): void
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
