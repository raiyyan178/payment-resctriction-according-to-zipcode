<?php
/*
Plugin Name: Dynamic ZIP Code Payment Restriction
Description: Restricts WooCommerce payment options based on shipping postcode in Checkout Block, bypassing functionality if shipping postcode is empty.
Author: Apex Web Studios
Version: 2.3
*/

// Prevent direct access to this file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue JavaScript and CSS
function dzpr_enqueue_zipcode_scripts() {
    if ( is_checkout() && ! is_wc_endpoint_url() ) {
        wp_enqueue_script( 'dzpr-zipcode-validation', plugin_dir_url( __FILE__ ) . 'js/zipcode-validation.js', array( 'jquery', 'wp-data' ), '2.3', true );
        wp_enqueue_style( 'dzpr-styles', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), '2.3' );
        $allowed_zipcodes = get_option( 'dzpr_allowed_zipcodes', '' );
        $allowed_zipcodes_array = array_filter( array_map( 'trim', explode( ',', $allowed_zipcodes ) ) );
        wp_localize_script( 'dzpr-zipcode-validation', 'dzpr_params', array(
            'allowed_zipcodes' => array_values( $allowed_zipcodes_array ),
            'error_message' => __( 'Delivery not available in your location.', 'woocommerce' ),
            'is_block_checkout' => function_exists( 'has_block' ) && has_block( 'woocommerce/checkout' )
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'dzpr_enqueue_zipcode_scripts' );

// Save shipping ZIP code to order meta
function dzpr_save_zipcode_field( $order_id ) {
    $zipcode = ! empty( $_POST['shipping_postcode'] ) ? sanitize_text_field( $_POST['shipping_postcode'] ) : '';
    if ( ! empty( $zipcode ) ) {
        update_post_meta( $order_id, '_delivery_zipcode', $zipcode );
    }
}
add_action( 'woocommerce_checkout_update_order_meta', 'dzpr_save_zipcode_field' );

// Server-side validation for shipping ZIP code
function dzpr_validate_zipcode() {
    $allowed_zipcodes = get_option( 'dzpr_allowed_zipcodes', '' );
    $allowed_zipcodes_array = array_filter( array_map( 'trim', explode( ',', $allowed_zipcodes ) ) );
    $zipcode = ! empty( $_POST['shipping_postcode'] ) ? sanitize_text_field( $_POST['shipping_postcode'] ) : '';
    if ( ! empty( $zipcode ) && ! empty( $allowed_zipcodes_array ) && ! in_array( $zipcode, $allowed_zipcodes_array ) ) {
        wc_add_notice( __( 'Delivery not available in your location.', 'woocommerce' ), 'error' );
    }
    if ( ! empty( $zipcode ) && ! preg_match( '/^[0-9]{5}$/', $zipcode ) ) {
        wc_add_notice( __( 'Please enter a valid 5-digit ZIP code for shipping.', 'woocommerce' ), 'error' );
    }
}
add_action( 'woocommerce_checkout_process', 'dzpr_validate_zipcode' );

// Register settings
function dzpr_register_settings() {
    add_option( 'dzpr_allowed_zipcodes', '' );
    register_setting( 'dzpr_settings_group', 'dzpr_allowed_zipcodes', array(
        'sanitize_callback' => 'dzpr_sanitize_zipcodes',
    ) );
}
add_action( 'admin_init', 'dzpr_register_settings' );

// Sanitize ZIP codes
function dzpr_sanitize_zipcodes( $input ) {
    $zipcodes = array_map( 'trim', explode( ',', $input ) );
    $valid_zipcodes = array();
    foreach ( $zipcodes as $zipcode ) {
        if ( preg_match( '/^[0-9]{5}$/', $zipcode ) ) {
            $valid_zipcodes[] = $zipcode;
        }
    }
    return implode( ',', $valid_zipcodes );
}

// Add admin settings page
function dzpr_add_admin_menu() {
    add_submenu_page(
        'woocommerce',
        __( 'ZIP Code Settings', 'woocommerce' ),
        __( 'ZIP Code Settings', 'woocommerce' ),
        'manage_woocommerce',
        'dzpr-settings',
        'dzpr_settings_page'
    );
}
add_action( 'admin_menu', 'dzpr_add_admin_menu' );

function dzpr_settings_page() {
    $allowed_zipcodes = get_option( 'dzpr_allowed_zipcodes', '' );
    $count = ! empty( $allowed_zipcodes ) ? count( array_filter( explode( ',', $allowed_zipcodes ) ) ) : 0;
    ?>
    <div class="wrap">
        <h1><?php _e( 'ZIP Code Payment Restriction Settings', 'woocommerce' ); ?></h1>
        <p>Current allowed ZIP codes: <?php echo esc_html( $allowed_zipcodes ? $allowed_zipcodes : 'None' ); ?> (<?php echo $count; ?> total)</p>
        <?php if ( function_exists( 'has_block' ) && has_block( 'woocommerce/checkout' ) ) : ?>
            <p class="description" style="color: blue;">Checkout Block detected. Using shipping postcode for validation.</p>
        <?php endif; ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'dzpr_settings_group' ); ?>
            <?php do_settings_sections( 'dzpr_settings_group' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="dzpr_allowed_zipcodes"><?php _e( 'Allowed ZIP Codes', 'woocommerce' ); ?></label>
                    </th>
                    <td>
                        <textarea name="dzpr_allowed_zipcodes" id="dzpr_allowed_zipcodes" rows="5" cols="50"><?php echo esc_textarea( $allowed_zipcodes ); ?></textarea>
                        <p class="description"><?php _e( 'Enter allowed 5-digit ZIP codes for shipping, separated by commas (e.g., 80902,80903,80904). Invalid ZIP codes will be ignored. Leave empty to allow all ZIP codes.', 'woocommerce' ); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Activation hook
function dzpr_activate() {
    add_option( 'dzpr_allowed_zipcodes', '' );
}
register_activation_hook( __FILE__, 'dzpr_activate' );

// Deactivation hook
function dzpr_deactivate() {
    // No cleanup needed
}
register_deactivation_hook( __FILE__, 'dzpr_deactivate' );