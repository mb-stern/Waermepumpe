# Dokumentierte Fehler

Stand: 20.10.2026 · Branch: `development` · ursprünglicher Fehlerstand F-01/F-02: `7808018eeb6c61658862d23219b135ea09475d94`.

F-01 und F-02 wurden auf dem genannten Stand reproduziert und inzwischen auf `development` korrigiert. Sie betreffen Randfälle der Sollwerteingabe und bedeuten nicht, dass der normale Betrieb grundsätzlich gestört ist. F-03 und F-04 beschreiben die später ergänzte Luxtronik-Klickerkennung. Die ursprünglichen Fundstellen und Reproduktionen bleiben zur Nachvollziehbarkeit erhalten. Eine vollständige Modulprüfung ist damit noch nicht abgeschlossen.

## F-01: Fehlende Validierung numerischer Steuerungswerte im PHP-Modul

**Priorität:** Hoch, da Sollwerte betroffen sind.

**Fundstellen:** `WP/module.php`, `RequestAction()` ab Zeile 183, insbesondere die Typumwandlung ab Zeile 224 und die Weitergabe ab Zeile 243; außerdem `CastValueForVariableType()` ab Zeile 1451.

**Auslöser:** `SetNumericControl` wird für eine gültig konfigurierte numerische Steuervariable mit einem fehlenden, nicht numerischen oder außerhalb des vorgesehenen Eingabebereichs liegenden `value` aufgerufen.

**Ursache:** Das Modul wandelt den Wert vor einer fachlichen Prüfung in den Variablentyp um. Für `SetNumericControl` gibt es keine Prüfung auf einen vorhandenen, gültigen Zahlenwert und keine serverseitige Bereichsprüfung. Die Assoziationsprüfung im selben Codepfad gilt ausschließlich für `SetControlMode`. Eine Begrenzung im Browser schützt direkte Aktionsaufrufe nicht.

**Reproduktion:** Der vorhandene Symcon-Ersatz aus `tests/regression.php` wurde verwendet. Variable 42 wurde als Float-Sollwert mit Ausgangswert 45 angelegt und `WarmWaterSetpointVariable` darauf gesetzt. Es wurden ausschließlich simulierte Variablen beschrieben.

| Aufruf von `RequestAction('SetNumericControl', …)` | Tatsächlich geschriebener Wert |
| --- | --- |
| `['function' => 'warmWaterSetpoint', 'value' => 9999]` | `9999.0` |
| `['function' => 'warmWaterSetpoint', 'value' => 'invalid']` | `0.0` |
| `['function' => 'warmWaterSetpoint']` | `0.0` |

**Auswirkung:** Fehlerhafte Eingaben können unbeabsichtigte Sollwerte erzeugen. Ohne Variablenaktion schreibt das Modul direkt mit `SetValue`; mit einer Aktion gibt es den konvertierten Wert an `RequestAction` weiter. Ob eine nachgeschaltete Geräteintegration unzulässige Werte zurückweist, wurde nicht geprüft. Der Test belegt keine Änderung an einer echten Wärmepumpe.

**Status: korrigiert.** Vor der Typumwandlung werden der Eingabetyp, ein gültiger endlicher Zahlenwert und der zur Zielvariable passende Typ geprüft. Fehlende Werte, Boolean-Werte, nicht numerischer Text und nicht ganzzahlige Werte für Integer-Variablen werden abgewiesen. Numerische Strings bleiben zulässig. Ein gemeinsamer Helfer liefert die Profil- bzw. Standardgrenzen für Bedienoberfläche und serverseitige Bereichsprüfung. Ungültige Werte lösen vor jedem Schreib- oder Aktionsaufruf eine verständliche Fehlermeldung aus.

**Abnahme:** Fehlender Wert, ungültiger Text und Werte außerhalb des zulässigen Bereichs führen zu keinem Schreib-/Aktionsaufruf. Gültige Werte einschließlich der Grenzen bleiben bedienbar. Beide numerischen Steuerfunktionen und beide Weitergabewege sind zu prüfen.

## F-02: Rundung kann die numerischen Eingabegrenzen überschreiten

**Priorität:** Mittel; abhängig von Bereich und Schrittweite des verwendeten Profils.

**Fundstellen:** `WP/module.php`, JavaScript-Funktion `clamp()` ab Zeile 3371; Verwendung beim Übernehmen ab Zeile 3407.

**Auslöser:** Eine Eingabegrenze liegt nicht auf dem durch die Rundung verwendeten Schrittraster.

**Ursache:** `clamp()` begrenzt zunächst auf Minimum/Maximum und rundet danach mit `Math.round(result / step) * step`. Die Rundung kann das Ergebnis wieder außerhalb des Bereichs verschieben. Das Schrittraster orientiert sich außerdem an null, unabhängig vom konfigurierten Minimum.

**Reproduktion:** Die Funktion wurde direkt aus `WP/module.php` extrahiert und mit Node ausgeführt:

| Minimum | Maximum | Schrittweite | Eingabe | Ergebnis |
| --- | --- | --- | --- | --- |
| 20 | 80 | 0,3 | 80 | **80,1** |

**Auswirkung:** Ein außerhalb des angegebenen Maximums liegender Wert kann an `SetNumericControl` übergeben werden. Das `max`-Attribut des Eingabefeldes verhindert diesen JavaScript-Aufruf nicht. Zusammen mit F-01 fehlt an beiden Stellen eine verlässliche Bereichssicherung. Bei passenden Rastern, etwa 20–80 mit Schrittweite 0,5, tritt dieses Beispiel nicht auf.

**Status: korrigiert.** Das Minimum ist jetzt ausdrücklich der Rasterursprung, entsprechend dem `min`-Attribut des Eingabefelds. Die Rundung wählt einen Rasterpunkt innerhalb des zulässigen Bereichs und sichert die Grenzen abschließend gegen Gleitkommaabweichungen ab. Nicht zum Raster passende Obergrenzen ergeben den höchsten zulässigen Rasterpunkt. Das ursprüngliche Beispiel liefert nun `80` statt `80,1`; bei Minimum `20,1`, Maximum `80` und Schrittweite `0,3` wird `79,8` gewählt. Leere Eingaben führen beim Übernehmen zu keinem Aktionsaufruf. Die serverseitige Validierung aus F-01 gilt zusätzlich; direkte API-Aufrufe werden auf Typ und Bereich geprüft, ohne sie stillschweigend auf das Bedienraster zu runden.

**Abnahme:** Nicht am Raster liegende Unter- und Obergrenzen sowie negative Bereiche prüfen. Kein Ergebnis darf Minimum oder Maximum verletzen; das vereinbarte Schrittraster muss eingehalten werden. Bestehende Standardprofile müssen weiterhin funktionieren.

## F-03: Feste Pixelposition verhindert die Erkennung von Luxtronik-Menüzeilen

**Priorität:** Mittel; die neue direkte Mausbedienung ist betroffen.

**Fehlerstand:** `5adf27f4ecd21224e86085309c2be7870181e8b6`.

**Fundstellen:** `WP/luxtronik-display.js`, `LuxtronikDisplay.analyze()`; `WP/module.php`, Pointer-Behandlung des LCD-Canvas.

**Nutzerrückmeldung:** Es erscheinen keine gelben Umrandungen und Mausklicks bewirken nichts. Das zeigt, dass keine Klickflächen angeboten werden. Die exakten LCD-Bilddaten des betroffenen Geräts liegen noch nicht als Datei vor; die folgende lokale Reproduktion belegt einen konkreten Fehler, bestätigt aber noch nicht die vollständige Ursache am Regler.

**Reproduktion:** Gleiches synthetisches Menübild, gleicher Auswahlbalken und gleiche Schrift; ausschließlich der horizontale Ursprung wird verschoben. Bei x=18 werden sieben Klickflächen erkannt, bei x=19 und x=20 keine. Ein Pixel Abweichung genügt. Die bisherigen Testbilder verwendeten den erwarteten Ursprung und deckten diesen Unterschied nicht ab.

**Ursache:** Die Auswahlprüfung und die Zeilenbereiche waren auf x=18 und eine feste rechte Kante zugeschnitten. Zugleich wurde der ursprüngliche Displayklick als Enter entfernt. Ohne erkannte Flächen gab es dadurch keine Mausbedienung auf dem Display.

**Status: lokal korrigiert, Abnahme am Regler offen.** Die Balkenkanten werden jetzt aus dem Bild bestimmt. Varianten mit leicht asymmetrischen Pfeilkonturen werden berücksichtigt. Eine eigene Anzeige nennt die Anzahl anklickbarer Elemente bzw. den Ersatzbetrieb. Ohne erkannte Flächen bestätigt ein Displayklick wieder die aktuelle Auswahl mit Enter; ein Doppelklickschutz verhindert eine sofortige Bestätigung der nächsten Ansicht nach einem automatischen Öffnen. Der neue PNG-Export speichert das unveränderte LCD-Bild für die Prüfung tatsächlicher Firmware-Layouts.

**Abnahme:** Menübilder mit x=17/18/19/20/22 und Zeilenhöhen 9/10 prüfen, direkte Klickabläufe auch mit x=19 und Zeilenhöhe 10 ausführen, Pfeilvarianten und unverändert blockierte mehrdeutige Bilder prüfen. Bei unbekanntem Bild muss ein manueller Displayklick genau Drücken und Loslassen übertragen. PNG-Export muss 160 × 64 Pixel haben; Verbindungsabbruch und Schließen müssen Export und Bedienung sperren. Die echten LCD-Daten und die gelben Klickflächen am Regler sind noch zu prüfen.

## F-04: Verschwindende Auswahlmarkierung entfernt Klickflächen zwischen Displayupdates

**Priorität:** Mittel; direkte Displayklicks reagieren unregelmäßig oder fallen vorübergehend auf Enter zurück.

**Fehlerstand:** `1a16e6012e828de9bc35a571f4875f326fe295dd`.

**Nutzerrückmeldung:** Die gelbe Umrandung erscheint nur teilweise. Anschließend sieht die Anzeige gleich aus, die Umrandung fehlt aber und ein Klick wirkt anders. Ein Mitschnitt der tatsächlichen Reglerbilder liegt weiterhin nicht vor.

**Lokale Reproduktion:** Dasselbe Menü wird mit sichtbarer Auswahlmarkierung, ohne Markierung und wieder mit Markierung übertragen. Vor der Korrektur ergeben diese Phasen sechs, null und sechs Klickflächen. Der Code erkennt das unveränderte Menü ohne aktuelle Auswahl nicht wieder und ersetzt die Klickfläche vollständig. Ein zwischen Maustaste drücken und loslassen eintreffendes Bild kann dadurch den ursprünglichen Button entfernen. Der Enter-Ersatzbetrieb ändert zusätzlich die Bedeutung desselben Displayklicks.

**Status: lokal korrigiert, Prüfung am Gerät offen.** Bei identischer Überschrift und identischen Zeilenmustern bleiben bekannte Flächen über die Blinkphase bestehen. Ohne bestätigte Auswahl wird kein Bedienbefehl gesendet; der gewünschte Klick wartet begrenzt auf die nächste erkennbare Markierung. Ein stabiler Container fängt Mausdrücken und Loslassen ab und prüft beim Loslassen Ziel, Position und Menüstruktur erneut. Ein anderes Menü, ein manuelles Bedienereignis, Ziehen aus der Fläche, Verbindungsabbruch und Schließen verwerfen den vorgemerkten Klick. Nach Enter zählt ein Cursorblinken nicht mehr als abgeschlossener Ansichtswechsel.

**Abnahme:** Wechselbilder mit und ohne Markierung ergeben gleichbleibende Menü-/Symbolflächen. Während einer gedrückt gehaltenen Maustaste bleibt die Fläche erhalten; Loslassen ohne Markierung sendet noch keinen Befehl. Nach bestätigter Auswahl wird das vorgemerkte Ziel genau einmal geöffnet. Zusätzliche Blinkbilder zwischen Navigationsschritten dürfen weder Abbruch noch doppelte Befehle auslösen. Eine neue Menüüberschrift während des Klicks und Loslassen außerhalb der Fläche dürfen nichts öffnen. Blinkbilder nach Enter dürfen keine erneute Bestätigung freigeben. Diese Fälle bestehen lokal mit simuliertem WebSocket bei 1200 und 390 Pixel Breite; 964 PHP-Regressionprüfungen bleiben erfolgreich. Die Übereinstimmung mit der tatsächlichen Blinkdarstellung des Reglers ist noch zu bestätigen.

## Prüfstatus und Quellen für F-01/F-02

- PHP-Syntaxprüfung auf `development`: erfolgreich.
- PHP-Regressionstests nach der Korrektur: **724 Prüfungen erfolgreich**, mit lokalem Symcon-Ersatz. Für diesen Lauf wurde die lokal installierte PHP-DOM-Erweiterung ausdrücklich geladen.
- Die neuen Regressionstests prüfen ungültige Eingaben ohne Seiteneffekte, beide numerischen Steuerfunktionen, direkte Schreibzugriffe und Standard-/benutzerdefinierte Aktionen, Profilgrenzen sowie die Typprüfung. Die ursprünglichen 601 Prüfungen sind darin enthalten.
- Browserregression mit Chromium: erfolgreich bei 1200 und 390 Pixel Ansichtsbreite. Die tatsächlichen Sollwertmenüs wurden für beide Steuerfunktionen mit Standard-, verschobenen und negativen Rastern sowie leeren Eingaben geprüft. Aktionsaufrufe wurden ausschließlich lokal aufgezeichnet.
- Keine echten Geräteaktionen und keine Prüfung gegen eine laufende IP-Symcon-Instanz.
- [Offizieller Symcon-Dokumentationsindex](https://www.symcon.de/de/llms.txt) und [LoxWiki: Java Webinterface](https://loxwiki.atlassian.net/wiki/spaces/LOX/pages/1533935933/Java+Webinterface) waren bei der letzten Zugriffsprüfung mit HTTP 200 erreichbar; die Inhalte wurden geladen. Für die Korrektur wurden zusätzlich die offiziellen Dokumentationen zu [SDK RequestAction](https://www.symcon.de/de/llms/developer/sdk-tools/sdk-php/module/requestaction.md), [Variablenzugriff](https://www.symcon.de/de/llms/functions/access-variables.md) und [Variablenverwaltung einschließlich Profilen](https://www.symcon.de/de/llms/functions/management-variables.md) herangezogen. Das Luxtronik-Protokoll wurde durch diese Korrektur nicht verändert oder vollständig geprüft.

Weitere Code-, Dokumentations- und Teständerungen erfolgen ausschließlich auf `development`; `main` bleibt die unveränderte Kompatibilitätsreferenz.
