Feature: Second admin can see only own users
  Scenario: Create second admins and check they can see only own users
    When user "demo@sma" logs in with password "secretpassword"
    Then logged user can create user "secondadmin1@test.com" with role "second_admin"
    And logged user can create user "secondadmin2@test.com" with role "second_admin"
    And logged user can change user "secondadmin1@test.com" password to "secretpassword1"
    And logged user can change user "secondadmin2@test.com" password to "secretpassword2"
    Then user "secondadmin1@test.com" logs in with password "secretpassword1"
    And logged user can create user "usertest1_1@test.com" with role "user"
    And logged user can create registration code "RegCode1_1" for role "user"
    And logged user can create registration code "RegCode1_2" for role "user"
    Then user "secondadmin2@test.com" logs in with password "secretpassword2"
    And logged user can create registration code "RegCode2" for role "user"
    And logged user can create user "usertest2_1@test.com" with role "user"
    Then user "usertest1_2@test.com" can register with code "RegCode1_1" giving password "123456789012"
    And user "usertest1_3@test.com" can register with code "RegCode1_1" giving password "123456789012"
    And user "usertest1_4@test.com" can register with code "RegCode1_2" giving password "123456789012"
    Then user "usertest2_2@test.com" can register with code "RegCode2" giving password "123456789012"
    And user "usertest2_3@test.com" can register with code "RegCode2" giving password "123456789012"
    Then user "secondadmin1@test.com" logs in with password "secretpassword1"
    And logged user can see only "2" own registration codes
    And logged user can see only "4" own users
    Then user "secondadmin2@test.com" logs in with password "secretpassword2"
    And logged user can see only "1" own registration codes
    And logged user can see only "3" own users
    Then user "demo@sma" logs in with password "secretpassword"
    And logged user can see all "3" registration codes
    And logged user can see all "10" users

    # Database check
    Then user "demo@sma" logs in with password "secretpassword"
    And logged user can see that user "usertest1_1@test.com" has '{"role_id": "4", "registration_code_id": null, "created_by_email": "secondadmin1@test.com", "registration_type":"manual"}'
    And logged user can see that user "usertest1_2@test.com" has '{"role_id": "4", "created_by_email": "secondadmin1@test.com", "registration_type":"code"}'
    And logged user can see that user "usertest1_3@test.com" has '{"role_id": "4", "created_by_email": "secondadmin1@test.com", "registration_type":"code"}'
    And logged user can see that user "usertest1_4@test.com" has '{"role_id": "4", "created_by_email": "secondadmin1@test.com", "registration_type":"code"}'
    And logged user can see that user "usertest2_1@test.com" has '{"role_id": "4", "registration_code_id": null, "created_by_email": "secondadmin2@test.com", "registration_type":"manual"}'
    And logged user can see that user "usertest2_2@test.com" has '{"role_id": "4", "created_by_email": "secondadmin2@test.com", "registration_type":"code"}'
    And logged user can see that user "usertest2_3@test.com" has '{"role_id": "4", "created_by_email": "secondadmin2@test.com", "registration_type":"code"}'

    # Cleanup
    Then user "demo@sma" logs in with password "secretpassword"
    And logged user can delete user "secondadmin1@test.com"
    And logged user can delete user "secondadmin2@test.com"
    And logged user can delete user "usertest1_1@test.com"
    And logged user can delete user "usertest1_2@test.com"
    And logged user can delete user "usertest1_3@test.com"
    And logged user can delete user "usertest1_4@test.com"
    And logged user can delete user "usertest2_1@test.com"
    And logged user can delete user "usertest2_2@test.com"
    And logged user can delete user "usertest2_3@test.com"
    And logged user can delete registration code "RegCode1_1"
    And logged user can delete registration code "RegCode1_2"
    And logged user can delete registration code "RegCode2"
