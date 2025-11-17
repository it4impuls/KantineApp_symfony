# API

## Authentication

The API requires authentication. For that POST the credentials in JSON format to `/api/login/`. After that a rememberme-cookie will be automatically set that will be valid for a month or until the server restarts.

## Endpoints
| url | permission | purpose | Example Request | Example Response |
| --- | ---------- | ------- | ------------------- | ------------ |
| /api/costumers/{id} | COSTUMER_SHOW | get a JSON object of the costumer with id {id} | GET `/api/costumers/4` |`[{"id":4,"firstname":"test4","lastname":"test4","active":true,"enddate":{"date":"2029-10-28 00:00:00.000000","timezone_type":3,"timezone":"UTC"},"Department":"BVB"}]`|
| /api/costumers?{key1=value1&key2=value2&...} | COSTUMER_SHOW | Filter costumers by property. Empty for all costumers | /api/costumers?active=true&Department=IT | `[{"id":102,...,"active":true,"enddate":...,"Department":"IT"},{"id":103,...,"active":true,"enddate":...,"Department":"IT"},{"id":104,...,"active":true,"enddate":...,"Department":"IT"}]` |
| /api/login/ | None | get login session | `POST` `/api/login/` BODY(JSON):`{username=admin&password=admin}` |  |