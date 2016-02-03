Awesome-Checkout
================

Our magento extension for checkout.

Installation Notes
------------------

* Copy extension's code over magento's root directory, it will install itself.
* Installing it doesn't make any changes in the database except the module's entry in `core_resource` table.

Installation using Modman
-------------------------

* Install `modman`  
	You can follow the instructions at `git clone https://github.com/colinmollenhour/modman.git`
* Go to your magento root directory and initialize modman  
	`modman init`
* Then get AwesomeCheckout installed  
	`modman clone git@github.com:anattadesign/AwesomeCheckout.git`
* In magento admin, go to System > Configuration > Advanced > Developer > Template Settings. Change `Allow Symlinks` to `Yes`, if it's not already.

Upgradation Notes
-----------------

* Copy extension's code over magento's root directory, and its done.
* If you have modified any of the core files, then changes will be lost.
	* If you have made changes in translation files, then make sure not to copy over core translations files or else your changes will get lost. They are only meant for providing a starting point.

FAQ
---
**How to check if I installed the extension correctly**  
Go to `System > Configuration > Advanced > Advanced` and check if you see the module name `"AnattaDesign_AwesomeCheckout"` under `"Disable Modules Output"`.  
If you can't see it verify that the extension is installed and clear cache. You might need to re-compile if compilation is enabled.

**Is it safe to install this directly on the live site?**  
Never install directly on the live site.  
Magento extensions can often create issues or conflicts, even when extensions are using all the magento recommended practices. Please test on a staging site first.

**How can I configure this extension?**  
Available configurable options in magento admin are at `System > Configuration > Anatta Design > Awesome Checkout`

**How do I see AwesomeCheckout in action?**  
After the extension is installed, you should go to the default magento onepage checkout. AwesomeCheckout replaces it with its awesomeness.

**I installed the extension but now the checkout page is broken.**  
This is caused due to conflicts with other 3rd party extensions. First thing will be to check against Magento rewrite conflicts. Resolve any that you find.  
Next, enable Template Path Hints and see if the AwesomeCheckout templates get loaded correctly or if some other extension is overriding them.

**The checkout page loads, but there are some JS errors in the console.**  
AwesomeCheckout relies heavily on Javascript. It will automatically try to remove any other 3rd party javascript from the checkout page that might cause conflicts. However, some themes add them directly in templates as opposed to magento best practices of using layout XML. Please view page source and remove any Javascript that doesn't belong to magento or AwesomeCheckout.

**My payment extension is not supported by AwesomeCheckout.**  
AwesomeCheckout, by default, supports some major payment extensions. But if your payment method is not supported, you will have to ask a magento developer to integrate it.

**I need to customize AwesomeCheckout.**  
Some minor customizations regarding header/footer are supported via magento admin settings. However, if you need any other customizations, you will have to ask a magento developer to do these for you.

**I want to customize AwesomeCheckout for my store?**  
We do not take customization requests for AwesomeCheckout. However, the extension is fully open source, and you can hire any independent magento developer for them.

**Can I include custom css/js on the page?**  
There are fields for this in the Magento Admin Settings. You can also do it from the code level if you like.

**The postcode auto-completion is not accurate.**  
AwesomeCheckout, by default, uses google API to "guess" the city/state/country. It is accurate in most cases but not always. If you would like to implement your own logic, please look at `AnattaDesign_AwesomeCheckout_OnepageController::postcodeAddressAction()`

**Can I disable postcode auto-completion for certain postcodes?**  
Please take a look at checkout.postcodeAddress() function in the Javascript. You can replace it with an empty function. If you want to check, it can be found in `js/anattadesign/awesomecheckout/opcheckout.js`

**Can I completely disable postcode auto-completion?**  
This is available as a setting inside AwesomeCheckout configuration options in the admin panel.

**Phone number masking only works for some countries.**  
By default, we have only implemented phone masking for some countries. If you want to add/remove support for a country, please take a look at `checkout.setPhoneMasking()` function implemented in `js/anattadesign/awesomecheckout/opcheckout.js`. Once you change the masking, also look into `numberOfDigitsPerCountry()` function in the same file for validating the phone number length based on the country.

**I have another question not covered here.**  
Please [create a new issue](https://github.com/anattadesign/AwesomeCheckout/issues/new) on github and we will be glad to get in touch.
