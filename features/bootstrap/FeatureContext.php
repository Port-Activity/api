<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use PHPUnit\Framework\Assert;
use SMA\PAA\TOOL\DateTools;
use SMA\PAA\SERVICE\JwtService;

class FeatureContext implements SnippetAcceptingContext
{

    public function __construct()
    {
        $this->api = new Api();
        $this->sessionData = [];
        $this->apiKeyId = 0;
        $this->apiKey = "";

        $this->userIds = [];
        $this->registrationCodes = [];
        $this->registrationCodeIds = [];
        $this->apiKeys = [];
        $this->notificationIds = [];
        $this->decisionItemIds = [];
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
     * @Given there is new user with email :arg1
     */
    public function thereIsNewUserWithEmail($email)
    {
        $this->newUserEmail = $email;
    }

    /**
     * @When I login
     */
    public function iLogin()
    {
        $this->sessionData = $this->api->login($this->email, $this->password);
        $this->sessionId = $this->sessionData["session_id"];
        Assert::assertTrue(strlen($this->sessionId) > 10);
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
            36,
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
     * @When Attach ETD to new API key
     */
    public function iAttachEtaToNewApiKey()
    {
        $result = $this->api->post(
            $this->sessionId,
            "timestamp-api-key-weights",
            ["timestamp_time_type" => "Estimated",
            "timestamp_state" => "Departure_Vessel_Berth",
            "api_key_ids" => [$this->apiKeyId]]
        );
            Assert::assertEquals(["result" => "OK"], $result);
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
     * @Then Timestamp add should be rejected
     */
    public function timestampAddShouldBeRejected()
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

    /**
     * @Then I can create a new user
     */
    public function iCanCreateANewUser()
    {
        $result = $this->api->post(
            $this->sessionId,
            "users",
            ["email" => $this->newUserEmail,
            "first_name" => "First",
            "last_name" => "last",
            "role" => "user"]
        );


        $this->newUserId = isset($result["id"]) ? $result["id"] : 0;
        $this->newUserPassword = isset($result["password"]) ? $result["password"] : "";

        Assert::assertTrue($this->newUserId > 0);
        Assert::assertEquals(30, strlen($this->newUserPassword));
    }

        /**
     * @Then new user can request password reset using email :email
     */
    public function newUserCanRequestPasswordResetUsingEmail($email)
    {
        $result = $this->api->post(
            "",
            "request-password-reset",
            ["email" => $email, "port" => "dummy port"]
        );

        Assert::assertEquals($result, ["message" => "Password reset requested"]);
    }

    /**
     * @Then I can change the new user password to :password using email :email
     */
    public function iCanChangeTheNewUserPasswordToUsingEmail($password, $email)
    {
        $result = $this->api->post(
            $this->sessionId,
            "change-password",
            ["email" => $email, "password" => $password]
        );

        Assert::assertEquals(1, $result);
    }

    /**
     * @Then I can delete new user
     */
    public function iCanDeleteNewUser()
    {
        $result = $this->api->delete($this->sessionId, "users/" . $this->newUserId);

        Assert::assertEquals(1, $result);
    }

    // New style cases begin here

    /**
     * @Given there are sweet dreams for :arg1 seconds
     */
    public function thereAreSweetDreamsForSeconds($arg1)
    {
        sleep($arg1);
    }

    /**
     * @Then anyone can see that public setting :arg1 is :arg2
     */
    public function anyoneCanSeeThatPublicSettingIs($arg1, $arg2)
    {
        $result = $this->api->get(
            "",
            "public-settings?name=" . $arg1
        );

        $expected[$arg1] = $arg2;
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then anyone cannot see status of non public setting :arg1
     */
    public function anyoneCannotSeeStatusOfNonPublicSetting($arg1)
    {
        $result = $this->api->get(
            "",
            "public-settings?name=" . $arg1
        );

        $expected = ["error" => "Invalid setting name: " . $arg1];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @When user :arg1 logs in with password :arg2
     */
    public function userLogsInWithPassword($arg1, $arg2)
    {
        $this->sessionData = $this->api->login($arg1, $arg2);
        $this->sessionId = $this->sessionData["session_id"];
        Assert::assertTrue(strlen($this->sessionId) > 10);
    }

    /**
     * @Then logged user changes setting :arg1 to :arg2
     */
    public function loggedUserChangesSettingTo($arg1, $arg2)
    {
        $result = $this->api->put(
            $this->sessionId,
            "settings/" . $arg1 . "/" . $arg2,
            []
        );

        $expected[$arg1] = $arg2;
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then user :arg1 cannot log in with password :arg2 because of invalid password
     */
    public function userCannotLogInWithPasswordBecauseOfInvalidPassword($arg1, $arg2)
    {
        $result = $this->api->login($arg1, $arg2);
        $expected = ["error" => "Invalid access"];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then user :arg1 cannot log in with password :arg2 because account is suspended
     */
    public function userCannotLogInWithPasswordBecauseAccountIsSuspended($arg1, $arg2)
    {
        $result = $this->api->login($arg1, $arg2);
        $result = preg_replace('/ \d+ /', '$1 X ', $result);
        $expected = ["message" => "Too many failed login attempts. Account suspended for X minute(s)."];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then user :arg1 cannot log in with password :arg2 because account is locked
     */
    public function userCannotLogInWithPasswordBecauseAccountIsLocked($arg1, $arg2)
    {
        $result = $this->api->login($arg1, $arg2);
        $expected = ["message" => "Too many failed login attempts. Account locked."];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Given there is password reset token for email :arg1
     */
    public function thereIsPasswordResetTokenForEmail($arg1)
    {
        if (!getenv("SKIP_LOCAL_INIT") && file_exists(__DIR__ . "/../../src/lib/init_local.php")) {
            require(__DIR__ . "/../../src/lib/init_local.php");
        }
        require __DIR__ . "/../../src/lib/autoload.php";
        $privateKey = json_decode(getenv("PRIVATE_KEY_JSON"));
            $publicKey = json_decode(getenv("PUBLIC_KEY_JSON"));

            $formUrl = getenv("BASE_URL") . "/reset-password";
            $jwtService = new JwtService($privateKey, $publicKey);
            // Generate token
            $expiresIn = 24 * 60 * 60; // TODO: validity length, currently one day
            $token = $jwtService->encode(["email" => $arg1, "port" => "dummy"], $expiresIn);

            $this->passwordResetToken = $token;
    }

    /**
     * @Then that user can reset password to :arg1
     */
    public function thatUserCanResetPasswordTo($arg1)
    {
        $result = $this->api->post(
            "",
            "reset-password",
            ["token" => $this->passwordResetToken, "password" => $arg1]
        );

        Assert::assertEquals(13, count($result));
    }

    /**
     * @Then that user cannot reset password to short password :arg1
     */
    public function thatUserCannotResetPasswordToShortPassword($arg1)
    {
        $result = $this->api->post(
            "",
            "reset-password",
            ["token" => $this->passwordResetToken, "password" => $arg1]
        );

        $result = preg_replace('/ \d+ /', '$1 X ', $result);
        $expected = ["error" => "Password must be at least X characters in length"];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then that user cannot reset password to weak password :arg1
     */
    public function thatUserCannotResetPasswordToWeakPassword($arg1)
    {
        $result = $this->api->post(
            "",
            "reset-password",
            ["token" => $this->passwordResetToken, "password" => $arg1]
        );

        $expected = ["error" => "Weak password. Not enough unique characters in password."];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then logged user can create user :arg1 with role :arg2
     */
    public function loggedUserCanCreateUserWithRole($arg1, $arg2)
    {
        $result = $this->api->post(
            $this->sessionId,
            "users",
            ["email" => $arg1,
            "first_name" => "First".$arg1,
            "last_name" => "Last".$arg1,
            "role" => $arg2]
        );

        $this->newUserId = isset($result["id"]) ? $result["id"] : 0;
        $this->newUserPassword = isset($result["password"]) ? $result["password"] : "";

        if ($this->newUserId > 0) {
            $this->userIds[$arg1] = $this->newUserId;
        }

        Assert::assertTrue($this->newUserId > 0);
        Assert::assertEquals(30, strlen($this->newUserPassword));
    }

    /**
     * @Then logged user can delete user :arg1
     */
    public function loggedUserCanDeleteUser($arg1)
    {
        $result = $this->api->delete($this->sessionId, "users/" . $this->userIds[$arg1]);

        if ($result) {
            unset($this->userIds[$arg1]);
        }

        Assert::assertEquals(1, $result);
    }

    /**
     * @Then logged user can change user :arg1 password to :arg2
     */
    public function loggedUserCanChangeUserPasswordTo($arg1, $arg2)
    {
        $result = $this->api->post(
            $this->sessionId,
            "change-password",
            ["email" => $arg1, "password" => $arg2]
        );

        Assert::assertEquals(1, $result);
    }

    /**
     * @Then logged user cannot change user :arg1 password to short password :arg2
     */
    public function loggedUserCannotChangeUserPasswordToShortPassword($arg1, $arg2)
    {
        $result = $this->api->post(
            $this->sessionId,
            "change-password",
            ["email" => $arg1, "password" => $arg2]
        );

        $result = preg_replace('/ \d+ /', '$1 X ', $result);
        $expected = ["error" => "Password must be at least X characters in length"];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then logged user cannot change user :arg1 password to weak password :arg2
     */
    public function loggedUserCannotChangeUserPasswordToWeakPassword($arg1, $arg2)
    {
        $result = $this->api->post(
            $this->sessionId,
            "change-password",
            ["email" => $arg1, "password" => $arg2]
        );

        $expected = ["error" => "Weak password. Not enough unique characters in password."];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then logged user can lock user :arg1
     */
    public function loggedUserCanLockUser($arg1)
    {
        $result = $this->api->post(
            $this->sessionId,
            "user-lock",
            ["id" => $this->userIds[$arg1], "locked" => true]
        );

        $expected = $this->userIds[$arg1];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then logged user can unlock user :arg1
     */
    public function loggedUserCanUnlockUser($arg1)
    {
        $result = $this->api->post(
            $this->sessionId,
            "user-lock",
            ["id" => $this->userIds[$arg1], "locked" => false]
        );

        $expected = $this->userIds[$arg1];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then logged user can suspend user :arg1
     */
    public function loggedUserCanSuspendUser($arg1)
    {
        $result = $this->api->post(
            $this->sessionId,
            "user-suspend",
            ["id" => $this->userIds[$arg1], "suspended" => true]
        );

        $expected = $this->userIds[$arg1];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then logged user can unsuspend user :arg1
     */
    public function loggedUserCanUnsuspendUser($arg1)
    {
        $result = $this->api->post(
            $this->sessionId,
            "user-suspend",
            ["id" => $this->userIds[$arg1], "suspended" => false]
        );

        $expected = $this->userIds[$arg1];
        Assert::assertEquals($expected, $result);
    }


    /**
     * @Then logged user can create registration code :arg1 for role :arg2
     */
    public function loggedUserCanCreateRegistrationCodeForRole($arg1, $arg2)
    {
        $result = $this->api->post(
            $this->sessionId,
            "registration-codes",
            ["role" => $arg2, "description" => $arg1]
        );

        Assert::assertTrue($result > 0);
        $this->registrationCodeIds[$arg1] = $result;

        $code = $this->api->get(
            $this->sessionId,
            "registration-codes?search=" . $arg1. "&limit=100&offset=0&sort=id"
        );
        Assert::assertEquals(1, count($code["data"]));
        $this->registrationCodes[$arg1] = $code["data"][0]["code"];
    }

    /**
     * @Then logged user can delete registration code :arg1
     */
    public function loggedUserCanDeleteRegistrationCode($arg1)
    {
        $result = $this->api->delete($this->sessionId, "registration-codes/" . $this->registrationCodeIds[$arg1]);

        if ($result === []) {
            unset($this->registrationCodes[$arg1]);
            unset($this->registrationCodeIds[$arg1]);
        }

        Assert::assertEquals([], $result);
    }

    /**
     * @Then user :arg1 can register with code :arg2 giving password :arg3
     */
    public function userCanRegisterWithCodeGivingPassword($arg1, $arg2, $arg3)
    {
        $this->sessionData = $this->api->post(
            "",
            "register",
            ["first_name" => "First".$arg1,
            "last_name" => "Last".$arg1,
            "code" => $this->registrationCodes[$arg2],
            "email" => $arg1,
            "password" => $arg3]
        );

        $this->sessionId = $this->sessionData["session_id"];
        $this->userIds[$arg1] = $this->sessionData["user"]["id"];

        Assert::assertTrue(strlen($this->sessionId) > 10);
    }

    /**
     * @Then user :arg1 cannot register with code :arg2 giving short password :arg3
     */
    public function userCannotRegisterWithCodeGivingShortPassword($arg1, $arg2, $arg3)
    {
        $result = $this->api->post(
            "",
            "register",
            ["first_name" => "First".$arg1,
            "last_name" => "Last".$arg1,
            "code" => $this->registrationCodes[$arg2],
            "email" => $arg1,
            "password" => $arg3]
        );

        $result = preg_replace('/ \d+ /', '$1 X ', $result);
        $expected = ["error" => "Password must be at least X characters in length"];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then user :arg1 cannot register with code :arg2 giving weak password :arg3
     */
    public function userCannotRegisterWithCodeGivingWeakPassword($arg1, $arg2, $arg3)
    {
        $result = $this->api->post(
            "",
            "register",
            ["first_name" => "First".$arg1,
            "last_name" => "Last".$arg1,
            "code" => $this->registrationCodes[$arg2],
            "email" => $arg1,
            "password" => $arg3]
        );

        $expected = ["error" => "Weak password. Not enough unique characters in password."];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then user :arg1 can register without code giving password :arg2
     */
    public function userCanRegisterWithoutCodeGivingPassword($arg1, $arg2)
    {
        $this->sessionData = $this->api->post(
            "",
            "codeless-register",
            ["first_name" => "First".$arg1,
            "last_name" => "Last".$arg1,
            "email" => $arg1,
            "password" => $arg2]
        );

        $this->sessionId = $this->sessionData["session_id"];
        $this->userIds[$arg1] = $this->sessionData["user"]["id"];

        Assert::assertTrue(strlen($this->sessionId) > 10);
    }

    /**
     * @Then user :arg1 cannot register without code giving password :arg2
     */
    public function userCannotRegisterWithoutCodeGivingPassword($arg1, $arg2)
    {
        $result = $this->api->post(
            "",
            "codeless-register",
            ["first_name" => "First".$arg1,
            "last_name" => "Last".$arg1,
            "email" => $arg1,
            "password" => $arg2]
        );

        $expected = ["error" => "Codeless registration not permitted"];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then user :arg1 cannot register without code giving short password :arg2
     */
    public function userCannotRegisterWithoutCodeGivingShortPassword($arg1, $arg2)
    {
        $result = $this->api->post(
            "",
            "codeless-register",
            ["first_name" => "First".$arg1,
            "last_name" => "Last".$arg1,
            "email" => $arg1,
            "password" => $arg2]
        );

        $result = preg_replace('/ \d+ /', '$1 X ', $result);
        $expected = ["error" => "Password must be at least X characters in length"];
        Assert::assertEquals($expected, $result);
    }

    /**
     * @Then logged user can see only :arg1 own registration codes
     */
    public function loggedUserCanSeeOnlyOwnRegistrationCodes($arg1)
    {
        $userId = $this->sessionData["user"]["id"];
        $codes = $this->api->get(
            $this->sessionId,
            "registration-codes?search=&limit=100&offset=0&sort=id"
        );
        Assert::assertEquals($arg1, count($codes["data"]));

        foreach ($codes["data"] as $code) {
            Assert::assertEquals($userId, $code["created_by"]);
        }
    }

    /**
     * @Then logged user can see only :arg1 own users
     */
    public function loggedUserCanSeeOnlyOwnUsers($arg1)
    {
        $userId = $this->sessionData["user"]["id"];
        $users = $this->api->get(
            $this->sessionId,
            "users?search=&limit=100&offset=0&sort=id"
        );
        Assert::assertEquals($arg1, count($users["data"]));

        foreach ($users["data"] as $user) {
            Assert::assertEquals($userId, $user["created_by"]);
        }
    }

    /**
     * @Then logged user can see all :arg1 registration codes
     */
    public function loggedUserCanSeeAllRegistrationCodes($arg1)
    {
        $codes = $this->api->get(
            $this->sessionId,
            "registration-codes?search=&limit=100&offset=0&sort=id"
        );
        Assert::assertEquals($arg1, count($codes["data"]));
    }

    /**
     * @Then logged user can see all :arg1 users
     */
    public function loggedUserCanSeeAllUsers($arg1)
    {
        $users = $this->api->get(
            $this->sessionId,
            "users?search=&limit=100&offset=0&sort=id"
        );

        Assert::assertEquals($arg1, count($users["data"]));
    }

    /**
     * @Then logged user can see that user :arg1 has :arg2
     */
    public function loggedUserCanSeeThatUserHas($arg1, $arg2)
    {
        $result = $this->api->get(
            $this->sessionId,
            "users?search=" . $arg1 . "&limit=1&offset=0&sort=id"
        );

        $expecteds = json_decode($arg2, true);

        foreach ($expecteds as $k => $v) {
            Assert::assertEquals($v, $result["data"][0][$k]);
        }
    }


    /**
     * @Then logged user can create API key :arg1
     */
    public function loggedUserCanCreateApiKey($arg1)
    {
        $apiKey = $this->api->post($this->sessionId, "api-keys", ["name" => $arg1]);
        $this->apiKeys[$arg1] = $apiKey;

        Assert::assertTrue(strlen($apiKey["key"]) > 10);
    }

    /**
     * @Then logged user can set timestamp :arg1 :arg2 API key priorities to :arg3
     */
    public function loggedUserCanSetTimestampApiKeyPrioritiesTo($arg1, $arg2, $arg3)
    {
        $timeType = $arg1;
        $state = $arg2;

        $apiKeyIds = [];
        $apiKeys = explode(",", $arg3);

        foreach ($apiKeys as $apiKey) {
            $apiKeyIds[] = $this->apiKeys[$apiKey]["id"];
        }

        $result = $this->api->post(
            $this->sessionId,
            "timestamp-api-key-weights",
            ["timestamp_time_type" => $timeType, "timestamp_state" => $state, "api_key_ids" => $apiKeyIds]
        );

        Assert::assertEquals(["result" => "OK"], $result);
    }

    /**
     * @Then logged user can set payload key :arg1 API key priorities to :arg2
     */
    public function loggedUserCanSetPayloadKeyApiKeyPrioritiesTo($arg1, $arg2)
    {
        $apiKeyIds = [];
        $apiKeys = explode(",", $arg2);

        foreach ($apiKeys as $apiKey) {
            $apiKeyIds[] = $this->apiKeys[$apiKey]["id"];
        }

        $result = $this->api->post(
            $this->sessionId,
            "payload-key-api-key-weights",
            ["payload_key" => $arg1, "api_key_ids" => $apiKeyIds]
        );

        Assert::assertEquals(["result" => "OK"], $result);
    }

    /**
     * @Then logged user can see that payload key :arg1 has API key priorities :arg2
     */
    public function loggedUserCanSeeThatPayloadKeyHasApiKeyPriorities($arg1, $arg2)
    {
        $apiKeys = explode(",", $arg2);

        $result = $this->api->get(
            $this->sessionId,
            "payload-key-api-key-weights"
        );

        $expectedApiKeys = [];
        $weight = count($apiKeys);
        foreach ($apiKeys as $apiKey) {
            $apiKeyId = $this->apiKeys[$apiKey]["id"];
            $expectedApiKeys[] = ["api_key_id" => $apiKeyId, "api_key_name" => $apiKey, "weight" => $weight];
            $weight = $weight - 1;
        }

        $expected = [["key" => $arg1, "api_keys" => $expectedApiKeys]];

        Assert::assertEquals($expected, $result["payload_keys"]);
    }

    /**
     * @Then send timestamp :arg1 :arg2 with offset :arg3 with payload :arg4 using API key :arg5
     */
    public function sendTimestampWithOffsetWithPayloadUsingApiKey($arg1, $arg2, $arg3, $arg4, $arg5)
    {
        require __DIR__ . "/../../src/lib/autoload.php";
        $apiKey = $this->apiKeys[$arg5]["key"];
        $imo = 1234567;
        $vesselName = "Unikieship";
        $timeType = $arg1;
        $state = $arg2;
        $dateTools = new DateTools();
        $time = $dateTools->addIsoDuration($dateTools->now(), $arg3);
        $payload = json_decode($arg4, true);
        $result = $this->api->postWithApiKey($apiKey, "agent/rest/timestamps", [
            "imo" => $imo,
            "vessel_name" => $vesselName,
            "time_type" => $timeType,
            "state" => $state,
            "time" => $time,
            "payload" => $payload
        ]);

        Assert::assertEquals(["result" => "OK"], $result);
    }

    /**
     * @Then logged user can see that port call :arg1 has payload :arg2 value of :arg3
     */
    public function loggedUserCanSeeThatPortCallHasPayloadValueOf($arg1, $arg2, $arg3)
    {
        $result = $this->api->get(
            $this->sessionId,
            "port-call?id=" . $arg1
        );

        Assert::assertEquals($arg3, $result["ship"][$arg2]);
    }

    // phpcs:disable
    /**
     * @Then logged user can create new :arg1 notification :arg2 for IMO :arg3 and port call master ID :arg4 with decisions :arg5
     */
    public function loggedUserCanCreateNewNotificationForImoAndPortCallMasterIdWithDecisions($arg1, $arg2, $arg3, $arg4, $arg5)
    {
        $decisions = null;
        if(!empty($arg5)) {
            $decisions = explode(",", $arg5);
        }

        $result = $this->api->post(
            $this->sessionId,
            "notifications",
            ["type" => $arg1,
            "message" => $arg2,
            "ship_imo" => $arg3,
            "port_call_master_id" => $arg4,
            "decisions" => $decisions
            ]
        );
    // phpcs:enable

        Assert::assertNotEquals(["error" => "Invalid port call master ID: " . $arg4], $result);
        Assert::assertEquals($arg1, $result["type"]);
        Assert::assertEquals($arg2, $result["message"]);
        Assert::assertEquals($arg3, $result["ship_imo"]);
        Assert::assertEquals("port_call_decision", $result["decision"]["type"]);
        Assert::assertEquals($arg4, $result["decision"]["port_call_master_id"]);
        $ct = 0;
        foreach ($decisions as $decision) {
            Assert::assertEquals($decision, $result["decision"]["decision_items"][$ct]["label"]);
            $ct =$ct + 1;
        }
    }

    /**
     * @Then logged user can observe notification :arg1
     */
    public function loggedUserCanObserveNotification($arg1)
    {
        $notifications = $this->api->get(
            $this->sessionId,
            "notifications"
        );

        foreach ($notifications as $notification) {
            if ($notification["message"] === $arg1) {
                $this->notificationIds[$arg1] = $notification["id"];
            }
        }

        Assert::assertTrue(isset($this->notificationIds[$arg1]));
    }

    /**
     * @Then logged user can observe decision item :arg1 in notification :arg2
     */
    public function loggedUserCanObserveDecisionItemInNotification($arg1, $arg2)
    {
        $notifications = $this->api->get(
            $this->sessionId,
            "notifications"
        );

        foreach ($notifications as $notification) {
            if ($notification["id"] === $this->notificationIds[$arg2]) {
                foreach ($notification["decision"]["decision_items"] as $decisionItem) {
                    if ($decisionItem["label"] === $arg1) {
                        $this->decisionItemIds[$arg1] = $decisionItem["id"];
                    }
                }
            }
        }

        Assert::assertTrue(isset($this->decisionItemIds[$arg1]));
    }

    /**
     * @Then logged user can set decision item :arg1 response to :arg2
     */
    public function loggedUserCanSetDecisionItemResponseTo($arg1, $arg2)
    {
        $result = $this->api->post(
            $this->sessionId,
            "decision-item-response",
            ["id" => $this->decisionItemIds[$arg1],
            "response" => $arg2
            ]
        );

        Assert::assertEquals(["result" => "OK"], $result);
    }

    /**
     * @Then logged user can see that decision item :arg1 in notification :arg2 has response :arg3 and type :arg4
     */
    public function loggedUserCanSeeThatDecisionItemInNotificationHasResponseAndType($arg1, $arg2, $arg3, $arg4)
    {
        $notifications = $this->api->get(
            $this->sessionId,
            "notifications"
        );

        $foundDecisionItem = null;
        foreach ($notifications as $notification) {
            if ($notification["id"] === $this->notificationIds[$arg2]) {
                foreach ($notification["decision"]["decision_items"] as $decisionItem) {
                    if ($decisionItem["id"] === $this->decisionItemIds[$arg1]) {
                        $foundDecisionItem = $decisionItem;
                    }
                }
            }
        }

        Assert::assertEquals($foundDecisionItem["response_name"], $arg3);
        Assert::assertEquals($foundDecisionItem["response_type"], $arg4);
    }

    /**
     * @Then logged user can see that decision item :arg1 in notification :arg2 has response no response or type
     */
    public function loggedUserCanSeeThatDecisionItemInNotificationHasResponseNoResponseOrType($arg1, $arg2)
    {
        $notifications = $this->api->get(
            $this->sessionId,
            "notifications"
        );

        $foundDecisionItem = null;
        foreach ($notifications as $notification) {
            if ($notification["id"] === $this->notificationIds[$arg2]) {
                foreach ($notification["decision"]["decision_items"] as $decisionItem) {
                    if ($decisionItem["id"] === $this->decisionItemIds[$arg1]) {
                        $foundDecisionItem = $decisionItem;
                    }
                }
            }
        }

        Assert::assertEquals($foundDecisionItem["response_name"], null);
        Assert::assertEquals($foundDecisionItem["response_type"], null);
    }

    /**
     * @Then logged user can reply :arg1 to notification :arg2 with :arg3 notification using IMO :arg4
     */
    public function loggedUserCanReplyToNotificationWithNotificationUsingImo($arg1, $arg2, $arg3, $arg4)
    {
        $result = $this->api->post(
            $this->sessionId,
            "notifications",
            ["type" => $arg3,
            "message" => $arg1,
            "ship_imo" => $arg4,
            "parent_id" => $this->notificationIds[$arg2]
            ]
        );

        Assert::assertEquals($arg3, $result["type"]);
        Assert::assertEquals($arg1, $result["message"]);
        Assert::assertEquals($arg4, $result["ship_imo"]);
        Assert::assertEquals($this->notificationIds[$arg2], $result["parent_notification_id"]);
    }

    /**
     * @Then logged user can see that notification :arg1 has reply :arg2
     */
    public function loggedUserCanSeeThatNotificationHasReply($arg1, $arg2)
    {
        $notifications = $this->api->get(
            $this->sessionId,
            "notifications"
        );

        $found = false;
        foreach ($notifications as $notification) {
            if ($notification["id"] === $this->notificationIds[$arg1]) {
                foreach ($notification["children"] as $child) {
                    if ($child["message"] === $arg2) {
                        $found = true;
                    }
                }
            }
        }

        Assert::assertTrue($found);
    }

    /**
     * @Then logged user can delete all timestamps from IMO :arg1
     */
    public function loggedUserCanDeleteAllTimestampsFromImo($arg1)
    {
        $timestamps = $this->api->get(
            $this->sessionId,
            "timestamps?imo=" . $arg1
        );

        foreach ($timestamps["data"] as $timestamp) {
            $result = $this->api->delete(
                $this->sessionId,
                "timestamps",
                ["id" => $timestamp["id"]]
            );

            Assert::assertEquals(1, $result);
        }

        $timestamps = $this->api->get(
            $this->sessionId,
            "timestamps?imo=" . $arg1
        );

        Assert::assertTrue(empty($timestamps["data"]));
    }

    /**
     * @Then logged user closes port call having master ID :arg1
     */
    public function loggedUserClosesPortCallHavingMasterId($arg1)
    {
        $portCalls = $this->api->get(
            $this->sessionId,
            "ongoing-port-calls"
        );

        $portCallId = null;

        foreach ($portCalls["portcalls"] as $portCall) {
            $portCallRange = $this->api->get(
                $this->sessionId,
                "port-call-range?port_call_id=" . $portCall["ship"]["id"]
            );

            if ($portCallRange["master_id"] === $arg1) {
                $portCallId = $portCall["ship"]["id"];
            }
        }

        Assert::assertNotEquals(null, $portCallId);

        $result = $this->api->get(
            $this->sessionId,
            "force-close-port-call?port_call_id=" . $portCallId
        );

        Assert::assertEquals(["result" => "OK"], $result);
    }

    /**
     * @Then logged user can see that notification :arg1 has decision of type :arg2 with status :arg3
     */
    public function loggedUserCanSeeThatNotificationHasDecisionOfTypeWithStatus($arg1, $arg2, $arg3)
    {
        $notifications = $this->api->get(
            $this->sessionId,
            "notifications"
        );

        $foundNotification = null;
        foreach ($notifications as $notification) {
            if ($notification["id"] === $this->notificationIds[$arg1]) {
                $foundNotification = $notification;
            }
        }

        Assert::assertNotEquals(null, $foundNotification);
        Assert::assertNotEquals(null, $foundNotification["decision"]);
        Assert::assertEquals($arg2, $foundNotification["decision"]["type"]);
        Assert::assertEquals($arg3, $foundNotification["decision"]["status"]);
    }

    /**
     * @Then logged user cannot set decision item :arg1 response to :arg2 because :arg3
     */
    public function loggedUserCannotSetDecisionItemResponseTo($arg1, $arg2, $arg3)
    {
        $decisions = $this->api->get(
            $this->sessionId,
            "decisions?limit=100"
        );

        $foundDecisionId = null;
        foreach ($decisions["data"] as $decision) {
            foreach ($decision["decision_items"] as $decisionItem) {
                if ($decisionItem["id"] === $this->decisionItemIds[$arg1]) {
                    $foundDecisionId = $decision["id"];
                }
            }
        }

        $result = $this->api->post(
            $this->sessionId,
            "decision-item-response",
            ["id" => $this->decisionItemIds[$arg1],
            "response" => $arg2
            ]
        );

        Assert::assertNotEquals(null, $foundDecisionId);
        Assert::assertEquals(["error" => $arg3], $result);
    }

    // phpcs:disable
    /**
     * @Then logged user cannot create new :arg1 notification :arg2 for IMO :arg3 and port call master ID :arg4 with decisions :arg5 because :arg6
     */
    public function loggedUserCannotCreateNewNotificationForImoAndPortCallMasterIdWithDecisionsBecause($arg1, $arg2, $arg3, $arg4, $arg5, $arg6)
    {
        $decisions = null;
        if(!empty($arg5)) {
            $decisions = explode(",", $arg5);
        }

        $result = $this->api->post(
            $this->sessionId,
            "notifications",
            ["type" => $arg1,
            "message" => $arg2,
            "ship_imo" => $arg3,
            "port_call_master_id" => $arg4,
            "decisions" => $decisions
            ]
        );
    // phpcs:enable

        Assert::assertEquals(["error" => $arg6], $result);
    }

    /**
     * @Then logged user can delete all notifications
     */
    public function loggedUserCanDeleteAllNotifications()
    {
        $notifications = $this->api->get(
            $this->sessionId,
            "notifications"
        );

        foreach ($notifications as $notification) {
            $result = $this->api->delete(
                $this->sessionId,
                "notifications/" . $notification["id"]
            );

            Assert::assertEquals(["result" => "OK"], $result);
        }

        $notifications = $this->api->get(
            $this->sessionId,
            "notifications"
        );

        Assert::assertTrue(empty($notifications));
    }

    /**
     * @Then logged user closes decision from :arg1 notification
     */
    public function loggedUserClosesDecisionFromNotification($arg1)
    {
        $notifications = $this->api->get(
            $this->sessionId,
            "notifications"
        );

        $foundNotification = null;
        foreach ($notifications as $notification) {
            if ($notification["id"] === $this->notificationIds[$arg1]) {
                $foundNotification = $notification;
            }
        }

        Assert::assertNotEquals(null, $foundNotification);
        Assert::assertNotEquals(null, $foundNotification["decision"]);

        $result = $this->api->post(
            $this->sessionId,
            "close-decision",
            ["id" => $foundNotification["decision"]["id"]]
        );

        Assert::assertEquals(["result" => "OK"], $result);
    }
}
