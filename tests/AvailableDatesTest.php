<?php

use Carbon\Carbon;
use CustomDeliverTimes\Dates;
use PHPUnit\Framework\TestCase;
use RachidLaasri\Travel\Travel;

class AvailableDatesTest extends TestCase
{
    public function testFirstDateIsTodayWhenBefore11am(): void
    {
        Travel::to('2021-03-25 10:59:00');

        $dates = (new Dates)->list();

        self::assertIsArray($dates);
        self::assertEquals($dates[0], Carbon::today());
    }

    public function testFirstDateIsTomorrowWhenAfter11am(): void
    {
        Travel::to('2021-03-25 11:00:00');

        $dates = (new Dates)->list();

        self::assertIsArray($dates);
        self::assertEquals($dates[0], Carbon::tomorrow());
    }

    public function testDateIsNotListedWhenWeekend(): void
    {
        Travel::to('next saturday');

        $dates = (new Dates)->list();

        self::assertIsArray($dates);
        self::assertEquals($dates[0], Carbon::now()->next('monday'));
    }

    public function testOfficialHolidayIsNotAvailable(): void
    {
        Travel::to('2021-05-12 12:00:00');

        $dates = (new Dates)->list();

        self::assertEquals($dates[0], Carbon::parse('2021-05-14'));
    }

    public function testNonOfficialHolidayIsAvailable(): void
    {
        Travel::to('2021-05-04 12:00:00');

        $dates = (new Dates)->list();

        self::assertEquals($dates[0], Carbon::parse('2021-05-05'));
    }

    protected function tearDown(): void
    {
        Travel::back();
    }
}
