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

use SmartyStreets\PhpSdk\Exceptions\SmartyException;
use SmartyStreets\PhpSdk\StaticCredentials;
use SmartyStreets\PhpSdk\ClientBuilder;
use SmartyStreets\PhpSdk\US_Street\Lookup as UsLookup;
use SmartyStreets\PhpSdk\International_Street\Lookup as IntLookup;

class Validator
{

    //region Properties

    /**
     * @var \Pyxl\SmartyStreets\Helper\Config
     */
    private $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    //endregion

    /**
     * Validator constructor.
     *
     * @param \Pyxl\SmartyStreets\Helper\Config $config
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Pyxl\SmartyStreets\Helper\Config $config,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->config = $config;
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

        $client = new ClientBuilder($this->getCredentials());

        // Build different client and lookup for US vs International
        $street = $address->getStreet();
        if ($address->getCountryId() === "US") {
            $client = $client->buildUsStreetApiClient();
            $lookup = new UsLookup();
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
            $lookup = new IntLookup();
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
                    'Your address could not be validated. Please try to enter a valid address. If you continue to experience issues, please contact <a href="mailto:ecommsales@curvature.com">eCommSales@Curvature.com</a>'
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
        $staticCredentials = new StaticCredentials(
            $this->config->getAuthId(),
            $this->config->getAuthToken()
        );
        return $staticCredentials;
    }

}