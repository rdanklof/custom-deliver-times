<?php
/**
 * Plugin Name: Custom Delivery Times
 * Plugin URI: https://www.richarddanklof.nl
 * Description: WooCommerce custom delivery times
 * Version: 0.1
 * Author: Richard Danklof
 * Author URI: https://www.richarddanklof.nl
 */

require_once __DIR__ . '/vendor/autoload.php';

use CustomDeliverTimes\Dates;

/**
 * Add custom field to the checkout page
 */
add_action('woocommerce_before_order_notes', 'custom_checkout_field');
function custom_checkout_field($checkout)
{
    date_default_timezone_set('Europe/Amsterdam');

    echo '<div id="custom_checkout_field">';


    $query_args = array(
        'fields' => 'ids',
        'post_type' => 'shop_order',
        'post_status' => array_keys( wc_get_order_statuses() ),
        'posts_per_page' => -1,
//        'numberposts' => -1,
        'date_query' => array(
            array(
                'before' => date('Y-m-d', $end),
                'after'  => '2020-12-01',//date('Y-m-d', $start),
                'inclusive' => true,
            ),
        ),
    );

    while ($start <= $end) {
        $isToday = date('Y-m-d') === date('Y-m-d', $start);
        $isTomorrow = date('Y-m-d', time() + 86400) === date('Y-m-d', $start);
        $weekDay = date('N', $start);

        if ($isToday && date('H') > 10) {
            $start += 86400;
            continue;
        }
        global $wpdb;
        if ($weekDay <= 5) {
            foreach ($timeSlots as $timeSlot) {
                $value = date('d-m-Y', $start) . ' ' . $timeSlot;
                $order_count = $wpdb->get_var("SELECT
                    COUNT(wp_posts.id)
                FROM
                    `wp_posts`
                INNER JOIN
                    `wp_postmeta` ON (wp_postmeta.post_id = wp_posts.id AND wp_postmeta.meta_key = 'Gewenst Bezorgmoment' AND `wp_postmeta`.`meta_value` = '" . $value . "')
                WHERE
                        `wp_posts`.`post_type` = 'shop_order'
                GROUP BY
                    `wp_postmeta`.`meta_value`");

                if ($order_count >= 3) {
                    continue;
                }

                if ($isToday) {
                    $label = 'Vandaag';
                } elseif ($isTomorrow) {
                    $label = 'Morgen';
                } else {
                    $label = $weekDays[$weekDay] . ' ' . date('j', $start) . ' ' . strtolower($months[date('n',
                            $start)]);
                }

                $label .= ' ' . $timeSlot;

                $options[$value] = $label;
            }
        }

        $start += 86400;
        $count++;

        if ($count === 3) {
            continue;
        }
    }

    woocommerce_form_field('delivery_moment', [
        'type' => 'select',
        'class' => [
            'my-field-class form-row-wide',
        ],
        'options' => $options,
        'required' => 1,
        'label' => __('Gewenst Bezorgmoment'),
    ],
        $checkout->get_value('delivery_moment')
    );

    echo '</div>';
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
