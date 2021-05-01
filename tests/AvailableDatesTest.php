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

        $dates = (new Dates)->list();

        self::assertIsArray($dates);
        self::assertEquals($dates[0], Carbon::today());
    }

    public function testFirstDateIsTomorrowWhenAfter11am(): void
    {
        Travel::to('25-03-2021 11:00:00');

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

    protected function tearDown(): void
    {
        Travel::back();
    }
}
