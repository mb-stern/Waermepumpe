# Wärmepumpe für IP-Symcon

Visualisierung einer Wärmepumpe in IP-Symcon auf Basis der **Lovelace Heat Pump Card** von Manfred Tremmel.

Das Modul stellt den kompletten Wärmepumpenprozess grafisch dar und verbindet die Visualisierung direkt mit Variablen aus IP-Symcon. Es ist nicht auf einen bestimmten Wärmepumpenhersteller beschränkt und kann mit beliebigen Datenquellen verwendet werden, sofern die entsprechenden Werte in IP-Symcon vorhanden sind.

Die Konfiguration erfolgt vollständig über das IP-Symcon-Konfigurationsformular.

---

## Funktionen**

### Wärmepumpentypen**

Unterstützt werden die drei von der Heat Pump Card vorgesehenen Wärmepumpentypen:

- Luft/Wasser (`A2W`)

- Wasser/Wasser (`W2W`)

- Sole/Wasser (`G2W`)

Je nach gewähltem Wärmepumpentyp wird die Darstellung automatisch angepasst.

---

## Wärmepumpenstatus**

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

### Tag-/Nachtbetrieb**

Ist eine entsprechende Variable konfiguriert, kann die Card zwischen Sonnen- und Mondsymbol umschalten.

Ohne konfigurierte Variable werden beide Symbole ausgeblendet.

---

## Temperaturen**

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

## Raumtemperatur**

Für die Raumtemperatur können die von der Heat Pump Card vorgesehenen Werte verwendet werden.

Wenn spezielle Soll-/Betriebsarten nicht benötigt werden, kann einfach die vorhandene Raumtemperatur als normaler Raumtemperaturwert verwendet werden.

---

## Kältekreis**

Der Kältekreis kann mit folgenden Messwerten dargestellt werden:

### Verdampfer**

- Temperatur

- Druck

### Kondensator**

- Temperatur

- Druck

### Verdichter**

- Betriebszustand

- Verdichterwert / Leistung / Drehzahl

### Expansionsventil**

- Öffnungsgrad des Expansionsventils

Die genaue Datenquelle ist nicht vorgeschrieben. Dadurch können Werte unterschiedlicher Wärmepumpen und Steuerungen verwendet werden.

---

## Heiz- und Kühlbetrieb**

Im Kühlbetrieb wird die Funktion von Verdampfer und Kondensator grafisch vertauscht.

Hierfür werden gezielt die entsprechenden Elemente der originalen SVG-Grafik verwendet:

- Verdampfersymbol

- Kondensatorsymbol

- zugehörige Temperaturen

- zugehörige Drücke

Andere Elemente des Kältekreises werden dabei nicht verschoben.

---

## Lüfter**

Bei einer Luft/Wasser-Wärmepumpe kann der Lüfter animiert werden.

Als Datenquelle können sowohl:

- Boolean

- Integer

- Float

verwendet werden.

### Dynamische Lüftergeschwindigkeit**

Bei einem Zahlenwert wird die Animationsgeschwindigkeit dynamisch angepasst.

Die Darstellung ist für einen typischen Bereich von ungefähr **0–500 U/min** optimiert.

Beispiel:

| Lüfterwert | Grafische Umdrehungsdauer |

|---:|---:|

| 0 | Lüfter steht |

| 100 | ca. 2,5 s |

| 200 | ca. 1,8 s |

| 300 | ca. 1,2 s |

| 400 | ca. 0,8 s |

| 500 | ca. 0,5 s |

Zwischenwerte werden stufenlos interpoliert.

Bei einer Boolean-Variable wird:

- `false` als Stillstand

- `true` als mittlere Lüftergeschwindigkeit

interpretiert.

---

## Pumpen**

Folgende Pumpen können abhängig von der Anlage dargestellt werden:

- Umwälzpumpe

- Speicherladepumpe

- Heizkreispumpe

- weitere von der Original-Card unterstützte Pumpen

Eine Pumpe wird nur angezeigt, wenn die entsprechende Variable konfiguriert ist.

Dadurch bleibt die Darstellung auch bei einfacheren Anlagen übersichtlich.

---

## Warmwasser-/Heizungs-Umschaltventil**

Das Umschaltventil zwischen Heiz- und Warmwasserbetrieb kann über eine Variable dargestellt werden.

Ist keine Variable konfiguriert, wird das Ventilsymbol vollständig ausgeblendet.

---

## Heizkreise**

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

## Warmwasserspeicher und Pufferspeicher**

Die Speicher können mit mehreren Temperatursensoren dargestellt werden.

Unterstützt werden:

- Temperatur oben

- Temperatur Mitte

- Temperatur unten

Fehlende Mittel- oder Untertemperaturen können durch die Logik der Card aus den vorhandenen Temperaturen abgeleitet werden.

---

## Temperaturfarben

Standardmäßig verwendet das Modul die **Original-Temperaturfarben der Heat Pump Card**. Optional kann im Konfigurationsformular **„Eigene Temperaturfarben verwenden“** aktiviert werden.

Für die eigenen Farben ist folgende 10-stufige Standardvorlage hinterlegt:

| Temperatur | Farbe | Hex |
|---:|---|---|
| 15 °C | Dunkelblau | `#0066CC` |
| 20 °C | Hellblau | `#29A9E8` |
| 25 °C | Cyan | `#39D5D5` |
| 30 °C | Gelb | `#FFD166` |
| 35 °C | Hellgelb | `#FFE066` |
| 40 °C | Gold | `#FFC233` |
| 45 °C | Orange | `#FF9818` |
| 50 °C | Orange-Rot | `#FF5A18` |
| 55 °C | Rot | `#E52B2B` |
| 60 °C | Signalrot | `#FF0000` |

Zwischen den Stützpunkten werden die Farben stufenlos interpoliert. Temperaturen unterhalb bzw. oberhalb der Skala werden auf die jeweilige Randfarbe begrenzt.

Die eigene Farbskala wird durchgängig für die temperaturabhängige Hydraulikdarstellung verwendet, unter anderem für Heizkreise, Speicher, Warmwasserstrecke und Solarthermie. Über den Button **„Eigene Farben auf Standardvorlage zurücksetzen“** können alle zehn Temperaturpunkte und Farben jederzeit auf diese Vorlage zurückgesetzt werden.

---

## Heizstäbe**

Die Warmwasserspeicher-Darstellung wurde gegenüber der Original-Card erweitert.

Es können **0 bis 3 Heizstäbe** konfiguriert werden.

### Anzahl Heizstäbe**

Im Konfigurationsformular kann ausgewählt werden:

- Keine

- 1 Heizstab

- 2 Heizstäbe

- 3 Heizstäbe

Die Heizstäbe werden von unten nach oben im Warmwasserspeicher angeordnet.

Damit befindet sich bei nur einem vorhandenen Heizstab dieser automatisch an der untersten Position.

### Heizstab-Variablen**

Für jeden Heizstab kann eine eigene IP-Symcon-Variable ausgewählt werden:

- Heizstab 1

- Heizstab 2

- Heizstab 3

Die Variable darf beispielsweise sein:

- Boolean-Schaltzustand

- Integer-Leistung

- Float-Leistung

- anderer numerischer Betriebswert

### Einschaltschwelle**

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

### Boolean-Variablen**

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

### Darstellung**

Alle aktiven Heizstäbe werden einheitlich **rot** dargestellt.

Inaktive vorhandene Heizstäbe bleiben dezent sichtbar.

Die Heizstäbe befinden sich in der unteren Hälfte des Warmwasserspeichers.

---

## Thermische Solaranlage**

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

## Flexible Variablentypen**

Wo technisch sinnvoll, ist das Modul nicht unnötig auf einen einzelnen IP-Symcon-Variablentyp beschränkt.

Insbesondere Status- und Betriebswerte können je nach Funktion beispielsweise als:

- Boolean

- Integer

- Float

verarbeitet werden.

Dadurch kann das Modul mit unterschiedlichen Wärmepumpen, Steuerungen und Integrationen verwendet werden.

---

## Konfigurationsprinzip**

Die Konfiguration ist bewusst optional aufgebaut.

Es müssen **nicht alle Werte vorhanden sein**.

Grundsätzlich gilt:

\> Nicht konfiguriert = nicht anzeigen.

Dadurch kann dieselbe Visualisierung sowohl für einfache als auch für umfangreich instrumentierte Wärmepumpenanlagen verwendet werden.

Die Feldbezeichnungen orientieren sich möglichst direkt an den Funktionen der zugrunde liegenden Heat Pump Card.

---

## Technische Integration

Die JavaScript-Logik der Visualisierung ist direkt in `module.php` integriert. Eine separate `heat-pump-card.js` und eine separate `de.json` werden nicht benötigt. Die benötigten deutschen Beschriftungen sind Bestandteil des Moduls.

Als externe Darstellungsressource bleibt die SVG-Datei `heat-pump/heat-pump-card/heat-pump.svg` erhalten.

---

## Kompatibilität**

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

## Grundlage**

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

- JavaScript und deutsche Beschriftungen direkt in `module.php` integriert

---

## Lizenz**

Dieses Projekt verwendet Bestandteile der **Lovelace Heat Pump Card** von Manfred Tremmel.

Die jeweiligen Urheber- und Lizenzhinweise der verwendeten Originalkomponenten sind zu beachten.

Eigene Erweiterungen dieses IP-Symcon-Moduls unterliegen der im Repository angegebenen Lizenz.

---

## Hinweis**

Wärmepumpen unterscheiden sich je nach Hersteller, Modell, Hydraulik und vorhandener Sensorik erheblich.

Nicht jede Anlage stellt sämtliche von der Visualisierung unterstützten Werte zur Verfügung. Nicht vorhandene Werte können einfach unkonfiguriert bleiben.

Die Visualisierung dient der Darstellung und Überwachung der vorhandenen Betriebsdaten und ersetzt keine sicherheitsrelevanten Funktionen oder Regelungen der Wärmepumpe.

## Versionen**

### Version 1.0 (22.08.2026)**

- Initiale Version.