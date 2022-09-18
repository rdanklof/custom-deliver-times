<?php

use Carbon\Carbon;
use CustomDeliverTimes\Dates;
use PHPUnit\Framework\TestCase;
use RachidLaasri\Travel\Travel;

/**
 * @coversDefaultClass \CustomDeliverTimes\Dates
 * @covers ::__construct;
 */
class AvailableDatesTest extends TestCase
{
    public function setUp(): void
    {
        date_default_timezone_set('Europe/Amsterdam');
    }

    /**
     * @covers ::list
     */
    public function testFirstDateIsFridayWhenBefore19pm(): void
    {
        Travel::to('2022-09-22 18:59:59');

        $dates = (new Dates)->list(true);

        self::assertIsArray($dates);
        self::assertEquals($dates[0], Carbon::tomorrow());
    }

    /**
     * @covers ::list
     */
    public function testFirstDateIsFridayWhenAfter19pm(): void
    {
        Travel::to('2022-09-22 19:00:00');

        $dates = (new Dates)->list(true);

        self::assertIsArray($dates);
        self::assertEquals($dates[0], Carbon::parse('2022-09-30'));
    }

    /**
     * @covers ::list
     */
    public function testFirstDateIsNextFridayWhenCurrentDayIsFriday(): void
    {
        Travel::to('2022-09-23 19:00:00');

        $dates = (new Dates)->list(true);

        self::assertIsArray($dates);
        self::assertEquals($dates[0], Carbon::parse('2022-09-30'));
    }

    public function testFirstDateIsTodayWhenBefore11am(): void
    {
        Travel::to('2021-03-25 10:59:00');

        $dates = (new Dates)->list(false);

        self::assertIsArray($dates);
        self::assertEquals($dates[0], Carbon::today());
    }

    public function testFirstDateIsTomorrowWhenAfter11am(): void
    {
        Travel::to('2021-03-25 11:00:00');

        $dates = (new Dates)->list(false);

        self::assertIsArray($dates);
        self::assertEquals($dates[0], Carbon::tomorrow());
    }

    public function testDateIsNotListedWhenWeekend(): void
    {
        Travel::to('next saturday');

        $dates = (new Dates)->list(false);

        self::assertIsArray($dates);
        self::assertEquals($dates[0], Carbon::now()->next('monday'));
    }

    public function testOfficialHolidayIsNotAvailable(): void
    {
        Travel::to('2021-05-12 12:00:00');

        $dates = (new Dates)->list(false);

        self::assertEquals($dates[0], Carbon::parse('2021-05-14'));
    }

    public function testNonOfficialHolidayIsAvailable(): void
    {
        Travel::to('2021-05-04 12:00:00');

        $dates = (new Dates)->list(false);

        self::assertEquals($dates[0], Carbon::parse('2021-05-05'));
    }

    protected function tearDown(): void
    {
        Travel::back();
    }
}
