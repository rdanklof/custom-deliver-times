<?php
/**
 * Plugin Name: Custom Delivery Times
 * Description: WooCommerce custom delivery times
 * Version: 0.1
 * Author: Richard Danklof
 * Author URI: https://www.richarddanklof.nl
 */

require_once __DIR__ . '/vendor/autoload.php';

use CustomDeliverTimes\DeliveryTimes;

add_action('woocommerce_before_order_notes', 'custom_checkout_field');
function custom_checkout_field($checkout): void
{
    (new DeliveryTimes)->render($checkout);
}

/**
 * Checkout Process
 */
add_action('woocommerce_checkout_process', 'customised_checkout_field_process');
function customised_checkout_field_process()
{
    // Show an error message if the field is not set.
    if (!$_POST['delivery_moment']) wc_add_notice(__('Please enter value!') , 'error');
}

/**
 * Update the value given in custom field
 */
add_action('woocommerce_checkout_update_order_meta', 'custom_checkout_field_update_order_meta');
function custom_checkout_field_update_order_meta($order_id)
{
    if (!empty($_POST['delivery_moment'])) {
        update_post_meta($order_id, 'Gewenst Bezorgmoment', sanitize_text_field($_POST['delivery_moment']));
    }
}

/**
 * Display field value on the order edit page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

function my_custom_checkout_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('Gewenst Bezorgmoment').':</strong> <br/>' . get_post_meta( $order->get_id(), 'Gewenst Bezorgmoment', true ) . '</p>';
}

/**
 * Add a custom field (in an order) to the emails
 */
add_filter( 'woocommerce_email_order_meta_fields', 'custom_woocommerce_email_order_meta_fields', 10, 3 );
function custom_woocommerce_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
    $fields['Gewenst Bezorgmoment'] = [
        'label' => 'Gewenst Bezorgmoment',
        'value' => get_post_meta( $order->id, 'Gewenst Bezorgmoment', true ),
    ];
    return $fields;
}

//
//function dbi_add_settings_page() {
//    add_options_page( 'Custom Deliver Times', 'Custom Deliver Times', 'manage_options', 'custom-deliver-times',
//    'dbi_render_plugin_settings_page' );
//}
//add_action( 'admin_menu', 'dbi_add_settings_page' );
//
//
//function dbi_render_plugin_settings_page() {
//    ?>
<!--    <h2>Custom Deliver Times</h2>-->
<!--    <form action="options.php" method="post">-->
<!--        --><?php
//        settings_fields( 'dbi_example_plugin_options' );
//        do_settings_sections( 'dbi_example_plugin' ); ?>
<!--        <input name="submit" class="button button-primary" type="submit" value="--><?php //esc_attr_e( 'Save' ); ?><!--" />-->
<!--    </form>-->
<!--    --><?php
//}
//
//function dbi_register_settings() {
//    register_setting( 'dbi_example_plugin_options', 'dbi_example_plugin_options', 'dbi_example_plugin_options_validate' );
//    add_settings_section( 'api_settings', 'API Settings', 'dbi_plugin_section_text', 'dbi_example_plugin' );
//
//    add_settings_field( 'dbi_plugin_setting_api_key', 'API Key', 'dbi_plugin_setting_api_key', 'dbi_example_plugin', 'api_settings' );
//    add_settings_field( 'dbi_plugin_setting_results_limit', 'Results Limit', 'dbi_plugin_setting_results_limit', 'dbi_example_plugin', 'api_settings' );
//    add_settings_field( 'dbi_plugin_setting_start_date', 'Start Date', 'dbi_plugin_setting_start_date', 'dbi_example_plugin', 'api_settings' );
//}
//add_action( 'admin_init', 'dbi_register_settings' );
