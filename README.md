# Pyxl_SmartyStreets
Using the SmartyStreet SDK this module offers address validation for customer addresses. 
The validation is performed when a customer adds/edits their address from the Customer Dashboard
as well as in the checkout process.

This module requires valid API credentials for SmartyStreets. 
You can find their pricing options [here](https://smartystreets.com/pricing)  

## Getting Started
To install this module run the following.

If you **don't** have Pyxl_Core installed already run this first:

    composer config repositories.pyxl-core git https://github.com/thinkpyxl/magento2-Pyxl_Core.git
    composer require pyxl/core:^1.0.0
    bin/magento module:enable Pyxl_Core
    
Then require this package:

    composer config repositories.pyxl-smartystreets git https://github.com/thinkpyxl/magento2-Pyxl_SmartyStreets.git
    composer require pyxl/module-smartystreets:^1.0.0
    bin/magento module:enable Pyxl_SmartyStreets
    bin/magento setup:upgrade
    bin/magento cache:clean 
    
    
## Settings  
Navigate to **Stores** -> **Configuration** -> **Pyxl** ->  **SmartyStreets** to enable 
this module and enter your Auth ID and Token. 

## Documentation
SmartyStreets PHP SDK Documentation can be found [here](https://smartystreets.com/docs/sdk/php)

## TODO
* Provide a selection list of valid addresses if more than 1 candidate returned. 

## Authors
* Joel Rainwater
