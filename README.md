# Updating der Webseite

## Server Daten updaten

nachdem der neue code auf [github.com/it4impuls/KantineApp_symfony/](github.com/it4impuls/KantineApp_symfony/) geladen wurde, führe den [workflow deploy to all-Inkl (Production)](https://github.com/it4impuls/KantineApp_symfony/actions/workflows/Deploy_Prod.yml) aus (run workflow). Dies läd alle wichtigen date auf den Server. Falls sich daten zum Server geändert haben müssen diese in [optionen -> Environments -> dev](https://github.com/it4impuls/KantineApp_symfony/settings/environments) von den Fachanleitern geändert werden.

## Neue composer packages installieren
Falls neue packages installiert wurden, müssen diese auf dem server installiert werden.
Logge dich dafür per ssh auf den server ein und führe folgende commandos aus:
```bash
cd kantine.impuls-reha.de       # Falls nicht schon im Ordner
export APP_ENV=prod
composer install --no-dev --optimize-autoloader
```

## Update DB Zugangsdaten
```bash
cd kantine.impuls-reha.de       # Falls nicht schon im Ordner
export APP_RUNTIME_ENV=prod
php bin/console secrets:set DATABASE_HOST      # addresse zur Datenbank, normalerweise localhost
php bin/console secrets:set DATABASE_NAME      # Name der Datenbank
php bin/console secrets:set DATABASE_PASSWORD  # Passwort des Datenbank-Benutzers
php bin/console secrets:set DATABASE_USER      # Username des Datenbank-Benutzers

# nach Änderungen secrets exportieren (performence optimierung)
php bin/console secrets:decrypt-to-local --force
```

## DB Felder updaten
Falls Felder geändert wurden (Änderungen in /src/Entity/**) muss die Datenbank [migriert](https://symfony.com/doc/current/the-fast-track/en/8-doctrine.html#migrating-the-database) werden. Mit
```bash
php bin/console make:migration
```
Migration erstellen BEVOR der Code auf Github gepusht wird. Dies erstellt eine Datei im `migrations/` Ordner, welche auf den Server geladen werden muss. (siehe "Server Daten updaten")
Dann per ssh auf Server einloggen und 
```bash
cd kantine.impuls-reha.de       # Falls nicht schon im Ordner
php bin/console doctrine:migrations:migrate
```
ausführen

# Server Installation
Schritte für update zuerst ausführen, um danen auf den Server hochzuladen und composer packages installieren.

## Per SSH auf Server einloggen
Vom eigenen PC mit
```
ssh {{ssh-user}}@{{ssh-host}}
```
auf dem Server einloggen. {{...}} mit echten Daten ersetzen.
In den Ordner wechseln
```bash
cd kantine.impuls-reha.de
```


## Secrets hochladen/setzen
Falls Secrets und decrypt key in `/config/secrets/prod/` von einer vorherigen installation vorhanden sind, lade diese auf den Server hoch. Die Dateien haben das Format `prod.{{secret_name}}.{{random_hex}}.php`, sowie `prod.decrypt.private.php`, `prod.encrypt.public.php`, `prod.list.php`.

Falls eines dieser Dateien nicht vorhanden ist, müssen die Secrets [neu generiert werden](https://symfony.com/doc/current/configuration/secrets.html#generate-cryptographic-keys):
```bash
export APP_RUNTIME_ENV=prod 
php bin/console secrets:generate-keys
php bin/console secrets:set APP_SECRET --random
```

DB Zugangsdaten setzen (siehe "Update DB Zugangsdaten").

## Datenbank migration

### Option 1: Bestehende Datenbank importieren
Falls eine Datenbank bereits besteht kann diese einfach importiert werden.

### Option 2: Leere Datenbank erstellen
Um das Datenbank Schema in die Datenbank zu übernehmen, muss
```bash
php bin/console doctrine:migrations:migrate
```
ausgeführt werden.

## All-ink auf den `public` Ordner verweisen
Webspace Directory muss in den public order zeigen!
* In all-inkl mit KAS account anmelden
* Subdomain -> Subdomain -> Edit
in Webspace `public/` hinter dem bestehenden Namen eingeben.
![Webspace in All-inkl /elbkantine.impuls-tempus.de/public/](media/subdomain.png)


## Superuser erstellen

```bash
php bin/console sonata:user:create --super-admin "{{username}}" "{{email}}" "{{password}}"
```
{{ }} mit den tatsächlichen Daten Ersätzen

## Sonstige Accounts erstellen
Nun sollte die Webseite funktionieren. Auf der Admin-Seite [kantine.impuls-reha.de/admin](kantine.impuls-reha.de/admin) Anmelden. Unter `Benutzer -> Benutzer` Neuen Benutzer anlegen.

Wichtig!!! Achte auf die Rollen. Gib neuen Account nur so viele Rechte wie er tatsächlich braucht. (z.B. kantine nur Zugriff auf Bestellungen, TN-Verwaltung nur Zugriff auf Kunden etc. )

Stelle Sicher dass "Aktiviert" angekreutzt ist, damit man sich auch damit anmelden kann.


## Cronjobs
Das löschen alter, inaktiver Teilnehmer läuft in einem cronjob. Dieser muss im all-inkl unter `Tools` -> `cronjobs` eingerichtet werden. https://all-inkl.com/wichtig/anleitungen/kas/tools/cronjobs/einrichtung_479.html:
![all-inkl cron](media/cronjob.png)

Es muss ein Benutzer mit `Costumer delete` privilegien Angelegt werden und dessen login Informationen unter `advanced` abgelegt werden.
![cron user](media/cron_user.png)
