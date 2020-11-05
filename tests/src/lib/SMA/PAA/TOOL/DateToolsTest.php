<?php
namespace SMA\PAA\TOOL;

use DateTime;
use DateInterval;
use PHPUnit\Framework\TestCase;

final class DateToolsTest extends TestCase
{
    public function testIsValidIsoDateTime(): void
    {
        $tools = new DateTools();
        $this->assertTrue($tools->isValidIsoDateTime("2019-12-12T12:13:14+00:00"));
    }
    public function testFailsWhenNoDatePart(): void
    {
        $tools = new DateTools();
        $this->assertFalse($tools->isValidIsoDateTime("2019-12-12"));
    }
    public function testFailsWhenNoTimezone(): void
    {
        $tools = new DateTools();
        $this->assertFalse($tools->isValidIsoDateTime("2019-12-12T12:13:14"));
    }
    public function testFailsWhenEmptyInput(): void
    {
        $tools = new DateTools();
        $this->assertFalse($tools->isValidIsoDateTime(""));
    }
    public function testFailsWhenTimeZoneIsGivenAsZ(): void
    {
        $tools = new DateTools();
        $this->assertFalse($tools->isValidIsoDateTime("2019-12-12T12:13:14Z"));
    }
    public function testDateDifferenceIsCalculatedCorrectly(): void
    {
        $tools = new DateTools();
        $this->assertEquals(-4, $tools->differenceSeconds("2019-12-12T12:13:14Z", "2019-12-12T12:13:10Z"));
    }
    public function testConvertingDateToLocalDateWithoutTimezoneFallsBackToUTC(): void
    {
        $tools = new DateTools();
        $this->assertEquals('2019-12-12 12:13', $tools->localDate("2019-12-12T12:13:14Z"));
    }
    public function testConvertingDateToLocalDateWhenTimeZoneIsGiven(): void
    {
        $tools = new DateTools();
        $this->assertEquals('2019-12-12 14:13', $tools->localDate("2019-12-12T12:13:14Z", "Europe/Helsinki"));
    }
    // phpcs:disable
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage DateTime::__construct(): Failed to parse time string (FOO2019-12-12T12:13:14Z) at position 0 (F): The timezone could not be found in the database
     */
    // phpcs:enable
    public function testConvertingInvalidDateToLocalDateWhenTimeZoneIsGiven(): void
    {
        $tools = new DateTools();
        $tools->localDate("FOO2019-12-12T12:13:14Z");
    }
    public function testCheckingTimeZoneFailsWhenNotATimeZone()
    {
        $tools = new DateTools();
        $this->assertFalse($tools->isValidTimeZone("Foo/Bar"));
    }
    public function testCheckingTimeZoneWhenValidTimeZone()
    {
        $tools = new DateTools();
        $this->assertTrue($tools->isValidTimeZone("Europe/Helsinki"));
    }
    public function testAddDateInterval()
    {
        $tools = new DateTools();
        $dateInterval1 = new DateInterval("P1DT12H");
        $dateInterval2 = new DateInterval("PT5H");
        $dateIntervalRes = $tools->addDateInterval($dateInterval1, $dateInterval2);
        $res = new DateTime();
        $check = clone $res;
        $res->add($dateIntervalRes);

        $dateIntervalCheck = new DateInterval("P1DT17H");
        $check->add($dateIntervalCheck);

        $this->assertTrue($res == $check);
    }
    public function testSubDateInterval()
    {
        $tools = new DateTools();
        $dateInterval1 = new DateInterval("P1DT12H");
        $dateInterval2 = new DateInterval("PT5H");
        $dateIntervalRes = $tools->subDateInterval($dateInterval1, $dateInterval2);
        $res = new DateTime();
        $check = clone $res;
        $res->add($dateIntervalRes);

        $dateIntervalCheck = new DateInterval("P1DT7H");
        $check->add($dateIntervalCheck);

        $this->assertTrue($res == $check);
    }
    public function testCompareDateInterval()
    {
        $tools = new DateTools();
        $dateInterval1 = new DateInterval("P1DT12H");
        $dateInterval2 = new DateInterval("PT5H");
        $larger = $tools->compareDateInterval($dateInterval1, $dateInterval2);
        $smaller = $tools->compareDateInterval($dateInterval2, $dateInterval1);
        $equals = $tools->compareDateInterval($dateInterval1, $dateInterval1);

        $this->assertEquals(1, $larger);
        $this->assertEquals(-1, $smaller);
        $this->assertEquals(0, $equals);
    }
}
