# Vertrauenswürdige Browser in u5CMS

Ab 12.9.5.10 kann eine u5CMS-Installation ausgewählten Benutzerkonten eine festgelegte Anzahl vertrauenswürdiger Browser erlauben. Ein registrierter Browser kann die Anmeldung nach Ablauf der gewöhnlichen CMS-Sitzung wieder aufnehmen. Er behält die bestehenden Berechtigungen des angemeldeten Kontos.

## config.php anpassen

Die bestehende produktive `config.php` bearbeiten. Die folgenden Einstellungen einmal aufnehmen beziehungsweise vorhandene Einstellungen ersetzen:

```php
$u5trusteddeviceenabled = 'yes';
$u5trusteddeviceusers = array(
    'alice@example.org' => 3,
    'bob@example.org' => 2,
);
```

Die Schlüssel sind die genauen CMS-Anmeldenamen, die Zahlen die maximal gleichzeitig registrierten Browser pro Konto und CMS-Installation. Die Beispieladressen durch die tatsächlichen Konten ersetzen. Dieselbe E-Mail-Adresse allein ersetzt keine Anmeldung und verleiht keine neuen CMS-Rechte.

| Einstellung | Wirkung |
| --- | --- |
| `'alice@example.org' => 3` | Dieses Konto darf bis zu drei Browser registrieren. |
| `'bob@example.org' => 2` | Dieses Konto darf bis zu zwei Browser registrieren. |
| Konto fehlt oder Wert `0` | Keine Registrierung und keine Wiederaufnahme über Browservertrauen. |
| `$u5trusteddeviceenabled = 'no';` | Browservertrauen für die gesamte Installation deaktivieren. |

Die Grenzen als ganze Zahlen ohne Anführungszeichen schreiben. Negative Werte, Dezimalzahlen, Zeichenketten und boolesche Werte erteilen keine Freigabe. Eine bisherige Liste wie `array('alice@example.org')` bleibt kompatibel und erlaubt einen Browser je aufgeführtem Konto.

Eine höhere Grenze schafft zusätzliche freie Plätze; bestehende Browser bleiben registriert. Bei einer Senkung behalten die Browser innerhalb der verbleibenden nummerierten Plätze ihr Vertrauen. Browser auf höheren Plätzen werden bei der nächsten Verwendung abgewiesen und beim Anzeigen oder Verwenden widerrufen. Deshalb vor einer Senkung die Browserliste prüfen und gezielt nicht mehr benötigte Profile widerrufen.

## Browser registrieren

1. Im gewünschten Browser regulär am betreffenden CMS anmelden, bei AGOV-Anbindung also mit AGOV.
2. Auf genau diesem Host `/u5admin/trusteddevice.php` öffnen.
3. Die angezeigte Belegung und das Konto prüfen.
4. **Trust this browser** wählen. Das benötigt einen freien Platz im konfigurierten Kontingent.
5. Für ein weiteres Browserprofil oder ein weiteres CMS wiederholen.

Ein bereits registrierter Browser belegt durch erneutes Klicken keinen weiteren Platz. Andere Browser werden nicht automatisch registriert. Ist die Grenze erreicht, muss zuerst ein bestehender Browser widerrufen werden.

## Browser widerrufen oder ersetzen

Nach einer normalen Anmeldung zeigt dieselbe Seite die registrierten Browser des eigenen Kontos mit Platznummer, Registrierungszeit und letzter Verwendung in UTC. Der aktuelle Browser ist gekennzeichnet. Mit **Revoke** lässt sich jeder eigene Eintrag einzeln widerrufen. Damit kann auch ein verlorenes Cloudbrowserprofil entfernt werden; anschliessend den neuen Browser registrieren.

Eine bewusste Abmeldung über die CMS-Abmeldefunktion widerruft das Vertrauen des aktuellen Browsers. Andere registrierte Browser bleiben erhalten. Eine Änderung des gespeicherten Kontogeheimnisses entwertet die daran gebundenen Browser ebenfalls. Der Widerruf beendet die dauerhafte Wiederaufnahme; eine bereits bestehende gewöhnliche Sitzung folgt weiterhin ihrer normalen Laufzeit.

## Cloudbrowser von mehreren Personen

Stefan und Markus verwenden vorzugsweise ihre eigenen CMS-Konten. Beide Konten können unabhängige Grenzen haben. Auch dasselbe Konto kann innerhalb seines Kontingents mehrere Browser registrieren; es arbeitet dann überall mit denselben Kontoberechtigungen.

Das CMS erkennt den geheimen Cookie im registrierten Browserprofil. Es kann nicht feststellen, ob vor dem Browser ChatGPT oder eine andere Person sitzt. Browserprofile und Cookies dürfen deshalb nicht weitergegeben oder zwischen Profilen kopiert werden. Die Freigabe bleibt nur erhalten, solange das jeweilige Cloudbrowserprofil seinen Cookie behält. Ein neues Chatfenster allein garantiert weder dessen Erhalt noch dessen Verlust.

## Laufzeit und technische Voraussetzungen

Die Funktion benötigt HTTPS und die sitzungsbasierte u5CMS-Anmeldung. Bei aktiviertem OIDC und bei HTTP Basic Auth bleibt sie ausgeschaltet. Jede CMS-Installation wird separat konfiguriert und jeder Host separat registriert. Es gibt keinen gemeinsamen Cookie für alle Websites.

Der geheime Browser-Cookie ist hostgebunden, `Secure`, `HttpOnly` und `SameSite=Strict`. Der Server speichert den SHA-256-Prüfwert des Geheimnisses. Der Cookie gilt bis zu 400 Tage und wird bei regelmässiger Nutzung höchstens einmal pro Tag erneuert. Das ist keine Garantie für ein unbegrenzt bestehendes Cloudbrowserprofil.

Die Datenbanktabelle wird bei der Registrierung automatisch angelegt. Eine vorhandene Tabelle für bisher einen Browser je Konto wird dabei unter Erhalt der bestehenden Tokens angepasst. Ein Cronjob ist nicht erforderlich.

## Manueller Rollout nur dieser Funktion

Die beiden Produktivdateien `trusteddevice.inc.php` und `u5admin/trusteddevice.php` sowie die passenden Anschlussstellen in `login.inc.php`, `u5admin/usercheck.inc.php` und `logout.php` installieren. Die vorhandene `config.php` direkt bearbeiten und alle anderen Einstellungen erhalten. Dabei die vorhandenen OIDC-Prüfungen beibehalten. Ein vollständiger CMS-Versionswechsel ist für diesen gezielten Rollout nicht erforderlich. Zusätzliche Skripte, Backupkopien oder temporäre Deployment-Dateien müssen auf dem Zielserver nicht verbleiben.

Anschliessend eine normale Anmeldung, die Browserregistrierung, die Wiederaufnahme nach Ablauf der gewöhnlichen Sitzung, die Kontingentgrenze und den gezielten Widerruf prüfen. Alle dauerhaft benötigten Dateien gehören zum Produktivcode; keine Hilfsendpunkte zur Registrierung oder zur Remoteausführung hinzufügen.
