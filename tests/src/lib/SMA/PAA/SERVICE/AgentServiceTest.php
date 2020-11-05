<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;

final class AgentServiceTest extends TestCase
{
    public function testVerifyAndConvertTimestampCorrectZFormat(): void
    {
        $agent = new AgentService();
        $time = $agent->verifyAndConvertTime("2019-01-30T07:00:00Z");
        $this->assertEquals($time, "2019-01-30T07:00:00+00:00");
    }

    public function testVerifyAndConvertTimestampCorrect0000Format(): void
    {
        $agent = new AgentService();
        $time = $agent->verifyAndConvertTime("2019-01-30T07:00:00-0045");
        $this->assertEquals($time, "2019-01-30T07:45:00+00:00");
    }

    public function testVerifyAndConvertTimestampCorrect00Colon00Format(): void
    {
        $agent = new AgentService();
        $time = $agent->verifyAndConvertTime("2019-01-30T07:00:00+10:30");
        $this->assertEquals($time, "2019-01-29T20:30:00+00:00");
    }
    public function testVerifyAndConvertTimestampCorrectMillisecondFormat(): void
    {
        $agent = new AgentService();
        $time = $agent->verifyAndConvertTime("2019-01-30T07:00:00.012+10:30");
        $this->assertEquals($time, "2019-01-29T20:30:00+00:00");
    }
    public function testVerifyAndConvertTimestampCorrectMillisecondFormatWithZTimezone(): void
    {
        $agent = new AgentService();
        $time = $agent->verifyAndConvertTime("2019-01-30T07:00:00.912Z");
        $this->assertEquals($time, "2019-01-30T07:00:00+00:00");
    }
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid date format
     */
    public function testVerifyAndConvertTimestampInvalidFormat(): void
    {
        $agent = new AgentService();
        $time = $agent->verifyAndConvertTime("20190130T07:00:00Z");
        $this->assertEquals($time, "2019-01-30T07:00:00+00:00");
    }

    public function testVerifyLogisticsDataAllGood(): void
    {
        $frontLicensePlates[] = ["number" => "ABC123", "nationality" => "FIN"];
        $frontLicensePlates[] = ["number" => "ABC234", "nationality" => "FIN"];
        $rearLicensePlates[] = ["number" => "BCD234", "nationality" => "SWE"];
        $rearLicensePlates[] = ["number" => "BCD456", "nationality" => "SWE"];
        $containers[] = ["identification" => "1234567890"];
        $containers[] = ["identification" => "2345678901"];
        $containers[] = ["identification" => "3456789012"];

        $agent = new AgentService();
        $this->assertTrue($agent->verifyLogisticsData("In", $frontLicensePlates, $rearLicensePlates, $containers));
        $this->assertTrue($agent->verifyLogisticsData("Out", $frontLicensePlates, $rearLicensePlates, $containers));
    }
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid direction value: NotInOrOut
     */
    public function testVerifyLogisticsDataInvalidDirection(): void
    {
        $frontLicensePlates[] = ["number" => "ABC123", "nationality" => "FIN"];
        $frontLicensePlates[] = ["number" => "ABC234", "nationality" => "FIN"];
        $rearLicensePlates[] = ["number" => "BCD234", "nationality" => "SWE"];
        $rearLicensePlates[] = ["number" => "BCD456", "nationality" => "SWE"];
        $containers[] = ["identification" => "1234567890"];
        $containers[] = ["identification" => "2345678901"];
        $containers[] = ["identification" => "3456789012"];

        $agent = new AgentService();
        $agent->verifyLogisticsData("NotInOrOut", $frontLicensePlates, $rearLicensePlates, $containers);
    }
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid parameter(s) in front_license_plates: dummy1, dummy2
     */
    public function testVerifyLogisticsDataExtraFrontLicensePlateParameter(): void
    {
        $frontLicensePlates[] = ["dummy1" => "data", "dummy2" => "data", "number" => "ABC123", "nationality" => "FIN"];
        $frontLicensePlates[] = ["number" => "ABC234", "nationality" => "FIN"];
        $rearLicensePlates[] = ["number" => "BCD234", "nationality" => "SWE"];
        $rearLicensePlates[] = ["number" => "BCD456", "nationality" => "SWE"];
        $containers[] = ["identification" => "1234567890"];
        $containers[] = ["identification" => "2345678901"];
        $containers[] = ["identification" => "3456789012"];

        $agent = new AgentService();
        $agent->verifyLogisticsData("In", $frontLicensePlates, $rearLicensePlates, $containers);
    }
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid parameter(s) in rear_license_plates: dummy1, dummy2
     */
    public function testVerifyLogisticsDataExtraRearLicensePlateParameter(): void
    {
        $frontLicensePlates[] = ["number" => "ABC123", "nationality" => "FIN"];
        $frontLicensePlates[] = ["number" => "ABC234", "nationality" => "FIN"];
        $rearLicensePlates[] = ["number" => "BCD234", "nationality" => "SWE"];
        $rearLicensePlates[] = ["dummy1" => "data", "dummy2" => "data", "number" => "BCD456", "nationality" => "SWE"];
        $containers[] = ["identification" => "1234567890"];
        $containers[] = ["identification" => "2345678901"];
        $containers[] = ["identification" => "3456789012"];

        $agent = new AgentService();
        $agent->verifyLogisticsData("In", $frontLicensePlates, $rearLicensePlates, $containers);
    }
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid parameter(s) in containers: dummy1, dummy2
     */
    public function testVerifyLogisticsDataExtraContainerParameter(): void
    {
        $frontLicensePlates[] = ["number" => "ABC123", "nationality" => "FIN"];
        $frontLicensePlates[] = ["number" => "ABC234", "nationality" => "FIN"];
        $rearLicensePlates[] = ["number" => "BCD234", "nationality" => "SWE"];
        $rearLicensePlates[] = ["number" => "BCD456", "nationality" => "SWE"];
        $containers[] = ["identification" => "1234567890"];
        $containers[] = ["identification" => "2345678901", "dummy1" => "data", "dummy2" => "data"];
        $containers[] = ["identification" => "3456789012"];

        $agent = new AgentService();
        $agent->verifyLogisticsData("In", $frontLicensePlates, $rearLicensePlates, $containers);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing parameter(s) from front_license_plates: nationality
     */
    public function testVerifyLogisticsDataMissingFrontLicensePlateParameter(): void
    {
        $frontLicensePlates[] = ["number" => "ABC123", "nationality" => "FIN"];
        $frontLicensePlates[] = ["number" => "ABC234"];
        $rearLicensePlates[] = ["number" => "BCD234", "nationality" => "SWE"];
        $rearLicensePlates[] = ["number" => "BCD456", "nationality" => "SWE"];
        $containers[] = ["identification" => "1234567890"];
        $containers[] = ["identification" => "2345678901"];
        $containers[] = ["identification" => "3456789012"];

        $agent = new AgentService();
        $agent->verifyLogisticsData("In", $frontLicensePlates, $rearLicensePlates, $containers);
    }
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing parameter(s) from rear_license_plates: number, nationality
     */
    public function testVerifyLogisticsDataMissingRearLicensePlateParameter(): void
    {
        $frontLicensePlates[] = ["number" => "ABC123", "nationality" => "FIN"];
        $frontLicensePlates[] = ["number" => "ABC234", "nationality" => "FIN"];
        $rearLicensePlates[] = ["number" => "BCD234", "nationality" => "SWE"];
        $rearLicensePlates[] = [];
        $containers[] = ["identification" => "1234567890"];
        $containers[] = ["identification" => "2345678901"];
        $containers[] = ["identification" => "3456789012"];

        $agent = new AgentService();
        $agent->verifyLogisticsData("In", $frontLicensePlates, $rearLicensePlates, $containers);
    }
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing parameter(s) from containers: identification
     */
    public function testVerifyLogisticsDataMissingContainerParameter(): void
    {
        $frontLicensePlates[] = ["number" => "ABC123", "nationality" => "FIN"];
        $frontLicensePlates[] = ["number" => "ABC234", "nationality" => "FIN"];
        $rearLicensePlates[] = ["number" => "BCD234", "nationality" => "SWE"];
        $rearLicensePlates[] = ["number" => "BCD456", "nationality" => "SWE"];
        $containers[] = ["identification" => "1234567890"];
        $containers[] = [];
        $containers[] = ["identification" => "3456789012"];

        $agent = new AgentService();
        $agent->verifyLogisticsData("In", $frontLicensePlates, $rearLicensePlates, $containers);
    }
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Time type is actual, but time is in future.
     */
    public function testSettingActualIntoFutureMoreThanTenMinutesFails()
    {
        $service = new AgentService();
        $service->verifyActualIsNotInFuture("Actual", "2020-05-11T12:40:41", "2020-05-11T12:30:40");
    }
    public function testSettingActualIntoFutureLessThanTenMinutesIsOk()
    {
        $service = new AgentService();
        $this->assertTrue(
            $service->verifyActualIsNotInFuture("Actual", "2020-05-11T12:40:40", "2020-05-11T12:30:40")
        );
        $this->assertTrue(
            $service->verifyActualIsNotInFuture("Actual", "2020-05-11T12:40:39", "2020-05-11T12:30:40")
        );
    }
    public function testSettingActualIntoPastIsOk()
    {
        $service = new AgentService();
        $this->assertTrue(
            $service->verifyActualIsNotInFuture("Actual", "2020-05-11T12:30:45", "2020-05-11T12:30:50")
        );
    }
    public function testSettingAnyOtherTypeIntoFutureIsOk()
    {
        $service = new AgentService();
        $this->assertTrue(
            $service->verifyActualIsNotInFuture("Estimate", "2020-05-11T12:30:45", "2020-05-11T12:30:40")
        );
    }
}
