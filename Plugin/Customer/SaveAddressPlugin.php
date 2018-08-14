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

namespace Pyxl\SmartyStreets\Plugin\Customer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\InputException;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Pyxl\SmartyStreets\Model\Validator;
use Pyxl\SmartyStreets\Helper\Config;

class SaveAddressPlugin
{

    /**
     * @var Validator
     */
    private $validator;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    private $regionCollectionFactory;
    /**
     * @var \Magento\Customer\Api\Data\RegionInterfaceFactory
     */
    private $regionDataFactory;
    /**
     * @var Config
     */
    private $config;

    /**
     * SaveAddressPlugin constructor.
     *
     * @param Validator $validator
     * @param CollectionFactory $regionCollectionFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param Config $config
     */
    public function __construct(
        Validator $validator,
        CollectionFactory $regionCollectionFactory,
        RegionInterfaceFactory $regionDataFactory,
        Config $config
    )
    {
        $this->validator = $validator;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->config = $config;
    }

    /**
     * Before saving the address we want to validate it using SmartyStreets.
     * If it is valid we will update the address with the first candidate returned
     * to ensure proper format.
     * If failed we throw an error to be displayed the controller calling save.
     *
     * @param AddressRepositoryInterface $subject
     * @param callable $proceed
     * @param AddressInterface $address
     *
     * @return \Magento\Customer\Api\Data\AddressInterface
     * @throws InputException
     */
    public function aroundSave(
        AddressRepositoryInterface $subject,
        callable $proceed,
        AddressInterface $address
    ) 
    {
        if ($this->config->isModuleEnabled()) {
            $results = $this->validator->validate($address);
            if ($results['valid']) {
                $firstCandidate = $results['candidates'][0];
                $street = $address->getStreet();

                // US and Intl have different Candidate models
                if ($address->getCountryId() == "US") {
                    /** @var \SmartyStreets\PhpSdk\US_Street\Candidate $firstCandidate */
                    $street[0] = $firstCandidate->getDeliveryLine1();
                    $street[1] = $firstCandidate->getDeliveryLine2();
                    $components = $firstCandidate->getComponents();
                    $address->setPostcode($components->getZipcode() . '-' . $components->getPlus4Code());
                    $address->setCity($components->getCityName());
                    if ($address->getRegion()->getRegionCode() !== $components->getStateAbbreviation()) {
                        $this->setRegion($components->getStateAbbreviation(), $address);
                    }
                    $metaData = $firstCandidate->getMetadata();
                    if ($county = $metaData->getCountyName()) {
                        $address->setCustomAttribute('county', $county);
                    }
                } else {
                    /** @var \SmartyStreets\PhpSdk\International_Street\Candidate $firstCandidate */
                    $street[0] = $firstCandidate->getAddress1();
                    $street[1] = $firstCandidate->getAddress2();
                    $street[2] = $firstCandidate->getAddress3();
                    $components = $firstCandidate->getComponents();
                    $address->setPostcode($components->getPostalCode());
                    $address->setCity($components->getLocality());
                    if ($address->getRegion()->getRegionCode() !== $components->getAdministrativeArea()) {
                        $this->setRegion($components->getAdministrativeArea(), $address);
                    }
                }
                $address->setStreet($street);

            } else {
                throw new InputException($results['message']);
            }
        }
        return $proceed($address);
    }

    /**
     * Set Region on address from code
     *
     * @param string $regionCode
     * @param AddressInterface $address
     *
     * @return void
     */
    private function setRegion($regionCode, AddressInterface $address)
    {
        $collection = $this->regionCollectionFactory->create();
        /** @var \Magento\Directory\Model\Region $region */
        $region = $collection
            ->addRegionCodeFilter($regionCode)
            ->getFirstItem();
        if ($region) {
            $regionData = $this->regionDataFactory->create();
            $regionData->setRegionCode($region->getCode());
            $regionData->setRegionId($region->getRegionId());
            $regionData->setRegion($region->getDefaultName());
            $address->setRegion($regionData);
        }
        return;
    }

}