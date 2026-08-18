<?php

/*
 * ## Third-Party Components
 *
 * Komponente | Lizenz | Verwendung |
 * ------------|---------|------------|
 * lovelace-heat-pump-card (Manfred Tremmel) | MIT | Wärmepumpengrafik |
 */

declare(strict_types=1);

class Waermepumpe extends IPSModuleStrict
{

    private const BINARY_PROPERTIES = [
        'HeatingPumpStatusOnOff',
        'HeatingPumpHotWaterMode',
        'HeatingPumpHeatingMode',
        'HeatingPumpCoolingMode',
        'HeatingPumpPartyMode',
        'HeatingPumpEnergySaveMode',
        'HeatingPumpNightMode',
        'Warning',
        'Error',
        'DefrostMode',
        'AdditionalHeating',
        'HpRunning',
        'CompressorRunning',
        'CirculatingPumpRunning',
        'StorageChargingPumpRunning',
        'HeatingCircuitPumpRunning1',
        'HeatingCircuitPumpRunning2',
        'HeatingCircuitPumpRunning3',
        'WWHeatingValve',
        'HeaterRodWW',
        'HeaterRodHP',
        'HeaterRodLevel1',
        'HeaterRodLevel2',
        'ThermalSolarPump'
    ];

    private const VARIABLE_PROPERTIES = [
        'TemperatureGroundWaterIn',
        'TemperatureGroundWaterOut',

        'HeatingPumpStatusOnOff',
        'HeatingPumpHotWaterMode',
        'HeatingPumpHeatingMode',
        'HeatingPumpCoolingMode',
        'HeatingPumpPartyMode',
        'HeatingPumpEnergySaveMode',
        'HeatingPumpNightMode',

        'Warning',
        'Error',
        'DefrostMode',
        'AdditionalHeating',

        'OutdoorTemperature',
        'AmbientTemperatureNormal',
        'AmbientTemperatureReduced',
        'AmbientTemperatureParty',
        'SupplyTemperature',

        'HpRunning',
        'CompressorRunning',
        'CirculatingPumpRunning',
        'StorageChargingPumpRunning',

        'TankTempHPUp',
        'TankTempHPMiddle',
        'TankTempHPDown',

        'TankTempWWUp',
        'TankTempWWMiddle',
        'TankTempWWDown',

        'HeatingCircuitPumpRunning1',
        'SupplyTemperatureHeating1',
        'RefluxTemperatureHeating1',

        'HeatingCircuitPumpRunning2',
        'SupplyTemperatureHeating2',
        'RefluxTemperatureHeating2',

        'HeatingCircuitPumpRunning3',
        'SupplyTemperatureHeating3',
        'RefluxTemperatureHeating3',

        'EvaporatorPressure',
        'EvaporatorTemperature',
        'CondenserPressure',
        'CondenserTemperature',
        'ExpansionValveOpening',
        'CompressorValue',

        'WWHeatingValve',
        'HeaterRodWW',
        'HeaterRodHP',
        'HeaterRodLevel1',
        'HeaterRodLevel2',

        'ThermalSolarPump',
        'ThermalSolarPumpSpeed',
        'ThermalSolarPanelTemp',
        'ThermalSolarFluxTemp'
    ];

    public function Create(): void
    {
        parent::Create();

        // Allgemein
        $this->RegisterPropertyString('HeatingPumpType', 'A2W');

        // Primärquelle / Wärmepumpe
        $this->RegisterPropertyInteger('TemperatureGroundWaterIn', 0);
        $this->RegisterPropertyInteger('TemperatureGroundWaterOut', 0);

        $this->RegisterPropertyInteger('HeatingPumpStatusOnOff', 0);
        $this->RegisterPropertyInteger('HeatingPumpHotWaterMode', 0);
        $this->RegisterPropertyInteger('HeatingPumpHeatingMode', 0);
        $this->RegisterPropertyInteger('HeatingPumpCoolingMode', 0);
        $this->RegisterPropertyInteger('HeatingPumpPartyMode', 0);
        $this->RegisterPropertyInteger('HeatingPumpEnergySaveMode', 0);
        $this->RegisterPropertyInteger('HeatingPumpNightMode', 0);

        $this->RegisterPropertyInteger('Warning', 0);
        $this->RegisterPropertyInteger('Error', 0);
        $this->RegisterPropertyInteger('DefrostMode', 0);
        $this->RegisterPropertyInteger('AdditionalHeating', 0);

        $this->RegisterPropertyInteger('OutdoorTemperature', 0);
        $this->RegisterPropertyInteger('AmbientTemperatureNormal', 0);
        $this->RegisterPropertyInteger('AmbientTemperatureReduced', 0);
        $this->RegisterPropertyInteger('AmbientTemperatureParty', 0);
        $this->RegisterPropertyInteger('SupplyTemperature', 0);

        $this->RegisterPropertyInteger('HpRunning', 0);
        $this->RegisterPropertyInteger('CompressorRunning', 0);
        $this->RegisterPropertyInteger('CirculatingPumpRunning', 0);
        $this->RegisterPropertyInteger('StorageChargingPumpRunning', 0);

        // Pufferspeicher
        $this->RegisterPropertyBoolean('TankHP', true);
        $this->RegisterPropertyInteger('TankTempHPUp', 0);
        $this->RegisterPropertyInteger('TankTempHPMiddle', 0);
        $this->RegisterPropertyInteger('TankTempHPDown', 0);

        // Warmwasserspeicher
        $this->RegisterPropertyBoolean('TankWW', true);
        $this->RegisterPropertyBoolean('LayeredChargeStorage', false);
        $this->RegisterPropertyInteger('TankTempWWUp', 0);
        $this->RegisterPropertyInteger('TankTempWWMiddle', 0);
        $this->RegisterPropertyInteger('TankTempWWDown', 0);

        // Heizkreis 1
        $this->RegisterPropertyString('HeatingCircuitType1', 'underfloor');
        $this->RegisterPropertyInteger('HeatingCircuitPumpRunning1', 0);
        $this->RegisterPropertyInteger('SupplyTemperatureHeating1', 0);
        $this->RegisterPropertyInteger('RefluxTemperatureHeating1', 0);

        // Heizkreis 2
        $this->RegisterPropertyString('HeatingCircuitType2', 'off');
        $this->RegisterPropertyInteger('HeatingCircuitPumpRunning2', 0);
        $this->RegisterPropertyInteger('SupplyTemperatureHeating2', 0);
        $this->RegisterPropertyInteger('RefluxTemperatureHeating2', 0);

        // Heizkreis 3
        $this->RegisterPropertyString('HeatingCircuitType3', 'off');
        $this->RegisterPropertyInteger('HeatingCircuitPumpRunning3', 0);
        $this->RegisterPropertyInteger('SupplyTemperatureHeating3', 0);
        $this->RegisterPropertyInteger('RefluxTemperatureHeating3', 0);

        // Kältekreis
        $this->RegisterPropertyInteger('EvaporatorPressure', 0);
        $this->RegisterPropertyInteger('EvaporatorTemperature', 0);
        $this->RegisterPropertyInteger('CondenserPressure', 0);
        $this->RegisterPropertyInteger('CondenserTemperature', 0);
        $this->RegisterPropertyInteger('ExpansionValveOpening', 0);
        $this->RegisterPropertyInteger('CompressorValue', 0);

        // Ventil / Heizstab
        $this->RegisterPropertyInteger('WWHeatingValve', 0);
        $this->RegisterPropertyInteger('HeaterRodWW', 0);
        $this->RegisterPropertyInteger('HeaterRodHP', 0);
        $this->RegisterPropertyInteger('HeaterRodLevel1', 0);
        $this->RegisterPropertyInteger('HeaterRodLevel2', 0);

        // Solarthermie
        $this->RegisterPropertyBoolean('ThermalSolarAvailable', false);
        $this->RegisterPropertyInteger('ThermalSolarPump', 0);
        $this->RegisterPropertyInteger('ThermalSolarPumpSpeed', 0);
        $this->RegisterPropertyInteger('ThermalSolarPanelTemp', 0);
        $this->RegisterPropertyInteger('ThermalSolarFluxTemp', 0);

        // HTML-SDK Visualisierung
        $this->SetVisualizationType(1);

        $this->SetBuffer('RegisteredVariables', '[]');
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();

        $this->RegisterVariableMessages();

        // Änderungen im Konfigurationsformular sofort an bereits offene
        // HTML-SDK-Kacheln senden und dort vollständig neu aufbauen.
        $this->ReloadVisualization();
    }

    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void
    {
        if ($Message === VM_UPDATE) {
            // Bei jeder überwachten Variablenänderung vollständigen aktuellen
            // Zustand senden und die Card in der offenen Kachel neu erzeugen.
            $this->ReloadVisualization();
        }
    }

    public function GetVisualizationTile(): string
    {
        if (!$this->ResourcesAvailable()) {
            return '<div style="padding:16px;font-family:sans-serif;color:#c62828;">'
                . 'Wärmepumpen-Ressourcen fehlen. Erwartet wird: heat-pump/heat-pump-card.js sowie heat-pump/heat-pump-card/heat-pump.svg und de.json.'
                . '</div>';
        }

        return $this->BuildVisualizationHtml();
    }

    public function GetConfigurationForm(): string
    {
        $elements = [
            [
                'type'    => 'Label',
                'caption' => 'Die Anlagenstruktur wird von der originalen Heat-Pump-Card dynamisch aus diesen Optionen aufgebaut. Messvariablen liefern nur Werte und Zustände.'
            ],
            [
                'type'    => 'Select',
                'name'    => 'HeatingPumpType',
                'caption' => 'Wärmepumpentyp',
                'options' => [
                    ['caption' => 'Luft / Wasser', 'value' => 'A2W'],
                    ['caption' => 'Wasser / Wasser', 'value' => 'W2W'],
                    ['caption' => 'Sole / Wasser', 'value' => 'G2W']
                ]
            ],

            [
                'type'    => 'ExpansionPanel',
                'caption' => 'Wärmepumpe',
                'items'   => [
                    [
                        'type'    => 'Label',
                        'caption' => 'Temperaturen und Wärmequelle'
                    ],
                    $this->VariableRow('Außentemperatur – Temperatur der Außenluft', 'OutdoorTemperature', 'Vorlauftemperatur WP – Heizwasser am Ausgang der Wärmepumpe', 'SupplyTemperature'),
                    $this->VariableRow('Quelle Eingang – Temperatur vor Verdampfer (Sole/Wasser)', 'TemperatureGroundWaterIn', 'Quelle Ausgang – Temperatur nach Verdampfer (Sole/Wasser)', 'TemperatureGroundWaterOut'),
                    $this->VariableRow('Raumtemperatur Normal – Soll-/Referenzwert Normalbetrieb', 'AmbientTemperatureNormal', 'Raumtemperatur Reduziert – Soll-/Referenzwert Absenkbetrieb', 'AmbientTemperatureReduced'),
                    $this->VariableRow('Raumtemperatur Party – Soll-/Referenzwert Partybetrieb', 'AmbientTemperatureParty', '', ''),

                    [
                        'type'    => 'Label',
                        'caption' => 'Betriebszustände'
                    ],
                    $this->VariableRow('Wärmepumpe läuft – Gesamtstatus der Wärmepumpe', 'HpRunning', 'Verdichter läuft – Kompressor aktiv/inaktiv', 'CompressorRunning'),
                    $this->VariableRow('WP Ein/Aus – Freigabe bzw. Betriebsbereitschaft', 'HeatingPumpStatusOnOff', 'Zusatzheizung – elektrischer/zusätzlicher Wärmeerzeuger aktiv', 'AdditionalHeating'),
                    $this->VariableRow('Abtaubetrieb – Abtauvorgang des Verdampfers aktiv', 'DefrostMode', '', ''),

                    [
                        'type'    => 'Label',
                        'caption' => 'Betriebsarten'
                    ],
                    $this->VariableRow('Heizbetrieb – Wärmepumpe heizt das Gebäude', 'HeatingPumpHeatingMode', 'Warmwasserbetrieb – Wärmepumpe lädt den Warmwasserspeicher', 'HeatingPumpHotWaterMode'),
                    $this->VariableRow('Kühlbetrieb – Wärmepumpe arbeitet im Kühlmodus', 'HeatingPumpCoolingMode', 'Nachtbetrieb – Nacht-/Absenkbetrieb aktiv', 'HeatingPumpNightMode'),
                    $this->VariableRow('Energiesparbetrieb – Eco-/Sparbetrieb aktiv', 'HeatingPumpEnergySaveMode', 'Partybetrieb – temporärer Komfortbetrieb aktiv', 'HeatingPumpPartyMode'),

                    [
                        'type'    => 'Label',
                        'caption' => 'Meldungen'
                    ],
                    $this->VariableRow('Warnung – allgemeiner Warnstatus', 'Warning', 'Fehler – allgemeiner Störungs-/Fehlerstatus', 'Error'),

                    [
                        'type'    => 'Label',
                        'caption' => 'Kältekreis'
                    ],
                    $this->VariableRow('Niederdruck – Druck auf der Verdampfer-/Sauggasseite', 'EvaporatorPressure', 'Verdampfungstemperatur – Kältemittel-Sättigungstemperatur auf der Niederdruckseite', 'EvaporatorTemperature'),
                    $this->VariableRow('Hochdruck – Druck auf der Verflüssiger-/Druckgasseite', 'CondenserPressure', 'Kondensationstemperatur – Kältemittel-Sättigungstemperatur auf der Hochdruckseite', 'CondenserTemperature'),
                    $this->VariableRow('Expansionsventil – Öffnungsgrad des elektronischen Expansionsventils', 'ExpansionValveOpening', 'Verdichterdrehzahl – Drehzahl/Frequenz bzw. Leistungswert des Verdichters', 'CompressorValue')
                ]
            ],

            [
                'type'    => 'ExpansionPanel',
                'caption' => 'Speicher',
                'items'   => [
                    [
                        'type'  => 'RowLayout',
                        'items' => [
                            [
                                'type'    => 'CheckBox',
                                'name'    => 'TankHP',
                                'caption' => 'Pufferspeicher anzeigen'
                            ],
                            [
                                'type'    => 'CheckBox',
                                'name'    => 'TankWW',
                                'caption' => 'Warmwasserspeicher anzeigen'
                            ],
                            [
                                'type'    => 'CheckBox',
                                'name'    => 'LayeredChargeStorage',
                                'caption' => 'Kombinierter Schichtspeicher'
                            ]
                        ]
                    ],
                    $this->VariableRow('Puffer oben – obere Speichertemperatur', 'TankTempHPUp', 'Warmwasser oben – obere Speichertemperatur', 'TankTempWWUp'),
                    $this->VariableRow('Puffer Mitte – mittlere Speichertemperatur', 'TankTempHPMiddle', 'Warmwasser Mitte – mittlere Speichertemperatur', 'TankTempWWMiddle'),
                    $this->VariableRow('Puffer unten – untere Speichertemperatur', 'TankTempHPDown', 'Warmwasser unten – untere Speichertemperatur', 'TankTempWWDown'),
                    $this->VariableRow('Speicherladepumpe – lädt Puffer/WW-Speicher', 'StorageChargingPumpRunning', 'Zirkulationspumpe – Warmwasserzirkulation aktiv', 'CirculatingPumpRunning'),
                    $this->VariableRow('Umschaltventil WW/Heizung – Stellung des 3-Wege-Ventils', 'WWHeatingValve', 'Heizstab Warmwasser – elektrischer Heizstab aktiv', 'HeaterRodWW'),
                    $this->VariableRow('Heizstab Puffer – Zusatzheizung im Heizspeicher', 'HeaterRodHP', 'Heizstab Stufe 1 – erste Leistungsstufe aktiv', 'HeaterRodLevel1'),
                    $this->VariableRow('Heizstab Stufe 2', 'HeaterRodLevel2', '', '')
                ]
            ],

            $this->HeatingCircuitPanel(1),
            $this->HeatingCircuitPanel(2),
            $this->HeatingCircuitPanel(3),


            [
                'type'    => 'ExpansionPanel',
                'caption' => 'Solarthermie',
                'items'   => [
                    [
                        'type'    => 'CheckBox',
                        'name'    => 'ThermalSolarAvailable',
                        'caption' => 'Solarthermie anzeigen'
                    ],
                    $this->VariableRow('Solarpumpe – Solarthermie-Pumpe aktiv', 'ThermalSolarPump', 'Pumpendrehzahl – Drehzahl/Leistung der Solarpumpe', 'ThermalSolarPumpSpeed'),
                    $this->VariableRow('Kollektortemperatur – Temperatur am Solarkollektor', 'ThermalSolarPanelTemp', 'Solar-Vorlauftemperatur – Temperatur vom Kollektor zum Speicher', 'ThermalSolarFluxTemp')
                ]
            ]
        ];

        return json_encode(
            [
                'elements' => $elements
            ],
            JSON_THROW_ON_ERROR
        );
    }

    public function UpdateVisualization(): void
    {
        // Button im Konfigurationsformular: komplette HTML-SDK-Kachel neu laden.
        $this->ReloadVisualization();
    }

    private function ReloadVisualization(): void
    {
        // GetVisualizationTile() wird vom HTML-SDK nur initial aufgerufen.
        // Darum bei ApplyChanges(), Button und VM_UPDATE immer den kompletten
        // aktuellen Zustand an alle offenen Visualisierungen senden.
        $this->UpdateVisualizationValue(
            json_encode(
                [
                    'type'   => 'rebuild',
                    'config' => $this->BuildCardConfig(),
                    'states' => $this->BuildHassStates()
                ],
                JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_THROW_ON_ERROR
            )
        );
    }

    private function HeatingCircuitPanel(int $number): array
    {
        $suffix = (string) $number;

        return [
            'type'    => 'ExpansionPanel',
            'caption' => 'Heizkreis ' . $suffix,
            'items'   => [
                [
                    'type'    => 'Select',
                    'name'    => 'HeatingCircuitType' . $suffix,
                    'caption' => 'Typ',
                    'options' => [
                        ['caption' => 'Aus', 'value' => 'off'],
                        ['caption' => 'Fußbodenheizung', 'value' => 'underfloor'],
                        ['caption' => 'Heizkörper', 'value' => 'radiator']
                    ]
                ],
                $this->VariableRow(
                    'Heizkreispumpe – Pumpe des Heizkreises',
                    'HeatingCircuitPumpRunning' . $suffix,
                    'Vorlauf – Temperatur zum Heizkreis',
                    'SupplyTemperatureHeating' . $suffix
                ),
                $this->VariableRow(
                    'Rücklauf – Temperatur vom Heizkreis',
                    'RefluxTemperatureHeating' . $suffix,
                    '',
                    ''
                )
            ]
        ];
    }

    private function VariableRow(string $caption1, string $name1, string $caption2, string $name2): array
    {
        $columns = [
            [
                'type'  => 'ColumnLayout',
                'width' => '50%',
                'items' => [
                    [
                        'type'    => 'SelectVariable',
                        'name'    => $name1,
                        'caption' => $caption1
                    ]
                ]
            ]
        ];

        if ($name2 !== '') {
            $columns[] = [
                'type'  => 'ColumnLayout',
                'width' => '50%',
                'items' => [
                    [
                        'type'    => 'SelectVariable',
                        'name'    => $name2,
                        'caption' => $caption2
                    ]
                ]
            ];
        }

        return [
            'type'  => 'RowLayout',
            'items' => $columns
        ];
    }

    private function ResourcesAvailable(): bool
    {
        foreach ($this->GetResourceFiles() as $fileName) {
            if (!is_file($fileName) || !is_readable($fileName)) {
                return false;
            }
        }

        return true;
    }

    private function GetResourceFiles(): array
    {
        $sourceDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'heat-pump';
        $assetDirectory = $sourceDirectory . DIRECTORY_SEPARATOR . 'heat-pump-card';

        return [
            'js'   => $sourceDirectory . DIRECTORY_SEPARATOR . 'heat-pump-card.js',
            'svg'  => $assetDirectory . DIRECTORY_SEPARATOR . 'heat-pump.svg',
            'lang' => $assetDirectory . DIRECTORY_SEPARATOR . 'de.json'
        ];
    }

    private function RegisterVariableMessages(): void
    {
        $oldIds = json_decode($this->GetBuffer('RegisteredVariables'), true);
        if (is_array($oldIds)) {
            foreach ($oldIds as $oldId) {
                if (is_int($oldId) || ctype_digit((string) $oldId)) {
                    @ $this->UnregisterMessage((int) $oldId, VM_UPDATE);
                }
            }
        }

        $ids = [];

        foreach (self::VARIABLE_PROPERTIES as $property) {
            $variableId = $this->ReadPropertyInteger($property);

            if ($variableId > 0 && IPS_VariableExists($variableId)) {
                $this->RegisterMessage($variableId, VM_UPDATE);
                $ids[] = $variableId;
            }
        }

        $ids = array_values(array_unique($ids));
        $this->SetBuffer('RegisteredVariables', json_encode($ids, JSON_THROW_ON_ERROR));
    }

    private function BuildVisualizationHtml(): string
    {
        $files = $this->GetResourceFiles();

        $vendorJs = file_get_contents($files['js']);
        $svg = file_get_contents($files['svg']);
        $localizationRaw = file_get_contents($files['lang']);

        if ($vendorJs === false || $svg === false || $localizationRaw === false) {
            return '<div style="padding:16px;font-family:sans-serif;color:#c62828;">'
                . 'Die Wärmepumpen-Ressourcen konnten nicht gelesen werden.'
                . '</div>';
        }

        $localization = json_decode($localizationRaw, true);
        if (!is_array($localization)) {
            return '<div style="padding:16px;font-family:sans-serif;color:#c62828;">'
                . 'de.json der Wärmepumpen-Card enthält kein gültiges JSON.'
                . '</div>';
        }

        $configJson = json_encode(
            $this->BuildCardConfig(),
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
            | JSON_THROW_ON_ERROR
        );

        $statesJson = json_encode(
            $this->BuildHassStates(),
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
            | JSON_THROW_ON_ERROR
        );

        $svgJson = json_encode(
            $svg,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
            | JSON_THROW_ON_ERROR
        );

        $localizationJson = json_encode(
            $localization,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
            | JSON_THROW_ON_ERROR
        );

        // Die SVG-CSS-Variablen der Original-Card werden bewusst NICHT entfernt.
        // Ohne --primary-text-color / --card-background-color würden große Teile
        // der Grafik in der Symcon-Dunkelansicht schwarz auf dunkel erscheinen.
        //
        // Verhindert nur, dass ein möglicher String "</script>" die HTML-SDK-
        // Rückgabe beendet. Die Originaldatei im Modul wird nicht verändert.
        $vendorJs = str_replace('</script>', '<\/script>', $vendorJs);

        return <<<HTML
<style>
    html,
    body {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
        background: transparent !important;
        color: inherit;
        font-family: Arial, sans-serif;
    }

    #wp-root {
        width: 100%;
        height: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }

    #wp-error {
        display: none;
        margin: 8px;
        padding: 10px;
        white-space: pre-wrap;
        border: 1px solid #c62828;
        border-radius: 6px;
        color: #c62828;
        font-size: 12px;
        line-height: 1.45;
    }

    heat-pump-card,
    heat-pump-card ha-card {
        display: block;
        width: 100%;
        height: 100%;
        max-width: 100%;
        max-height: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }

    heat-pump-card ha-card {
        background: transparent !important;
        box-shadow: none !important;
        border: 0 !important;
    }

    heat-pump-card svg {
        display: block;
        width: 100% !important;
        height: 100% !important;
        max-width: 100%;
        max-height: 100%;
    }
</style>

<div id="wp-root">
    <div id="wp-error"></div>
    <heat-pump-card id="wp-card"></heat-pump-card>
</div>

<script>
{$vendorJs}
</script>

<script>
(() => {
    let currentConfig = {$configJson};
    let currentStates = {$statesJson};

    const embeddedSvg = {$svgJson};
    const embeddedLocalization = {$localizationJson};

    const errorBox = document.getElementById('wp-error');

    const showError = (message) => {
        if (!errorBox) {
            return;
        }

        errorBox.textContent = message;
        errorBox.style.display = 'block';
    };

    const clearError = () => {
        if (!errorBox) {
            return;
        }

        errorBox.textContent = '';
        errorBox.style.display = 'none';
    };

    const HeatPumpClass = customElements.get('heat-pump-card');

    if (!HeatPumpClass) {
        showError('heat-pump-card.js wurde injiziert, aber das Custom Element "heat-pump-card" wurde nicht registriert.');
        return;
    }

    /*
     * Symcon-Adapter:
     * Die Originaldatei heat-pump-card.js bleibt unangetastet.
     * Nur die beiden Methoden, die in Home Assistant per XMLHttpRequest
     * SVG und Sprachdatei nachladen, werden zur Laufzeit auf die bereits
     * von PHP eingebetteten Ressourcen umgebogen.
     */
    HeatPumpClass.prototype.readSvg = function(lang, handleSvg, hass) {
        try {
            this.innerHTML =
                '<ha-card>\\n'
                + embeddedSvg
                    // heat-pump.svg is an XML file. In HTML-SDK it is inserted
                    // as inline SVG, so the XML CDATA wrapper around <style>
                    // must be removed while preserving the original CSS.
                    .replace(/<!\[CDATA\[/g, '')
                    .replace(/\]\]>/g, '')
                    .replace(/ class="rotate"/g, '')
                    .replace(/display: inline;/g, 'display: none;')
                + '</ha-card>';

            this.content = this.querySelector('svg');

            if (!this.content) {
                throw new Error('SVG konnte nicht in die Card eingefügt werden.');
            }

            // SVG immer in die vorhandene HTML-SDK-Kachel einpassen.
            this.content.setAttribute('width', '100%');
            this.content.setAttribute('height', '100%');
            this.content.setAttribute('preserveAspectRatio', 'xMidYMid meet');

            // Der Hintergrund gehört dem Symcon-Layout, nicht der Fremd-Card.
            // Inline-Styles übersteuern die im Original-SVG fest eingetragenen
            // weißen/schwarzen Hintergründe, ohne die Originaldatei zu ändern.
            this.content.style.setProperty('background', 'transparent', 'important');
            this.content.style.setProperty('background-color', 'transparent', 'important');
            this.content.style.setProperty('--card-background-color', 'transparent');

            // Text-/Linienfarbe möglichst aus dem aktuellen Layout übernehmen.
            const layoutTextColor = getComputedStyle(document.body).color;
            if (layoutTextColor) {
                this.content.style.setProperty('--primary-text-color', layoutTextColor);
            }


            const details = this.content.querySelector('#linkDetails');
            if (details && typeof this.linkHandling === 'function') {
                details.addEventListener('click', this.linkHandling);
            }

            const settings = this.content.querySelector('#linkSettings');
            if (settings && typeof this.linkHandling === 'function') {
                settings.addEventListener('click', this.linkHandling);
            }

            this.readLocalization(lang, hass);
        } catch (error) {
            showError(
                'Fehler beim Einfügen der Wärmepumpen-SVG:\\n'
                + (error && error.stack ? error.stack : String(error))
            );
        }

        return embeddedSvg;
    };

    HeatPumpClass.prototype.readLocalization = function(lang, hass) {
        try {
            HeatPumpClass.localization = embeddedLocalization;

            const texts =
                embeddedLocalization
                && embeddedLocalization.svgTexts
                    ? embeddedLocalization.svgTexts
                    : {};

            const setText = (selector, value) => {
                const element = this.content ? this.content.querySelector(selector) : null;
                if (element) {
                    element.innerHTML = value || '';
                }
            };

            setText('#textTankWWName', texts.tankWWName);
            setText('#textTankHPName', texts.tankHPName);
            setText('#textEvaporator', texts.evaporator);
            setText('#textCondenser', texts.condenser);
            setText('#textCompressor', texts.compressor);
            setText('#textExpansionValve', texts.expansionValve);
            setText('#textCirculatingPump', texts.circulatingPump);
            setText('#textSupplyTemperatureLabel', texts.supplyTemperatureLabel);

            this.setConfig(this.config || {});
            this.setValues(hass || { language: 'de-DE', states: {} });

            // Die eigentliche Symcon-Zuordnung wird direkt nach der
            // Initialisierung nochmals durch applyCardData() gesetzt.
        } catch (error) {
            showError(
                'Fehler beim Initialisieren der Wärmepumpen-Card:\\n'
                + (error && error.stack ? error.stack : String(error))
            );
        }
    };

    const isEntityOn = (entityName) => {
        if (!entityName || !currentStates || !currentStates[entityName]) {
            return false;
        }

        const value = String(currentStates[entityName].state ?? '').trim().toLowerCase();
        return ['1', 'true', 'on', 'yes', 'ja', 'ein', 'active', 'aktiv'].includes(value);
    };

    const setStroke = (svg, selector, value) => {
        const element = svg.querySelector(selector);
        if (element) {
            element.style.stroke = value;
        }
    };

    const applyCoolingVisualization = (card) => {
        if (!card || !card.content) {
            return;
        }

        const svg = card.content;
        const cooling = isEntityOn(currentConfig.heatingPumpCoolingMode);

        /*
         * Die Original-Card 0.9.0 blendet im Kühlbetrieb lediglich
         * #gHPStatusCooling ein. Für Symcon drehen wir zusätzlich auf der
         * Heiz-/Gebäudeseite Vorlauf und Rücklauf farblich um:
         *
         * Heizen:  Vorlauf rot, Rücklauf blau
         * Kühlen:  Vorlauf blau, Rücklauf rot
         *
         * Warmwasser/Solarthermie bleiben unverändert.
         */
        const supplyColor = cooling ? '#0000ff' : '#ff0000';
        const returnColor = cooling ? '#ff0000' : '#0000ff';

        [
            '#pathPipeToBuffer',
            '#pathPipeToHeatingCircuitPump',
            '#pathPipeToHeatingCircuitPump2',
            '#pathPipeToHeatingCircuitPump3',
            '#pathPipeBufferToHeating'
        ].forEach((selector) => setStroke(svg, selector, supplyColor));

        [
            '#pathPipeFromBuffer',
            '#pathPipeToHP',
            '#pathPipeToHP2',
            '#pathPipeHeatingToBuffer'
        ].forEach((selector) => setStroke(svg, selector, returnColor));

        // Heizkreis-Farbverläufe ebenfalls umdrehen.
        [
            ['#stopCircuit1', '#stopCircuit2'],
            ['#stopCircuit3', '#stopCircuit4'],
            ['#stopCircuit5', '#stopCircuit6']
        ].forEach(([hotStopSelector, coldStopSelector]) => {
            const hotStop = svg.querySelector(hotStopSelector);
            const coldStop = svg.querySelector(coldStopSelector);

            if (hotStop) {
                hotStop.style.stopColor = cooling ? '#34109f' : '#a00f0f';
            }
            if (coldStop) {
                coldStop.style.stopColor = cooling ? '#a00f0f' : '#34109f';
            }
        });

        // Kennzeichnung am Root für spätere CSS-/Animations-Erweiterungen.
        svg.dataset.operatingMode = cooling ? 'cooling' : 'heating';
    };

    const formatStateForSvg = (entityName) => {
        if (!entityName || !currentStates || !currentStates[entityName]) {
            return '';
        }

        const item = currentStates[entityName];
        const raw = item.state;

        if (raw === null || raw === undefined || raw === '') {
            return '';
        }

        const numeric = Number(raw);
        const unit =
            item.attributes && item.attributes.unit_of_measurement
                ? String(item.attributes.unit_of_measurement).trim()
                : '';

        if (!Number.isNaN(numeric)) {
            const value = new Intl.NumberFormat('de-CH', {
                minimumFractionDigits: 1,
                maximumFractionDigits: 1
            }).format(numeric);

            return unit ? value + ' ' + unit : value;
        }

        return unit ? String(raw) + ' ' + unit : String(raw);
    };

    const updateRefrigerantValues = (card) => {
        if (!card || !card.content) {
            return;
        }

        const values = [
            ['#textEvaporatorPressure', currentConfig.evaporatorPressure],
            ['#textEvaporatorTemperature', currentConfig.evaporatorTemperature],
            ['#textCondenserPressure', currentConfig.condenserPressure],
            ['#textCondenserTemperature', currentConfig.condenserTemperature],
            ['#textExpansionValveOpening', currentConfig.expansionValveOpening],
            ['#textCompressorValue', currentConfig.compressorValue]
        ];

        values.forEach(([selector, entityName]) => {
            const element = card.content.querySelector(selector);
            if (element) {
                element.textContent = formatStateForSvg(entityName);
            }
        });
    };

    const resolveLayoutTextColor = () => {
        // Im HTML-SDK erbt document.body die Textfarbe des übergeordneten
        // Symcon-Layouts nicht zuverlässig. Der Farbschemamodus dagegen wird
        // vom Browser an die Kachel weitergereicht.
        const dark = window.matchMedia
            && window.matchMedia('(prefers-color-scheme: dark)').matches;

        return dark ? '#ffffff' : '#000000';
    };

    const isBlack = (value) => {
        const normalized = String(value || '')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '');

        return [
            'black',
            '#000',
            '#000000',
            'rgb(0,0,0)',
            'rgba(0,0,0,1)'
        ].includes(normalized);
    };

    const applyThemeColors = (card) => {
        if (!card || !card.content) {
            return;
        }

        const svg = card.content;
        const textColor = resolveLayoutTextColor();

        // Hintergrund ausschließlich vom Symcon-Layout.
        svg.style.setProperty('background', 'transparent', 'important');
        svg.style.setProperty('background-color', 'transparent', 'important');
        svg.style.setProperty('--card-background-color', 'transparent');
        svg.style.setProperty('--primary-text-color', textColor);
        svg.style.setProperty('color', textColor);

        // Text grundsätzlich an den Layoutmodus koppeln.
        svg.querySelectorAll('text, tspan').forEach((element) => {
            element.style.setProperty('fill', textColor, 'important');
            element.style.setProperty('color', textColor, 'important');
        });

        // Schwarze Linien/Symbole der Original-SVG im Dark Mode weiß machen.
        // Im Light Mode bleiben sie schwarz.
        svg.querySelectorAll('*').forEach((element) => {
            const computed = getComputedStyle(element);

            const fill =
                element.getAttribute('fill')
                || element.style.fill
                || computed.fill;

            const stroke =
                element.getAttribute('stroke')
                || element.style.stroke
                || computed.stroke;

            if (isBlack(fill)) {
                element.style.setProperty('fill', textColor, 'important');
            }

            if (isBlack(stroke)) {
                element.style.setProperty('stroke', textColor, 'important');
            }
        });
    };

    const rebuildCard = () => {
        const root = document.getElementById('wp-root');

        if (!root) {
            showError('Der Wärmepumpen-Container wurde im HTML nicht gefunden.');
            return;
        }

        const oldCard = document.getElementById('wp-card');
        if (oldCard) {
            oldCard.remove();
        }

        // Wirklich ein neues Custom Element erzeugen. Die Original-Card hält
        // intern Zustand in content/config; nur innerHTML zu leeren reicht daher
        // nicht für einen vollständigen Neuaufbau.
        const card = document.createElement('heat-pump-card');
        card.id = 'wp-card';
        root.appendChild(card);

        // Initialisierung erst im nächsten Browser-Zyklus, damit das neue
        // Custom Element vollständig verbunden ist.
        requestAnimationFrame(() => {
            applyCardData();
        });
    };

    const applyCardData = () => {
        const card = document.getElementById('wp-card');

        if (!card) {
            showError('Das Element <heat-pump-card> wurde im HTML nicht gefunden.');
            return;
        }

        try {
            clearError();

            card.setConfig(currentConfig);
            card.hass = {
                language: 'de-DE',
                states: currentStates
            };

            const finalizeSvg = (attempt = 0) => {
                if (card.content) {
                    applyCoolingVisualization(card);
                    updateRefrigerantValues(card);
                    applyThemeColors(card);
                    return;
                }

                if (attempt < 40) {
                    window.setTimeout(() => finalizeSvg(attempt + 1), 25);
                }
            };

            finalizeSvg();
        } catch (error) {
            showError(
                'Fehler beim Übergeben der Symcon-Daten an die Card:\\n'
                + (error && error.stack ? error.stack : String(error))
            );
        }
    };

    /*
     * HTML-SDK: Nachrichten vom PHP-Modul.
     * GetVisualizationTile() liefert den kompletten Initialzustand.
     * Danach aktualisiert UpdateVisualizationValue() nur noch die Daten.
     */
    window.handleMessage = (message) => {
        let data = message;

        if (typeof data === 'string') {
            try {
                data = JSON.parse(data);
            } catch (error) {
                showError('Ungültige HTML-SDK-Nachricht: ' + String(error));
                return;
            }
        }

        if (!data || typeof data !== 'object') {
            return;
        }

        if (data.config) {
            currentConfig = data.config;
        }

        if (data.states) {
            currentStates = data.states;
        }

        if (data.type === 'rebuild') {
            rebuildCard();
            return;
        }

        applyCardData();
    };

    if (window.matchMedia) {
        const scheme = window.matchMedia('(prefers-color-scheme: dark)');
        const onSchemeChange = () => {
            rebuildCard();
        };

        if (typeof scheme.addEventListener === 'function') {
            scheme.addEventListener('change', onSchemeChange);
        } else if (typeof scheme.addListener === 'function') {
            scheme.addListener(onSchemeChange);
        }
    }

    applyCardData();
})();
</script>
HTML;
    }

    private function BuildCardConfig(): array
    {
        return [
            'title'                        => '',
            'heatingPumpType'              => $this->ReadPropertyString('HeatingPumpType'),

            'temperatureGroundWaterIn'     => $this->EntityName('TemperatureGroundWaterIn'),
            'temperatureGroundWaterOut'    => $this->EntityName('TemperatureGroundWaterOut'),

            'heatingPumpStatusOnOff'       => $this->EntityName('HeatingPumpStatusOnOff'),
            'heatingPumpHotWaterMode'      => $this->EntityName('HeatingPumpHotWaterMode'),
            'heatingPumpHeatingMode'       => $this->EntityName('HeatingPumpHeatingMode'),
            'heatingPumpCoolingMode'       => $this->EntityName('HeatingPumpCoolingMode'),
            'heatingPumpPartyMode'         => $this->EntityName('HeatingPumpPartyMode'),
            'heatingPumpEnergySaveMode'    => $this->EntityName('HeatingPumpEnergySaveMode'),
            'heatingPumpNightMode'         => $this->EntityName('HeatingPumpNightMode'),

            'warning'                      => $this->EntityName('Warning'),
            'error'                        => $this->EntityName('Error'),
            'defrostMode'                  => $this->EntityName('DefrostMode'),
            'additionalHeating'            => $this->EntityName('AdditionalHeating'),

            'outdoorTemperature'           => $this->EntityName('OutdoorTemperature'),
            'ambientTemperatureNormal'     => $this->EntityName('AmbientTemperatureNormal'),
            'ambientTemperatureReduced'    => $this->EntityName('AmbientTemperatureReduced'),
            'ambientTemperatureParty'      => $this->EntityName('AmbientTemperatureParty'),
            'supplyTemperature'            => $this->EntityName('SupplyTemperature'),

            'hpRunning'                    => $this->EntityName('HpRunning'),
            'compressorRunning'            => $this->EntityName('CompressorRunning'),
            'circulatingPumpRunning'       => $this->EntityName('CirculatingPumpRunning'),
            'storageChargingPumpRunning'   => $this->EntityName('StorageChargingPumpRunning'),

            'tankHP'                       => $this->ReadPropertyBoolean('TankHP'),
            'tankTempHPUp'                 => $this->EntityName('TankTempHPUp'),
            'tankTempHPMiddle'             => $this->EntityName('TankTempHPMiddle'),
            'tankTempHPDown'               => $this->EntityName('TankTempHPDown'),

            'tankWW'                       => $this->ReadPropertyBoolean('TankWW'),
            'layeredChargeStorage'         => $this->ReadPropertyBoolean('LayeredChargeStorage'),
            'tankTempWWUp'                 => $this->EntityName('TankTempWWUp'),
            'tankTempWWMiddle'             => $this->EntityName('TankTempWWMiddle'),
            'tankTempWWDown'               => $this->EntityName('TankTempWWDown'),

            'heatingCircuitType1'           => $this->ReadPropertyString('HeatingCircuitType1'),
            'heatingCircuitPumpRunning'     => $this->EntityName('HeatingCircuitPumpRunning1'),
            'supplyTemperatureHeating'      => $this->EntityName('SupplyTemperatureHeating1'),
            'refluxTemperatureHeating'      => $this->EntityName('RefluxTemperatureHeating1'),

            'heatingCircuitType2'           => $this->ReadPropertyString('HeatingCircuitType2'),
            'heatingCircuitPumpRunning2'    => $this->EntityName('HeatingCircuitPumpRunning2'),
            'supplyTemperatureHeating2'     => $this->EntityName('SupplyTemperatureHeating2'),
            'refluxTemperatureHeating2'     => $this->EntityName('RefluxTemperatureHeating2'),

            'heatingCircuitType3'           => $this->ReadPropertyString('HeatingCircuitType3'),
            'heatingCircuitPumpRunning3'    => $this->EntityName('HeatingCircuitPumpRunning3'),
            'supplyTemperatureHeating3'     => $this->EntityName('SupplyTemperatureHeating3'),
            'refluxTemperatureHeating3'     => $this->EntityName('RefluxTemperatureHeating3'),

            'evaporatorPressure'            => $this->EntityName('EvaporatorPressure'),
            'evaporatorTemperature'         => $this->EntityName('EvaporatorTemperature'),
            'condenserPressure'             => $this->EntityName('CondenserPressure'),
            'condenserTemperature'          => $this->EntityName('CondenserTemperature'),
            'expansionValveOpening'         => $this->EntityName('ExpansionValveOpening'),
            'compressorValue'               => $this->EntityName('CompressorValue'),

            'wwHeatingValve'                => $this->EntityName('WWHeatingValve'),
            'heaterRodWW'                   => $this->EntityName('HeaterRodWW'),
            'heaterRodHP'                   => $this->EntityName('HeaterRodHP'),
            'heaterRodLevel1'               => $this->EntityName('HeaterRodLevel1'),
            'heaterRodLevel2'               => $this->EntityName('HeaterRodLevel2'),

            'thermalSolarAvailable'         => $this->ReadPropertyBoolean('ThermalSolarAvailable'),
            'thermalSolarPump'              => $this->EntityName('ThermalSolarPump'),
            'thermalSolarPumpSpeed'         => $this->EntityName('ThermalSolarPumpSpeed'),
            'thermalSolarPanelTemp'         => $this->EntityName('ThermalSolarPanelTemp'),
            'thermalSolarFluxTemp'          => $this->EntityName('ThermalSolarFluxTemp')
        ];
    }

    private function BuildHassStates(): array
    {
        $states = [];

        foreach (self::VARIABLE_PROPERTIES as $property) {
            $variableId = $this->ReadPropertyInteger($property);

            if ($variableId <= 0 || !IPS_VariableExists($variableId)) {
                continue;
            }

            $entityName = 'ips_' . $variableId;
            $isBinary = in_array($property, self::BINARY_PROPERTIES, true);

            $states[$entityName] = $this->BuildHassState($variableId, $isBinary);
        }

        return $states;
    }

    private function EntityName(string $property): ?string
    {
        $variableId = $this->ReadPropertyInteger($property);

        if ($variableId <= 0 || !IPS_VariableExists($variableId)) {
            return null;
        }

        return 'ips_' . $variableId;
    }

    private function BuildHassState(int $variableId, bool $binary): array
    {
        $value = GetValue($variableId);

        if ($binary) {
            $state = $this->NormalizeBinaryValue($value) ? 'on' : 'off';
        } else {
            if (is_bool($value)) {
                $state = $value ? '1' : '0';
            } elseif (is_float($value)) {
                $state = rtrim(rtrim(sprintf('%.10F', $value), '0'), '.');
            } else {
                $state = (string) $value;
            }
        }

        return [
            'state'      => $state,
            'attributes' => [
                'unit_of_measurement' => $this->GetVariableUnit($variableId)
            ]
        ];
    }

    private function NormalizeBinaryValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value !== 0.0;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array(
            $normalized,
            ['1', 'true', 'on', 'yes', 'ja', 'ein', 'active', 'aktiv'],
            true
        );
    }

    private function GetVariableUnit(int $variableId): string
    {
        $variable = IPS_GetVariable($variableId);

        $profileName = $variable['VariableCustomProfile'] !== ''
            ? $variable['VariableCustomProfile']
            : $variable['VariableProfile'];

        if ($profileName === '' || !IPS_VariableProfileExists($profileName)) {
            return '';
        }

        $profile = IPS_GetVariableProfile($profileName);

        return trim((string) ($profile['Suffix'] ?? ''));
    }
}
