# Zeiterfassung

Die Zeiterfassung der Teilnehmer bei der Impuls Reha erfolgt digital.
Jeder Teilnehmer bekommt zu Beginn einen Teilnehmerausweis mit einem Barcode, welcher eindeutig zugeordnet wird.
Verteilt über die Standorte stehen die sogenannten Clienten mit einem Scanner, welche jeden Scan über eine Web-API an dieses Framework senden.
Innerhalb des Frameworks in diesem Repository, werden die Scans heuristisch als Ein- bzw. Auscheckvorgänge erkannt und mit Zeitstempel in einer MariaDB gespeichert.
Die Aufbereitung der Einträge zu einer Anwesenheitsliste erfolgt ebenfalls in diesem Framework.

## Die Clienten

Der Quellcode der Clienten liegt auf einem privatem [Github-Repository](https://github.com/zeiterfassungClients/ClientAPI).
Er ist in Python geschrieben und benutzt die Web-API von diesem Symfony Framework.
Wenn der Client einen Barcode-Scan bemerkt schickt er entweder den Wert des Barcodes direkt an dem Webserver oder speichert diesem mit dem Zeitstempel lokal in einer SQLite Datenbank.
Der letztere Fall wird benutzt, wenn die Web-API nicht erreichbar ist.
Die zwischengespeicherten Werte werden an die Web-API geschickt, sobald die Verbindung wieder steht.

## Die Web-API 

Der Quellcode für die Web-API, mit welcher die Clienten kommunizieren findet man in `apps/zeiterfassung/src/Controller/ScannerPageController.php`.

