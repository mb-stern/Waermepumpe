# Wärmepumpe für IP-Symcon

Folgende Funktionen beinhaltet das Energiefluss Symcon Repository

- __Waermepumpe__ ([Dokumentation](WP))   

Visualisierung einer Wärmepumpe in IP-Symcon auf Basis der **Lovelace Heat Pump Card** von Manfred Tremmel.

Das Modul stellt den kompletten Wärmepumpenprozess grafisch dar und verbindet die Visualisierung direkt mit Variablen aus IP-Symcon. Es ist nicht auf einen bestimmten Wärmepumpenhersteller beschränkt und kann mit beliebigen Datenquellen verwendet werden, sofern die entsprechenden Werte in IP-Symcon vorhanden sind.

Die Konfiguration erfolgt vollständig über das IP-Symcon-Konfigurationsformular.

---

## Funktionen

### Wärmepumpentypen

Unterstützt werden die drei von der Heat Pump Card vorgesehenen Wärmepumpentypen:

- Luft/Wasser (`A2W`)
- Wasser/Wasser (`W2W`)
- Sole/Wasser (`G2W`)

Je nach gewähltem Wärmepumpentyp wird die Darstellung automatisch angepasst.