<?php
/**
 * This file is part of OXID eShop Community Edition.
 *
 * OXID eShop Community Edition is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Community Edition is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2016
 * @version   OXID eShop CE
 */

namespace OxidEsales\EshopCommunity\Application\Controller;

use oxRegistry;

/**
 * Interesting, useful links window.
 * Arranges interesting links window (contents may be changed in
 * administrator GUI) with short link description and URL. OXID
 * eShop -> LINKS.
 */
class LinksController extends \OxidEsales\Eshop\Application\Controller\FrontendController
{

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = 'page/info/links.tpl';

    /**
     * Links list.
     *
     * @var object
     */
    protected $_oLinksList = null;

    /**
     * Template variable getter. Returns links list
     *
     * @return object
     */
    public function getLinksList()
    {
        if ($this->_oLinksList === null) {
            $this->_oLinksList = false;
            // Load links
            $oLinksList = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class);
            $oLinksList->init("oxlinks");
            $oLinksList->getList();
            $this->_oLinksList = $oLinksList;
        }

        return $this->_oLinksList;
    }

    /**
     * Returns Bread Crumb - you are here page1/page2/page3...
     *
     * @return array
     */
    public function getBreadCrumb()
    {
        $aPaths = [];
        $aPath = [];
        $iBaseLanguage = \OxidEsales\Eshop\Core\Registry::getLang()->getBaseLanguage();
        $aPath['title'] = \OxidEsales\Eshop\Core\Registry::getLang()->translateString('LINKS', $iBaseLanguage, false);
        $aPath['link'] = $this->getLink();

        $aPaths[] = $aPath;

        return $aPaths;
    }
}
