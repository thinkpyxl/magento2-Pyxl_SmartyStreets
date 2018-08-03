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
    private $configHelper;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var \Pyxl\SmartyStreets\Model\AutocompleteConfigProvider
     */
    private $configProvider;

    /**
     * Autocomplete constructor.
     *
     * @param Template\Context $context
     * @param \Pyxl\SmartyStreets\Helper\Config $configHelper
     * @param \Pyxl\SmartyStreets\Model\AutocompleteConfigProvider $autocompleteConfigProvider
     * @param \Magento\Framework\Serialize\SerializerInterface $jsonSerializer
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Pyxl\SmartyStreets\Helper\Config $configHelper,
        \Pyxl\SmartyStreets\Model\AutocompleteConfigProvider $autocompleteConfigProvider,
        \Magento\Framework\Serialize\SerializerInterface $jsonSerializer,
        array $data = []
    )
    {
        $this->configHelper = $configHelper;
        $this->jsonSerializer = $jsonSerializer;
        $this->configProvider = $autocompleteConfigProvider;
        parent::__construct( $context, $data );
    }

    /**
     * @return bool|null
     */
    public function isEnabled()
    {
        return $this->configHelper->isAutocompleteEnabled();
    }

    /**
     * Returns serialized config data for address edit view
     *
     * @return bool|string
     */
    public function getSerializedAutocompleteConfig()
    {
        $config = $this->configProvider->getConfig();
        return $this->jsonSerializer->serialize($config);
    }

}