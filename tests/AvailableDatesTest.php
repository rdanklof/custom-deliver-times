<?php

use Carbon\Carbon;
use CustomDeliverTimes\Dates;
use PHPUnit\Framework\TestCase;
use RachidLaasri\Travel\Travel;

class AvailableDatesTest extends TestCase
{
    public function testFirstDateIsTodayWhenBefore11am(): void
    {
        Travel::to('25-03-2021 10:59:00');

        $dates = Dates::list();

        $this->assertIsArray($dates);
        $this->assertEquals($dates[0], Carbon::today()->toDateString());
    }

    public function testFirstDateIsTomorrowWhenAfter11am(): void
    {
        Travel::to('25-03-2021 11:00:00');

        $dates = Dates::list();

        $this->assertIsArray($dates);
        $this->assertEquals($dates[0], Carbon::tomorrow()->toDateString());
    }

    public function testDateIsNotListedWhenWeekend(): void
    {
        Travel::to('next saturday');

        $dates = Dates::list();

        $this->assertIsArray($dates);
        $this->assertEquals($dates[0], Carbon::now()->next('monday')->toDateString());
    }

    protected function tearDown(): void
    {
        Travel::back();
    }
}
