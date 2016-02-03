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

class AnattaDesign_AwesomeCheckout_Block_Form_Cc extends Mage_Payment_Block_Form_Cc {

	protected function _construct() {
		parent::_construct();

		// Only replace the template on frontend side else we lose the ability to place orders from magento admin (The condition we are using doesn't work for all pages example like Magento connect pages, but definitely works in our context)
		if ( ! Mage::app()->getStore()->isAdmin() )
			$this->setTemplate( 'anattadesign/awesomecheckout/onepage/payment/form/cc.phtml' );
	}

}