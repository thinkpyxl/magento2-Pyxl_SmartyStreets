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

namespace Pyxl\SmartyStreets\Setup;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * UpgradeData constructor.
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Version 1.0.1
         * Creates "county" attribute
         */
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                'county',
                [
                    'input' => 'text',
                    'label' => 'County',
                    'visible' => 1,
                    'required' => 0,
                    'system' => 0,
                    'user_defined' => 1,
                    'position' => 115,
                    'sort_order' => 115,

                    'used_in_grid' => 0,
                    'visible_in_grid' => 0,
                    'filterable_in_grid' => 0,
                    'searchable_in_grid' => 0
                ]
            );

            // grab the attribute
            $county = $this->eavConfig->getAttribute(
                AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                'county'
            );
            // add to attribute set
            $eavSetup->addAttributeToSet(
                AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                2,
                $county->getId()
            );
            // Add to appropriate forms
            $county->setData(
                'used_in_forms',
                ['adminhtml_customer_address', 'customer_address_edit']
            );
            $county->save(); // using attribute repository to save did not save forms...

        }

        $setup->endSetup();
    }

}