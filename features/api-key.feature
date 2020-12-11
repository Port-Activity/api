Feature: Api-key
  There is routes on API that requires API KEY.

  Scenario: Creating new API KEY
    Given I am logged in
    When I add new API KEY
    And Attach ETD to new API key
    Then I should be able to push timestamp of that type into api
    And I should be able to push timestamp of that type into api vis agent rest timestamp api


  Scenario: Using in-active API KEY it is not permitted to post timestamp
    Given I am logged in
    When I add new API KEY
    And set API KEY as inactive
    And Attach ETD to new API key
    Then Timestamp add should be rejected

  Scenario: Posting logistics timestamp
    Given I am logged in
    When I add new API KEY
    Then I should be able to push timestamp to rest logistics timestamp api
    And I should be able to get timestamp from rest logistics timestamp api
    And I should be able to search timestamp from rest logistics timestamp api