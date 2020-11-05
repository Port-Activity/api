Feature: Login
  In order to login
  As a port actor
  I need email and password

  Scenario: Login to Port activity app
    Given there is a "demo@sma" email with with password "secretpassword"
    When I login
    Then I should get json with session_id
    And I should get user data of logged user

  Scenario: Login to Port activity app always generates new sessionId
    Given there is a "demo@sma" email with with password "secretpassword"
    When I login and send bearer token "null"
    Then I should login with new sessionId
    And I sessionId is not "null"
