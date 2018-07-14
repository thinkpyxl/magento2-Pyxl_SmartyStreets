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

use Psr\Log\LoggerInterface;
use Pyxl\SmartyStreets\Helper\Config;
use SmartyStreets\PhpSdk\Exceptions\SmartyException;
use SmartyStreets\PhpSdk\StaticCredentialsFactory;
use SmartyStreets\PhpSdk\ClientBuilderFactory;
use SmartyStreets\PhpSdk\US_Street\LookupFactory as UsLookupFactory;
use SmartyStreets\PhpSdk\International_Street\LookupFactory as IntLookupFactory;

class Validator
{

    /**
     * @var \Pyxl\SmartyStreets\Helper\Config
     */
    private $config;
    /**
     * @var \SmartyStreets\PhpSdk\StaticCredentialsFactory
     */
    private $staticCredentialsFactory;
    /**
     * @var \SmartyStreets\PhpSdk\ClientBuilderFactory
     */
    private $clientBuilderFactory;
    /**
     * @var \SmartyStreets\PhpSdk\US_Street\LookupFactory
     */
    private $usStreetLookupFactory;
    /**
     * @var \SmartyStreets\PhpSdk\International_Street\LookupFactory
     */
    private $intStreetFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Validator constructor.
     *
     * @param Config $config
     * @param StaticCredentialsFactory $staticCredentialsFactory
     * @param ClientBuilderFactory $clientBuilderFactory
     * @param UsLookupFactory $usStreetLookupFactory
     * @param IntLookupFactory $intStreetFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        StaticCredentialsFactory $staticCredentialsFactory,
        ClientBuilderFactory $clientBuilderFactory,
        UsLookupFactory $usStreetLookupFactory,
        IntLookupFactory $intStreetFactory,
        LoggerInterface $logger
    )
    {
        $this->config = $config;
        $this->staticCredentialsFactory = $staticCredentialsFactory;
        $this->clientBuilderFactory = $clientBuilderFactory;
        $this->usStreetLookupFactory = $usStreetLookupFactory;
        $this->intStreetFactory = $intStreetFactory;
        $this->logger = $logger;
    }

    /**
     * Validates the given address using SmartyStreets API.
     * If valid returns all candidates
     * If not valid returns appropriate messaging
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return array
     */
    public function validate(\Magento\Customer\Api\Data\AddressInterface $address) 
    {
        $response = [
            'valid' => false,
            'candidates' => []
        ];

        /** @var \SmartyStreets\PhpSdk\ClientBuilder $client */
        $client = $this->clientBuilderFactory->create(
            ['signer' => $this->getCredentials()]
        );
        // Build different client and lookup for US vs International
        $street = $address->getStreet();
        if ($address->getCountryId() === "US") {
            $client = $client->buildUsStreetApiClient();
            /** @var \SmartyStreets\PhpSdk\US_Street\Lookup $lookup */
            $lookup = $this->usStreetLookupFactory->create();
            if ($street && !empty($street)) {
                $lookup->setStreet($street[0]);
                $lookup->setSecondary((count($street)>1) ? $street[1] : null);
            }
            if ($region = $address->getRegion()) {
                $lookup->setState($region->getRegionCode());
            }
            $lookup->setCity($address->getCity());
            $lookup->setZipcode($address->getPostcode());
        } else {
            $client = $client->buildInternationalStreetApiClient();
            /** @var \SmartyStreets\PhpSdk\International_Street\Lookup $lookup */
            $lookup = $this->intStreetFactory->create();
            if ($street && !empty($street)) {
                $lookup->setAddress1($street[0]);
                $lookup->setAddress2((count($street)>1) ? $street[1] : null);
                $lookup->setAddress3((count($street)>2) ? $street[2] : null);
            }
            if ($region = $address->getRegion()) {
                $lookup->setAdministrativeArea($region->getRegionCode());
            }
            $lookup->setLocality($address->getCity());
            $lookup->setPostalCode($address->getPostcode());
            $lookup->setCountry($address->getCountryId());
        }

        try {
            $client->sendLookup($lookup);
            /** @var \SmartyStreets\PhpSdk\US_Street\Candidate[]|\SmartyStreets\PhpSdk\International_Street\Candidate[] $result */
            $result = $lookup->getResult();
            // if no results it means address is not valid.
            if (empty($result)) {
                $response['message'] = __(
                    'The provided address is not valid and no substitutes could be located. Please try again.'
                );
            } else {
                $response['valid'] = true;
                $response['candidates'] = $result;
            }
        } catch (SmartyException $e) {
            // Received error back from API.
            $response['message'] = __($e->getMessage());
        } catch (\Exception $e) {
            $response['message'] = __(
                'There was an unknown error. Please try again later.'
            );
            $this->logger->error($e);
        }
        return $response;
    }

    /**
     * Build Credentials object for client
     *
     * @return \SmartyStreets\PhpSdk\StaticCredentials
     */
    private function getCredentials()
    {
        /** @var \SmartyStreets\PhpSdk\StaticCredentials $staticCredentials */
        $staticCredentials = $this->staticCredentialsFactory->create(
            [
                'authId' => $this->config->getAuthId(),
                'authToken' => $this->config->getAuthToken()
            ]
        );
        return $staticCredentials;
    }

}