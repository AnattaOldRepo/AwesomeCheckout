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

class AnattaDesign_AwesomeCheckout_Block_Onepage extends Mage_Checkout_Block_Onepage_Abstract {

	public function getSteps() {
		$steps = array( );

		if ( Mage::helper( 'anattadesign_awesomecheckout' )->isVirtualOnly() ) {
			$stepCodes = array( 'billing', 'payment', 'review' );

			// Show billing step first
			$this->getCheckout()->setStepData( 'billing', 'allow', true );
		} else {
			$stepCodes = array( 'shipping', 'shipping_method', 'payment', 'review' );

			// Show shipping step first
			$this->getCheckout()->setStepData( 'shipping', 'allow', true );
		}

		foreach ( $stepCodes as $step ) {
			$steps[$step] = $this->getCheckout()->getStepData( $step );
		}

		return $steps;
	}

	public function getActiveStep() {
		if ( Mage::helper( 'anattadesign_awesomecheckout' )->isVirtualOnly() ) {
			return 'billing';
		}
		return 'shipping';
	}

}