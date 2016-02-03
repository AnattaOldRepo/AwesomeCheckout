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

class AnattaDesign_AwesomeCheckout_Model_Observer {

	public function uponAdminLogin() {
		$this->ping();
		$this->checkAwesomeCheckoutVersion();
	}

	/**
	 * If the order was placed via guest checkout, here we are still linking the order to the correct customer id based on the email
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function checkout_submit_all_after( $observer ) {
		if( !Mage::helper( 'anattadesign_awesomecheckout' )->getConfigData( 'options/link_guest_orders' ) )
			return;

		/** @var Mage_Sales_Model_Order $order */
		$order = $observer->getEvent()->getOrder();
		/** @var Mage_Sales_Model_Quote $order */
		$quote = $observer->getEvent()->getQuote();

		if( !$order->getCustomerId() ) {
			$customer = Mage::getModel( 'customer/customer' );
			$customer->setWebsiteId( Mage::app()->getWebsite()->getId() );

			$customer->loadByEmail( $quote->getCustomerEmail() );

			if( $customer->getId() ) {
				$order->setCustomer( $customer );
				$order->setCustomerId( $customer->getId() );
				$order->setCustomerIsGuest( false );
				$order->setCustomerGroupId( $customer->getGroupId() );
				$order->setCustomerEmail( $customer->getEmail() );
				$order->setCustomerFirstname( $customer->getFirstname() );
				$order->setCustomerLastname( $customer->getLastname() );
				$order->setCustomerMiddlename( $customer->getMiddlename() );
				$order->setCustomerPrefix( $customer->getPrefix() );
				$order->setCustomerSuffix( $customer->getSuffix() );
				$order->setCustomerTaxvat( $customer->getTaxvat() );
				$order->setCustomerGender( $customer->getGender() );
				$order->save();
			}
		}
	}

	public function addLayoutHandleForPaymentExtensionsCompatibility( $observer ) {
		$update = $observer->getEvent()->getLayout()->getUpdate();
		$handles = $update->getHandles();
		// Awesome Checkout Virtual Products Support Extension
		if ( Mage::helper( 'anattadesign_awesomecheckout' )->isVirtualOnly() ) {
			$update->addHandle( 'anattadesign_awesomecheckout_virtual' );
		}
		// Braintree handle
		if ( Mage::helper( 'anattadesign_awesomecheckout/edition' )->isExtensionEnabled( 'Braintree' ) && in_array( 'checkout_onepage_review', $handles ) ) {
			$update->addHandle( 'anattadesign_awesomecheckout_braintree_checkout_onepage_review' );
		}
		// Braintree handle
		if ( Mage::helper( 'anattadesign_awesomecheckout/edition' )->isExtensionEnabled( 'Braintree_Payments' ) && in_array( 'checkout_onepage_review', $handles ) ) {
			$update->addHandle( 'anattadesign_awesomecheckout_braintree_payments_checkout_onepage_review' );
		}
		// Sagepay handle
		if ( Mage::helper( 'anattadesign_awesomecheckout/edition' )->isExtensionEnabled( 'Ebizmarts_SagePaySuite' ) && in_array( 'checkout_onepage_review', $handles ) ) {
			$update->addHandle( 'anattadesign_awesomecheckout_sagepay_checkout_onepage_review' );
		}
	}

	public function subscribeNewsletter( $observer ) {
		// Bail out if newsletter functionality is disabled in admin
		if ( ! Mage::getStoreConfigFlag( 'awesomecheckout/newsletter/enable' ) )
			return;

		// if either admin set in the config to not ask the user if they want to subscribe or if the user has confirmed that they want to subscribe
		if ( ! Mage::getStoreConfigFlag( 'awesomecheckout/newsletter/ask_the_user' ) || 1 == Mage::app()->getFrontController()->getRequest()->getParam( 'newsletter_subscribe' ) ) {
			$email = $observer->getEvent()->getOrder()->getCustomerEmail();
			$subscriber = Mage::getModel( 'newsletter/subscriber' )->loadByEmail( $email );

			// If user is already subscribed or unsubscribed, don't do anything except if user has unsubscribed before but has opted to subscribe on checkout page, then re-subscribe.
			if ( $subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED &&
				( 1 == Mage::app()->getFrontController()->getRequest()->getParam( 'newsletter_subscribe' ) || $subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED ) ) {
				$subscriber->setImportMode( true )->subscribe( $email );
			}
		}
	}

	public function dispatchGiftMessageEventForWhenSagepayTakesOverTheAction( $observer) {
		// Dispatch custom event for hooking in GiftMessage functionality which works on Observer in core
		Mage::dispatchEvent( 'checkout_controller_onepage_save_giftmessage', array( 'request' => $observer->getEvent()->getControllerAction()->getRequest(), 'quote' => Mage::getSingleton('checkout/session')->getQuote() ) );
	}

	public function layoutUpdate( $observer ) {
		if ( Mage::helper( 'anattadesign_awesomecheckout/edition' )->isMageEnterprise() ) {
			$updates = $observer->getEvent()->getUpdates();
			$updates->addChild( 'anattadesign_awesomecheckout_enterprise' )->file = 'anattadesign_awesomecheckout/enterprise.xml';
		}
	}

	public function ping() {

		// Instead of using getStoreConfig make a direct sql query to bypass magento cache
		// $is_ping_rescheduled = Mage::getStoreConfig( 'anattadesign_awesomecheckout_ping_rescheduled' );
		$connection = Mage::getSingleton( 'core/resource' )->getConnection( 'core_read' );
		$table = Mage::getSingleton('core/resource')->getTableName( 'core_config_data' );
		$stmt = $connection->query( "SELECT value FROM $table WHERE path='anattadesign_awesomecheckout_ping_rescheduled' AND scope = 'default' AND scope_id = 0 LIMIT 1;" );
		$data = $stmt->fetch();
		// If $data is false, then that means there is no row in the table, and no ping has been rescheduled
		if ( $data !== false )
			Mage::helper( 'anattadesign_awesomecheckout' )->ping();
	}

	public function checkAwesomeCheckoutVersion() {
		$request_url = 'http://api.anattadesign.com/awesomecheckout/1alpha/status/latestVersion';
		// make call
		$client = new Varien_Http_Client($request_url);
		$client->setMethod(Varien_Http_Client::GET);
		try {
			$response = $client->request();
			if ($response->isSuccessful()) {
				$json_response = json_decode($response->getBody());
				$json_success = $json_response->status === 'success' ? true : false;
			}
		} catch (Exception $e) {
			$json_success = false;
		}
		if ($json_success) {
			// Don't do anything if we are on latest, this prevents duplicate notifications
			if ( version_compare( $json_response->latestVersion, Mage::getStoreConfig( 'anattadesign_awesomecheckout_latest_checked_version' ), 'eq' ) )
				return;

			$connection = Mage::getSingleton( 'core/resource' )->getConnection( 'core_read' );
			$table = Mage::getSingleton('core/resource')->getTableName( 'core_resource' );
			$stmt = $connection->query( "SELECT version FROM $table WHERE code='anattadesign_awesomecheckout_setup'" );
			$data = $stmt->fetch();
			$version = $data['version'];

			if ( version_compare( $json_response->latestVersion, $version, '>' ) ) {
				Mage::getModel( 'adminnotification/inbox' )
						->setSeverity( Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE )
						->setTitle(Mage::helper('anattadesign_awesomecheckout')->__("Awesome Checkout %s is now available", $json_response->latestVersion))
						->setDateAdded( gmdate( 'Y-m-d H:i:s' ) )
						->setUrl( 'http://www.awesomecheckout.com/update' )
						->setDescription(Mage::helper('anattadesign_awesomecheckout')->__('Your version of Awesome Checkout is currently not up-to-date. Please <a href="http://www.awesomecheckout.com/update">click here</a> to get the latest version.'))
						->save();
				Mage::getModel( 'core/config' )->saveConfig( 'anattadesign_awesomecheckout_latest_checked_version', $json_response->latestVersion );
			}
		}
	}

}