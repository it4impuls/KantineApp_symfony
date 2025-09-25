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
dev.env
.htaccess
```

WICHTIG:

Webspace Directory muss in den public order zeigen!
![Webspace in All-inkl /elbkantine.impuls-tempus.de/public/](media/subdomain.png)