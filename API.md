# API

## Authentication

The API requires authentication. For that POST the credentials in JSON format to `/api/login/`. After that a rememberme-cookie will be automatically set that will be valid for a month or until the server restarts.

## Endpoints
| url | permission | purpose | Example Request | Example Response |
| --- | ---------- | ------- | ------------------- | ------------ |
| /api/costumers/{id} | COSTUMER_SHOW | get a JSON object of the costumer with id {id} | GET `/api/costumers/4` |`[{"id":4,"firstname":...,"lastname":...,"active":true,"enddate":...,"Department":"BVB"}]`|
| /api/costumers?{key1=value1&key2=value2&...} | COSTUMER_SHOW | Filter costumers by property. Empty for all costumers | /api/costumers?active=true&Department=IT | `[{"id":...,"active":true,"enddate":...,"Department":"IT"},{"id":...,"active":true,"enddate":...,"Department":"IT"},{"id":...,"active":true,"enddate":...,"Department":"IT"}]` |
| /api/login/ | None | get login session | `POST` `/api/login/` BODY(JSON):`{username=admin&password=admin}` |  |