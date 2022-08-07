<?php

namespace CustomDeliverTimes;

use PHPUnit\Framework\TestCase;
use RachidLaasri\Travel\Travel;

/**
 * @coversDefaultClass \CustomDeliverTimes\DeliveryTimes
 */
class DeliveryTimesTest extends TestCase
{
    public function setUp(): void
    {
        date_default_timezone_set('Europe/Amsterdam');
    }

    /**
     * @covers ::openForBusiness
     * @dataProvider openForBusinessDataProvider
     */
    public function testOpenForBusiness(string $date, array $excluded, bool $expectation): void
    {
        Travel::to($date);
        self::assertEquals($expectation, (new DeliveryTimes($excluded))->openForBusiness());
    }

    public function openForBusinessDataProvider(): iterable
    {
        yield 'Current date is not excluded, and current time is before the cutoff' => [
            '2022-03-25 10:00:00',
            [],
            true,
        ];

        yield 'Current date is not excluded, and current time is after the cutoff' => [
            '2022-03-25 11:00:00',
            [],
            false,
        ];

        yield 'Current date is excluded, and current time is before the cutoff' => [
            '2022-03-25 10:00:00',
            ['2022-03-25'],
            false,
        ];

        yield 'Current date is excluded, and current time is after the cutoff' => [
            '2022-03-25 11:00:00',
            ['2022-03-25'],
            false,
        ];
    }
}
