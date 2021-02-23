<?php

namespace CustomDeliverTimes;

class DeliveryTimes
{
    public function list(): array
    {
        $dates = new Dates();
        $times = new Times();
        $orders = new Orders();

        $options = [];

        foreach ($dates->list() as $date) {
            $label = $dates->getLabel($date);
            foreach ($times->list() as $timeSlot) {
                $orderCount = $orders->getOrdersByDeliveryDateAndTime($date, $timeSlot);
                if ($orderCount >= 3) {
                    continue;
                }
                $value = $date->toDateString() . ' ' . $timeSlot;
                $label .= ' ' . $timeSlot;
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
