# Auf Server laden ohne ssh-zugang

Ohne ssh zugang können composer-dependencies nicht automatisch installiert werden können, müssen diese manuell via ftp hochgeladen werden.

Zu hochladenden Dateien/Ordner ohne ssh:
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
git clone {{repository}}
```

reference: https://symfony.com/doc/current/deployment.html

`.env` mit 
```
APP_ENV=prod
APP_SECRET={{secret}}
DATABASE_URL="mysql://root@127.0.0.1:3306/{{db_name}}"
```

`composer dump-env prod`

`composer install --no-dev --optimize-autoloader`

`APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear`

WICHTIG:

Webspace Directory muss in den public order zeigen!
![Webspace in All-inkl /elbkantine.impuls-tempus.de/public/](media/subdomain.png)


Admin erstellen mit:
`php bin/console sonata:user:create --super-admin <username> <email> <password>`