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

class AnattaDesign_AwesomeCheckout_Helper_Data extends Mage_Core_Helper_Abstract {

	protected $_geoip_record;
	protected $_config_cache = array( );

	/**
	 * Get config data
	 *
	 * @param string $xmlnode
	 */
	public function getConfigData( $xmlnode ) {
		if ( !isset( $this->_config_cache[$xmlnode] ) ) {
			$this->_config_cache[$xmlnode] = Mage::getStoreConfig( 'awesomecheckout/' . $xmlnode );
		}
		return $this->_config_cache[$xmlnode];
	}

	/**
	 * Determine if the current quote item only has virtual products in it.
	 */
	public function isVirtualOnly() {
		$cartItems = Mage::getSingleton( 'checkout/session' )->getQuote()->getAllItems();
		foreach ( $cartItems as $item ) {
			if ( !$item->getIsVirtual() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get GeopIP Record
	 */
	public function getGeoipRecord() {
		if ( is_null( $this->_geoip_record ) ) {

			if ( extension_loaded( 'mbstring' ) ) {

				$datafile = Mage::getBaseDir( 'media' ) . '/anattadesign/geoip/default/GeoLiteCity.dat';

				if ( is_readable( $datafile ) && is_file( $datafile ) ) {
					try {
						$this->_geoip_record = AnattaDesign_AwesomeCheckout_Model_GeoIP_Core::getInstance( $datafile, AnattaDesign_AwesomeCheckout_Model_GeoIP_Core::GEOIP_STANDARD )
								->geoip_record_by_addr( $_SERVER['REMOTE_ADDR'] );
					} catch ( Exception $e ) {
						$this->_geoip_record = false;
					}
				} else {
					$this->_geoip_record = false;
				}
			} else {
				$this->_geoip_record = false;
			}
		}

		return $this->_geoip_record;
	}

	/**
	 * Get fullname from address
	 * @param Mage_Customer_Model_Address_Abstract $address
	 * @return string
	 */
	public function getFullname( $address ) {
		$parts = array( );
		if ( $address->getFirstname() )
			$parts[] = $address->getFirstname();
		if ( $address->getMiddlename() )
			$parts[] = $address->getMiddlename();
		if ( $address->getLastname() )
			$parts[] = $address->getLastname();
		if(empty($parts))
			return trim( Mage::getSingleton( 'customer/session' )->getCustomer()->getName() );
		return trim( join( ' ', $parts ) );
	}

	/**
	 * Prepared address data:
	 *  - fullname
	 *  - telephone
	 *
	 * @param array $data
	 * @return array $data
	 */
	public function prepareAddressData( array $data ) {
		if ( isset( $data[ 'fullname' ] ) ) {
			$data = array_merge( $data, $this->getFullnameParts( $data[ 'fullname' ] ) );
			unset( $data[ 'fullname' ] );
		}
		if ( isset( $data[ 'telephone' ] ) ) {
			$data[ 'telephone' ] = preg_replace( '/\D/', '', $data[ 'telephone' ] );
		}
		if ( !$this->getConfigData( 'options/require_phone' ) ) {
			if ( empty( $data[ 'telephone' ] ) ) {
				$data[ 'telephone' ] = '0000000000';
			}
		}

		return $data;
	}

	/**
	 * Get fullname parts: firstname, lastname, middlename
	 *
	 * @param string $fullname
	 * @return array
	 */
	public function getFullnameParts( $fullname ) {
		$names = explode( " ", trim( preg_replace( "/\s\s+/", " ", $fullname ) ) );
		$parts['firstname'] = array_shift( $names );
		$parts['lastname'] = array_pop( $names );
		$parts['middlename'] = join( " ", $names );
		return $parts;
	}

	public function getAllowedCountryOptionById( $id ) {
		$options = Mage::getResourceModel( 'directory/country_collection' )->loadByStore()->toOptionArray();
		foreach ( $options as $option ) {
			if ( $option['value'] == $id ) {
				return $option;
			}
		}
		return null;
	}

	/**
	 * Check if customer email exists
	 *
	 * @param string $email
	 * @param int $websiteId
	 * @return false|Mage_Customer_Model_Customer
	 */
	public function customerEmailExists( $email, $websiteId = null ) {
		$customer = Mage::getModel( 'customer/customer' );
		if ( $websiteId ) {
			$customer->setWebsiteId( $websiteId );
		} else {
			$customer->setWebsiteId( Mage::app()->getWebsite()->getId() );
		}

		$customer->loadByEmail( trim( $email ) );

		if ( $customer->getId() ) {
			return $customer;
		}
		return false;
	}

	public function addCustomerPassword( array &$data, $password = NULL ) {
		if ( !$password ) {
			$password = Mage::getModel( 'customer/customer' )->generatePassword();
		}
		$data['customer_password'] = $password;
		$data['confirm_password'] = $password;
		$encrypted_password = Mage::getModel( 'customer/customer' )->encryptPassword( $password );
		Mage::getSingleton( 'checkout/type_onepage' )->getQuote()->setPasswordHash( $encrypted_password );

		return $data;
	}

	public function trailingslashit( $string ) {
		return $this->untrailingslashit( $string ) . '/';
	}

	public function untrailingslashit( $string ) {
		return rtrim( $string, '/' );
	}

	public function getLogoUrl() {
		$logo_url = Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_MEDIA , array( '_secure' => Mage::app()->getStore()->isCurrentlySecure() ) ) . 'anattadesign/logo/';
		if ( (string) Mage::getStoreConfig( 'awesomecheckout/options/logo' ) == '' )
			$logo_url .= 'anatta_logo.png';
		else
			$logo_url .= Mage::getStoreConfig( 'awesomecheckout/options/logo' );
		return $logo_url;
	}

	public function getEstimatedShipping() {
		return $this->_minimumShippingCost;
	}

	public function getRandomRegion( $countryCode ) {
		$regionsCollection = Mage::getResourceModel( 'directory/region_collection' )->addCountryFilter( $countryCode )->load();
		$regions = $regionsCollection->toArray();

		if ( $regions[ 'totalRecords' ] == 0 ) {
			return 'Region'; // return string "Region" instead of nothing, doesn't matter what we return
		}

		// array_rand returns a random key
		$selection = array_rand( $regions[ 'items' ] );

		return $regions['items'][$selection];
	}

	public function getShippingCodeByName( $name ) {
		$activeCarriers = Mage::getSingleton( 'shipping/config' )->getActiveCarriers();
		foreach ( $activeCarriers as $carrierCode => $carrierModel ) {
			if ( $carrierMethods = $carrierModel->getAllowedMethods() ) {
				foreach ( $carrierMethods as $methodCode => $method ) {
					if ( $method == $name ) {
						return $carrierCode . '_' . $methodCode;
					}
				}
			}
		}
		return false;
	}

	public function estimateShipping() {
		$geoip = $this->getGeoipRecord();
		$quote = Mage::getSingleton( 'checkout/session' )->getQuote();
		$needToSaveQuote = false;
		$this->_minimumShippingCost = false;

		// If the shipping method has already been selected by the customer, than we can safely ignore this whole process.
		$currentShippingMethod = $quote->getShippingAddress()->getShippingMethod();
		if ( $currentShippingMethod )
			return;

		$changedData = array();

		$shippingAddress = $quote->getShippingAddress();
		if ( $geoip || $shippingAddress->getCountryId() || Mage::helper( 'core' )->getDefaultCountry() ) {
			if ( $shippingAddress->getCountryId() == '' ) {
				if( $geoip && $geoip->country_code ) {
					$shippingAddress->setCountryId( $geoip->country_code );
					$changedData['setCountryId'] = '';
				} else {
					$shippingAddress->setCountryId( Mage::helper( 'core' )->getDefaultCountry() );
					$changedData['setCountryId'] = '';
				}
				$needToSaveQuote = true;
			}
			if ( $shippingAddress->getRegion() == '' ) {
				if ( !$geoip || empty( $geoip->region ) || !ctype_alpha( $geoip->region ) ) { // sometimes $geoip->region returns a number
					$random = $this->getRandomRegion( $shippingAddress->getCountryId() );
					if ( isset($random['region_id']) ) {
						$shippingAddress->setRegion( $random['region_id'] );
						$changedData['setRegion'] = '';
						$changedData['setRegionId'] = '';
					}
					$needToSaveQuote = true;
				} elseif( $geoip ) {
					$shippingAddress->setRegion( $geoip->region );
					$changedData['setRegion'] = '';
					$changedData['setRegionId'] = '';
					$needToSaveQuote = true;
				}
			}
			if ( $shippingAddress->getCity() == '' ) {
				if ( !$geoip || empty( $geoip->city ) ) {
					if ( !isset( $random ) ) {
						$random = $this->getRandomRegion( $shippingAddress->getCountryId() ); // Set Random Region as City
					}
					$shippingAddress->setCity( $random['name'] );
					$changedData['setCity'] = '';
					$needToSaveQuote = true;
				} elseif( $geoip ) {
					$shippingAddress->setCity( $geoip->city );
					$changedData['setCity'] = '';
					$needToSaveQuote = true;
				}
			}
			if ( $geoip && $shippingAddress->getPostcode() == '' ) {
				$shippingAddress->setPostcode( $geoip->postal_code );
				$changedData['setPostcode'] = '';
				$needToSaveQuote = true;
			}
			if ( $needToSaveQuote ) {
				$quote->getShippingAddress()->setCollectShippingRates( true );
				$quote->getShippingAddress()->collectShippingRates();
				$quote->save();
			}
		}

		$rates = $quote->getShippingAddress()->getShippingRatesCollection();
		$rates = $rates->getData();
		$minimumShippingCost = false;

		foreach ( $rates as $rate ) {
			if ( $minimumShippingCost === false || $rate[ 'price' ] < $minimumShippingCost ) {
				$minimumShippingCost = $rate[ 'price' ];
				$changedData['setShippingMethod'] = null;
				$quote->getShippingAddress()->setShippingMethod( $rate[ 'code' ] );
			}
		}

		// try to get any of the active shipping methods as minimum shipping cost if we still don't have any available from quote, this is meant to be the last resort
		if( $minimumShippingCost === false ) {
			$carriers = Mage::getStoreConfig( 'carriers', Mage::app()->getStore()->getId() );
			foreach( $carriers as $carrierCode => $carrierConfig ) {
				if( $carrierConfig['active'] ) {
					if( isset( $carrierConfig['price'] ) && ( $minimumShippingCost === false || $carrierConfig['price'] < $minimumShippingCost ) ) {
						$minimumShippingCost = $carrierConfig['price'];
						//$quote->getShippingAddress()->setShippingMethod( $this->getShippingCodeByName( $carrierConfig[ 'name' ] ) ); // setting a shipping method way doesn't work until shipping rates are actually collected for quote
					}
				}
			}
		}

		foreach($changedData as $func => $value) {
			$shippingAddress->$func($value);
		}

		$quote->collectTotals();
		$quote->save();

		$this->_minimumShippingCost = $minimumShippingCost;
	}

	public function ping() {

		// Get current version of the extension
		$connection = Mage::getSingleton( 'core/resource' )->getConnection( 'core_read' );
		$table = Mage::getSingleton('core/resource')->getTableName( 'core_resource' );
		$stmt = $connection->query( "SELECT version FROM $table WHERE code='anattadesign_awesomecheckout_setup';" );
		$data = $stmt->fetch();
		$version = $data['version'];

		$ping = array(
			'version' => $version,
			'site_name' => Mage::getStoreConfig( 'general/store_information/name' ),
			'url' => 'http://' . str_replace( array( 'http://', 'https://', '/index.php/', '/index.php' ), '', Mage::getUrl() ) // making sure the url is in format - http://domain.com/
		);

		$ping['url'] = $this->trailingslashit( $ping['url'] );

		// make call
		$client = new Varien_Http_Client( 'http://api.anattadesign.com/awesomecheckout/1alpha/collect/ping' );
		$client->setMethod( Varien_Http_Client::POST );
		$client->setParameterPost( 'ping', $ping );

		try {
			$response = $client->request();
			if ( $response->isSuccessful() ) {
				$json_response = json_decode( $response->getBody(), true );
				$ping_success = $json_response['status'] == 'success' ? true : false;
			}
		} catch ( Exception $e ) {
			$ping_success = false;
		}

		if ( $ping_success ) {
			// make sure ping is not rescheduled anymore
			Mage::getModel( 'core/config' )->deleteConfig( 'anattadesign_awesomecheckout_ping_rescheduled' );
		} else {
			// reschedule ping, increment counts if its already scheduled, so that we can see how many times it has failed
			// $ping_rescheduled = Mage::getStoreConfig( 'anattadesign_awesomecheckout_ping_rescheduled' );
			// Fetch directly from database to bypass Magento config cache.
			// Its better to bypass cache and make a sql query in favor of performance, sql query is not gonna run up on frontend side, except when all the cache is refreshed & extension is upgraded
			$table = Mage::getSingleton('core/resource')->getTableName( 'core_config_data' );
			$stmt = $connection->query( "SELECT value FROM $table WHERE path='anattadesign_awesomecheckout_ping_rescheduled' AND scope = 'default' AND scope_id = 0 LIMIT 1;" );
			$data = $stmt->fetch();
			if ( $data === false )
				$ping_rescheduled = 1;
			else
				$ping_rescheduled = intval( $data['value'] ) + 1;

			Mage::getModel( 'core/config' )->saveConfig( 'anattadesign_awesomecheckout_ping_rescheduled', $ping_rescheduled );
		}
	}

}