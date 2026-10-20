# Dokumentierte Fehler

Stand: 20.10.2026 · Branch: `development` · ursprünglicher Fehlerstand: `7808018eeb6c61658862d23219b135ea09475d94`.

F-01 und F-02 wurden auf dem genannten ursprünglichen Stand reproduziert und inzwischen auf `development` korrigiert. F-03 wurde auf dem späteren Stand `21a623d` ergänzt. Die ursprünglichen beiden Befunde betreffen Randfälle der Sollwerteingabe und bedeuten nicht, dass der normale Betrieb grundsätzlich gestört ist. Die ursprünglichen Fundstellen und Reproduktionen bleiben zur Nachvollziehbarkeit erhalten. Eine vollständige Modulprüfung ist damit noch nicht abgeschlossen.

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

## F-03: Zwei vorhandene Luxtronik-Idents fehlen in der Zuordnungstabelle

**Gemeldetes Verhalten:** Verdichterdrehzahl und Kondensationstemperatur bleiben trotz korrekt ausgewählter Luxtronik-Quelle und vieler anderer erfolgreicher Zuordnungen unzugeordnet.

**Bestätigte Ursache:** Die vom Nutzer bereitgestellte Diagnose des geladenen Stands `einheitlich-2026-10-20` findet für `Verdichterdrehzahl` und `Kondensationstemperatur` jeweils keine Variable. Die tatsächlichen direkten Untervariablen und die anschließenden Screenshots zeigen stattdessen die Idents **`Verdichterfrequenz`** und **`Liquefaction_Temperature`**. `Verdichterfrequenz_Soll` existiert zusätzlich und ist ein Sollwert. Quelle und geladener Codestand stimmen; die Instanz-ID ist nicht die Ursache. Die vorherige Annahme eines Problems beim Übernehmen leerer Formularfelder erklärte diesen Fall nicht.

**Korrektur:** Die vorhandene zentrale Ident-Tabelle enthält nun `Verdichterdrehzahl` / `Verdichterfrequenz` und `Kondensationstemperatur` / `Liquefaction_Temperature`. Es wird derselbe gemeinsame Resolver wie für alle anderen Idents verwendet. Originale Idents behalten Vorrang, wenn beide Varianten verfügbar sind. `Verdichterfrequenz_Soll` wird nicht als Istwert verwendet. Profilwerte werden unverändert angezeigt; für die Frequenzanimation wird eine passende schematische Skala verwendet, ohne eine physikalische Drehzahlumrechnung zu behaupten.

**Prüfung:** Regressionen bilden die tatsächlich gemeldeten Ident-Namen unter einer beliebigen Testinstanz nach, prüfen die ausgewählten IDs, Werte, Abonnements, grauen Picker und die Zuordnungsübersicht. Sie prüfen ausdrücklich einen zusätzlich vorhandenen Sollwert sowie den Fall, dass nur der Sollwert existiert. Browserprüfungen verwenden daraus erzeugte PHP-Daten und Variablenmeldungen für Anzeige und Frequenzanimation. Die Bestätigung nach dem Update auf der echten Installation steht aus.

## F-04: Automatisch erkannte Variablen wurden als Text statt als graue Picker dargestellt

**Ursache:** Die Formularänderungen ab `1743f1d` blendeten die ursprünglichen Picker bei einer automatischen Zuordnung aus und ersetzten sie durch Quellenbeschriftungen. Dadurch fehlten die ausdrücklich gewünschten grauen Variablen-Picker.

**Korrektur:** Automatisch erkannte Variablen werden wieder als native `SelectVariable`-Felder mit aufgelöster ID, `visible: true` und `enabled: false` dargestellt. Diese Anzeige-Picker besitzen keine registrierte Konfigurationseigenschaft; ihr expliziter Wert wird daher nicht durch eine leere gespeicherte manuelle ID überschrieben. Die ursprünglichen Eigenschaften und manuellen Picker bleiben erhalten und werden mit **Manuelle Konfiguration** wieder verwendet. Dieselbe Darstellung gilt für alle Einträge der Ident-Tabelle.

**Prüfung:** Regressionen prüfen Feldtyp, Sichtbarkeit, ausgewählte ID, deaktivierten Zustand und den manuellen Modus. Sie prüfen außerdem, dass Anzeige-Picker keine Eigenschaften registrieren oder gespeicherte manuelle Zuordnungen verändern. Die Symcon-Ersatztests prüfen die erzeugte Formularstruktur; sie ersetzen keine Sichtprüfung in einer echten Symcon-Konsole.

## Prüfstatus und Quellen

- PHP-Syntaxprüfung auf `development`: erfolgreich.
- PHP-Regressionstests nach der Korrektur: **724 Prüfungen erfolgreich**, mit lokalem Symcon-Ersatz. Für diesen Lauf wurde die lokal installierte PHP-DOM-Erweiterung ausdrücklich geladen.
- Die neuen Regressionstests prüfen ungültige Eingaben ohne Seiteneffekte, beide numerischen Steuerfunktionen, direkte Schreibzugriffe und Standard-/benutzerdefinierte Aktionen, Profilgrenzen sowie die Typprüfung. Die ursprünglichen 601 Prüfungen sind darin enthalten.
- Browserregression mit Chromium: erfolgreich bei 1200 und 390 Pixel Ansichtsbreite. Die tatsächlichen Sollwertmenüs wurden für beide Steuerfunktionen mit Standard-, verschobenen und negativen Rastern sowie leeren Eingaben geprüft. Aktionsaufrufe wurden ausschließlich lokal aufgezeichnet.
- Keine echten Geräteaktionen und keine Prüfung gegen eine laufende IP-Symcon-Instanz.
- [Offizieller Symcon-Dokumentationsindex](https://www.symcon.de/de/llms.txt) und [LoxWiki: Java Webinterface](https://loxwiki.atlassian.net/wiki/spaces/LOX/pages/1533935933/Java+Webinterface) waren bei der letzten Zugriffsprüfung mit HTTP 200 erreichbar; die Inhalte wurden geladen. Für die Korrektur wurden zusätzlich die offiziellen Dokumentationen zu [SDK RequestAction](https://www.symcon.de/de/llms/developer/sdk-tools/sdk-php/module/requestaction.md), [Variablenzugriff](https://www.symcon.de/de/llms/functions/access-variables.md) und [Variablenverwaltung einschließlich Profilen](https://www.symcon.de/de/llms/functions/management-variables.md) herangezogen. Das Luxtronik-Protokoll wurde durch diese Korrektur nicht verändert oder vollständig geprüft.

Weitere Code-, Dokumentations- und Teständerungen erfolgen ausschließlich auf `development`; `main` bleibt die unveränderte Kompatibilitätsreferenz.
