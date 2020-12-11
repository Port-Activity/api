# API

- [API](#api)
  - [Authentication with API](#authentication-with-api)
    - [Example of using session key](#example-of-using-session-key)
    - [Example of using api key](#example-of-using-api-key)
  - [POST:/login](#postlogin)
  - [POST:/logout](#postlogout)
  - [GET:/session](#getsession)
  - [POST:/register-push-token](#postregister-push-token)
  - [GET:/users](#getusers)
  - [POST:/users](#postusers)
  - [PUT:/users/:id](#putusersid)
  - [GET:/users/:id](#getusersid)
  - [DELETE:/users/:id](#deleteusersid)
  - [POST:/register](#postregister)
  - [POST:/request-password-reset](#postrequest-password-reset)
  - [POST:/reset-password](#postreset-password)
  - [POST:/change-password](#postchange-password)
  - [POST:/timestamps](#posttimestamps)
  - [GET:/timestamp-definitions](#gettimestamp-definitions)
  - [POST:/agent/rest/timestamps](#postagentresttimestamps)
  - [POST:/agent/rest/logistics-timestamps](#postagentrestlogistics-timestamps)
  - [POST:/agent/rest/vis-notifications](#postagentrestvis-notifications)
  - [POST:/agent/rest/vis-messages](#postagentrestvis-messages)
  - [POST:/agent/rest/vis-voyage-plans](#postagentrestvis-voyage-plans)
  - [POST:/agent/rest/vis-vessels](#postagentrestvis-vessels)
  - [POST:/agent/rest/live-eta](#postagentrestlive-eta)
  - [POST:/agent/rest/inbound-vessels](#postagentrestinbound-vessels)
  - [POST:/agent/rest/slot-reservations](#postagentrestslot-reservations)
  - [PUT:/agent/rest/slot-reservations](#putagentrestslot-reservations)
  - [GET:/agent/rest/slot-reservation-by-id](#getagentrestslot-reservation-by-id)
  - [DELETE:/agent/rest/slot-reservations](#deleteagentrestslot-reservations)
  - [GET:/port-calls](#getport-calls)
  - [GET:/port-call-timestamps](#getport-call-timestamps)
  - [GET:/non-port-call-timestamps](#getnon-port-call-timestamps)
  - [GET:/ongoing-port-calls](#getongoing-port-calls)
  - [GET:/port-call](#getport-call)
  - [GET:/port-call-csv](#getport-call-csv)
  - [GET:/port-call-template](#getport-call-template)
  - [GET:/logistics-timestamps/:limit](#getlogistics-timestampslimit)
  - [GET:/logistics-timestamps/by-license-plate/:license_plate](#getlogistics-timestampsby-license-platelicense_plate)
  - [POST:/rta-web-form](#postrta-web-form)
  - [GET:/api-keys](#getapi-keys)
  - [POST:/api-keys](#postapi-keys)
  - [PUT:/api-keys/disable](#putapi-keysdisable)
  - [PUT:/api-keys/enable](#putapi-keysenable)
  - [GET:/health-check](#gethealth-check)
  - [POST:/translation/:ns/:lng](#posttranslationnslng)
  - [GET:/translations/:ns/:lng](#gettranslationsnslng)
  - [PUT:/translations/:ns/:lng](#puttranslationsnslng)
  - [POST:/translations/:ns/:lng](#posttranslationsnslng)
  - [GET:/registration-codes/:limit](#getregistration-codeslimit)
  - [POST:/registration-codes](#postregistration-codes)
  - [PUT:/registration-codes/:id](#putregistration-codesid)
  - [DELETE:/registration-codes/:id](#deleteregistration-codesid)
  - [GET:/notifications/:limit](#getnotificationslimit)
  - [POST:/notifications](#postnotifications)
  - [DELETE:/notifications/:id](#deletenotificationsid)
  - [GET:/pinned-vessel-ids](#getpinned-vessel-ids)
  - [PUT:/pinned-vessel-ids](#putpinned-vessel-ids)
  - [GET:/settings](#getsettings)
  - [GET:/settings/by-name/:name](#getsettingsby-namename)
  - [PUT:/settings/:name/:value](#putsettingsnamevalue)
  - [GET:/roles](#getroles)
  - [GET:/permissions](#getpermissions)
  - [GET:/role-permissions](#getrole-permissions)
  - [GET:/role-permissions/by-role/:role](#getrole-permissionsby-rolerole)
  - [PUT:/role-permissions/:role/:permission](#putrole-permissionsrolepermission)
  - [DELETE:/role-permissions/:role/:permission](#deleterole-permissionsrolepermission)
  - [POST:/push-notification-tokens](#postpush-notification-tokens)
  - [GET:/export/timestamps](#getexporttimestamps)
  - [GET:/timestamps](#gettimestamps)
  - [DELETE:/timestamps](#deletetimestamps)
  - [GET:/export/port-calls](#getexportport-calls)
  - [GET:/vessels](#getvessels)
  - [POST:/vessel-visibility](#postvessel-visibility)
  - [GET:/vis-services](#getvis-services)
  - [GET:/vis-vessels](#getvis-vessels)
  - [GET:/vis-voyage-plans](#getvis-voyage-plans)
  - [GET:/vis-text-messages](#getvis-text-messages)
  - [GET:/vis-notifications](#getvis-notifications)
  - [POST:/vis-text-messages-with-imo](#postvis-text-messages-with-imo)
  - [POST:/vis-text-messages](#postvis-text-messages)
  - [POST:/vis-send-rta](#postvis-send-rta)
  - [GET:/vis-service-poll](#getvis-service-poll)
  - [GET:/vis-configuration](#getvis-configuration)
  - [POST:/update-port-call-id](#postupdate-port-call-id)
  - [POST:/unset-port-call-id](#postunset-port-call-id)
  - [POST:/restore-timestamp](#postrestore-timestamp)
  - [GET:/rebuild-port-calls](#getrebuild-port-calls)
  - [GET:/timestamps-to-port-calls](#gettimestamps-to-port-calls)
  - [GET:/force-close-port-call](#getforce-close-port-call)
  - [GET:/force-scan-port-call](#getforce-scan-port-call)
  - [GET:/port-call-range](#getport-call-range)
  - [POST:/port-call-range](#postport-call-range)
  - [GET:/inbound-vessels](#getinbound-vessels)
  - [POST:/ports](#postports)
  - [GET:/ports](#getports)
  - [POST:/port-whitelist-in](#postport-whitelist-in)
  - [POST:/port-whitelist-out](#postport-whitelist-out)
  - [POST:/berths](#postberths)
  - [PUT:/berths](#putberths)
  - [DELETE:/berths](#deleteberths)
  - [GET:/berths](#getberths)
  - [GET:/berth-by-id](#getberth-by-id)
  - [POST:/own-nominations](#postown-nominations)
  - [PUT:/own-nominations](#putown-nominations)
  - [DELETE:/own-nominations](#deleteown-nominations)
  - [GET:/own-nominations](#getown-nominations)
  - [GET:/own-nomination-by-id](#getown-nomination-by-id)
  - [GET:/all-nominatable-berths](#getall-nominatable-berths)
  - [GET:/consignee-users](#getconsignee-users)
  - [POST:/nomination-for-consignee](#postnomination-for-consignee)
  - [PUT:/nominations](#putnominations)
  - [DELETE:/nominations](#deletenominations)
  - [GET:/nominations](#getnominations)
  - [GET:/nomination-by-id](#getnomination-by-id)
  - [POST:/berth-reservations](#postberth-reservations)
  - [PUT:/berth-reservations](#putberth-reservations)
  - [DELETE:/berth-reservations](#deleteberth-reservations)
  - [GET:/berth-reservations](#getberth-reservations)
  - [GET:/berth-reservation-by-id](#getberth-reservation-by-id)
  - [POST:/slot-reservations](#postslot-reservations)
  - [PUT:/slot-reservations](#putslot-reservations)
  - [DELETE:/slot-reservations](#deleteslot-reservations)
  - [GET:/slot-reservations](#getslot-reservations)
  - [GET:/slot-reservation-by-id](#getslot-reservation-by-id)
  - [GET:/dashboard-slot-reservations](#getdashboard-slot-reservations)
  - [GET:/timestamp-api-key-weights](#gettimestamp-api-key-weights)
  - [POST:/timestamp-api-key-weights](#posttimestamp-api-key-weights)
  - [GET:/sea-chart/vessels](#getsea-chartvessels)
  - [GET:/sea-chart/markers](#getsea-chartmarkers)
  - [POST:/sea-chart/vessels](#postsea-chartvessels)
  - [GET:/sea-chart/fixed-vessels](#getsea-chartfixed-vessels)
  - [POST:/sea-chart/fixed-vessel](#postsea-chartfixed-vessel)
  - [DELETE:/sea-chart/fixed-vessel](#deletesea-chartfixed-vessel)

## Authentication with API

You can authenticate either with your session key or with your api key. Session key is generated once logged in to the system. Api key is more permanent access method that can be used with machine-to-machine communications.

### Example of using session key

```bash
curl http://localhost:8000/export/timestamps?id=<id>&imo=<imo>&port_call_id=<port_call_id>&start_date_time=<start_date_time>&end_date_time=<end_date_time>&offset=<offset>&limit=<limit>&sort=<sort> \
-X 'GET' \
-H 'Authorization: Bearer yourbearer'
```

### Example of using api key

```bash
curl http://localhost:8000/export/timestamps?id=<id>&imo=<imo>&port_call_id=<port_call_id>&start_date_time=<start_date_time>&end_date_time=<end_date_time>&offset=<offset>&limit=<limit>&sort=<sort> \
-X 'GET' \
-H 'Authorization: ApiKey yourapikey'
```
## POST:/login

Required permission: login

Parameters:

- email **string**

- password **string**

```bash
curl http://localhost:8000/login \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"email":"<email>","password":"<password>"}'

```

## POST:/logout

Required permission: login

No parameters

```bash
curl http://localhost:8000/logout \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/session

Required permission: login

No parameters

```bash
curl http://localhost:8000/session?email=<email>&password=<password> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/register-push-token

Required permission: basic_user_action

Parameters:

- installation_id **mixed**

- platform **mixed**

- push_token **mixed**

```bash
curl http://localhost:8000/register-push-token \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"installation_id":"<installation_id>","platform":"<platform>","push_token":"<push_token>"}'

```

## GET:/users

Required permission: manage_user

Parameters:

- limit **mixed**

- offset **mixed**

- sort **mixed**

- search **mixed** (optional)

```bash
curl http://localhost:8000/users?limit=<limit>&offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/users

Required permission: manage_user

Parameters:

- email **string**

- first_name **string**

- last_name **string**

- role **string** (optional)

```bash
curl http://localhost:8000/users \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"email":"<email>","first_name":"<first_name>","last_name":"<last_name>","role":"<role>"}'

```

## PUT:/users/:id

Required permission: manage_user

Parameters:

- id **int**

- email **string**

- first_name **string**

- last_name **string**

- password **string** (optional)

- role **string** (optional)

- updateRole **bool** (optional)

```bash
curl http://localhost:8000/users/:id \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"email":"<email>","first_name":"<first_name>","last_name":"<last_name>","password":"<password>","role":"<role>","updateRole":"<updateRole>"}'

```

## GET:/users/:id

Required permission: manage_user

Parameters:

- id **int**

```bash
curl http://localhost:8000/users/:id \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## DELETE:/users/:id

Required permission: manage_user

Parameters:

- id **int**

```bash
curl http://localhost:8000/users/:id \
-X 'DELETE'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '[]'

```

## POST:/register

Required permission: public

Parameters:

- first_name **mixed**

- last_name **mixed**

- code **mixed**

- email **mixed**

- password **mixed**

```bash
curl http://localhost:8000/register \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"first_name":"<first_name>","last_name":"<last_name>","code":"<code>","email":"<email>","password":"<password>"}'

```

## POST:/request-password-reset

Required permission: public

Parameters:

- email **string**

- port **string**

```bash
curl http://localhost:8000/request-password-reset \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"email":"<email>","port":"<port>"}'

```

## POST:/reset-password

Required permission: public

Parameters:

- password **string**

- token **string**

```bash
curl http://localhost:8000/reset-password \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"password":"<password>","token":"<token>"}'

```

## POST:/change-password

Required permission: manage_user

Parameters:

- email **mixed**

- password **mixed**

```bash
curl http://localhost:8000/change-password \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"email":"<email>","password":"<password>"}'

```

## POST:/timestamps

Required permission: add_manual_timestamp

Parameters:

- imo **int**

- vessel_name **string**

- time_type **string**

- state **string**

- time **string**

- payload **array**

```bash
curl http://localhost:8000/timestamps \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"imo":123,"vessel_name":"<vessel_name>","time_type":"<time_type>","state":"<state>","time":"<time>","payload":[]}'

```

## GET:/timestamp-definitions

Required permission: add_manual_timestamp

No parameters

```bash
curl http://localhost:8000/timestamp-definitions?imo=<imo>&vessel_name=<vessel_name>&time_type=<time_type>&state=<state>&time=<time>&payload=<payload> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/agent/rest/timestamps

Required permission: timestamp

Parameters:

- imo **int**

- vessel_name **string**

- time_type **string**

- state **string**

- time **string**

- payload **array**

```bash
curl http://localhost:8000/agent/rest/timestamps \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"imo":123,"vessel_name":"<vessel_name>","time_type":"<time_type>","state":"<state>","time":"<time>","payload":[]}'

```

## POST:/agent/rest/logistics-timestamps

Required permission: timestamp

Parameters:

- time **string**

- external_id **int**

- checkpoint **string**

- direction **string**

- front_license_plates **array**

- rear_license_plates **array**

- containers **array**

```bash
curl http://localhost:8000/agent/rest/logistics-timestamps \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"time":"<time>","external_id":123,"checkpoint":"<checkpoint>","direction":"<direction>","front_license_plates":[],"rear_license_plates":[],"containers":[]}'

```

## POST:/agent/rest/vis-notifications

Required permission: timestamp

Parameters:

- time **string**

- from_service_id **string**

- message_id **string**

- message_type **string**

- notification_type **string**

- subject **string**

- payload **string**

```bash
curl http://localhost:8000/agent/rest/vis-notifications \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"time":"<time>","from_service_id":"<from_service_id>","message_id":"<message_id>","message_type":"<message_type>","notification_type":"<notification_type>","subject":"<subject>","payload":"<payload>"}'

```

## POST:/agent/rest/vis-messages

Required permission: timestamp

Parameters:

- time **string**

- from_service_id **string**

- to_service_id **string**

- message_id **string**

- message_type **string**

- payload **string**

```bash
curl http://localhost:8000/agent/rest/vis-messages \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"time":"<time>","from_service_id":"<from_service_id>","to_service_id":"<to_service_id>","message_id":"<message_id>","message_type":"<message_type>","payload":"<payload>"}'

```

## POST:/agent/rest/vis-voyage-plans

Required permission: timestamp

Parameters:

- time **string**

- from_service_id **string**

- to_service_id **string**

- message_id **string**

- message_type **string**

- rtz_state **string**

- rtz_parse_results **string**

- payload **string**

```bash
curl http://localhost:8000/agent/rest/vis-voyage-plans \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"time":"<time>","from_service_id":"<from_service_id>","to_service_id":"<to_service_id>","message_id":"<message_id>","message_type":"<message_type>","rtz_state":"<rtz_state>","rtz_parse_results":"<rtz_parse_results>","payload":"<payload>"}'

```

## POST:/agent/rest/vis-vessels

Required permission: timestamp

Parameters:

- imo **int**

- vessel_name **string**

- service_id **string**

- service_url **string**

```bash
curl http://localhost:8000/agent/rest/vis-vessels \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"imo":123,"vessel_name":"<vessel_name>","service_id":"<service_id>","service_url":"<service_url>"}'

```

## POST:/agent/rest/live-eta

Required permission: timestamp

Parameters:

- imo **int**

- time **string**

- payload **array**

```bash
curl http://localhost:8000/agent/rest/live-eta \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"imo":123,"time":"<time>","payload":[]}'

```

## POST:/agent/rest/inbound-vessels

Required permission: timestamp

Parameters:

- time **string**

- imo **int**

- vessel_name **string**

- from_service_id **string**

```bash
curl http://localhost:8000/agent/rest/inbound-vessels \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"time":"<time>","imo":123,"vessel_name":"<vessel_name>","from_service_id":"<from_service_id>"}'

```

## POST:/agent/rest/slot-reservations

Required permission: timestamp

Parameters:

- email **string**

- imo **int**

- vessel_name **string**

- loa **string**

- beam **string**

- draft **string**

- laytime **string**

- eta **string**

```bash
curl http://localhost:8000/agent/rest/slot-reservations \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"email":"<email>","imo":123,"vessel_name":"<vessel_name>","loa":"<loa>","beam":"<beam>","draft":"<draft>","laytime":"<laytime>","eta":"<eta>"}'

```

## PUT:/agent/rest/slot-reservations

Required permission: timestamp

Parameters:

- id **int**

- laytime **string**

- jit_eta **string**

```bash
curl http://localhost:8000/agent/rest/slot-reservations \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123,"laytime":"<laytime>","jit_eta":"<jit_eta>"}'

```

## GET:/agent/rest/slot-reservation-by-id

Required permission: timestamp

Parameters:

- id **int**

```bash
curl http://localhost:8000/agent/rest/slot-reservation-by-id?id=<id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## DELETE:/agent/rest/slot-reservations

Required permission: timestamp

Parameters:

- id **int**

```bash
curl http://localhost:8000/agent/rest/slot-reservations \
-X 'DELETE'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123}'

```

## GET:/port-calls

Required permission: basic_user_action

Parameters:

- limit **mixed**

- offset **mixed**

- sort **mixed** (optional)

- search **mixed** (optional)

```bash
curl http://localhost:8000/port-calls?limit=<limit>&offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/port-call-timestamps

Required permission: basic_user_action

Parameters:

- port_call_id **mixed**

- limit **mixed**

- offset **mixed**

- sort **mixed**

```bash
curl http://localhost:8000/port-call-timestamps?port_call_id=<port_call_id>&limit=<limit>&offset=<offset>&sort=<sort> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/non-port-call-timestamps

Required permission: basic_user_action

Parameters:

- imo **mixed**

- limit **mixed**

- offset **mixed**

- sort **mixed**

```bash
curl http://localhost:8000/non-port-call-timestamps?imo=<imo>&limit=<limit>&offset=<offset>&sort=<sort> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/ongoing-port-calls

Required permission: basic_user_action

No parameters

```bash
curl http://localhost:8000/ongoing-port-calls?imo=<imo>&limit=<limit>&offset=<offset>&sort=<sort> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/port-call

Required permission: basic_user_action

Parameters:

- id **int**

```bash
curl http://localhost:8000/port-call?id=<id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/port-call-csv

Required permission: basic_user_action

Parameters:

- id **int**

- time_zone **string**

```bash
curl http://localhost:8000/port-call-csv?id=<id>&time_zone=<time_zone> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/port-call-template

Required permission: basic_user_action

Parameters:

- namespace **string**

```bash
curl http://localhost:8000/port-call-template?namespace=<namespace> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/logistics-timestamps/:limit

Required permission: basic_user_action

Parameters:

- limit **int**

```bash
curl http://localhost:8000/logistics-timestamps/:limit \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/logistics-timestamps/by-license-plate/:license_plate

Required permission: basic_user_action

Parameters:

- license_plate **string**

```bash
curl http://localhost:8000/logistics-timestamps/by-license-plate/:license_plate \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/rta-web-form

Required permission: send_rta_web_form

Parameters:

- port_call_id **int**

- port **string**

- imo **int**

- rta **string**

- eta_min **string**

- eta_max **string**

```bash
curl http://localhost:8000/rta-web-form \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"port_call_id":123,"port":"<port>","imo":123,"rta":"<rta>","eta_min":"<eta_min>","eta_max":"<eta_max>"}'

```

## GET:/api-keys

Required permission: manage_api_key

Parameters:

- limit **mixed**

- offset **mixed**

- sort **mixed**

- search **mixed** (optional)

```bash
curl http://localhost:8000/api-keys?limit=<limit>&offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/api-keys

Required permission: manage_api_key

Parameters:

- name **mixed**

```bash
curl http://localhost:8000/api-keys \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"name":"<name>"}'

```

## PUT:/api-keys/disable

Required permission: manage_api_key

Parameters:

- id **int**

```bash
curl http://localhost:8000/api-keys/disable \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123}'

```

## PUT:/api-keys/enable

Required permission: manage_api_key

Parameters:

- id **int**

```bash
curl http://localhost:8000/api-keys/enable \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123}'

```

## GET:/health-check

Required permission: public

No parameters

```bash
curl http://localhost:8000/health-check?id=<id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/translation/:ns/:lng

Required permission: translation

Parameters:

- ns **string**

- lng **string**

- missing **array** (optional)

```bash
curl http://localhost:8000/translation/:ns/:lng \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"missing":[]}'

```

## GET:/translations/:ns/:lng

Required permission: public

Parameters:

- ns **string**

- lng **string**

```bash
curl http://localhost:8000/translations/:ns/:lng \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## PUT:/translations/:ns/:lng

Required permission: manage_translation

Parameters:

- ns **string**

- lng **string**

- updated **array**

```bash
curl http://localhost:8000/translations/:ns/:lng \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"updated":[]}'

```

## POST:/translations/:ns/:lng

Required permission: manage_translation

Parameters:

- ns **string**

- lng **string**

- translations **array**

```bash
curl http://localhost:8000/translations/:ns/:lng \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"translations":[]}'

```

## GET:/registration-codes/:limit

Required permission: manage_registration_code

Parameters:

- limit **mixed**

- offset **mixed**

- sort **mixed**

- search **mixed** (optional)

```bash
curl http://localhost:8000/registration-codes/:limit?offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/registration-codes

Required permission: manage_registration_code

Parameters:

- role **mixed**

- description **mixed**

```bash
curl http://localhost:8000/registration-codes \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"role":"<role>","description":"<description>"}'

```

## PUT:/registration-codes/:id

Required permission: manage_registration_code

Parameters:

- id **string**

- enabled **int**

- role **string**

```bash
curl http://localhost:8000/registration-codes/:id \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"enabled":123,"role":"<role>"}'

```

## DELETE:/registration-codes/:id

Required permission: manage_registration_code

Parameters:

- id **int**

```bash
curl http://localhost:8000/registration-codes/:id \
-X 'DELETE'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '[]'

```

## GET:/notifications/:limit

Required permission: basic_user_action

No parameters

```bash
curl http://localhost:8000/notifications/:limit?id=<id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/notifications

Required permission: send_push_notification

Parameters:

- type **string**

- message **string**

- ship_imo **string** (optional)

```bash
curl http://localhost:8000/notifications \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"type":"<type>","message":"<message>","ship_imo":"<ship_imo>"}'

```

## DELETE:/notifications/:id

Required permission: delete_push_notification

Parameters:

- id **int**

```bash
curl http://localhost:8000/notifications/:id \
-X 'DELETE'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '[]'

```

## GET:/pinned-vessel-ids

Required permission: basic_user_action

No parameters

```bash
curl http://localhost:8000/pinned-vessel-ids?id=<id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## PUT:/pinned-vessel-ids

Required permission: basic_user_action

Parameters:

- vessel_ids **mixed** (optional)

```bash
curl http://localhost:8000/pinned-vessel-ids \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"vessel_ids":"<vessel_ids>"}'

```

## GET:/settings

Required permission: basic_user_action

No parameters

```bash
curl http://localhost:8000/settings?vessel_ids=<vessel_ids> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/settings/by-name/:name

Required permission: basic_user_action

Parameters:

- name **string**

```bash
curl http://localhost:8000/settings/by-name/:name \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## PUT:/settings/:name/:value

Required permission: manage_setting

Parameters:

- name **string**

- value **string**

```bash
curl http://localhost:8000/settings/:name/:value \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '[]'

```

## GET:/roles

Required permission: basic_user_action

No parameters

```bash
curl http://localhost:8000/roles?name=<name>&value=<value> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/permissions

Required permission: basic_user_action

No parameters

```bash
curl http://localhost:8000/permissions?name=<name>&value=<value> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/role-permissions

Required permission: basic_user_action

No parameters

```bash
curl http://localhost:8000/role-permissions?name=<name>&value=<value> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/role-permissions/by-role/:role

Required permission: basic_user_action

Parameters:

- role **string**

```bash
curl http://localhost:8000/role-permissions/by-role/:role \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## PUT:/role-permissions/:role/:permission

Required permission: manage_permission

Parameters:

- role **string**

- permission **string**

```bash
curl http://localhost:8000/role-permissions/:role/:permission \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '[]'

```

## DELETE:/role-permissions/:role/:permission

Required permission: manage_permission

Parameters:

- role **string**

- permission **string**

```bash
curl http://localhost:8000/role-permissions/:role/:permission \
-X 'DELETE'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '[]'

```

## POST:/push-notification-tokens

Required permission: push_notification_token

Parameters:

- tokens **array**

```bash
curl http://localhost:8000/push-notification-tokens \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"tokens":[]}'

```

## GET:/export/timestamps

Required permission: export_api

Parameters:

- id **int** (optional)

- imo **int** (optional)

- port_call_id **int** (optional)

- start_date_time **string** (optional)

- end_date_time **string** (optional)

- offset **int** (optional)

- limit **int** (optional)

- sort **string** (optional)

```bash
curl http://localhost:8000/export/timestamps?id=<id>&imo=<imo>&port_call_id=<port_call_id>&start_date_time=<start_date_time>&end_date_time=<end_date_time>&offset=<offset>&limit=<limit>&sort=<sort> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/timestamps

Required permission: manage_port_call

Parameters:

- id **int** (optional)

- imo **int** (optional)

- port_call_id **int** (optional)

- start_date_time **string** (optional)

- end_date_time **string** (optional)

- offset **int** (optional)

- limit **int** (optional)

- sort **string** (optional)

```bash
curl http://localhost:8000/timestamps?id=<id>&imo=<imo>&port_call_id=<port_call_id>&start_date_time=<start_date_time>&end_date_time=<end_date_time>&offset=<offset>&limit=<limit>&sort=<sort> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## DELETE:/timestamps

Required permission: manage_port_call

Parameters:

- id **int**

```bash
curl http://localhost:8000/timestamps \
-X 'DELETE'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123}'

```

## GET:/export/port-calls

Required permission: export_api

No parameters

```bash
curl http://localhost:8000/export/port-calls?id=<id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/vessels

Required permission: basic_user_action

Parameters:

- limit **int** (optional)

- offset **int** (optional)

- sort **string** (optional)

- search **string** (optional)

```bash
curl http://localhost:8000/vessels?limit=<limit>&offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/vessel-visibility

Required permission: manage_port_call

Parameters:

- imo **int**

- visible **mixed**

```bash
curl http://localhost:8000/vessel-visibility \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"imo":123,"visible":"<visible>"}'

```

## GET:/vis-services

Required permission: view_vis_information

Parameters:

- imo **int** (optional)

- service_id **string** (optional)

```bash
curl http://localhost:8000/vis-services?imo=<imo>&service_id=<service_id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/vis-vessels

Required permission: view_vis_information

Parameters:

- imo **int** (optional)

- vessel_name **string** (optional)

- service_id **string** (optional)

- offset **int** (optional)

- limit **int** (optional)

- sort **string** (optional)

```bash
curl http://localhost:8000/vis-vessels?imo=<imo>&vessel_name=<vessel_name>&service_id=<service_id>&offset=<offset>&limit=<limit>&sort=<sort> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/vis-voyage-plans

Required permission: view_vis_information

Parameters:

- from_service_id **string** (optional)

- to_service_id **string** (optional)

- offset **int** (optional)

- limit **int** (optional)

- sort **string** (optional)

```bash
curl http://localhost:8000/vis-voyage-plans?from_service_id=<from_service_id>&to_service_id=<to_service_id>&offset=<offset>&limit=<limit>&sort=<sort> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/vis-text-messages

Required permission: view_vis_information

Parameters:

- from_service_id **string** (optional)

- to_service_id **string** (optional)

- offset **int** (optional)

- limit **int** (optional)

- sort **string** (optional)

```bash
curl http://localhost:8000/vis-text-messages?from_service_id=<from_service_id>&to_service_id=<to_service_id>&offset=<offset>&limit=<limit>&sort=<sort> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/vis-notifications

Required permission: view_vis_information

Parameters:

- from_service_id **string** (optional)

- offset **int** (optional)

- limit **int** (optional)

- sort **string** (optional)

```bash
curl http://localhost:8000/vis-notifications?from_service_id=<from_service_id>&offset=<offset>&limit=<limit>&sort=<sort> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/vis-text-messages-with-imo

Required permission: send_vis_text_message

Parameters:

- imo **int**

- subject **string**

- body **string**

```bash
curl http://localhost:8000/vis-text-messages-with-imo \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"imo":123,"subject":"<subject>","body":"<body>"}'

```

## POST:/vis-text-messages

Required permission: send_vis_text_message

Parameters:

- vis_service_id **string**

- author **string**

- subject **string**

- body **string**

- informationObjectReferenceId **string** (optional)

- informationObjectReferenceType **string** (optional)

- area **string** (optional)

```bash
curl http://localhost:8000/vis-text-messages \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"vis_service_id":"<vis_service_id>","author":"<author>","subject":"<subject>","body":"<body>","informationObjectReferenceId":"<informationObjectReferenceId>","informationObjectReferenceType":"<informationObjectReferenceType>","area":"<area>"}'

```

## POST:/vis-send-rta

Required permission: send_vis_rta

Parameters:

- vis_service_id **string**

- rta **string**

- eta_min **string**

- eta_max **string**

- port_call_id **int** (optional)

```bash
curl http://localhost:8000/vis-send-rta \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"vis_service_id":"<vis_service_id>","rta":"<rta>","eta_min":"<eta_min>","eta_max":"<eta_max>","port_call_id":123}'

```

## GET:/vis-service-poll

Required permission: view_vis_information

No parameters

```bash
curl http://localhost:8000/vis-service-poll?vis_service_id=<vis_service_id>&rta=<rta>&eta_min=<eta_min>&eta_max=<eta_max>&port_call_id=<port_call_id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/vis-configuration

Required permission: view_vis_information

No parameters

```bash
curl http://localhost:8000/vis-configuration?vis_service_id=<vis_service_id>&rta=<rta>&eta_min=<eta_min>&eta_max=<eta_max>&port_call_id=<port_call_id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/update-port-call-id

Required permission: manage_port_call

Parameters:

- id **int**

- portCallId **int**

```bash
curl http://localhost:8000/update-port-call-id \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123,"portCallId":123}'

```

## POST:/unset-port-call-id

Required permission: manage_port_call

Parameters:

- id **int**

```bash
curl http://localhost:8000/unset-port-call-id \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123}'

```

## POST:/restore-timestamp

Required permission: manage_port_call

Parameters:

- id **int**

```bash
curl http://localhost:8000/restore-timestamp \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123}'

```

## GET:/rebuild-port-calls

Required permission: manage_port_call

Parameters:

- imo **int**

```bash
curl http://localhost:8000/rebuild-port-calls?imo=<imo> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/timestamps-to-port-calls

Required permission: manage_port_call

Parameters:

- imo **int**

- isFromAgent **bool** (optional)

- ignoreStatus **bool** (optional)

```bash
curl http://localhost:8000/timestamps-to-port-calls?imo=<imo>&isFromAgent=<isFromAgent>&ignoreStatus=<ignoreStatus> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/force-close-port-call

Required permission: manage_port_call

Parameters:

- port_call_id **int**

```bash
curl http://localhost:8000/force-close-port-call?port_call_id=<port_call_id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/force-scan-port-call

Required permission: manage_port_call

Parameters:

- port_call_id **int**

```bash
curl http://localhost:8000/force-scan-port-call?port_call_id=<port_call_id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/port-call-range

Required permission: manage_port_call

Parameters:

- port_call_id **int**

```bash
curl http://localhost:8000/port-call-range?port_call_id=<port_call_id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/port-call-range

Required permission: manage_port_call

Parameters:

- port_call_id **int**

- start **string**

- end **string**

```bash
curl http://localhost:8000/port-call-range \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"port_call_id":123,"start":"<start>","end":"<end>"}'

```

## GET:/inbound-vessels

Required permission: basic_user_action

Parameters:

- limit **int** (optional)

- offset **int** (optional)

- sort **string** (optional)

- search **string** (optional)

```bash
curl http://localhost:8000/inbound-vessels?limit=<limit>&offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/ports

Required permission: manage_port

Parameters:

- name **string**

- service_id **string**

- locodes **array**

```bash
curl http://localhost:8000/ports \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"name":"<name>","service_id":"<service_id>","locodes":[]}'

```

## GET:/ports

Required permission: manage_port

Parameters:

- limit **int** (optional)

- offset **int** (optional)

- sort **string** (optional)

- search **string** (optional)

```bash
curl http://localhost:8000/ports?limit=<limit>&offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/port-whitelist-in

Required permission: manage_port

Parameters:

- service_id **string**

- whitelist **mixed**

```bash
curl http://localhost:8000/port-whitelist-in \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"service_id":"<service_id>","whitelist":"<whitelist>"}'

```

## POST:/port-whitelist-out

Required permission: manage_port

Parameters:

- service_id **string**

- whitelist **mixed**

```bash
curl http://localhost:8000/port-whitelist-out \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"service_id":"<service_id>","whitelist":"<whitelist>"}'

```

## POST:/berths

Required permission: manage_berth

Parameters:

- code **string**

- name **string**

- nominatable **mixed**

```bash
curl http://localhost:8000/berths \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"code":"<code>","name":"<name>","nominatable":"<nominatable>"}'

```

## PUT:/berths

Required permission: manage_berth

Parameters:

- id **int**

- code **string**

- name **string**

- nominatable **mixed**

```bash
curl http://localhost:8000/berths \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123,"code":"<code>","name":"<name>","nominatable":"<nominatable>"}'

```

## DELETE:/berths

Required permission: manage_berth

Parameters:

- id **int**

```bash
curl http://localhost:8000/berths \
-X 'DELETE'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123}'

```

## GET:/berths

Required permission: view_berth

Parameters:

- limit **int** (optional)

- offset **int** (optional)

- sort **string** (optional)

- search **string** (optional)

```bash
curl http://localhost:8000/berths?limit=<limit>&offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/berth-by-id

Required permission: view_berth

Parameters:

- id **int**

```bash
curl http://localhost:8000/berth-by-id?id=<id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/own-nominations

Required permission: manage_own_queue_nomination

Parameters:

- company_name **string**

- email **string**

- imo **int**

- vessel_name **string**

- window_start **string**

- window_end **string**

- berth_ids **array**

```bash
curl http://localhost:8000/own-nominations \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"company_name":"<company_name>","email":"<email>","imo":123,"vessel_name":"<vessel_name>","window_start":"<window_start>","window_end":"<window_end>","berth_ids":[]}'

```

## PUT:/own-nominations

Required permission: manage_own_queue_nomination

Parameters:

- id **int**

- company_name **string**

- email **string**

- imo **int**

- vessel_name **string**

- window_start **string** (optional)

- window_end **string** (optional)

- berth_ids **array** (optional)

```bash
curl http://localhost:8000/own-nominations \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123,"company_name":"<company_name>","email":"<email>","imo":123,"vessel_name":"<vessel_name>","window_start":"<window_start>","window_end":"<window_end>","berth_ids":[]}'

```

## DELETE:/own-nominations

Required permission: manage_own_queue_nomination

Parameters:

- id **int**

```bash
curl http://localhost:8000/own-nominations \
-X 'DELETE'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123}'

```

## GET:/own-nominations

Required permission: view_own_queue_nomination

Parameters:

- limit **int** (optional)

- offset **int** (optional)

- sort **string** (optional)

- search **string** (optional)

```bash
curl http://localhost:8000/own-nominations?limit=<limit>&offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/own-nomination-by-id

Required permission: view_own_queue_nomination

Parameters:

- id **int**

```bash
curl http://localhost:8000/own-nomination-by-id?id=<id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/all-nominatable-berths

Required permission: view_own_queue_nomination

No parameters

```bash
curl http://localhost:8000/all-nominatable-berths?id=<id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/consignee-users

Required permission: view_all_queue_nomination

No parameters

```bash
curl http://localhost:8000/consignee-users?id=<id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/nomination-for-consignee

Required permission: manage_all_queue_nomination

Parameters:

- consignee_user_id **int**

- company_name **string**

- email **string**

- imo **int**

- vessel_name **string**

- window_start **string**

- window_end **string**

- berth_ids **array**

```bash
curl http://localhost:8000/nomination-for-consignee \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"consignee_user_id":123,"company_name":"<company_name>","email":"<email>","imo":123,"vessel_name":"<vessel_name>","window_start":"<window_start>","window_end":"<window_end>","berth_ids":[]}'

```

## PUT:/nominations

Required permission: manage_all_queue_nomination

Parameters:

- id **int**

- company_name **string**

- email **string**

- imo **int**

- vessel_name **string**

- window_start **string** (optional)

- window_end **string** (optional)

- berth_ids **array** (optional)

```bash
curl http://localhost:8000/nominations \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123,"company_name":"<company_name>","email":"<email>","imo":123,"vessel_name":"<vessel_name>","window_start":"<window_start>","window_end":"<window_end>","berth_ids":[]}'

```

## DELETE:/nominations

Required permission: manage_all_queue_nomination

Parameters:

- id **int**

```bash
curl http://localhost:8000/nominations \
-X 'DELETE'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123}'

```

## GET:/nominations

Required permission: view_all_queue_nomination

Parameters:

- limit **int** (optional)

- offset **int** (optional)

- sort **string** (optional)

- search **string** (optional)

```bash
curl http://localhost:8000/nominations?limit=<limit>&offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/nomination-by-id

Required permission: view_all_queue_nomination

Parameters:

- id **int**

```bash
curl http://localhost:8000/nomination-by-id?id=<id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/berth-reservations

Required permission: manage_berth_reservation

Parameters:

- berth_id **int**

- berth_reservation_type **string**

- reservation_start **string**

- reservation_end **string**

- slot_reservation_id **int** (optional)

```bash
curl http://localhost:8000/berth-reservations \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"berth_id":123,"berth_reservation_type":"<berth_reservation_type>","reservation_start":"<reservation_start>","reservation_end":"<reservation_end>","slot_reservation_id":123}'

```

## PUT:/berth-reservations

Required permission: manage_berth_reservation

Parameters:

- id **int**

- reservation_start **string**

- reservation_end **string**

```bash
curl http://localhost:8000/berth-reservations \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123,"reservation_start":"<reservation_start>","reservation_end":"<reservation_end>"}'

```

## DELETE:/berth-reservations

Required permission: manage_berth_reservation

Parameters:

- id **int**

```bash
curl http://localhost:8000/berth-reservations \
-X 'DELETE'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123}'

```

## GET:/berth-reservations

Required permission: view_berth_reservation

Parameters:

- berth_id **int**

- limit **int** (optional)

- offset **int** (optional)

- sort **string** (optional)

- search **string** (optional)

```bash
curl http://localhost:8000/berth-reservations?berth_id=<berth_id>&limit=<limit>&offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/berth-reservation-by-id

Required permission: view_berth_reservation

Parameters:

- id **int**

```bash
curl http://localhost:8000/berth-reservation-by-id?id=<id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/slot-reservations

Required permission: manage_queue_slot_reservation

Parameters:

- email **string**

- imo **int**

- vessel_name **string**

- loa **float**

- beam **float**

- draft **float**

- laytime **string**

- eta **string**

```bash
curl http://localhost:8000/slot-reservations \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"email":"<email>","imo":123,"vessel_name":"<vessel_name>","loa":"<loa>","beam":"<beam>","draft":"<draft>","laytime":"<laytime>","eta":"<eta>"}'

```

## PUT:/slot-reservations

Required permission: manage_queue_slot_reservation

Parameters:

- id **int**

- laytime **string**

- jit_eta **string**

- rta_window_start **string** (optional)

```bash
curl http://localhost:8000/slot-reservations \
-X 'PUT'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123,"laytime":"<laytime>","jit_eta":"<jit_eta>","rta_window_start":"<rta_window_start>"}'

```

## DELETE:/slot-reservations

Required permission: manage_queue_slot_reservation

Parameters:

- id **int**

- cancel_only **bool** (optional)

- by_vessel **bool** (optional)

```bash
curl http://localhost:8000/slot-reservations \
-X 'DELETE'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123,"cancel_only":"<cancel_only>","by_vessel":"<by_vessel>"}'

```

## GET:/slot-reservations

Required permission: view_queue_slot_reservation

Parameters:

- limit **int** (optional)

- offset **int** (optional)

- sort **string** (optional)

- search **string** (optional)

- isDashboard **bool** (optional)

```bash
curl http://localhost:8000/slot-reservations?limit=<limit>&offset=<offset>&sort=<sort>&search=<search>&isDashboard=<isDashboard> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/slot-reservation-by-id

Required permission: view_queue_slot_reservation

Parameters:

- id **int**

```bash
curl http://localhost:8000/slot-reservation-by-id?id=<id> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/dashboard-slot-reservations

Required permission: view_queue_slot_reservation

Parameters:

- limit **int** (optional)

- offset **int** (optional)

- sort **string** (optional)

- search **string** (optional)

```bash
curl http://localhost:8000/dashboard-slot-reservations?limit=<limit>&offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/timestamp-api-key-weights

Required permission: manage_api_key

No parameters

```bash
curl http://localhost:8000/timestamp-api-key-weights?limit=<limit>&offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/timestamp-api-key-weights

Required permission: manage_api_key

Parameters:

- timestamp_time_type **string**

- timestamp_state **string**

- api_key_ids **array**

```bash
curl http://localhost:8000/timestamp-api-key-weights \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"timestamp_time_type":"<timestamp_time_type>","timestamp_state":"<timestamp_state>","api_key_ids":[]}'

```

## GET:/sea-chart/vessels

Required permission: basic_user_action

No parameters

```bash
curl http://localhost:8000/sea-chart/vessels?timestamp_time_type=<timestamp_time_type>&timestamp_state=<timestamp_state>&api_key_ids=<api_key_ids> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## GET:/sea-chart/markers

Required permission: basic_user_action

No parameters

```bash
curl http://localhost:8000/sea-chart/markers?timestamp_time_type=<timestamp_time_type>&timestamp_state=<timestamp_state>&api_key_ids=<api_key_ids> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/sea-chart/vessels

Required permission: update_sea_chart_location

Parameters:

- latitude **mixed**

- longitude **mixed**

- headingDegrees **mixed**

- speedKnots **mixed**

- locationTimestamp **mixed**

- imo **mixed** (optional)

- mmsi **mixed** (optional)

```bash
curl http://localhost:8000/sea-chart/vessels \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"latitude":"<latitude>","longitude":"<longitude>","headingDegrees":"<headingDegrees>","speedKnots":"<speedKnots>","locationTimestamp":"<locationTimestamp>","imo":"<imo>","mmsi":"<mmsi>"}'

```

## GET:/sea-chart/fixed-vessels

Required permission: modify_sea_chart_vessels

Parameters:

- limit **int**

- offset **int**

- sort **string**

- search **string** (optional)

```bash
curl http://localhost:8000/sea-chart/fixed-vessels?limit=<limit>&offset=<offset>&sort=<sort>&search=<search> \
-X 'GET'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
```

## POST:/sea-chart/fixed-vessel

Required permission: modify_sea_chart_vessels

Parameters:

- mmsi **int**

- markerTypeId **int**

- vesselName **string**

- imo **mixed** (optional)

```bash
curl http://localhost:8000/sea-chart/fixed-vessel \
-X 'POST'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"mmsi":123,"markerTypeId":123,"vesselName":"<vesselName>","imo":"<imo>"}'

```

## DELETE:/sea-chart/fixed-vessel

Required permission: modify_sea_chart_vessels

Parameters:

- id **int**

```bash
curl http://localhost:8000/sea-chart/fixed-vessel \
-X 'DELETE'\
-H 'Content-Type: application/json;charset=utf-8' \
-H 'Accept: application/json, text/plain, */*' \
-H 'Authorization: Bearer yourbearer'
-d '{"id":123}'

```

