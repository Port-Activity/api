<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use PHPUnit\Framework\Assert;
use SMA\PAA\TOOL\DateTools;

class FeatureContext implements SnippetAcceptingContext
{

    public function __construct()
    {
        $this->api = new Api();
        $this->sessionData = [];
        $this->apiKeyId = 0;
        $this->apiKey = "";
    }

    /**
     * @Given there is a :arg1 email with with password :arg2
     */
    public function thereIsAEmailWithWithPassword($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * @When I login
     */
    public function iLogin()
    {
        $this->sessionData = $this->api->login($this->email, $this->password);
    }

    /**
     * @Then I should get json with session_id
     */
    public function iShouldGetJsonWithSessionId()
    {
        Assert::assertTrue(strlen($this->sessionData['session_id']) > 10);
    }

    /**
     * @Then I should get user data of logged user
     */
    public function iShouldGetUserDataOfLoggedUser()
    {
        $data = $this->sessionData['user'];
        $permissions = $data['permissions'];
        unset($data['permissions']);
        Assert::assertEquals(
            21,
            sizeof($permissions)
        );
        Assert::assertEquals(
            [
            'email' => 'demo@sma'
            ,'first_name' => 'Demo'
            ,'last_name' => 'SMA'
            ,'role' => 'admin'
            ,'id' => 1
            ],
            $data
        );
    }

    /**
     * @Given I am logged in
     */
    public function iAmLoggedIn()
    {
        $this->sessionData = $this->api->login("demo@sma", "secretpassword");
        $this->sessionId = $this->sessionData["session_id"];
        Assert::assertTrue(strlen($this->sessionId) > 10);
    }

    /**
     * @When I add new API KEY
     */
    public function iAddNewApiKey()
    {
        $result = $this->api->post($this->sessionId, "api-keys", ["name" => "New API KEY"]);
        $this->apiKeyId = $result["id"];
        $this->apiKey = $result["key"];
        Assert::assertTrue(strlen($this->apiKey) > 10);
    }

    /**
     * @When Attach timestamp there
     */
    public function attachTimestampThere()
    {
        /*
        $result = $this->api->put($this->sessionId, "api-key/timestamp", [
            "api_key" => $this->apiKey,
            "state" => "Departure_Vessel_Berth",
            "time_type" => "Estimated"
        ]);
        */
    }

    /**
     * @Then I should be able to push timestamp of that type into api
     */
    public function iShouldBeAbleToPushTimestampOfThatTypeIntoApi()
    {
        require __DIR__ . "/../../src/lib/autoload.php";
        $imo = 1234567;
        $dateTools = new DateTools();
        $ts = $dateTools->now();
        $result = $this->api->postWithApiKey($this->apiKey, "agent/rest/timestamps", [
            "imo" => $imo,
            "vessel_name" => "Unikieship",
            "time_type" => "Estimated",
            "state" => "Departure_Vessel_Berth",
            "time" => $ts,
            "payload" => []
        ]);
        Assert::assertEquals(["result" => "OK"], $result);
    }

    /**
     * @When set API KEY as inactive
     */
    public function setApiKeyAsInactive()
    {
        $result = $this->api->put($this->sessionId, "api-keys/disable", [
            "id" => $this->apiKeyId,
            "is_active" => false
        ]);
        Assert::assertTrue($result);
    }

    /**
     * @Then Timstamp add should be rejected
     */
    public function timstampAddShouldBeRejected()
    {
        require __DIR__ . "/../../src/lib/autoload.php";
        $imo = 1234567;
        $dateTools = new DateTools();
        $ts = $dateTools->now();
        $result = $this->api->postWithApiKey($this->apiKey, "timestamp", [
            "imo" => $imo,
            "vessel_name" => "Unikieship",
            "time_type" => "Estimated",
            "state" => "Departure_Vessel_Berth",
            "time" => $ts,
            "payload" => []
        ]);
        Assert::assertEquals(["error" => "Invalid access"], $result);
    }

    /**
     * @Then I should be able to push timestamp of that type into api vis agent rest timestamp api
     */
    public function iShouldBeAbleToPushTimestampOfThatTypeIntoApiVisAgentRestTimestampApi()
    {
        require __DIR__ . "/../../src/lib/autoload.php";
        $imo = 1234567;
        $dateTools = new DateTools();
        $ts = $dateTools->now();
        $result = $this->api->postWithApiKey($this->apiKey, "agent/rest/timestamps", [
            "imo" => $imo,
            "vessel_name" => "Unikieship",
            "time_type" => "Estimated",
            "state" => "Departure_Vessel_Berth",
            "time" => $ts,
            "payload" => []
        ]);
        Assert::assertEquals(["result" => "OK"], $result);
    }

     /**
     * @Then I should be able to push timestamp to rest logistics timestamp api
     */
    public function iShouldBeAbleToPushTimestampToRestLogisticsApi()
    {
        $externalId = 1234567;
        $checkpoint = "Check 1";
        $direction = "Out";
        $frontLicensePlates[] = ["number" => "ABC123", "nationality" => "FIN"];
        $frontLicensePlates[] = ["number" => "ABC234", "nationality" => "FIN"];
        $rearLicensePlates[] = ["number" => "BCD234", "nationality" => "SWE"];
        $rearLicensePlates[] = ["number" => "BCD456", "nationality" => "SWE"];
        $containers[] = ["identification" => "1234567890"];
        $containers[] = ["identification" => "2345678901"];
        $containers[] = ["identification" => "3456789012"];

        require __DIR__ . "/../../src/lib/autoload.php";

        $dateTools = new DateTools();
        $ts = $dateTools->now();
        $result = $this->api->postWithApiKey($this->apiKey, "agent/rest/logistics-timestamps", [
            "time" => $ts,
            "external_id" => $externalId,
            "checkpoint" => $checkpoint,
            "direction" => $direction,
            "front_license_plates" => $frontLicensePlates,
            "rear_license_plates" => $rearLicensePlates,
            "containers" => $containers
        ]);
        Assert::assertEquals(["result" => "OK"], $result);
    }

     /**
     * @Then I should be able to get timestamp from rest logistics timestamp api
     */
    public function iShouldBeAbleToGetTimestampFromRestLogisticsApi()
    {
        $externalId = 1234567;
        $checkpoint = "Check 1";
        $direction = "Out";
        $frontLicensePlates[] = ["number" => "ABC123", "nationality" => "FIN"];
        $frontLicensePlates[] = ["number" => "ABC234", "nationality" => "FIN"];
        $rearLicensePlates[] = ["number" => "BCD234", "nationality" => "SWE"];
        $rearLicensePlates[] = ["number" => "BCD456", "nationality" => "SWE"];
        $containers[] = ["identification" => "1234567890"];
        $containers[] = ["identification" => "2345678901"];
        $containers[] = ["identification" => "3456789012"];

        require __DIR__ . "/../../src/lib/autoload.php";

        $result = $this->api->get($this->sessionId, "logistics-timestamps/2");
        #print_r($result);
        Assert::assertEquals($externalId, end($result)["external_id"]);
        Assert::assertEquals($checkpoint, end($result)["checkpoint"]);
        Assert::assertEquals($direction, end($result)["direction"]);
        Assert::assertEquals($frontLicensePlates, end($result)["front_license_plates"]);
        Assert::assertEquals($rearLicensePlates, end($result)["rear_license_plates"]);
        Assert::assertEquals($containers, end($result)["containers"]);
    }

     /**
     * @Then I should be able to search timestamp from rest logistics timestamp api
     */
    public function iShouldBeAbleToSearchTimestampFromRestLogisticsApi()
    {
        $externalId = 1234567;
        $checkpoint = "Check 1";
        $direction = "Out";
        $frontLicensePlates[] = ["number" => "ABC123", "nationality" => "FIN"];
        $frontLicensePlates[] = ["number" => "ABC234", "nationality" => "FIN"];
        $rearLicensePlates[] = ["number" => "BCD234", "nationality" => "SWE"];
        $rearLicensePlates[] = ["number" => "BCD456", "nationality" => "SWE"];
        $containers[] = ["identification" => "1234567890"];
        $containers[] = ["identification" => "2345678901"];
        $containers[] = ["identification" => "3456789012"];

        require __DIR__ . "/../../src/lib/autoload.php";

        $result = $this->api->get($this->sessionId, "logistics-timestamps/by-license-plate/ABC123");

        Assert::assertEquals("ABC123", $result["license_plate"]);
        Assert::assertEquals($externalId, end($result["timestamps"])["external_id"]);
        Assert::assertEquals($checkpoint, end($result["timestamps"])["checkpoint"]);
        Assert::assertEquals($direction, end($result["timestamps"])["direction"]);
        Assert::assertEquals($frontLicensePlates, end($result["timestamps"])["front_license_plates"]);
        Assert::assertEquals($rearLicensePlates, end($result["timestamps"])["rear_license_plates"]);
        Assert::assertEquals($containers, end($result["timestamps"])["containers"]);
    }

    /**
     * @When I login and send bearer token :token
     */
    public function iLoginAndSendBearerToken($token)
    {
        $this->sessionData = $this->api->loginAndSendBearerToken("demo@sma", "secretpassword", $token);
    }

    /**
     * @Then I should login with new sessionId
     */
    public function iShouldLoginWithNewSessionid()
    {
        Assert::assertEquals($this->sessionData, ["error" => "Session expired"]);
    }

    /**
     * @Then I sessionId is not :token
     */
    public function iSessionidIsNot($token)
    {
        Assert::assertNotEquals($this->sessionData["session_id"], $token);
    }
}
