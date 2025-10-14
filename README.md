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
```bash
git clone https://github.com/it4impuls/KantineApp_symfony.git
```



`.env` mit 
```.env
APP_ENV=prod
DATABASE_URL="mysql://root@127.0.0.1:3306/{{db_name}}"
```

Die datenbank sollte lokal ohne passwort ansprechbar sein. Falls nicht, müssen die credentials [via secrets gespeichert werden](https://symfony.com/doc/current/configuration/secrets.html#create-or-update-secrets) und in der [config/packages/doctrine.yaml angegeben werden
](https://symfony.com/doc/current/reference/configuration/doctrine.html#doctrine-dbal-configuration), und die `DATABASE_URL` in der .env gelöscht werden.

```yaml
doctrine:
    dbal:
        dbname:               '%env(DATABASE_NAME)%'
        host:                 localhost
        port:                 3306
        user:                 '%env(DATABASE_USER)%'
        password:             '%env(DATABASE_PASSWORD)%'
        driver:               mysql
```


reference: https://symfony.com/doc/current/deployment.html

```bash
composer dump-env prod
APP_RUNTIME_ENV=prod php bin/console secrets:generate-keys
composer install --no-dev --optimize-autoloader
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
```

WICHTIG:

Webspace Directory muss in den /public/ order zeigen!
![Webspace in All-inkl /elbkantine.impuls-tempus.de/public/](media/subdomain.png)

Admin erstellen mit:
```bash
php bin/console sonata:user:create --super-admin {{username}} {{email}} {{password}}
```