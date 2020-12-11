Feature: Port call decisions
  Scenario: Port call decisions can be created and decision item states changed
    When user "demo@sma" logs in with password "secretpassword"
    Then logged user can create API key "API key decision test 1"
    And logged user can set timestamp "Estimated" "Arrival_Vessel_PortArea" API key priorities to "API key decision test 1"
    And logged user can set timestamp "Estimated" "Departure_Vessel_Berth" API key priorities to "API key decision test 1"
    And logged user can create user "first_user1@decisiontest.com" with role "first_user"
    And logged user can change user "first_user1@decisiontest.com" password to "111122223333"
    And logged user can create user "first_user2@decisiontest.com" with role "first_user"
    And logged user can change user "first_user2@decisiontest.com" password to "222233334444"

    Then send timestamp "Estimated" "Arrival_Vessel_PortArea" with offset "PT1H" with payload '{"source": "master_source", "external_id": "decision1", "berth_name": "berth"}' using API key "API key decision test 1"
    And send timestamp "Estimated" "Departure_Vessel_Berth" with offset "PT2H" with payload '{"source": "master_source", "external_id": "decision1", "berth_name": "berth"}' using API key "API key decision test 1"

    Then user "first_user1@decisiontest.com" logs in with password "111122223333"
    And logged user can create new "port_call_decision" notification "Ship is arriving" for IMO "1234567" and port call master ID "decision1" with decisions "Pilot,Tugs,Linesmen"

    Then user "first_user2@decisiontest.com" logs in with password "222233334444"
    And logged user can observe notification "Ship is arriving"
    And logged user can observe decision item "Pilot" in notification "Ship is arriving"
    And logged user can observe decision item "Tugs" in notification "Ship is arriving"
    And logged user can observe decision item "Linesmen" in notification "Ship is arriving"
    And logged user can set decision item "Pilot" response to "Accept"
    And logged user can set decision item "Tugs" response to "Reject"
    And logged user can set decision item "Linesmen" response to "Accept"

    Then user "first_user1@decisiontest.com" logs in with password "111122223333"
    And logged user can see that decision item "Pilot" in notification "Ship is arriving" has response "Accept" and type "positive"
    And logged user can see that decision item "Tugs" in notification "Ship is arriving" has response "Reject" and type "negative"
    And logged user can see that decision item "Linesmen" in notification "Ship is arriving" has response "Accept" and type "positive"
    And logged user can set decision item "Tugs" response to "Accept"
    And logged user can set decision item "Linesmen" response to ""
    And logged user cannot set decision item "Tugs" response to "Exciting" because "Invalid decision item response name: Exciting"

    Then user "first_user2@decisiontest.com" logs in with password "222233334444"
    And logged user can see that decision item "Pilot" in notification "Ship is arriving" has response "Accept" and type "positive"
    And logged user can see that decision item "Tugs" in notification "Ship is arriving" has response "Accept" and type "positive"
    And logged user can see that decision item "Linesmen" in notification "Ship is arriving" has response no response or type
    And logged user can reply "Child1" to notification "Ship is arriving" with "ship" notification using IMO "1234567"

    Then user "first_user1@decisiontest.com" logs in with password "111122223333"
    And logged user can reply "Child2" to notification "Ship is arriving" with "ship" notification using IMO "1234567"
    And logged user can see that notification "Ship is arriving" has reply "Child1"
    And logged user can see that notification "Ship is arriving" has reply "Child2"

    Then user "demo@sma" logs in with password "secretpassword"
    And logged user closes port call having master ID "decision1"
    And logged user can see that notification "Ship is arriving" has decision of type "port_call_decision" with status "closed"

    Then user "first_user1@decisiontest.com" logs in with password "111122223333"
    And logged user cannot set decision item "Tugs" response to "Accept" because "Decision is closed: 1"
    And logged user cannot create new "port_call_decision" notification "Closed ship is arriving" for IMO "1234567" and port call master ID "decision1" with decisions "Pilot,Tugs,Linesmen" because "Given port call is closed: decision1"

    Then send timestamp "Estimated" "Arrival_Vessel_PortArea" with offset "P3D" with payload '{"source": "master_source", "external_id": "decision2", "berth_name": "berth"}' using API key "API key decision test 1"
    And send timestamp "Estimated" "Departure_Vessel_Berth" with offset "P4D" with payload '{"source": "master_source", "external_id": "decision2", "berth_name": "berth"}' using API key "API key decision test 1"

    Then user "first_user1@decisiontest.com" logs in with password "111122223333"
    And logged user cannot create new "port_call_decision" notification "Missing imo" for IMO "" and port call master ID "decision2" with decisions "Pilot,Tugs,Linesmen" because "Invalid assigned values for type: port_call_decision. Valid assigned values are message, ship_imo, port_call_master_id and decisions."
    And logged user cannot create new "port_call_decision" notification "Missing port call master ID" for IMO "1234567" and port call master ID "" with decisions "Pilot,Tugs,Linesmen" because "Invalid assigned values for type: port_call_decision. Valid assigned values are message, ship_imo, port_call_master_id and decisions."
    And logged user cannot create new "port_call_decision" notification "Missing decisions" for IMO "1234567" and port call master ID "decision2" with decisions "" because "Invalid assigned values for type: port_call_decision. Valid assigned values are message, ship_imo, port_call_master_id and decisions."
    And logged user cannot create new "port_call_decision" notification "Empty decision" for IMO "1234567" and port call master ID "decision2" with decisions "1, ,2" because "Decision cannot be empty."
    And logged user cannot create new "port_call_decision" notification "Too many decisions" for IMO "1234567" and port call master ID "decision2" with decisions "1,2,3,4,5,6" because "Too many decisions. Maximum is: 5"
    And logged user cannot create new "port_call_decision" notification "Invalid port call master ID" for IMO "1234567" and port call master ID "decision3" with decisions "Pilot,Tugs,Linesmen" because "Invalid port call master ID: decision3"
    And logged user cannot create new "port_call_decision" notification "Wrong IMO" for IMO "2345678" and port call master ID "decision2" with decisions "Pilot,Tugs,Linesmen" because "IMO: 2345678 does not have port call master ID: decision2"
    And logged user can create new "port_call_decision" notification "Ship 2 is arriving" for IMO "1234567" and port call master ID "decision2" with decisions "Pilot2,Tugs2"
    And logged user can observe notification "Ship 2 is arriving"
    And logged user can observe decision item "Tugs2" in notification "Ship 2 is arriving"
    And logged user can set decision item "Tugs2" response to "Accept"
    And logged user closes decision from "Ship 2 is arriving" notification
    And logged user can see that notification "Ship 2 is arriving" has decision of type "port_call_decision" with status "closed"
    And logged user cannot set decision item "Tugs2" response to "Accept" because "Decision is closed: 2"

    # Cleanup
    Then user "demo@sma" logs in with password "secretpassword"
    And logged user can delete user "first_user1@decisiontest.com"
    And logged user can delete user "first_user2@decisiontest.com"
    And logged user can delete all timestamps from IMO "1234567"
    And logged user can delete all notifications
