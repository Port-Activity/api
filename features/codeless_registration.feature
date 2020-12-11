Feature: Codeless registration
  Scenario: Codeless registration
    When user "demo@sma" logs in with password "secretpassword"
    Then logged user can create user "secondadmin1@test.com" with role "second_admin"
    And logged user can create user "secondadmin2@test.com" with role "second_admin"
    And logged user can change user "secondadmin1@test.com" password to "secretpassword1"
    And logged user can change user "secondadmin2@test.com" password to "secretpassword2"

    Then user "secondadmin1@test.com" logs in with password "secretpassword1"
    And logged user can create registration code "RegCode1" for role "first_user"

    Then user "secondadmin2@test.com" logs in with password "secretpassword2"
    And logged user can create registration code "RegCode2" for role "first_user"

    Then anyone can see that public setting "codeless_registration_module" is "disabled"
    And anyone cannot see status of non public setting "activity_module"
    And user "usertest1_1@test.com" cannot register without code giving password "1234"
    And user "usertest1_1@test.com" can register with code "RegCode1" giving password "123456789012"

    Then user "secondadmin2@test.com" logs in with password "secretpassword2"
    And logged user changes setting "codeless_registration_module" to "enabled"
    And anyone can see that public setting "codeless_registration_module" is "enabled"
    And user "usertest2_1@test.com" cannot register without code giving short password "123"
    And user "usertest2_1@test.com" can register without code giving password "2345"
    And user "usertest1_2@test.com" can register with code "RegCode1" giving password "234567890123"

    Then user "secondadmin1@test.com" logs in with password "secretpassword1"
    And logged user changes setting "codeless_registration_module" to "disabled"
    And anyone can see that public setting "codeless_registration_module" is "disabled"
    And logged user changes setting "codeless_registration_module" to "enabled"
    And anyone can see that public setting "codeless_registration_module" is "enabled"
    And user "usertest1_3@test.com" can register without code giving password "1234"
    And user "usertest2_2@test.com" can register with code "RegCode2" giving password "345678901234"

    Then user "secondadmin1@test.com" logs in with password "secretpassword1"
    And logged user changes setting "codeless_registration_module" to "disabled"
    And anyone can see that public setting "codeless_registration_module" is "disabled"
    And logged user can see only "1" own registration codes
    And logged user can see only "3" own users

    Then user "secondadmin2@test.com" logs in with password "secretpassword2"
    And logged user can see only "1" own registration codes
    And logged user can see only "2" own users

    Then user "demo@sma" logs in with password "secretpassword"
    And logged user can see all "2" registration codes
    And logged user can see all "8" users

    # Database check
    Then user "demo@sma" logs in with password "secretpassword"
    And logged user can see that user "usertest1_1@test.com" has '{"role_id": "3", "created_by_email": "secondadmin1@test.com", "registration_type":"code"}'
    And logged user can see that user "usertest1_2@test.com" has '{"role_id": "3", "created_by_email": "secondadmin1@test.com", "registration_type":"code"}'
    And logged user can see that user "usertest1_3@test.com" has '{"role_id": "4", "registration_code_id": null, "created_by_email": "secondadmin1@test.com", "registration_type":"codeless"}'
    And logged user can see that user "usertest2_1@test.com" has '{"role_id": "4", "registration_code_id": null, "created_by_email": "secondadmin2@test.com", "registration_type":"codeless"}'
    And logged user can see that user "usertest2_2@test.com" has '{"role_id": "3", "created_by_email": "secondadmin2@test.com", "registration_type":"code"}'

    # Cleanup
    Then user "demo@sma" logs in with password "secretpassword"
    And logged user can delete user "secondadmin1@test.com"
    And logged user can delete user "secondadmin2@test.com"
    And logged user can delete user "usertest1_1@test.com"
    And logged user can delete user "usertest1_2@test.com"
    And logged user can delete user "usertest1_3@test.com"
    And logged user can delete user "usertest2_1@test.com"
    And logged user can delete user "usertest2_2@test.com"
    And logged user can delete registration code "RegCode1"
    And logged user can delete registration code "RegCode2"
