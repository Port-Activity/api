Feature: Password rules can be set per role
  Scenario: Admins and users can have different password rules
    When user "demo@sma" logs in with password "secretpassword"

    Then logged user can create user "user1@passwordtest.com" with role "user"
    And logged user can change user "user1@passwordtest.com" password to "1111"
    And logged user cannot change user "user1@passwordtest.com" password to short password "111"

    And logged user can create registration code "PasswordTestRegAdmin" for role "admin"
    And logged user can create registration code "PasswordTestRegUser" for role "user"

    Then user "admin1@passwordtest.com" cannot register with code "PasswordTestRegAdmin" giving short password "1234"
    And user "admin1@passwordtest.com" cannot register with code "PasswordTestRegAdmin" giving weak password "111111222222"
    And user "admin1@passwordtest.com" can register with code "PasswordTestRegAdmin" giving password "123456789012"

    Then user "user2@passwordtest.com" cannot register with code "PasswordTestRegUser" giving short password "222"
    And user "user2@passwordtest.com" can register with code "PasswordTestRegUser" giving password "2222"

    Then user "admin1@passwordtest.com" logs in with password "123456789012"
    And logged user cannot change user "admin1@passwordtest.com" password to short password "1234"
    And logged user cannot change user "admin1@passwordtest.com" password to weak password "111111222222"
    And logged user can change user "admin1@passwordtest.com" password to "111122223333"

    Given there is password reset token for email "admin1@passwordtest.com"
    Then that user cannot reset password to short password "1234"
    And that user cannot reset password to weak password "111111222222"
    And that user can reset password to "234567890123"

    Given there is password reset token for email "user1@passwordtest.com"
    Then that user cannot reset password to short password "111"
    And that user can reset password to "1111"

    # Cleanup
    Then user "demo@sma" logs in with password "secretpassword"
    And logged user can delete user "user1@passwordtest.com"
    And logged user can delete user "admin1@passwordtest.com"
    And logged user can delete user "user2@passwordtest.com"
    And logged user can delete registration code "PasswordTestRegAdmin"
    And logged user can delete registration code "PasswordTestRegUser"