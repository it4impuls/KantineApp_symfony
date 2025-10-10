# Auf Server laden ohne ssh-zugang

Ohne ssh zugang können composer-dependencies nicht automatisch installiert werden können, müssen diese manuell via ftp hochgeladen werden.

Zu hochladenden Dateien/Ordner:
```
assets/
config/
public/
src/
templates/
translations/ (optional)
vendor/ (composer dependencies)
.env
.htaccess
composer.json
```

Wenn ssh Zugang besteht 
```
compose.yaml
composer.json
composer.lock
```
anstatt `vendor`


WICHTIG:

Webspace Directory muss in den public order zeigen!
![Webspace in All-inkl /elbkantine.impuls-tempus.de/public/](media/subdomain.png)


Admin erstellen mit:
`php bin/console sonata:user:create --super-admin <username> <email> <password>`