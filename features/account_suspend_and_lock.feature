Feature: Account suspend and lock
  Scenario: User account is suspended and locked according to password rules
    When user "demo@sma" logs in with password "secretpassword"

    Then logged user can create user "user1@locktest.com" with role "user"
    And logged user can change user "user1@locktest.com" password to "1111"

    Then logged user can lock user "user1@locktest.com"
    And user "user1@locktest.com" cannot log in with password "1111" because account is locked
    And logged user can unlock user "user1@locktest.com"
    And user "user1@locktest.com" logs in with password "1111"

    Then user "demo@sma" logs in with password "secretpassword"
    And logged user can suspend user "user1@locktest.com"
    And user "user1@locktest.com" cannot log in with password "1111" because account is suspended
    And logged user can unsuspend user "user1@locktest.com"
    And user "user1@locktest.com" logs in with password "1111"

    Then user "user1@locktest.com" cannot log in with password "2222" because of invalid password
    And user "user1@locktest.com" cannot log in with password "2222" because of invalid password
    And user "user1@locktest.com" logs in with password "1111"

    Then user "user1@locktest.com" cannot log in with password "2222" because of invalid password
    And user "user1@locktest.com" cannot log in with password "2222" because of invalid password
    And user "user1@locktest.com" cannot log in with password "2222" because account is suspended
    And user "user1@locktest.com" cannot log in with password "1111" because account is suspended

    Given there are sweet dreams for "65" seconds
    Then user "user1@locktest.com" cannot log in with password "2222" because of invalid password
    And user "user1@locktest.com" cannot log in with password "2222" because of invalid password
    And user "user1@locktest.com" cannot log in with password "2222" because account is suspended
    And user "user1@locktest.com" cannot log in with password "1111" because account is suspended

    Then user "demo@sma" logs in with password "secretpassword"
    And logged user can unsuspend user "user1@locktest.com"
    And user "user1@locktest.com" cannot log in with password "2222" because account is locked
    And user "user1@locktest.com" cannot log in with password "1111" because account is locked

    Given there is password reset token for email "user1@locktest.com"
    Then that user can reset password to "3333"
    Then user "user1@locktest.com" logs in with password "3333"

    # Cleanup
    Then user "demo@sma" logs in with password "secretpassword"
    And logged user can delete user "user1@locktest.com"
