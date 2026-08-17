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
    private const WEB_FOLDER = 'Waermepumpe';

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
        $this->RegisterPropertyString('Title', 'Wärmepumpe');
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

        $this->PublishResources();
        $this->RegisterVariableMessages();

        // Bei geänderter Konfiguration die bereits geöffnete HTML-SDK-Darstellung aktualisieren.
        $this->PushVisualizationState(true);
    }

    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void
    {
        if ($Message === VM_UPDATE) {
            $this->PushVisualizationState(false);
        }
    }

    public function GetVisualizationTile(): string
    {
        if (!$this->ResourcesAvailable()) {
            return '<div style="padding:16px;font-family:sans-serif;color:#c62828;">'
                . 'Wärmepumpen-Ressourcen fehlen. Erwartet wird die Originalstruktur: heat-pump/heat-pump-card.js sowie heat-pump/heat-pump-card/heat-pump.svg und de.json.'
                . '</div>';
        }

        return $this->BuildVisualizationHtml();
    }

    public function GetConfigurationForm(): string
    {
        $elements = [
            [
                'type'    => 'Label',
                'caption' => 'Dynamische Wärmepumpengrafik auf Basis der lovelace-heat-pump-card 0.9.0.'
            ],
            [
                'type'  => 'RowLayout',
                'items' => [
                    [
                        'type'    => 'ValidationTextBox',
                        'name'    => 'Title',
                        'caption' => 'Titel'
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
                    ]
                ]
            ],

            [
                'type'    => 'ExpansionPanel',
                'caption' => 'Wärmepumpe und Betriebszustände',
                'items'   => [
                    $this->VariableRow('Außentemperatur', 'OutdoorTemperature', 'Vorlauftemperatur WP', 'SupplyTemperature'),
                    $this->VariableRow('Quelle Eingang', 'TemperatureGroundWaterIn', 'Quelle Ausgang', 'TemperatureGroundWaterOut'),
                    $this->VariableRow('Wärmepumpe läuft', 'HpRunning', 'Verdichter läuft', 'CompressorRunning'),
                    $this->VariableRow('WP Ein/Aus', 'HeatingPumpStatusOnOff', 'Heizbetrieb', 'HeatingPumpHeatingMode'),
                    $this->VariableRow('Warmwasserbetrieb', 'HeatingPumpHotWaterMode', 'Kühlbetrieb', 'HeatingPumpCoolingMode'),
                    $this->VariableRow('Nachtbetrieb', 'HeatingPumpNightMode', 'Energiesparbetrieb', 'HeatingPumpEnergySaveMode'),
                    $this->VariableRow('Partybetrieb', 'HeatingPumpPartyMode', 'Zusatzheizung', 'AdditionalHeating'),
                    $this->VariableRow('Warnung', 'Warning', 'Fehler', 'Error'),
                    $this->VariableRow('Abtaubetrieb', 'DefrostMode', 'Raumtemperatur normal', 'AmbientTemperatureNormal'),
                    $this->VariableRow('Raumtemperatur reduziert', 'AmbientTemperatureReduced', 'Raumtemperatur Party', 'AmbientTemperatureParty')
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
                    $this->VariableRow('Puffer oben', 'TankTempHPUp', 'Warmwasser oben', 'TankTempWWUp'),
                    $this->VariableRow('Puffer Mitte', 'TankTempHPMiddle', 'Warmwasser Mitte', 'TankTempWWMiddle'),
                    $this->VariableRow('Puffer unten', 'TankTempHPDown', 'Warmwasser unten', 'TankTempWWDown'),
                    $this->VariableRow('Speicherladepumpe', 'StorageChargingPumpRunning', 'Zirkulationspumpe', 'CirculatingPumpRunning'),
                    $this->VariableRow('WW-/Heizungsventil', 'WWHeatingValve', 'Heizstab Warmwasser', 'HeaterRodWW'),
                    $this->VariableRow('Heizstab Puffer', 'HeaterRodHP', 'Heizstab Stufe 1', 'HeaterRodLevel1'),
                    $this->VariableRow('Heizstab Stufe 2', 'HeaterRodLevel2', '', '')
                ]
            ],

            $this->HeatingCircuitPanel(1),
            $this->HeatingCircuitPanel(2),
            $this->HeatingCircuitPanel(3),

            [
                'type'    => 'ExpansionPanel',
                'caption' => 'Kältekreis',
                'items'   => [
                    $this->VariableRow('Verdampferdruck', 'EvaporatorPressure', 'Verdampfertemperatur', 'EvaporatorTemperature'),
                    $this->VariableRow('Verflüssigerdruck', 'CondenserPressure', 'Verflüssigertemperatur', 'CondenserTemperature'),
                    $this->VariableRow('Expansionsventil Öffnung', 'ExpansionValveOpening', 'Verdichterwert', 'CompressorValue')
                ]
            ],

            [
                'type'    => 'ExpansionPanel',
                'caption' => 'Solarthermie',
                'items'   => [
                    [
                        'type'    => 'CheckBox',
                        'name'    => 'ThermalSolarAvailable',
                        'caption' => 'Solarthermie anzeigen'
                    ],
                    $this->VariableRow('Solarpumpe', 'ThermalSolarPump', 'Pumpendrehzahl', 'ThermalSolarPumpSpeed'),
                    $this->VariableRow('Kollektortemperatur', 'ThermalSolarPanelTemp', 'Solar-Vorlauftemperatur', 'ThermalSolarFluxTemp')
                ]
            ]
        ];

        $actions = [
            [
                'type'    => 'Button',
                'caption' => 'Visualisierung neu aufbauen',
                'onClick' => 'WP_UpdateVisualization($id);'
            ]
        ];

        return json_encode(
            [
                'elements' => $elements,
                'actions'  => $actions
            ],
            JSON_THROW_ON_ERROR
        );
    }

    public function UpdateVisualization(): void
    {
        // Öffentliche Hilfsfunktion für den Button im Konfigurationsformular.
        $this->PublishResources();
        $this->PushVisualizationState(true);
    }

    private function PushVisualizationState(bool $reloadConfig): void
    {
        $payload = [
            'type'   => $reloadConfig ? 'config' : 'states',
            'config' => $reloadConfig ? $this->BuildCardConfig() : null,
            'states' => $this->BuildHassStates()
        ];

        $this->UpdateVisualizationValue(
            json_encode(
                $payload,
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
                    'Heizkreispumpe',
                    'HeatingCircuitPumpRunning' . $suffix,
                    'Vorlauf',
                    'SupplyTemperatureHeating' . $suffix
                ),
                $this->VariableRow(
                    'Rücklauf',
                    'RefluxTemperatureHeating' . $suffix,
                    '',
                    ''
                )
            ]
        ];
    }

    private function VariableRow(string $caption1, string $name1, string $caption2, string $name2): array
    {
        $items = [];

        if ($name1 !== '') {
            $items[] = [
                'type'    => 'SelectVariable',
                'name'    => $name1,
                'caption' => $caption1
            ];
        }

        if ($name2 !== '') {
            $items[] = [
                'type'    => 'SelectVariable',
                'name'    => $name2,
                'caption' => $caption2
            ];
        }

        return [
            'type'  => 'RowLayout',
            'items' => $items
        ];
    }

    private function ResourcesAvailable(): bool
    {
        $sourceDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'heat-pump';

        $requiredFiles = [
            $sourceDirectory . DIRECTORY_SEPARATOR . 'heat-pump-card.js',
            $sourceDirectory . DIRECTORY_SEPARATOR . 'heat-pump-card' . DIRECTORY_SEPARATOR . 'heat-pump.svg',
            $sourceDirectory . DIRECTORY_SEPARATOR . 'heat-pump-card' . DIRECTORY_SEPARATOR . 'de.json'
        ];

        foreach ($requiredFiles as $fileName) {
            if (!is_file($fileName)) {
                return false;
            }
        }

        return true;
    }

    private function PublishResources(): bool
    {
        $sourceDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'heat-pump';

        $sourceCardFile = $sourceDirectory . DIRECTORY_SEPARATOR . 'heat-pump-card.js';
        $sourceAssetDirectory = $sourceDirectory
            . DIRECTORY_SEPARATOR
            . 'heat-pump-card';

        $webfrontRoot = IPS_GetKernelDir()
            . 'webfront'
            . DIRECTORY_SEPARATOR
            . 'user'
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, self::WEB_FOLDER);

        $webfrontAssetDirectory = $webfrontRoot
            . DIRECTORY_SEPARATOR
            . 'heat-pump-card';

        $requiredFiles = [
            $sourceCardFile,
            $sourceAssetDirectory . DIRECTORY_SEPARATOR . 'heat-pump.svg',
            $sourceAssetDirectory . DIRECTORY_SEPARATOR . 'de.json'
        ];

        foreach ($requiredFiles as $fileName) {
            if (!is_file($fileName)) {
                $this->LogMessage(
                    sprintf('Fehlende Ressource: %s', $fileName),
                    KL_ERROR
                );
                return false;
            }
        }

        if (
            !is_dir($webfrontAssetDirectory)
            && !@mkdir($webfrontAssetDirectory, 0775, true)
            && !is_dir($webfrontAssetDirectory)
        ) {
            $this->LogMessage(
                'WebFront-Verzeichnis konnte nicht erstellt werden: ' . $webfrontAssetDirectory,
                KL_ERROR
            );
            return false;
        }

        // Originale dist-Struktur 1:1 veröffentlichen:
        //
        // /user/Waermepumpe/
        // ├── heat-pump-card.js
        // └── heat-pump-card/
        //     ├── heat-pump.svg
        //     └── de.json
        if (!@copy(
            $sourceCardFile,
            $webfrontRoot . DIRECTORY_SEPARATOR . 'heat-pump-card.js'
        )) {
            $this->LogMessage('heat-pump-card.js konnte nicht kopiert werden.', KL_ERROR);
            return false;
        }

        foreach (['heat-pump.svg', 'de.json'] as $fileName) {
            if (!@copy(
                $sourceAssetDirectory . DIRECTORY_SEPARATOR . $fileName,
                $webfrontAssetDirectory . DIRECTORY_SEPARATOR . $fileName
            )) {
                $this->LogMessage(
                    sprintf('%s konnte nicht kopiert werden.', $fileName),
                    KL_ERROR
                );
                return false;
            }
        }

        return true;
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
        $configJson = json_encode(
            $this->BuildCardConfig(),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR
        );

        $statesJson = json_encode(
            $this->BuildHassStates(),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR
        );

        $scriptUrl = '/user/' . self::WEB_FOLDER . '/heat-pump-card.js';
        $publicFolder = self::WEB_FOLDER . '/heat-pump-card';
        $title = htmlspecialchars($this->ReadPropertyString('Title'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
<div id="wp-root" class="wp-root">
    <div class="wp-title">{$title}</div>
    <heat-pump-card id="wp-card"></heat-pump-card>
</div>

<style>
    html, body {
        margin: 0;
        padding: 0;
        background: transparent;
    }

    .wp-root {
        width: 100%;
        height: 100%;
        box-sizing: border-box;
        overflow: auto;
        font-family: var(--font-family, Arial, sans-serif);
        color: var(--content-color, inherit);
    }

    .wp-title {
        font-size: 18px;
        font-weight: 600;
        margin: 8px;
    }

    heat-pump-card,
    heat-pump-card ha-card {
        display: block;
        width: 100%;
        background: transparent !important;
        box-shadow: none !important;
    }

    heat-pump-card svg {
        display: block;
        width: 100% !important;
        height: auto !important;
        max-width: 100%;
    }
</style>

<script src="{$scriptUrl}"></script>
<script>
(() => {
    let currentConfig = {$configJson};
    let currentStates = {$statesJson};

    const resourceFolder = '/user/{$publicFolder}/';

    const getCard = () => document.getElementById('wp-card');

    const getHeatPumpClass = () => customElements.get('heat-pump-card');

    const configureVendorClass = () => {
        const HeatPumpClass = getHeatPumpClass();

        if (!HeatPumpClass) {
            return false;
        }

        // Die Original-Card bleibt unverändert.
        // Lediglich ihr öffentlicher Ressourcenpfad wird zur Laufzeit
        // von HACS auf das von Symcon veröffentlichte Verzeichnis gesetzt.
        HeatPumpClass.cardFolder = resourceFolder;

        return true;
    };

    const applyCardData = () => {
        const card = getCard();

        if (!card || !configureVendorClass() || typeof card.setConfig !== 'function') {
            return false;
        }

        try {
            // Reihenfolge ist wichtig:
            // setConfig speichert zunächst nur die Topologie.
            // Das Setzen von hass startet beim ersten Mal das Laden von SVG
            // und Sprachdatei. Danach übernimmt die Original-Card selbst
            // setConfig() und setValues().
            card.setConfig(currentConfig);

            card.hass = {
                language: 'de-DE',
                states: currentStates
            };

            return true;
        } catch (error) {
            console.error('Wärmepumpen-Card konnte nicht initialisiert werden:', error);
            return false;
        }
    };

    const start = () => {
        if (!applyCardData()) {
            window.setTimeout(start, 50);
        }
    };

    // HTML-SDK: Laufende Wert- und Konfigurationsänderungen aus PHP.
    window.handleMessage = (message) => {
        let data = message;

        if (typeof data === 'string') {
            try {
                data = JSON.parse(data);
            } catch (error) {
                console.error('Ungültige HTML-SDK-Nachricht:', error);
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

        applyCardData();
    };

    if (customElements.get('heat-pump-card')) {
        start();
    } else {
        customElements.whenDefined('heat-pump-card').then(start);
    }
})();
</script>
HTML;
    }

    private function BuildCardConfig(): array
    {
        return [
            'title'                        => $this->ReadPropertyString('Title'),
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
