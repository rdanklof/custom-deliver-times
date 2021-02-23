<?php

namespace CustomDeliverTimes;

use Carbon\Carbon;

class Orders
{
    public function getOrdersByDeliveryDateAndTime(Carbon $date, string $timeSlot): int
    {
        global $wpdb;

        $deliveryTime = $date->format('d-m-Y') . ' ' . $timeSlot;

        $orderCount = (int) $wpdb->get_var("SELECT 
                COUNT(wp_posts.id)
            FROM
                wp_posts
            INNER JOIN
                wp_postmeta 
            ON
                (wp_postmeta.post_id = wp_posts.id AND wp_postmeta.meta_key = 'Gewenst Bezorgmoment' AND wp_postmeta.meta_value = '{$deliveryTime}')
            WHERE 
                wp_posts.post_type = 'shop_order'
            GROUP BY 
                wp_postmeta.meta_value");

        return $orderCount ?? 0;
    }
}
