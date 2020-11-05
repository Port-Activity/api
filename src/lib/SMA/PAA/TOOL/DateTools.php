<?php
namespace SMA\PAA\TOOL;

use Exception;
use DateTime;
use DateTimeZone;
use DateInterval;

class DateTools
{
    public function now()
    {
        return $this->isoDate("");
    }
    /**
     * @throws \Exception when invalid date
     */
    public function isoDate(string $dateTime, string $timeZone = null): string
    {
        $time = new DateTime($dateTime);
        $tz = ($timeZone === null) ? "UTC" : $timeZone;
        $time->setTimeZone(new DateTimeZone($tz));
        return $time->format("Y-m-d\TH:i:sP");
    }
    /**
     * @throws \Exception when invalid date
     */
    public function localDate(string $dateTime, string $timeZone = null): string
    {
        $time = new DateTime($dateTime);
        $tz = ($timeZone === null) ? "UTC" : $timeZone;
        $time->setTimeZone(new DateTimeZone($tz));
        return $time->format("Y-m-d H:i");
    }
    public function isValidIsoDateTime(string $date)
    {
        $dateTime = DateTime::createFromFormat(DateTime::ATOM, $date);
        return $dateTime instanceof DateTime && $dateTime->format(DateTime::ATOM) === $date;
    }
    public function differenceSeconds(string $fromDate, string $toDate)
    {
        return strtotime($toDate) - strtotime($fromDate);
    }
    public function formatUtc(string $dateTime, string $format): string
    {
        $time = new DateTime($dateTime);
        $time->setTimeZone(new DateTimeZone("UTC"));
        return $time->format($format);
    }
    public function addIsoDuration(string $dateTime, string $duration)
    {
        $time = new DateTime($dateTime);
        return $time->add(new DateInterval($duration))->format("Y-m-d\TH:i:sP");
    }
    public function subIsoDuration(string $dateTime, string $duration)
    {
        $time = new DateTime($dateTime);
        return $time->sub(new DateInterval($duration))->format("Y-m-d\TH:i:sP");
    }
    public function isValidIsoDuration(string $duration): bool
    {
        try {
            new DateInterval($duration);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function dateIntervalToHHMM(DateInterval $dateInterval): string
    {
        $hours = 0;
        $minutes = 0;

        // Years and months are only approximation
        $hours += $dateInterval->y * 8766;
        $hours += $dateInterval->m * 731;
        $hours += $dateInterval->d * 24;
        $hours += $dateInterval->h;
        $minutes = $dateInterval->i;

        return str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT);
    }
    public function isValidTimeZone(string $timeZone)
    {
        try {
            new DateTimeZone($timeZone);
            return true;
        } catch (\Exception $e) {
        }
        return false;
    }
    public function addDateInterval(DateInterval $first, DateInterval $second): DateInterval
    {
        $before = new DateTime();
        $after = clone $before;

        $before->add($first);
        $before->add($second);

        return $after->diff($before);
    }
    public function subDateInterval(DateInterval $first, DateInterval $second): DateInterval
    {
        $before = new DateTime();
        $after = clone $before;

        $before->add($first);
        $before->sub($second);

        return $after->diff($before);
    }
    public function compareDateInterval(DateInterval $first, DateInterval $second): int
    {
        $firstDate = new DateTime();
        $secondDate = clone $firstDate;

        $firstDate->add($first);
        $secondDate->add($second);

        if ($firstDate == $secondDate) {
            return 0;
        } elseif ($firstDate > $secondDate) {
            return 1;
        } elseif ($firstDate < $secondDate) {
            return -1;
        }
    }
}
