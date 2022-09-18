<?php

namespace CustomDeliverTimes;

use Carbon\Carbon;

class DeliveryTimes
{
    public array $excluded;
    public int $maxOrdersPerSlot = 3;

    public function __construct(array $excluded = [], int $maxOrdersPerSlot = 3)
    {
        $this->maxOrdersPerSlot = $maxOrdersPerSlot;
        $this->excluded = $excluded;
    }

    public function list(): array
    {
        date_default_timezone_set('Europe/Amsterdam');
        Carbon::setLocale('nl-NL');

        $dates = new Dates($this->excluded);
        $times = new Times();
        $orders = new Orders();

        $options = [];

        foreach ($dates->list() as $date) {
            foreach ($times->list() as $timeSlot) {
                $label = $dates->getLabel($date);
                $orderCount = $orders->getOrdersByDeliveryDateAndTime($date, $timeSlot);
                if ($orderCount >= $this->maxOrdersPerSlot) {
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

    public function openForBusiness(): bool
    {
        date_default_timezone_set('Europe/Amsterdam');
        Carbon::setLocale('nl-NL');

        $currentHour = Carbon::now()->hour;

        if (in_array(Carbon::today()->toDateString(), $this->excluded, true)) {
            return false;
        }

        if ($currentHour < 11) {
            $dates = (new Dates($this->excluded))->list();
            return Carbon::parse($dates[0])->toDateTimeString() !== Carbon::today()->toDateString();
        }

        return false;
    }
}
