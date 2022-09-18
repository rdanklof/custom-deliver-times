<?php

namespace CustomDeliverTimes;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Umulmrum\Holiday\Constant\HolidayType;
use Umulmrum\Holiday\Filter\IncludeTimespanFilter;
use Umulmrum\Holiday\Filter\IncludeTypeFilter;
use Umulmrum\Holiday\HolidayCalculator;
use Umulmrum\Holiday\Model\HolidayList;
use Umulmrum\Holiday\Provider\Netherlands\Netherlands;

class Dates
{
    public array $excluded;
    public HolidayList $holidays;

    public function __construct(array $excluded = [])
    {
        $this->excluded = $this->getExcluded($excluded);
    }

    public function list(bool $onlyFridays = true): array
    {
        date_default_timezone_set('Europe/Amsterdam');
        $current = Carbon::now();
        $currentHour = $current->hour;

        if ($onlyFridays) {
            if($current->isThursday() && $currentHour < 19){
                $start = Carbon::today();
            } elseif($current->isThursday() && $currentHour >= 19){
                $start = Carbon::today()->addWeek()->next('friday');
            } else {
                $start = Carbon::today();
            }
        } else {
            $start = $currentHour < 11
                ? Carbon::today()
                : Carbon::tomorrow();
        }

        $range = CarbonPeriod::create($start, $start->copy()->addMonth());

        $this->getHolidays($range);

        $return = [];

        foreach ($range as $date) {

            if ($onlyFridays && !$date->isFriday()) {
                continue;
            }

            if ($onlyFridays && $date->isToday()) {
                continue;
            }

            if (in_array($date->copy()->startOfDay()->timestamp, $this->excluded, true)) {
                continue;
            }

            if ($date->isWeekend()) {
                continue;
            }

            if ($this->isHoliday($date)) {
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

    private function getHolidays(CarbonPeriod $period): void
    {
        $firstDay = $period->first();
        $lastDay = $period->last();

        if ($firstDay === null || $lastDay === null) {
            return;
        }

        $holidayCalculator = new HolidayCalculator();

        $holidays = $holidayCalculator->calculate(Netherlands::class, [2021, 2022, 2023]);

        $firstDay = new \DateTime($firstDay->toDateString());
        $lastDay = new \DateTime($lastDay->toDateString());

        $holidays = $holidays->filter(new IncludeTimespanFilter($firstDay, $lastDay));
        $holidays->filter(new IncludeTypeFilter(HolidayType::DAY_OFF));

        $this->holidays = $holidays;
    }

    private function isHoliday(CarbonInterface $day): bool
    {
        return $this->holidays->isHoliday(new \DateTime($day->toDateString()));
    }
}
