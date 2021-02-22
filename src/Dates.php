<?php

namespace CustomDeliverTimes;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class Dates
{
    public static function list(): array
    {
        $currentTime = Carbon::now()->hour;

        $start = $currentTime < 11
            ? Carbon::today()
            : Carbon::tomorrow();

        $return = [];

        foreach (CarbonPeriod::create($start, $start->copy()->addDays(10)) as $date) {
            if ($date->isWeekend()) {
                continue;
            }
            $return[] = $date->toDateString();
        }

        return $return;
    }
}
