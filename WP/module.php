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
        'AmbientTemperatureActual',
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
        'HeaterRod1',
        'HeaterRod2',
        'HeaterRod3',

        'ThermalSolarPump',
        'ThermalSolarPumpSpeed',
        'ThermalSolarPanelTemp',
        'ThermalSolarFluxTemp',
        'ThermalSolarReturnTemp',

        'OperatingStatusVariable',
        'HeatingControlVariable',
        'HotWaterControlVariable',
        'CoolingControlVariable',
        'FanSpeed'
    ];

    public function Create(): void
    {
        parent::Create();

        // Allgemein
        $this->RegisterPropertyString('HeatingPumpType', 'A2W');

        // Zentrale Status- und Steuerungszuordnung
        $this->RegisterPropertyInteger('OperatingStatusVariable', 0);
        $this->RegisterPropertyString('OperatingStatusHeatingValues', '0,6');
        $this->RegisterPropertyString('OperatingStatusHotWaterValues', '1');
        $this->RegisterPropertyString('OperatingStatusCoolingValues', '7');
        $this->RegisterPropertyString('OperatingStatusDefrostValues', '4');

        $this->RegisterPropertyInteger('HeatingControlVariable', 0);
        $this->RegisterPropertyInteger('HotWaterControlVariable', 0);
        $this->RegisterPropertyInteger('CoolingControlVariable', 0);

        // Luft/Wasser: Lüfterdrehzahl > 0 hat Vorrang vor dem Status-Fallback.
        $this->RegisterPropertyInteger('FanSpeed', 0);
        $this->RegisterPropertyString('FanActiveStatusValues', '0,1,2,4,7');

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
        $this->RegisterPropertyInteger('AmbientTemperatureActual', 0);
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

        // Einheitlicher Temperatur-Farbverlauf für Heizkreis, Speicher und Wärmetauscher
        $this->RegisterPropertyBoolean('UseCustomTemperatureColors', false);
        $this->RegisterPropertyInteger('TemperaturePoint1', 20);
        $this->RegisterPropertyInteger('TemperatureColor1', 26316);       // #0066CC
        $this->RegisterPropertyInteger('TemperaturePoint2', 30);
        $this->RegisterPropertyInteger('TemperatureColor2', 6733823);     // #66BFFF
        $this->RegisterPropertyInteger('TemperaturePoint3', 40);
        $this->RegisterPropertyInteger('TemperatureColor3', 16769126);    // #FFE066
        $this->RegisterPropertyInteger('TemperaturePoint4', 50);
        $this->RegisterPropertyInteger('TemperatureColor4', 16752412);    // #FF9F1C
        $this->RegisterPropertyInteger('TemperaturePoint5', 60);
        $this->RegisterPropertyInteger('TemperatureColor5', 15746116);    // #F04444
        $this->RegisterPropertyInteger('TemperaturePoint6', 70);
        $this->RegisterPropertyInteger('TemperatureColor6', 16711680);    // #FF0000

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

        // Erweiterte Symcon-Darstellung: drei echte Heizstäbe im Warmwasserspeicher
        $this->RegisterPropertyInteger('HeaterRodCount', 0);
        $this->RegisterPropertyInteger('HeaterRod1', 0);
        $this->RegisterPropertyInteger('HeaterRod1Threshold', 1);
        $this->RegisterPropertyInteger('HeaterRod2', 0);
        $this->RegisterPropertyInteger('HeaterRod2Threshold', 1);
        $this->RegisterPropertyInteger('HeaterRod3', 0);
        $this->RegisterPropertyInteger('HeaterRod3Threshold', 1);

        // Solarthermie
        $this->RegisterPropertyBoolean('ThermalSolarAvailable', false);
        $this->RegisterPropertyInteger('ThermalSolarPump', 0);
        $this->RegisterPropertyInteger('ThermalSolarPumpSpeed', 0);
        $this->RegisterPropertyInteger('ThermalSolarPanelTemp', 0);
        $this->RegisterPropertyInteger('ThermalSolarFluxTemp', 0);
        $this->RegisterPropertyInteger('ThermalSolarReturnTemp', 0);

        // HTML-SDK Visualisierung
        $this->SetVisualizationType(1);

        $this->SetBuffer('RegisteredVariables', '[]');
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();

        $this->RegisterVariableMessages();

        // Wie im Energiefluss-Modul: Nach ApplyChanges die komplette
        // HTML-SDK-Seite im Browser neu laden.
        if (IPS_GetKernelRunlevel() === KR_READY) {
            $this->ReloadHtml();
        }
    }

    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void
    {
        try {
            if ($Message === VM_UPDATE && IPS_GetKernelRunlevel() === KR_READY) {
                $this->UpdateVisualizationValue(
                    json_encode(
                        [
                            'type' => 'update',
                            'data' => $this->BuildVisualizationData()
                        ],
                        JSON_THROW_ON_ERROR
                        | JSON_UNESCAPED_UNICODE
                        | JSON_UNESCAPED_SLASHES
                    )
                );
            }
        } catch (Throwable $e) {
            $this->LogMessage('MessageSink: ' . $e->getMessage(), KL_ERROR);
        }
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        if ($Ident !== 'SetControlMode') {
            throw new InvalidArgumentException('Unbekannte Aktion: ' . $Ident);
        }

        $payload = is_string($Value) ? json_decode($Value, true) : $Value;
        if (!is_array($payload)) {
            throw new InvalidArgumentException('Ungültige Steuerungsdaten.');
        }

        $function = (string) ($payload['function'] ?? '');
        $value = $payload['value'] ?? null;

        $propertyMap = [
            'heating'  => 'HeatingControlVariable',
            'hotwater' => 'HotWaterControlVariable',
            'cooling'  => 'CoolingControlVariable'
        ];

        if (!isset($propertyMap[$function])) {
            throw new InvalidArgumentException('Unbekannte Steuerfunktion.');
        }

        $variableId = $this->ReadPropertyInteger($propertyMap[$function]);
        if ($variableId <= 0 || !IPS_VariableExists($variableId)) {
            throw new RuntimeException('Für diese Funktion ist keine gültige Steuervariable konfiguriert.');
        }

        $variable = IPS_GetVariable($variableId);
        $typedValue = $this->CastValueForVariableType($value, (int) $variable['VariableType']);

        $allowedValues = $this->GetProfileAssociationValues($variableId);
        if ($allowedValues !== [] && !$this->ValueInList($typedValue, $allowedValues)) {
            throw new InvalidArgumentException('Der gewünschte Modus ist im Variablenprofil nicht definiert.');
        }

        $actionId = (int) ($variable['VariableCustomAction'] ?: $variable['VariableAction']);
        if ($actionId > 0) {
            \RequestAction($variableId, $typedValue);
        } else {
            SetValue($variableId, $typedValue);
        }

        /*
         * Kein kompletter HTML-Reload nötig.
         * Die geänderte Variable löst VM_UPDATE aus und MessageSink()
         * überträgt die neuen Zustände direkt an die bestehende Card.
         */
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

                    ['type' => 'Label', 'caption' => 'Betriebsstatus'],
                    $this->VariableGrid([
                        ['caption' => 'Betriebsstatus (Integer)', 'name' => 'OperatingStatusVariable']
                    ]),
                    [
                        'type'  => 'RowLayout',
                        'items' => [
                            ['type' => 'ValidationTextBox', 'name' => 'OperatingStatusHeatingValues', 'caption' => 'Heizen – Statuswerte'],
                            ['type' => 'ValidationTextBox', 'name' => 'OperatingStatusHotWaterValues', 'caption' => 'Warmwasser – Statuswerte'],
                            ['type' => 'ValidationTextBox', 'name' => 'OperatingStatusCoolingValues', 'caption' => 'Kühlen – Statuswerte'],
                            ['type' => 'ValidationTextBox', 'name' => 'OperatingStatusDefrostValues', 'caption' => 'Abtauen – Statuswerte']
                        ]
                    ],

                    ['type' => 'Label', 'caption' => 'Betriebsarten und Steuerung'],
                    $this->VariableGrid([
                        ['caption' => 'Betriebsart Heizen (Integer)', 'name' => 'HeatingControlVariable'],
                        ['caption' => 'Betriebsart Warmwasser (Integer)', 'name' => 'HotWaterControlVariable'],
                        ['caption' => 'Betriebsart Kühlen (Integer)', 'name' => 'CoolingControlVariable']
                    ]),

                    ['type' => 'Label', 'caption' => 'Betriebszustände'],
                    $this->VariableGrid([
                        ['caption' => 'Wärmepumpe Ein/Aus', 'name' => 'HeatingPumpStatusOnOff'],
                        ['caption' => 'Nachtbetrieb aktiv', 'name' => 'HeatingPumpNightMode'],
                        ['caption' => 'Energiesparbetrieb aktiv', 'name' => 'HeatingPumpEnergySaveMode'],
                        ['caption' => 'Partybetrieb aktiv', 'name' => 'HeatingPumpPartyMode'],
                        ['caption' => 'Zusatzheizung aktiv', 'name' => 'AdditionalHeating'],
                        ['caption' => 'Warnung aktiv', 'name' => 'Warning'],
                        ['caption' => 'Fehler aktiv', 'name' => 'Error']
                    ]),

                    ['type' => 'Label', 'caption' => 'Verdichter'],
                    $this->VariableGrid([
                        ['caption' => 'Verdichter aktiv', 'name' => 'CompressorRunning'],
                        ['caption' => 'Verdichterdrehzahl', 'name' => 'CompressorValue']
                    ]),

                    ['type' => 'Label', 'caption' => 'Primärquelle'],
                    $this->VariableGrid([
                        ['caption' => 'Quelle Eingang (Sole-/Wasser-WP)', 'name' => 'TemperatureGroundWaterIn'],
                        ['caption' => 'Quelle Ausgang (Sole-/Wasser-WP)', 'name' => 'TemperatureGroundWaterOut'],
                        ['caption' => 'Lüfterdrehzahl / Lüfter aktiv', 'name' => 'FanSpeed']
                    ]),
                    ['type' => 'Label', 'caption' => 'Temperaturen'],
                    $this->VariableGrid([
                        ['caption' => 'Außentemperatur', 'name' => 'OutdoorTemperature'],
                        ['caption' => 'WP Vorlauf', 'name' => 'SupplyTemperature']
                    ]),

                    ['type' => 'Label', 'caption' => 'Raumtemperaturen'],
                    $this->VariableGrid([
                        ['caption' => 'Raumtemperatur Normal', 'name' => 'AmbientTemperatureNormal'],
                        ['caption' => 'Raumtemperatur Ist (Fallback)', 'name' => 'AmbientTemperatureActual'],
                        ['caption' => 'Raumtemperatur Reduziert', 'name' => 'AmbientTemperatureReduced'],
                        ['caption' => 'Raumtemperatur Party', 'name' => 'AmbientTemperatureParty']
                    ]),

                    ['type' => 'Label', 'caption' => 'Kältekreis'],
                    $this->VariableGrid([
                        ['caption' => 'Niederdruck', 'name' => 'EvaporatorPressure'],
                        ['caption' => 'Verdampfungstemperatur', 'name' => 'EvaporatorTemperature'],
                        ['caption' => 'Hochdruck', 'name' => 'CondenserPressure'],
                        ['caption' => 'Kondensationstemperatur', 'name' => 'CondenserTemperature'],
                        ['caption' => 'Expansionsventil Öffnung', 'name' => 'ExpansionValveOpening']
                    ])

                ]
            ],

            [
                'type'    => 'ExpansionPanel',
                'caption' => 'Speicher',
                'items'   => [
                    [
                        'type'  => 'RowLayout',
                        'items' => [
                            ['type' => 'CheckBox', 'name' => 'TankHP', 'caption' => 'Pufferspeicher'],
                            ['type' => 'CheckBox', 'name' => 'TankWW', 'caption' => 'Warmwasserspeicher'],
                            ['type' => 'CheckBox', 'name' => 'LayeredChargeStorage', 'caption' => 'Schichtspeicher']
                        ]
                    ],
                    $this->VariableRow('Puffer oben', 'TankTempHPUp', 'Warmwasser oben', 'TankTempWWUp'),
                    $this->VariableRow('Puffer Mitte', 'TankTempHPMiddle', 'Warmwasser Mitte', 'TankTempWWMiddle'),
                    $this->VariableRow('Puffer unten', 'TankTempHPDown', 'Warmwasser unten', 'TankTempWWDown'),
                    $this->VariableRow('Speicherladepumpe aktiv', 'StorageChargingPumpRunning', 'Zirkulationspumpe aktiv', 'CirculatingPumpRunning'),
                    $this->VariableRow('Umschaltventil Warmwasser/Heizung', 'WWHeatingValve', 'Heizstab Puffer aktiv', 'HeaterRodHP'),
                    ['type' => 'Label', 'caption' => 'Heizstäbe Warmwasserspeicher'],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'Select',
                                'name' => 'HeaterRodCount',
                                'caption' => 'Anzahl Heizstäbe',
                                'options' => [
                                    ['caption' => 'Keine', 'value' => 0],
                                    ['caption' => '1', 'value' => 1],
                                    ['caption' => '2', 'value' => 2],
                                    ['caption' => '3', 'value' => 3]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            $this->VariableSelect('Heizstab 1', 'HeaterRod1'),
                            [
                                'type' => 'NumberSpinner',
                                'name' => 'HeaterRod1Threshold',
                                'caption' => 'Ein ab',
                                'digits' => 0
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            $this->VariableSelect('Heizstab 2', 'HeaterRod2'),
                            [
                                'type' => 'NumberSpinner',
                                'name' => 'HeaterRod2Threshold',
                                'caption' => 'Ein ab',
                                'digits' => 0
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            $this->VariableSelect('Heizstab 3', 'HeaterRod3'),
                            [
                                'type' => 'NumberSpinner',
                                'name' => 'HeaterRod3Threshold',
                                'caption' => 'Ein ab',
                                'digits' => 0
                            ]
                        ]
                    ]                ]
            ],

            $this->HeatingCircuitPanel(1),
            $this->HeatingCircuitPanel(2),
            $this->HeatingCircuitPanel(3),

            [
                'type'    => 'ExpansionPanel',
                'caption' => 'Temperaturfarben',
                'items'   => [
                    [
                        'type' => 'CheckBox',
                        'name' => 'UseCustomTemperatureColors',
                        'caption' => 'Eigene Temperaturfarben verwenden'
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'NumberSpinner',
                                'name' => 'TemperaturePoint1',
                                'caption' => 'Stufe 1 Temperatur',
                                'digits' => 0
                            ],
                            [
                                'type' => 'SelectColor',
                                'name' => 'TemperatureColor1',
                                'caption' => 'Farbe',
                                'allowTransparent' => false
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'NumberSpinner',
                                'name' => 'TemperaturePoint2',
                                'caption' => 'Stufe 2 Temperatur',
                                'digits' => 0
                            ],
                            [
                                'type' => 'SelectColor',
                                'name' => 'TemperatureColor2',
                                'caption' => 'Farbe',
                                'allowTransparent' => false
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'NumberSpinner',
                                'name' => 'TemperaturePoint3',
                                'caption' => 'Stufe 3 Temperatur',
                                'digits' => 0
                            ],
                            [
                                'type' => 'SelectColor',
                                'name' => 'TemperatureColor3',
                                'caption' => 'Farbe',
                                'allowTransparent' => false
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'NumberSpinner',
                                'name' => 'TemperaturePoint4',
                                'caption' => 'Stufe 4 Temperatur',
                                'digits' => 0
                            ],
                            [
                                'type' => 'SelectColor',
                                'name' => 'TemperatureColor4',
                                'caption' => 'Farbe',
                                'allowTransparent' => false
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'NumberSpinner',
                                'name' => 'TemperaturePoint5',
                                'caption' => 'Stufe 5 Temperatur',
                                'digits' => 0
                            ],
                            [
                                'type' => 'SelectColor',
                                'name' => 'TemperatureColor5',
                                'caption' => 'Farbe',
                                'allowTransparent' => false
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'NumberSpinner',
                                'name' => 'TemperaturePoint6',
                                'caption' => 'Stufe 6 Temperatur',
                                'digits' => 0
                            ],
                            [
                                'type' => 'SelectColor',
                                'name' => 'TemperatureColor6',
                                'caption' => 'Farbe',
                                'allowTransparent' => false
                            ]
                        ]
                    ]
                ]
            ],

            [
                'type'    => 'ExpansionPanel',
                'caption' => 'Solarthermie',
                'items'   => [
                    ['type' => 'CheckBox', 'name' => 'ThermalSolarAvailable', 'caption' => 'Solarthermie'],
                    $this->VariableRow('Solarpumpe aktiv', 'ThermalSolarPump', 'Solarpumpendrehzahl', 'ThermalSolarPumpSpeed'),
                    $this->VariableRow('Kollektortemperatur', 'ThermalSolarPanelTemp', 'Solar Vorlauf', 'ThermalSolarFluxTemp'),
                    $this->VariableRow('Solar Rücklauf', 'ThermalSolarReturnTemp', '', '')
                ]
            ]
        ];

        return json_encode(
            ['elements' => $elements],
            JSON_THROW_ON_ERROR
        );
    }

    public function UpdateVisualization(): void
    {
        $this->ReloadHtml();
    }

    public function ReloadHtml(): void
    {
        $this->UpdateVisualizationValue(
            json_encode(
                ['command' => 'reloadHtml'],
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
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
                    'caption' => 'Heizkreistyp',
                    'options' => [
                        ['caption' => 'Aus', 'value' => 'off'],
                        ['caption' => 'Fußbodenheizung', 'value' => 'underfloor'],
                        ['caption' => 'Heizkörper', 'value' => 'radiator']
                    ]
                ],
                $this->VariableRow(
                    'Heizkreispumpe aktiv',
                    'HeatingCircuitPumpRunning' . $suffix,
                    'Heizkreis Vorlauf',
                    'SupplyTemperatureHeating' . $suffix
                ),
                $this->VariableRow(
                    'Heizkreis Rücklauf',
                    'RefluxTemperatureHeating' . $suffix,
                    '',
                    ''
                )
            ]
        ];
    }

    private function VariableSelect(string $caption, string $name): array
    {
        return [
            'type'     => 'SelectVariable',
            'name'     => $name,
            'caption'  => $caption
        ];
    }

    private function VariableRow(string $caption1, string $name1, string $caption2, string $name2): array
    {
        $entries = [];

        foreach ([[$caption1, $name1], [$caption2, $name2]] as [$caption, $name]) {
            if ($name === '') {
                continue;
            }

            $entries[] = [
                'caption' => $caption,
                'name'    => $name
            ];
        }

        return $this->VariableGrid($entries);
    }

    private function VariableGrid(array $entries): array
    {
        $rows = [];

        foreach (array_chunk($entries, 3) as $chunk) {
            $columns = [];

            foreach ($chunk as $entry) {
                $columns[] = [
                    'type'  => 'ColumnLayout',
                    'items' => [
                        [
                            'type'    => 'SelectVariable',
                            'name'    => $entry['name'],
                            'caption' => $entry['caption']
                        ]
                    ]
                ];
            }

            while (count($columns) < 3) {
                $columns[] = [
                    'type'  => 'ColumnLayout',
                    'items' => []
                ];
            }

            $rows[] = [
                'type'  => 'RowLayout',
                'items' => $columns
            ];
        }

        return [
            'type'  => 'ColumnLayout',
            'items' => $rows
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

        $appJs = file_get_contents($files['js']);
        $svg = file_get_contents($files['svg']);
        $localizationRaw = file_get_contents($files['lang']);

        if ($appJs === false || $svg === false || $localizationRaw === false) {
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

        if (isset($localization['svgTexts']) && is_array($localization['svgTexts'])) {
            $localization['svgTexts']['expansionValve'] = 'Expansionsventil';
            $localization['svgTexts']['condenser'] = 'Kondensator';
        }

        $payloadJson = json_encode(
            [
                'config'       => $this->BuildCardConfig(),
                'data'         => $this->BuildVisualizationData(),
                'controls'     => $this->BuildControlData(),
                'svg'          => $svg,
                'localization' => $localization
            ],
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
            | JSON_THROW_ON_ERROR
        );

        $appJs = str_replace('</script>', '<\/script>', $appJs);

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

    #wp-mode-menu {
        position: fixed;
        z-index: 9999;
        display: none;
        min-width: 190px;
        max-width: 300px;
        padding: 6px;
        border-radius: 8px;
        background: color-mix(in srgb, Canvas 94%, transparent);
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.35);
        color: CanvasText;
        font: 14px Arial, sans-serif;
    }

    #wp-mode-menu .wp-mode-title {
        padding: 6px 8px;
        font-weight: 600;
        opacity: .8;
    }

    #wp-mode-menu button {
        display: block;
        width: 100%;
        margin: 2px 0;
        padding: 8px 10px;
        border: 0;
        border-radius: 6px;
        background: transparent;
        color: inherit;
        text-align: left;
        cursor: pointer;
    }

    #wp-mode-menu button:hover {
        background: rgba(127,127,127,.16);
    }

    #wp-mode-menu button.active {
        background: rgba(127,127,127,.34);
        font-weight: 600;
    }
</style>

<div id="wp-root">
    <div id="wp-error"></div>
    <heat-pump-card id="wp-card"></heat-pump-card>
    <div id="wp-mode-menu"></div>
</div>

<script>
{$appJs}
</script>

<script>
SymconHeatPump.init({$payloadJson});
</script>
HTML;
    }

    private function BuildCardConfig(): array
    {
        return [
            'title'                      => '',
            'heatingPumpType'            => $this->ReadPropertyString('HeatingPumpType'),

            'temperatureGroundWaterIn'   => $this->DataKey('TemperatureGroundWaterIn', 'temperatureGroundWaterIn'),
            'temperatureGroundWaterOut'  => $this->DataKey('TemperatureGroundWaterOut', 'temperatureGroundWaterOut'),

            'heatingPumpStatusOnOff'     => $this->DataKey('HeatingPumpStatusOnOff', 'heatingPumpStatusOnOff'),
            'heatingPumpHotWaterMode'    => $this->HasOperatingStatus() ? 'heatingPumpHotWaterMode' : $this->DataKey('HeatingPumpHotWaterMode', 'heatingPumpHotWaterMode'),
            'heatingPumpHeatingMode'     => $this->HasOperatingStatus() ? 'heatingPumpHeatingMode' : $this->DataKey('HeatingPumpHeatingMode', 'heatingPumpHeatingMode'),
            'heatingPumpCoolingMode'     => $this->HasOperatingStatus() ? 'heatingPumpCoolingMode' : $this->DataKey('HeatingPumpCoolingMode', 'heatingPumpCoolingMode'),
            'heatingPumpPartyMode'       => $this->DataKey('HeatingPumpPartyMode', 'heatingPumpPartyMode'),
            'heatingPumpEnergySaveMode'  => $this->DataKey('HeatingPumpEnergySaveMode', 'heatingPumpEnergySaveMode'),
            'heatingPumpNightMode'       => $this->DataKey('HeatingPumpNightMode', 'heatingPumpNightMode'),

            'warning'                    => $this->DataKey('Warning', 'warning'),
            'error'                      => $this->DataKey('Error', 'error'),
            'defrostMode'                => $this->HasOperatingStatus() ? 'defrostMode' : $this->DataKey('DefrostMode', 'defrostMode'),
            'additionalHeating'          => $this->DataKey('AdditionalHeating', 'additionalHeating'),

            'outdoorTemperature'         => $this->DataKey('OutdoorTemperature', 'outdoorTemperature'),
            'ambientTemperatureNormal'   => $this->DataKeyWithFallback('AmbientTemperatureNormal', 'AmbientTemperatureActual', 'ambientTemperatureNormal'),
            'ambientTemperatureReduced'  => $this->DataKey('AmbientTemperatureReduced', 'ambientTemperatureReduced'),
            'ambientTemperatureParty'    => $this->DataKey('AmbientTemperatureParty', 'ambientTemperatureParty'),
            'supplyTemperature'          => $this->DataKey('SupplyTemperature', 'supplyTemperature'),

            'hpRunning'                  => ($this->ReadPropertyInteger('FanSpeed') > 0 || $this->HasOperatingStatus()) ? 'hpRunning' : $this->DataKey('HpRunning', 'hpRunning'),
            'fanSpeed'                   => $this->DataKey('FanSpeed', 'fanSpeed'),
            'compressorRunning'          => $this->DataKey('CompressorRunning', 'compressorRunning'),
            'circulatingPumpRunning'     => $this->DataKey('CirculatingPumpRunning', 'circulatingPumpRunning'),
            'storageChargingPumpRunning' => $this->DataKey('StorageChargingPumpRunning', 'storageChargingPumpRunning'),

            'tankHP'                     => $this->ReadPropertyBoolean('TankHP'),
            'tankTempHPUp'               => $this->DataKey('TankTempHPUp', 'tankTempHPUp'),
            'tankTempHPMiddle'           => $this->DataKey('TankTempHPMiddle', 'tankTempHPMiddle'),
            'tankTempHPDown'             => $this->DataKey('TankTempHPDown', 'tankTempHPDown'),

            'tankWW'                     => $this->ReadPropertyBoolean('TankWW'),
            'layeredChargeStorage'       => $this->ReadPropertyBoolean('LayeredChargeStorage'),
            'tankTempWWUp'               => $this->DataKey('TankTempWWUp', 'tankTempWWUp'),
            'tankTempWWMiddle'           => $this->DataKey('TankTempWWMiddle', 'tankTempWWMiddle'),
            'tankTempWWDown'             => $this->DataKey('TankTempWWDown', 'tankTempWWDown'),

            'heatingCircuitType1'        => $this->ReadPropertyString('HeatingCircuitType1'),
            'heatingCircuitPumpRunning'  => $this->DataKey('HeatingCircuitPumpRunning1', 'heatingCircuitPumpRunning'),
            'supplyTemperatureHeating'   => $this->DataKey('SupplyTemperatureHeating1', 'supplyTemperatureHeating'),
            'refluxTemperatureHeating'   => $this->DataKey('RefluxTemperatureHeating1', 'refluxTemperatureHeating'),

            'heatingCircuitType2'        => $this->ReadPropertyString('HeatingCircuitType2'),
            'heatingCircuitPumpRunning2' => $this->DataKey('HeatingCircuitPumpRunning2', 'heatingCircuitPumpRunning2'),
            'supplyTemperatureHeating2'  => $this->DataKey('SupplyTemperatureHeating2', 'supplyTemperatureHeating2'),
            'refluxTemperatureHeating2'  => $this->DataKey('RefluxTemperatureHeating2', 'refluxTemperatureHeating2'),

            'heatingCircuitType3'        => $this->ReadPropertyString('HeatingCircuitType3'),
            'heatingCircuitPumpRunning3' => $this->DataKey('HeatingCircuitPumpRunning3', 'heatingCircuitPumpRunning3'),
            'supplyTemperatureHeating3'  => $this->DataKey('SupplyTemperatureHeating3', 'supplyTemperatureHeating3'),
            'refluxTemperatureHeating3'  => $this->DataKey('RefluxTemperatureHeating3', 'refluxTemperatureHeating3'),

            'evaporatorPressure'         => $this->DataKey('EvaporatorPressure', 'evaporatorPressure'),
            'evaporatorTemperature'      => $this->DataKey('EvaporatorTemperature', 'evaporatorTemperature'),
            'condenserPressure'          => $this->DataKey('CondenserPressure', 'condenserPressure'),
            'condenserTemperature'       => $this->DataKey('CondenserTemperature', 'condenserTemperature'),
            'expansionValveOpening'      => $this->DataKey('ExpansionValveOpening', 'expansionValveOpening'),
            'compressorValue'            => $this->DataKey('CompressorValue', 'compressorValue'),

            'wwHeatingValve'             => $this->DataKey('WWHeatingValve', 'wwHeatingValve'),
            'heaterRodWW'                => $this->DataKey('HeaterRodWW', 'heaterRodWW'),
            'heaterRodHP'                => $this->DataKey('HeaterRodHP', 'heaterRodHP'),
            'heaterRodLevel1'            => $this->DataKey('HeaterRodLevel1', 'heaterRodLevel1'),
            'heaterRodLevel2'            => $this->DataKey('HeaterRodLevel2', 'heaterRodLevel2'),

            'heaterRodCount'             => $this->ReadPropertyInteger('HeaterRodCount'),
            'heaterRod1'                 => $this->DataKey('HeaterRod1', 'heaterRod1'),
            'heaterRod1Threshold'        => $this->ReadPropertyInteger('HeaterRod1Threshold'),
            'heaterRod2'                 => $this->DataKey('HeaterRod2', 'heaterRod2'),
            'heaterRod2Threshold'        => $this->ReadPropertyInteger('HeaterRod2Threshold'),
            'heaterRod3'                 => $this->DataKey('HeaterRod3', 'heaterRod3'),
            'heaterRod3Threshold'        => $this->ReadPropertyInteger('HeaterRod3Threshold'),

            'useCustomTemperatureColors' => $this->ReadPropertyBoolean('UseCustomTemperatureColors'),
            'temperatureColorScale'      => [
                ['temperature' => $this->ReadPropertyInteger('TemperaturePoint1'), 'color' => $this->ReadPropertyInteger('TemperatureColor1')],
                ['temperature' => $this->ReadPropertyInteger('TemperaturePoint2'), 'color' => $this->ReadPropertyInteger('TemperatureColor2')],
                ['temperature' => $this->ReadPropertyInteger('TemperaturePoint3'), 'color' => $this->ReadPropertyInteger('TemperatureColor3')],
                ['temperature' => $this->ReadPropertyInteger('TemperaturePoint4'), 'color' => $this->ReadPropertyInteger('TemperatureColor4')],
                ['temperature' => $this->ReadPropertyInteger('TemperaturePoint5'), 'color' => $this->ReadPropertyInteger('TemperatureColor5')],
                ['temperature' => $this->ReadPropertyInteger('TemperaturePoint6'), 'color' => $this->ReadPropertyInteger('TemperatureColor6')]
            ],

            'thermalSolarAvailable'      => $this->ReadPropertyBoolean('ThermalSolarAvailable'),
            'thermalSolarPump'           => $this->DataKey('ThermalSolarPump', 'thermalSolarPump'),
            'thermalSolarPumpSpeed'      => $this->DataKey('ThermalSolarPumpSpeed', 'thermalSolarPumpSpeed'),
            'thermalSolarPanelTemp'      => $this->DataKey('ThermalSolarPanelTemp', 'thermalSolarPanelTemp'),
            'thermalSolarFluxTemp'       => $this->DataKey('ThermalSolarFluxTemp', 'thermalSolarFluxTemp'),
            'thermalSolarReturnTemp'     => $this->DataKey('ThermalSolarReturnTemp', 'thermalSolarReturnTemp')
        ];
    }

    private function BuildVisualizationData(): array
    {
        $data = [];

        $map = [
            'temperatureGroundWaterIn'   => 'TemperatureGroundWaterIn',
            'temperatureGroundWaterOut'  => 'TemperatureGroundWaterOut',
            'heatingPumpStatusOnOff'     => 'HeatingPumpStatusOnOff',
            'heatingPumpHotWaterMode'    => 'HeatingPumpHotWaterMode',
            'heatingPumpHeatingMode'     => 'HeatingPumpHeatingMode',
            'heatingPumpCoolingMode'     => 'HeatingPumpCoolingMode',
            'heatingPumpPartyMode'       => 'HeatingPumpPartyMode',
            'heatingPumpEnergySaveMode'  => 'HeatingPumpEnergySaveMode',
            'heatingPumpNightMode'       => 'HeatingPumpNightMode',
            'warning'                    => 'Warning',
            'error'                      => 'Error',
            'defrostMode'                => 'DefrostMode',
            'additionalHeating'          => 'AdditionalHeating',
            'outdoorTemperature'         => 'OutdoorTemperature',
            'ambientTemperatureNormal'   => 'AmbientTemperatureNormal',
            'ambientTemperatureReduced'  => 'AmbientTemperatureReduced',
            'ambientTemperatureParty'    => 'AmbientTemperatureParty',
            'supplyTemperature'          => 'SupplyTemperature',
            'hpRunning'                  => 'HpRunning',
            'fanSpeed'                   => 'FanSpeed',
            'compressorRunning'          => 'CompressorRunning',
            'circulatingPumpRunning'     => 'CirculatingPumpRunning',
            'storageChargingPumpRunning' => 'StorageChargingPumpRunning',
            'tankTempHPUp'               => 'TankTempHPUp',
            'tankTempHPMiddle'           => 'TankTempHPMiddle',
            'tankTempHPDown'             => 'TankTempHPDown',
            'tankTempWWUp'               => 'TankTempWWUp',
            'tankTempWWMiddle'           => 'TankTempWWMiddle',
            'tankTempWWDown'             => 'TankTempWWDown',
            'heatingCircuitPumpRunning'  => 'HeatingCircuitPumpRunning1',
            'supplyTemperatureHeating'   => 'SupplyTemperatureHeating1',
            'refluxTemperatureHeating'   => 'RefluxTemperatureHeating1',
            'heatingCircuitPumpRunning2' => 'HeatingCircuitPumpRunning2',
            'supplyTemperatureHeating2'  => 'SupplyTemperatureHeating2',
            'refluxTemperatureHeating2'  => 'RefluxTemperatureHeating2',
            'heatingCircuitPumpRunning3' => 'HeatingCircuitPumpRunning3',
            'supplyTemperatureHeating3'  => 'SupplyTemperatureHeating3',
            'refluxTemperatureHeating3'  => 'RefluxTemperatureHeating3',
            'evaporatorPressure'         => 'EvaporatorPressure',
            'evaporatorTemperature'      => 'EvaporatorTemperature',
            'condenserPressure'          => 'CondenserPressure',
            'condenserTemperature'       => 'CondenserTemperature',
            'expansionValveOpening'      => 'ExpansionValveOpening',
            'compressorValue'            => 'CompressorValue',
            'wwHeatingValve'             => 'WWHeatingValve',
            'heaterRodWW'                => 'HeaterRodWW',
            'heaterRodHP'                => 'HeaterRodHP',
            'heaterRodLevel1'            => 'HeaterRodLevel1',
            'heaterRodLevel2'            => 'HeaterRodLevel2',
            'heaterRod1'                 => 'HeaterRod1',
            'heaterRod2'                 => 'HeaterRod2',
            'heaterRod3'                 => 'HeaterRod3',
            'thermalSolarPump'           => 'ThermalSolarPump',
            'thermalSolarPumpSpeed'      => 'ThermalSolarPumpSpeed',
            'thermalSolarPanelTemp'      => 'ThermalSolarPanelTemp',
            'thermalSolarFluxTemp'       => 'ThermalSolarFluxTemp',
            'thermalSolarReturnTemp'     => 'ThermalSolarReturnTemp'
        ];

        foreach ($map as $key => $property) {
            $this->AddVariableData(
                $data,
                $key,
                $property,
                in_array($property, self::BINARY_PROPERTIES, true)
            );
        }

        if (!isset($data['ambientTemperatureNormal'])) {
            $this->AddVariableData(
                $data,
                'ambientTemperatureNormal',
                'AmbientTemperatureActual',
                false
            );
        }

        if ($this->HasOperatingStatus()) {
            $statusValue = GetValue($this->ReadPropertyInteger('OperatingStatusVariable'));

            $data['heatingPumpHeatingMode'] = $this->BinaryData(
                $this->ValueMatchesCsv($statusValue, $this->ReadPropertyString('OperatingStatusHeatingValues'))
            );
            $data['heatingPumpHotWaterMode'] = $this->BinaryData(
                $this->ValueMatchesCsv($statusValue, $this->ReadPropertyString('OperatingStatusHotWaterValues'))
            );
            $data['heatingPumpCoolingMode'] = $this->BinaryData(
                $this->ValueMatchesCsv($statusValue, $this->ReadPropertyString('OperatingStatusCoolingValues'))
            );
            $data['defrostMode'] = $this->BinaryData(
                $this->ValueMatchesCsv($statusValue, $this->ReadPropertyString('OperatingStatusDefrostValues'))
            );
        }

        $fanSpeedId = $this->ReadPropertyInteger('FanSpeed');
        if ($fanSpeedId > 0 && IPS_VariableExists($fanSpeedId)) {
            $data['hpRunning'] = $this->BinaryData((float) GetValue($fanSpeedId) > 0.0);
        } elseif ($this->HasOperatingStatus()) {
            $statusValue = GetValue($this->ReadPropertyInteger('OperatingStatusVariable'));
            $data['hpRunning'] = $this->BinaryData(
                $this->ValueMatchesCsv($statusValue, $this->ReadPropertyString('FanActiveStatusValues'))
            );
        }

        return $data;
    }

    private function AddVariableData(
        array &$data,
        string $key,
        string $property,
        bool $binary
    ): void {
        $variableId = $this->ReadPropertyInteger($property);

        if ($variableId <= 0 || !IPS_VariableExists($variableId)) {
            return;
        }

        $value = GetValue($variableId);

        $data[$key] = [
            'value'  => $binary ? $this->NormalizeBinaryValue($value) : $value,
            'unit'   => $binary ? '' : $this->GetVariableUnit($variableId),
            'binary' => $binary
        ];
    }

    private function BinaryData(bool $value): array
    {
        return [
            'value'  => $value,
            'unit'   => '',
            'binary' => true
        ];
    }

    private function DataKey(string $property, string $key): string
    {
        $variableId = $this->ReadPropertyInteger($property);

        return $variableId > 0 && IPS_VariableExists($variableId) ? $key : '';
    }

    private function DataKeyWithFallback(
        string $property,
        string $fallbackProperty,
        string $key
    ): string {
        return $this->DataKey($property, $key) !== ''
            || $this->DataKey($fallbackProperty, $key) !== ''
                ? $key
                : '';
    }

    private function HasOperatingStatus(): bool
    {
        $variableId = $this->ReadPropertyInteger('OperatingStatusVariable');
        return $variableId > 0 && IPS_VariableExists($variableId);
    }



    private function ValueMatchesCsv(mixed $value, string $csv): bool
    {
        $items = array_filter(
            array_map('trim', explode(',', $csv)),
            static fn(string $item): bool => $item !== ''
        );

        foreach ($items as $item) {
            if (is_numeric($value) && is_numeric($item)) {
                if ((float) $value === (float) $item) {
                    return true;
                }
            } elseif ((string) $value === $item) {
                return true;
            }
        }

        return false;
    }

    private function BuildControlData(): array
    {
        return [
            'hasOperatingStatus' => $this->HasOperatingStatus(),
            'heating'            => $this->BuildControlInfo('HeatingControlVariable'),
            'hotwater'           => $this->BuildControlInfo('HotWaterControlVariable'),
            'cooling'            => $this->BuildControlInfo('CoolingControlVariable')
        ];
    }

    private function BuildControlInfo(string $property): array
    {
        $variableId = $this->ReadPropertyInteger($property);

        if ($variableId <= 0 || !IPS_VariableExists($variableId)) {
            return [
                'configured'   => false,
                'variableId'   => 0,
                'currentValue' => null,
                'enabled'      => false,
                'options'      => []
            ];
        }

        $currentValue = GetValue($variableId);
        $profileName = $this->GetVariableProfileName($variableId);
        $options = [];
        $offValues = [];

        if ($profileName !== '' && IPS_VariableProfileExists($profileName)) {
            $profile = IPS_GetVariableProfile($profileName);

            foreach (($profile['Associations'] ?? []) as $association) {
                $associationValue = $association['Value'];
                $associationName = (string) $association['Name'];

                $options[] = [
                    'value' => $associationValue,
                    'name'  => $associationName
                ];

                if (preg_match('/(^|\s)(aus|off|disabled|deaktiviert)(\s|$)/iu', trim($associationName)) === 1) {
                    $offValues[] = $associationValue;
                }
            }
        }

        return [
            'configured'   => true,
            'variableId'   => $variableId,
            'currentValue' => $currentValue,
            'enabled'      => $offValues === [] ? true : !$this->ValueInList($currentValue, $offValues),
            'options'      => $options
        ];
    }

    private function GetProfileAssociationValues(int $variableId): array
    {
        $profileName = $this->GetVariableProfileName($variableId);
        if ($profileName === '' || !IPS_VariableProfileExists($profileName)) {
            return [];
        }

        $profile = IPS_GetVariableProfile($profileName);

        return array_map(
            static fn(array $association): mixed => $association['Value'],
            $profile['Associations'] ?? []
        );
    }

    private function GetVariableProfileName(int $variableId): string
    {
        $variable = IPS_GetVariable($variableId);

        return $variable['VariableCustomProfile'] !== ''
            ? (string) $variable['VariableCustomProfile']
            : (string) $variable['VariableProfile'];
    }

    private function ValueInList(mixed $value, array $values): bool
    {
        foreach ($values as $candidate) {
            if (is_numeric($value) && is_numeric($candidate)) {
                if ((float) $value === (float) $candidate) {
                    return true;
                }
            } elseif ((string) $value === (string) $candidate) {
                return true;
            }
        }

        return false;
    }

    private function CastValueForVariableType(mixed $value, int $variableType): mixed
    {
        return match ($variableType) {
            0       => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ((int) $value !== 0),
            1       => (int) $value,
            2       => (float) $value,
            3       => (string) $value,
            default => $value
        };
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
