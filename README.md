# Dynamic ZIP Code Payment Restriction for WooCommerce

A lightweight WooCommerce plugin that restricts payment methods based on the customer's shipping ZIP code in the Checkout Block. If the shipping ZIP code is empty, the plugin is bypassed, ensuring all payment methods remain available. Billing ZIP codes are ignored, maintaining default WooCommerce behavior.

## Features
- Validates 5-digit shipping ZIP codes against a list defined in WooCommerce settings.
- Bypasses all restrictions if the shipping ZIP code is empty, ensuring a smooth checkout.
- Ignores billing ZIP code, preserving default WooCommerce behavior.
- Saves validated shipping ZIP code to order meta for record-keeping.
- Displays a clear error message for invalid shipping ZIP codes.
- Compatible with WooCommerce Checkout Block for real-time validation.
- Easy configuration via **WooCommerce > ZIP Code Settings**.

## Installation
1. Download or clone this repository.
2. Upload the `dynamic-zipcode-payment-restriction` folder to `wp-content/plugins/` on your WordPress site.
3. Activate the plugin in **Plugins > Installed Plugins**.
4. Go to **WooCommerce > ZIP Code Settings** and enter allowed 5-digit ZIP codes (e.g., `80902,80903,80904`), separated by commas.
5. Clear any caching plugins and exclude the `/checkout/` page from caching.

## Usage
- **Configure ZIP Codes**: In **WooCommerce > ZIP Code Settings**, enter a comma-separated list of allowed 5-digit ZIP codes. Leave empty to allow all ZIP codes.
- **Checkout Behavior**:
  - If the shipping ZIP code is empty, all payment methods (e.g., Authorize.Net) are available, and checkout proceeds normally.
  - If a valid shipping ZIP code is entered (matches the allowed list), payment methods are shown.
  - If an invalid shipping ZIP code is entered, payment methods are hidden, and an error message ("Delivery not available in your location") is displayed.
  - Billing ZIP codes do not affect payment method availability.
- **Order Meta**: Validated shipping ZIP codes are saved as `_delivery_zipcode` in the order details.

## Requirements
- WordPress 5.0 or higher
- WooCommerce 8.0 or higher
- PHP 7.4 or higher

## Compatibility
- Tested with WooCommerce Checkout Block.
- Compatible with payment gateways like Authorize.Net (ensure your gateway supports Checkout Block).
- May require testing with custom themes or plugins for full compatibility.

## Troubleshooting
If payment methods are hidden unexpectedly:
1. **Check ZIP Code Settings**: Ensure allowed ZIP codes are correctly entered in **WooCommerce > ZIP Code Settings**.
2. **Clear Cache**: Exclude `/checkout/` in caching plugins and clear browser cache.
3. **Test with Default Theme**: Switch to Storefront theme to rule out theme conflicts.
4. **Disable Plugins**: Deactivate other plugins except WooCommerce and your payment gateway to check for conflicts.
5. **Verify Selectors**: Ensure `#shipping-postcode` and `#payment-method` exist in your checkout DOM (use browser developer tools).
6. **Classic Checkout**: If issues persist, revert to classic checkout in **Appearance > Editor > Templates > Page: Checkout > Revert to Classic Template**
