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

namespace Pyxl\SmartyStreets\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{

    const XML_PATH_SMARTYSTREETS_SETTINGS   = 'smartystreets/settings';
    const XML_PATH_SMARTYSTREETS_ENABLED    = 'enabled';
    const XML_PATH_SMARTYSTREETS_AUTH_ID    = 'auth_id';
    const XML_PATH_SMARTYSTREETS_AUTH_TOKEN = 'auth_token';

    /**
     * @var array
     */
    private $settings = [];

    /**
     * Get all settings fields from configuration as array
     * Optionally get value from single field
     *
     * @param null|string $store
     * @param string $scopeType
     * @param string|null $field
     *
     * @return array|string|null
     */
    private function getSettings(
        $store = null,
        $scopeType = ScopeInterface::SCOPE_STORE,
        $field = null
    )
    {
        if (empty($this->settings)) {
            $this->settings = $this->scopeConfig->getValue(
                $this::XML_PATH_SMARTYSTREETS_SETTINGS,
                $scopeType,
                $store
            );
        }
        if ($field) {
            return isset($this->settings[$field]) ? $this->settings[$field] : null;
        }
        return $this->settings;
    }

    /**
     * Return whether this module is enabled
     *
     * @param null|string $store
     * @param string $scopeType
     *
     * @return bool|null
     */
    public function isModuleEnabled($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->getSettings($store, $scopeType, $this::XML_PATH_SMARTYSTREETS_ENABLED);
    }

    /**
     * Returns API Auth ID
     *
     * @param null|string $store
     * @param string $scopeType
     *
     * @return string|null
     */
    public function getAuthId($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->getSettings($store, $scopeType, $this::XML_PATH_SMARTYSTREETS_AUTH_ID);
    }

    /**
     * Returns API Auth Token
     *
     * @param null|string $store
     * @param string $scopeType
     *
     * @return string|null
     */
    public function getAuthToken($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->getSettings($store, $scopeType, $this::XML_PATH_SMARTYSTREETS_AUTH_TOKEN);
    }

}