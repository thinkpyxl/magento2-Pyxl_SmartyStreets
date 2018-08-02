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

namespace Pyxl\SmartyStreets\Block\Address;

use Magento\Framework\View\Element\Template;

class Autocomplete extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Pyxl\SmartyStreets\Helper\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    private $regionCollectionFactory;

    public function __construct(
        Template\Context $context,
        \Pyxl\SmartyStreets\Helper\Config $config,
        \Magento\Framework\Serialize\SerializerInterface $jsonSerializer,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        array $data = []
    )
    {
        $this->config = $config;
        $this->jsonSerializer = $jsonSerializer;
        $this->regionCollectionFactory = $regionCollectionFactory;
        parent::__construct( $context, $data );
    }

    /**
     * @return null|string
     */
    public function getSiteKey()
    {
        return $this->config->getSiteKey();
    }

    public function getAuthToken()
    {
        return $this->config->getAuthToken();
    }

    public function getAuthId()
    {
        return $this->config->getAuthId();
    }

    /**
     * @return bool|null
     */
    public function isEnabled()
    {
        return $this->config->isAutocompleteEnabled();
    }

    /**
     * Get all regions to lookup ID by Code
     *
     * @return bool|string
     */
    public function getRegions()
    {
        $collection = $this->regionCollectionFactory->create()->load();
        $regions = [];
        /** @var \Magento\Directory\Model\Region $region */
        foreach ( $collection->getItems() as $region ) {
            $regions[$region->getCode()] = $region->getRegionId();
        }
        return $this->jsonSerializer->serialize($regions);
    }

    public function getValidateUrl()
    {
        return $this->_urlBuilder->getUrl('smartystreets/ajax/validate');
    }

}