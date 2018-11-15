<?php
/**
 * Pyxl_SmartyStreets
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2018 Pyxl, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Pyxl\SmartyStreets\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class AutocompleteConfigProvider implements ConfigProviderInterface
{

    /**
     * @var \Pyxl\SmartyStreets\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * AutocompleteConfigProvider constructor.
     *
     * @param \Pyxl\SmartyStreets\Helper\Config $configHelper
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     */
    public function __construct(
        \Pyxl\SmartyStreets\Helper\Config $configHelper,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
    )
    {
        $this->configHelper = $configHelper;
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config['smartystreets'] = [
            'website_key' => $this->configHelper->getSiteKey(),
            'regions' => $this->getRegions()
        ];
        return $config;
    }

    /**
     * Get all regions to lookup ID by Code
     *
     * @return array
     */
    private function getRegions()
    {
        $collection = $this->regionCollectionFactory->create()->load();
        $regions = [];
        /** @var \Magento\Directory\Model\Region $region */
        foreach ( $collection->getItems() as $region ) {
            if($region->getCountryId() == 'US') {
                $regions[$region->getCode()] = $region->getRegionId();
            }
        }
        return $regions;
    }

}