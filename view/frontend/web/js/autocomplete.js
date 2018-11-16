define(
    [
        'jquery',
        'uiComponent',
        'SmartyStreetsSDK',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'jquery/ui',
    ],
    function ($, Component, SmartyStreetsSDK, checkoutData, uiRegistry) {
        'use strict';

        return Component.extend({
            configData: null,
            usAutocompleteLookup: null,
            autocompleteClient: null,
            validationClient: null,
            usStreetLookup: null,
            isCheckout: true,
            addressFields: {
                'street1': 'street_1',
                'street2': 'street_2',
                'city': 'city',
                'region': 'region',
                'region_id': 'region_id',
                'postcode': 'zip',
                'county': 'county'
            },

            initialize: function () {
                let self = this;
                this._super();

                this.configData = (this.isCheckout)
                    ? window.checkoutConfig.smartystreets
                    : window.autocompleteConfig.smartystreets;

                /** Init SmartyStreets **/
                //autocomplete
                const SmartyStreetsCore = SmartyStreetsSDK.core;
                this.usAutocompleteLookup = SmartyStreetsSDK.usAutocomplete.Lookup;

                // Add your credentials to a credentials object.
                // let autoCompleteCredentials = new SmartyStreetsCore.SharedCredentials('<?php echo $block->getSiteKey() ?>');
                let autoCompleteCredentials = new SmartyStreetsCore.SharedCredentials(this.configData.website_key);
                let autocompleteClientBuilder = new SmartyStreetsCore.ClientBuilder(autoCompleteCredentials);
                this.autocompleteClient = autocompleteClientBuilder.buildUsAutocompleteClient(); //todo add intl lookup

                // validation
                this.usStreetLookup = SmartyStreetsSDK.usStreet.Lookup;
                let validationClientBuilder = new SmartyStreetsCore.ClientBuilder(autoCompleteCredentials);
                this.validationClient = validationClientBuilder.buildUsStreetApiClient();

                // set field names if is checkout
                if (this.isCheckout) {
                    uiRegistry.async('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset')(function (fieldset) {
                        fieldset.elems().forEach(function(elem){
                            if (elem.index === 'street') {
                                let street = elem.elems();
                                if (street.length) {
                                    self.addressFields.street1 = street[0].uid;
                                }
                                if (street.length > 1) {
                                    self.addressFields.street2 = street[1].uid;
                                }
                            } else if (elem.index in self.addressFields) {
                                self.addressFields[elem.index] = elem.uid;
                            }
                        });
                        self.initAutocomplete();
                    });
                } else {
                    this.initAutocomplete();
                }

                return this;

            },

            /**
             * Init the autocomplete functionality on the given field
             */
            initAutocomplete: function () {
                let self = this;
                let initialized = false;
                $(document).delegate('#' + this.addressFields.street1, "focus", function() {
                    if (!initialized) {
                        $(this)
                            .autocomplete({
                                classes: {
                                    "ui-autocomplete": "ui-autocomplete smartystreets-autocomplete" // y no work???
                                },
                                source: function (request, response) {
                                    let term = request.term;
                                    self.lookupAddress(term, response);
                                },
                                select: function (event, ui) {
                                    self.selectAddress(ui.item.value);
                                    return false;
                                }
                            })
                            .attr("autocomplete", "smartystreets")
                            // using this since "classes" is not working above
                            .autocomplete("widget").addClass('smartystreets-autocomplete');
                        initialized = true;
                    }
                });
            },

            /**
             * Search for addresses from partial typed
             *
             * @param {string} partialAddress
             * @param {function} jqueryResponse
             */
            lookupAddress: function (partialAddress, jqueryResponse) {
                let lookup = new this.usAutocompleteLookup(partialAddress);

                this.autocompleteClient.send(lookup)
                    .then(showResults)
                    .catch(handleError);

                /**
                 * Display list of options for autocomplete
                 *
                 * @param {object} response
                 */
                function showResults(response) {
                    let addresses = [];
                    if (response.result && Array.isArray(response.result)) {
                        addresses = $.map(response.result, function (value, key) {
                            return {
                                label: value.text,
                                value: value.text
                            }
                        });
                    }
                    jqueryResponse(addresses);
                }

                /**
                 * TODO Decide how to handle failures.
                 *
                 * @param {object} response
                 */
                function handleError(response) {
                    console.log(response);
                }
            },

            /**
             * Validate selected address and fill in the appropriate fields with response
             *
             * @param {string} address
             */
            selectAddress: function (address) {
                let self = this;
                let lookup1 = new this.usStreetLookup();
                lookup1.street = address;

                // Send the lookup from the client and handle the response.
                this.validationClient.send(lookup1)
                    .then(handleSuccess)
                    .catch(handleError);

                /**
                 * Fill out all appropriate fields from response components
                 *
                 * @param {object} response
                 */
                function handleSuccess(response) {
                    response.lookups.map(function(lookup, index) {
                        $('#autocomplete-error').remove();
                        if (lookup.result.length > 0) {
                            let result = lookup.result[0];
                            let components = result.components;
                            $('#'+self.addressFields.street1).val(result.deliveryLine1).change();
                            $('#'+self.addressFields.street2).val(result.deliveryLine2).change();
                            $('#'+self.addressFields.city).val(components.cityName).change();
                            $('#'+self.addressFields.state).val(components.state).change();
                            if (components.state in self.configData.regions) {
                                $('#'+self.addressFields.region_id).val(self.configData.regions[components.state]).change();
                            }
                            $('#'+self.addressFields.postcode).val(components.zipCode+'-'+components.plus4Code).change();
                            let metaData = result.metadata;
                            if (metaData && metaData.hasOwnProperty('countyName')) {
                                $('#'+self.addressFields.county).val(metaData.countyName).change();
                            }
                        } else {
                            $('#'+self.addressFields.street1).val(address).change();
                            $('<div id="autocomplete-error" generated="true" class="mage-error">Your address could not be validated. Please try to enter a valid address. If you continue to experience issues, please contact <a href="mailto:ecommsales@curvature.com">eCommSales@Curvature.com</a>. </div>').insertAfter('#'+self.addressFields.street1);
                        }
                    });

                }

                /**
                 * TODO Decide how to handle failures.
                 *
                 * @param {object} response
                 */
                function handleError(response) {
                    console.log("Error", response);
                }
            },

        });

    }
);