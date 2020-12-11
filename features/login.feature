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

  Scenario: Login to Port activity app case insensitive user name
    Given there is a "dEmO@sMa" email with with password "secretpassword"
    When I login
    Then I should get json with session_id
    And I should get user data of logged user

  Scenario: Create user and test user name case insensitivity
    Given there is a "demo@sma" email with with password "secretpassword"
    And there is new user with email "behat@test.com"
    When I login
    Then I can create a new user
    And new user can request password reset using email "behat@test.com"
    Given there is password reset token for email "behat@test.com"
    Then that user can reset password to "secretpassword1"
    Given there is password reset token for email "bEhAt@TeSt.CoM"
    Then that user can reset password to "secretpassword2"
    Then I can change the new user password to "passwordsecret1" using email "behat@test.com"
    Then I can change the new user password to "passwordsecret2" using email "bEhAt@TeSt.CoM"
    Then I can delete new user