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

/**
 * Global functions
 */

function ccType(num) {
	num = num.replace(/[^\d]/g, '');
	// only consider the first 6 digits to match
	num = num.slice(0,6);
	if (num.match(/^5[1-5][0-9]{4}$/)) {
		return 'MasterCard';
	} else if (num.match(/^4[0-9]{5}(?:[0-9]{3})?$/)) {
		return 'Visa';
	} else if (num.match(/^3[47][0-9]{4}$/)) {
		return 'AmEx';
	} else if (num.match(/^6(?:011|5[0-9]{2}|[4][4-9][0-9])[0-9]{2}$/)) {
		return 'Discover';
	} else if (num.length == 6 && parseInt(num) >= 622126 && parseInt(num) <= 622925) {
		return 'Discover';
	}
	return null;
}

function magentoCcType(name,pg) {
	if (pg) {
		if (pg == 'sagepaydirectpro_cc_number') {
			// VISA,MC,DELTA,SWITCH,MAESTRO,AMEX,UKE,DINERS,JCB,LASER
			var mc = {
				Visa: "VISA",
				MasterCard: "MC",
				// SagePay doesn't support Discover but leaving it here won't hurt & will work as is (if it accepts DI) when added in future
				Discover: "DI",
				AmEx: "AMEX"
			};
		}
	} else {
		var mc = {
			Visa: "VI",
			MasterCard: "MC",
			Discover: "DI",
			AmEx: "AE"
		};
	}
	return mc[name];
}

function getErrorMessageLine(id) {
	id = id.replace('shipping:','');
	id = id.replace('billing:','');
	var message;
	switch (id) {
		case 'login-email': message = 'Email'; break;
		case 'login-password': message = 'Password'; break;
		case 'fullname': message = 'Full Name'; break;
		case 'street1': message = 'Address'; break;
		case 'postcode': message = 'Zip/Postal Code'; break;
		case 'city': message = 'City'; break;
		case 'region_id': message = 'State/Province'; break;
		case 'country_id': message = 'Country'; break;
		case 'telephone': message = 'Phone	Number'; break;
		case 'ccsave_cc_number':
		case 'authorizenet_cc_number': message = 'Credit Card number'; break;
		case 'ccsave_expiration':
		case 'authorizenet_expiration': message = 'Credit Card expiration date (Month)'; break;
		case 'ccsave_expiration_yr':
		case 'authorizenet_expiration_yr': message = 'Credit Card expiration date (Year)'; break;
		case 'authorizenet_cc_cid':
		case 'ccsave_cc_cid': message = 'Credit Card CVV code'; break;
		default: message = 'Incorrect information';
	}
	// If default message is being used, try to guess for CC fields, if its some payment extension
	if (message == 'Incorrect information') {
		if (id.indexOf('cc_owner') != -1)
			message = 'Name on Card';
		if (id.indexOf('cc_number') != -1)
			message = 'Credit Card number';
		if (id.indexOf('expiration') != -1)
			message = 'Credit Card expiration date (Month)';
		if (id.indexOf('expiration_yr') != -1)
			message = 'Credit Card expiration date (Year)';
		if (id.indexOf('cc_cid') != -1)
			message = 'Credit Card CVV code';
	}
	return '<li>'+message+'</li>';
}

// jQuery modal function as an alternative to bootstrap's modal
var modalfreeze = false;
jQuery.fn.modal = function (options) {
	// preventing against accidental double click
	if (modalfreeze)
		return;

	if (!options)
		return this.modal(this.is(':visible') ? 'hide' : 'show');

	var el = this;
	var hide = function () {
		el.modal('hide');
		return false;
	}
	var show = function () {
		el.modal('show');
		return false;
	}
	var keypresshandler = function (e) {
		if (e.keyCode === 27)
			hide();
		return false;
	}

	modalfreeze = true;
	if (options == 'show') {
		jQuery('body').append('<div class="modal-backdrop fade in"></div>');
		this.show().animate({top: '50%', opacity: 1}, 500, null, function () {
			jQuery(this).addClass('in');
			jQuery(this).one('click', '[data-dismiss="modal"]', hide);
			jQuery('div.modal-backdrop').on('click', hide);
			jQuery(document).on('keyup', keypresshandler);
			modalfreeze = false;
		});
	} else if (options == 'hide') {
		this.off('click', '[data-dismiss="modal"]', hide);
		jQuery('div.modal-backdrop').off('click', hide);
		jQuery(document).off('keyup', keypresshandler);
		this.animate({top: '-25%', opacity: 0}, 500, null, function () {
			jQuery(this).removeClass('in').hide();
			jQuery('div.modal-backdrop').remove();
			modalfreeze = false;
		});
	}

	return this;
}

// Extend Array prototype with custom inArray method
Array.prototype.inArray = function(element) {
	for (var i=0; i < this.length; i++) {
		if (element == this[i])
			return i;
	}
	return false;
};

// Extend Array prototype with custom pushIfNotExist method
Array.prototype.pushIfNotExist = function(element) {
	if ( this.inArray(element) === false ) {
		this.push(element);
	}
};

// Extend Array prototype with custom removeIfExist method
Array.prototype.removeIfExist = function(element) {
	var index = this.inArray(element);
	if ( index !== false ) {
		this.splice(index,1);
	}
}

/**
 * Validator settings
 */
var validatorSettings = {
	debug: false,
	errorPlacement: function(error, element) {
		var id = element.attr('id');

		jQuery(jq(id)).parents('.fields').find('.sidetip p').hide();
		jQuery(jq(id)).parents('.fields').find('.sidetip .bad').show();

		// change IDs for fields where we have single label for 2 inputs
		id = (id == 'shipping:region') ? 'shipping:region_id' : id;
		id = (id == 'shipping:country_label') ? 'shipping:country_id' : id;
		id = (id == 'billing:region') ? 'billing:region_id' : id;
		id = (id == 'billing:country_label') ? 'billing:country_id' : id;
		jQuery('label[for="' + id + '"]').addClass('err');

		if (checkout.collectErrorsFlag)
			checkout.errorsCollection.pushIfNotExist(getErrorMessageLine(id));
	},
	success: function(label) {
		var for_attr = label.attr('for');

		jQuery(jq(for_attr)).parents('.fields').find('.sidetip p').hide();
		// show checkmark only when the field has lost focus
		if (!jQuery(jq(for_attr)).is(':focus'))
			jQuery(jq(for_attr)).parents('.fields').find('.sidetip .good').show().delay(2000).fadeOut();

		// change IDs for fields where we have single label for 2 inputs
		for_attr = (for_attr == 'shipping:country_label') ? 'shipping:country_id' : for_attr;
		for_attr = (for_attr == 'shipping:region') ? 'shipping:region_id' : for_attr;
		for_attr = (for_attr == 'billing:country_label') ? 'billing:country_id' : for_attr;
		for_attr = (for_attr == 'billing:region') ? 'billing:region_id' : for_attr;
		jQuery('label[for="' + for_attr + '"]').removeClass('err');

		if (checkout.collectErrorsFlag)
			checkout.errorsCollection.removeIfExist(getErrorMessageLine(for_attr));
	}
};

// Trigger blur on email field to counter-attach browser's autocomplete feature
window.onload = function() {
	if (jQuery('#login-email') && jQuery('#login-email').val() != '') {
		jQuery('#login-email').trigger('blur');
	};
}

// Make checkout go back when interacting via browser's back
window.onpopstate = function(event) {
	if (event.state != null)
		jQuery('a[href=#'+event.state.step+']').trigger('click');
};

/**
 * Document ready
 */

jQuery(document).ready(function() {

	// clear any hashes
	location.hash = '';

	var handler = function (e) {
		var form_type = jQuery(this).closest('form').attr('id').split('-')[1];
		if ('payment' == form_type)
			form_type = 'billing';
		checkout.setPhoneMasking(form_type);
		return false;
	}

	jQuery('#checkoutSteps').on('change', jq('shipping:country_id') + ',' + jq('billing:country_id'), handler);

	// trigger the phonemasking handler on page load
	jQuery(jq('shipping:country_id')).trigger('change');

	checkout.prepareAddressForm('shipping');
	jQuery('.validate-cc-number').live('keyup change', function() {
		var type = window.ccType(this.value);
		var cards = jQuery(this).parents('ul:first').find('.cards');
		if (cards.length) {
			jQuery('li', cards).removeClass('on').addClass('off');
			var ccType = jQuery(this).parents('ul:first').find('.cc_type')
			jQuery(ccType).val('');
			if (type) {
				var mType = magentoCcType(type);
				jQuery('li.' + mType, cards).removeClass('off').addClass('on');
				// If this is Sagepay, then save ccType's field value which it can accept
				if (jQuery(this).attr('id') == 'sagepaydirectpro_cc_number') mType = magentoCcType(type,'sagepaydirectpro_cc_number');
				jQuery(ccType).val(mType);
			}
		}
	});

	jQuery('#login-email').blur(function() {
		if (jQuery(this).closest('form').validate().element(this)) {
			checkout.checkEmailExists(this.value);
		};
	});

	// This was added because the original function used .required-entry as the class
	var _orig_init_func = RegionUpdater.prototype.initialize;
	RegionUpdater.prototype.initialize = function(countryEl, regionTextEl, regionSelectEl, regions, disableAction, zipEl) {
		var config = regions.config;
		_orig_init_func.call(this, countryEl, regionTextEl, regionSelectEl, regions, disableAction, zipEl);
		regions.config = config;
	};
	var _orig_func = RegionUpdater.prototype._checkRegionRequired;
	RegionUpdater.prototype._checkRegionRequired = function() {
		_orig_func.call(this);
		var elements = [this.regionTextEl, this.regionSelectEl];
		if(typeof this.config == 'undefined') {
			return;
		}
		var regionRequired = this.config.regions_required.indexOf(this.countryEl.value) >= 0;

		elements.each(function(currentElement) {
			if(!regionRequired) {
				if(currentElement.hasClassName('required')) {
					currentElement.removeClassName('required');
				}
				if('select' == currentElement.tagName.toLowerCase() &&
					currentElement.hasClassName('validate-select')) {
					currentElement.removeClassName('validate-select');
				}
			} else {
				if(!currentElement.hasClassName('required')) {
					currentElement.addClassName('required');
				}
				if('select' == currentElement.tagName.toLowerCase() && !currentElement.hasClassName('validate-select')) {
					currentElement.addClassName('validate-select');
				}
			}
		});
	};

	jQuery('#checkout-shipping-method-load input[type=radio]:checked').click();
	jQuery(document).on('click', '#checkout-shipping-method-load input[type=radio]', function(){
		shipping.selectedShippingMethod = jQuery(this).val();
	});

	if(typeof EbizmartsSagePaySuite !== 'undefined' && typeof EbizmartsSagePaySuite.Checkout !== 'undefined' ){
		EbizmartsSagePaySuite.Checkout.prototype.getPaymentMethod = function(){
			var form = null;

    			//Fix this when using IE patch

    			if($('multishipping-billing-form')){
        			form = $('multishipping-billing-form');
    			}else if(this.getConfig('osc')){
        			form = this.getConfig('oscFrm');
    			}else if((typeof this.getConfig('payment')) != 'undefined'){
        			form = $(this.getConfig('payment').form)[0];
    			}

    			if(form === null){
       	 			return this.code;
    			}

    			var checkedPayment = null

    			form.getInputs('radio', 'payment[method]').each(function(el){
        			if(el.checked){
            			checkedPayment = el.value;
            			throw $break;
        			}
    			});

    			if(checkedPayment != null){
        			return checkedPayment;
    			}

    			return this.code;
		};
	}
});

/**
 * Checkout
 * @param options
 */
Checkout = function(accordion, options) {
	this.init(accordion, options);
}
jQuery.extend(Checkout.prototype, {

	options: null,
	accordion: null,
	activeSection: 'shipping',
	loadWaiting: false,
	placeholderSupport: 'placeholder' in document.createElement('input'),
	currentShippingMethodIndex: null,
	highlightShippingCost: false, // flag to indicate whether we need to highlight shipping cost in progress box
	collectErrorsFlag: 0, // flag to indicate whether to collect validation errors to show in a modal box
	errorsCollection: [], // placeholder for collecting validation error messages
	GASteps: [{"payment":0}, {"review":0}], // google analytics steps

	init: function(accordion, options) {
		this.accordion = accordion;
		this.options = options;
		if(this.options.separate_shipping_method_step){
			this.GASteps= [{"shipping_method":0}, {"payment":0}, {"review":0}];
		}
	},

	reloadProgressBlock: function(section) {
		jQuery('#checkout-progress-wrapper').load(this.options.progressUrl + "?section=" + section,function(){
			jQuery('table.p-final tr.shine').delay(2000).queue(function(next){
				jQuery(this).removeClass('shine');
				next();
			});
			if (checkout.highlightShippingCost) {
				jQuery('.shipping-mthd-total').addClass('price-highlight').delay(800).queue(function(next){
					jQuery('.shipping-mthd-total').removeClass('price-highlight');
					next();
				});
				checkout.highlightShippingCost = false;
			}
			if('shipping_method' === section){
				jQuery('#checkout-shipping-method-load input[value='+shipping.selectedShippingMethod+']').click();
			}
		});
		if('shipping_method' === section){
			jQuery('#checkout-shipping-method-load input[value='+shipping.selectedShippingMethod+']').click();
		}
	},

	reloadReviewBlock: function() {
		jQuery('#checkout-review-load').load(this.options.reviewUrl);
	},

	_disableEnableAll: function(element, isDisabled) {
		var descendants = element.descendants();
		for (var k in descendants) {
			descendants[k].disabled = isDisabled;
		}
		element.disabled = isDisabled;
	},

	setLoadWaiting: function(step, keepDisabled) {
		if (step) {
			if (this.loadWaiting) {
				this.setLoadWaiting(false);
			}
			var container = $(step + '-buttons-container');
			container.addClassName('disabled');
			container.setStyle({
				opacity: .5
			});
			this._disableEnableAll(container, true);
			Element.show(step + '-please-wait');
		} else {
			if (this.loadWaiting) {
				var container = $(this.loadWaiting + '-buttons-container');
				var isDisabled = (keepDisabled ? true : false);
				if (!isDisabled) {
					container.removeClassName('disabled');
					container.setStyle({
						opacity: 1
					});
				}
				this._disableEnableAll(container, isDisabled);
				Element.hide(this.loadWaiting + '-please-wait');
			}
		}
		this.loadWaiting = step;
	},

	gotoSection: function(section) {
		// @TODO: add GA tracking via hooks
		if ((typeof _gaq !== "undefined" && _gaq !== null) || typeof ga !== 'undefined') {
			var i = 0;
			if (section === 'review' && this.options.separate_shipping_method_step) {
				i = 2;
			} else if (section === 'review') {
				i = 1;
			}
			if (section === 'payment' && this.options.separate_shipping_method_step) {
				i = 1;
			}
			if (typeof checkout.GASteps[i][section] !== 'undefined' && checkout.GASteps[i][section] === 0)
			{
				if(typeof _gaq !== "undefined" && _gaq !== null){
					_gaq.push(['_trackPageview', '/checkout/onepage/' + section + '/']);
				}
				if(typeof ga !== 'undefined'){
					ga('send', 'pageview', '/checkout/onepage/' + section + '/');
				}
				checkout.GASteps[i][section] = 1;
			}
		}
		this.activeSection = section;
		block = jQuery('#opc-' + section);
		block.addClass('allow');
		this.accordion.openSection('opc-' + section);
	},

	openSection: function(section) {
		this.activeSection = section;
		this.accordion.openSection('opc-' + section);
		this.markActiveSection();
                if(!checkout.options.disable_postcode_autocomplete){
			jQuery(jq('shipping-postcode-please-wait')).hide();
			jQuery(jq('billing-postcode-please-wait')).hide();
		}
	},

	markActiveSection: function() {
		var section = this.activeSection == 'shipping_method' ? 'shipping' : this.activeSection; // shipping and shipping_method share progress-block
		if (section != 'review') jQuery('.progress-block.review .buttons-set').remove(); // remove submit button
		this.reloadProgressBlock(section);
		jQuery('.progress-block').removeClass('active');
		jQuery('.progress-block.' + section).addClass('active');
		jQuery('.mark-arrow').appendTo(jQuery('.progress-block.' + section));
	},

	back: function() {
		if (this.loadWaiting) return;
		this.accordion.openPrevSection(true);
	},

	saveShippingMethodIndex: function(){
		this.currentShippingMethodIndex = jQuery('#checkout-shipping-method-load input[type=radio]').index(jQuery('#checkout-shipping-method-load input[type=radio]:checked'));
	},

	getSavedShippingMethodIndex: function(){
		return this.currentShippingMethodIndex;
	},

	getCurrentShippingMethodIndex: function() {
		return jQuery('#checkout-shipping-method-load input[type=radio]').index(jQuery('#checkout-shipping-method-load input[type=radio]:checked'));
	},

	setStepResponse: function(response) {
		if (response.update_section) {
			// Sometimes 'payment-method' is used instead of 'payment'
			sectionName = ("payment-method" === response.update_section.name) ? "payment" : response.update_section.name;

			jQuery('#checkout-' + sectionName + '-load').html(response.update_section.html);
		}
		if (response.allow_sections) {
			response.allow_sections.each(function(e) {
				$('opc-' + e).addClass('allow');
			});
		}

		if (response.goto_section) {
			// Useful for customers on phones, otherwise they might start a step half way down the screen.
			if('shipping_method' !== response.goto_section) {
				// If we are on the shipping method step we don't need to set the scroll position as shipping_method step is a part of shipping step
				jQuery('html, body').scrollTop(0);
			}

			this.reloadProgressBlock(response.goto_section);
			this.gotoSection(response.goto_section);
			if(billing.sameAsBillingChecked === 0){
				checkout.prepareAddressForm('billing');
				jQuery('.dflt-adrs-labl input[type=checkbox][name="billing[same_as_shipping]"]').removeAttr('checked');
				jQuery('#billing-address-previously-saved').hide(400);
				jQuery('#billing-new-address-form').show(400);
				jQuery('#billing-address-dropdown').show(400);
			}
			return true;
		}
		if (response.redirect) {
			location.href = response.redirect;
			return true;
		}
		return false;
	},

	handleError: function(msg,title) {
		if (typeof(msg) == 'object')
			msg = msg.join("\n<br />");

		// set title
		if (title)
			jQuery('#error-message .modal-ac-header h3').text(title);
		else
			jQuery('#error-message .modal-ac-header h3').text('Error Occured');

		if (msg) {
			jQuery('#error-message .modal-ac-body').html(msg);
			jQuery('#error-message').modal('show');
		}
		return false;
	},

	checkEmailExists: function(email) {
		// checking if there has actually been a change since the last time this function was called, this is because of bug in IE that it calls change event even when value hasn't been modified
		if(email === this._previouscheckingemail)
			return;
		this._previouscheckingemail = email;

		jQuery('#email-please-wait').fadeIn();
		var that = this;
		jQuery.post(this.options.emailExistsUrl, {
			email: email
		}, function(data) {
			jQuery('#email-please-wait').fadeOut();
			jQuery('#login-password-container').show();
			if (data.exists) {
				jQuery('.has-account').show();
				jQuery('.already_customer').hide();
				jQuery('.create_account').hide();
				jQuery('#login-submit').show();
				jQuery('#send-new-password').show();
				jQuery('#login-email').parents('.fields').find('.sidetip .good').show().delay(2000).fadeOut();
				jQuery('.enter_password label').text( Translator.translate( 'Password' ) );
				if(jQuery('.create_account input').prop('checked')) {
					jQuery('.enter_password').show();
					jQuery('.enter_password input').focus();
					jQuery('#shipping-new-address-form').find('label').removeClass('err');
					jQuery('#shipping-new-address-form').find('.sidetip').children().hide();
					jQuery('.has-account').hide();
					that.loginFadeIn();
					jQuery('#continue_as_guest').hide();
				} else {
					jQuery('#continue_as_guest').show();
					//jQuery('.enter_password').hide();
				}
			} else {
				jQuery('.has-account').hide();
				jQuery('.already_customer').hide();
				jQuery('.create_account').show();
				jQuery('#continue_as_guest').hide();
				jQuery('#login-submit').hide();
				jQuery('#send-new-password').hide();
				jQuery('.enter_password label').text( Translator.translate( 'Create a password' ) );
				that.loginFadeOut();
				if(jQuery('.create_account input').prop('checked')) {
					jQuery('.enter_password').show();
					//jQuery('.enter_password input').focus();
					jQuery('#shipping-new-address-form').find('label').removeClass('err');
					jQuery('#shipping-new-address-form').find('.sidetip').children().hide();
				} else {
					jQuery('.enter_password').hide();
				}
			}
		}, 'json');
	},

	loginFadeIn: function() {
		// fade out & disable fields
		var form = jQuery('#shipping-new-address-form');
		var shippingMethodForm = jQuery('#co-shipping-method-form');
		form.addClass('faded');
		shippingMethodForm.addClass('faded');
		form.find('input,select').removeClass('error').prop('disabled',true);
		shippingMethodForm.find('input,select').removeClass('error').prop('disabled',true);
		jQuery('#shipping-new-address-form label').removeClass('err');
		jQuery('#co-shipping-method-form label').removeClass('err');
		form.find('.sidetip p').hide();
		shippingMethodForm.find('.sidetip p').hide();
		jQuery('#shipping-method-buttons-container input.button').addClass('inactive').attr('disabled', 'disabled');
	},

	loginFadeOut: function() {
		// fade in & enable fields
		var form = jQuery('#shipping-new-address-form');
		var shippingMethodForm = jQuery('#co-shipping-method-form');
		form.removeClass('faded');
		shippingMethodForm.removeClass('faded');
		form.find('input,select').prop('disabled',false);
		shippingMethodForm.find('input,select').prop('disabled',false);
		jQuery('#shipping-method-buttons-container input.button').removeClass('inactive').removeAttr('disabled');
	},

	sendNewPassword: function(email) {
		jQuery.post(this.options.sendNewPasswordUrl, {
			email: email
		}, function(data) {
			checkout.handleError(data.message, data.title);
		}, 'json');
	},

	login: function() {
		if(this.options.isVirtual)
			var validator = billing.getValidator();
		else
			var validator = shipping.getValidator();
		if (validator.element('#login-password') && validator.element('#login-email')) {
			jQuery.post(this.options.loginUrl, {
				username: jQuery('#login-email').val(),
				password: jQuery('#login-password').val()
			}, function(data) {
				if (!data.error) {
					window.location.reload();
				} else {
					checkout.handleError(data.message, data.title);
				}
			}, 'json');
		}
	},

	getCountryLabel: function(id) {
		for (var i in countryOptions) {
			if (countryOptions[i].value == id) {
				return countryOptions[i].label;
			}
		}
		throw "There is no country by id: " + id;
	},

	isCountryAllowed: function(label) {
		for (var i in countryOptions) {
			if (countryOptions[i].label == label) {
				return true;
			}
		}
		return false;
	},

	postcodeAddress: function(el) {
		var id = el.id;
		var type = id.substr(0, id.indexOf(':'));
		var country = jQuery(jq(type + ':country_label')).val(); // send the country so that we return pincode details specific to the country passed, if multiples are available
		if(!checkout.options.disable_postcode_autocomplete){
			jQuery('#' + type + '-postcode-please-wait').fadeIn();
		}
		if(this.postcodeAddressXHR && 4 !== this.postcodeAddressXHR.readystate) {
			this.postcodeAddressXHR.abort();
		}
		this.postcodeAddressXHR = jQuery.get(this.options.postcodeAddressUrl, {
			postcode: el.value,
			country: country
		}, function(resp) {
			// Hide ajax loader
			if(!checkout.options.disable_postcode_autocomplete){
				jQuery('#' + type + '-postcode-please-wait').fadeOut();
			}

			if (resp.error || typeof resp.data === 'undefined') {
				return;
			}

			var data = resp.data;
			if ( !data ) {
				// there wasn't any error but we still have no data returned, let's clear the fields to force user input
				jQuery(jq(type + ':city')).val('');
				jQuery(jq(type + ':region')).val('');
				jQuery(jq(type + ':region_id')).val('');
				jQuery(jq(type + ':country_label')).val('');
				jQuery(jq(type + ':country_id')).val('');
				window[type + "RegionUpdater"]["update"]();
				checkout.setPhoneMasking(type);
			}

			if (data.city) {
				// Hide IE placeholder for city
				if (checkout.placeholderSupport === false) jQuery(jq(type + ':city')).parent().find('.ieplaceholder').hide();

				jQuery(jq(type + ':city')).val(data.city).addClass('shine').delay(1000).queue(function(next){
					jQuery(jq(type + ':city')).removeClass('shine');
					jQuery(jQuery(jq(type + ':city'))).closest('form').validate(validatorSettings).element(jQuery(jq(type + ':city')));
					next();
				}).change();
			}
			if (data.state) {
				jQuery(jq(type + ':region')).val(data.state).addClass('shine').delay(1000).queue(function(next){
					jQuery(jq(type + ':region')).removeClass('shine');
					jQuery(jQuery(jq(type + ':region'))).closest('form').validate(validatorSettings).element(jQuery(jq(type + ':region')));
					next();
				});
				jQuery(jq(type + ':region_id')).addClass('shine').delay(1000).queue(function(next){
					jQuery(jq(type + ':region_id')).removeClass('shine');
					jQuery(jQuery(jq(type + ':region_id'))).closest('form').validate(validatorSettings).element(jQuery(jq(type + ':region_id')));
					next();
				}).change();
			}
			if (data.country) {
				jQuery(jq(type + ':country_label')).val(data.country).addClass('shine').delay(1000).queue(function(next){
					jQuery(jq(type + ':country_label')).removeClass('shine');
					jQuery(jQuery(jq(type + ':country_label'))).closest('form').validate(validatorSettings).element(jQuery(jq(type + ':country_label')));
					next();
				}).change();
				jQuery(jq(type + ':country_id')).val(data.country).addClass('shine').delay(1000).queue(function(next){
					jQuery(jq(type + ':country_id')).removeClass('shine');
					next();
				});
			}
			if (data.country_id) jQuery(jq(type + ':country_id')).val(data.country_id).change();
			jQuery(jQuery(jq(type + ':country_id'))).closest('form').validate(validatorSettings).element(jQuery(jq(type + ':country_id')));
			window[type + "RegionUpdater"]["update"]();
			// TODO: validate city, state, country

			// do it once we have new city/state/country fields filled
			checkout.setPhoneMasking(type);
			if(!checkout.options.separate_shipping_method_step)
			{	// save shipping info when postcode ajax fills location
				if (type == 'shipping')
					shipping.save(0);
			}
		}, 'json');
	},

	cancelCoupon: function() {
		jQuery('#remove-coupone').val(1);
		jQuery('#coupon-cancel-link').hide();
		jQuery('#coupon-cancel-please-wait').show(400);
		jQuery.get(this.options.couponPostUrl, jQuery('#discount-coupon-form').serialize(), function(response) {
			if (response.error) {
				jQuery('#coupon-cancel-please-wait').hide();
				jQuery('#coupon-cancel-link').show(400);
				return checkout.handleError(response.message);
			} else {
				jQuery('#coupon-cancel-please-wait').hide();
				jQuery('#coupon-cancel-link').hide(400);
				jQuery('#coupon-apply-link').show(400);
			}

			checkout.reloadProgressBlock(response.update_section.name);
			if (response.update_section.html) {
				checkout.setStepResponse(response);
			}
			jQuery('#checkout-review-table > tfoot > tr:last').delay(500).queue(function(next){
				jQuery(this).addClass('shine');
				next();
			}).delay(1000).queue(function(next){
				jQuery(this).removeClass('shine');
				next();
			});
		}, 'json');
	},

	applyCoupon: function() {
		jQuery('#remove-coupone').val(0);
		if (!jQuery.trim(jQuery('#coupon_code').val()))
			return checkout.handleError("Please enter a valid promo code");
		jQuery('#coupon_apply_button').hide();
		jQuery('#coupon-apply-please-wait').show(400);

		jQuery.get(this.options.couponPostUrl, jQuery('#discount-coupon-form').serialize(), function(response) {
			if (response.error) {
				jQuery('#coupon-apply-please-wait').hide();
				jQuery('#coupon_apply_button').show(400);
				return checkout.handleError(response.error);
			} else {
				jQuery('#coupon-apply-please-wait').hide();
				jQuery('#coupon_apply_button').show(400);
				jQuery('#coupon-apply-link').hide();
				jQuery('#discount-coupon-form-wrapper').hide();
				jQuery('#coupon-cancel-link').show(400);
			}

			checkout.reloadProgressBlock(response.update_section.name);
			if (response.update_section.html) {
				checkout.setStepResponse(response);
			}
			jQuery('#checkout-review-table > tfoot > tr:last, #checkout-review-table > tfoot > tr:eq(1)').delay(200).queue(function(next){
				jQuery(this).addClass('shine');
				next();
			}).delay(1000).queue(function(next){
				jQuery(this).removeClass('shine');
				next();
			});
		}, 'json');
	},

	setPhoneMasking: function(form_type) {
		var ph = jQuery(jq(form_type+':telephone')).val(),
			new_ph = '';
			country = jQuery(jq(form_type+':country_id')).val(),
			city = '',
			state = '',
			mask = '';

		ph = ph.replace(/\D/g,''); // reject everything other than numbers

		switch(country) {
			case 'US':
			case 'CA':
				mask = "(999) 999-9999";
				break;
			case 'ES':
				mask = "(999) 999 99 99";
				break;
			case 'BR':
				mask = "(99) 9999-9999?9"; // (xx) xxxx-xxxx OR (11) 9xxxx-xxxx
				break;
			case 'DE':
				mask = "(9999) 999999";
				break;
			case 'FR':
				mask = "99 99 99 99 99";
				break;
			case 'AU':
				mask = "99 9999 9999";
				break;
			case 'RU':
				mask = "(9 9999) 99-99-99";
				break;
			case 'GB':
			case 'IT':
			default:
				jQuery(jq(form_type+':telephone')).unmask();
				jQuery(jq(form_type+':telephone')).val(ph);
		}
		if (mask != '') {
			jQuery(jq(form_type+':telephone')).mask(mask);
			// now replace 9 in mask with the actual numbers & others with underscore
			for (var digit=0; digit<mask.length; digit++) {
				if (mask[digit] == '9') {
					if ( ph.length ) {
						new_ph += ph.charAt(0);
						ph = ph.slice(1); // remove the first digit too
					} else {
						new_ph += '_';
					}
				} else {
					new_ph += mask[digit];
				}
			}
			jQuery(jq(form_type+':telephone')).val(new_ph);
		}
	},

	ajaxFailure: function() {}, // Braintree's payment extension support

	prepareAddressForm: function(addressType) {
		// placeholder attribute support for IE
		if ( checkout.placeholderSupport === false ) {

			jQuery('.input-text').each(function() {
				var field = jQuery(this);
				// Show placeholders initially on page load if the field is empty
				// Counter-attack timing issue
				setTimeout(function(){
					if (jQuery.trim(field.val()) == '' && field.attr('id') != 'shipping:region' && field.attr('id') != 'billing:region')
						field.parent().find('.ieplaceholder').show();
				},100);

				// Make sure placeholder is hidden when field is in focus
				field.live('focus', function() {
					field.parent().find('.ieplaceholder').hide();
				});

				// Upon blur, show placeholder only when the field is empty
				field.live('blur', function() {
					// Counter-attack timing issue
					setTimeout( function(){
						if ( jQuery.trim( field.val() ) == '' )
							field.parent().find('.ieplaceholder').show();
					},100);
				});
			});

			jQuery('.ieplaceholder').click(function(){
				jQuery(this).hide();
				jQuery(this).parent().find('input').focus();
			});
		}

		jQuery('.input-text, .validate-select').each(function() {
			jQuery(this).live('focus', function() {
				//show tip if we are already not on success for that field
				if (!jQuery(this).parents('.fields').find('.sidetip .good').is(':visible')) {
					jQuery(this).parents('.fields').find('.sidetip p').hide();
					jQuery(this).parents('.fields').find('.sidetip .tip').show();
				}
			});
			jQuery(this).live('blur', function() {
				jQuery(this).closest('form').validate(validatorSettings).element(this);
			});
		});

		jQuery(jq('shipping:country_label') + ',' + jq('billing:country_label')).autocomplete({
		source: function(request, response) {
			var matches = jQuery.map(countryOptions, function(tag) {
				if (tag.label.toUpperCase().indexOf(request.term.toUpperCase()) === 0) {
					return tag;
				}
			});
			response(matches);
		},
		select: function(event, ui) {
			event.target.value = ui.item.label;
			jQuery(event.target).next().val(ui.item.value);

			if(this.id === 'shipping:country_label'){
				shippingRegionUpdater.update();
				// region updater changes the state field amongst selectbox & text-input, also if the state field is required or not depends on the selected country
				// so let us remove the error class if it is present, if there is an error we will still trigger it later
				jQuery(jq('shipping:region_id')).closest('div.state').find('label').removeClass('err');
				jQuery(jq('shipping:region_id')).removeClass('error');
				jQuery(jq('shipping:region')).removeClass('error');
			}else if(this.id === 'billing:country_label'){
				billingRegionUpdater.update();
				// region updater changes the state field amongst selectbox & text-input also if the state field is required or not depends on the selected country
				// so let us remove the error class if it is present, if there is an error we will still trigger it later
				jQuery(jq('billing:region_id')).closest('div.state').find('label').removeClass('err');
				jQuery(jq('billing:region_id')).removeClass('error');
				jQuery(jq('billing:region')).removeClass('error');
			}
			// trigger change event manually since input[type=hidden] doesn't fire change event when its value changes
			jQuery(jq(event.target.id.replace('label','id'))).trigger('change');

			return false;
		},
		change: function(event, ui) {
			if (ui.item == null) {
				jQuery(event.target).next().val('');
				if (event.target.value != '' && checkout.isCountryAllowed(event.target.value) == false) {
					checkout.handleError(Translator.translate('Country is not allowed. Please select one from the list.'));
					event.target.value = '';
				}
			}
		},
		autoFocus: true,
		delay: 0
	});
	
	if(addressType === 'shipping'){
		// save shipping info on load, since we might have an address loaded already
		if(!checkout.options.isVirtual) {
			shipping.supressErrorsModal = 1;
			shipping.save(0);
		}

		// trigger the saving of shipping step when we have the change event on shipping form, which will be bubbled up by the corresponding input fields & select boxes
		jQuery('#co-shipping-form').change(function(event){
                        shipping.supressErrorsModal = 1;
			if(jQuery(event.target).is(jQuery(jq('shipping:telephone'))) && !(this.xhr && this.xhr.readystate != 4) && !jQuery('#shipping-address-select').val())
				shipping.reloadShippingMethods = false;
			else
				shipping.reloadShippingMethods = true;
			shipping.save(0);
		});
	}

	if(!checkout.options.disable_postcode_autocomplete)
	{
		(function() {
			var timeoutID;
			var prevValue;
			jQuery(jq('shipping:postcode') + ',' + jq('billing:postcode')).bind('keyup',function(e) {
				var value = String(jQuery(this).val());

				var that = this;

				// if we already have the keyup event queued, remove it's callback
				if(timeoutID) {
					clearTimeout(timeoutID);
				}

				if(prevValue === value) {
					// if the previously loaded value is same as the current value, then return. Useful for cases where event is triggered by keys such as Ctrl, Shift, Tab etc.
					return;
				}

				if (value.length >= 4) {
					// add the postcode lookup to the queue
					timeoutID = setTimeout(function() {

						if ( e.originalEvent === undefined ) {
							// triggered programmatically
						} else {
							// clicked by the user
						}

						checkout.postcodeAddress(that);

						prevValue = value;
					}, 500);
				}
			});
		})();
		// reload address from postcode on pageload
		jQuery(jq(addressType+':postcode')).trigger('keyup');
	}

	jQuery(jq('shipping:postcode') + ',' + jq('billing:postcode')).focus(function(){
		jQuery(this).attr('pattern', '\\d*');
		jQuery(this).attr('novalidate','novalidate');
		var v = jQuery(this.id.substr(0,this.id.indexOf(':')) + ':country_id').val();
		if ('AR' == v || 'CA' == v || 'NL' == v || 'UK' == v){
			jQuery(this).removeAttr('pattern');
			jQuery(this).removeAttr('novalidate');
		}
	});

	// submission of forms of a particular step using enter/return key
	jQuery(document).keypress(function(event) {
		if (event.which == 13) {
			// just hide modal box if it's modal box is active
			if ( jQuery('#error-message').is(':visible') ) {
				jQuery('#error-message').modal('hide');
			} else if (jQuery('input').not('#login-password').is(':focus')) { // any input element except the password field
				jQuery('#opc-' + checkout.activeSection).find('footer button').trigger('click');
			}
		}
	});

	jQuery(document).ajaxError(function(event, response) {
		if (response.readyState === 0 && response.status === 0) {
			// do nothing, this happens in cases like when we quickly reload, and browser cancels the ajax request
		} else if (response.readyState === 4 && response.status === 403) {
			window.location = checkout.options.failureUrl;
		} else if (response.status === 500) {
			checkout.handleError('Server 500 error!');
		} else if (response.status !== 200) {
			checkout.handleError('Server ' + response.status + ' error! (Ready State: ' + response.readyState + ')');
		}
	});
	}
});

/**
 * Shipping
 * @param options
 */
Shipping = function(options) {
	this.init(options);
}
jQuery.extend(Shipping.prototype, {

	options: null,
	selectedShippingMethod: null,

	init: function(options) {
		this.options = options;

		// change url hash, doesn't really need to be inside init, but semantically its correct to have it here
		if (window.history && 'replaceState' in window.history)
			window.history.replaceState({ step: 'shipping'},'','#shipping');
	},

	newAddress: function(isNew) {
		if (isNew) {
			jQuery(this.options.form).find('input[type=text], textarea').val('');
			jQuery(jq('shipping:region_id')).val('').hide();
			jQuery(jq('shipping:region')).show();
			if (checkout.placeholderSupport === false)
				jQuery('#co-shipping-form .ieplaceholder').show();
			this.resetSelectedAddress();
			jQuery('#shipping-new-address-form').show(400);
			if(!checkout.options.disable_postcode_autocomplete){
				jQuery(jq('shipping:postcode')).trigger('keyup');
				jQuery(jq('shipping-postcode-please-wait')).hide();
			}
			jQuery(jq('shipping:telephone')).closest('li').find('.sidetip').children().hide();
		} else {
			jQuery('#address-please-wait').fadeIn();
			jQuery.get(this.options.getAddressUrl, {
				address: jQuery('select[name=shipping_address_id]').val()
			}, function(data) {
				jQuery('#address-please-wait').fadeOut();
				shipping.fillForm(data);
			}, 'json');
			jQuery('#shipping-new-address-form').hide(400);
		}
	},

	fillForm: function(elementValues) {
		// hide all placeholders
		if (checkout.placeholderSupport === false) jQuery('.ieplaceholder').hide();

		arrElements = jQuery(':input', this.options.form).not('select[name="shipping_address_id"]').not('input[name="shipping[address_id]"]').not('input[name="shipping[save_in_address_book]"]');

		for (var elemIndex in arrElements) {
			if (arrElements[elemIndex].id) {
				var fieldName = arrElements[elemIndex].id.replace(/^shipping:/, '');
				arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';

				if (fieldName.indexOf('country') == -1 && fieldName.indexOf('region') == -1 && arrElements[elemIndex].value == '' && checkout.placeholderSupport === false) {
					jQuery(arrElements[elemIndex]).parent().find('.ieplaceholder').show();
				}
				if (fieldName == 'country_label') {
					arrElements[elemIndex].value = checkout.getCountryLabel(elementValues['country_id']);
				}
			}
		}

		// trigger blur event to format input as per masking
		jQuery(jq('shipping:telephone')).trigger('blur');

		shippingRegionUpdater.update();

		jQuery(this.options.form).valid();
		if(!checkout.options.separate_shipping_method_step)
		{
			// save shipping info after populating form
			this.save();
		}
	},

	_beforeSave: function() {
		// This can be overriden
	},

	save: function(validateFrom) {
		validateFrom = (typeof validateFrom === "undefined") ? 1 : validateFrom;
		if (checkout.loadWaiting != false && this.xhr && this.xhr.readystate != 4) {
			this.xhr.abort();
		}
		checkout.collectErrorsFlag = 1;
		var validator = jQuery(this.options.form).validate(validatorSettings);
		this._beforeSave();
		var isEmailFieldEmpty = typeof jQuery('#login-email').val() !== 'undefined'? jQuery('#login-email').val() : true;
		if(validateFrom === 1){
			if (validator.form()) {
				this.sendAjaxRequest();
			} else {
				var errors = checkout.errorsCollection.join('') ? checkout.errorsCollection.join('') : '<li>Incorrect Information</li>';
				checkout.handleError('<ul>'+errors+'</ul>','Can you go back and look at:');
				checkout.errorsCollection = []; // empty it
				shipping.triggerShippingMethodSave = false;
				shipping.triggerShippingSave = 0;
			}
		}else if((isEmailFieldEmpty
			&& jQuery(jq('shipping:fullname')).val()
			&& jQuery(jq('shipping:street1')).val()
			&& jQuery(jq('shipping:postcode')).val()
			&& jQuery(jq('shipping:city')).val()
			&& jQuery(jq('shipping:country_id')).val()) || jQuery('#shipping-address-select').val()){
				this.sendAjaxRequest();
		}
		checkout.collectErrorsFlag = 0;
	},
	
	sendAjaxRequest: function() {
		if(checkout.options.separate_shipping_method_step){
			jQuery('#shipping-buttons-container input.button').addClass('inactive').attr('disabled', 'disabled').val(shipping.options.savingMessage);
		}
		if (shipping.reloadShippingMethods) {
			if(!checkout.options.separate_shipping_method_step || shipping.triggerShippingSave){
				checkout.setLoadWaiting(shipping.triggerShippingMethodSave ? 'shipping-method' : 'shipping');
			}
			jQuery('#shipping-method-buttons-container input.button').addClass('inactive').attr('disabled', 'disabled').val(shipping.options.loadingMessage);
		}
		var data = jQuery(this.options.form).serialize();
		if(!jQuery(jq('shipping:street1')).val() || !jQuery(jq('shipping:fullname')).val() || !jQuery(jq('shipping:telephone')).val().replace(/\D/g,'') || !jQuery(jq('shipping:telephone')).closest('form').validate(validatorSettings).element(jQuery(jq('shipping:telephone'))))
			data = data.replace(/&?shipping%5Btelephone%5D=([^&]$|[^&]*)/i, "");
		this.xhr = jQuery.post(jQuery(this.options.form).attr('action'), data, this.nextStep, 'json');
	},

	nextStep: function(response) {
		checkout.setLoadWaiting(false);
		jQuery('#shipping-method-buttons-container input.button').removeClass('inactive').removeAttr('disabled').val(shipping.options.oldLoadingMessage.substring(0,19)+'→');
		if(checkout.options.separate_shipping_method_step){
			jQuery('#shipping-buttons-container input.button').removeClass('inactive').removeAttr('disabled').val(shipping.options.oldSavingMessage.substring(0,28)+'→');
		}
		if (shipping.supressErrorsModal) {
			shipping.collectedErrors = response.message;
		} else {
			if (response.error) {
				if (window.shippingRegionUpdater) {
					shippingRegionUpdater.regionTextEl.value = shippingRegionUpdater.regionSelectEl.value;
					shippingRegionUpdater.update();
				}
				shipping.triggerShippingMethodSave = false;
				shipping.triggerShippingSave = 0;
				return checkout.handleError(response.message);
			}
			shipping.collectedErrors = false;
		}
		if (checkout.options.separate_shipping_method_step) {
			if (shipping.triggerShippingSave) {
				shipping.reloadShippingMethods = true;
			} else {
				shipping.reloadShippingMethods = false;
			}
			shipping.triggerShippingSave = 0;
		}
		if (shipping.reloadShippingMethods)
			checkout.setStepResponse(response);
		shipping.reloadShippingMethods = true;
		
		// set the first shipping method as default if none is selected
		if (jQuery('#checkout-shipping-method-load input[type=radio]:checked').length == 0)
			jQuery('#checkout-shipping-method-load input[type=radio]').eq(0).attr('checked','checked');

		checkout.saveShippingMethodIndex();

		// change url hash
		if (window.history && 'replaceState' in window.history)
			window.history.pushState({ step: 'shipping_method'},'','#shipping_method');

		// if the shipping method was triggered previously, let's trigger it again now, this will show users error messages in case the shipping method was invalid
		if(shipping.triggerShippingMethodSave) {
			shippingMethod.save();
		}
		shipping.triggerShippingMethodSave = false;
	},

	getValidator: function() {
		return jQuery(this.options.form).validate(validatorSettings);
	},

	resetSelectedAddress: function() {
		var selectElement = $('shipping-address-select')
		if (selectElement) {
			selectElement.value = '';
		}
	},
});

/**
 * ShippingMethod
 * @param options
 */
ShippingMethod = function(options) {
	this.init(options);
}
jQuery.extend(ShippingMethod.prototype, {

	options: null,

	init: function(options) {
		this.options = options;
	},

	_beforeSave: function() {
		// This can be overriden
	},

	save: function() {
		shipping.supressErrorsModal = 0;
		shipping.triggerShippingMethodSave = true;
		if (checkout.loadWaiting != false) return;
		checkout.setLoadWaiting('shipping-method');
		if (!checkout.options.separate_shipping_method_step) {
			checkout.collectErrorsFlag = 1;
			var validator = jQuery(shipping.options.form).validate(validatorSettings);
			if (!validator.form()) {
				var errors = checkout.errorsCollection.join('') ? checkout.errorsCollection.join('') : '<li>Incorrect Information</li>';
				checkout.handleError('<ul>'+errors+'</ul>','Can you go back and look at:');
				checkout.errorsCollection = []; // empty it
				checkout.setLoadWaiting(false);
				shipping.triggerShippingMethodSave = false;
				return false;
			}
			checkout.collectErrorsFlag = 0;
		}
		if (shipping.collectedErrors){
			return shipping.save();
		}
		this._beforeSave();
		shipping.triggerShippingMethodSave = false;
		jQuery.post(jQuery(this.options.form).attr('action'), jQuery(this.options.form).serialize()+ "&"+jQuery('#co-shipping-form').serialize(), this.nextStep, 'json');

		// Check if we need to highlight shipping cost
		checkout.highlightShippingCost = ( checkout.getSavedShippingMethodIndex() != checkout.getCurrentShippingMethodIndex() ) ? true : false;
		checkout.saveShippingMethodIndex();
	},

	nextStep: function(response) {
		checkout.setLoadWaiting(false);
		if (shipping.collectedErrors){
			return checkout.handleError(shipping.collectedErrors);
		}
		if (response.error) {
			return checkout.handleError(response.message);
		}
		checkout.setStepResponse(response);

		// hide payment label if only one payment method is enabled
		if (jQuery('#checkout-payment-method-load dt').length == 1) {
			jQuery('#checkout-payment-method-load dt').hide();
			jQuery('#checkout-payment-method-load dt.free').show();
		}

		// change url hash
		if (window.history && 'replaceState' in window.history)
			window.history.pushState({ step: 'payment'},'','#payment');
	}
});

/**
 * Payment
 * @param options
 */
Payment = function(options) {
	this.init(options);
}
jQuery.extend(Payment.prototype, {

	options: null,

	init: function(options) {
		this.options = options;
		// Braintree's payment extension support
		var p = this;
		setTimeout(function() {
			p.form = jQuery(p.options.form);
		}, 1000);
	},

	_beforeSave: function() {
		// This can be overriden
	},

	save: function() {
		checkout.collectErrorsFlag = 1;
		var validator = jQuery(this.options.form).validate(validatorSettings);
		if (checkout.loadWaiting != false)
			return;
		this._beforeSave();
		if (validator.form()) {
			checkout.setLoadWaiting('payment');
			jQuery(this.options.form).append('<div id="temporary_password_div" style="display:none"><input type="hidden" name="password" value="'+jQuery('#login-password').val()+'"</div>');
			jQuery.post(jQuery(this.options.form).attr('action'), jQuery(this.options.form).serialize(), this.nextStep, 'json');
			jQuery('#temporary_password_div').remove();
		} else {
			var errors = checkout.errorsCollection.join('') ? checkout.errorsCollection.join('') : '<li>Incorrect Information</li>';
			checkout.handleError('<ul>'+errors+'</ul>','Can you go back and look at:');
			checkout.errorsCollection = []; // empty it
		}
		checkout.collectErrorsFlag = 0;
	},

	nextStep: function(response) {
		checkout.setLoadWaiting(false);
		if (response.error) {
			if (response.update_section !== undefined) {
				checkout.handleError(response.error);
			} else {
				return checkout.handleError(response.error);
			}
		}
		checkout.setStepResponse(response);

		// change url hash
		if (window.history && 'replaceState' in window.history)
			window.history.pushState({ step: 'review'},'','#review');

		// set focus to "Place order" button so that enter / return key can place orders too
		jQuery('#review-buttons-container button').focus();
	},

	switchMethod: function(method) {
		if (this.currentMethod && $('payment_form_' + this.currentMethod)) {
			this.changeVisible(this.currentMethod, true);
			$('payment_form_' + this.currentMethod).fire('payment-method:switched-off', {
				method_code: this.currentMethod
			});
		}
		if ($('payment_form_' + method)) {
			this.changeVisible(method, false);
			$('payment_form_' + method).fire('payment-method:switched', {
				method_code: method
			});
		} else {
			//Event fix for payment methods without form like "Check / Money order"
			document.body.fire('payment-method:switched', {
				method_code: method
			});
		}
		this.currentMethod = method;

		// add active class to active payment method
		jQuery('#checkout-payment-method-load .sp-methods dt').removeClass('active');
		jQuery('#checkout-payment-method-load .sp-methods dd').removeClass('active');
		jQuery('#checkout-payment-method-load .sp-methods dt.'+method).addClass('active');
		jQuery('#checkout-payment-method-load .sp-methods dd.'+method).addClass('active');
	},

	changeVisible: function(method, mode) {
		var block = '#payment_form_' + method;
		if (mode) {
			jQuery([block, block + '_before', block + '_after'].join(',')).hide().find(':input').attr('disabled', 'disabled');
		} else {
			jQuery([block, block + '_before', block + '_after'].join(',')).show().find(':input').removeAttr('disabled');
		}
	}
});

/**
 * Billing
 * @param options
 */
Billing = function(options) {
	this.init(options);
}
jQuery.extend(Billing.prototype, {

	options : null,
	sameAsBillingChecked: 1,

	init : function(options) {
		this.options = options;
	},

	newAddress: function(isNew) {
		if (isNew) {
			jQuery(this.options.form).find("input[type=text]:not('ul.payment-form *'), textarea:not('ul.payment-form *')").val('');
			jQuery(jq('billing:region_id')).val('').hide();
			jQuery(jq('billing:region')).show();
			if (checkout.placeholderSupport === false)
				jQuery('#co-payment-form .ieplaceholder').show();
			jQuery('#billing-new-address-form').show(400);
			if(!checkout.options.disable_postcode_autocomplete){
				jQuery(jq('billing:postcode')).trigger('keyup');
				jQuery(jq('billing-postcode-please-wait')).hide();
			}
			checkout.prepareAddressForm('billing');
		} else {
			jQuery('#billing-address-please-wait').fadeIn();
			jQuery.get(this.options.getAddressUrl, {
				address: jQuery('select[name=billing_address_id]').val()
			}, function(data) {
				jQuery('#billing-address-please-wait').fadeOut();
				billing.fillForm(data);
			}, 'json');
			jQuery('#billing-new-address-form').hide(400);
		}
	},

	fillForm: function(elementValues) {
		// hide all placeholders
		if (checkout.placeholderSupport === false) jQuery('.ieplaceholder').hide();

		arrElements = jQuery('#billing-new-address-form :input', this.options.form)
		.not('select[name="billing_address_id"]')
		.not('input[name="billing[address_id]"]');

		for (var elemIndex in arrElements) {
			if (arrElements[elemIndex].id) {
				var fieldName = arrElements[elemIndex].id.replace(/^billing:/, '');
				arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
				if (arrElements[elemIndex].value == '' && checkout.placeholderSupport === false) {
					jQuery(arrElements[elemIndex]).parent().find('.ieplaceholder').show();
				}

				if (fieldName.indexOf('country') == -1 && fieldName.indexOf('region') == -1 && arrElements[elemIndex].value == '' && checkout.placeholderSupport === false) {
					jQuery(arrElements[elemIndex]).parent().find('.ieplaceholder').show();
				}
				if (fieldName == 'country_label') {
					arrElements[elemIndex].value = checkout.getCountryLabel(elementValues['country_id']);
				}
			}
		}

		// trigger blur event to format input as per masking
		jQuery(jq('billing:telephone')).trigger('blur');

		billingRegionUpdater.update();

		jQuery(this.options.form).valid();
	},

	_beforeSave: function() {
		// This can be overriden
	},

	save : function() {
		var validator = this.getValidator();
		if (checkout.loadWaiting!=false) return;
		this._beforeSave();
		if(validator.form()) {
			checkout.setLoadWaiting('billing');
			jQuery.post(jQuery(this.options.form).attr('action'), jQuery(this.options.form).serialize(), this.nextStep, 'json');
		}
	},

	nextStep: function(response) {
		checkout.setLoadWaiting(false);

		if (window.billingRegionUpdater) {
			billingRegionUpdater.update();
		}

		if (response.error) {
			return checkout.handleError(response.message);
		}

		checkout.setStepResponse(response);

		// hide payment label if only one payment method is enabled
		if (jQuery('#checkout-payment-method-load dt').length == 1)
			jQuery('#checkout-payment-method-load dt').hide();

		// change url hash
		if (window.history && 'replaceState' in window.history)
			window.history.pushState({ step: 'payment'},'','#payment');
	},

	getValidator: function() {
		return jQuery(this.options.form).validate(validatorSettings);
	},

	addressSameAsBilling: function() {
		jQuery('#billing-address-previously-saved').toggle(400);
		if (jQuery('#billing-address-dropdown').length && jQuery('#billing-address-dropdown').is(":hidden")) {
			jQuery('#billing-address-dropdown').toggle(400);
			if (jQuery('#billing-address-select').val() === '') {
				jQuery('#billing-new-address-form').show(400);
			}
		} else if (jQuery('#billing-address-dropdown').length) {
			jQuery('#billing-address-dropdown').toggle(400);
			jQuery('#billing-new-address-form').hide(400);
		} else {
			jQuery('#billing-new-address-form').toggle(400);
			if(!checkout.options.disable_postcode_autocomplete){
				jQuery('#billing-postcode-please-wait').hide();
			}
		}
		checkout.prepareAddressForm('billing');
		if(!jQuery('.dflt-adrs-labl input[type=checkbox][name="billing[same_as_shipping]"]:checked').length){
			billing.sameAsBillingChecked = 0;
		}else{
			billing.sameAsBillingChecked = 1;
		}
	},
});

/**
 * Review
 * @param options
 */
Review = function(options) {
	this.init(options);
}
jQuery.extend(Review.prototype, {

	options: null,

	init: function(options) {
		this.options = options;
		// Braintree's payment extension support
		if('sagepaypaypal' === payment.currentMethod) {
			var p = this;
			setTimeout(function() {
				p.options.saveUrl = p.saveUrl;
			}, 1000);
		} else {
			var p = this;
			setTimeout(function() {
				p.saveUrl = p.options.saveUrl;
			}, 1000);
		}
	},

	_beforeSave: function() {
		// This can be overriden
	},

	save: function() {
		if (checkout.loadWaiting != false) return;
		this._beforeSave();
		checkout.setLoadWaiting('review');

		//see if the sagepay payment method has been selected & call their function if it is, it also does the ajax request & call nextStep
		if('sagepaydirectpro' == payment.currentMethod || 'sagepayform' == payment.currentMethod) {
			return new Ajax.Request(SuiteConfig.getConfig('global', 'sgps_saveorder_url'),{
				method:"post",
				parameters: Form.serialize($$(payment.options.form)[0]),
				onSuccess:function(f){
					SageServer.reviewSave(f);
				}.bind(SageServer)
			});
		}
		
		if (payment.currentMethod + 'Save' in this) {
			var params = this[payment.currentMethod + 'Save']();
		}
		else
		{
			var params = jQuery(payment.options.form).serialize();
		}

		var disabled = jQuery(this.options.agreementsForm).find(':input:disabled').removeAttr('disabled');
		params += '&' + jQuery(this.options.agreementsForm).serialize();
		params += '&' + jQuery(this.options.giftMessageForm).serialize();
		if (jQuery(this.options.newsletterForm).length)
			params += '&' + jQuery(this.options.newsletterForm).serialize();
		disabled.attr('disabled', 'disabled');

		params.save = true;
		jQuery.post(this.options.saveUrl, params, this.nextStep, 'json');
	},

	nextStep: function(response) {
		//sagepay sends in the raw xhr object over to this function but we send in a json object, here we check for raw xhr object & try to convert it to json
		try {
			if(4 == response.readyState && 200 == response.status)
				response = jQuery.parseJSON(response.responseText);
		} catch(e) {
			//suppressed the error for all the cases where it's not sagepay
		}

		checkout.setLoadWaiting(false);
		if (response.redirect) {
			location.href = response.redirect;
			return;
		}
		if (response.success) {
			window.location = review.options.successUrl;
		} else {
			checkout.handleError(response.error_messages);
		}

		/*if (response.update_section) {
			jQuery('#checkout-' + response.update_section.name + '-load').html(response.update_section.html);
		}*/

		if (response.goto_section) {
			checkout.gotoSection(response.goto_section);
			checkout.reloadProgressBlock();
		}
	}
});

/* http://docs.jquery.com/Frequently_Asked_Questions#How_do_I_select_an_element_by_an_ID_that_has_characters_used_in_CSS_notation.3F */

function jq(id) {
	return '#' + id.replace(/(:|\.)/g, '\\$1');
}

function numberOfDigitsPerCountry(country_id) {
	switch(country_id) {
		case 'US':
		case 'CA':
			return 10;//"(999) 999-9999";
			break;
		case 'ES':
			return 10;//"(999) 999 99 99";
			break;
		case 'BR':
			return [10, 11];//"(99) 9999-9999?9"; // (xx) xxxx-xxxx OR (11) 9xxxx-xxxx
			break;
		case 'DE':
			return 10;//"(9999) 999999";
			break;
		case 'FR':
			return 10;//"99 99 99 99 99";
			break;
		case 'AU':
			return 10;//"99 9999 9999";
			break;
		case 'RU':
			return 11;//"(9 9999) 99-99-99";
			break;
		case 'GB':
		case 'IT':
		default:
			return 0;
	}
}

/* http://docs.jquery.com/Plugins/Validation */
jQuery.validator.addMethod("fullname", function(value, element) {
	return (/^[^\d|\s]+\s[^\d]+$/.test(value));
}, "* Fullname should consist of at least first and last names.");

jQuery.validator.addMethod("maskedPostcodeAtleast4Digits", function(value, element) {
	value = value.replace(/_/g,'').replace(/-/g,''); // remove masking junk (dash & underscores)
	return value.length >= 4;
}, "* Postcode needs to be of atleast 4 digits.");

jQuery.validator.addMethod("maskedTelephoneAllDigits", function(value, element) {
	value = value.replace(/\D/g,''); // reject everything other than numbers
	if ( !value && !jQuery(element).hasClass('required') ) {
		return true;
	}
	if(value != parseInt(value, 10))
		return false;
	var form_type = jQuery('#checkoutSteps > li.active form').attr('id').split('-')[1];
	if ('payment' == form_type)
		form_type = 'billing';
	country_id = jQuery(jq(form_type+':country_id')).val();
	var numberOfDigits = numberOfDigitsPerCountry(country_id);
	if (jQuery.isArray(numberOfDigits)) { // if number of possible lengths are multiple
		var flag = false;
		numberOfDigits.forEach(function(element) {
			if(element == value.length)
				flag = true;
		});
		return flag;
	} else if ((parseInt(numberOfDigits, 10)) != 0) { // if number of possible length is a single number
		return (parseInt(numberOfDigits, 10)) == value.length;
	} else { // default case when any length number is acceptable since its known for that country
		return true;
	}
}, "* Phone needs to be of atleast 5 digits & should be valid for your selected country.");

jQuery.validator.addClassRules({
	'required-entry': {
		required: true
	},
	'postcode': {
		maskedPostcodeAtleast4Digits: ''
	},
	'telephone': {
		maskedTelephoneAllDigits: ''
	},
	'validate-cc-number': {
		creditcard2: ''
	}
});
