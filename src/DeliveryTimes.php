<?php

namespace CustomDeliverTimes;

use Carbon\Carbon;

class DeliveryTimes
{
    public function list(): array
    {
        date_default_timezone_set('Europe/Amsterdam');
        Carbon::setLocale('nl-NL');

        $dates = new Dates();
        $times = new Times();
        $orders = new Orders();

        $options = [];

        foreach ($dates->list() as $date) {
            foreach ($times->list() as $timeSlot) {
                $label = $dates->getLabel($date);
                $orderCount = $orders->getOrdersByDeliveryDateAndTime($date, $timeSlot);
                if ($orderCount >= 3) {
                    continue;
                }
                $value = $date->format('d-m-Y') . ' ' . $timeSlot;
                $label .= ' ' . $timeSlot;

                if (WP_DEBUG) {
                    $label .= ' (' . $orderCount . ')';
                }

                $options[$value] = $label;
            }
        }

        return $options;
    }

    public function render($checkout): void
    {
        echo '<div id="custom_checkout_field">';

        woocommerce_form_field('delivery_moment', [
            'type' => 'select',
            'class' => [
                'my-field-class form-row-wide',
            ],
            'options' => $this->list(),
            'required' => 1,
            'label' => __('Gewenst Bezorgmoment'),
        ], $checkout->get_value('delivery_moment'));

        echo '</div>';
    }
}
