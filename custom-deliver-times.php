<?php
/**
 * Plugin Name: Custom Deliver Times
 * Description: WooCommerce custom deliver times
 * Version: 0.1
 * Author: Richard Danklof
 * Author URI: https://www.richarddanklof.nl
 */

require_once __DIR__ . '/vendor/autoload.php';

use CustomDeliverTimes\DeliveryTimes;

add_action('woocommerce_before_order_notes', 'cdt_field');
function cdt_field($checkout): void
{
    $excludedDates = explode("\n", get_option('cdt_excluded_dates', ''));
    $maxOrdersPerSlot = get_option('cdt_max_orders_per_slot', 3);

    (new DeliveryTimes($excludedDates, $maxOrdersPerSlot))->render($checkout);
}

/**
 * Checkout Process
 */
add_action('woocommerce_checkout_process', 'cdt_field_process');
function cdt_field_process()
{
    // Show an error message if the field is not set.
    if (!$_POST['delivery_moment']) {
        wc_add_notice(__('Please enter value!'), 'error');
    }
}

/**
 * Update the value given in custom field
 */
add_action('woocommerce_checkout_update_order_meta', 'cdt_field_update_order_meta');
function cdt_field_update_order_meta($order_id)
{
    if (!empty($_POST['delivery_moment'])) {
        update_post_meta($order_id, 'Gewenst Bezorgmoment', sanitize_text_field($_POST['delivery_moment']));
    }
}

/**
 * Display field value on the order edit page
 */
add_action('woocommerce_admin_order_data_after_billing_address', 'cdt_field_display_admin_order_meta', 10, 1);
function cdt_field_display_admin_order_meta($order)
{
    echo '<p><strong>' . __('Gewenst Bezorgmoment') . ':</strong> <br/>' . get_post_meta($order->get_id(),
            'Gewenst Bezorgmoment', true) . '</p>';
}

/**
 * Add a custom field (in an order) to the emails
 */
add_filter('woocommerce_email_order_meta_fields', 'cdt_woocommerce_email_order_meta_fields', 10, 3);
function cdt_woocommerce_email_order_meta_fields($fields, $sent_to_admin, $order)
{
    $fields['Gewenst Bezorgmoment'] = [
        'label' => 'Gewenst Bezorgmoment',
        'value' => get_post_meta($order->id, 'Gewenst Bezorgmoment', true),
    ];
    return $fields;
}

add_action('admin_init', 'cdt_register_settings');
function cdt_register_settings()
{
    add_option('cdt_max_orders_per_slot', 3);
    register_setting('cdt_options_group', 'cdt_max_orders_per_slot', 'cdt_callback');

    add_option('cdt_excluded_dates', '');
    register_setting('cdt_options_group', 'cdt_excluded_dates', 'cdt_callback');
}

add_action('admin_menu', 'cdt_register_options_page');
function cdt_register_options_page()
{
    add_options_page('Custom Deliver Times', 'Custom Deliver Times', 'manage_options', 'custom-deliver-times',
        'cdt_options_page');
}

function cdt_options_page()
{
    ?>
    <div>
        <h2>Custom Deliver Times</h2>
        <form method="post" action="options.php">
            <?php settings_fields('cdt_options_group'); ?>
            <p>Stel op deze pagina het maximaal aantal orders per tijdslot in.</p>
            <table>
                <tr>
                    <th scope="row"><label for="cdt_max_orders_per_slot">Max Orders Per Timeslot</label></th>
                    <td><input type="number" id="cdt_max_orders_per_slot" name="cdt_max_orders_per_slot"
                               value="<?= get_option('cdt_max_orders_per_slot'); ?>"/></td>
                </tr>
                <tr>
                    <th scope="row"><label for="cdt_excluded_dates">Uitgesloten Datums</label></th>
                    <td><textarea id="cdt_excluded_dates"
                                  name="cdt_excluded_dates"><?= get_option('cdt_excluded_dates'); ?></textarea></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
} ?>
