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

namespace Pyxl\SmartyStreets\Block\Checkout\Onepage;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class AutocompleteProcessor implements LayoutProcessorInterface
{

    /**
     * @var \Pyxl\SmartyStreets\Helper\Config
     */
    private $configHelper;

    /**
     * AutocompleteProcessor constructor.
     *
     * @param \Pyxl\SmartyStreets\Helper\Config $configHelper
     */
    public function __construct(
        \Pyxl\SmartyStreets\Helper\Config $configHelper
    )
    {
        $this->configHelper = $configHelper;
    }

    /**
     * Process js Layout of block
     * Disable autocomplete component if disabled in config
     *
     * @param array $jsLayout
     *
     * @return array
     */
    public function process($jsLayout)
    {
        if (!$this->configHelper->isAutocompleteEnabled()) {
            $jsLayout['components']['checkout']['children']['autocomplete']['config']['componentDisabled'] = true;
        }
        return $jsLayout;
    }

}