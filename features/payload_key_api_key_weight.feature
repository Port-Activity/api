Feature: Payload key API key weight
  Scenario: Payload key API key weight determines which timestamp payload to use
    When user "demo@sma" logs in with password "secretpassword"
    #
    # Create API keys
    #
    Then logged user can create API key "API key 1"
    And logged user can create API key "API key 2"
    #
    # Set API key priorities
    #
    Then logged user can set timestamp "Estimated" "Arrival_Vessel_PortArea" API key priorities to "API key 2,API key 1"
    And logged user can set timestamp "Estimated" "Departure_Vessel_Berth" API key priorities to "API key 2,API key 1"
    #
    # Send timestamps without payload key API key priorities
    #
    Then send timestamp "Estimated" "Arrival_Vessel_PortArea" with offset "PT1H" with payload '{"source": "master_source", "external_id": "1", "berth_name": "berth1"}' using API key "API key 2"
    And logged user can see that port call "1" has payload "berth_name" value of "berth1"
    Then send timestamp "Estimated" "Departure_Vessel_Berth" with offset "PT2H" with payload '{"source": "master_source", "external_id": "1", "berth_name": "berth2"}' using API key "API key 1"
    And logged user can see that port call "1" has payload "berth_name" value of "berth2"
    #
    # Set payload key API key priorities
    #
    Then logged user can set payload key "berth_name" API key priorities to "API key 2,API key 1"
    And logged user can see that payload key "berth_name" has API key priorities "API key 2,API key 1"
    #
    # Send timestamp with lower payload priority. Note that all timestamps are scanned and weights are now in use.
    #
    Then send timestamp "Estimated" "Departure_Vessel_Berth" with offset "PT3H" with payload '{"source": "master_source", "external_id": "1", "berth_name": "berth3"}' using API key "API key 1"
    And logged user can see that port call "1" has payload "berth_name" value of "berth1"
    #
    # Send timestamp with higher payload priority
    #
    Then send timestamp "Estimated" "Arrival_Vessel_PortArea" with offset "PT2H" with payload '{"source": "master_source", "external_id": "1", "berth_name": "berth4"}' using API key "API key 2"
    And logged user can see that port call "1" has payload "berth_name" value of "berth4"
    #
    # Send timestamp with higher payload priority and then with lower payload priority
    #
    Then send timestamp "Estimated" "Arrival_Vessel_PortArea" with offset "PT3H" with payload '{"source": "master_source", "external_id": "1", "berth_name": "berth5"}' using API key "API key 2"
    And logged user can see that port call "1" has payload "berth_name" value of "berth5"
    Then send timestamp "Estimated" "Departure_Vessel_Berth" with offset "PT5H" with payload '{"source": "master_source", "external_id": "1", "berth_name": "berth6"}' using API key "API key 1"
    And logged user can see that port call "1" has payload "berth_name" value of "berth5"
    #
    # Cleanup
    #
    Then user "demo@sma" logs in with password "secretpassword"
    And logged user can delete all timestamps from IMO "1234567"
