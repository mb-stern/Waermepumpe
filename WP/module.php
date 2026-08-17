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

        // Verhindert nur, dass ein möglicher String "</script>" die HTML-SDK-
        // Rückgabe beendet. Die Originaldatei im Modul wird nicht verändert.
        $vendorJs = str_replace('</script>', '<\/script>', $vendorJs);

        $title = htmlspecialchars(
            $this->ReadPropertyString('Title'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        return <<<HTML
<style>
    html,
    body {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: auto;
        background: transparent;
        font-family: Arial, sans-serif;
    }

    #wp-root {
        width: 100%;
        min-height: 100%;
        box-sizing: border-box;
    }

    #wp-title {
        box-sizing: border-box;
        padding: 8px 10px 4px 10px;
        font-size: 18px;
        font-weight: 600;
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
    heat-pump-card ha-card,
    heat-pump-card svg {
        display: block;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    heat-pump-card ha-card {
        background: transparent !important;
        box-shadow: none !important;
        border: 0 !important;
    }

    heat-pump-card svg {
        height: auto !important;
    }
</style>

<div id="wp-root">
    <div id="wp-title">{$title}</div>
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
                    .replace(/.*--primary-text-color:.*/g, '')
                    .replace(/ class="rotate"/g, '')
                    .replace(/display: inline;/g, 'display: none;')
                + '</ha-card>';

            this.content = this.querySelector('svg');

            if (!this.content) {
                throw new Error('SVG konnte nicht in die Card eingefügt werden.');
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
        } catch (error) {
            showError(
                'Fehler beim Initialisieren der Wärmepumpen-Card:\\n'
                + (error && error.stack ? error.stack : String(error))
            );
        }
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

        applyCardData();
    };

    applyCardData();
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
