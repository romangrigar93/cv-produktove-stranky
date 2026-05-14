<?php

namespace ToretZasilkovna\Toret\Library;

use DateTimeImmutable;
use DateTimeZone;

class Date
{
    private DateTimeZone $timezone;

    /**
     * @throws \DateInvalidTimeZoneException
     */
    public function __construct(string $timezone = 'Europe/Bratislava')
    {
        $this->timezone = new DateTimeZone($timezone);
    }

    public function getShippingDay(string $format = 'Y-m-d'): string
    {
        $now = new DateTimeImmutable('now', $this->timezone);

        if ($now->format('H') < 14 && (int)$now->format('N') < 6) {
            return $now->format($format);
        }

        $shippingDate = $now;
        do {
            $shippingDate = $shippingDate->modify('+1 day');
        } while ((int)$shippingDate->format('N') >= 6);

        return $shippingDate->format($format);
    }

    public function isWeekend(string $date): bool
    {
        $dt = new DateTimeImmutable($date, $this->timezone);
        return (int)$dt->format('N') >= 6;
    }

    public function getNextWorkingDay(string $format = 'Y-m-d'): string
    {
        $date = new DateTimeImmutable('now', $this->timezone);

        do {
            $date = $date->modify('+1 day');
        } while ((int)$date->format('N') >= 6);

        return $date->format($format);
    }

    public function getDayOfWeek(string $date): int
    {
        return (int)(new DateTimeImmutable($date, $this->timezone))->format('N');
    }

    public function getDaysSince(string $date): int
    {
        $start = new DateTimeImmutable($date, $this->timezone);
        $end = new DateTimeImmutable('today', $this->timezone);

        $diff = $end->diff($start);

        return $diff->days;
    }

    public function getDateAdvanced($plusdays, string $format = 'Y-m-d'): string
    {
        if ($plusdays == 0) {
            return date($format);
        } else {
            $date = date($format, strtotime(date($format) . ' +' . $plusdays . ' day'));
        }

        if (get_option('tcp_default_date_working') == 'ok') {
            if ($this->isWeekend(date($format)))
                $date = $this->getNextWorkingDayFromDate($date);
        }

        return $date;

    }

    public function getNextWorkingDayFromDate($date, string $format = 'Y-m-d'): string
    {
        if (self::isWeekend($date . ' +1 day')) {
            $day = $this->dayOfWeek(date($format) . ' +1 day');
            if ($day == '6') {
                $date = date($format, strtotime($date . ' +3 day'));
            } else {
                $date = date($format, strtotime($date . ' +2 day'));
            }
        } else {
            $date = date($format, strtotime($date . ' +1 day'));
        }

        return $date;
    }

    public function dayOfWeek($date)
    {
        return date('N', strtotime($date));
    }
}