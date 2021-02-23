<?php

use CustomDeliverTimes\Dates;
use CustomDeliverTimes\Orders;
use CustomDeliverTimes\Times;
use PHPUnit\Framework\TestCase;

class AvailableTimeSlotsTest extends TestCase
{
    public function testTimeSlotIsHiddenWhen(): void
    {
        $stub = $this->createMock(Orders::class);

        $stub->method('getOrdersByDeliveryDateAndTime')->willReturn(3);

        $dates = new Dates();
        $times = new Times();

        $options = [];

        foreach ($dates->list() as $date) {
            $label = $dates->getLabel($date);
            foreach ($times->list() as $timeSlot) {
                $orderCount = $stub->getOrdersByDeliveryDateAndTime($date, $timeSlot);
                if ($orderCount >= 3) {
                    continue;
                }
                $value = $date->toDateString() . ' ' . $timeSlot;
                $label .= ' ' . $timeSlot;
                $options[$value] = $label;
            }
        }

        $this->assertEmpty($options);
    }
}
