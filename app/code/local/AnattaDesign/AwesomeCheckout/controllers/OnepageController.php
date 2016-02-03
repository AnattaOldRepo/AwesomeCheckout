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

class AnattaDesign_AwesomeCheckout_OnepageController extends Mage_Checkout_Controller_Action {

	protected $_sectionUpdateFunctions = array(
		'payment-method' => '_getPaymentMethodsHtml',
		'shipping-method' => '_getShippingMethodsHtml',
		'review' => '_getReviewHtml',
	);

	/**
	 * @return Mage_Checkout_OnepageController
	 */
	public function preDispatch() {
		parent::preDispatch();
		$this->_preDispatchValidateCustomer();

		$checkoutSessionQuote = Mage::getSingleton( 'checkout/session' )->getQuote();
		if ( $checkoutSessionQuote->getIsMultiShipping() ) {
			$checkoutSessionQuote->setIsMultiShipping( false );
			$checkoutSessionQuote->removeAllAddresses();
		}

		if ( $this->getRequest()->getActionName() != 'success' ) {
			Mage::helper( 'anattadesign_awesomecheckout' )->estimateShipping();
		}

		return $this;
	}

	protected function _ajaxRedirectResponse() {
		$this->getResponse()
				->setHeader( 'HTTP/1.1', '403 Session Expired' )
				->setHeader( 'Login-Required', 'true' )
				->sendResponse();
		return $this;
	}

	/**
	 * Validate ajax request and redirect on failure
	 *
	 * @return bool
	 */
	protected function _expireAjax() {
		if ( !$this->getOnepage()->getQuote()->hasItems()
				|| $this->getOnepage()->getQuote()->getHasError()
				|| $this->getOnepage()->getQuote()->getIsMultiShipping() ) {
			$this->_ajaxRedirectResponse();
			return true;
		}
		$action = $this->getRequest()->getActionName();
		if ( Mage::getSingleton( 'checkout/session' )->getCartWasUpdated( true )
				&& !in_array( $action, array( 'index', 'progress' ) ) ) {
			$this->_ajaxRedirectResponse();
			return true;
		}

		return false;
	}

	/**
	 * Get shipping method step html
	 *
	 * @return string
	 */
	protected function _getShippingMethodsHtml() {
		$layout = $this->getLayout();
		$update = $layout->getUpdate();
		if ( Mage::helper( 'anattadesign_awesomecheckout' )->isVirtualOnly() ) {
			$update->load( array( 'checkout_onepage_shippingmethod', 'anattadesign_awesomecheckout_virtual' ) );
		} else {
			$update->load( 'checkout_onepage_shippingmethod' );
		}
		$layout->generateXml();
		$layout->generateBlocks();
		$output = $layout->getOutput();
		return $output;
	}

	/**
	 * Get payment method step html
	 *
	 * @return string
	 */
	protected function _getPaymentMethodsHtml() {
		$layout = $this->getLayout();
		$update = $layout->getUpdate();
		if ( Mage::helper( 'anattadesign_awesomecheckout' )->isVirtualOnly() ) {
			$update->load( array( 'anattadesign_awesomecheckout_onepage_paymentmethod', 'anattadesign_awesomecheckout_virtual' ) );
		} else {
			$update->load( 'anattadesign_awesomecheckout_onepage_paymentmethod' );
		}
		$layout->generateXml();
		$layout->generateBlocks();
		$output = $layout->getOutput();
		return $output;
	}

	protected function _getAdditionalHtml() {
		$layout = $this->getLayout();
		$update = $layout->getUpdate();
		if ( Mage::helper( 'anattadesign_awesomecheckout' )->isVirtualOnly() ) {
			$update->load( array( 'checkout_onepage_additional', 'anattadesign_awesomecheckout_virtual' ) );
		} else {
			$update->load( 'checkout_onepage_additional' );
		}
		$layout->generateXml();
		$layout->generateBlocks();
		$output = $layout->getOutput();
		return $output;
	}

	/**
	 * Get order review step html
	 *
	 * @return string
	 */
	protected function _getReviewHtml() {
		return $this->getLayout()->getBlock( 'root' )->toHtml();
	}

	/**
	 * Get one page checkout model
	 *
	 * @return Mage_Checkout_Model_Type_Onepage
	 */
	public function getOnepage() {
		return Mage::getSingleton( 'checkout/type_onepage' );
	}

	/**
	 * Checkout page
	 */
	public function indexAction() {
		if ( !Mage::helper( 'checkout' )->canOnepageCheckout() ) {
			Mage::getSingleton( 'checkout/session' )->addError( $this->__( 'The onepage checkout is disabled.' ) );
			$this->_redirect( 'checkout/cart' );
			return;
		}
		$quote = $this->getOnepage()->getQuote();
		if ( !$quote->hasItems() || $quote->getHasError() ) {
			$this->_redirect( 'checkout/cart' );
			return;
		}
		if ( !$quote->validateMinimumAmount() ) {
			$error = Mage::getStoreConfig( 'sales/minimum_order/error_message' );
			Mage::getSingleton( 'checkout/session' )->addError( $error );
			$this->_redirect( 'checkout/cart' );
			return;
		}
		Mage::getSingleton( 'checkout/session' )->setCartWasUpdated( false );
		Mage::getSingleton( 'customer/session' )->setBeforeAuthUrl( Mage::getUrl( '*/*/*', array( '_secure' => true ) ) );
		$this->getOnepage()->initCheckout();
		$this->loadLayout();
		$this->_initLayoutMessages( 'customer/session' );
		$this->getLayout()->getBlock( 'head' )->setTitle( $this->__( 'Checkout' ) );
		$this->renderLayout();
	}

	/**
	 * Checkout status block
	 */
	public function progressAction() {
		if ( $this->_expireAjax() ) {
			return;
		}
		$this->loadLayout( false );
		$this->renderLayout();
	}

	public function shippingMethodAction() {
		if ( $this->_expireAjax() ) {
			return;
		}
		$this->loadLayout( false );
		$this->renderLayout();
	}

	public function reviewAction() {
		if ( $this->_expireAjax() ) {
			return;
		}
		$this->loadLayout( false );
		$this->renderLayout();
	}

	/**
	 * Order success action
	 */
	public function successAction() {
		$session = $this->getOnepage()->getCheckout();
		if ( !$session->getLastSuccessQuoteId() ) {
			$this->_redirect( 'checkout/cart' );
			return;
		}

		$lastQuoteId = $session->getLastQuoteId();
		$lastOrderId = $session->getLastOrderId();
		$lastRecurringProfiles = $session->getLastRecurringProfileIds();
		if ( !$lastQuoteId || (!$lastOrderId && empty( $lastRecurringProfiles )) ) {
			$this->_redirect( 'checkout/cart' );
			return;
		}

		$session->clear();
		$this->loadLayout();
		$this->_initLayoutMessages( 'checkout/session' );
		Mage::dispatchEvent( 'checkout_onepage_controller_success_action', array( 'order_ids' => array( $lastOrderId ) ) );
		$this->renderLayout();
	}

	public function failureAction() {
		$lastQuoteId = $this->getOnepage()->getCheckout()->getLastQuoteId();
		$lastOrderId = $this->getOnepage()->getCheckout()->getLastOrderId();

		if ( !$lastQuoteId || !$lastOrderId ) {
			$this->_redirect( 'checkout/cart' );
			return;
		}

		$this->loadLayout();
		$this->renderLayout();
	}

	public function getAdditionalAction() {
		$this->getResponse()->setBody( $this->_getAdditionalHtml() );
	}

	/**
	 * Address JSON
	 */
	public function getAddressAction() {
		if ( $this->_expireAjax() ) {
			return;
		}
		$addressId = $this->getRequest()->getParam( 'address', false );
		if ( $addressId ) {
			$address = $this->getOnepage()->getAddress( $addressId );

			if ( Mage::getSingleton( 'customer/session' )->getCustomer()->getId() == $address->getCustomerId() ) {
				$this->getResponse()->setHeader( 'Content-type', 'application/json' );
				$address->setFullname( Mage::helper( 'anattadesign_awesomecheckout' )->getFullname( $address ) );
				$this->getResponse()->setBody( $address->toJson() );
			} else {
				$this->getResponse()->setHeader( 'HTTP/1.1', '403 Forbidden' );
			}
		}
	}

	/**
	 * Save checkout method
	 */
	public function saveMethodAction() {
		if ( $this->_expireAjax() ) {
			return;
		}
		if ( $this->getRequest()->isPost() ) {
			$method = $this->getRequest()->getPost( 'method' );
			$result = $this->getOnepage()->saveCheckoutMethod( $method );
			$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
		}
	}

	/**
	 * save checkout billing address
	 */
	public function saveBillingAction() {
		if ( $this->_expireAjax() ) {
			return;
		}
		if ( $this->getRequest()->isPost() ) {
			$data = $this->getRequest()->getPost( 'billing', array( ) );
			$data = Mage::helper( 'anattadesign_awesomecheckout' )->prepareAddressData( $data );
			$customerAddressId = $this->getRequest()->getPost( 'billing', false );
			$customerAddressId = $customerAddressId['address_id'];

			$login = $this->getRequest()->getParam( 'login', array( ) );
			if ( isset( $login['username'] ) ) {
				$data['email'] = $login['username'];
			}

			$result = $this->getOnepage()->saveBilling( $data, false );

			if(isset($data['country_id'])){
				if(Mage::helper('directory')->isRegionRequired($data['country_id'])
					&& isset($data['region_id']) && empty($data['region_id']) && $data['region_id'] != '0'
					&& isset($data['region']) && empty($data['region'])){
					$result['error'] = 1;
					$region_model = Mage::getModel('eav/entity_attribute')->loadByCode('customer_address','region_id');
					$result['message'][] = Mage::helper('anattadesign_awesomecheckout')->__('"%s" is a required value.', $region_model->getStoreLabel());
				}
			}
			if ( !isset( $result['error'] ) ) {
				/* check quote for virtual */
				if ( $this->getOnepage()->getQuote()->isVirtual() ) {
					$result['goto_section'] = 'payment';
					$result['update_section'] = array(
						'name' => 'payment-method',
						'html' => $this->_getPaymentMethodsHtml()
					);
				} elseif ( isset( $data['use_for_shipping'] ) && $data['use_for_shipping'] == 1 ) {
					$result['goto_section'] = 'shipping_method';
					$result['update_section'] = array(
						'name' => 'shipping-method',
						'html' => $this->_getShippingMethodsHtml()
					);

					$result['allow_sections'] = array( 'shipping' );
					$result['duplicateBillingInfo'] = 'true';
				} else {
					$result['goto_section'] = 'shipping';
				}
			}

			$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
		}
	}

	/**
	 * Shipping address save action
	 */
	public function saveShippingAction() {
		if ( $this->_expireAjax() ) {
			return;
		}
		if ( $this->getRequest()->isPost() ) {
			$data = $this->getRequest()->getPost( 'shipping', array( ) );
			$data = Mage::helper( 'anattadesign_awesomecheckout' )->prepareAddressData( $data );

			$result = $this->getOnepage()->saveShipping( $data, false );
			if(isset($data['country_id'])){
				if(Mage::helper('directory')->isRegionRequired($data['country_id'])
					&& isset($data['region_id']) && empty($data['region_id']) && $data['region_id'] != '0'
					&& isset($data['region']) && empty($data['region'])){
					$result['error'] = 1;
					$region_model = Mage::getModel('eav/entity_attribute')->loadByCode('customer_address','region_id');
					$result['message'][] = Mage::helper('anattadesign_awesomecheckout')->__('"%s" is a required value.', $region_model->getStoreLabel());
				}
			}
			if (isset($result['error'])) {
				$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
				return $this;
			}

			if ( !isset( $result['error'] ) ) {
				$result['goto_section'] = 'shipping_method';
				$result['update_section'] = array(
					'name' => 'shipping-method',
					'html' => $this->_getShippingMethodsHtml()
				);
			}
			$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
		}
	}

	/**
	 * Shipping method save action
	 */
	public function saveShippingMethodAction() {
		if ( $this->_expireAjax() ) {
			return;
		}
		if ( $this->getRequest()->isPost() ) {
			$shipping = $this->getRequest()->getPost( 'shipping', array( ) );
			$shipping = Mage::helper( 'anattadesign_awesomecheckout' )->prepareAddressData( $shipping );
			$this->getOnepage()->getQuote()->setTotalsCollectedFlag(true);
			if(!Mage::getStoreConfig( 'awesomecheckout/options/separate_shipping_method_step' )){
				$result = $this->getOnepage()->saveShipping( $shipping, false );
				if(isset($shipping['country_id'])){
					if(Mage::helper('directory')->isRegionRequired($shipping['country_id'])
						&& isset($shipping['region_id']) && empty($shipping['region_id']) && $shipping['region_id'] != '0'
						&& isset($shipping['region']) && empty($shipping['region'])){
						$result['error'] = 1;
						$region_model = Mage::getModel('eav/entity_attribute')->loadByCode('customer_address','region_id');
						$result['message'][] = Mage::helper('anattadesign_awesomecheckout')->__('"%s" is a required value.', $region_model->getStoreLabel());
					}
				}
				if (isset($result['error'])) {
					$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
					return $this;
				}
			}
			$login = $this->getRequest()->getParam( 'login', array( ) );
			if ( isset( $login['username'] ) ) {
				$shipping['email'] = $login['username'];
			}

			$shipping['save_in_address_book'] = 0;

			// TODO: see if quote billing address is already entered

			// errors in billing are intentionally ignored here as there is no way for user to actually edit the billing address on shipping step
			if ( Mage::getSingleton( 'customer/session' )->isLoggedIn()
					&& Mage::getSingleton( 'customer/session' )->getCustomer()->getDefaultBilling() ) {
				if( $this->getOnepage()->getQuote()->getBillingAddress()->getCustomerAddressId() != NULL){
					$this->getOnepage()->saveBilling( $shipping, Mage::getSingleton( 'customer/session' )->getCustomer()->getDefaultBilling() );
				}
			} else if ( !$this->getOnepage()->getQuote()->isAllowedGuestCheckout() || $this->getRequest()->getParam( 'should_create_account' ) ) {
				$password = ( isset($login['password']) && $login['password'] ) ? $login['password'] : '';
				$this->getOnepage()->saveCheckoutMethod( Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER );
				Mage::helper( 'anattadesign_awesomecheckout' )->addCustomerPassword( $shipping, $password );
				$temp = $this->getOnepage()->saveBilling( $shipping, false );
				if(!$this->getOnepage()->getQuote()->getBillingAddress()->getEmail() || (isset($temp['message']) && false !== strpos($temp['message'], 'Email'))) {
					$result = $temp;
				}
			} else {
				$this->getOnepage()->saveCheckoutMethod( Mage_Checkout_Model_Type_Onepage::METHOD_GUEST );
				$temp = $this->getOnepage()->saveBilling( $shipping, false );
				if(!$this->getOnepage()->getQuote()->getBillingAddress()->getEmail() || (isset($temp['message']) && false !== strpos($temp['message'], 'Email'))) {
					$result = $temp;
				}
			}
			$data = $this->getRequest()->getPost( 'shipping_method', '' );
			$result = $this->getOnepage()->saveShippingMethod( $data );

			$this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
			$this->getOnepage()->getQuote()->collectTotals();
			$this->getOnepage()->getQuote()->save();

			/*
			  $result will have erro data if shipping method is empty
			 */
			if ( !$result ) {
				Mage::dispatchEvent( 'checkout_controller_onepage_save_shipping_method', array( 'request' => $this->getRequest(),
					'quote' => $this->getOnepage()->getQuote() ) );
				$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );

				$result['goto_section'] = 'payment';
				$result['update_section'] = array(
					'name' => 'payment-method',
					'html' => $this->_getPaymentMethodsHtml()
				);
			}
			$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
		}
	}

	/**
	 * Save payment ajax action
	 *
	 * Sets either redirect or a JSON response
	 */
	public function savePaymentAction() {
		if ( $this->_expireAjax() ) {
			return;
		}
		try {
			if ( !$this->getRequest()->isPost() ) {
				$this->_ajaxRedirectResponse();
				return;
			}

			$result = array( );
			$billing = $this->getRequest()->getPost( 'billing', array( ) );
			$password = $this->getRequest()->getPost( 'password', null );
			$this->getOnepage()->getQuote()->setTotalsCollectedFlag(true);
			if ( !array_key_exists( 'same_as_shipping', $billing ) && !$this->getOnepage()->getQuote()->isVirtual() ) {
				$billing = Mage::helper( 'anattadesign_awesomecheckout' )->prepareAddressData( $billing );
				if ( !Mage::getSingleton( 'customer/session' )->isLoggedIn() ) {
					$billing['email'] = $this->getOnepage()->getQuote()->getBillingAddress()->getEmail();
					if( $password ) {
						Mage::helper( 'anattadesign_awesomecheckout' )->addCustomerPassword( $billing, $password );
					} else {
						Mage::helper( 'anattadesign_awesomecheckout' )->addCustomerPassword( $billing );
					}
				}
				$result = $this->getOnepage()->saveBilling( $billing, false );
				if ( isset( $result['error'] ) ) {
					throw new Mage_Core_Exception( join( ' ', (array) $result['message'] ) );
				}
			} elseif ( !$this->getOnepage()->getQuote()->isVirtual() ) {
				$shipping_address = $this->getOnepage()->getQuote()->getShippingAddress()->getData();
				$shipping_address['email'] = $this->getOnepage()->getQuote()->getBillingAddress()->getEmail();
				if( $password ) {
					Mage::helper( 'anattadesign_awesomecheckout' )->addCustomerPassword( $shipping_address, $password );
				} else {
					Mage::helper( 'anattadesign_awesomecheckout' )->addCustomerPassword( $shipping_address );
				}
				if( Mage::getSingleton( 'customer/session' )->isLoggedIn() && Mage::getSingleton( 'customer/session' )->getCustomer()->getDefaultBilling() ) {
					$result = $this->getOnepage()->saveBilling( $billing, Mage::getSingleton( 'customer/session' )->getCustomer()->getDefaultBilling() );
				} else if ( !$this->getOnepage()->getQuote()->isAllowedGuestCheckout() || $this->getRequest()->getParam( 'should_create_account' ) ) {
					$result = $this->getOnepage()->saveBilling( $shipping_address, false );
				} else {
					$result = $this->getOnepage()->saveBilling( $shipping_address, false );
				}
			}

			if ( empty( $result['error'] ) ) {
				// set payment to quote
				$data = $this->getRequest()->getPost( 'payment', array( ) );
				if(!(array_key_exists('method', $data) && $data['method']))
					$data['method'] = 'free';
				$this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
				$result = $this->getOnepage()->savePayment( $data );

				// get section and redirect data
				$redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
				if ( empty( $result['error'] ) && !$redirectUrl ) {
					$this->loadLayout( 'checkout_onepage_review' );
					$result['goto_section'] = 'review';
					$result['update_section'] = array(
						'name' => 'review',
						'html' => $this->_getReviewHtml()
					);
				}
				if ( $redirectUrl ) {
					$result['redirect'] = $redirectUrl;
				}
			} else {
				$result['error'] = $result['message'];
			}
		} catch ( Mage_Payment_Exception $e ) {
			if ( $e->getFields() ) {
				$result['fields'] = $e->getFields();
			}
			$result['error'] = $e->getMessage();
		} catch ( Mage_Core_Exception $e ) {
			// Go to the same section(payment) so that allowed payments are shown.
			// This is the case when selected payment method is not allowed for changed billing address country.
			if ( strncmp( $e->getMessage(), $this->__( 'Selected payment type is not allowed for billing country.' ), mb_strlen( $e->getMessage() ) ) === 0 ) {
				$result['update_section'] = array(
					'name' => 'payment-method',
					'html' => $this->_getPaymentMethodsHtml()
				);
				$result['error'] = $this->__( $e->getMessage() ) . $this->__( " Allowed payment methods for %s are loaded.", $billing['country_label'] );
			} else {
				$result['error'] = $e->getMessage();
			}
		} catch ( Exception $e ) {
			Mage::logException( $e );
			$result['error'] = $this->__( 'Unable to set Payment Method.' );
		}
		$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
	}

	/* @var $_order Mage_Sales_Model_Order */

	protected $_order;

	/**
	 * Get Order by quoteId
	 *
	 * @return Mage_Sales_Model_Order
	 */
	protected function _getOrder() {
		if ( is_null( $this->_order ) ) {
			$this->_order = Mage::getModel( 'sales/order' )->load( $this->getOnepage()->getQuote()->getId(), 'quote_id' );
			if ( !$this->_order->getId() ) {
				throw new Mage_Payment_Model_Info_Exception( Mage::helper( 'core' )->__( "Can not create invoice. Order was not found." ) );
			}
		}
		return $this->_order;
	}

	/**
	 * Create invoice
	 *
	 * @return Mage_Sales_Model_Order_Invoice
	 */
	protected function _initInvoice() {
		$items = array( );
		foreach ( $this->getOnepage()->getQuote()->getAllItems() as $item ) {
			$items[$item->getId()] = $item->getQty();
		}
		/* @var $invoice Mage_Sales_Model_Service_Order */
		$invoice = Mage::getModel( 'sales/service_order', $this->_getOrder() )->prepareInvoice( $items );
		$invoice->setEmailSent( true );

		Mage::register( 'current_invoice', $invoice );
		return $invoice;
	}

	/**
	 * Create order action
	 */
	public function saveOrderAction() {
		if ( $this->_expireAjax() ) {
			return;
		}

		// Dispatch custom event for hooking in GiftMessage functionality which works on Observer in core
		Mage::dispatchEvent( 'checkout_controller_onepage_save_giftmessage', array( 'request' => $this->getRequest(), 'quote' => $this->getOnepage()->getQuote() ) );

		$result = array( );
		try {
			if ( $requiredAgreements = Mage::helper( 'checkout' )->getRequiredAgreementIds() ) {
				$postedAgreements = array_keys( $this->getRequest()->getPost( 'agreement', array( ) ) );
				if ( $diff = array_diff( $requiredAgreements, $postedAgreements ) ) {
					$result['success'] = false;
					$result['error'] = true;
					$result['error_messages'] = $this->__( 'Please agree to all the terms and conditions before placing the order.' );
					$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
					return;
				}
			}

			if ( $data = $this->getRequest()->getPost( 'payment', false ) ) {
				$this->getOnepage()->getQuote()->getPayment()->importData( $data );
			}
			$this->getOnepage()->saveOrder();

			$storeId = Mage::app()->getStore()->getId();
			$paymentHelper = Mage::helper( "payment" );
			$zeroSubTotalPaymentAction = $paymentHelper->getZeroSubTotalPaymentAutomaticInvoice( $storeId );
			if ( $paymentHelper->isZeroSubTotal( $storeId )
					&& $this->_getOrder()->getGrandTotal() == 0
					&& $zeroSubTotalPaymentAction == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE
					&& $paymentHelper->getZeroSubTotalOrderStatus( $storeId ) == 'pending' ) {
				$invoice = $this->_initInvoice();
				$invoice->getOrder()->setIsInProcess( true );
				$invoice->save();
			}

			$redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
			$result['success'] = true;
			$result['error'] = false;
		} catch ( Mage_Payment_Model_Info_Exception $e ) {
			$message = $e->getMessage();
			if ( !empty( $message ) ) {
				$result['error_messages'] = $message;
			}
			$result['goto_section'] = 'payment';
			$result['update_section'] = array(
				'name' => 'payment-method',
				'html' => $this->_getPaymentMethodsHtml()
			);
		} catch ( Mage_Core_Exception $e ) {
			Mage::logException( $e );
			Mage::helper( 'checkout' )->sendPaymentFailedEmail( $this->getOnepage()->getQuote(), $e->getMessage() );
			$result['success'] = false;
			$result['error'] = true;
			$result['error_messages'] = $e->getMessage();

			// Add helping message to customer - usability enhancement
			$gateway_error_message = Mage::getStoreConfig( 'awesomecheckout/options/gateway_error_message' );
			$general_store_info_phone = Mage::getStoreConfig( 'general/store_information/phone' );
			$this->getOnepage()->getCheckout()->setGotoSection( 'payment' );
			$this->getOnepage()->getCheckout()->setUpdateSection( 'payment-method' );

			if ( ! empty( $gateway_error_message ) ) {
				$result['error_messages'] .= "<br /><br />" . Mage::getStoreConfig( 'awesomecheckout/options/gateway_error_message' );
			} else if ( ! empty( $general_store_info_phone ) ) {
				$result['error_messages'] .= "<br /><br /> ";
				$result['error_messages'] .= $this->__( "Please contact our support at %s", Mage::getStoreConfig( 'general/store_information/phone' ) );
			}

			if ( $gotoSection = $this->getOnepage()->getCheckout()->getGotoSection() ) {
				$result['goto_section'] = $gotoSection;
				$this->getOnepage()->getCheckout()->setGotoSection( null );
			}

			if ( $updateSection = $this->getOnepage()->getCheckout()->getUpdateSection() ) {
				if ( isset( $this->_sectionUpdateFunctions[$updateSection] ) ) {
					$updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
					$result['update_section'] = array(
						'name' => $updateSection,
						'html' => $this->$updateSectionFunction()
					);
				}
				$this->getOnepage()->getCheckout()->setUpdateSection( null );
			}
		} catch ( Exception $e ) {
			Mage::logException( $e );
			Mage::helper( 'checkout' )->sendPaymentFailedEmail( $this->getOnepage()->getQuote(), $e->getMessage() );
			$result['success'] = false;
			$result['error'] = true;
			$result['error_messages'] = $this->__( 'There was an error processing your order. Please contact us or try again later.' );
		}
		$this->getOnepage()->getQuote()->save();
		/**
		 * when there is redirect to third party, we don't want to save order yet.
		 * we will save the order in return action.
		 */
		if ( isset( $redirectUrl ) ) {
			$result['redirect'] = $redirectUrl;
		}

		$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
	}

	/**
	 * See if supplied email already exists
	 */
	public function emailExistsAction() {
		$result = new stdClass();
		$result->exists = Mage::helper( 'anattadesign_awesomecheckout' )->customerEmailExists( $this->getRequest()->getParam( 'email' ) );
		$this->getOnepage()->getQuote()->setCustomerEmail($this->getRequest()->getParam( 'email' ))->save();
		$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
	}

	/**
	 * Send new password to customer
	 */
	public function sendNewPasswordAction() {
		$result = new stdClass();
		$result->error = false;

		$email = $this->getRequest()->getPost( 'email' );
		if ( $email ) {
			if ( !Zend_Validate::is( $email, 'EmailAddress' ) ) {
				$result->error = true;
				$result->message = $this->__( 'Invalid email address.' );
				$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
				return;
			}
			$customer = Mage::getModel( 'customer/customer' )
					->setWebsiteId( Mage::app()->getStore()->getWebsiteId() )
					->loadByEmail( $email );

			if ( $customer->getId() ) {
				try {
					$newPassword = $customer->generatePassword();
					$customer->changePassword( $newPassword, false );
					$customer->sendPasswordReminderEmail();

					$result->title = $this->__( 'Your new password will arrive over email' );
					$result->message = $this->__( 'Please wait just a few minutes for an email to arrive from our store providing you your new password to login.' );
					$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
					return;
				} catch ( Exception $e ) {

				}
			} else {
				$result->error = true;
				$result->message = $this->__( 'This email address was not found in our records.' );
				$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
				return;
			}
		} else {
			$result->error = true;
			$result->message = $this->__( 'Please enter your email.' );
			$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
			return;
		}
	}

	/**
	 * Customer login via login form on checkout
	 */
	public function loginAction() {
		$result = new stdClass();
		$result->error = false;

		$username = $this->getRequest()->getPost( 'username', null );
		$password = $this->getRequest()->getPost( 'password', null );

		if ( $username && $password ) {
			try {
				Mage::getSingleton( 'customer/session' )->login( $username, $password );
			} catch ( Mage_Core_Exception $e ) {
				$title = '';
				switch ( $e->getCode() ) {
					case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
						$message = $this->__( 'This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper( 'customer' )->getEmailConfirmationUrl( $username ) );
						break;
					case AnattaDesign_AwesomeCheckout_Model_Customer::EXCEPTION_INVALID_PASSWORD:
						$title = $this->__( 'Sorry, that\'s the wrong password' );
						$message = $this->__( 'Please try again with another password or continue as a guest if you can\'t remember it.' );
						break;
					default:
						$message = $e->getMessage();
						break;
				}
				$result->error = true;
				$result->message = $message;
				$result->title = $title;
			} catch ( Exception $e ) {
				$result->error = true;
				$result->message = $e->getMessage();
			}
		} else {
			$result->error = true;
			$result->message = $this->__( 'Login and password are required' );
		}

		$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
	}

	/**
	 * Get country, city and state from postcode
	 */
	public function postcodeAddressAction() {
		$result = new stdClass();
		$result->error = false;

		$postcode = $this->getRequest()->getParam( 'postcode' );
		$postcode = urlencode( trim( $postcode ) );
		$specificCountry = trim( $this->getRequest()->getParam( 'country' ) );
		$allowed_countries = $this->_getCountryOptions();

		$request_url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . $postcode . '&sensor=true';
		// make call
		$client = new Varien_Http_Client($request_url);
		$client->setMethod(Varien_Http_Client::GET);
		try {
			$response = $client->request();
			if ($response->isSuccessful()) {
				$json_response = json_decode($response->getBody());
				$json_success = $json_response->status === 'OK' ? true : false;
			}
		} catch (Exception $e) {
			$json_success = false;
		}
		if ($json_success) {
			if ( 1 == count( $json_response->results ) ) {
				// if we only have one result let's return that & stop right here
				$result = $this->_extractAddress( $json_response->results[ 0 ]->address_components );
			} else if ( !empty( $specificCountry ) ) {
				// if we have multiple results & a country already specified, then prefer the specified country first
				$flag = true;
				$firstRecordFlag = true;
				foreach ( $json_response->results as $resultInstance ) {
					foreach ( $resultInstance->address_components as $address_component ) {
						if ( $address_component->types[ 0 ] == 'country' && $flag ) {
							if ( $address_component->long_name === $specificCountry ) {
								// found a record where country matches the already specified country, let's try to use this address
								$result = $this->_extractAddress( $resultInstance->address_components );
								if ( !$result->error ) {
									// the address matched & we have the needed information, no need to continue. Hooray!
									$flag = false;
								}
							} else if($firstRecordFlag){
								// stored first record as result if countries in all records doesn't match the already specified country
								$firstResult = $this->_extractAddress( $resultInstance->address_components );
								if ( !$result->error ) {
									// the first record stored no need to store it again.
									$firstRecordFlag = false;
								}
							}
						}
					}
				}
			}
			// if result is emtpy then use first record (if first record exists)
			if(!isset($result->data) && isset($firstResult->data)){
				$result = $firstResult;
			}
			// if the country is not allowed in admin settings, just return no results
			if ((isset($result->data) && isset($result->data->country_id)) || (isset($result->data) && isset($result->data->country))) {
				$flag = true;
				foreach ($allowed_countries as $allowed_country) {
					if (($allowed_country['value'] === $result->data->country_id) || ($allowed_country['label'] === $result->data->country)) {
						$flag = false;
						break;
					}
				}
				if ($flag) {
					unset($result->data);
				}
			}
		} else {
			$result->error = true;
		}

		$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
	}

	/**
	 * Extract the address result object from address components array we get from google API
	 */
	private function _extractAddress( $address_components ) {
		$result = new stdClass();
		$result->data = new stdClass();

		if ( count( $address_components ) ) {
			$result->error = true;
			foreach ( $address_components as $address_component ) {
				foreach ( $address_component->types as $type ) {
					if ($type == "sublocality") {
						$result->error = false;
						$result->data->city = $address_component->long_name;
					} elseif ( $type == "locality" && !isset($result->data->city)) {
						$result->error = false;
						$result->data->city = $address_component->long_name;
					} else if ( $type == "administrative_area_level_1" ) {
						$result->error = false;
						$result->data->state = $address_component->short_name;
					} else if ( $type == "country" ) {
						$result->error = false;
						$countryOption = Mage::helper( 'anattadesign_awesomecheckout' )->getAllowedCountryOptionById( $address_component->short_name );
						$result->data->country = $address_component->long_name;
						if ( $countryOption ) {
							$result->data->country_id = $countryOption[ 'value' ];
							$result->data->country = $countryOption[ 'label' ];
						}
					}
				}
			}
		} else {
			$result->error = true;
		}

		return $result;
	}

	/**
	 * Initialize coupon
	 */
	public function couponPostAction() {
		/**
		 * No reason continue with empty shopping cart
		 */
		if ( !$this->getOnepage()->getQuote()->getItemsCount() ) {
			$this->_redirect( 'checkout/cart' );
			return;
		}

		$couponCode = (string) $this->getRequest()->getParam( 'coupon_code' );
		if ( $this->getRequest()->getParam( 'remove' ) == 1 ) {
			$couponCode = '';
		}
		$oldCouponCode = $this->getOnepage()->getQuote()->getCouponCode();

		if ( !strlen( $couponCode ) && !strlen( $oldCouponCode ) ) {
			// TODO: no previous coupon code or new, render message or redirect
			return;
		}

		$result = array( );

		try {
			$this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates( true );
			$this->getOnepage()->getQuote()->setCouponCode( strlen( $couponCode ) ? $couponCode : ''  )->collectTotals()->save();

			if ($couponCode && $this->getOnepage()->getQuote()->getCouponCode() != $couponCode) {
				$result['error'] = $this->__( 'Coupon code "%s" is not valid.', Mage::helper( 'core' )->htmlEscape( $couponCode ) );
			} else {
				$result['update_section'] = array(
					'name' => $this->getRequest()->getParam('reload_step_name'),
				);

				if ($this->getRequest()->getParam('reload_step_block')) {
					$this->loadLayout($this->getRequest()->getParam('reload_step_block'));
					$result['update_section']['html'] = $this->_getReviewHtml();
				}
			}
		} catch ( Mage_Core_Exception $e ) {
			$result['error'] = $e->getMessage();
		} catch ( Exception $e ) {
			$result['error'] = $this->__( 'Cannot apply the coupon code.' );
			Mage::logException( $e );
		}

		$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
	}

	/**
	 * Get Allowed Countries
	 */
	protected function _getCountryOptions() {
		$options = false;
		$useCache = Mage::app()->useCache('config');
		if ($useCache) {
			$cacheId = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
			$cacheTags = array('config');
			if ($optionsCache = Mage::app()->loadCache($cacheId)) {
				$options = unserialize($optionsCache);
			}
		}

		if ($options == false) {
			$options = Mage::getSingleton('directory/country')->getResourceCollection()->loadByStore()->toOptionArray();
			if ($useCache) {
				Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
			}
		}
		return $options;
	}
}
