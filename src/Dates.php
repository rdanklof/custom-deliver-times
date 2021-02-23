<?php

namespace CustomDeliverTimes;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class Dates
{
    public function list(): array
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
            $return[] = $date;
        }

        return $return;
    }

    public function getLabel(Carbon $date): string
    {
        if ($date->isToday()) {
            return 'Vandaag';
        }

        if ($date->isTomorrow()) {
            return 'Morgen';
        }

        return ucfirst($date->dayName) . ' ' . $date->day . ' ' . $date->monthName;
    }
}
