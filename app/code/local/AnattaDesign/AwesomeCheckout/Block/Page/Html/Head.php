<?php
/**
 * This file is part of AwesomeCheckout.
 *
 * AwesomeCheckout is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AwesomeCheckout is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AwesomeCheckout.  If not, see <http://www.gnu.org/licenses/>.
 */

class AnattaDesign_AwesomeCheckout_Block_Page_Html_Head extends Mage_Page_Block_Html_Head {

	public function getWhitelistedCssJsItems() {
		$default = array(
			'prototype/prototype.js',
			'lib/ccard.js',
			'prototype/validation.js',
			'scriptaculous/builder.js',
			'scriptaculous/effects.js',
			'scriptaculous/dragdrop.js',
			'scriptaculous/controls.js',
			'scriptaculous/slider.js',
			'varien/js.js',
			'varien/form.js',
			'varien/menu.js',
			'mage/translate.js',
			'mage/cookies.js',
			'mage/directpost.js',
			'mage/captcha.js',
			'mage/centinel.js',
			'varien/weee.js',
			'lib/ds-sleight.js',
			// Magento Enterprise
			'js/enterprise/catalogevent.js',
			// PAYONE extension v3.1.3 (http://www.magentocommerce.com/magento-connect/payone-extension.html)
			'payone/core/client_api.js',
			'payone/core/creditcard.js',
			'payone/core/financing.js',
			'payone/core/onlinebanktransfer.js',
			'payone/core/wallet.js',
			'payone/core/addresscheck.js',
			// Braintree extension v0.6.7 (http://www.magentocommerce.com/magento-connect/braintree-6697.html)
			'braintree/braintree-1.3.4.js',
			// SAGEPAY extension v3.0.9 (http://www.magentocommerce.com/magento-connect/ebizmarts-sage-pay-suite-ce-sage-pay-approved.html)
			'sagepaysuite/css/growler/growler.css',
			'sagepaysuite/css/sagePaySuite_Checkout.css',
			'sagepaysuite/js/growler/growler.js',
			'sagepaysuite/direct.js',
			'sagepaysuite/common.js',
			'sagepaysuite/sagePaySuite.js',
			'sagepaysuite/js/sagePaySuite_Checkout.js',
			'sagepaysuite/livepipe/livepipe.js',
			'sagepaysuite/livepipe/window.js',
			// OGONE extension v13.07.10 (http://www.magentocommerce.com/magento-connect/ogone-9913.html)
			'netresearch/ops/payment.js',
			'css/ops.css',
			// Stripe extension (http://www.magentocommerce.com/magento-connect/stripe-6.html)
			'radweb/stripe/stripe.js',
			// AwesomeCheckout
			'anattadesign/awesomecheckout/jquery-1.7.2.min.js',
			'anattadesign/awesomecheckout/jquery-ui-1.8.20.custom.min.js',
			'anattadesign/awesomecheckout/jquery.maskedinput-1.3.min.js',
			'anattadesign/awesomecheckout/jquery.validate.min.js',
			'anattadesign/awesomecheckout/jquery.validate.creditcard2-1.0.1.js',
			'anattadesign/awesomecheckout/opcheckout.js',
			'css/anattadesign/awesomecheckout/ui-lightness/jquery-ui-1.8.20.custom.css',
			'css/anattadesign/awesomecheckout/styles.css'
		);

		$userDefinedWhitelist = explode( "\n", Mage::getStoreConfig( 'awesomecheckout/advanced/whitelisted_css_js' ) );

		// remove empty entries (handle the case when user enters extra new lines in admin field, as well as the empty entry added by explode in case of admin field being empty)
		$userDefinedWhitelist = array_filter( array_map( 'trim', $userDefinedWhitelist ) );

		return array_merge( $default, $userDefinedWhitelist );
	}

	public function getCssJsHtml() {
		if ( $this->getRequest()->getModuleName() == 'anattadesign_awesomecheckout' && $this->getRequest()->getControllerName() == 'onepage' && $this->getRequest()->getActionName() != 'success' ) {
			$whitelisted = $this->getWhitelistedCssJsItems();
			foreach ( $this->_data[ 'items' ] as $item ) {
				if ( in_array( $item[ 'name' ], $whitelisted ) ) {
					continue; // Don't touch it, let it rain over me
				} else {
					$this->removeItem( $item[ 'type' ], $item[ 'name' ] );
				}
			}
		}

		return parent::getCssJsHtml();
	}
}
