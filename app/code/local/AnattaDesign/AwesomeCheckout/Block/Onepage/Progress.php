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

class AnattaDesign_AwesomeCheckout_Block_Onepage_Progress extends Mage_Checkout_Block_Onepage_Progress {

	public function _beforeToHtml() {
		$section = $this->getRequest()->getParam( 'section', false );
		switch( $section ) {
			case 'shipping':
				$this->getCheckout()->setStepData( 'shipping', 'complete', false );
			case 'billing':
				$this->getCheckout()->setStepData( 'billing', 'complete', false );
			case 'payment':
				$this->getCheckout()->setStepData( 'payment', 'complete', false );
				$this->getCheckout()->setStepData( 'shipping', 'complete', true );
		}
	}

	public function getActive() {
		if( Mage::helper( 'anattadesign_awesomecheckout' )->isVirtualOnly() ) {
			$active = $this->getRequest()->getParam( 'section', 'billing' );
		} else {
			$active = $this->getRequest()->getParam( 'section', 'shipping' );
		}

		return $active;
	}

	public function getShippingAddressHtml() {
		$address = $this->getShipping();
		$data = array(
			Mage::helper( 'anattadesign_awesomecheckout' )->getFullname( $address ),
			$address->getStreetFull(),
			$address->getCity() . ', ' . $address->getCountryModel()->getIso3Code() . ' ' . $address->getPostcode()
		);

		return join( '<br/>', $data );
	}

}