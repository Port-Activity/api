# Port Activity App

## Developing

This project follows PSR-2 Coding Style.
Read more: https://docs.opnsense.org/development/guidelines/psr2.html

### Install database

Note: port is exposed to host in 5532. Just to avoid other instances of posgres. See Makefile for more.
```
 make docker-init
```

If already created but docker has shutdown just restart it with:
```
make docker-start
```

### Init local environment

Create init_local.php file and set correct environment values there.
```
	cp src/lib/init_local.php.sample.php src/lib/init_local.php
```

### Database create
Create database (first time only):
```
#> make db-create
```


### Database migration
Database requires migration when changed. To migrate database do:
```
#> make db-migrate
```

### Install dependencies
```
#> make install
```

### Check code formatting with phpcs
```
#> make phpcs
```

### Autofix some code formatting with phpcbf
```
#> make phpcbf
```

### Run tests
```
#> make test
```

### Run behat tests
All tests can be run with:

```
make test-integration-cycle
```

Single test can be run with:

```
make test-integration-cycle BEHAT_FEATURE=features/<FEATURE_FILE>
```

All behat tests assume clean database.
Behat containers are not automatically cleaned when tests fail.
Containers can be cleaned with:

```
make stop-services
```

#### Test coverage
Coverage should be generated whenever test are executed. However you need to intall xdebug.

```
pecl install xdebug
```


### Run server
```
#> make run
```

### Call API


```
#> curl 'http://localhost:8000/login' \
-XPOST \
-H 'Content-Type: application/json;charset=UTF-8' \
--data-binary '{
  "email": "foo",
  "password": "foo"
}'

{"message":"authentication ok"}
```

### Call Sea Chart API

Updating vessel locations (IMO is optional):
```
curl 'http://localhost:8000/sea-chart/vessels' \
-H 'Authorization: ApiKey [VALID-API-KEY]' \
-H 'Content-Type: application/json' \
-X POST \
-d '{"locations":[{"imo": 100000181, "mmsi": 230928000,"latitude": 60.803207, \
"longitude": 17.856447, "headingDegrees": 120.4,"speedKnots": 4.6, \
"locationTimestamp":"2020-11-12T12:03:05+00:00","courseOverGroundDegrees":90}]}'
```

Getting fixed vessels (search is optional):
```
curl -X GET -G 'http://localhost:8000/sea-chart/fixed-vessels' \
-H 'Authorization: ApiKey [VALID-API-KEY]' \
-H 'Content-Type: application/json' \
-d limit=10 \
-d offset=0 \
-d sort=vessel_name \
-d search=Tug
```

Adding fixed vessel (IMO is optional):
```
curl 'http://localhost:8000/sea-chart/fixed-vessel' \
-H 'Authorization: ApiKey [VALID-API-KEY]' \
-H 'Content-Type: application/json' \
-X POST \
-d '{"mmsi": 316041365,"markerTypeId":2,"vesselName":"TugFixed","imo": null}'
```

Deleting fixed vessel:
```
curl 'http://localhost:8000/sea-chart/fixed-vessel' \
-H 'Authorization: ApiKey [VALID-API-KEY]' \
-H 'Content-Type: application/json' \
-X DELETE \
-d '{"id": 9}'
```

## Alternative for makefile: docker-compose

### Init local environment

Create init_local.php file and set correct environment values there.
```
	cp src/lib/init_local.php.sample.php src/lib/init_local.php
```

Create `.env` from `.env.template` with values from `init_local.php`

### Commands for docker compose
- `docker-compose build` build containers
- `docker-compose up -d` start containers in detached mode
- `docker-compose stop` stop containers
- `docker-compose down` remove containers
- `docker-compose down -v` remove containers and volumes (database is removed)
- `docker-compose logs -f api` show logs from api container
- `docker-compose logs -f redis` show logs from redis container
- `docker-compose logs -f postgres` show logs from postgres container
- `docker-compose ps` show running containers

- `docker-compose exec php make db-migrate` make migrations

## Timestamp payload recommended key names
- external_id = ID from external service
- original_message = Original message from external service
- location = Free form location name
- from_port = Where the vessel is coming from (format UNLOCODE)
- to_port = Where the vessel is going to (format UNLOCODE)
- next_port = Where the vessel is going next when it departs from to_port (format UNLOCODE)
- call_sign = Vessel call sign
- mmsi = Vessel MMSI number
- gross_weight = Vessel gross weight (unit?)
- net_weight = Vessel net weight (unit?)
- email = Vessel email
- berth = Berth number (for ETA form usage)
- berth_name = Berth name (for badge usage)
- vessel_loa = Vessel LOA (unit?)
- vessel_beam = Vessel beam (unit?)
- vessel_draft = Vessel draft (unit?)
- source = Source of data, eg. "Live_ETA"
- slot_reservation_id = Internal ID of slot request
- slot_reservation_status = Internal status of slot request
- rta_window_start = Start of RTA window
- rta_window_end = End of RTA window
- laytime = How long vessel will stay at berth

## STM ships aka. VIS ships

In code base there is terminology VIS used frequently. In UI STM terminology is used. Basicly everything referred as VIS refers to STM ships.


## Updating unlocode lists
To update uplocode data do following:
- update file ./src/lib/SMA/PAA/SERVICE/unlocode/code-list.csv  (download new version from internet)
- run ./split-unlodes-by-country.sh script
- commit changed files to git
- deploy new version

More info about unlocodes: https://www.unece.org/fileadmin/DAM/cefact/locode/UNLOCODE_Manual.pdf
