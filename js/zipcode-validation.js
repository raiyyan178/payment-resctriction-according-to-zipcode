jQuery(document).ready(function($) {
    // Get ZIP code from shipping only using DOM or data store
    function get_zipcode() {
        try {
            var zipcode = $('#shipping-postcode').val() || '';
            if (!zipcode && typeof wp !== 'undefined' && wp.data) {
                var checkoutState = wp.data.select('wc/store/checkout');
                if (checkoutState) {
                    zipcode = checkoutState.getShippingAddress().postcode || '';
                }
            }
            return zipcode ? zipcode.trim() : '';
        } catch (e) {
            return '';
        }
    }

    // Validate shipping ZIP code and update UI
    function validate_zipcode() {
        try {
            var zipcode = get_zipcode();

            // Skip all functionality if shipping ZIP code is empty
            if (zipcode === '') {
                $('.wc-block-components-notice.dzpr-notice').remove();
                $('#payment-method, .wc-block-checkout__payment-method').removeClass('hidden').show();
                $('.wc-block-components-checkout-place-order-button, button[name="woocommerce_checkout_place_order"]').prop('disabled', false).removeClass('disabled');
                return;
            }

            var $payment_section = $('#payment-method, .wc-block-checkout__payment-method');
            var $place_order_button = $('.wc-block-components-checkout-place-order-button, button[name="woocommerce_checkout_place_order"]');

            // Remove existing notices
            $('.wc-block-components-notice.dzpr-notice').remove();

            // Validation logic for non-empty shipping ZIP codes
            if (dzpr_params.allowed_zipcodes.length === 0) {
                $payment_section.removeClass('hidden').show();
                $place_order_button.prop('disabled', false).removeClass('disabled');
            } else if (dzpr_params.allowed_zipcodes.includes(zipcode)) {
                $payment_section.removeClass('hidden').show();
                $place_order_button.prop('disabled', false).removeClass('disabled');
            } else {
                $payment_section.addClass('hidden').hide();
                $place_order_button.prop('disabled', true).addClass('disabled');
                $('<div class="wc-block-components-notice dzpr-notice"><div class="wc-block-components-notice__content">' + dzpr_params.error_message + '</div></div>')
                    .insertBefore('#payment-method, .wc-block-checkout__payment-method');
            }
        } catch (e) {
            // Silently handle errors
        }
    }

    // Retry finding payment section only when needed
    function initialize_validation() {
        var zipcode = get_zipcode();
        if (zipcode === '') {
            return;
        }

        var attempts = 0;
        var max_attempts = 30;
        var interval = setInterval(function() {
            var $payment_section = $('#payment-method, .wc-block-checkout__payment-method');
            attempts++;
            if ($payment_section.length > 0) {
                clearInterval(interval);
                validate_zipcode();
            } else if (attempts >= max_attempts) {
                alert('ZIP Code Validation Error: Payment section not found after multiple attempts. Please contact support.');
                clearInterval(interval);
            }
        }, 500);
    }

    // Block checkout: Filter payment methods
    if (dzpr_params.is_block_checkout && typeof wp !== 'undefined' && wp.hooks) {
        wp.hooks.addFilter(
            'woocommerce_available_payment_gateways',
            'dzpr_plugin',
            function(gateways) {
                try {
                    var zipcode = get_zipcode();
                    if (zipcode === '') {
                        return gateways;
                    }
                    if (dzpr_params.allowed_zipcodes.length === 0) {
                        return gateways;
                    }
                    if (dzpr_params.allowed_zipcodes.includes(zipcode)) {
                        return gateways;
                    }
                    return {};
                } catch (e) {
                    return gateways;
                }
            }
        );
    }

    // Event delegation for shipping postcode only
    $(document).on('input blur change', '#shipping-postcode', function() {
        validate_zipcode();
    });

    // Monitor billing/shipping toggle
    $(document).on('change', '#checkbox-control-0', function() {
        setTimeout(validate_zipcode, 100);
    });

    // Monitor checkout state changes for shipping postcode
    if (typeof wp !== 'undefined' && wp.data) {
        var lastZipcode = '';
        wp.data.subscribe(function() {
            try {
                var zipcode = get_zipcode();
                if (zipcode !== lastZipcode) {
                    lastZipcode = zipcode;
                    validate_zipcode();
                }
            } catch (e) {
                // Silently handle errors
            }
        });
    }

    // Initial validation
    try {
        initialize_validation();
    } catch (e) {
        // Silently handle errors
    }
});