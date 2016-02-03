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

class AnattaDesign_MultipleHandles_Model_Core_Layout_Update extends Mage_Core_Model_Layout_Update {

	public function fetchPackageLayoutUpdates( $handle ) {
		$_profilerKey = 'layout/package_update: ' . $handle;
		Varien_Profiler::start( $_profilerKey );
		if( empty( $this->_packageLayout ) ) {
			$this->fetchFileLayoutUpdates();
		}

		foreach( $this->_packageLayout->$handle as $updateXml ) {
			/** @var Mage_Core_Model_Layout_Element $updateXml */

			$handle = $updateXml->getAttribute( 'ifhandle' );
			if( $handle ) {
				$handle = explode( ' ', $handle );
				$handle = array_diff( $handle, $this->getHandles() );
				if( !empty( $handle ) ) {
					continue;
				}
			}

			$this->fetchRecursiveUpdates( $updateXml );
			$this->addUpdate( $updateXml->innerXml() );
		}
		Varien_Profiler::stop( $_profilerKey );

		return true;
	}
}