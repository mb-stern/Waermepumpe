# Wärmepumpe für IP-Symcon

Visualisierung einer Wärmepumpe in IP-Symcon.

Das Modul stellt den kompletten Wärmepumpenprozess grafisch dar und verbindet die Visualisierung direkt mit Variablen aus IP-Symcon. Es ist nicht auf einen bestimmten Wärmepumpenhersteller beschränkt und kann mit beliebigen Datenquellen verwendet werden, sofern die entsprechenden Werte in IP-Symcon vorhanden sind.

Die Konfiguration erfolgt vollständig über das IP-Symcon-Konfigurationsformular.

## Luxtronik (optional)

Im Picker **Luxtronik (optional)** die gewünschte Instanz auswählen. Passende Variablen werden über ihre Idents zugeordnet und direkt in den Variablenpickern angezeigt. Felder ohne passenden Ident bleiben frei wählbar.

Bei **Keine** sind alle Variablen frei wählbar, auch wenn nur eine Luxtronik-Instanz installiert ist. Gespeicherte manuelle Zuordnungen bleiben erhalten und werden nach dem Entfernen der Luxtronik-Auswahl wieder verwendet. Änderungen wie gewohnt mit **Übernehmen** speichern.

### LUX Bedienfeld: Geräteanzeige und Fernsteuerung

Die bisherige funktionslose Kachel **Einstellungen** wird durch **LUX Bedienfeld** ersetzt, wenn eine Webadresse verfügbar ist. Die Adresse wird aus `IPAddress` der ausgewählten Luxtronik-Instanz abgeleitet: `http://<Adresse>/Webserver/index.html`. Ohne ausgewählte Quelle wird keine Instanz automatisch verwendet. Der Datenport des Luxtronik-Moduls (8888/8889) wird nicht als Webserver-Port übernommen.

Eine bereits gespeicherte abweichende Webadresse bleibt erhalten. Ohne gültige Adresse bleibt die Kachel ausgeblendet.

Erst ein Klick öffnet eine Verbindung vom Browser zum Regler auf **WebSocket-Port 8214**, mit dem Unterprotokoll `Lux_WS`. Das Bedienfeld verwendet denselben Ablauf wie die bereitgestellte `Lux.js`: `LOGIN`, regelmäßiges `REFRESH` und `GET` mit der aktuell übermittelten Menü-ID der **Fernsteuerung**. Es werden keine festen Menü-IDs verwendet. Die originalen LCD-Bilddaten werden direkt dargestellt; die dekorative Geräteabbildung mit Drehrad wird nicht nachgebaut.

Die Anmeldung erfolgt automatisch mit dem vorbelegten Standardpasswort. Im Bedienfenster lässt es sich für die aktuelle Sitzung ändern und mit **Erneut verbinden** verwenden. Es wird weder als Moduleigenschaft gespeichert noch an das PHP-Modul übertragen. Beim regulären Schließen wird das Eingabefeld auf den Standardwert zurückgesetzt.

Das Display mit **Pfeiltasten** und **Enter/Leertaste** bedienen oder die Pfeil- und Enter-Schaltflächen anklicken. Ein Mausklick auf das Display entspricht Drücken und Loslassen; auch das Mausrad funktioniert entsprechend der Originalsoftware. Die Befehle sind `MOVE;1` (links/oben), `MOVE;0` (rechts/unten), `MOVE;2` (drücken) und `MOVE;6` (loslassen). Beim Öffnen werden keine Bedienbefehle gesendet. Die Tasten bleiben gesperrt, bis gültige Displaydaten vorliegen.

Während das Fenster offen ist, wird jede Sekunde `REFRESH` gesendet. Leere `Values`-Antworten gelten als Lebenszeichen eines unveränderten Displays. Nach einem Verbindungsabbruch oder 15 Sekunden ohne Antwort werden die Tasten gesperrt und bis zu drei Wiederverbindungen mit 1, 3 und 10 Sekunden Abstand versucht. Nach 30 Sekunden erfolgreicher Kommunikation beginnt diese Zählung neu. Hintergrundseiten warten gegebenenfalls bis zur erneuten Sichtbarkeit. Eine Wiederverbindung wiederholt keine alten Tastendrücke. Bei ungültigen Daten, fehlender Fernsteuerung oder einer Anmeldung ohne Displayantwort wird ein Hinweis angezeigt; eine manuelle Wiederverbindung bleibt möglich. Schließen beendet Verbindung, Abfragen und geplante Wiederverbindungen.

**Originalinterface öffnen** öffnet die Webadresse in einem neuen Tab. Diese Alternative bleibt bei Verbindungsproblemen verfügbar. Das bereitgestellte `Lux.js` verwendet einen unverschlüsselten WebSocket; aus einer HTTPS-Visualisierung wird deshalb auf das separat geöffnete Originalinterface verwiesen. Eine abweichende HTTPS-Webadresse rüstet den WebSocket-Port nicht automatisch auf TLS um. Der Browser muss den Regler im lokalen Netz erreichen können; der Datenport des Symcon-Luxtronik-Moduls wird für die Fernsteuerung nicht verwendet.

Die Adresse wird über die dokumentierte Symcon-Funktion `IPS_GetProperty` aus der übernommenen Instanzkonfiguration gelesen. Lokale Tests prüfen die Adressauflösung, das LCD-Format, dynamische Navigation, Tastatur-/Mausbedienung, Verbindungsabbruch und den separaten Tab mit simulierten Gegenstellen. Das reale Gerät und die Ausführung innerhalb der echten Symcon-Visualisierung sind noch nicht geprüft; insbesondere kann der Regler Verbindungen aus einer anderen Browser-Origin ablehnen.

### Automatisch zuordenbare Felder

Die folgende Zuordnung wurde gegen `mb-stern/Luxtronik`, Branch `development`, Commit `1fa55a2fd6bca7e02566e01fa975d1cdad7383c3` geprüft. Bei mehreren angegebenen Idents wird der erste vorhandene verwendet. Die Werte sind im Luxtronik-Modul bereits umgerechnet; hier erfolgt keine zusätzliche Skalierung.

| Feld in der Visualisierung | Luxtronik-Ident |
| --- | --- |
| Betriebsstatus | `Betriebszustand` (Java-ID 80, nicht SEC-Board-Zustand 191) |
| Betriebsart Heizen / Warmwasser / Kühlen | `Mode_Heizung` / `Mode_WW` / `Mode_Kuehlung` |
| Warmwasser-Solltemperatur, bedienbar | `Anpassung_WW` |
| Heiztemperaturkorrektur, bedienbar | `Anpassung_Temp` |
| Außentemperatur | `Aussentemperatur` |
| Raumtemperatur Soll | `Raumtemperatur_Sollwert`, alternativ `Desired_Room_Temperature` |
| Raumtemperatur Ist | `Raumtemperatur_Istwert`, alternativ `Raumtemperatur_Raumstation_1` |
| WP Vorlauf und Heizkreis 1 Vorlauf | `Vorlauftemperatur_Heizkreis` |
| Heizkreis 1 Rücklauf Ist | `Ruecklauftemperatur_Heizkreis` |
| Rücklauf Soll, nur Anzeige | `Ruecklauf_Soll_Heizkreis` |
| Lüfterdrehzahl / Lüfter aktiv | `Ventilator_Drehzahl` / `Ausgang_Solepumpe_Ventilator` |
| Verdichter aktiv / Drehzahl oder Frequenz | `Ausgang_Verdichter_1` / `Verdichterdrehzahl`, alternativ `Verdichterfrequenz` (Istwert) |
| Leistung | `Waermeleistung` (thermisch, kW) |
| Niederdruck / Verdampfungstemperatur | `Niederdruck` / `Vapourisation_Temperature` |
| Hochdruck / Kondensationstemperatur | `Hochdruck` / `Kondensationstemperatur`, alternativ `Liquefaction_Temperature` |
| Heizkreis 1 Pumpe / Pumpenwert | `Ausgang_Heizungsumwaelzpumpe` / `Steuersignal_Umwaelzpumpe` |
| Warmwasserspeicher oben | `Temperatur_Brauchwasser_Oben` |
| Brauchwasser Mitte | `Warmwasser_Ist_Temperatur` |
| Warmwasser-Umschaltventil und Speicherladepumpe | `Ausgang_Brauchwasserpumpe_Umstellventil` |
| Zirkulationspumpe | `Ausgang_Zirkulationspumpe` |
| Solar-Kollektortemperatur | `Fuehler_Solarkollektor` |

Das Verdichtersymbol dreht abhängig vom Rohwert der **Verdichterdrehzahl**: höhere Drehzahl bedeutet schnellere Animation. Die Bewegung ist zur besseren Lesbarkeit verlangsamt und begrenzt; Drehzahl und Frequenz verwenden passende schematische Animationsskalen. Zahlenwert und Profilformat bleiben unverändert; eine Frequenz wird nicht in U/min umgerechnet. Drehzahl null oder ein ausgeschalteter **Verdichter aktiv** stoppen das Symbol und die zugehörigen Flusspunkte. Ist keine nutzbare Drehzahl vorhanden, steuert **Verdichter aktiv** die bisherige feste Animation. Ohne separates Aktivsignal reicht eine positive Drehzahl. Bei reduzierter Bewegung bleibt das Symbol still. Die **Kondensationstemperatur** wird mit dem bereits vom Luxtronik-Modul umgerechneten Wert am Kondensator angezeigt.

Luxtronik legt nur aktivierte Datenpunkte und Bedienvariablen an. Fehlende Idents werden nicht anhand ähnlich klingender Namen oder beliebiger Nummern geraten. Benötigte Datenpunkte bzw. Bedienvariablen müssen zuerst im Luxtronik-Modul aktiviert werden. Steuerfelder werden nur automatisch zugeordnet, wenn die gefundene Variable numerisch ist und eine Standard- oder benutzerdefinierte Aktion besitzt; reine Ist- oder Sollwertmessungen werden dadurch nicht zu Stellbefehlen.

Die physische Zuordnung zusätzlicher Mischkreise, einzelner Heizstäbe und weiterer Speicherfühler ist anlagenabhängig. Für diese Felder sowie für die Expansionsventil-Öffnung und freie Zusatzwerte gibt es hier keine automatische Zuordnung. Sie bleiben manuell konfigurierbar. Anlagenkomponenten wie Speicher, Heizkreistypen und Anzahl der Heizstäbe werden ebenfalls nicht automatisch geändert. Die gemeinsame Luxtronik-Ausgabe „Brauchwasserpumpe/Umstellventil“ und die Zuordnung des ungemischten Hauptkreises zu Heizkreis 1 müssen zur tatsächlichen Hydraulik passen.

Die lokalen Regressionstests prüfen Erkennung, Auswahl mehrerer Instanzen, Idents, externe Quellen, ungespeicherte manuelle Änderungen, Quellenwechsel, Variablenpicker und die anschließenden Daten-/Aktionswege mit einem Symcon-Ersatz. Die Bedienung des Konfigurationsformulars auf einem echten IP-Symcon-System muss zusätzlich geprüft werden.

---

## Funktionen


## Wärmepumpenstatus

Folgende Betriebszustände können über IP-Symcon-Variablen dargestellt werden:

- Wärmepumpe Ein/Aus
- Heizbetrieb
- Warmwasserbetrieb
- Kühlbetrieb
- Abtaubetrieb
- Zusatzheizung
- Warnung
- Fehler
- Tag-/Nachtbetrieb

Nicht konfigurierte optionale Statusanzeigen werden ausgeblendet.

### Tag-/Nachtbetrieb

Ist eine entsprechende Variable konfiguriert, kann die Card zwischen Sonnen- und Mondsymbol umschalten.

Ohne konfigurierte Variable werden beide Symbole ausgeblendet.

---

## Temperaturen

Je nach Anlage können unter anderem folgende Temperaturen dargestellt werden:

- Außentemperatur
- Raumtemperatur
- Vorlauftemperatur
- Rücklauftemperatur
- Wärmequelle Eintritt
- Wärmequelle Austritt
- Verdampfertemperatur
- Kondensatortemperatur
- Warmwasserspeicher oben
- Warmwasserspeicher Mitte
- Warmwasserspeicher unten
- Pufferspeicher oben
- Pufferspeicher Mitte
- Pufferspeicher unten

Nicht vorhandene Messwerte müssen nicht konfiguriert werden.

---

## Raumtemperatur

Für die Raumtemperatur können die von der Heat Pump Card vorgesehenen Werte verwendet werden.

Wenn spezielle Soll-/Betriebsarten nicht benötigt werden, kann einfach die vorhandene Raumtemperatur als normaler Raumtemperaturwert verwendet werden.

---

## Kältekreis

Der Kältekreis kann mit folgenden Messwerten dargestellt werden:

### Verdampfer

- Temperatur
- Druck

### Kondensator

- Temperatur
- Druck

### Verdichter

- Betriebszustand
- Verdichterwert / Leistung / Drehzahl

### Expansionsventil

- Öffnungsgrad des Expansionsventils

Die genaue Datenquelle ist nicht vorgeschrieben. Dadurch können Werte unterschiedlicher Wärmepumpen und Steuerungen verwendet werden.

---

## Heiz- und Kühlbetrieb

Im Kühlbetrieb wird die Funktion von Verdampfer und Kondensator grafisch vertauscht.

Hierfür werden gezielt die entsprechenden Elemente der originalen SVG-Grafik verwendet:

- Verdampfersymbol
- Kondensatorsymbol
- zugehörige Temperaturen
- zugehörige Drücke

Andere Elemente des Kältekreises werden dabei nicht verschoben.

---

## Lüfter

Bei einer Luft/Wasser-Wärmepumpe kann der Lüfter animiert werden.

Unter **Wärmepumpe → Lüfter** stehen **Drehzahl** und **Aktivsignal (Alternative)** gemeinsam nebeneinander. Eine gültige Drehzahlvariable reicht aus: Bei einer Drehzahl größer null ist der Lüfter aktiv, bei null steht er. Das separate Aktivsignal wird nur verwendet, wenn keine gültige Drehzahlvariable verknüpft ist. Bestehende Zuordnungen bleiben erhalten.

Als Datenquelle können sowohl:

- Boolean
- Integer
- Float

verwendet werden.

### Dynamische Lüftergeschwindigkeit

Bei einem Zahlenwert wird die Animationsgeschwindigkeit dynamisch angepasst.

Die Darstellung ist für einen typischen Bereich von ungefähr **0–500 U/min** optimiert.

Beispiel:

\| Lüfterwert | Grafische Umdrehungsdauer |

\|---:|---:|

\| 0 | Lüfter steht |

\| 100 | ca. 2,5 s |

\| 200 | ca. 1,8 s |

\| 300 | ca. 1,2 s |

\| 400 | ca. 0,8 s |

\| 500 | ca. 0,5 s |

Zwischenwerte werden stufenlos interpoliert.

Bei einer Boolean-Variable wird:

- `false` als Stillstand
- `true` als mittlere Lüftergeschwindigkeit

interpretiert.

---

## Pumpen

Folgende Pumpen können abhängig von der Anlage dargestellt werden:

- Umwälzpumpe
- Speicherladepumpe
- Heizkreispumpe
- weitere von der Original-Card unterstützte Pumpen

Eine Pumpe wird nur angezeigt, wenn die entsprechende Variable konfiguriert ist.

Dadurch bleibt die Darstellung auch bei einfacheren Anlagen übersichtlich.

---

## Warmwasser-/Heizungs-Umschaltventil

Das Umschaltventil zwischen Heiz- und Warmwasserbetrieb kann über eine Variable dargestellt werden.

Ist keine Variable konfiguriert, wird das Ventilsymbol vollständig ausgeblendet.

---

## Heizkreise

Die von der Heat Pump Card vorgesehenen Heizkreise können verwendet werden.

Unterstützte Heizkreisarten sind:

- Aus
- Fußbodenheizung
- Heizkörper

Je nach Heizkreis können folgende Werte dargestellt werden:

- Heizkreispumpe
- Vorlauftemperatur
- Rücklauftemperatur

---

## Sonderfunktion bei genau einem Heizkreis

Für Anlagen mit **genau einem aktiven Heizkreis** steht im Bereich Heizkreis 1 die Option **„Heizen des Warmwasserspeichers integrieren“** zur Verfügung.

Ist die Option aktiviert und ein Umschaltventil Warmwasser/Heizung konfiguriert, wird dessen Stellung als Referenz verwendet. Während der Warmwasserbereitung werden die zuletzt gültigen Vor- und Rücklauftemperaturen des Heizkreises in der Heizkreis-Darstellung eingefroren. Gleichzeitig werden die aktuellen Vor- und Rücklauftemperaturen an der Warmwasserstrecke bei der Wärmepumpe angezeigt. Nach der Rückschaltung auf Heizen übernimmt der Heizkreis wieder die aktuellen Werte.

Ist die Option deaktiviert, wird diese Sonderdarstellung nicht angewendet. Ist kein Umschaltventil konfiguriert, erfolgt ebenfalls keine Umschaltlogik.

---

## Warmwasserspeicher und Pufferspeicher

Die Speicher können mit mehreren Temperatursensoren dargestellt werden.

Unterstützt werden:

- Temperatur oben
- Temperatur Mitte
- Temperatur unten

Es werden ausschließlich verknüpfte Sensoren mit einem vorhandenen Messwert angezeigt. Jeder Sensor behält seine Position: **oben bleibt oben, Mitte bleibt Mitte und unten bleibt unten**, auch wenn nur ein einzelner Sensor konfiguriert ist. Fehlende Temperaturen werden weder ergänzt noch aus anderen Sensoren übernommen. Beim Entfernen einer Zuordnung oder Ausbleiben eines Messwerts wird der alte Text vollständig gelöscht. Eine echte Temperatur von **0 °C** bleibt sichtbar. Auch die Konfigurationsvorschau erzeugt keine Temperaturwerte.

---

## Temperaturfarben

Standardmäßig verwendet das Modul die **Original-Temperaturfarben der Heat Pump Card**. Optional kann im Konfigurationsformular **„Eigene Temperaturfarben verwenden“** aktiviert werden.

Für die eigenen Farben ist folgende 10-stufige Standardvorlage hinterlegt:

\| Temperatur | Farbe | Hex |

\|---:|---|---|

\| 15 °C | Dunkelblau | `#0066CC` |

\| 20 °C | Hellblau | `#29A9E8` |

\| 25 °C | Cyan | `#39D5D5` |

\| 30 °C | Gelb | `#FFD166` |

\| 35 °C | Hellgelb | `#FFE066` |

\| 40 °C | Gold | `#FFC233` |

\| 45 °C | Orange | `#FF9818` |

\| 50 °C | Orange-Rot | `#FF5A18` |

\| 55 °C | Rot | `#E52B2B` |

\| 60 °C | Signalrot | `#FF0000` |

Zwischen den Stützpunkten werden die Farben stufenlos interpoliert. Temperaturen unterhalb bzw. oberhalb der Skala werden auf die jeweilige Randfarbe begrenzt.

Die eigene Farbskala wird durchgängig für die temperaturabhängige Hydraulikdarstellung verwendet, unter anderem für Heizkreise, Speicher, Warmwasserstrecke und Solarthermie. Über den Button **„Eigene Farben auf Standardvorlage zurücksetzen“** können alle zehn Temperaturpunkte und Farben jederzeit auf diese Vorlage zurückgesetzt werden.

---

## Heizstäbe

Die Warmwasserspeicher-Darstellung wurde gegenüber der Original-Card erweitert.

Es können **0 bis 3 Heizstäbe** konfiguriert werden.

### Anzahl Heizstäbe

Im Konfigurationsformular kann ausgewählt werden:

- Keine
- 1 Heizstab
- 2 Heizstäbe
- 3 Heizstäbe

Die Heizstäbe werden von unten nach oben im Warmwasserspeicher angeordnet.

Damit befindet sich bei nur einem vorhandenen Heizstab dieser automatisch an der untersten Position.

### Heizstab-Variablen

Für jeden Heizstab kann eine eigene IP-Symcon-Variable ausgewählt werden:

- Heizstab 1
- Heizstab 2
- Heizstab 3

Die Variable darf beispielsweise sein:

- Boolean-Schaltzustand
- Integer-Leistung
- Float-Leistung
- anderer numerischer Betriebswert

### Einschaltschwelle

Für jeden Heizstab kann direkt neben der Variable eine Schwelle **„Ein ab“** angegeben werden.

Beispiel mit Leistung:

```text

Heizstab 1: Leistungsvariable

Ein ab:     100

```

Bei:

```text

0 W

```

wird der Heizstab als inaktiv dargestellt.

Bei:

```text

1200 W

```

wird der Heizstab als aktiv dargestellt.

Damit kann anstelle eines reinen Relaisstatus die tatsächlich aufgenommene Leistung verwendet werden. Das ist beispielsweise hilfreich, wenn ein Relais eingeschaltet ist, der Heizstab jedoch durch einen Temperaturbegrenzer abgeschaltet wurde.

### Boolean-Variablen

Boolean-Werte werden ebenfalls unterstützt:

```text

false = 0

true  = 1

```

Für eine Boolean-Variable kann daher einfach:

```text

Ein ab: 1

```

verwendet werden.

### Darstellung

Alle aktiven Heizstäbe werden einheitlich **rot** dargestellt.

Inaktive vorhandene Heizstäbe bleiben dezent sichtbar.

Die Heizstäbe befinden sich in der unteren Hälfte des Warmwasserspeichers.

---

## Thermische Solaranlage

Die von der Original-Card vorgesehene thermische Solaranlage kann verwendet werden, wenn entsprechende Werte vorhanden sind.

Mögliche Daten:

- Solarpumpe
- Pumpendrehzahl
- Kollektortemperatur
- Vorlauftemperatur
- Rücklauftemperatur

Wird keine Solarthermie verwendet, muss dieser Bereich nicht konfiguriert werden.

---

## Zusatzwerte

Die 13 von der SVG vorgesehenen freien Zusatzfelder sind im Modul konfigurierbar. Für jeden Platz `000` bis `012` können im Bereich **„Zusatzwerte“** festgelegt werden:

- eine frei wählbare Bezeichnung
- eine IP-Symcon-Variable

Der Variablenwert wird formatiert und mit seiner Einheit dargestellt, sofern diese über IP-Symcon verfügbar ist. Nicht belegte Zusatzfelder bleiben ausgeblendet.

Damit lassen sich beispielsweise COP, elektrische Leistung, Wärmemenge, Volumenstrom, Betriebsstunden oder weitere anlagenspezifische Messwerte ergänzen. Die Positionen entsprechen den in der Original-SVG vorgesehenen Additional-Value-Plätzen.

---

## Flexible Variablentypen

Wo technisch sinnvoll, ist das Modul nicht unnötig auf einen einzelnen IP-Symcon-Variablentyp beschränkt.

Insbesondere Status- und Betriebswerte können je nach Funktion beispielsweise als:

- Boolean
- Integer
- Float

verarbeitet werden.

Dadurch kann das Modul mit unterschiedlichen Wärmepumpen, Steuerungen und Integrationen verwendet werden.

---

## Konfigurationsprinzip

Die Konfiguration ist bewusst optional aufgebaut.

Es müssen **nicht alle Werte vorhanden sein**.

Grundsätzlich gilt:

\> Nicht konfiguriert = nicht anzeigen.

Dadurch kann dieselbe Visualisierung sowohl für einfache als auch für umfangreich instrumentierte Wärmepumpenanlagen verwendet werden.

Die Feldbezeichnungen orientieren sich möglichst direkt an den Funktionen der zugrunde liegenden Heat Pump Card.

---

## Bedienbare Status- und Sollwertsymbole

Die obere Statusleiste wurde erweitert. Sichtbare Statussymbole werden dynamisch und mit gleichmäßigen Abständen innerhalb des vorgesehenen Bereichs angeordnet. Die Anordnung verwendet einheitliche SVG-Koordinaten und ist damit auf Desktop- und Mobilansichten identisch.

Für **Heizen**, **Warmwasser** und **Kühlen** können im Konfigurationsformular Variablen für die Betriebsart hinterlegt werden. Sind für eine Funktion passende Werte/Profile verfügbar, kann die Betriebsart direkt über das jeweilige Symbol in der Visualisierung umgeschaltet werden. Die Auswahl erfolgt über ein für Maus- und Touchbedienung geeignetes Popup.

Zusätzlich können zwei Sollwerte direkt in der Statusleiste angezeigt und bedient werden:

- Warmwasser-Solltemperatur
- Heiztemperaturkorrektur

Die beiden Sollwertsymbole zeigen den aktuellen Zahlenwert direkt im Kreis. Ein Antippen öffnet die zugehörige Werteingabe. Nicht konfigurierte Funktionen werden nicht angezeigt.

### Statusfarben

Die Statussymbole unterscheiden zwischen inaktivem, eingeschaltetem und aktivem Zustand. Aktive Betriebsarten werden passend zur Funktion hervorgehoben, beispielsweise Heizen und Warmwasser in Orange sowie Kühlen in Blau.

---

## Animierte Energie- und Medienflüsse

Die Visualisierung erweitert die statische Originaldarstellung um animierte Flüsse. Dazu gehören – abhängig von Betriebsart und vorhandenen Komponenten – unter anderem der Kältekreis sowie Heizungs- und Warmwasserpfade. Die Flussrichtung wird an den jeweiligen Betriebszustand angepasst.

Elektrische Heizstäbe werden bei Aktivität zusätzlich dezent zwischen Orange und Rot pulsierend dargestellt. Die vorhandene Aktivierungs- und Schwellwertlogik bleibt dabei unverändert.

---

## Voll- und Kompaktansicht

Die Visualisierung besitzt zwei unabhängig gerenderte Ansichten:

- **Vollansicht** – komplette Wärmepumpengrafik
- **Kompaktansicht** – für kleinere Displays optimierte Darstellung des oberen Informations- und Bedienbereichs

Über den Umschalter **⇄** unten in der Mitte kann direkt zwischen beiden Ansichten gewechselt werden. Die Kompaktansicht ist **kein Zoom oder Ausschnitt der Vollansicht**, sondern wird eigenständig gerendert. Dadurch bleibt die vollständige Darstellung unverändert.

Die gewählte Ansicht wird lokal im jeweiligen Browser bzw. auf dem jeweiligen Gerät gespeichert. Dadurch kann beispielsweise auf dem Smartphone dauerhaft die Kompaktansicht verwendet werden, während Tablet oder Desktop gleichzeitig die Vollansicht derselben IP-Symcon-Instanz anzeigen. Das Umschalten auf einem Gerät beeinflusst die anderen Views nicht.

Die Kompaktansicht übernimmt das aktuelle helle bzw. dunkle Layout der IP-Symcon-Visualisierung und unterstützt die gleichen Touch-Popups für Betriebsarten und Sollwerte.

---

## Technische Integration

Die JavaScript-Logik der Visualisierung ist direkt in `module.php` integriert. Eine separate `heat-pump-card.js` und eine separate `de.json` werden nicht benötigt. Die benötigten deutschen Beschriftungen sind Bestandteil des Moduls.

Als externe Darstellungsressource bleibt die SVG-Datei `heat-pump/heat-pump-card/heat-pump.svg` erhalten.

---

## Kompatibilität

Das Modul ist nicht speziell an Luxtronik gebunden.

Es kann grundsätzlich mit jeder Wärmepumpe verwendet werden, deren Daten als IP-Symcon-Variablen zur Verfügung stehen.

Beispiele für mögliche Datenquellen:

- Luxtronik
- Modbus
- MQTT
- Hersteller-APIs
- Home-Automation-Gateways
- selbst erstellte IP-Symcon-Skripte oder Module

Die Zuordnung erfolgt ausschließlich über die im Konfigurationsformular ausgewählten Variablen.

---

## Grundlage

Die grafische Darstellung basiert auf:

**Lovelace Heat Pump Card**  

von Manfred Tremmel

Die ursprüngliche Heat Pump Card stellt eine SVG-basierte Wärmepumpenvisualisierung bereit. Dieses Projekt integriert und erweitert diese Darstellung für die Verwendung in IP-Symcon.

Zu den Erweiterungen gehören unter anderem:

- IP-Symcon-Konfigurationsformular
- direkte Zuordnung von IP-Symcon-Variablen
- optionale Anzeige nicht vorhandener Komponenten
- dynamische Lüftergeschwindigkeit
- angepasste Darstellung für Kühlbetrieb
- optionale, frei konfigurierbare 10-stufige Temperaturfarbskala mit Reset-Vorlage
- Unterstützung von bis zu drei Heizstäben
- frei definierbare Einschaltschwellen für Heizstäbe
- Solarthermie mit Vor- und Rücklauftemperatur
- Sonderdarstellung für Warmwasserbereitung bei genau einem Heizkreis
- 13 frei konfigurierbare Zusatzwerte
- dynamisch angeordnete und bedienbare Statussymbole
- direkte Bedienung von Heizen, Warmwasser und Kühlen über Touch-/Maus-Popups
- Sollwertsymbole für Warmwasser-Solltemperatur und Heiztemperaturkorrektur
- animierte Flüsse für Kältekreis, Heizungs- und Warmwasserpfade
- pulsierende Darstellung aktiver elektrischer Heizstäbe
- eigenständig gerenderte Voll- und Kompaktansicht mit lokal gespeicherter Auswahl
- Umschalter `⇄` unten mittig für die geräteindividuelle Ansicht
- JavaScript und deutsche Beschriftungen direkt in `module.php` integriert

---

## Lizenz

Dieses Projekt verwendet Bestandteile der **Lovelace Heat Pump Card** von Manfred Tremmel.

Die jeweiligen Urheber- und Lizenzhinweise der verwendeten Originalkomponenten sind zu beachten.

Eigene Erweiterungen dieses IP-Symcon-Moduls unterliegen der im Repository angegebenen Lizenz.

---

## Hinweis

Wärmepumpen unterscheiden sich je nach Hersteller, Modell, Hydraulik und vorhandener Sensorik erheblich.

Nicht jede Anlage stellt sämtliche von der Visualisierung unterstützten Werte zur Verfügung. Nicht vorhandene Werte können einfach unkonfiguriert bleiben.

Die Visualisierung dient der Darstellung und Überwachung der vorhandenen Betriebsdaten und ersetzt keine sicherheitsrelevanten Funktionen oder Regelungen der Wärmepumpe.

## Versionen

### Version 2.0 (23.09.2026)

- Neue grafische Oberfläche.

### Version 1.3 (03.09.2026)

- Geänderte Farben in der Konfiguration werden nun nicht mehr ohne Rückfrage gespeichert.

### Version 1.2 (27.08.2026)

- Die animierten Flusslinien werden nun auch im Heizkörper und Bodenheizung dargestellt.
- Die Flussrichtung und Beschriftung im Kältekreislauf wurde angepasst.

### Version 1.1 (24.08.2026)

- Es kann auf jedem Gerät eine individuelle Ansicht gewählt werden. Werden pro View mehrere unterschiedliche Ansichten gewünscht, muss die Instanz geklont werden. Die Kacheln in der Visu identifizieren sich nun über die Instanz.
- Zusätzlich zu den Heizstäben im Warmwasserspeicher wird im Bedienfeld entsprechend ein Icon eingeblendet falls Heizstäbe konfiguriert sind.
- Tag und Nachtmodus lässt sich nun über einen Integer konfigurieren.

### Version 1.0 (23.08.2026)

- Initiale Version.
## Update von Version 1.x auf Version 2.0

Bestehende Instanzen können weiterverwendet werden. Bibliotheks-ID und Modul-ID
bleiben gegenüber `main` unverändert. Die 88 weiterverwendeten Eigenschaften
behalten ihre bisherigen Namen, Typen und Standardwerte. Bereits gespeicherte
Variablen-IDs und Einstellungen werden nicht zurückgesetzt; eine neue Instanz
oder erneute Zuordnung ist dafür nicht erforderlich.

Im Formular sind die Messwerte nach Wärmepumpe, Speicher, Heizkreisen,
Solarthermie und Zusatzwerten geordnet. Nicht benötigte Variablen bleiben auf
„Keine“. Es werden nur Einstellungen angeboten, welche die aktuelle
Visualisierung verwendet. Die alten Einzelstatusfelder (Ein/Aus, Heizen,
Warmwasser, Kühlen, Abtauen), deren Statuswertlisten, die alten Heizstabstatus,
Solarpumpenstatus und Solar-Vor-/Rücklauftemperaturen sowie die bisherige
Warmwasser-Umschaltoption für einen Heizkreis werden nicht mehr abgefragt.
Die zugehörigen gespeicherten Einstellungen bleiben aus Kompatibilitätsgründen
registriert und werden nicht gelöscht. Die Lüfterstatusvariable bleibt als
Fallback für eine fehlende Drehzahlvariable auswählbar. Die drei einzeln
konfigurierbaren Heizstäbe mit Anzahl und Einschaltschwelle bleiben verfügbar.

Unter **Wärmepumpe → Messwerte → Rücklauf Soll** kann eine zusätzliche
Solltemperaturvariable ausgewählt werden. Die Rücklaufzeile in der Infobox
zeigt dann beispielsweise `28.9 °C / Soll 30.0 °C`. Der Istwert verwendet weiter
die bestehende Variable `RefluxTemperatureHeating1` aus `main`. Ohne Sollwert
wird weiterhin nur der Istwert angezeigt. Die Box behält ihre Größe; die Werte
stehen näher an der Beschriftung und passen sich bei langen Profiltexten an
den vorhandenen Platz an. Der Sollwert dient der Anzeige, nicht der Bedienung.

Die in `development` entfallenen Funktionen werden nicht wieder eingeführt:
Wasser-/Sole-Wärmepumpentypen mit Quellentemperaturen, Party-/Energiesparmodus,
Tag-/Nachtstatus, Warnung/Fehler/Zusatzheizung, reduzierte/Party-Raumtemperatur,
Puffer-Heizstab, Schichtladung und eigene Temperaturfarbskala. Von den früheren
13 Zusatzwerten werden die Plätze 000–009 weiterverwendet; 010–012 entfallen.
Die neue Oberfläche bleibt auf Luft/Wasser ausgelegt. Neue Felder wie Leistung,
Heizkreisname und Pumpendrehzahl/Durchfluss müssen bei Bedarf zugeordnet werden.

### Lokale Prüfung

Mit PHP 8.4 oder einer passenden PHP-Version des Zielsystems:

```sh
php -l WP/module.php
php tests/regression.php
```

Die Tests prüfen die Kompatibilität zu den in `main` registrierten Eigenschaften,
das Formular, Variablenabonnements und Datenaufbereitung mit einem minimalen
Symcon-Ersatz. Sie ersetzen keine Prüfung in einer echten IP-Symcon-Installation.
Mit `WP_TEST_ARTIFACT_DIR=/tmp/wp-review php tests/regression.php` können HTML und
JavaScript zur zusätzlichen Browser-/Syntaxprüfung ausgegeben werden.

Offizielle SDK-Dokumentation: <https://www.symcon.de/de/llms.txt>.

Die optionale Browserprüfung `python3 tests/browser.py` benötigt Playwright und
Chromium. Sie prüft die Rücklaufzeile bei Desktop- und Mobilbreite, die
unveränderte Boxbreite sowie Aktualisierungen und fehlende Messwerte.

### Technisch moderne Grafik

Die SVG verwendet klare Gerätekonturen, neutrale Speicherflächen mit schmalen
Temperaturakzenten und dezente Schatten. Beschriftungen und Geräteflächen folgen
dem Symcon-Theme. Alle bisherigen Element-IDs, Variablenzuordnungen und die
Breite der Infobox bleiben erhalten.

Flusspunkte erscheinen nur bei passenden aktiven Betriebsvariablen, etwa
Verdichter- und Pumpenstatus. Ohne solche Daten wird keine Aktivität dargestellt.
Die Animation bleibt bei Datenaktualisierungen stabil. Die Betriebssystemoption
für reduzierte Bewegung unterdrückt die animierten Flusspunkte und Rotoren.

Die Boileranzeige zeigt ausschließlich zugeordnete Speicherfühler. Heizkreis-Vor- und Rücklauf werden dort auch während der Warmwasserbereitung nicht als zusätzliche Temperaturen eingeblendet.
