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
