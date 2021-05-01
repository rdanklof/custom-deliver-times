<?php

namespace CustomDeliverTimes;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class Dates
{
    public array $excluded;

    public function __construct(array $excluded = [])
    {
        $this->excluded = $this->getExcluded($excluded);
    }

    public function list(): array
    {
        $currentTime = Carbon::now()->hour;

        $start = $currentTime < 11
            ? Carbon::today()
            : Carbon::tomorrow();

        $return = [];

        foreach (CarbonPeriod::create($start, $start->copy()->addDays(14)) as $date) {
            if (in_array($date->copy()->startOfDay()->timestamp, $this->excluded, true)) {
                continue;
            }

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

    private function getExcluded(array $values): array
    {
        $return = [];

        foreach ($values as $excluded) {
            $return[] = Carbon::parse($excluded)->startOfDay()->timestamp;
        }

        return $return;
    }
}
