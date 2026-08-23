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

        'AdditionalValue000',
        'AdditionalValue001',
        'AdditionalValue002',
        'AdditionalValue003',
        'AdditionalValue004',
        'AdditionalValue005',
        'AdditionalValue006',
        'AdditionalValue007',
        'AdditionalValue008',
        'AdditionalValue009',
        'AdditionalValue010',
        'AdditionalValue011',
        'AdditionalValue012',

        'OperatingStatusVariable',
        'HeatingControlVariable',
        'HotWaterControlVariable',
        'CoolingControlVariable',
        'WarmWaterSetpointVariable',
        'HeatingTemperatureCorrectionVariable',
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

        // Direkt bedienbare Sollwerte in der oberen Icon-Leiste
        $this->RegisterPropertyInteger('WarmWaterSetpointVariable', 0);
        $this->RegisterPropertyInteger('HeatingTemperatureCorrectionVariable', 0);

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
        $this->RegisterPropertyInteger('TemperaturePoint1', 15);
        $this->RegisterPropertyInteger('TemperatureColor1', 26316);       // #0066CC
        $this->RegisterPropertyInteger('TemperaturePoint2', 20);
        $this->RegisterPropertyInteger('TemperatureColor2', 2730472);       // #29A9E8
        $this->RegisterPropertyInteger('TemperaturePoint3', 25);
        $this->RegisterPropertyInteger('TemperatureColor3', 3790293);       // #39D5D5
        $this->RegisterPropertyInteger('TemperaturePoint4', 30);
        $this->RegisterPropertyInteger('TemperatureColor4', 16765286);       // #FFD166
        $this->RegisterPropertyInteger('TemperaturePoint5', 35);
        $this->RegisterPropertyInteger('TemperatureColor5', 16769126);       // #FFE066
        $this->RegisterPropertyInteger('TemperaturePoint6', 40);
        $this->RegisterPropertyInteger('TemperatureColor6', 16761395);       // #FFC233
        $this->RegisterPropertyInteger('TemperaturePoint7', 45);
        $this->RegisterPropertyInteger('TemperatureColor7', 16750616);       // #FF9818
        $this->RegisterPropertyInteger('TemperaturePoint8', 50);
        $this->RegisterPropertyInteger('TemperatureColor8', 16734744);       // #FF5A18
        $this->RegisterPropertyInteger('TemperaturePoint9', 55);
        $this->RegisterPropertyInteger('TemperatureColor9', 15018795);       // #E52B2B
        $this->RegisterPropertyInteger('TemperaturePoint10', 60);
        $this->RegisterPropertyInteger('TemperatureColor10', 16711680);       // #FF0000

        // Heizkreis 1
        // Sonderdarstellung bei genau einem Heizkreis:
        // Heizkreiswerte bei Warmwasserbereitung einfrieren und aktuelle
        // Vor-/Rücklauftemperatur am Warmwasserspeicher anzeigen.
        $this->RegisterPropertyBoolean('SingleCircuitHotWaterTemperatureSwitch', true);
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

        // Frei belegbare Zusatzwerte der SVG (000 ... 012)
        $this->RegisterPropertyString('AdditionalLabel000', '');
        $this->RegisterPropertyInteger('AdditionalValue000', 0);
        $this->RegisterPropertyString('AdditionalLabel001', '');
        $this->RegisterPropertyInteger('AdditionalValue001', 0);
        $this->RegisterPropertyString('AdditionalLabel002', '');
        $this->RegisterPropertyInteger('AdditionalValue002', 0);
        $this->RegisterPropertyString('AdditionalLabel003', '');
        $this->RegisterPropertyInteger('AdditionalValue003', 0);
        $this->RegisterPropertyString('AdditionalLabel004', '');
        $this->RegisterPropertyInteger('AdditionalValue004', 0);
        $this->RegisterPropertyString('AdditionalLabel005', '');
        $this->RegisterPropertyInteger('AdditionalValue005', 0);
        $this->RegisterPropertyString('AdditionalLabel006', '');
        $this->RegisterPropertyInteger('AdditionalValue006', 0);
        $this->RegisterPropertyString('AdditionalLabel007', '');
        $this->RegisterPropertyInteger('AdditionalValue007', 0);
        $this->RegisterPropertyString('AdditionalLabel008', '');
        $this->RegisterPropertyInteger('AdditionalValue008', 0);
        $this->RegisterPropertyString('AdditionalLabel009', '');
        $this->RegisterPropertyInteger('AdditionalValue009', 0);
        $this->RegisterPropertyString('AdditionalLabel010', '');
        $this->RegisterPropertyInteger('AdditionalValue010', 0);
        $this->RegisterPropertyString('AdditionalLabel011', '');
        $this->RegisterPropertyInteger('AdditionalValue011', 0);
        $this->RegisterPropertyString('AdditionalLabel012', '');
        $this->RegisterPropertyInteger('AdditionalValue012', 0);

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
                            'type'     => 'update',
                            'data'     => $this->BuildVisualizationData(),
                            'controls' => $this->BuildControlData()
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
        if (!in_array($Ident, ['SetControlMode', 'SetNumericControl'], true)) {
            throw new InvalidArgumentException('Unbekannte Aktion: ' . $Ident);
        }

        $payload = is_string($Value) ? json_decode($Value, true) : $Value;
        if (!is_array($payload)) {
            throw new InvalidArgumentException('Ungültige Steuerungsdaten.');
        }

        $function = (string) ($payload['function'] ?? '');
        $value = $payload['value'] ?? null;

        if ($Ident === 'SetControlMode') {
            $propertyMap = [
                'heating'  => 'HeatingControlVariable',
                'hotwater' => 'HotWaterControlVariable',
                'cooling'  => 'CoolingControlVariable'
            ];
        } else {
            $propertyMap = [
                'warmWaterSetpoint'            => 'WarmWaterSetpointVariable',
                'heatingTemperatureCorrection' => 'HeatingTemperatureCorrectionVariable'
            ];
        }

        if (!isset($propertyMap[$function])) {
            throw new InvalidArgumentException('Unbekannte Steuerfunktion.');
        }

        $variableId = $this->ReadPropertyInteger($propertyMap[$function]);
        if ($variableId <= 0 || !IPS_VariableExists($variableId)) {
            throw new RuntimeException('Für diese Funktion ist keine gültige Steuervariable konfiguriert.');
        }

        $variable = IPS_GetVariable($variableId);
        $typedValue = $this->CastValueForVariableType(
            $value,
            (int) $variable['VariableType']
        );

        if ($Ident === 'SetControlMode') {
            $allowedValues = $this->GetProfileAssociationValues($variableId);
            if ($allowedValues !== [] && !$this->ValueInList($typedValue, $allowedValues)) {
                throw new InvalidArgumentException(
                    'Der gewünschte Modus ist im Variablenprofil nicht definiert.'
                );
            }
        }

        $actionId = (int) (
            $variable['VariableCustomAction']
            ?: $variable['VariableAction']
        );

        if ($actionId > 0) {
            \RequestAction($variableId, $typedValue);
        } else {
            SetValue($variableId, $typedValue);
        }
    }

    public function GetVisualizationTile(): string
    {
        if (!$this->ResourcesAvailable()) {
            return '<div style="padding:16px;font-family:sans-serif;color:#c62828;">'
                . 'Wärmepumpen-Ressource fehlt. Erwartet wird: heat-pump/heat-pump-card/heat-pump.svg.'
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
                        ['caption' => 'Raumtemperatur Reduziert', 'name' => 'AmbientTemperatureReduced'],

                        ['caption' => 'Partybetrieb aktiv', 'name' => 'HeatingPumpPartyMode'],
                        ['caption' => 'Raumtemperatur Party', 'name' => 'AmbientTemperatureParty'],

                        ['caption' => 'Warmwasser-Solltemperatur', 'name' => 'WarmWaterSetpointVariable'],
                        ['caption' => 'Heiztemperaturkorrektur', 'name' => 'HeatingTemperatureCorrectionVariable'],

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

                    ['type' => 'Label', 'caption' => 'Raumtemperatur Normalbetrieb'],
                    $this->VariableGrid([
                        ['caption' => 'Raumtemperatur Normal', 'name' => 'AmbientTemperatureNormal'],
                        ['caption' => 'Raumtemperatur Ist (Fallback)', 'name' => 'AmbientTemperatureActual']
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
                        'type' => 'Button',
                        'caption' => 'Eigene Farben auf Standardvorlage zurücksetzen',
                        'onClick' => <<<'PHP'
$defaults = [
    [15, 26316],
    [20, 2730472],
    [25, 3782101],
    [30, 16765286],
    [35, 16769126],
    [40, 16761395],
    [45, 16750616],
    [50, 16734744],
    [55, 15084331],
    [60, 16711680]
];

foreach ($defaults as $index => $item) {
    $number = $index + 1;
    IPS_SetProperty($id, 'TemperaturePoint' . $number, $item[0]);
    IPS_SetProperty($id, 'TemperatureColor' . $number, $item[1]);
}

IPS_ApplyChanges($id);
echo 'Temperaturfarben wurden auf die Standardvorlage zurückgesetzt.';
PHP
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
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'NumberSpinner',
                                'name' => 'TemperaturePoint7',
                                'caption' => 'Stufe 7 Temperatur',
                                'digits' => 0
                            ],
                            [
                                'type' => 'SelectColor',
                                'name' => 'TemperatureColor7',
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
                                'name' => 'TemperaturePoint8',
                                'caption' => 'Stufe 8 Temperatur',
                                'digits' => 0
                            ],
                            [
                                'type' => 'SelectColor',
                                'name' => 'TemperatureColor8',
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
                                'name' => 'TemperaturePoint9',
                                'caption' => 'Stufe 9 Temperatur',
                                'digits' => 0
                            ],
                            [
                                'type' => 'SelectColor',
                                'name' => 'TemperatureColor9',
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
                                'name' => 'TemperaturePoint10',
                                'caption' => 'Stufe 10 Temperatur',
                                'digits' => 0
                            ],
                            [
                                'type' => 'SelectColor',
                                'name' => 'TemperatureColor10',
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
,

            [
                'type'    => 'ExpansionPanel',
                'caption' => 'Zusatzwerte',
                'items'   => [
                    [
                        'type' => 'Label',
                        'caption' => 'Bis zu 13 zusätzliche Werte an den vorgesehenen Positionen der Grafik anzeigen.'
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'ValidationTextBox',
                                'name' => 'AdditionalLabel000',
                                'caption' => 'Bezeichnung 1'
                            ],
                            [
                                'type' => 'SelectVariable',
                                'name' => 'AdditionalValue000',
                                'caption' => 'Variable 1'
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'ValidationTextBox',
                                'name' => 'AdditionalLabel001',
                                'caption' => 'Bezeichnung 2'
                            ],
                            [
                                'type' => 'SelectVariable',
                                'name' => 'AdditionalValue001',
                                'caption' => 'Variable 2'
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'ValidationTextBox',
                                'name' => 'AdditionalLabel002',
                                'caption' => 'Bezeichnung 3'
                            ],
                            [
                                'type' => 'SelectVariable',
                                'name' => 'AdditionalValue002',
                                'caption' => 'Variable 3'
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'ValidationTextBox',
                                'name' => 'AdditionalLabel003',
                                'caption' => 'Bezeichnung 4'
                            ],
                            [
                                'type' => 'SelectVariable',
                                'name' => 'AdditionalValue003',
                                'caption' => 'Variable 4'
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'ValidationTextBox',
                                'name' => 'AdditionalLabel004',
                                'caption' => 'Bezeichnung 5'
                            ],
                            [
                                'type' => 'SelectVariable',
                                'name' => 'AdditionalValue004',
                                'caption' => 'Variable 5'
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'ValidationTextBox',
                                'name' => 'AdditionalLabel005',
                                'caption' => 'Bezeichnung 6'
                            ],
                            [
                                'type' => 'SelectVariable',
                                'name' => 'AdditionalValue005',
                                'caption' => 'Variable 6'
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'ValidationTextBox',
                                'name' => 'AdditionalLabel006',
                                'caption' => 'Bezeichnung 7'
                            ],
                            [
                                'type' => 'SelectVariable',
                                'name' => 'AdditionalValue006',
                                'caption' => 'Variable 7'
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'ValidationTextBox',
                                'name' => 'AdditionalLabel007',
                                'caption' => 'Bezeichnung 8'
                            ],
                            [
                                'type' => 'SelectVariable',
                                'name' => 'AdditionalValue007',
                                'caption' => 'Variable 8'
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'ValidationTextBox',
                                'name' => 'AdditionalLabel008',
                                'caption' => 'Bezeichnung 9'
                            ],
                            [
                                'type' => 'SelectVariable',
                                'name' => 'AdditionalValue008',
                                'caption' => 'Variable 9'
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'ValidationTextBox',
                                'name' => 'AdditionalLabel009',
                                'caption' => 'Bezeichnung 10'
                            ],
                            [
                                'type' => 'SelectVariable',
                                'name' => 'AdditionalValue009',
                                'caption' => 'Variable 10'
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'ValidationTextBox',
                                'name' => 'AdditionalLabel010',
                                'caption' => 'Bezeichnung 11'
                            ],
                            [
                                'type' => 'SelectVariable',
                                'name' => 'AdditionalValue010',
                                'caption' => 'Variable 11'
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'ValidationTextBox',
                                'name' => 'AdditionalLabel011',
                                'caption' => 'Bezeichnung 12'
                            ],
                            [
                                'type' => 'SelectVariable',
                                'name' => 'AdditionalValue011',
                                'caption' => 'Variable 12'
                            ]
                        ]
                    ],
                    [
                        'type' => 'RowLayout',
                        'items' => [
                            [
                                'type' => 'ValidationTextBox',
                                'name' => 'AdditionalLabel012',
                                'caption' => 'Bezeichnung 13'
                            ],
                            [
                                'type' => 'SelectVariable',
                                'name' => 'AdditionalValue012',
                                'caption' => 'Variable 13'
                            ]
                        ]
                    ]
                ]
            ],
            [
                "type" => "RowLayout",
                "items" => [
                    [
                        "type" => "Image",
                        "onClick" => "echo 'https://paypal.me/mbstern';",
                        "image" => "data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAAA8AAD/7gAOQWRvYmUAZMAAAAAB/9sAhAAGBAQEBQQGBQUGCQYFBgkLCAYGCAsMCgoLCgoMEAwMDAwMDBAMDg8QDw4MExMUFBMTHBsbGxwfHx8fHx8fHx8fAQcHBw0MDRgQEBgaFREVGh8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx//wAARCABLAGQDAREAAhEBAxEB/8QAqwABAAICAwEBAAAAAAAAAAAAAAUGAgcDBAgJAQEBAAIDAQAAAAAAAAAAAAAAAAMEAgUGARAAAQMCAwMEDwMICwAAAAAAAgEDBAAFERIGIRMHMdEUFkFRcSKyk6PDJFSEFTZGZmEyCIGxQlKSIzODkaFigmOz00QlVRgRAAICAQIDBQYFBQAAAAAAAAABAgMREgQhMQVBUWEiE/BxgaGxBpHRQhQVwfEyUiP/2gAMAwEAAhEDEQA/AN+WWywr/CS63VDfkPmeUc5CICJKKCKCqbNlAd/qNpr1YvGHz0A6jaa9WLxh89AOo2mvVi8YfPQDqNpr1YvGHz0A6jaa9WLxh89AOo2mvVi8YfPQDqNpr1YvGHz0A6jaa9WLxh89AOo2mvVi8YfPQDqNpr1YvGHz0A6jaa9WLxh89ARnuVr3/wC4t+97o3PSui51+9jly5vvZezhQEnob4ajd1zw1oCeoBQCgFAeZtWfik1ZbtT3W3W22284MKU7GYceR4nCFk1DMSi4KbVHHYldDT0eEoJtvLRrrN7JSaSIr/1nr3/q7Z+y/wD6tS/wtXfL5GH76Xci4aC/FPFul1j2zVFtC3dKMWmrhGMiZEyXAd6B98Iqv6WZcOzVTc9HcYuUHnHYTVb1N4Zv6tIXhQCgFAV/569g85QGWhvhqN3XPDWgJ6gFAKA4LhLbhwJMxxcG4zRvGq9psVJfzVlGOWkeN4WT53SZJyZD0lxcTfMnTVe2aqS/nru0sLBz74s6XSj7SVD6rJfTR+g+6ZIAjiRKgiiY44rsSitZ44JcT6E6Nv8ADvunok2Kpd6KNPgf3wdbREISw/prkd3t5U2OMjZbHeQ3FanHkTdVi2KAUBX/AJ69g85QGWhvhqN3XPDWgJ6gFAKAp/F+6LbOGOpZaLlLoLrIL/afTcp/W5VrYw1XRXiRXvEGeElElHKAqRLsERTFVVewiJXZS5GjTXNmAWi7GSCEJ9SXYibo+aq2h9xk9zUuco/ii26T0VKalt3C6AjaMrmYjLgpKachHhyYdqrNVLzlmj6l1aMouuvjnm/yPWPBCG8zpJ19xFQZUozax7IiIhin94VrnOuTTuS7om5+2q3Hbtv9UvyRsKtMdEKAUBX/AJ69g85QGWhvhqN3XPDWgJ6gFAKA1F+KK59E4XnGQsCuE2Oxh2xFVeX/ACq2nSIZuz3JlTeSxA8waGY3l9RzDYy0Z4/auAp4VdZHmct1aeKH4tI2xpzTl11Fcfd9uESfQCdJXCyigjgiqq7eyqVjudzCmOqXI5/Z7Ke4nohz5l8snAu6HIA7zMaZjIuJtRlI3CTtZiQRHu7a1F/XYJeRNvxOg232xNyzbJKPhzNwwYMWBDZhxG0ajRwRtpseRBHYlc3ZNzk5Pi2djVXGuKjFYijnrAzFAKAr/wA9ewecoDLQ3w1G7rnhrQE9QCgFAUzidwvtnEC3QoNwmyITcJ5XwWPkXMRAod8hiXIi7Kt7TduhtpJ5IbqVNYZp7UfBCFodyO7ZnZ10dnIYPKbYkLYtqKphuhTaSr2e1XRdO6h6revTHByv3BtmowjBOXF9hduB1knx7hc50qM6wKNAw0roEGZSJSLDMicmVKq9cvjKMYpp8cnv2ztpxnOUk1wxx9vA29XOHXigFAKAUBX/AJ69g85QGWhvhqN3XPDWgNAyeKvFSdB1ZqS36lhQbTY5xsQ7e+wwrj4K4qADSqKqSoOXl5a6JbOhOEHFuUlz4mud02m0+CNl2HjvpKPpawytX3Fm3Xy5xQffiNg4eVCVUF0hBD3YuCmdM3YWtfZ06bnJVrMUyxHcR0rVzJ5njHw3eisTG7yBRJMz3czI3TyNlJyiWTMoYJ3pouK7KgexuTxp44z8CRXw7yQvOvdM2y7rYXZo+/SiuS24IiZkjbYEeYyEVEEwBfvKlY1bWc0pY8ucGN16hFvtSbNadfNfsabjaiO7xXAefVkbcTTe8JBVcSwFEXL3tdB+w27tdWh8Fzyzj/5TdxpVznHjLGnCybGd4kaSiOtxbhPCPOyCUhlEM0aNRRVAiEVRFTkwrSrpt0lmMcx+p0b6xt4NRnLEscefDwIy6a2emah0tGsEpCgXQ3XJJ7vabTRYKnfpmH7h7anq2SjXY7F5o4x737IrX9Sc7qY0vyTznh2L3+5lh1pqVrTGlLpf3W98NuYJ4WVLLnNNgBmwXDMSonJWv29XqTUe83Vk9MWzWjf4jrYPDTrZJgC3dHJbkGNZhexzutoJqSuKCKgI2aES5fs7NbB9Kl62hPy4zkr/ALtaNXaWuBxb04xpOy3vVD7Vll3ljpLFuQjkO5FxUVEQDeEmXBVXLhVaWym5yjDzKPaSq9KKcuGS02DUNk1Da2rrZZjc63vYo2+3jhiK4EioqIqKi8qKlVrKpQlpksMkjJSWUdD569g85UZkcGmSlDolSiBvZQtSFjtoqIpOIpZBxXBExKsoYys8jx8jWHCf8PVhTTrczXdl3uoCkOuE068RCLeKICELR7tccFL8tbje9TlrxVLy4KdO1WPMuJxM6R4h6Y1/q2XbNJRb/Evyf8ZOdeZaajMoK5WVA9uVBwBQRExypguFeu+qyqCc3Fx5rvGicZPCzkgLzojqx+G9+FqdBtt8W5dOhMKQkayVcRsGx3akmJMivIuxO5U1e49Td5hxjpx8P7kcq9NWHweS5aI4d6kj6KvmpLuBzteapj/vd4oi40w5gIspjlQVyd8SdwexUM93X68IrhVBkW5oslt54WbJL6lt0hwv0/CtsCVcbeJXoAE3ycMjQXeX7mZW1y9yot51SyUpKMvJ/T6kHT+iUwhGU4/9O33/AEKzE01re3WO+WIbA1MdnOOGt2J1vExPBO9QlzKX6Q4qmC1fnuaJ2Qs1uOn9OGauGz3VdVlXpqTlnzZXt7iW01o++QdR2WTIiKMS0Wnd5s4LjKczEYIiLjji6u3kqtut5XKqaT805/L2Rc2XT7YX1uS8sK/D/J5z9SF11B4q604XJa5tjbg3i43NtqVEYdBRagNkh70yJxUVVIU2Cv5Kh28qKrtSlmKj8zdWKc4YxxyQnEfgA63EusvS7DlxuF7ksNNxl3bbUCNsKQYKRJmU1aBFXlw2VNtepZaU+CivxfYYW7b/AF7Tk1fw51fbeIQXq2QblcbMlsj26CdlnNQpUbo4CCtkryLi2WVS2duvKN1XKrS3FS1NvUspns6ZKWVnGOw2bwp0m3pjR0eAkJ23OvOuypEJ+QMtxs3S5CeAQElyiOOCcta7eXepZnOfhgsUw0xwd/569g85VUlMtDfDUb7Ccx/bWgJ6gFAdO42a0XJWVuMJiYsY95H6Q0Du7P8AWDOi5V+1KzjZKPJ4PHFPmdysD0UAoBQCgFAKAUBX8U69YY7egcn8ygIeLj0iZuen/wAc83unDo2P879L9bLsoDs+k/UHkKAek/UHkKAek/UHkKAek/UHkKAek/UHkKAek/UHkKAek/UHkKAek/UHkKAek/UHkKAek/UHkKAiv3fvf/db/P8A4nvT+H4nd0B//9k="
                    ],
                    [
                        "type" => "Label",
                        "caption" => ""
                    ]
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
            'items'   => array_merge(
                $number === 1
                    ? [
                        [
                            'type' => 'CheckBox',
                            'name' => 'SingleCircuitHotWaterTemperatureSwitch',
                            'caption' => 'Heizen des Warmwasserspeichers integrieren'
                        ]
                    ]
                    : [],
                [
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
            )
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
        return [
            'svg' => __DIR__
                . DIRECTORY_SEPARATOR
                . 'heat-pump'
                . DIRECTORY_SEPARATOR
                . 'heat-pump-card'
                . DIRECTORY_SEPARATOR
                . 'heat-pump.svg'
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

        $appJs = $this->GetEmbeddedJavaScript();
        $svg = file_get_contents($files['svg']);

        if ($svg === false) {
            return '<div style="padding:16px;font-family:sans-serif;color:#c62828;">'
                . 'Die Wärmepumpen-SVG konnte nicht gelesen werden.'
                . '</div>';
        }

        /*
         * Die wenigen benötigten Texte sind Bestandteil unseres Moduls.
         * Eine separate de.json wird nicht mehr benötigt.
         */
        $localization = [
            'svgTexts' => [
                'tankWWName'             => 'Warmwasser',
                'tankHPName'             => 'Pufferspeicher',
                'evaporator'             => 'Verdampfer',
                'condenser'              => 'Kondensator',
                'compressor'             => 'Verdichter',
                'expansionValve'         => 'Expansionsventil',
                'circulatingPump'        => 'Zirkulationspumpe',
                'supplyTemperatureLabel' => 'Vorlauftemperatur'
            ]
        ];

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
        position: relative;
        width: 100%;
        height: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }

    #wp-compact-view {
        display: none;
        position: absolute;
        inset: 0;
        box-sizing: border-box;
        overflow: hidden;
        align-items: center;
        justify-content: center;
        padding: 8px 8px 48px;
    }

    #wp-compact-view svg {
        display: block;
        width: 100%;
        height: 100%;
        max-width: 100%;
        max-height: 100%;
        overflow: visible;
    }

    #wp-view-toggle {
        position: absolute;
        left: 50%;
        right: auto;
        bottom: 10px;
        transform: translateX(-50%);
        z-index: 10000;
        min-width: 38px;
        height: 30px;
        padding: 0 10px;
        border: 1px solid rgba(127,127,127,.25);
        border-radius: 6px;
        background: rgba(255,255,255,.07);
        color: var(--content-color, #fff);
        font: 600 18px Arial, sans-serif;
        line-height: 28px;
        text-align: center;
        cursor: pointer;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        user-select: none;
    }

    #wp-view-toggle:active {
        transform: translateX(-50%) scale(.96);
        opacity: .65;
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
        display: none;
        position: fixed;
        z-index: 2147483647;
        box-sizing: border-box;
        width: min(360px, calc(100vw - 24px));
        max-height: min(70vh, 520px);
        overflow-y: auto;
        left: 50%;
        bottom: 12px;
        transform: translateX(-50%);
        padding: 8px;
        border-radius: 12px;
        border: 1px solid rgba(127,127,127,.35);
        background: Canvas;
        color: CanvasText;
        box-shadow: 0 8px 28px rgba(0,0,0,.28);
        font: 14px Arial, sans-serif;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }

    /*
     * Auf genügend breiten Desktop-Ansichten darf das Menü kompakt
     * mittig erscheinen. Auf dem Handy bleibt es als Bottom-Sheet unten.
     */
    @media (min-width: 700px) {
        #wp-mode-menu {
            top: 50%;
            bottom: auto;
            transform: translate(-50%, -50%);
        }
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

    #wp-mode-menu .wp-numeric-row {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 4px 6px 8px;
    }

    #wp-mode-menu .wp-numeric-row button {
        width: 38px;
        min-width: 38px;
        text-align: center;
        font-size: 18px;
        padding: 6px;
    }

    #wp-mode-menu .wp-numeric-row input {
        width: 92px;
        box-sizing: border-box;
        padding: 7px 6px;
        border: 1px solid rgba(127,127,127,.45);
        border-radius: 5px;
        background: Canvas;
        color: CanvasText;
        text-align: right;
        font: inherit;
    }

    #wp-mode-menu .wp-numeric-unit {
        min-width: 28px;
        opacity: .8;
    }

    /*
     * Dezente Flussanimation für unsere erweiterte Temperaturdarstellung.
     * Die Original-Card bleibt unangetastet, solange eigene Farben
     * deaktiviert sind.
     */
    @keyframes symcon-flow-forward {
        from { stroke-dashoffset: 0; }
        to   { stroke-dashoffset: -24; }
    }

    @keyframes symcon-flow-reverse {
        from { stroke-dashoffset: 0; }
        to   { stroke-dashoffset: 24; }
    }

    .symcon-flow-overlay {
        pointer-events: none;
        fill: none !important;
        stroke: rgba(255,255,255,.68) !important;
        stroke-width: 2.2 !important;
        stroke-dasharray: 4 8 !important;
        stroke-linecap: round !important;
        vector-effect: non-scaling-stroke;
    }

    .symcon-flow-forward {
        animation: symcon-flow-forward 1.4s linear infinite;
    }

    .symcon-flow-reverse {
        animation: symcon-flow-reverse 1.4s linear infinite;
    }

    /*
     * Kältekreis: Flusspunkte bewusst dünner als bei den übrigen Leitungen,
     * damit die Temperaturfarbe der 5-px-Leitung klar sichtbar bleibt.
     */
    .symcon-flow-overlay.symcon-flow-refrigerant {
        stroke-width: 1.25 !important;
        stroke: rgba(255,255,255,.62) !important;
        stroke-dasharray: 3 9 !important;
    }

    /*
     * Heizkörper/Fußbodenheizung: Flusspunkte klar erkennbar,
     * aber weiterhin schmaler als die farbige Leitung.
     */
    .symcon-flow-overlay.symcon-flow-emitter {
        stroke-width: 1.45 !important;
        stroke-dasharray: 3 8 !important;
    }
</style>

<div id="wp-root">
    <div id="wp-error"></div>
    <heat-pump-card id="wp-card"></heat-pump-card>
    <div id="wp-compact-view" aria-hidden="true"></div>
    <button
        id="wp-view-toggle"
        type="button"
        title="Ansicht umschalten"
        aria-label="Ansicht umschalten"
    >⇄</button>
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

            'singleCircuitHotWaterTemperatureSwitch' => $this->ReadPropertyBoolean('SingleCircuitHotWaterTemperatureSwitch'),
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
                ['temperature' => $this->ReadPropertyInteger('TemperaturePoint6'), 'color' => $this->ReadPropertyInteger('TemperatureColor6')],
                ['temperature' => $this->ReadPropertyInteger('TemperaturePoint7'), 'color' => $this->ReadPropertyInteger('TemperatureColor7')],
                ['temperature' => $this->ReadPropertyInteger('TemperaturePoint8'), 'color' => $this->ReadPropertyInteger('TemperatureColor8')],
                ['temperature' => $this->ReadPropertyInteger('TemperaturePoint9'), 'color' => $this->ReadPropertyInteger('TemperatureColor9')],
                ['temperature' => $this->ReadPropertyInteger('TemperaturePoint10'), 'color' => $this->ReadPropertyInteger('TemperatureColor10')]
            ],

            'thermalSolarAvailable'      => $this->ReadPropertyBoolean('ThermalSolarAvailable'),
            'thermalSolarPump'           => $this->DataKey('ThermalSolarPump', 'thermalSolarPump'),
            'thermalSolarPumpSpeed'      => $this->DataKey('ThermalSolarPumpSpeed', 'thermalSolarPumpSpeed'),
            'thermalSolarPanelTemp'      => $this->DataKey('ThermalSolarPanelTemp', 'thermalSolarPanelTemp'),
            'thermalSolarFluxTemp'       => $this->DataKey('ThermalSolarFluxTemp', 'thermalSolarFluxTemp'),
            'thermalSolarReturnTemp'     => $this->DataKey('ThermalSolarReturnTemp', 'thermalSolarReturnTemp'),

            'additionalLabel000'          => $this->ReadPropertyString('AdditionalLabel000'),
            'additionalValue000'          => $this->DataKey('AdditionalValue000', 'additionalValue000'),
            'additionalLabel001'          => $this->ReadPropertyString('AdditionalLabel001'),
            'additionalValue001'          => $this->DataKey('AdditionalValue001', 'additionalValue001'),
            'additionalLabel002'          => $this->ReadPropertyString('AdditionalLabel002'),
            'additionalValue002'          => $this->DataKey('AdditionalValue002', 'additionalValue002'),
            'additionalLabel003'          => $this->ReadPropertyString('AdditionalLabel003'),
            'additionalValue003'          => $this->DataKey('AdditionalValue003', 'additionalValue003'),
            'additionalLabel004'          => $this->ReadPropertyString('AdditionalLabel004'),
            'additionalValue004'          => $this->DataKey('AdditionalValue004', 'additionalValue004'),
            'additionalLabel005'          => $this->ReadPropertyString('AdditionalLabel005'),
            'additionalValue005'          => $this->DataKey('AdditionalValue005', 'additionalValue005'),
            'additionalLabel006'          => $this->ReadPropertyString('AdditionalLabel006'),
            'additionalValue006'          => $this->DataKey('AdditionalValue006', 'additionalValue006'),
            'additionalLabel007'          => $this->ReadPropertyString('AdditionalLabel007'),
            'additionalValue007'          => $this->DataKey('AdditionalValue007', 'additionalValue007'),
            'additionalLabel008'          => $this->ReadPropertyString('AdditionalLabel008'),
            'additionalValue008'          => $this->DataKey('AdditionalValue008', 'additionalValue008'),
            'additionalLabel009'          => $this->ReadPropertyString('AdditionalLabel009'),
            'additionalValue009'          => $this->DataKey('AdditionalValue009', 'additionalValue009'),
            'additionalLabel010'          => $this->ReadPropertyString('AdditionalLabel010'),
            'additionalValue010'          => $this->DataKey('AdditionalValue010', 'additionalValue010'),
            'additionalLabel011'          => $this->ReadPropertyString('AdditionalLabel011'),
            'additionalValue011'          => $this->DataKey('AdditionalValue011', 'additionalValue011'),
            'additionalLabel012'          => $this->ReadPropertyString('AdditionalLabel012'),
            'additionalValue012'          => $this->DataKey('AdditionalValue012', 'additionalValue012')
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
            'thermalSolarReturnTemp'     => 'ThermalSolarReturnTemp',

            'additionalValue000'          => 'AdditionalValue000',
            'additionalValue001'          => 'AdditionalValue001',
            'additionalValue002'          => 'AdditionalValue002',
            'additionalValue003'          => 'AdditionalValue003',
            'additionalValue004'          => 'AdditionalValue004',
            'additionalValue005'          => 'AdditionalValue005',
            'additionalValue006'          => 'AdditionalValue006',
            'additionalValue007'          => 'AdditionalValue007',
            'additionalValue008'          => 'AdditionalValue008',
            'additionalValue009'          => 'AdditionalValue009',
            'additionalValue010'          => 'AdditionalValue010',
            'additionalValue011'          => 'AdditionalValue011',
            'additionalValue012'          => 'AdditionalValue012'
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
            'hasOperatingStatus'           => $this->HasOperatingStatus(),
            'heatingActive'                => $this->GetOperatingModeActive(
                'HeatingPumpHeatingMode',
                'OperatingStatusHeatingValues'
            ),
            'hotwaterActive'               => $this->GetOperatingModeActive(
                'HeatingPumpHotWaterMode',
                'OperatingStatusHotWaterValues'
            ),
            'coolingActive'                => $this->GetOperatingModeActive(
                'HeatingPumpCoolingMode',
                'OperatingStatusCoolingValues'
            ),
            'heating'                      => $this->BuildControlInfo('HeatingControlVariable'),
            'hotwater'                     => $this->BuildControlInfo('HotWaterControlVariable'),
            'cooling'                      => $this->BuildControlInfo('CoolingControlVariable'),
            'warmWaterSetpoint'            => $this->BuildNumericControlInfo('WarmWaterSetpointVariable', 20.0, 80.0, 0.5),
            'heatingTemperatureCorrection' => $this->BuildNumericControlInfo('HeatingTemperatureCorrectionVariable', -10.0, 10.0, 0.5)
        ];
    }

    private function GetOperatingModeActive(
        string $directProperty,
        string $statusValuesProperty
    ): bool {
        if ($this->HasOperatingStatus()) {
            $status = GetValue(
                $this->ReadPropertyInteger('OperatingStatusVariable')
            );

            return $this->ValueMatchesCsv(
                $status,
                $this->ReadPropertyString($statusValuesProperty)
            );
        }

        $variableId = $this->ReadPropertyInteger($directProperty);

        if ($variableId <= 0 || !IPS_VariableExists($variableId)) {
            return false;
        }

        return $this->NormalizeBinaryValue(GetValue($variableId));
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

    private function BuildNumericControlInfo(
        string $property,
        float $fallbackMin,
        float $fallbackMax,
        float $fallbackStep
    ): array {
        $variableId = $this->ReadPropertyInteger($property);

        if ($variableId <= 0 || !IPS_VariableExists($variableId)) {
            return [
                'configured'   => false,
                'variableId'   => 0,
                'currentValue' => null,
                'min'          => $fallbackMin,
                'max'          => $fallbackMax,
                'step'         => $fallbackStep,
                'unit'         => ''
            ];
        }

        $min = $fallbackMin;
        $max = $fallbackMax;
        $step = $fallbackStep;
        $unit = $this->GetVariableUnit($variableId);

        $profileName = $this->GetVariableProfileName($variableId);
        if ($profileName !== '' && IPS_VariableProfileExists($profileName)) {
            $profile = IPS_GetVariableProfile($profileName);

            if (isset($profile['MinValue']) && is_numeric($profile['MinValue'])) {
                $min = (float) $profile['MinValue'];
            }
            if (isset($profile['MaxValue']) && is_numeric($profile['MaxValue'])) {
                $max = (float) $profile['MaxValue'];
            }
            if (isset($profile['StepSize']) && is_numeric($profile['StepSize'])
                && (float) $profile['StepSize'] > 0.0) {
                $step = (float) $profile['StepSize'];
            }
        }

        return [
            'configured'   => true,
            'variableId'   => $variableId,
            'currentValue' => GetValue($variableId),
            'min'          => $min,
            'max'          => $max,
            'step'         => $step,
            'unit'         => $unit
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


    /*
     * ================================================================
     * Eingebettete JavaScript-Visualisierung
     * ================================================================
     *
     * Bewusst am Ende der module.php gehalten:
     * - PHP oben: IP-Symcon, Konfiguration, Daten und Aktionen
     * - JavaScript hier: SVG, Darstellung, Farben und Animationen
     *
     * heat-pump-card.js wird dadurch nicht mehr als separate Datei benötigt.
     */
    private function GetEmbeddedJavaScript(): string
    {
        return <<<'SYMC0N_HEATPUMP_JAVASCRIPT'
class HeatPumpCard extends HTMLElement {
  constructor() {
    super();
    this.config = {};
    this.data = {};
    this.content = null;
    this.localization = {};
  }

  mount(svg, localization = {}) {
    this.innerHTML =
      '<ha-card>\n'
      + String(svg || '')
          .replace(/<!\[CDATA\[/g, '')
          .replace(/\]\]>/g, '')
          .replace(/ class="rotate"/g, '')
          .replace(/display: inline;/g, 'display: none;')
      + '</ha-card>';

    this.content = this.querySelector('svg');
    this.localization = localization || {};

    if (!this.content) {
      throw new Error('SVG konnte nicht in die Card eingefügt werden.');
    }

    this.content.setAttribute('width', '100%');
    this.content.setAttribute('height', '100%');
    this.content.setAttribute('preserveAspectRatio', 'xMidYMid meet');
    this.content.style.setProperty('background', 'transparent', 'important');
    this.content.style.setProperty('background-color', 'transparent', 'important');
    this.content.style.setProperty('--card-background-color', 'transparent');

    /*
     * Die Theme-Farbe wird später aus Symcons --content-color übernommen.
     * Hier keine eigene body-Farbe mehr festschreiben.
     */

    const texts = this.localization.svgTexts || {};
    this.setText('#textTankWWName', texts.tankWWName);
    this.setText('#textTankHPName', texts.tankHPName);
    this.setText('#textEvaporator', texts.evaporator);
    this.setText('#textCondenser', texts.condenser);
    this.setText('#textCompressor', texts.compressor);
    this.setText('#textExpansionValve', texts.expansionValve);
    this.setText('#textCirculatingPump', texts.circulatingPump);
    this.setText('#textSupplyTemperatureLabel', texts.supplyTemperatureLabel);

    this.setConfig(this.config || {});
  }

  item(key) {
    return key && this.data && this.data[key] ? this.data[key] : null;
  }

  value(key) {
    const item = this.item(key);
    return item ? item.value : null;
  }

  number(key) {
    const value = this.value(key);
    if (value === null || value === undefined || value === '') {
      return null;
    }
    const normalized = String(value).replace(',', '.').replace(/[^0-9+\-.]/g, '');
    const number = Number(normalized);
    return Number.isFinite(number) ? number : null;
  }

  binary(key) {
    const item = this.item(key);
    if (!item) return false;
    if (item.binary) return !!item.value;

    const value = String(item.value ?? '').trim().toLowerCase();
    return ['1','true','on','yes','ja','ein','active','aktiv'].includes(value);
  }

  format(key) {
    const item = this.item(key);
    if (!item || item.value === null || item.value === undefined || item.value === '') {
      return '';
    }

    const numeric = this.number(key);
    if (numeric !== null) {
      const value = new Intl.NumberFormat('de-CH', {
        minimumFractionDigits: 1,
        maximumFractionDigits: 1
      }).format(numeric);
      return item.unit ? value + ' ' + item.unit : value;
    }

    return String(item.value);
  }

  setText(selector, value) {
    const element = this.content ? this.content.querySelector(selector) : null;
    if (element) element.textContent = value || '';
  }

  rotate(selector, active) {
    const element = this.content ? this.content.querySelector(selector) : null;
    if (!element) return;
    element.classList.toggle('rotate', !!active);
  }

  setData(data) {
    this.data = data || {};
    if (!this.content) return;

    const c = this.config || {};

    this.changeHeatPumpRunning(c.heatingPumpType, c.hpRunning);
    this.setText('#textG2WWaterTempIn', this.format(c.temperatureGroundWaterIn));
    this.setText('#textG2WWaterTempOut', this.format(c.temperatureGroundWaterOut));

    const party = this.binary(c.heatingPumpPartyMode);
    const night = this.binary(c.heatingPumpNightMode);
    const warning = this.binary(c.warning);

    const show = (selector, visible) => {
      const element = this.content.querySelector(selector);
      if (element) element.style.display = visible ? 'inline' : 'none';
    };

    show('#gHPStatusOff', !this.binary(c.heatingPumpStatusOnOff));
    show('#gHPStatusWW', this.binary(c.heatingPumpHotWaterMode));
    show('#gHPStatusHeating', this.binary(c.heatingPumpHeatingMode));
    show('#gHPStatusCooling', this.binary(c.heatingPumpCoolingMode));
    show('#gHPStatusParty', party);
    show('#gHPStatusSave', this.binary(c.heatingPumpEnergySaveMode));
    show('#gTimeSymbolNight', night);
    show('#gTimeSymbolDay', !night);
    show('#gWarning', warning);
    show('#gError', this.binary(c.error) || warning);
    show('#gDefrost', this.binary(c.defrostMode));
    show('#gAdditionalHeating', this.binary(c.additionalHeating));

    this.setText('#textOutdoorTemperatureValue', this.format(c.outdoorTemperature));

    if (party && this.format(c.ambientTemperatureParty)) {
      this.setText('#textIndoorTemperatureValue', this.format(c.ambientTemperatureParty));
    } else if (night && this.format(c.ambientTemperatureReduced)) {
      this.setText('#textIndoorTemperatureValue', this.format(c.ambientTemperatureReduced));
    } else {
      this.setText('#textIndoorTemperatureValue', this.format(c.ambientTemperatureNormal));
    }

    this.setText('#textSupplyTemperatureValue', this.format(c.supplyTemperature));

    this.rotate('#pathCompressor', this.binary(c.compressorRunning));
    this.rotate('#gHeatingCircuitPump', this.binary(c.heatingCircuitPumpRunning));
    this.rotate('#gHeatingCircuitPump2', this.binary(c.heatingCircuitPumpRunning2));
    this.rotate('#gHeatingCircuitPump3', this.binary(c.heatingCircuitPumpRunning3));
    this.rotate('#gCirculatingPumpBladeWheel', this.binary(c.circulatingPumpRunning));
    this.rotate('#gStorageChargingPump', this.binary(c.storageChargingPumpRunning));

    if (c.tankHP) {
      const up = this.number(c.tankTempHPUp);
      const middle = this.number(c.tankTempHPMiddle);
      const down = this.number(c.tankTempHPDown);
      this.setText('#textTankTempHPUp', this.format(c.tankTempHPUp));
      this.setText('#textTankTempHPMiddle', this.format(c.tankTempHPMiddle));
      this.setText('#textTankTempHPDown', this.format(c.tankTempHPDown));
      this.tankColors(up, middle, down, '#stop3020', '#stop3040', '#stop3030');
      show('#pathHeaterRodHP', this.binary(c.heaterRodHP));
    }

    if (c.tankWW) {
      const up = this.number(c.tankTempWWUp);
      const middle = this.number(c.tankTempWWMiddle);
      const down = this.number(c.tankTempWWDown);
      this.setText('#textTankTempWWUp', this.format(c.tankTempWWUp));
      this.setText('#textTankTempWWMiddle', this.format(c.tankTempWWMiddle));
      this.setText('#textTankTempWWDown', this.format(c.tankTempWWDown));
      this.tankColors(up, middle, down, '#stop3050', '#stop3070', '#stop3060');

      const valve = this.content.querySelector('#gWWHeatingValve');
      if (valve) {
        valve.setAttribute(
          'transform',
          'rotate(' + (this.binary(c.wwHeatingValve) ? '90' : '0') + ', 620, 450)'
        );
      }
    }

    this.heatingCircuit(
      c.heatingCircuitType1,
      c.supplyTemperatureHeating,
      c.refluxTemperatureHeating,
      '#stopCircuit1', '#stopCircuit2',
      '#textSupplyTemperatureHeating', '#textRefluxTemperatureHeating'
    );
    this.heatingCircuit(
      c.heatingCircuitType2,
      c.supplyTemperatureHeating2,
      c.refluxTemperatureHeating2,
      '#stopCircuit3', '#stopCircuit4',
      '#textSupplyTemperatureHeating2', '#textRefluxTemperatureHeating2'
    );
    this.heatingCircuit(
      c.heatingCircuitType3,
      c.supplyTemperatureHeating3,
      c.refluxTemperatureHeating3,
      '#stopCircuit5', '#stopCircuit6',
      '#textSupplyTemperatureHeating3', '#textRefluxTemperatureHeating3'
    );

    this.setText('#textEvaporatorPressure', this.format(c.evaporatorPressure));
    this.setText('#textEvaporatorTemperature', this.format(c.evaporatorTemperature));
    this.setText('#textCondenserPressure', this.format(c.condenserPressure));
    this.setText('#textCondenserTemperature', this.format(c.condenserTemperature));
    this.setText('#textExpansionValveOpening', this.format(c.expansionValveOpening));
    this.setText('#textCompressorValue', this.format(c.compressorValue));

    if (c.thermalSolarAvailable) {
      this.rotate('#gThermalSolarPump', this.binary(c.thermalSolarPump));
      this.setText('#textThermalSolarPanelTemp', this.format(c.thermalSolarPanelTemp));
      this.setText('#textThermalSolarFluxTemp', this.format(c.thermalSolarFluxTemp));
      this.setText('#textThermalSolarPumpSpeed', this.format(c.thermalSolarPumpSpeed));
    }
  }

  changeHeatPumpRunning(selection, runningKey) {
    const running = this.binary(runningKey);
    this.rotate('#pathHPFan', (!selection || selection === 'A2W') && running);
    this.rotate('#gHPW2WPumpBladeWheel', selection === 'W2W' && running);
    this.rotate('#gHPG2WPumpBladeWheel', selection === 'G2W' && running);
  }

  heatingCircuit(type, supplyKey, refluxKey, supplyStop, refluxStop, supplyText, refluxText) {
    if (!type || type === 'off') return;

    const supply = this.number(supplyKey);
    const reflux = this.number(refluxKey);
    const effectiveSupply = supply === null ? 30 : supply;
    const effectiveReflux = reflux === null ? Math.max(0, effectiveSupply - 10) : reflux;

    const s1 = this.content.querySelector(supplyStop);
    const s2 = this.content.querySelector(refluxStop);
    if (s1) s1.setAttribute('style', 'stop-color:' + this.tempColor(effectiveSupply));
    if (s2) s2.setAttribute('style', 'stop-color:' + this.tempColor(effectiveReflux));

    this.setText(supplyText, this.format(supplyKey));
    this.setText(refluxText, this.format(refluxKey));
  }

  tempColor(temp) {
    if (!temp) return '#ffffff00';
    if (temp > 60) return '#ff0000';
    return '#'
      + ('0' + Math.round(255 * temp / 60).toString(16)).substr(-2)
      + '00'
      + ('0' + Math.round(255 * Math.abs(60 - temp) / 60).toString(16)).substr(-2);
  }

  tankColors(up, middle, down, idUp, idMiddle, idDown) {
    let tempUp = Number.isFinite(up) ? up : null;
    let tempMiddle = Number.isFinite(middle) ? middle : null;
    let tempDown = Number.isFinite(down) ? down : null;

    if (tempUp !== null) {
      if (tempMiddle === null) {
        tempMiddle = tempDown !== null ? (tempUp + tempDown) / 2 : tempUp - 5;
      }
    } else if (tempMiddle !== null) {
      tempUp = tempMiddle + 5;
    } else if (tempDown !== null) {
      tempMiddle = tempDown + 5;
      tempUp = tempDown + 10;
    }

    if (tempMiddle !== null && tempDown === null) {
      tempDown = tempMiddle - 5;
    }

    const set = (id, value) => {
      const stop = this.content.querySelector(id);
      if (stop) stop.setAttribute('style', 'stop-color:' + this.tempColor(value));
    };

    set(idUp, tempUp);
    set(idMiddle, tempMiddle);
    set(idDown, tempDown);
  }

  setConfig(config) {
    this.config = config;
    if (this.content) {
      this.querySelector("ha-card").setAttribute("header", config.title);
      this.content.querySelector('#gHPFan').style.display = (!config.heatingPumpType || config.heatingPumpType === 'A2W' ? 'inline' : 'none');
      this.content.querySelector('#gHPW2W').style.display = (config.heatingPumpType === 'W2W' ? 'inline' : 'none');
      this.content.querySelector('#gHPG2W').style.display = (config.heatingPumpType === 'G2W' ? 'inline' : 'none');
      this.content.querySelector("#gCirculatingPump").style.display = config.circulatingPumpRunning ? 'inline' : 'none';
      this.content.querySelector('#gCirculatingPumpBladeWheel').classList.remove("rotate");
      this.content.querySelector("#gStorageChargingPump").style.display = config.storageChargingPumpRunning ? 'inline' : 'none';
      this.content.querySelector('#gStorageChargingPump').classList.remove("rotate");
      this.content.querySelector("#gTankHP").style.display = config.tankHP ? 'inline' : 'none';
      this.content.querySelector("#gWW").style.display = config.tankWW ? 'inline' : 'none';

      var type1 = config.heatingCircuitType1;
      if (!type1 || type1 === 'off') {
        this.content.querySelector('#gHeaterCircuit1').style.display = 'none';
      } else {
        this.content.querySelector('#gHeaterCircuit1').style.display = 'inline';
        this.content.querySelector('#gHeaterCircuitFloor1').style.display = (type1 === 'underfloor' ? 'inline' : 'none');
        this.content.querySelector('#radiator1').style.display = (type1 === 'radiator' ? 'inline' : 'none');
      }
      this.content.querySelector('#gHeatingCircuitPump').classList.remove("rotate");
      this.content.querySelector("#gHeatingCircuitPump").style.display = config.heatingCircuitPumpRunning ? 'inline' : 'none';

      var type2 = config.heatingCircuitType2;
      if (!type2 || type2 === 'off') {
        this.content.querySelector('#gHeaterCircuit2').style.display = 'none';
      } else {
        this.content.querySelector('#gHeaterCircuit2').style.display = 'inline';
        this.content.querySelector('#gHeaterCircuitFloor2').style.display = (type2 === 'underfloor' ? 'inline' : 'none');
        this.content.querySelector('#radiator2').style.display = (type2 === 'radiator' ? 'inline' : 'none');
      }
      this.content.querySelector('#gHeatingCircuitPump2').classList.remove("rotate");
      this.content.querySelector("#gHeatingCircuitPump2").style.display = config.heatingCircuitPumpRunning2 ? 'inline' : 'none';
      this.content.querySelector("#pathPipeToHP2").style.display = (!type1 || type1 === 'off') && (!type2 || type2 === 'off') ? 'none' : 'inline';

      var type3 = config.heatingCircuitType3;
      if (!type3 || type3 === 'off') {
        this.content.querySelector('#gHeaterCircuit3').style.display = 'none';
      } else {
        this.content.querySelector('#gHeaterCircuit3').style.display = 'inline';
        this.content.querySelector('#gHeaterCircuitFloor3').style.display = (type3 === 'underfloor' ? 'inline' : 'none');
        this.content.querySelector('#radiator3').style.display = (type3 === 'radiator' ? 'inline' : 'none');
      }
      this.content.querySelector('#gHeatingCircuitPump3').classList.remove("rotate");
      this.content.querySelector("#gHeatingCircuitPump3").style.display = config.heatingCircuitPumpRunning3 ? 'inline' : 'none';

      var noHeating = (!type1 || type1 === 'off') && (!type2 || type2 === 'off') && (!type3 || type3 === 'off') && !config.tankHP;
      var noHotWater = !config.tankWW;

      if (noHeating && noHotWater) {
        this.content.querySelector("#gPipe").style.display = 'none';
        this.content.querySelector("#gPipeBuffer").style.display = 'none';
        this.content.querySelector("#gPipeLayeredChargeStorage").style.display = 'none';
        this.content.querySelector("#gHP").setAttribute("transform", "translate(460 -300)");
        this.content.querySelector("#gSettings").setAttribute("transform", "translate(-25)");
      } else {
        this.content.querySelector("#pathPipeToBuffer").style.display = noHeating ? 'none' : 'inline';
        this.content.querySelector("#pathPipeFromBuffer").style.display = noHeating ? 'none' : 'inline';
        this.content.querySelector("#gPipe").style.display = 'inline';
        this.content.querySelector("#gPipeBuffer").style.display = config.layeredChargeStorage ? 'none' : 'inline';
        this.content.querySelector("#gPipeLayeredChargeStorage").style.display = config.layeredChargeStorage ? 'inline' : 'none';
        this.content.querySelector("#gWWHeatingValve").style.display = config.layeredChargeStorage ? 'none' : 'inline';
        this.content.querySelector("#gHP").removeAttribute("transform");
        this.content.querySelector("#gSettings").removeAttribute("transform");
      }

      if (!config.thermalSolarAvailable || config.thermalSolarAvailable === 'off') {
        this.content.querySelector('#gThermalSolar').style.display = 'none';
      } else {
        this.content.querySelector('#gThermalSolar').style.display = 'inline';
      }
      this.content.querySelector('#gThermalSolarPump').classList.remove("rotate");

      this.content.querySelector("#textLabel000").innerHTML = config.additionalLabel000 ? config.additionalLabel000 : '';
      this.content.querySelector("#textLabel001").innerHTML = config.additionalLabel001 ? config.additionalLabel001 : '';
      this.content.querySelector("#textLabel002").innerHTML = config.additionalLabel002 ? config.additionalLabel002 : '';
      this.content.querySelector("#textLabel003").innerHTML = config.additionalLabel003 ? config.additionalLabel003 : '';
      this.content.querySelector("#textLabel004").innerHTML = config.additionalLabel004 ? config.additionalLabel004 : '';
      this.content.querySelector("#textLabel005").innerHTML = config.additionalLabel005 ? config.additionalLabel005 : '';
      this.content.querySelector("#textLabel006").innerHTML = config.additionalLabel006 ? config.additionalLabel006 : '';
      this.content.querySelector("#textLabel007").innerHTML = config.additionalLabel007 ? config.additionalLabel007 : '';
      this.content.querySelector("#textLabel008").innerHTML = config.additionalLabel008 ? config.additionalLabel008 : '';
      this.content.querySelector("#textLabel009").innerHTML = config.additionalLabel009 ? config.additionalLabel009 : '';
      this.content.querySelector("#textLabel010").innerHTML = config.additionalLabel010 ? config.additionalLabel010 : '';
      this.content.querySelector("#textLabel011").innerHTML = config.additionalLabel011 ? config.additionalLabel011 : '';
      this.content.querySelector("#textLabel012").innerHTML = config.additionalLabel012 ? config.additionalLabel012 : '';

      this.setLinks();
    }
  }

  setLinks() {}
}

customElements.define("heat-pump-card", HeatPumpCard);

window.customCards = window.customCards || [];
window.customCards.push({
  type: "heat-pump-card",
  name: "Heat Pump Card",
  description: "A custom card displaying heat pump state"
});

/*
 * IP-Symcon adapter.
 * Sämtliche Darstellungslogik liegt in dieser Datei.
 */
window.SymconHeatPump = {
  init(payload) {
        let currentConfig = payload.config || {};
        let currentControls = payload.controls || {};
        let embeddedSvg = payload.svg || '';
        let embeddedLocalization = payload.localization || {};
        let currentData = payload.data || {};

        /*
         * Umschaltansicht wie beim Energiefluss-Modul:
         * full    = komplette Wärmepumpengrafik
         * compact = nur die obere Status-/Temperaturansicht
         *
         * Die Auswahl wird lokal im Browser/Handy gespeichert.
         */
        const VIEW_STORAGE_KEY = 'symcon-waermepumpe-view';
        let currentView = 'full';

        try {
            const storedView =
                window.localStorage.getItem(VIEW_STORAGE_KEY);

            if (
                storedView === 'compact'
                || storedView === 'full'
            ) {
                currentView = storedView;
            }
        } catch (error) {
            // LocalStorage ist optional.
        }

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

        const originalTempColor = HeatPumpClass.prototype.tempColor;

        /*
         * Einheitliche Temperatur-Farbskala für die gesamte Hydraulik:
         * 20 / 30 / 40 / 50 / 60 / 70 °C.
         * Dazwischen wird stufenlos interpoliert.
         */
        const getTemperatureColorStops = () => {
            const configured = Array.isArray(currentConfig.temperatureColorScale)
                ? currentConfig.temperatureColorScale
                : [];

            const defaults = [
                {temperature: 15, color: 26316},
                {temperature: 20, color: 2730472},
                {temperature: 25, color: 3790293},
                {temperature: 30, color: 16765286},
                {temperature: 35, color: 16769126},
                {temperature: 40, color: 16761395},
                {temperature: 45, color: 16750616},
                {temperature: 50, color: 16734744},
                {temperature: 55, color: 15018795},
                {temperature: 60, color: 16711680}
            ];

            const intToRgb = (value, fallback) => {
                const number = Number(value);
                if (!Number.isFinite(number)) {
                    return fallback;
                }

                const color = Math.max(0, Math.min(0xFFFFFF, Math.trunc(number)));

                return [
                    (color >> 16) & 0xFF,
                    (color >> 8) & 0xFF,
                    color & 0xFF
                ];
            };

            const fallbackRgb = [
                [0, 102, 204],
                [41, 169, 232],
                [57, 213, 213],
                [255, 209, 102],
                [255, 224, 102],
                [255, 194, 51],
                [255, 152, 24],
                [255, 90, 24],
                [229, 43, 43],
                [255, 0, 0]
            ];

            const stops = defaults.map((fallback, index) => {
                const item = configured[index] || fallback;
                const temperature = Number(item.temperature);

                return [
                    Number.isFinite(temperature)
                        ? temperature
                        : fallback.temperature,
                    intToRgb(item.color, fallbackRgb[index])
                ];
            });

            /*
             * Die Temperaturwerte dürfen frei konfiguriert werden.
             * Für die Interpolation sortieren wir sie automatisch aufsteigend.
             */
            stops.sort((a, b) => a[0] - b[0]);

            return stops;
        };

        const rgbToHex = (rgb) =>
            '#' + rgb.map((component) =>
                Math.max(0, Math.min(255, Math.round(component)))
                    .toString(16)
                    .padStart(2, '0')
            ).join('');

        const temperatureColor = (temperature) => {
            if (!currentConfig.useCustomTemperatureColors) {
                return null;
            }

            const value = Number(temperature);

            if (!Number.isFinite(value)) {
                return null;
            }

            const stops = getTemperatureColorStops();

            if (value <= stops[0][0]) {
                return rgbToHex(stops[0][1]);
            }

            const last = stops[stops.length - 1];
            if (value >= last[0]) {
                return rgbToHex(last[1]);
            }

            for (let i = 0; i < stops.length - 1; i++) {
                const [t1, c1] = stops[i];
                const [t2, c2] = stops[i + 1];

                if (value >= t1 && value <= t2) {
                    const factor = (value - t1) / (t2 - t1);

                    return rgbToHex(c1.map((component, index) =>
                        component + (c2[index] - component) * factor
                    ));
                }
            }

            return null;
        };

        const readStateNumber = (entity) => {
            if (!entity || !currentData || !currentData[entity]) {
                return null;
            }

            const raw = currentData[entity].value;
            const normalized = String(raw ?? '')
                .trim()
                .replace(',', '.')
                .replace(/[^0-9+\-.]/g, '');

            const value = Number(normalized);
            return Number.isFinite(value) ? value : null;
        };

        /*
         * Die Speicher der Original-Card verwenden tempColor().
         * Dadurch gilt automatisch dieselbe Skala für Puffer und Warmwasser.
         */
        HeatPumpClass.prototype.tempColor = function(temp) {
            if (!currentConfig.useCustomTemperatureColors) {
                if (typeof originalTempColor === 'function') {
                    return originalTempColor.call(this, temp);
                }

                return '#ffffff00';
            }

            return temperatureColor(temp) || '#ffffff00';
        };


        const isEntityOn = (entityName) => {
            if (!entityName || !currentData || !currentData[entityName]) {
                return false;
            }

            const item = currentData[entityName];
            if (item.binary) {
                return !!item.value;
            }

            const value = String(item.value ?? '').trim().toLowerCase();
            return ['1', 'true', 'on', 'yes', 'ja', 'ein', 'active', 'aktiv'].includes(value);
        };

        const setStroke = (svg, selector, value) => {
            const element = svg.querySelector(selector);
            if (element) {
                element.style.stroke = value;
            }
        };

        const applyRefrigerantCircuitMode = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;
            const cooling = stateIsOn(currentConfig.heatingPumpCoolingMode);

            const getText = (selector) => {
                const el = svg.querySelector(selector);
                return el ? el.textContent : '';
            };

            const setText = (selector, value) => {
                const el = svg.querySelector(selector);
                if (el) {
                    el.textContent = value;
                }
            };

            /*
             * Reversibler Kältekreis:
             *
             * Heizbetrieb:
             *   Außenseite links  = Verdampfer / Niederdruck
             *   Wasserseite rechts = Verflüssiger / Hochdruck
             *
             * Kühlbetrieb:
             *   Außenseite links  = Verflüssiger / Hochdruck
             *   Wasserseite rechts = Verdampfer / Niederdruck
             *
             * Verdichter und Expansionsventil bleiben an ihrer Position.
             * Wir spiegeln NICHT die komplette SVG, damit Texte und Symbole
             * nicht seitenverkehrt werden. Stattdessen werden die Funktionen
             * der beiden Wärmetauscher samt Messwerten sauber vertauscht.
             */

            if (!cooling) {
                return;
            }

            const evaporatorLabel = getText('#textEvaporator');
            const evaporatorPressure = getText('#textEvaporatorPressure');
            const evaporatorTemperature = getText('#textEvaporatorTemperature');

            const condenserLabel = getText('#textCondenser');
            const condenserPressure = getText('#textCondenserPressure');
            const condenserTemperature = getText('#textCondenserTemperature');

            setText('#textEvaporator', condenserLabel || 'Kondensator');
            setText('#textEvaporatorPressure', condenserPressure);
            setText('#textEvaporatorTemperature', condenserTemperature);

            setText('#textCondenser', evaporatorLabel || 'Verdampfer');
            setText('#textCondenserPressure', evaporatorPressure);
            setText('#textCondenserTemperature', evaporatorTemperature);

            /*
             * Die Original-SVG hat eindeutige IDs für genau die beiden
             * Wärmetauscher-Symbole:
             *
             * Verdampfer:
             *   #pathHPModelEvaporatorSymbol001
             *   #pathHPModelEvaporatorSymbol002
             *
             * Verflüssiger:
             *   #pathHPModelCondenserSymbol
             *
             * Im Kühlbetrieb werden ausschließlich diese drei Pfade verschoben.
             * Verdichter, Expansionsventil, Pumpen, Leitungen und Texte bleiben
             * unangetastet.
             */
            const evaporatorSymbols = [
                svg.querySelector('#pathHPModelEvaporatorSymbol001'),
                svg.querySelector('#pathHPModelEvaporatorSymbol002')
            ].filter(Boolean);

            const condenserSymbol = svg.querySelector('#pathHPModelCondenserSymbol');

            if (
                evaporatorSymbols.length === 2
                && condenserSymbol
                && svg.dataset.symconHeatExchangerSymbolsSwapped !== '1'
            ) {
                const unionBox = (elements) => {
                    const boxes = elements.map((element) => element.getBBox());

                    const minX = Math.min(...boxes.map((box) => box.x));
                    const minY = Math.min(...boxes.map((box) => box.y));
                    const maxX = Math.max(...boxes.map((box) => box.x + box.width));
                    const maxY = Math.max(...boxes.map((box) => box.y + box.height));

                    return {
                        cx: (minX + maxX) / 2,
                        cy: (minY + maxY) / 2
                    };
                };

                const evaporatorCenter = unionBox(evaporatorSymbols);
                const condenserCenter = unionBox([condenserSymbol]);

                const evaporatorDx = condenserCenter.cx - evaporatorCenter.cx;
                const evaporatorDy = condenserCenter.cy - evaporatorCenter.cy;

                const condenserDx = evaporatorCenter.cx - condenserCenter.cx;
                const condenserDy = evaporatorCenter.cy - condenserCenter.cy;

                const movePath = (element, dx, dy) => {
                    const originalTransform = element.getAttribute('transform') || '';

                    element.setAttribute(
                        'transform',
                        'translate(' + dx + ' ' + dy + ')'
                        + (originalTransform ? ' ' + originalTransform : '')
                    );
                };

                evaporatorSymbols.forEach((element) => {
                    movePath(element, evaporatorDx, evaporatorDy);
                });

                movePath(condenserSymbol, condenserDx, condenserDy);

                svg.dataset.symconHeatExchangerSymbolsSwapped = '1';
            }

            svg.dataset.refrigerantCircuitDirection = 'reversed';
        };

        const applyCoolingVisualization = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;
            const cooling = stateIsOn(currentConfig.heatingPumpCoolingMode);

            /*
             * WICHTIG:
             * Verdampfer/Kondensator und ihre Messwerte bleiben unverändert.
             * Die Original-Card kennt keine hydraulische Umschaltung im Kühlbetrieb.
             *
             * Wir ändern deshalb ausschließlich die Gebäudeseite:
             *
             * Heizen:
             *   Vorlauf  = warm / rot
             *   Rücklauf = kalt / blau
             *
             * Kühlen:
             *   Vorlauf  = kalt / blau
             *   Rücklauf = wärmer / rot
             */
            const supplyColor = cooling ? '#0a84ff' : '#ff3b30';
            const returnColor = cooling ? '#ff3b30' : '#0a84ff';

            const setStrokeColor = (selector, color) => {
                const element = svg.querySelector(selector);
                if (element) {
                    element.style.setProperty('stroke', color, 'important');
                }
            };

            const setFillColor = (selector, color) => {
                const element = svg.querySelector(selector);
                if (element) {
                    element.style.setProperty('fill', color, 'important');
                }
            };

            // Heizwasserseite / Puffer / Heizkreise.
            // Selektoren, die in einer bestimmten Topologie nicht existieren,
            // werden einfach übersprungen.
            [
                '#pathPipeToBuffer',
                '#pathPipeToHeatingCircuitPump',
                '#pathPipeToHeatingCircuitPump2',
                '#pathPipeToHeatingCircuitPump3',
                '#pathPipeBufferToHeating',
                '#pathPipeTankHPToHeating',
                '#pathPipeHeatingSupply',
                '#pathPipeHeatingSupply2',
                '#pathPipeHeatingSupply3'
            ].forEach((selector) => setStrokeColor(selector, supplyColor));

            [
                '#pathPipeFromBuffer',
                '#pathPipeToHP',
                '#pathPipeToHP2',
                '#pathPipeHeatingToBuffer',
                '#pathPipeHeatingReturn',
                '#pathPipeHeatingReturn2',
                '#pathPipeHeatingReturn3'
            ].forEach((selector) => setStrokeColor(selector, returnColor));

            // Falls die Heizkreise ihre Farben über SVG-Verläufe beziehen,
            // diese im Kühlbetrieb ebenfalls umkehren.
            const gradientPairs = [
                ['#stopCircuit1', '#stopCircuit2'],
                ['#stopCircuit3', '#stopCircuit4'],
                ['#stopCircuit5', '#stopCircuit6']
            ];

            gradientPairs.forEach(([supplyStopSelector, returnStopSelector]) => {
                const supplyStop = svg.querySelector(supplyStopSelector);
                const returnStop = svg.querySelector(returnStopSelector);

                if (supplyStop) {
                    supplyStop.style.setProperty('stop-color', supplyColor, 'important');
                }

                if (returnStop) {
                    returnStop.style.setProperty('stop-color', returnColor, 'important');
                }
            });

            // Heizkreis-Symbole optional leicht an den Modus anpassen, ohne
            // die originale Geometrie/Topologie zu verändern.
            if (cooling) {
                [
                    '#gHeaterCircuit1',
                    '#gHeaterCircuit2',
                    '#gHeaterCircuit3'
                ].forEach((selector) => {
                    const group = svg.querySelector(selector);
                    if (group) {
                        group.dataset.symconCooling = '1';
                    }
                });
            } else {
                [
                    '#gHeaterCircuit1',
                    '#gHeaterCircuit2',
                    '#gHeaterCircuit3'
                ].forEach((selector) => {
                    const group = svg.querySelector(selector);
                    if (group) {
                        delete group.dataset.symconCooling;
                    }
                });
            }

            // Nur als interner Marker für spätere Erweiterungen.
            svg.dataset.operatingMode = cooling ? 'cooling' : 'heating';
        };

        const formatStateForSvg = (entityName) => {
            if (!entityName || !currentData || !currentData[entityName]) {
                return '';
            }

            const item = currentData[entityName];
            const raw = item.value;

            if (raw === null || raw === undefined || raw === '') {
                return '';
            }

            const numeric = Number(raw);
            const unit = item.unit ? String(item.unit).trim() : '';

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
            /*
             * Symcon stellt dem HTML-SDK die aktuelle Designfarbe über
             * --content-color bereit. Diese verwenden wir direkt statt
             * einer eigenen Hell-/Dunkel-Erkennung.
             *
             * Damit folgt die Card automatisch dem tatsächlich gewählten
             * Symcon-Design:
             *   helles Layout  -> dunkle/schwarze Content-Farbe
             *   dunkles Layout -> helle/weiße Content-Farbe
             */
            const candidates = [
                document.documentElement,
                document.body,
                document.getElementById('wp-root')
            ].filter(Boolean);

            for (const element of candidates) {
                const value = getComputedStyle(element)
                    .getPropertyValue('--content-color')
                    .trim();

                if (value) {
                    return value;
                }
            }

            /*
             * Nur als Sicherheitsfallback, falls eine ältere Umgebung
             * --content-color nicht bereitstellt.
             */
            const bodyColor =
                getComputedStyle(document.body).color;

            return bodyColor || '#ffffff';
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

        const isWhite = (value) => {
            const normalized = String(value || '')
                .trim()
                .toLowerCase()
                .replace(/\s+/g, '');

            return [
                'white',
                '#fff',
                '#ffffff',
                'rgb(255,255,255)',
                'rgba(255,255,255,1)'
            ].includes(normalized);
        };

        const isNeutralThemeColor = (value) => {
            return isBlack(value) || isWhite(value);
        };

        const applyThemeColors = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;
            const textColor = resolveLayoutTextColor();

            /*
             * Symcon-Design direkt in die SVG weiterreichen.
             */
            svg.style.setProperty(
                '--content-color',
                textColor,
                'important'
            );
            svg.style.setProperty(
                '--primary-text-color',
                textColor,
                'important'
            );
            svg.style.setProperty(
                'color',
                textColor,
                'important'
            );

            svg.style.setProperty(
                'background',
                'transparent',
                'important'
            );
            svg.style.setProperty(
                'background-color',
                'transparent',
                'important'
            );
            svg.style.setProperty(
                '--card-background-color',
                'transparent'
            );

            /*
             * Sämtliche normale Beschriftung folgt --content-color.
             */
            svg.querySelectorAll('text, tspan').forEach((element) => {
                element.style.setProperty(
                    'fill',
                    'var(--content-color)',
                    'important'
                );
                element.style.setProperty(
                    'color',
                    'var(--content-color)',
                    'important'
                );
            });

            /*
             * Nur neutrale Originalfarben (weiß/schwarz) umstellen.
             * Temperaturfarben, Speicherfarben, Kältekreisgradienten und
             * Flussanimationen werden nicht verändert.
             */
            svg.querySelectorAll('*').forEach((element) => {
                if (
                    element.classList
                    && element.classList.contains(
                        'symcon-flow-overlay'
                    )
                ) {
                    return;
                }

                const computed = getComputedStyle(element);

                const fill =
                    element.getAttribute('fill')
                    || element.style.fill
                    || computed.fill;

                const stroke =
                    element.getAttribute('stroke')
                    || element.style.stroke
                    || computed.stroke;

                const fillText = String(fill || '');
                const strokeText = String(stroke || '');

                if (
                    !fillText.includes('url(')
                    && isNeutralThemeColor(fillText)
                ) {
                    element.style.setProperty(
                        'fill',
                        'var(--content-color)',
                        'important'
                    );
                }

                if (
                    !strokeText.includes('url(')
                    && isNeutralThemeColor(strokeText)
                ) {
                    element.style.setProperty(
                        'stroke',
                        'var(--content-color)',
                        'important'
                    );
                }
            });
        };

        const renderCompactView = (card) => {
            const host =
                document.getElementById('wp-compact-view');

            if (
                !host
                || !card
                || !card.content
            ) {
                return;
            }

            const sourceSvg = card.content;
            const sourceSettings =
                sourceSvg.querySelector('#gSettings');

            if (!sourceSettings) {
                return;
            }

            /*
             * KOMPAKTANSICHT WIRD EIGENSTÄNDIG NEU GERENDERT.
             * ----------------------------------------------------------
             * Kein Zoomen der Vollansicht und kein viewBox-Umschalten
             * am Original-SVG.
             *
             * Wir erzeugen ein eigenes SVG und übernehmen nur den bereits
             * fertig aufbereiteten Kopfbereich. Dadurch bleibt die
             * Vollansicht komplett unangetastet.
             */
            host.innerHTML = '';

            const ns =
                'http://www.w3.org/2000/svg';
            const xlinkNs =
                'http://www.w3.org/1999/xlink';

            const compactSvg =
                document.createElementNS(ns, 'svg');

            compactSvg.setAttribute(
                'viewBox',
                '45 35 515 305'
            );
            compactSvg.setAttribute(
                'preserveAspectRatio',
                'xMidYMid meet'
            );
            compactSvg.setAttribute(
                'role',
                'img'
            );

            /*
             * Referenzierte SVG-Symbole (<use>) müssen auch im neuen
             * eigenständigen SVG vorhanden sein. Das betrifft insbesondere
             * den originalen Warmwasserhahn.
             */
            const defs =
                document.createElementNS(ns, 'defs');

            const originalDefs =
                sourceSvg.querySelector('defs');

            if (originalDefs) {
                Array.from(originalDefs.children)
                    .forEach((child) => {
                        defs.appendChild(
                            child.cloneNode(true)
                        );
                    });
            }

            const settingsClone =
                sourceSettings.cloneNode(true);

            const copiedIds = new Set(
                Array.from(
                    defs.querySelectorAll('[id]')
                ).map((element) => element.id)
            );

            const copyUseTargets = (root) => {
                root.querySelectorAll('use')
                    .forEach((use) => {
                        const href =
                            use.getAttribute('href')
                            || use.getAttributeNS(
                                xlinkNs,
                                'href'
                            )
                            || use.getAttribute(
                                'xlink:href'
                            );

                        if (
                            !href
                            || !href.startsWith('#')
                        ) {
                            return;
                        }

                        const id = href.slice(1);

                        if (copiedIds.has(id)) {
                            return;
                        }

                        const source =
                            sourceSvg.querySelector(
                                '#' + CSS.escape(id)
                            );

                        if (!source) {
                            return;
                        }

                        const clone =
                            source.cloneNode(true);

                        defs.appendChild(clone);
                        copiedIds.add(id);

                        /*
                         * Falls das kopierte Symbol selbst weitere <use>
                         * enthält, deren Ziele ebenfalls übernehmen.
                         */
                        copyUseTargets(clone);
                    });
            };

            copyUseTargets(settingsClone);

            if (defs.childNodes.length > 0) {
                compactSvg.appendChild(defs);
            }

            compactSvg.appendChild(settingsClone);
            host.appendChild(compactSvg);

            /*
             * Die kopierten Symbole bekommen eigene Touch-/Popup-Handler,
             * weil DOM-Eventlistener beim cloneNode absichtlich nicht
             * übernommen werden.
             */
            [
                {
                    selector: '#gHPStatusHeating',
                    functionName: 'heating'
                },
                {
                    selector: '#gHPStatusWW',
                    functionName: 'hotwater'
                },
                {
                    selector: '#gHPStatusCooling',
                    functionName: 'cooling'
                }
            ].forEach((definition) => {
                const element =
                    compactSvg.querySelector(
                        definition.selector
                    );

                const control =
                    currentControls
                    && currentControls[
                        definition.functionName
                    ];

                if (
                    !element
                    || !control
                    || !control.configured
                    || !Array.isArray(control.options)
                    || control.options.length === 0
                ) {
                    return;
                }

                element.style.setProperty(
                    'cursor',
                    'pointer',
                    'important'
                );
                element.style.setProperty(
                    'pointer-events',
                    'all',
                    'important'
                );

                bindTap(
                    element,
                    (event) =>
                        openModeMenu(
                            definition.functionName,
                            event
                        )
                );
            });

            [
                {
                    selector:
                        '#gSymconWarmWaterSetpoint',
                    functionName:
                        'warmWaterSetpoint'
                },
                {
                    selector:
                        '#gSymconHeatingCorrection',
                    functionName:
                        'heatingTemperatureCorrection'
                }
            ].forEach((definition) => {
                const element =
                    compactSvg.querySelector(
                        definition.selector
                    );

                if (!element) {
                    return;
                }

                element.style.setProperty(
                    'cursor',
                    'pointer',
                    'important'
                );
                element.style.setProperty(
                    'pointer-events',
                    'all',
                    'important'
                );

                bindTap(
                    element,
                    (event) =>
                        openNumericMenu(
                            definition.functionName,
                            event
                        )
                );
            });
        };

        const applyViewMode = (card) => {
            const full =
                document.getElementById('wp-card');
            const compact =
                document.getElementById(
                    'wp-compact-view'
                );
            const toggle =
                document.getElementById(
                    'wp-view-toggle'
                );

            const compactMode =
                currentView === 'compact';

            if (full) {
                full.style.setProperty(
                    'display',
                    compactMode
                        ? 'none'
                        : 'block',
                    'important'
                );
            }

            if (compact) {
                compact.style.setProperty(
                    'display',
                    compactMode
                        ? 'flex'
                        : 'none',
                    'important'
                );
                compact.setAttribute(
                    'aria-hidden',
                    compactMode
                        ? 'false'
                        : 'true'
                );
            }

            if (toggle) {
                toggle.textContent = '⇄';
                toggle.title =
                    compactMode
                        ? 'Komplette Ansicht'
                        : 'Kompakte Ansicht';
                toggle.setAttribute(
                    'aria-label',
                    toggle.title
                );
            }
        };

        const setupViewToggle = () => {
            const toggle =
                document.getElementById('wp-view-toggle');

            if (!toggle || toggle.dataset.symconBound) {
                return;
            }

            toggle.dataset.symconBound = '1';

            const switchView = (event) => {
                event.preventDefault();
                event.stopPropagation();

                currentView =
                    currentView === 'compact'
                        ? 'full'
                        : 'compact';

                try {
                    window.localStorage.setItem(
                        VIEW_STORAGE_KEY,
                        currentView
                    );
                } catch (error) {
                    // LocalStorage ist optional.
                }

                const card =
                    document.getElementById('wp-card');

                if (currentView === 'compact') {
                    renderCompactView(card);
                }

                applyViewMode(card);
            };

            if (window.PointerEvent) {
                toggle.addEventListener(
                    'pointerup',
                    (event) => {
                        if (
                            event.pointerType === 'touch'
                            || event.pointerType === 'pen'
                        ) {
                            switchView(event);
                        }
                    }
                );
            }

            toggle.addEventListener(
                'click',
                (event) => {
                    /*
                     * Bei Touch wird der Klick nach pointerup teilweise
                     * zusätzlich erzeugt. Dann nicht doppelt umschalten.
                     */
                    if (
                        event.detail === 0
                        && (
                            'ontouchstart' in window
                            || navigator.maxTouchPoints > 0
                        )
                    ) {
                        return;
                    }

                    switchView(event);
                }
            );
        };

        const rebuildCard = () => {
            const root = document.getElementById('wp-root');

            if (!root) {
                showError('Der Wärmepumpen-Container wurde im HTML nicht gefunden.');
                return;
            }

            closeModeMenu();

            const oldCard = document.getElementById('wp-card');
            const newCard = document.createElement('heat-pump-card');
            newCard.id = 'wp-card';

            if (oldCard) {
                oldCard.replaceWith(newCard);
            } else {
                const menu = document.getElementById('wp-mode-menu');
                if (menu) {
                    root.insertBefore(newCard, menu);
                } else {
                    root.appendChild(newCard);
                }
            }

            requestAnimationFrame(() => {
                queueMicrotask(() => applyCardData());
            });
        };

        const stateIsOn = (entityName) => {
            if (!entityName || !currentData || !currentData[entityName]) {
                return false;
            }

            const item = currentData[entityName];
            if (item.binary) {
                return !!item.value;
            }

            return ['1', 'true', 'on', 'yes', 'ja', 'ein', 'active', 'aktiv']
                .includes(String(item.value ?? '').trim().toLowerCase());
        };

        const disableOriginalSettingsLink = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;
            const link = svg.querySelector('#linkSettings');

            if (!link || !link.parentNode) {
                return;
            }

            /*
             * Die Original-SVG legt die komplette Statusleiste in
             * <a id="linkSettings" href="#"> ... </a>.
             *
             * Auf dem Handy erzeugt Symcon/WebView daraus eine zusätzliche
             * Link-/Pfeilfläche. Ein preventDefault auf touchend ist aber
             * ebenfalls falsch, weil dadurch der nachfolgende click auf
             * unseren Icons unterdrückt werden kann.
             *
             * Lösung: den <a>-Wrapper komplett entfernen, die Kinder aber
             * unverändert an derselben Stelle im SVG belassen.
             */
            const parent = link.parentNode;

            while (link.firstChild) {
                parent.insertBefore(link.firstChild, link);
            }

            parent.removeChild(link);
        };


        const closeModeMenu = () => {
            const menu = document.getElementById('wp-mode-menu');
            if (menu) {
                menu.style.display = 'none';
                menu.innerHTML = '';
            }
        };

        const openModeMenu = (functionName, event) => {
            event.preventDefault();
            event.stopPropagation();

            const control = currentControls && currentControls[functionName];
            if (!control || !control.configured || !Array.isArray(control.options) || control.options.length === 0) {
                return;
            }

            const menu = document.getElementById('wp-mode-menu');
            if (!menu) {
                return;
            }

            const titles = {
                heating: 'Heizen',
                hotwater: 'Warmwasser',
                cooling: 'Kühlen'
            };

            menu.innerHTML = '';

            const title = document.createElement('div');
            title.className = 'wp-mode-title';
            title.textContent = titles[functionName] || functionName;
            menu.appendChild(title);

            const valuesEqual = (left, right) => {
                const leftNumber = Number(left);
                const rightNumber = Number(right);

                if (!Number.isNaN(leftNumber) && !Number.isNaN(rightNumber)) {
                    return leftNumber === rightNumber;
                }

                return String(left) === String(right);
            };

            control.options.forEach((option) => {
                const button = document.createElement('button');
                button.type = 'button';

                const selected = valuesEqual(option.value, control.currentValue);

                button.textContent = (selected ? '✓ ' : '   ') + option.name;
                button.dataset.value = String(option.value);

                if (selected) {
                    button.classList.add('active');
                    button.setAttribute('aria-current', 'true');
                }

                button.addEventListener('click', (clickEvent) => {
                    clickEvent.preventDefault();
                    clickEvent.stopPropagation();

                    requestAction(
                        'SetControlMode',
                        JSON.stringify({
                            function: functionName,
                            value: option.value
                        })
                    );

                    closeModeMenu();
                });

                menu.appendChild(button);
            });

            menu.style.display = 'block';
        };

        const openNumericMenu = (functionName, event) => {
            event.preventDefault();
            event.stopPropagation();

            const control = currentControls && currentControls[functionName];
            if (!control || !control.configured) {
                return;
            }

            const menu = document.getElementById('wp-mode-menu');
            if (!menu) {
                return;
            }

            const titles = {
                warmWaterSetpoint: 'Warmwasser-Solltemperatur',
                heatingTemperatureCorrection: 'Heiztemperaturkorrektur'
            };

            const min = Number(control.min);
            const max = Number(control.max);
            const step = Number(control.step) > 0 ? Number(control.step) : 0.5;
            const current = Number(control.currentValue);

            menu.innerHTML = '';

            const title = document.createElement('div');
            title.className = 'wp-mode-title';
            title.textContent = titles[functionName] || functionName;
            menu.appendChild(title);

            const row = document.createElement('div');
            row.className = 'wp-numeric-row';

            const minus = document.createElement('button');
            minus.type = 'button';
            minus.textContent = '−';

            const input = document.createElement('input');
            input.type = 'number';
            input.step = String(step);
            if (Number.isFinite(min)) input.min = String(min);
            if (Number.isFinite(max)) input.max = String(max);
            input.value = Number.isFinite(current) ? String(current) : '0';

            const unit = document.createElement('span');
            unit.className = 'wp-numeric-unit';
            unit.textContent = control.unit || '';

            const plus = document.createElement('button');
            plus.type = 'button';
            plus.textContent = '+';

            const clamp = (value) => {
                let result = value;
                if (Number.isFinite(min)) result = Math.max(min, result);
                if (Number.isFinite(max)) result = Math.min(max, result);
                return Math.round(result / step) * step;
            };

            minus.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                input.value = String(
                    clamp(Number(input.value || 0) - step)
                );
            });

            plus.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                input.value = String(
                    clamp(Number(input.value || 0) + step)
                );
            });

            row.appendChild(minus);
            row.appendChild(input);
            row.appendChild(unit);
            row.appendChild(plus);
            menu.appendChild(row);

            const apply = document.createElement('button');
            apply.type = 'button';
            apply.textContent = 'Übernehmen';
            apply.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const value = clamp(Number(input.value));
                if (!Number.isFinite(value)) {
                    return;
                }

                requestAction(
                    'SetNumericControl',
                    JSON.stringify({
                        function: functionName,
                        value
                    })
                );

                closeModeMenu();
            });
            menu.appendChild(apply);

            menu.style.display = 'block';

            /*
             * Auf Desktop Feld direkt fokussieren.
             * Auf Touch-Geräten nicht automatisch fokussieren, weil sonst
             * WebView/Browser zusätzliche Eingabe-/Pfeilflächen öffnen kann.
             */
            const touchDevice =
                ('ontouchstart' in window)
                || (
                    navigator.maxTouchPoints
                    && navigator.maxTouchPoints > 0
                );

            if (!touchDevice) {
                input.focus();
                input.select();
            }
        };


        const setIconColor = (group, color) => {
            if (!group) {
                return;
            }

            const tag = String(group.tagName || '').toLowerCase();

            group.setAttribute('color', color);
            group.setAttribute('fill', color);
            group.setAttribute('stroke', color);
            group.style.setProperty('color', color, 'important');
            group.style.setProperty('fill', color, 'important');

            /*
             * Warmwasser ist in der Original-SVG kein <g>, sondern ein
             * einzelnes <use xlink:href="#gWaterTap">. Genau dieses Element
             * muss direkt eingefärbt werden.
             */
            if (tag === 'use') {
                group.style.setProperty('fill', color, 'important');
                group.style.setProperty('stroke', color, 'important');
                return;
            }

            group.querySelectorAll(
                'path, rect, circle, ellipse, line, polyline, polygon, use'
            ).forEach((element) => {
                const computed = getComputedStyle(element);
                const fill = String(computed.fill || '').toLowerCase();
                const stroke = String(computed.stroke || '').toLowerCase();

                if (
                    fill !== 'none'
                    && fill !== 'rgba(0, 0, 0, 0)'
                    && fill !== 'transparent'
                ) {
                    element.setAttribute('fill', color);
                    element.style.setProperty('fill', color, 'important');
                }

                if (
                    stroke !== 'none'
                    && stroke !== 'rgba(0, 0, 0, 0)'
                    && stroke !== 'transparent'
                ) {
                    element.setAttribute('stroke', color);
                    element.style.setProperty('stroke', color, 'important');
                }
            });
        };

        const applyWWValvePipeGeometry = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            const pipeToBuffer = svg.querySelector('#pathPipeToBuffer');
            const pipeFromBuffer = svg.querySelector('#pathPipeFromBuffer');
            const pipeHotCold = svg.querySelector('#pathPipeHotColdHeatpump');
            const pipeHotWater = svg.querySelector('#pathPipeHotWaterToTank');

            /*
             * Das Umschaltventil sitzt bei (620 / 450), Radius 30.
             * Es gibt drei Leitungsstücke, die in den Ventilbereich hineinlaufen:
             *
             * 1. pathPipeToBuffer          -> nach oben
             * 2. pathPipeHotColdHeatpump  -> von links
             * 3. pathPipeHotWaterToTank   -> vom Warmwasserspeicher
             *
             * Nur diese drei Endstücke werden am Ventilrand gekürzt.
             */

            if (pipeToBuffer) {
                const original =
                    'm 598,450 c 22,0 22,-15 22,-30 V 158 c 0,-12 5,-18 18,-18 H 750';

                const withValve =
                    'M 620,420 V 158 c 0,-12 5,-18 18,-18 H 750';

                pipeToBuffer.setAttribute(
                    'd',
                    currentConfig.wwHeatingValve ? withValve : original
                );
            }

            if (pipeFromBuffer) {
                const original =
                    'm 997,265 c 0,15 0,30 -22,30 H 650 c -15,0 -22,13 -22,30 v 170 c 0,18 -18,30 -30,30';

                /*
                 * Blaue Rücklaufleitung:
                 * Der senkrechte Abschnitt liegt bei x=628 und läuft damit
                 * durch den Ventilkreis (Mittelpunkt 620/450, r=30).
                 * Nur der Bereich innerhalb des Kreises wird ausgespart.
                 */
                const withValve =
                    'm 997,265 c 0,15 0,30 -22,30 H 650 c -15,0 -22,13 -22,30 V 420 '
                    + 'M 628,480 V 495 c 0,18 -18,30 -30,30';

                pipeFromBuffer.setAttribute(
                    'd',
                    currentConfig.wwHeatingValve ? withValve : original
                );
            }

            if (pipeHotCold) {
                const original =
                    'm 598,525 h -85 c -15,0 -30,0 -35,-15 0,-15 15,-15 30,-15 15,0 30,0 30,10 0,5 -15,5 -30,5 -15,0 -30,0 -30,-15 0,-15 15,-15 30,-15 15,0 30,0 30,10 0,5 -15,5 -30,5 -15,0 -30,0 -30,-15 0,-15 15,-15 30,-15 15,0 30,0 30,10 0,5 -15,5 -30,5 -15,0 -30,0 -30,-15 0,-15 15,-15 30,-15 h 90';

                /*
                 * Original endet bei x=598 / y=450 und läuft damit in den
                 * Ventilkreis hinein. Mit Ventil endet der letzte horizontale
                 * Abschnitt bereits bei x=590, also exakt am linken Kreisrand.
                 */
                const withValve =
                    'm 598,525 h -85 c -15,0 -30,0 -35,-15 0,-15 15,-15 30,-15 15,0 30,0 30,10 0,5 -15,5 -30,5 -15,0 -30,0 -30,-15 0,-15 15,-15 30,-15 15,0 30,0 30,10 0,5 -15,5 -30,5 -15,0 -30,0 -30,-15 0,-15 15,-15 30,-15 15,0 30,0 30,10 0,5 -15,5 -30,5 -15,0 -30,0 -30,-15 0,-15 15,-15 30,-15 h 82';

                pipeHotCold.setAttribute(
                    'd',
                    currentConfig.wwHeatingValve ? withValve : original
                );
            }

            if (pipeHotWater) {
                const original =
                    'm 598,525 h 95 c 15,0 30,0 35,-15 0,-15 -15,-15 -30,-15 -15,0 -30,0 -30,10 0,5 15,5 30,5 15,0 30,0 30,-15 0,-15 -15,-15 -30,-15 -15,0 -30,0 -30,10 0,5 15,5 30,5 15,0 30,0 30,-15 0,-15 -15,-15 -30,-15 -15,0 -30,0 -30,10 0,5 15,5 30,5 15,0 30,0 30,-15 0,-15 -15,-15 -30,-15 H 598';

                /*
                 * Dieser Pfad kommt von rechts aus Richtung Warmwasserspeicher.
                 * Mit Ventil endet er deshalb am rechten Kreisrand bei x=650.
                 */
                const withValve =
                    'm 598,525 h 95 c 15,0 30,0 35,-15 0,-15 -15,-15 -30,-15 -15,0 -30,0 -30,10 0,5 15,5 30,5 15,0 30,0 30,-15 0,-15 -15,-15 -30,-15 -15,0 -30,0 -30,10 0,5 15,5 30,5 15,0 30,0 30,-15 0,-15 -15,-15 -30,-15 -15,0 -30,0 -30,10 0,5 15,5 30,5 15,0 30,0 30,-15 0,-15 -15,-15 -30,-15 H 650';

                pipeHotWater.setAttribute(
                    'd',
                    currentConfig.wwHeatingValve ? withValve : original
                );
            }
        };

        const storedCircuitColorKey = 'symconHeatPumpStoredCircuitColors';

        const loadStoredCircuitColors = () => {
            try {
                const raw = window.sessionStorage
                    ? window.sessionStorage.getItem(storedCircuitColorKey)
                    : null;

                return raw ? JSON.parse(raw) : {};
            } catch (error) {
                return {};
            }
        };

        const saveStoredCircuitColors = (colors) => {
            try {
                if (window.sessionStorage) {
                    window.sessionStorage.setItem(
                        storedCircuitColorKey,
                        JSON.stringify(colors)
                    );
                }
            } catch (error) {
                // Speicherung ist nur Komfort; Darstellung funktioniert auch ohne.
            }
        };

        let storedCircuitColors = loadStoredCircuitColors();

        const restoreOriginalTemperatureColors = (card) => {
            if (!card || !card.content || currentConfig.useCustomTemperatureColors) {
                return;
            }

            const svg = card.content;

            /*
             * Entfernt ausschließlich unsere Inline-Überschreibungen.
             * Danach greifen wieder die im Original-SVG/der Original-Card
             * definierten Farben und Gradienten.
             */
            [
                '#pathPipeRefluxWW',
                '#pathPipeToCirculatingPump',
                '#pathPipeHotColdHeatpump',
                '#pathPipeToBuffer',
                '#pathPipeFromBuffer',
                '#pathPipeHotWaterToTank',
                '#pathPipeToHeatingCircuitPump',
                '#pathPipeToHeatingCircuitPump2',
                '#pathPipeToHeatingCircuitPump3',
                '#pathPipeToHP',
                '#pathPipeToHP2',
                '#pathPipeBufferToHeating',
                '#pathPipeHeatingToBuffer',
                '#pathHPModelCondenserSymbol',
                '#pathHPModelEvaporatorSymbol001',
                '#pathHPModelEvaporatorSymbol002',
                '#pathUnderfloorHeating1',
                '#pathUnderfloorHeating2',
                '#pathUnderfloorHeating3',
                '#pathRadiatorPipeIn1',
                '#pathRadiatorPipeIn2',
                '#pathRadiatorPipeIn3',
                '#pathRadiatorPipeOut1',
                '#pathRadiatorPipeOut2',
                '#pathRadiatorPipeOut3',
                '#rectRadiator1',
                '#rectRadiator2',
                '#rectRadiator3',
                '#pathTankWWChassis',
                '#pathTankHPChassis'
            ].forEach((selector) => {
                const element = svg.querySelector(selector);
                if (!element) {
                    return;
                }

                element.style.removeProperty('stroke');
                element.style.removeProperty('stroke-opacity');
                element.style.removeProperty('fill');
                element.style.removeProperty('fill-opacity');
            });

            /*
             * Boiler-Wendel wieder an den Originalgradienten hängen.
             */
            const boilerCoil = svg.querySelector('#pathPipeHotWaterToTank');
            if (boilerCoil) {
                boilerCoil.style.removeProperty('display');
                boilerCoil.style.removeProperty('visibility');
                boilerCoil.style.removeProperty('stroke-width');
                boilerCoil.style.removeProperty('stroke-opacity');
                boilerCoil.style.setProperty(
                    'stroke',
                    'url(#linearGradientPipe1)'
                );
            }

            const outline = svg.querySelector('#symconBoilerCoilOutline');
            if (outline && outline.parentNode) {
                outline.parentNode.removeChild(outline);
            }

            const customGradient = svg.querySelector('#symconLinearGradientBoilerCoil');
            if (customGradient && customGradient.parentNode) {
                customGradient.parentNode.removeChild(customGradient);
            }
        };

        const applyTemperatureColorOpacity = (card) => {
            if (!currentConfig.useCustomTemperatureColors) {
                return;
            }

            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            /*
             * Die Original-Card verwendet bei Speicher und Hydraulik teilweise
             * 50 % Deckkraft. Das verfälscht konfigurierte Temperaturfarben auf
             * dunklem Hintergrund erheblich (Rot wirkt z.B. braun).
             *
             * Temperaturfarben deshalb überall mit voller Deckkraft darstellen.
             */
            [
                '#pathTankWWChassis',
                '#pathTankHPChassis'
            ].forEach((selector) => {
                const element = svg.querySelector(selector);
                if (element) {
                    element.style.setProperty('fill-opacity', '1', 'important');
                }
            });

            [
                '#pathPipeRefluxWW',
                '#pathPipeToCirculatingPump',
                '#pathPipeHotColdHeatpump',
                '#pathPipeToBuffer',
                '#pathPipeFromBuffer',
                '#pathPipeHotWaterToTank',
                '#pathPipeToHeatingCircuitPump',
                '#pathPipeToHeatingCircuitPump2',
                '#pathPipeToHeatingCircuitPump3',
                '#pathPipeToHP',
                '#pathPipeToHP2',
                '#pathPipeBufferToHeating',
                '#pathPipeHeatingToBuffer'
            ].forEach((selector) => {
                const element = svg.querySelector(selector);
                if (element) {
                    element.style.setProperty('stroke-opacity', '1', 'important');
                }
            });
        };

        const applyHeatingCircuitTemperatureColors = (card) => {
            if (!currentConfig.useCustomTemperatureColors) {
                return;
            }

            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            /*
             * Hydraulische Stellung ausschließlich über das konfigurierte
             * Umschaltventil Warmwasser/Heizung bestimmen.
             *
             * Ventil aktiv = Boiler/Warmwasser
             * Ventil inaktiv = Heizkreis
             *
             * Ist KEIN Ventil konfiguriert, gibt es keine unterscheidbare
             * hydraulische Stellung. Dann verwenden Heizungs- und Boilerseite
             * denselben Vorlauf-/Rücklauf-Farbverlauf.
             */
            const valveConfigured = !!currentConfig.wwHeatingValve;
            const hotWaterActive =
                valveConfigured && stateIsOn(currentConfig.wwHeatingValve);

            const circuits = [
                {
                    key: '1',
                    gradient: 'linearGradientCircuit1',
                    supply: currentConfig.supplyTemperatureHeating,
                    reflux: currentConfig.refluxTemperatureHeating,
                    supplyPipes: ['#pathPipeToHeatingCircuitPump'],
                    refluxPipes: ['#pathPipeToHP']
                },
                {
                    key: '2',
                    gradient: 'linearGradientCircuit2',
                    supply: currentConfig.supplyTemperatureHeating2,
                    reflux: currentConfig.refluxTemperatureHeating2,
                    supplyPipes: ['#pathPipeToHeatingCircuitPump2'],
                    refluxPipes: ['#pathPipeToHP2']
                },
                {
                    key: '3',
                    gradient: 'linearGradientCircuit3',
                    supply: currentConfig.supplyTemperatureHeating3,
                    reflux: currentConfig.refluxTemperatureHeating3,
                    supplyPipes: ['#pathPipeToHeatingCircuitPump3'],
                    refluxPipes: []
                }
            ];

            const setStrokeColor = (selectors, color) => {
                if (!color) {
                    return;
                }

                selectors.forEach((selector) => {
                    const element = svg.querySelector(selector);
                    if (element) {
                        element.style.setProperty('stroke', color, 'important');
                        element.style.setProperty('stroke-opacity', '1', 'important');
                    }
                });
            };

            const setFillColor = (selectors, color) => {
                if (!color) {
                    return;
                }

                selectors.forEach((selector) => {
                    const element = svg.querySelector(selector);
                    if (element) {
                        element.style.setProperty('fill', color, 'important');
                        element.style.setProperty('fill-opacity', '1', 'important');
                    }
                });
            };

            const setGradient = (gradientId, supplyColor, refluxColor) => {
                const gradient = svg.querySelector('#' + gradientId);
                if (!gradient || !supplyColor || !refluxColor) {
                    return;
                }

                const stops = gradient.querySelectorAll('stop');
                if (stops.length < 2) {
                    return;
                }

                stops[0].style.setProperty('stop-color', supplyColor, 'important');
                stops[0].setAttribute('stop-color', supplyColor);

                stops[stops.length - 1].style.setProperty(
                    'stop-color',
                    refluxColor,
                    'important'
                );
                stops[stops.length - 1].setAttribute('stop-color', refluxColor);
            };

            const setGradientStops = (gradient, color1, color2) => {
                if (!gradient || !color1 || !color2) {
                    return;
                }

                const stops = gradient.querySelectorAll('stop');
                if (stops.length < 2) {
                    return;
                }

                stops[0].style.setProperty('stop-color', color1, 'important');
                stops[0].setAttribute('stop-color', color1);

                stops[stops.length - 1].style.setProperty(
                    'stop-color',
                    color2,
                    'important'
                );
                stops[stops.length - 1].setAttribute('stop-color', color2);
            };

            const setHeatPumpCoilGradient = (hotColor, coolColor) => {
                const gradient = svg.querySelector('#linearGradientPipe1');
                setGradientStops(gradient, hotColor, coolColor);

                const heatPumpCoil = svg.querySelector('#pathPipeHotColdHeatpump');
                if (heatPumpCoil) {
                    heatPumpCoil.style.setProperty(
                        'stroke',
                        'url(#linearGradientPipe1)',
                        'important'
                    );
                    heatPumpCoil.style.setProperty(
                        'stroke-opacity',
                        '1',
                        'important'
                    );
                }
            };

            const ensureBoilerCoilGradient = () => {
                let gradient = svg.querySelector('#symconLinearGradientBoilerCoil');
                if (gradient) {
                    return gradient;
                }

                const original = svg.querySelector('#linearGradientPipe1');
                if (!original) {
                    return null;
                }

                gradient = original.cloneNode(true);
                gradient.setAttribute('id', 'symconLinearGradientBoilerCoil');

                gradient.querySelectorAll('stop').forEach((stop, index) => {
                    stop.setAttribute(
                        'id',
                        'symconBoilerCoilStop' + (index + 1)
                    );
                });

                original.parentNode.appendChild(gradient);
                return gradient;
            };

            const setBoilerCoilGradient = (hotColor, coolColor) => {
                const gradient = ensureBoilerCoilGradient();
                setGradientStops(gradient, hotColor, coolColor);

                const boilerCoil = svg.querySelector('#pathPipeHotWaterToTank');
                if (!boilerCoil) {
                    return;
                }

                /*
                 * Die Original-Card blendet diese Wendel nicht abhängig vom
                 * Ventil aus. Bei gleicher Farbe von Boiler und Wendel würde sie
                 * aber optisch vollständig im Speicher verschwinden.
                 *
                 * Deshalb eine schmale Kontur UNTER der eigentlichen Wendel.
                 * Die sichtbare Wendel selbst behält exakt ihre Temperaturfarbe.
                 */
                let outline = svg.querySelector('#symconBoilerCoilOutline');

                if (!outline) {
                    outline = boilerCoil.cloneNode(false);
                    outline.setAttribute('id', 'symconBoilerCoilOutline');
                    boilerCoil.parentNode.insertBefore(outline, boilerCoil);
                }

                const dark = window.matchMedia
                    && window.matchMedia('(prefers-color-scheme: dark)').matches;

                outline.style.setProperty(
                    'stroke',
                    dark ? 'rgba(255,255,255,0.65)' : 'rgba(0,0,0,0.55)',
                    'important'
                );
                outline.style.setProperty('stroke-width', '7', 'important');
                outline.style.setProperty('stroke-opacity', '1', 'important');
                outline.style.setProperty('fill', 'none', 'important');
                outline.style.setProperty('display', 'inline', 'important');
                outline.style.setProperty('visibility', 'visible', 'important');

                boilerCoil.style.setProperty(
                    'stroke',
                    'url(#symconLinearGradientBoilerCoil)',
                    'important'
                );
                boilerCoil.style.setProperty('stroke-width', '5', 'important');
                boilerCoil.style.setProperty('stroke-opacity', '1', 'important');
                boilerCoil.style.setProperty('fill', 'none', 'important');
                boilerCoil.style.setProperty('display', 'inline', 'important');
                boilerCoil.style.setProperty('visibility', 'visible', 'important');
            };

            let firstHeatingColors = null;
            let storedChanged = false;

            circuits.forEach((circuit) => {
                const supplyTemperature = readStateNumber(circuit.supply);
                const refluxTemperature = readStateNumber(circuit.reflux);

                let colors = storedCircuitColors[circuit.key] || null;

                /*
                 * Nur im Heizbetrieb werden die gespeicherten Heizkreisfarben
                 * aktualisiert. Beim Umschalten auf Warmwasser bleiben Heizkörper/
                 * Fußbodenheizung optisch auf ihrem letzten Heiz-Zustand stehen.
                 */
                if (
                    !hotWaterActive
                    && supplyTemperature !== null
                    && refluxTemperature !== null
                ) {
                    colors = {
                        supply: temperatureColor(supplyTemperature),
                        reflux: temperatureColor(refluxTemperature)
                    };

                    storedCircuitColors[circuit.key] = colors;
                    storedChanged = true;
                }

                /*
                 * Falls die Seite erstmals während Warmwasserladung geöffnet wird
                 * und noch nichts gespeichert ist, nehmen wir einmal die aktuell
                 * vorhandenen Werte als Startzustand.
                 */
                if (
                    !colors
                    && supplyTemperature !== null
                    && refluxTemperature !== null
                ) {
                    colors = {
                        supply: temperatureColor(supplyTemperature),
                        reflux: temperatureColor(refluxTemperature)
                    };
                }

                if (!colors || !colors.supply || !colors.reflux) {
                    return;
                }

                if (!firstHeatingColors) {
                    firstHeatingColors = colors;
                }

                setGradient(circuit.gradient, colors.supply, colors.reflux);

                [
                    '#pathUnderfloorHeating' + circuit.key,
                    '#pathRadiatorPipeIn' + circuit.key,
                    '#pathRadiatorPipeOut' + circuit.key
                ].forEach((selector) => {
                    const element = svg.querySelector(selector);
                    if (element) {
                        element.style.setProperty('stroke-opacity', '1', 'important');
                    }
                });

                const radiator = svg.querySelector('#rectRadiator' + circuit.key);
                if (radiator) {
                    radiator.style.setProperty('stroke-opacity', '1', 'important');
                    radiator.style.setProperty('fill-opacity', '1', 'important');
                }

                setStrokeColor(circuit.supplyPipes, colors.supply);
                setStrokeColor(circuit.refluxPipes, colors.reflux);
            });

            if (storedChanged) {
                saveStoredCircuitColors(storedCircuitColors);
            }

            /*
             * Gemeinsame Hydraulik am Umschaltventil:
             *
             * Heizung aktiv:
             *   letzte/aktuelle Heizkreisfarbe.
             *
             * Warmwasser aktiv:
             *   die Wärmepumpen-Vorlauftemperatur färbt die Leitung zum Boiler
             *   und die Heizwendel im Boiler. Die Heizkreisfarben bleiben stehen.
             */
            if (hotWaterActive) {
                /*
                 * Während der Warmwasserladung bleiben die Heizkreis-Symbole auf
                 * ihren gespeicherten Heizfarben. Für Wärmepumpe und Boiler-Wendel
                 * verwenden wir dagegen die AKTUELLEN Vorlauf-/Rücklaufwerte.
                 *
                 * Priorität:
                 *   Heizkreis 1 -> Heizkreis 2 -> Heizkreis 3 -> WP-Vorlauf
                 */
                const currentSupplyTemperature =
                    readStateNumber(currentConfig.supplyTemperatureHeating)
                    ?? readStateNumber(currentConfig.supplyTemperatureHeating2)
                    ?? readStateNumber(currentConfig.supplyTemperatureHeating3)
                    ?? readStateNumber(currentConfig.supplyTemperature);

                const currentRefluxTemperature =
                    readStateNumber(currentConfig.refluxTemperatureHeating)
                    ?? readStateNumber(currentConfig.refluxTemperatureHeating2)
                    ?? readStateNumber(currentConfig.refluxTemperatureHeating3);

                const boilerTemperature =
                    readStateNumber(currentConfig.tankTempWWUp)
                    ?? readStateNumber(currentConfig.tankTempWWMiddle)
                    ?? readStateNumber(currentConfig.tankTempWWDown);

                const hotColor =
                    temperatureColor(currentSupplyTemperature)
                    || temperatureColor(boilerTemperature);

                const refluxColor =
                    temperatureColor(currentRefluxTemperature)
                    || temperatureColor(boilerTemperature)
                    || hotColor;

                const boilerColor =
                    temperatureColor(boilerTemperature)
                    || hotColor;

                /*
                 * Warmwasserleitung aus dem Boiler zum Zapfhahn:
                 * immer mit der aktuellen Boilertemperaturfarbe darstellen.
                 */
                setStrokeColor(
                    ['#pathPipeToCirculatingPump'],
                    boilerColor
                );

                /*
                 * Wärmepumpen-Wendel:
                 * aktuelle Vorlauf-/Rücklauftemperatur, unabhängig vom
                 * separaten Boiler-Gradienten.
                 */
                if (hotColor && refluxColor) {
                    setHeatPumpCoilGradient(
                        hotColor,
                        refluxColor
                    );
                }

                /*
                 * Warmwasserladung aktiv:
                 * Boiler-Wendel = aktuelle Vorlauf-/Rücklauftemperatur.
                 */
                setBoilerCoilGradient(
                    hotColor,
                    refluxColor
                );

                // Heizseite während Boilerladung vollständig eingefroren lassen.
                if (firstHeatingColors) {
                    setStrokeColor(
                        ['#pathPipeToBuffer'],
                        firstHeatingColors.supply
                    );
                    setStrokeColor(
                        [
                            '#pathPipeFromBuffer',
                            '#pathPipeToHP',
                            '#pathPipeToHP2'
                        ],
                        firstHeatingColors.reflux
                    );
                }

                /*
                 * Die Wärmeübertrager-Symbole in der Wärmepumpe übernehmen
                 * ebenfalls die gemeinsame Temperaturskala.
                 */
                const condenserTemperature =
                    readStateNumber(currentConfig.condenserTemperature);
                const evaporatorTemperature =
                    readStateNumber(currentConfig.evaporatorTemperature);

                const condenserColor = temperatureColor(condenserTemperature);
                const evaporatorColor = temperatureColor(evaporatorTemperature);

                setFillColor(
                    ['#pathHPModelCondenserSymbol'],
                    condenserColor
                );
                setStrokeColor(
                    [
                        '#pathHPModelEvaporatorSymbol001',
                        '#pathHPModelEvaporatorSymbol002'
                    ],
                    evaporatorColor
                );

                // Zusätzliche Boilerfarbe am Speicher-Heizstab, falls vorhanden.
                if (boilerColor) {
                    const heaterRodWW = svg.querySelector('#pathHeaterRodWW');
                    if (heaterRodWW) {
                        heaterRodWW.style.setProperty(
                            'stroke',
                            boilerColor,
                            'important'
                        );
                    }
                }

                return;
            }

            /*
             * Heizbetrieb: gemeinsame Leitungen folgen wieder dem Heizkreis.
             */
            if (firstHeatingColors) {
                setStrokeColor(
                    ['#pathPipeToBuffer'],
                    firstHeatingColors.supply
                );
                setStrokeColor(
                    [
                        '#pathPipeFromBuffer',
                        '#pathPipeToHP',
                        '#pathPipeToHP2'
                    ],
                    firstHeatingColors.reflux
                );

                setHeatPumpCoilGradient(
                    firstHeatingColors.supply,
                    firstHeatingColors.reflux
                );
            }

            /*
             * Warmwasserleitung aus dem Boiler zum Zapfhahn:
             * unabhängig vom Betriebsmodus mit der aktuellen Boilertemperatur.
             */
            const boilerTemperature =
                readStateNumber(currentConfig.tankTempWWUp)
                ?? readStateNumber(currentConfig.tankTempWWMiddle)
                ?? readStateNumber(currentConfig.tankTempWWDown);

            const boilerColor = temperatureColor(boilerTemperature);

            setStrokeColor(
                ['#pathPipeToCirculatingPump'],
                boilerColor
            );

            /*
             * Kein Umschaltventil konfiguriert:
             * Boiler- und Heizungsseite verwenden denselben Vorlauf-/Rücklauf-
             * Farbverlauf. So entstehen ohne Ventil keine widersprüchlichen
             * Farben für dieselbe hydraulische Verbindung.
             *
             * Ventil konfiguriert und auf Heizung:
             * Boiler-Wendel zeigt die aktuelle Boilertemperaturfarbe.
             */
            if (!valveConfigured && firstHeatingColors) {
                setBoilerCoilGradient(
                    firstHeatingColors.supply,
                    firstHeatingColors.reflux
                );
            } else if (boilerColor) {
                setBoilerCoilGradient(
                    boilerColor,
                    boilerColor
                );
            }

            /*
             * Wärmepumpen-Wärmetauscher ebenfalls immer mit Temperaturfarbe.
             */
            const condenserTemperature =
                readStateNumber(currentConfig.condenserTemperature);
            const evaporatorTemperature =
                readStateNumber(currentConfig.evaporatorTemperature);

            setFillColor(
                ['#pathHPModelCondenserSymbol'],
                temperatureColor(condenserTemperature)
            );
            setStrokeColor(
                [
                    '#pathHPModelEvaporatorSymbol001',
                    '#pathHPModelEvaporatorSymbol002'
                ],
                temperatureColor(evaporatorTemperature)
            );
        };

        const applyHeatingReturnContinuity = (card) => {
            if (!currentConfig.useCustomTemperatureColors) {
                return;
            }

            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            const hotWaterActive =
                !!currentConfig.wwHeatingValve
                && stateIsOn(currentConfig.wwHeatingValve);

            const readFirstNumber = (entities) => {
                for (const entity of entities) {
                    const value = readStateNumber(entity);
                    if (value !== null) {
                        return value;
                    }
                }

                return null;
            };

            let refluxColor = null;

            if (hotWaterActive) {
                /*
                 * Während Warmwasserladung die zuletzt gespeicherte Heizfarbe
                 * verwenden, damit der Heizkreis optisch eingefroren bleibt.
                 */
                const stored =
                    storedCircuitColors['1']
                    || storedCircuitColors['2']
                    || storedCircuitColors['3'];

                refluxColor = stored && stored.reflux
                    ? stored.reflux
                    : null;
            } else {
                const refluxTemperature = readFirstNumber([
                    currentConfig.refluxTemperatureHeating,
                    currentConfig.refluxTemperatureHeating2,
                    currentConfig.refluxTemperatureHeating3
                ]);

                refluxColor = temperatureColor(refluxTemperature);
            }

            if (!refluxColor) {
                return;
            }

            /*
             * Diese SVG-Elemente bilden die sichtbare Rücklaufkette rechts.
             * Besonders pathPipeToHP2 ist das kurze Teilstück, das sonst gerne
             * in der ursprünglichen blauen Card-Farbe stehen bleibt.
             */
            [
                '#pathPipeFromBuffer',
                '#pathPipeToHP',
                '#pathPipeToHP2'
            ].forEach((selector) => {
                const element = svg.querySelector(selector);

                if (!element) {
                    return;
                }

                element.setAttribute('stroke', refluxColor);
                element.style.setProperty('stroke', refluxColor, 'important');
            });
        };

        const storedHeatingTemperatureKey =
            'symconHeatPumpStoredHeatingTemperatures';

        const loadStoredHeatingTemperatures = () => {
            try {
                const raw = window.sessionStorage
                    ? window.sessionStorage.getItem(storedHeatingTemperatureKey)
                    : null;

                return raw ? JSON.parse(raw) : {};
            } catch (error) {
                return {};
            }
        };

        const saveStoredHeatingTemperatures = (values) => {
            try {
                if (window.sessionStorage) {
                    window.sessionStorage.setItem(
                        storedHeatingTemperatureKey,
                        JSON.stringify(values)
                    );
                }
            } catch (error) {
                // Anzeige funktioniert auch ohne Session-Speicher.
            }
        };

        let storedHeatingTemperatures = loadStoredHeatingTemperatures();

        const applyAdditionalValues = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            for (let index = 0; index < 13; index++) {
                const suffix = String(index).padStart(3, '0');
                const labelKey = 'additionalLabel' + suffix;
                const valueKey = 'additionalValue' + suffix;

                const labelElement =
                    svg.querySelector('#textLabel' + suffix);
                const valueElement =
                    svg.querySelector('#textValue' + suffix);

                const label = String(
                    currentConfig[labelKey] || ''
                ).trim();

                const entity = currentConfig[valueKey] || '';
                const value = entity
                    ? formatStateForSvg(entity)
                    : '';

                if (labelElement) {
                    labelElement.textContent = label;
                    labelElement.style.setProperty(
                        'display',
                        label ? 'inline' : 'none',
                        'important'
                    );
                }

                if (valueElement) {
                    valueElement.textContent = value;
                    valueElement.style.setProperty(
                        'display',
                        value ? 'inline' : 'none',
                        'important'
                    );
                }
            }
        };

        const normalizeTemperatureUnits = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            /*
             * Einheitliche Darstellung sämtlicher numerischer Temperaturen:
             * immer "°C".
             *
             * Die Original-Card mischt je nach Element Werte mit "℃", "°C"
             * oder ganz ohne Einheit. Wir normalisieren deshalb ausschließlich
             * Text-Elemente mit Temp/Temperature im ID-Namen UND einem Zahlenwert.
             * Beschriftungen wie "Vorlauftemperatur" bleiben unangetastet.
             */
            svg.querySelectorAll(
                'text[id*="Temp"], text[id*="Temperature"]'
            ).forEach((element) => {
                const raw = String(element.textContent || '').trim();

                if (!/[+-]?\d+(?:[.,]\d+)?/.test(raw)) {
                    return;
                }

                const match = raw.match(/[+-]?\d+(?:[.,]\d+)?/);
                if (!match) {
                    return;
                }

                element.textContent = match[0] + ' °C';
            });
        };

        const applyThermalSolarVisualization = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;
            const solarGroup = svg.querySelector('#gThermalSolar');

            if (!solarGroup) {
                return;
            }

            /*
             * Die Solarthermie-Gruppe wurde beim Einbetten der Original-SVG
             * zunächst auf display:none gesetzt. Wenn Solarthermie in Symcon
             * aktiviert ist, halten wir die gesamte Gruppe inklusive der
             * Solar-Wendel ausdrücklich sichtbar.
             */
            if (!currentConfig.thermalSolarAvailable) {
                solarGroup.style.setProperty('display', 'none', 'important');
                solarGroup.style.setProperty('visibility', 'hidden', 'important');

                const existingCoil =
                    svg.querySelector('#symconThermalSolarTankCoil');

                if (existingCoil) {
                    existingCoil.style.setProperty(
                        'display',
                        'none',
                        'important'
                    );
                    existingCoil.style.setProperty(
                        'visibility',
                        'hidden',
                        'important'
                    );
                }

                const existingHotConnector =
                    svg.querySelector('#symconThermalSolarHotConnector');

                if (existingHotConnector) {
                    existingHotConnector.style.setProperty(
                        'display',
                        'none',
                        'important'
                    );
                    existingHotConnector.style.setProperty(
                        'visibility',
                        'hidden',
                        'important'
                    );
                }

                const existingReturnText =
                    svg.querySelector('#symconThermalSolarReturnTemp');

                if (existingReturnText) {
                    existingReturnText.style.setProperty(
                        'display',
                        'none',
                        'important'
                    );
                    existingReturnText.style.setProperty(
                        'visibility',
                        'hidden',
                        'important'
                    );
                }

                return;
            }

            solarGroup.style.setProperty('display', 'inline', 'important');
            solarGroup.style.setProperty('visibility', 'visible', 'important');

            /*
             * Solarpumpe nur anzeigen, wenn dafür tatsächlich eine Variable
             * konfiguriert wurde. Die Solarthermie selbst kann also sichtbar sein,
             * ohne dass automatisch eine Pumpe dargestellt wird.
             */
            const solarPumpGroup = svg.querySelector('#gThermalSolarPump');
            if (solarPumpGroup) {
                const pumpConfigured = !!currentConfig.thermalSolarPump;

                solarPumpGroup.style.setProperty(
                    'display',
                    pumpConfigured ? 'inline' : 'none',
                    'important'
                );
                solarPumpGroup.style.setProperty(
                    'visibility',
                    pumpConfigured ? 'visible' : 'hidden',
                    'important'
                );
            }

            /*
             * Kühle Solar-Rücklauftemperatur nur anzeigen, wenn Solar Rücklauf
             * tatsächlich konfiguriert ist.
             */
            let solarReturnText =
                svg.querySelector('#symconThermalSolarReturnTemp');

            if (currentConfig.thermalSolarReturnTemp) {
                if (!solarReturnText) {
                    solarReturnText = document.createElementNS(
                        'http://www.w3.org/2000/svg',
                        'text'
                    );
                    solarReturnText.setAttribute(
                        'id',
                        'symconThermalSolarReturnTemp'
                    );
                    /*
                     * Gleiche X-Position wie textThermalSolarFluxTemp (x=850),
                     * direkt darunter.
                     */
                    /*
                     * Rücklauftemperatur auf derselben horizontalen Linie/
                     * X-Ausrichtung wie die vorhandene Solar-Temperatur,
                     * aber direkt unterhalb der kühlen Rücklaufleitung.
                     */
                    solarReturnText.setAttribute('x', '850');
                    solarReturnText.setAttribute('y', '634');
                    solarReturnText.setAttribute('text-anchor', 'end');
                    solarReturnText.setAttribute('xml:space', 'preserve');
                    solarReturnText.style.setProperty(
                        'font-size',
                        '16px',
                        'important'
                    );
                    solarReturnText.style.setProperty(
                        'fill',
                        'var(--primary-text-color)',
                        'important'
                    );

                    solarGroup.appendChild(solarReturnText);
                }

                const returnValue =
                    readStateNumber(currentConfig.thermalSolarReturnTemp);

                if (returnValue !== null) {
                    solarReturnText.textContent =
                        new Intl.NumberFormat('de-CH', {
                            minimumFractionDigits: 1,
                            maximumFractionDigits: 1
                        }).format(returnValue) + ' °C';
                } else {
                    solarReturnText.textContent = '';
                }

                solarReturnText.style.setProperty(
                    'display',
                    'inline',
                    'important'
                );
                solarReturnText.style.setProperty(
                    'visibility',
                    'visible',
                    'important'
                );
            } else if (solarReturnText) {
                solarReturnText.textContent = '';
                solarReturnText.style.setProperty(
                    'display',
                    'none',
                    'important'
                );
                solarReturnText.style.setProperty(
                    'visibility',
                    'hidden',
                    'important'
                );
            }

            /*
             * Die Original-SVG verwendet für die Solar-Wendel lediglich ein <use>
             * in gThermalSolar. Dieses liegt in der SVG-Reihenfolge HINTER der
             * später gezeichneten, gefüllten Boilerfläche und kann deshalb
             * vollständig verdeckt werden.
             *
             * Wir erzeugen daher einen eigenständigen Pfad direkt in gTankWW,
             * unmittelbar NACH dem Boiler-Chassis. So liegt die Solar-Wendel
             * sichtbar über der Speicherfüllung, aber weiterhin sauber innerhalb
             * des Boilers.
             */
            const tankGroup = svg.querySelector('#gTankWW');
            const tankChassis = svg.querySelector('#pathTankWWChassis');
            const sourceCoil = svg.querySelector('#pathPipeHotColdHeatpump');
            const originalSolarUse = svg.querySelector('#usePathPipeThermalSolarTank');

            let solarCoil = svg.querySelector('#symconThermalSolarTankCoil');

            if (!solarCoil && tankGroup && tankChassis && sourceCoil) {
                solarCoil = sourceCoil.cloneNode(false);
                solarCoil.setAttribute('id', 'symconThermalSolarTankCoil');
                solarCoil.setAttribute('transform', 'translate(193,90)');
                solarCoil.removeAttribute('style');

                if (tankChassis.nextSibling) {
                    tankGroup.insertBefore(solarCoil, tankChassis.nextSibling);
                } else {
                    tankGroup.appendChild(solarCoil);
                }
            }

            /*
             * Die ursprüngliche <use>-Kopie aus gThermalSolar unterdrücken,
             * damit bei halbtransparentem Original-Speicher keine doppelte
             * Wendel sichtbar wird.
             */
            if (originalSolarUse) {
                originalSolarUse.style.setProperty('display', 'none', 'important');
                originalSolarUse.style.setProperty('visibility', 'hidden', 'important');
            }

            if (solarCoil) {
                solarCoil.style.setProperty('display', 'inline', 'important');
                solarCoil.style.setProperty('visibility', 'visible', 'important');
                solarCoil.style.setProperty('fill', 'none', 'important');
                solarCoil.style.setProperty('stroke-width', '5', 'important');
                solarCoil.style.setProperty('stroke-opacity', '1', 'important');
            }

            /*
             * Kurzes heißes Verbindungsstück zwischen Solar-Vorlauf und
             * Solar-Wendel. In der Original-SVG kann dieser Bereich durch die
             * später gezeichnete Boilerfläche optisch unterbrochen werden.
             *
             * Deshalb als eigener Pfad direkt in gTankWW und damit oberhalb der
             * Speicherfüllung zeichnen.
             */
            let solarHotConnector =
                svg.querySelector('#symconThermalSolarHotConnector');

            if (!solarHotConnector && tankGroup) {
                solarHotConnector = document.createElementNS(
                    'http://www.w3.org/2000/svg',
                    'path'
                );
                solarHotConnector.setAttribute(
                    'id',
                    'symconThermalSolarHotConnector'
                );

                /*
                 * Eintritt der Solar-Wendel liegt bei y=540.
                 * Das kurze Stück verbindet Boilerwand (x=745) mit dem
                 * vorhandenen Solar-Vorlauf/Wendelanschluss (x=791).
                 */
                solarHotConnector.setAttribute(
                    'd',
                    'M 745 540 H 791'
                );
                solarHotConnector.setAttribute('fill', 'none');

                tankGroup.appendChild(solarHotConnector);
            }

            if (solarHotConnector) {
                solarHotConnector.style.setProperty(
                    'display',
                    'inline',
                    'important'
                );
                solarHotConnector.style.setProperty(
                    'visibility',
                    'visible',
                    'important'
                );
                solarHotConnector.style.setProperty(
                    'stroke-width',
                    '5',
                    'important'
                );
                solarHotConnector.style.setProperty(
                    'stroke-opacity',
                    '1',
                    'important'
                );
                solarHotConnector.style.setProperty(
                    'fill',
                    'none',
                    'important'
                );
            }

            /*
             * Bei abgeschalteten eigenen Temperaturfarben bleibt die originale
             * Farbgebung der Heat-Pump-Card bestehen.
             */
            if (!currentConfig.useCustomTemperatureColors) {
                [
                    '#pathPipeThermalSolarHotWater',
                    '#pathPipeThermalSolarColdWater',
                    '#rectThermalSolarPanel',
                    '#symconThermalSolarTankCoil'
                ].forEach((selector) => {
                    const element = svg.querySelector(selector);
                    if (!element) {
                        return;
                    }

                    element.style.removeProperty('stroke');
                    element.style.removeProperty('stroke-opacity');
                    element.style.removeProperty('fill');
                    element.style.removeProperty('fill-opacity');
                });

                if (solarCoil) {
                    solarCoil.style.setProperty(
                        'stroke',
                        'url(#linearGradientPipe1)',
                        'important'
                    );
                    solarCoil.style.setProperty(
                        'stroke-opacity',
                        '1',
                        'important'
                    );
                }

                if (solarHotConnector) {
                    solarHotConnector.style.setProperty(
                        'stroke',
                        '#ff0000',
                        'important'
                    );
                }

                return;
            }

            // Kollektortemperatur bleibt weiterhin als Messwert der Card erhalten.
            // Für die Farbrichtung des Panels sind aber Solar-Vorlauf und
            // Solar-Rücklauf maßgebend.
            const panelTemperature =
                readStateNumber(currentConfig.thermalSolarPanelTemp);

            const supplyTemperature =
                readStateNumber(currentConfig.thermalSolarFluxTemp);

            /*
             * Solar-Rücklauf ist neu optional konfigurierbar.
             * Fehlt er, verwenden wir die untere Boilertemperatur als sinnvolle
             * Näherung für das Fluid, das die Solar-Wendel wieder verlässt.
             */
            const returnTemperature =
                readStateNumber(currentConfig.thermalSolarReturnTemp)
                ?? readStateNumber(currentConfig.tankTempWWDown)
                ?? readStateNumber(currentConfig.tankTempWWMiddle)
                ?? readStateNumber(currentConfig.tankTempWWUp);

            void panelTemperature;

            const supplyColor = temperatureColor(supplyTemperature);
            const returnColor = temperatureColor(returnTemperature);

            const setStroke = (selector, color) => {
                const element = svg.querySelector(selector);
                if (!element || !color) {
                    return;
                }

                element.style.setProperty('stroke', color, 'important');
                element.style.setProperty('stroke-opacity', '1', 'important');
            };

            const setFill = (selector, color) => {
                const element = svg.querySelector(selector);
                if (!element || !color) {
                    return;
                }

                element.style.setProperty('fill', color, 'important');
                element.style.setProperty('fill-opacity', '1', 'important');
            };

            /*
             * Solarleitungen:
             * - pathPipeThermalSolarHotWater ist der heiße Vorlauf vom Panel
             *   zum Boiler.
             * - pathPipeThermalSolarColdWater ist der kühlere Rücklauf vom
             *   Boiler zurück zum Panel.
             */
            setStroke('#pathPipeThermalSolarHotWater', supplyColor);
            setStroke('#pathPipeThermalSolarColdWater', returnColor);
            setStroke('#symconThermalSolarHotConnector', supplyColor);

            /*
             * Solarpanel:
             * Die Originalgeometrie läuft von links (heißer Ausgang/Vorlauf)
             * nach rechts (kühler Eingang/Rücklauf).
             *
             * Daher muss der Panelverlauf zwingend sein:
             *   links  = Solar-Vorlauffarbe
             *   rechts = Solar-Rücklauffarbe
             *
             * Die Kollektortemperatur bleibt ein eigener Messwert und wird NICHT
             * mehr als Endfarbe des Panels verwendet.
             */
            let panelGradient = svg.querySelector(
                '#symconLinearGradientThermalSolarPanel'
            );

            if (!panelGradient) {
                const originalPanelGradient =
                    svg.querySelector('#linearGradient3');

                if (originalPanelGradient) {
                    panelGradient = originalPanelGradient.cloneNode(true);
                    panelGradient.setAttribute(
                        'id',
                        'symconLinearGradientThermalSolarPanel'
                    );

                    panelGradient.querySelectorAll('stop').forEach(
                        (stop, index) => {
                            stop.setAttribute(
                                'id',
                                'symconThermalSolarPanelStop' + (index + 1)
                            );
                        }
                    );

                    originalPanelGradient.parentNode.appendChild(panelGradient);
                }
            }

            if (panelGradient && supplyColor && returnColor) {
                const panelStops = panelGradient.querySelectorAll('stop');

                if (panelStops.length >= 2) {
                    panelStops[0].style.setProperty(
                        'stop-color',
                        supplyColor,
                        'important'
                    );
                    panelStops[0].setAttribute(
                        'stop-color',
                        supplyColor
                    );

                    panelStops[panelStops.length - 1].style.setProperty(
                        'stop-color',
                        returnColor,
                        'important'
                    );
                    panelStops[panelStops.length - 1].setAttribute(
                        'stop-color',
                        returnColor
                    );
                }

                const panel = svg.querySelector('#rectThermalSolarPanel');

                if (panel) {
                    panel.style.setProperty(
                        'fill',
                        'url(#symconLinearGradientThermalSolarPanel)',
                        'important'
                    );
                    panel.style.setProperty(
                        'fill-opacity',
                        '1',
                        'important'
                    );
                }
            }

            /*
             * Eigener Gradient nur für den eigenständigen Solar-Wendel-Pfad.
             * Damit beeinflusst Solar weder die Wärmepumpen- noch die normale
             * Boiler-Wendel.
             */
            let gradient = svg.querySelector('#symconLinearGradientThermalSolar');

            if (!gradient) {
                const sourceGradient = svg.querySelector('#linearGradientPipe1');

                if (sourceGradient) {
                    gradient = sourceGradient.cloneNode(true);
                    gradient.setAttribute(
                        'id',
                        'symconLinearGradientThermalSolar'
                    );

                    gradient.querySelectorAll('stop').forEach((stop, index) => {
                        stop.setAttribute(
                            'id',
                            'symconThermalSolarStop' + (index + 1)
                        );
                    });

                    sourceGradient.parentNode.appendChild(gradient);
                }
            }

            if (gradient && supplyColor && returnColor) {
                const stops = gradient.querySelectorAll('stop');

                if (stops.length >= 2) {
                    stops[0].style.setProperty(
                        'stop-color',
                        supplyColor,
                        'important'
                    );
                    stops[0].setAttribute('stop-color', supplyColor);

                    stops[stops.length - 1].style.setProperty(
                        'stop-color',
                        returnColor,
                        'important'
                    );
                    stops[stops.length - 1].setAttribute(
                        'stop-color',
                        returnColor
                    );
                }

                if (solarCoil) {
                    solarCoil.style.setProperty(
                        'stroke',
                        'url(#symconLinearGradientThermalSolar)',
                        'important'
                    );
                    solarCoil.style.setProperty(
                        'stroke-opacity',
                        '1',
                        'important'
                    );
                }
            }
        };

        const positionHeatingCircuitTemperatures = (card) => {
            if (!card || !card.content) {
                return;
            }

            /*
             * Durch die einheitliche Anzeige inklusive "°C" benötigt der
             * Vorlaufwert am Heizkörper / an der Fußbodenheizung etwas mehr
             * Platz. Deshalb nur die Vorlauftexte leicht nach links schieben.
             */
            [
                '#textSupplyTemperatureHeating',
                '#textSupplyTemperatureHeating2',
                '#textSupplyTemperatureHeating3'
            ].forEach((selector) => {
                const element = card.content.querySelector(selector);
                if (!element) {
                    return;
                }

                if (!element.hasAttribute('data-symcon-original-x')) {
                    element.setAttribute(
                        'data-symcon-original-x',
                        element.getAttribute('x') || ''
                    );
                }

                const originalX = Number(
                    element.getAttribute('data-symcon-original-x')
                );

                if (Number.isFinite(originalX)) {
                    element.setAttribute('x', String(originalX - 10));
                } else {
                    element.setAttribute('transform', 'translate(-10 0)');
                }
            });
        };

        const ensureFlowOverlay = (
            svg,
            selector,
            overlayId,
            direction = 'forward',
            active = true
        ) => {
            const source = svg.querySelector(selector);

            if (!source) {
                return;
            }

            let overlay = svg.querySelector('#' + overlayId);

            if (!overlay) {
                overlay = source.cloneNode(false);
                overlay.setAttribute('id', overlayId);
                overlay.removeAttribute('style');

                if (source.parentNode) {
                    source.parentNode.insertBefore(
                        overlay,
                        source.nextSibling
                    );
                }
            }

            overlay.setAttribute(
                'class',
                'symcon-flow-overlay '
                + (direction === 'reverse'
                    ? 'symcon-flow-reverse'
                    : 'symcon-flow-forward')
            );

            overlay.style.setProperty(
                'display',
                active ? 'inline' : 'none',
                'important'
            );
            overlay.style.setProperty(
                'visibility',
                active ? 'visible' : 'hidden',
                'important'
            );
        };

        const removeFlowOverlays = (svg) => {
            svg.querySelectorAll('.symcon-flow-overlay').forEach(
                (element) => {
                    if (element && element.parentNode) {
                        element.parentNode.removeChild(element);
                    }
                }
            );
        };

        const applyRefrigerantTemperatureColors = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;
            const outer =
                svg.querySelector('#pathHPModelOuterCircle');
            const inner =
                svg.querySelector('#pathHPModelInnerCircle');

            /*
             * Unsere vollständige Kältekreis-Mittellinie.
             *
             * Die beiden Originalpfade sind zwei Konturen derselben
             * schematischen Leitung. Je nachdem, welchen davon man allein
             * verwendet, fehlt jeweils an Verdampfer / Verdichter /
             * Expansionsventil die andere Hälfte.
             *
             * Deshalb verwenden wir im erweiterten Modus KEINEN der beiden
             * Originalpfade als Leitung, sondern eine einzige Mittellinie,
             * die alle vier Bauteile vollständig miteinander verbindet.
             */
            const ensureCircuitPipe = () => {
                let pipe =
                    svg.querySelector('#symconRefrigerantCircuitPipe');

                if (pipe) {
                    return pipe;
                }

                const parent =
                    (outer && outer.parentNode)
                    || (inner && inner.parentNode)
                    || svg;

                pipe = document.createElementNS(
                    'http://www.w3.org/2000/svg',
                    'path'
                );

                pipe.setAttribute(
                    'id',
                    'symconRefrigerantCircuitPipe'
                );

                /*
                 * Eine durchgehende Leitung:
                 * - obere Hälfte um den Verdichter
                 * - äußere linke Hälfte um den Verdampfer
                 * - komplette untere Führung durchs Expansionsventil
                 * - äußere rechte Hälfte um den Kondensator
                 *
                 * Damit wird nicht mehr abwechselnd eine der beiden
                 * Originalkonturen "vergessen".
                 */
                pipe.setAttribute(
                    'd',
                    'M 414 378 '
                    + 'A 25 25 0 0 0 364 378 '
                    + 'C 315 390 280 430 264 462 '
                    + 'A 25 25 0 0 0 264 512 '
                    + 'C 282 560 325 594 369 600 '
                    + 'L 369 620 '
                    + 'L 389 600 '
                    + 'L 409 620 '
                    + 'L 409 600 '
                    + 'C 454 594 497 560 514 512 '
                    + 'A 25 25 0 0 0 514 462 '
                    + 'C 498 430 463 390 414 378 Z'
                );

                pipe.setAttribute('fill', 'none');

                if (outer) {
                    parent.insertBefore(pipe, outer);
                } else {
                    parent.appendChild(pipe);
                }

                return pipe;
            };

            const pipe = ensureCircuitPipe();

            /*
             * Vier kurze Brücken schließen exakt die Innenlücken an den
             * Bauteilen. Die Hauptleitung bleibt unverändert bestehen.
             */
            const bridgeDefinitions = [
                /*
                 * Verdichter: obere UND untere Hälfte um den Verdichter.
                 * Keine Linie mehr quer durch das Bauteil.
                 */
                {
                    id: 'symconRefrigerantBridgeCompressorOuter',
                    d: 'M 364 378 A 25 25 0 0 0 414 378'
                },
                {
                    id: 'symconRefrigerantBridgeCompressorInner',
                    d: 'M 364 378 A 25 25 0 0 1 414 378'
                },

                /*
                 * Verdampfer: linke UND rechte Hälfte.
                 */
                {
                    id: 'symconRefrigerantBridgeEvaporatorOuter',
                    d: 'M 264 462 A 25 25 0 0 0 264 512'
                },
                {
                    id: 'symconRefrigerantBridgeEvaporatorInner',
                    d: 'M 264 462 A 25 25 0 0 1 264 512'
                },

                /*
                 * Kondensator: rechte UND linke Hälfte.
                 */
                {
                    id: 'symconRefrigerantBridgeCondenserOuter',
                    d: 'M 514 462 A 25 25 0 0 1 514 512'
                },
                {
                    id: 'symconRefrigerantBridgeCondenserInner',
                    d: 'M 514 462 A 25 25 0 0 0 514 512'
                },

                /*
                 * Expansionsventil: unterer UND oberer Weg um das Ventil.
                 * Damit entsteht keine gerade Linie durch das Symbol.
                 */
                {
                    id: 'symconRefrigerantBridgeExpansionOuter',
                    d: 'M 369 600 L 369 620 L 389 600 L 409 620 L 409 600'
                },
                {
                    id: 'symconRefrigerantBridgeExpansionInner',
                    d: 'M 369 600 L 389 580 L 409 600'
                }
            ];
            const bridges = bridgeDefinitions.map((definition) => {
                let bridge = svg.querySelector('#' + definition.id);

                if (!bridge) {
                    bridge = document.createElementNS(
                        'http://www.w3.org/2000/svg',
                        'path'
                    );
                    bridge.setAttribute('id', definition.id);
                    bridge.setAttribute('d', definition.d);
                    bridge.setAttribute('fill', 'none');

                    if (pipe.parentNode) {
                        pipe.parentNode.insertBefore(
                            bridge,
                            pipe.nextSibling
                        );
                    }
                }

                return bridge;
            });

            if (!currentConfig.useCustomTemperatureColors) {
                if (outer) {
                    outer.style.removeProperty('display');
                    outer.style.removeProperty('visibility');
                }

                if (inner) {
                    inner.style.removeProperty('display');
                    inner.style.removeProperty('visibility');
                }

                if (pipe) {
                    pipe.style.setProperty(
                        'display',
                        'none',
                        'important'
                    );
                    pipe.style.setProperty(
                        'visibility',
                        'hidden',
                        'important'
                    );
                    pipe.removeAttribute(
                        'data-symcon-refrigerant-pipe'
                    );
                }

                bridges.forEach((bridge) => {
                    bridge.style.setProperty(
                        'display',
                        'none',
                        'important'
                    );
                    bridge.style.setProperty(
                        'visibility',
                        'hidden',
                        'important'
                    );
                    bridge.removeAttribute(
                        'data-symcon-refrigerant-pipe'
                    );
                });

                [
                    '#pathHPModelEvaporatorSymbol001',
                    '#pathHPModelEvaporatorSymbol002',
                    '#pathHPModelCondenserSymbol',
                    '#pathCompressor'
                ].forEach((selector) => {
                    const element = svg.querySelector(selector);

                    if (element) {
                        element.style.removeProperty('stroke');
                        element.style.removeProperty('fill');
                    }
                });

                return;
            }

            /*
             * Die beiden weißen Originalkonturen vollständig ausblenden.
             * Sichtbar bleibt ausschließlich unsere einzelne 5-px-Leitung.
             */
            [outer, inner].forEach((element) => {
                if (!element) {
                    return;
                }

                element.style.setProperty(
                    'display',
                    'none',
                    'important'
                );
                element.style.setProperty(
                    'visibility',
                    'hidden',
                    'important'
                );
                element.removeAttribute(
                    'data-symcon-refrigerant-pipe'
                );
            });

            const evaporatorTemperature =
                readStateNumber(
                    currentConfig.evaporatorTemperature
                );
            const condenserTemperature =
                readStateNumber(
                    currentConfig.condenserTemperature
                );

            const evaporatorColor =
                temperatureColor(evaporatorTemperature);
            const condenserColor =
                temperatureColor(condenserTemperature);

            if (!evaporatorColor || !condenserColor) {
                return;
            }

            const setStroke = (selector, color) => {
                const element = svg.querySelector(selector);

                if (!element) {
                    return;
                }

                element.style.setProperty(
                    'stroke',
                    color,
                    'important'
                );
                element.style.setProperty(
                    'stroke-opacity',
                    '1',
                    'important'
                );
            };

            setStroke(
                '#pathHPModelEvaporatorSymbol001',
                evaporatorColor
            );
            setStroke(
                '#pathHPModelEvaporatorSymbol002',
                evaporatorColor
            );
            setStroke(
                '#pathHPModelCondenserSymbol',
                condenserColor
            );

            let defs = svg.querySelector('defs');

            if (!defs) {
                defs = document.createElementNS(
                    'http://www.w3.org/2000/svg',
                    'defs'
                );
                svg.insertBefore(defs, svg.firstChild);
            }

            const createPaletteGradient = (
                id,
                lowTemperature,
                highTemperature
            ) => {
                let gradient = svg.querySelector('#' + id);

                if (!gradient) {
                    gradient = document.createElementNS(
                        'http://www.w3.org/2000/svg',
                        'linearGradient'
                    );

                    gradient.setAttribute('id', id);
                    gradient.setAttribute(
                        'gradientUnits',
                        'userSpaceOnUse'
                    );
                    gradient.setAttribute('x1', '240');
                    gradient.setAttribute('y1', '0');
                    gradient.setAttribute('x2', '540');
                    gradient.setAttribute('y2', '0');

                    defs.appendChild(gradient);
                }

                while (gradient.firstChild) {
                    gradient.removeChild(gradient.firstChild);
                }

                const low = Number(lowTemperature);
                const high = Number(highTemperature);
                const min = Math.min(low, high);
                const max = Math.max(low, high);
                const span = Math.max(0.001, max - min);

                let configured = getTemperatureColorStops()
                    .filter((stop) =>
                        Number(stop.temperature) > min
                        && Number(stop.temperature) < max
                    );

                if (low > high) {
                    configured = configured.reverse();
                }

                const stops = [
                    {
                        temperature: low,
                        color: temperatureColor(low)
                    },
                    ...configured,
                    {
                        temperature: high,
                        color: temperatureColor(high)
                    }
                ];

                stops.forEach((item) => {
                    const stop =
                        document.createElementNS(
                            'http://www.w3.org/2000/svg',
                            'stop'
                        );

                    const offset =
                        Math.abs(
                            Number(item.temperature) - low
                        ) / span;

                    stop.setAttribute(
                        'offset',
                        Math.max(
                            0,
                            Math.min(1, offset)
                        ) * 100 + '%'
                    );
                    stop.setAttribute(
                        'stop-color',
                        item.color
                    );

                    gradient.appendChild(stop);
                });

                return 'url(#' + id + ')';
            };

            const pipeGradient = createPaletteGradient(
                'symconRefrigerantTemperatureGradient',
                evaporatorTemperature,
                condenserTemperature
            );

            pipe.style.setProperty(
                'display',
                'inline',
                'important'
            );
            pipe.style.setProperty(
                'visibility',
                'visible',
                'important'
            );
            pipe.style.setProperty(
                'fill',
                'none',
                'important'
            );
            pipe.style.setProperty(
                'stroke',
                pipeGradient,
                'important'
            );
            pipe.style.setProperty(
                'stroke-width',
                '5',
                'important'
            );
            pipe.style.setProperty(
                'stroke-opacity',
                '1',
                'important'
            );
            pipe.style.setProperty(
                'stroke-linecap',
                'round',
                'important'
            );
            pipe.style.setProperty(
                'stroke-linejoin',
                'round',
                'important'
            );

            pipe.setAttribute(
                'data-symcon-refrigerant-pipe',
                '1'
            );

            bridges.forEach((bridge) => {
                bridge.style.setProperty(
                    'display',
                    'inline',
                    'important'
                );
                bridge.style.setProperty(
                    'visibility',
                    'visible',
                    'important'
                );
                bridge.style.setProperty(
                    'fill',
                    'none',
                    'important'
                );
                bridge.style.setProperty(
                    'stroke',
                    pipeGradient,
                    'important'
                );
                bridge.style.setProperty(
                    'stroke-width',
                    '5',
                    'important'
                );
                bridge.style.setProperty(
                    'stroke-opacity',
                    '1',
                    'important'
                );
                bridge.style.setProperty(
                    'stroke-linecap',
                    'round',
                    'important'
                );
                bridge.setAttribute(
                    'data-symcon-refrigerant-pipe',
                    '1'
                );
            });

            const compressorGradient =
                createPaletteGradient(
                    'symconCompressorTemperatureGradient',
                    evaporatorTemperature,
                    condenserTemperature
                );

            const compressor =
                svg.querySelector('#pathCompressor');

            if (compressor) {
                compressor.style.setProperty(
                    'stroke',
                    compressorGradient,
                    'important'
                );
                compressor.style.setProperty(
                    'stroke-opacity',
                    '1',
                    'important'
                );
            }
        };

        const applyFlowAnimations = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            /*
             * Flussanimation nur zusammen mit den eigenen Temperaturfarben.
             */
            if (!currentConfig.useCustomTemperatureColors) {
                removeFlowOverlays(svg);
                return;
            }

            const compressorRunning =
                stateIsOn(currentConfig.compressorRunning);

            const circuit1Configured =
                String(currentConfig.heatingCircuitType1 || 'off') !== 'off';
            const circuit2Configured =
                String(currentConfig.heatingCircuitType2 || 'off') !== 'off';
            const circuit3Configured =
                String(currentConfig.heatingCircuitType3 || 'off') !== 'off';

            const heatingPump1 =
                circuit1Configured
                && stateIsOn(currentConfig.heatingCircuitPumpRunning);
            const heatingPump2 =
                circuit2Configured
                && stateIsOn(currentConfig.heatingCircuitPumpRunning2);
            const heatingPump3 =
                circuit3Configured
                && stateIsOn(currentConfig.heatingCircuitPumpRunning3);

            const heatingModeActive =
                stateIsOn('heatingPumpHeatingMode')
                || !!(
                    currentControls
                    && currentControls.heatingActive === true
                );

            /*
             * Ein konfigurierter Heizkreis gilt als aktiv, sobald
             * - der Heizbetrieb aktiv ist ODER
             * - seine Heizkreispumpe aktiv meldet.
             *
             * Eine vorhandene, aber gerade false meldende Pumpenvariable
             * darf den sichtbaren Heizfluss nicht mehr blockieren.
             */
            const circuit1Active =
                circuit1Configured
                && (heatingModeActive || heatingPump1);

            const circuit2Active =
                circuit2Configured
                && (heatingModeActive || heatingPump2);

            const circuit3Active =
                circuit3Configured
                && (heatingModeActive || heatingPump3);

            const anyHeatingActive =
                circuit1Active
                || circuit2Active
                || circuit3Active;

            const storagePump =
                stateIsOn(currentConfig.storageChargingPumpRunning);

            const solarPump =
                stateIsOn(currentConfig.thermalSolarPump);

            /*
             * Für die hydraulische Richtung ist – wie bei unserer
             * Temperatur-/Einfrierlogik – ausschließlich das konfigurierte
             * Umschaltventil maßgebend.
             */
            const valveConfigured = !!currentConfig.wwHeatingValve;
            const valveToBoiler =
                valveConfigured
                && stateIsOn(currentConfig.wwHeatingValve);

            /*
             * ------------------------------------------------------------
             * KÄLTEKREIS
             * ------------------------------------------------------------
             * Nur EINE farbige Leitung und EIN Fluss-Overlay.
             */
            svg.querySelectorAll(
                '[id^="symconFlowRefrigerant"]'
            ).forEach((oldOverlay) => {
                if (
                    oldOverlay.id !==
                    'symconFlowRefrigerantMain'
                    && oldOverlay.parentNode
                ) {
                    oldOverlay.parentNode.removeChild(
                        oldOverlay
                    );
                }
            });

            ensureFlowOverlay(
                svg,
                '#symconRefrigerantCircuitPipe',
                'symconFlowRefrigerantMain',
                'forward',
                compressorRunning
            );

            [
                {name: 'CompressorOuter', direction: 'reverse'},
                {name: 'CompressorInner', direction: 'reverse'},
                {name: 'EvaporatorOuter', direction: 'forward'},
                {name: 'EvaporatorInner', direction: 'forward'},
                {name: 'CondenserOuter', direction: 'forward'},
                {name: 'CondenserInner', direction: 'forward'},
                {name: 'ExpansionOuter', direction: 'forward'},
                {name: 'ExpansionInner', direction: 'forward'}
            ].forEach((definition) => {
                const name = definition.name;

                ensureFlowOverlay(
                    svg,
                    '#symconRefrigerantBridge' + name,
                    'symconFlowRefrigerantBridge' + name,
                    definition.direction,
                    compressorRunning
                );

                const bridgeOverlay =
                    svg.querySelector(
                        '#symconFlowRefrigerantBridge' + name
                    );

                if (bridgeOverlay) {
                    bridgeOverlay.classList.add(
                        'symcon-flow-refrigerant'
                    );
                }
            });

            const refrigerantOverlay =
                svg.querySelector(
                    '#symconFlowRefrigerantMain'
                );

            if (refrigerantOverlay) {
                refrigerantOverlay.classList.add(
                    'symcon-flow-refrigerant'
                );
            }

            /*
             * Die beiden Wärmetauscher-Wendel in der Wärmepumpe explizit
             * mitnehmen. Sie werden absichtlich NICHT über die generische
             * Leitungs-Erkennung animiert.
             */
            ensureFlowOverlay(
                svg,
                '#pathHPModelEvaporatorSymbol001',
                'symconFlowEvaporatorCoil1',
                'forward',
                compressorRunning
            );
            ensureFlowOverlay(
                svg,
                '#pathHPModelEvaporatorSymbol002',
                'symconFlowEvaporatorCoil2',
                'forward',
                compressorRunning
            );
            ensureFlowOverlay(
                svg,
                '#pathHPModelCondenserSymbol',
                'symconFlowCondenserCoil',
                'forward',
                compressorRunning
            );

            /*
             * ------------------------------------------------------------
             * GEMEINSAME HYDRAULIK DIREKT AN DER WÄRMEPUMPE
             * ------------------------------------------------------------
             * Diese Stücke gehören sowohl zum Heizbetrieb als auch zur
             * Boilerladung und dürfen deshalb nicht fehlen.
             */
            const hydraulicActive =
                valveToBoiler || anyHeatingActive || storagePump;

            ensureFlowOverlay(
                svg,
                '#pathPipeHotColdHeatpump',
                'symconFlowHydraulicSupply',
                'forward',
                hydraulicActive
            );

            ensureFlowOverlay(
                svg,
                '#pathPipeRefluxWW',
                'symconFlowHydraulicReturn',
                'reverse',
                hydraulicActive
            );

            /*
             * ------------------------------------------------------------
             * HEIZKREISE
             * ------------------------------------------------------------
             * Bei Boilerstellung werden ALLE Heizkreis-Flüsse hart gestoppt,
             * auch wenn eine Heizkreispumpe noch "aktiv" meldet.
             */
            const heatingAllowed = !valveToBoiler;
            const heatingFlowActive =
                heatingAllowed && anyHeatingActive;

            /*
             * GEMEINSAME HEIZLEITUNG:
             *
             * pathPipeToBuffer ist trotz des historischen Namens die
             * durchgehende Vorlaufleitung vom Umschaltventil bis zum
             * Heizkreisverteiler.
             *
             * pathPipeFromBuffer ist die durchgehende Rücklaufleitung vom
             * Heizkreisverteiler zurück zur Wärmepumpe.
             *
             * Diese beiden Stücke müssen deshalb auch im reinen Heizbetrieb
             * animiert werden – nicht nur bei einer Speicherpumpe.
             */
            ensureFlowOverlay(
                svg,
                '#pathPipeToBuffer',
                'symconFlowHeatingCommonSupply',
                'forward',
                heatingFlowActive
            );

            /*
             * Die SVG-Pfadrichtung von pathPipeFromBuffer geht bereits
             * vom Heizkreis zurück zur Wärmepumpe. Deshalb FORWARD.
             * Das korrigiert die bisher umgekehrte Rücklaufrichtung.
             */
            ensureFlowOverlay(
                svg,
                '#pathPipeFromBuffer',
                'symconFlowHeatingCommonReturn',
                'forward',
                heatingFlowActive
            );

            /*
             * Gemeinsamer vertikaler Rücklauf zwischen Heizkreis 1/2 und
             * dem unteren Sammelpunkt. Bei Heizkreis 1 oder 2 gehört dieses
             * Stück zwingend zur vollständigen Rücklaufkette.
             */
            ensureFlowOverlay(
                svg,
                '#pathPipeToHP2',
                'symconFlowHeatingSharedReturn',
                'forward',
                heatingAllowed
                    && (circuit1Active || circuit2Active)
            );

            const animateEmitter = (
                number,
                enabled
            ) => {
                /*
                 * Fußbodenheizung:
                 * Die Original-Schlange ist bereits ein echter,
                 * durchgehender Wasserweg.
                 */
                const floor =
                    svg.querySelector(
                        '#pathUnderfloorHeating' + number
                    );

                if (floor) {
                    const floorParent = floor.parentElement;
                    const floorVisible =
                        !floorParent
                        || getComputedStyle(floorParent).display !== 'none';

                    ensureFlowOverlay(
                        svg,
                        '#pathUnderfloorHeating' + number,
                        'symconFlowEmitterFloor' + number,
                        'forward',
                        !!enabled && floorVisible
                    );

                    const floorOverlay =
                        svg.querySelector(
                            '#symconFlowEmitterFloor' + number
                        );

                    if (floorOverlay) {
                        floorOverlay.classList.add(
                            'symcon-flow-emitter'
                        );

                        if (floorOverlay.parentNode) {
                            floorOverlay.parentNode.appendChild(
                                floorOverlay
                            );
                        }
                    }
                }

                /*
                 * Heizkörper:
                 *
                 * #rectRadiatorN besteht aus einem Außenrahmen plus vielen
                 * einzelnen geschlossenen Rippen. Eine Dash-Animation darauf
                 * kann deshalb nicht als ein durchgehender Wasserfluss
                 * erscheinen.
                 *
                 * Wir legen im gleichen <g> einen einzigen zusammenhängenden
                 * Serpentinenpfad über alle Rippen. Bei Heizkreis 2 und 3
                 * übernimmt er automatisch den transform des Eltern-<g>.
                 */
                const radiator =
                    svg.querySelector(
                        '#rectRadiator' + number
                    );

                if (!radiator || !radiator.parentNode) {
                    return;
                }

                const radiatorParent = radiator.parentElement;
                const radiatorVisible =
                    getComputedStyle(radiatorParent).display !== 'none';

                let flowPath =
                    svg.querySelector(
                        '#symconRadiatorWaterPath' + number
                    );

                if (!flowPath) {
                    flowPath = document.createElementNS(
                        'http://www.w3.org/2000/svg',
                        'path'
                    );

                    flowPath.setAttribute(
                        'id',
                        'symconRadiatorWaterPath' + number
                    );

                    /*
                     * Eintritt links oben -> durch alle Rippen ->
                     * Austritt rechts unten.
                     */
                    flowPath.setAttribute(
                        'd',
                        'M 837 82 '
                        + 'L 847 82 L 847 118 '
                        + 'L 857 118 L 857 82 '
                        + 'L 867 82 L 867 118 '
                        + 'L 877 118 L 877 82 '
                        + 'L 887 82 L 887 118 '
                        + 'L 897 118 L 897 82 '
                        + 'L 907 82 L 907 118 '
                        + 'L 917 118 L 917 82 '
                        + 'L 927 82 L 927 115 '
                        + 'L 937 115'
                    );

                    flowPath.setAttribute(
                        'fill',
                        'none'
                    );

                    /*
                     * Der Basis-Pfad selbst bleibt unsichtbar.
                     * Sichtbar ist nur sein animiertes Overlay.
                     */
                    flowPath.style.setProperty(
                        'stroke',
                        'transparent',
                        'important'
                    );
                    flowPath.style.setProperty(
                        'fill',
                        'none',
                        'important'
                    );

                    radiatorParent.appendChild(flowPath);
                }

                /*
                 * Pfad ganz nach vorne holen, damit er nicht durch
                 * Heizkörperfüllung oder andere SVG-Elemente verdeckt wird.
                 */
                if (flowPath.parentNode) {
                    flowPath.parentNode.appendChild(flowPath);
                }

                ensureFlowOverlay(
                    svg,
                    '#symconRadiatorWaterPath' + number,
                    'symconFlowEmitterRadiator' + number,
                    'forward',
                    !!enabled && radiatorVisible
                );

                const radiatorOverlay =
                    svg.querySelector(
                        '#symconFlowEmitterRadiator' + number
                    );

                if (radiatorOverlay) {
                    radiatorOverlay.classList.add(
                        'symcon-flow-emitter'
                    );

                    /*
                     * Nicht auf geerbte SVG-/CSS-Werte verlassen:
                     * der Heizkörper-Fluss bekommt seine sichtbaren
                     * Eigenschaften direkt auf dem Overlay.
                     */
                    radiatorOverlay.style.setProperty(
                        'fill',
                        'none',
                        'important'
                    );
                    radiatorOverlay.style.setProperty(
                        'stroke',
                        'rgba(255,255,255,.82)',
                        'important'
                    );
                    radiatorOverlay.style.setProperty(
                        'stroke-width',
                        '2',
                        'important'
                    );
                    radiatorOverlay.style.setProperty(
                        'stroke-dasharray',
                        '4 7',
                        'important'
                    );
                    radiatorOverlay.style.setProperty(
                        'stroke-linecap',
                        'round',
                        'important'
                    );
                    radiatorOverlay.style.setProperty(
                        'opacity',
                        '1',
                        'important'
                    );

                    if (radiatorOverlay.parentNode) {
                        radiatorOverlay.parentNode.appendChild(
                            radiatorOverlay
                        );
                    }
                }
            };

            const animateCircuit = (
                number,
                active,
                supplySelectors,
                returnSelectors
            ) => {
                const enabled = !!active && heatingAllowed;

                supplySelectors.forEach((selector, index) => {
                    const overlayId =
                        'symconFlowHeatingSupply'
                        + number + '_' + index;

                    ensureFlowOverlay(
                        svg,
                        selector,
                        overlayId,
                        'forward',
                        enabled
                    );

                    if (
                        selector.includes('Radiator')
                        || selector.includes('Underfloor')
                    ) {
                        const overlay =
                            svg.querySelector('#' + overlayId);

                        if (overlay) {
                            overlay.classList.add(
                                'symcon-flow-emitter'
                            );
                        }
                    }
                });

                returnSelectors.forEach((selector, index) => {
                    const overlayId =
                        'symconFlowHeatingReturn'
                        + number + '_' + index;

                    ensureFlowOverlay(
                        svg,
                        selector,
                        overlayId,
                        'forward',
                        enabled
                    );

                    if (selector.includes('Radiator')) {
                        const overlay =
                            svg.querySelector('#' + overlayId);

                        if (overlay) {
                            overlay.classList.add(
                                'symcon-flow-emitter'
                            );
                        }
                    }
                });
            };

            animateCircuit(
                1,
                circuit1Active,
                [
                    '#pathPipeToHeatingCircuitPump',
                    '#pathRadiatorPipeIn1'
                ],
                [
                    '#pathPipeToHP',
                    '#pathRadiatorPipeOut1'
                ]
            );
            animateEmitter(
                1,
                circuit1Active && heatingAllowed
            );

            animateCircuit(
                2,
                circuit2Active,
                [
                    '#pathPipeToHeatingCircuitPump2',
                    '#pathRadiatorPipeIn2'
                ],
                [
                    '#pathRadiatorPipeOut2'
                ]
            );
            animateEmitter(
                2,
                circuit2Active && heatingAllowed
            );

            animateCircuit(
                3,
                circuit3Active,
                [
                    '#pathPipeToHeatingCircuitPump3',
                    '#pathRadiatorPipeIn3'
                ],
                [
                    '#pathRadiatorPipeOut3'
                ]
            );
            animateEmitter(
                3,
                circuit3Active && heatingAllowed
            );

            /*
             * ------------------------------------------------------------
             * BOILER / WARMWASSER
             * ------------------------------------------------------------
             * Bei Boilerstellung läuft der Fluss ausschließlich über diesen
             * Zweig. Der Boiler-Wendel selbst wird ebenfalls animiert.
             */
            /*
             * Boiler-Wendel:
             * Die SVG-Pfadrichtung läuft entgegengesetzt zur gewünschten
             * hydraulischen Flussrichtung, deshalb hier bewusst "reverse".
             */
            ensureFlowOverlay(
                svg,
                '#pathPipeHotWaterToTank',
                'symconFlowBoilerCoil',
                'reverse',
                valveToBoiler
            );

            /*
             * Keine Flussanimation auf der Warmwasserleitung vom Boiler
             * zum Wasserhahn / zur Zapfstelle.
             * Ein eventuell noch vorhandenes Overlay aus einer früheren
             * Aktualisierung wird explizit ausgeblendet.
             */
            const faucetFlow = svg.querySelector('#symconFlowBoilerReturn');
            if (faucetFlow) {
                faucetFlow.style.setProperty(
                    'display',
                    'none',
                    'important'
                );
                faucetFlow.style.setProperty(
                    'visibility',
                    'hidden',
                    'important'
                );
            }

            /*
             * ------------------------------------------------------------
             * PUFFERSPEICHER
             * ------------------------------------------------------------
             */
            ensureFlowOverlay(
                svg,
                '#pathPipeToBuffer',
                'symconFlowBufferSupply',
                'forward',
                storagePump && !valveToBoiler && !anyHeatingActive
            );

            ensureFlowOverlay(
                svg,
                '#pathPipeFromBuffer',
                'symconFlowBufferReturn',
                'forward',
                storagePump && !valveToBoiler && !anyHeatingActive
            );

            ensureFlowOverlay(
                svg,
                '#pathPipeBufferToHeating',
                'symconFlowBufferToHeating',
                'forward',
                storagePump && anyHeatingActive && !valveToBoiler
            );

            ensureFlowOverlay(
                svg,
                '#pathPipeHeatingToBuffer',
                'symconFlowHeatingToBuffer',
                'reverse',
                storagePump && anyHeatingActive && !valveToBoiler
            );

            /*
             * ------------------------------------------------------------
             * SOLARTHERMIE
             * ------------------------------------------------------------
             */
            ensureFlowOverlay(
                svg,
                '#pathPipeThermalSolarHotWater',
                'symconFlowSolarSupply',
                'forward',
                solarPump
            );

            ensureFlowOverlay(
                svg,
                '#pathPipeThermalSolarColdWater',
                'symconFlowSolarReturn',
                'reverse',
                solarPump
            );

            ensureFlowOverlay(
                svg,
                '#symconThermalSolarTankCoil',
                'symconFlowSolarCoil',
                'forward',
                solarPump
            );

            ensureFlowOverlay(
                svg,
                '#symconThermalSolarHotConnector',
                'symconFlowSolarConnector',
                'forward',
                solarPump
            );
        };

        const applySingleCircuitTemperatureDisplay = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            /*
             * Diese Sonderfunktion kann in Heizkreis 1 abgeschaltet werden.
             * Aus = normale Card-Darstellung ohne Einfrieren und ohne die
             * zusätzlichen Vor-/Rücklaufwerte am Warmwasserspeicher.
             */
            if (!currentConfig.singleCircuitHotWaterTemperatureSwitch) {
                [
                    '#symconBoilerSupplyTemperature',
                    '#symconBoilerRefluxTemperature'
                ].forEach((selector) => {
                    const element = svg.querySelector(selector);
                    if (element && element.parentNode) {
                        element.parentNode.removeChild(element);
                    }
                });
                return;
            }

            /*
             * Diese Erweiterung gilt bewusst nur bei genau EINEM Heizkreis.
             */
            const circuits = [
                {
                    key: '1',
                    type: String(currentConfig.heatingCircuitType1 || 'off'),
                    supply: currentConfig.supplyTemperatureHeating,
                    reflux: currentConfig.refluxTemperatureHeating,
                    supplyText: '#textSupplyTemperatureHeating',
                    refluxText: '#textRefluxTemperatureHeating'
                },
                {
                    key: '2',
                    type: String(currentConfig.heatingCircuitType2 || 'off'),
                    supply: currentConfig.supplyTemperatureHeating2,
                    reflux: currentConfig.refluxTemperatureHeating2,
                    supplyText: '#textSupplyTemperatureHeating2',
                    refluxText: '#textRefluxTemperatureHeating2'
                },
                {
                    key: '3',
                    type: String(currentConfig.heatingCircuitType3 || 'off'),
                    supply: currentConfig.supplyTemperatureHeating3,
                    reflux: currentConfig.refluxTemperatureHeating3,
                    supplyText: '#textSupplyTemperatureHeating3',
                    refluxText: '#textRefluxTemperatureHeating3'
                }
            ];

            const activeCircuits = circuits.filter(
                (circuit) => circuit.type !== 'off'
            );

            const tankGroup = svg.querySelector('#gTankWW');

            /*
             * Zusatztexte am Boiler entfernen, wenn mehr oder weniger als
             * ein Heizkreis aktiv ist.
             */
            const removeBoilerTexts = () => {
                [
                    '#symconBoilerSupplyTemperature',
                    '#symconBoilerRefluxTemperature'
                ].forEach((selector) => {
                    const element = svg.querySelector(selector);
                    if (element && element.parentNode) {
                        element.parentNode.removeChild(element);
                    }
                });
            };

            if (activeCircuits.length !== 1 || !tankGroup) {
                removeBoilerTexts();
                return;
            }

            const circuit = activeCircuits[0];

            /*
             * Allein die Stellung des konfigurierten Umschaltventils ist
             * maßgebend:
             *   aktiv   = Boiler
             *   inaktiv = Heizung
             *
             * Ohne Ventil gibt es keine Einfrier-/Umschaltlogik.
             */
            const valveConfigured = !!currentConfig.wwHeatingValve;
            const boilerActive =
                valveConfigured && stateIsOn(currentConfig.wwHeatingValve);

            const supplyTemperature = readStateNumber(circuit.supply);
            const refluxTemperature = readStateNumber(circuit.reflux);

            const formatTemperature = (value) => {
                if (value === null || !Number.isFinite(Number(value))) {
                    return '';
                }

                return new Intl.NumberFormat('de-CH', {
                    minimumFractionDigits: 1,
                    maximumFractionDigits: 1
                }).format(Number(value)) + ' °C';
            };

            /*
             * Im Heizbetrieb die letzten echten Heizkreiswerte speichern.
             * Beim Boilerbetrieb werden diese Werte nicht mehr überschrieben.
             */
            if (
                !boilerActive
                && supplyTemperature !== null
                && refluxTemperature !== null
            ) {
                storedHeatingTemperatures[circuit.key] = {
                    supply: supplyTemperature,
                    reflux: refluxTemperature
                };

                saveStoredHeatingTemperatures(storedHeatingTemperatures);
            }

            /*
             * Beim Umschalten auf Boiler bleiben die Temperaturtexte am
             * Heizkörper / an der Fußbodenheizung auf den letzten Heizwerten.
             */
            if (boilerActive) {
                const stored = storedHeatingTemperatures[circuit.key];

                if (stored) {
                    const supplyText = svg.querySelector(circuit.supplyText);
                    const refluxText = svg.querySelector(circuit.refluxText);

                    if (supplyText) {
                        supplyText.textContent =
                            formatTemperature(stored.supply);
                    }

                    if (refluxText) {
                        refluxText.textContent =
                            formatTemperature(stored.reflux);
                    }
                }
            }

            const boilerTemperature =
                readStateNumber(currentConfig.tankTempWWUp)
                ?? readStateNumber(currentConfig.tankTempWWMiddle)
                ?? readStateNumber(currentConfig.tankTempWWDown);

            /*
             * Zwei zusätzliche Temperaturwerte neben der Boiler-Wendel.
             */
            const ensureText = (id, y) => {
                let element = svg.querySelector('#' + id);

                if (!element) {
                    element = document.createElementNS(
                        'http://www.w3.org/2000/svg',
                        'text'
                    );
                    element.setAttribute('id', id);
                    element.setAttribute('x', '590');
                    element.setAttribute('y', String(y));
                    element.setAttribute('text-anchor', 'end');
                    element.setAttribute('xml:space', 'preserve');
                    element.style.setProperty('font-size', '14px', 'important');
                    element.style.setProperty(
                        'fill',
                        'var(--primary-text-color)',
                        'important'
                    );

                    tankGroup.appendChild(element);
                }

                return element;
            };

            const boilerSupplyText = ensureText(
                'symconBoilerSupplyTemperature',
                438
            );
            const boilerRefluxText = ensureText(
                'symconBoilerRefluxTemperature',
                548
            );

            if (boilerActive) {
                /*
                 * Boiler wird geladen:
                 * Die beiden Zusatzwerte zeigen ausschließlich während dieser
                 * Phase die aktuell anliegenden Vorlauf-/Rücklauftemperaturen.
                 *
                 * Gleichzeitig bleiben die Temperaturwerte am einzigen Heizkreis
                 * auf den zuletzt im Heizbetrieb gespeicherten Werten eingefroren.
                 * Maßgebend ist ausschließlich die Stellung des
                 * Umschaltventils Warmwasser/Heizung.
                 */
                boilerSupplyText.textContent =
                    formatTemperature(supplyTemperature);
                boilerRefluxText.textContent =
                    formatTemperature(refluxTemperature);

                boilerSupplyText.style.setProperty(
                    'display',
                    'inline',
                    'important'
                );
                boilerRefluxText.style.setProperty(
                    'display',
                    'inline',
                    'important'
                );
            } else {
                /*
                 * Keine Boilerladung:
                 * Die zusätzlichen Vorlauf-/Rücklaufwerte am Boiler vollständig
                 * ausblenden. Die normale Boilertemperatur im Speicher bleibt
                 * selbstverständlich erhalten.
                 */
                boilerSupplyText.textContent = '';
                boilerRefluxText.textContent = '';

                boilerSupplyText.style.setProperty(
                    'display',
                    'none',
                    'important'
                );
                boilerRefluxText.style.setProperty(
                    'display',
                    'none',
                    'important'
                );
            }

            /*
             * Ohne konfiguriertes Ventil gibt es keine eindeutige Boilerphase.
             * Deshalb werden die zusätzlichen Werte ebenfalls nicht angezeigt.
             */
            if (!valveConfigured) {
                removeBoilerTexts();
            }
        };

        const applyTerminology = (card) => {
            if (!card || !card.content) {
                return;
            }

            const condenserText = card.content.querySelector('#textCondenser');
            if (condenserText) {
                condenserText.textContent = 'Kondensator';
            }
        };

        const applyOptionalStatusVisibility = (card) => {
            if (!card || !card.content) {
                return;
            }

            const setConfiguredVisibility = (selector, configured) => {
                const element = card.content.querySelector(selector);
                if (!element) {
                    return;
                }

                if (!configured) {
                    element.style.setProperty('display', 'none', 'important');
                    element.style.setProperty('visibility', 'hidden', 'important');
                } else {
                    element.style.removeProperty('visibility');
                    element.style.removeProperty('display');
                }
            };

            // Wärmepumpe Ein/Aus:
            // Ohne Datenpunkt kein "Aus"-Symbol anzeigen.
            setConfiguredVisibility(
                '#gHPStatusOff',
                !!currentConfig.heatingPumpStatusOnOff
            );

            // Tag/Nacht:
            // Die Original-Card zeigt ohne Variable automatisch die Sonne.
            // In Symcon werden Sonne UND Mond nur angezeigt, wenn eine
            // Nachtbetriebsvariable tatsächlich konfiguriert ist.
            const nightModeConfigured = !!currentConfig.heatingPumpNightMode;
            if (!nightModeConfigured) {
                setConfiguredVisibility('#gTimeSymbolDay', false);
                setConfiguredVisibility('#gTimeSymbolNight', false);
            }

            // Warmwasser-/Heizungs-Umschaltventil:
            // Die Original-Card zeichnet es in Grundstellung auch ohne Datenpunkt.
            // Ohne Zuordnung soll das Ventilsymbol vollständig verschwinden.
            if (!currentConfig.wwHeatingValve) {
                setConfiguredVisibility('#gWWHeatingValve', false);
            }
        };


        const applyThreeHeaterRods = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            /*
             * Aktive elektrische Heizstäbe pulsieren dezent zwischen
             * Orange und Rot. Nur die Darstellung wird animiert; die
             * vorhandene Aktivierungs-/Schwellwertlogik bleibt unverändert.
             */
            let heaterRodStyle =
                svg.querySelector('#symconHeaterRodPulseStyle');

            if (!heaterRodStyle) {
                heaterRodStyle = document.createElementNS(
                    'http://www.w3.org/2000/svg',
                    'style'
                );
                heaterRodStyle.setAttribute(
                    'id',
                    'symconHeaterRodPulseStyle'
                );
                heaterRodStyle.textContent = `
                    @keyframes symconHeaterRodPulse {
                        0%, 100% {
                            stroke: #ff9800;
                            filter:
                                drop-shadow(0 0 1.5px rgba(0,0,0,.95))
                                drop-shadow(0 0 1px rgba(255,152,0,.35));
                        }
                        50% {
                            stroke: #f04444;
                            filter:
                                drop-shadow(0 0 1.5px rgba(0,0,0,.95))
                                drop-shadow(0 0 3px rgba(240,68,68,.55));
                        }
                    }

                    .symcon-heater-rod-active {
                        animation:
                            symconHeaterRodPulse 1.8s ease-in-out infinite;
                    }
                `;
                svg.insertBefore(
                    heaterRodStyle,
                    svg.firstChild
                );
            }
            const tankGroup = svg.querySelector('#gTankWW');
            const original = svg.querySelector('#pathHeaterRodWW');

            if (!tankGroup || !original) {
                return;
            }

            const heaterRodCount = Math.max(
                0,
                Math.min(3, Number(currentConfig.heaterRodCount || 0))
            );

            /*
             * Der einzelne Original-WW-Heizstab wird grundsätzlich unterdrückt.
             * Sonst würde er bei Anzahl 0 durch heaterRodWW wieder erscheinen.
             */
            original.style.setProperty('display', 'none', 'important');
            original.style.setProperty('visibility', 'hidden', 'important');

            const ensureRod = (id, y) => {
                let rod = svg.querySelector('#' + id);

                if (!rod) {
                    rod = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    rod.setAttribute('id', id);
                    rod.setAttribute('fill', 'none');
                    tankGroup.appendChild(rod);
                }

                /*
                 * Position bei jeder Aktualisierung neu setzen. So rutschen die
                 * Heizstäbe beim Ändern der Anzahl sofort von unten nach oben.
                 */
                /*
                 * Heizstäbe an der LINKEN Boilerwand:
                 * Eintritt/Austritt liegen bei x=655 und die Schleife ragt
                 * nach rechts in den Speicher. Dadurch bleibt die rechte Seite
                 * für Solar-/Boiler-Wendel und Temperaturtexte frei.
                 */
                rod.setAttribute(
                    'd',
                    'M 655 ' + y + ' H 695 C 701 ' + y + ' 701 ' + (y - 10)
                    + ' 695 ' + (y - 10) + ' H 655'
                );

                rod.style.setProperty('fill', 'none', 'important');
                rod.style.setProperty('stroke-width', '5', 'important');
                rod.style.setProperty('stroke-linecap', 'round', 'important');
                rod.style.setProperty('stroke-linejoin', 'round', 'important');

                return rod;
            };

            /*
             * Die drei Positionen liegen gut verteilt im WW-Speicher.
             * Inaktiv bleibt der vorhandene Stab sichtbar, aber dezent.
             * Aktiv erhält jede Stufe eine klar erkennbare warme Farbe.
             */
            /*
             * Die vorhandenen Heizstäbe werden immer von unten nach oben angeordnet:
             * 1 Heizstab  -> 625 (ganz unten)
             * 2 Heizstäbe -> 595 / 625
             * 3 Heizstäbe -> 565 / 595 / 625
             */
            const rodPositions = {
                1: [610],
                2: [580, 610],
                3: [550, 580, 610]
            };

            const configuredPositions = rodPositions[heaterRodCount] || [];
            const rodEntities = [
                currentConfig.heaterRod1,
                currentConfig.heaterRod2,
                currentConfig.heaterRod3
            ];
            const rodThresholds = [
                currentConfig.heaterRod1Threshold,
                currentConfig.heaterRod2Threshold,
                currentConfig.heaterRod3Threshold
            ];
            const activeRodColor =
                temperatureColor(60) || '#f04444';
            const rodColors = [activeRodColor, activeRodColor, activeRodColor];

            const rods = [0, 1, 2].map((index) => ({
                element: ensureRod(
                    'pathHeaterRodWW' + (index + 1),
                    configuredPositions[index] || 610
                ),
                entity: rodEntities[index],
                threshold: rodThresholds[index],
                activeColor: rodColors[index]
            }));

            const isActive = (entity, threshold) => {
                if (!entity || !currentData || !currentData[entity]) {
                    return false;
                }

                const raw = currentData[entity].value;
                const value = String(raw ?? '').trim().toLowerCase();
                const limit = Number.isFinite(Number(threshold)) ? Number(threshold) : 1;

                // Bool: true entspricht 1, false entspricht 0.
                if (['true', 'on', 'yes', 'ja', 'ein', 'active', 'aktiv'].includes(value)) {
                    return 1 >= limit;
                }

                if (['false', 'off', 'no', 'nein', 'aus', 'inactive', 'inaktiv', ''].includes(value)) {
                    return 0 >= limit;
                }

                const numeric = Number(raw);
                return Number.isFinite(numeric) && numeric >= limit;
            };

            rods.forEach((rodInfo, index) => {
                const exists = index < heaterRodCount;

                if (!exists) {
                    rodInfo.element.classList.remove(
                        'symcon-heater-rod-active'
                    );
                    rodInfo.element.style.setProperty('display', 'none', 'important');
                    rodInfo.element.style.setProperty('visibility', 'hidden', 'important');
                    return;
                }

                const active = isActive(rodInfo.entity, rodInfo.threshold);

                rodInfo.element.style.setProperty('display', 'block', 'important');
                rodInfo.element.style.setProperty('visibility', 'visible', 'important');
                if (active) {
                    /*
                     * Keine Inline-Stroke-Farbe mit !important setzen:
                     * die Farbe kommt bei aktivem Stab aus der Pulsanimation.
                     */
                    rodInfo.element.style.removeProperty('stroke');
                    rodInfo.element.style.removeProperty('filter');
                    rodInfo.element.classList.add(
                        'symcon-heater-rod-active'
                    );
                } else {
                    rodInfo.element.classList.remove(
                        'symcon-heater-rod-active'
                    );
                    rodInfo.element.style.setProperty(
                        'stroke',
                        'rgba(255,255,255,0.55)',
                        'important'
                    );
                    rodInfo.element.style.setProperty(
                        'filter',
                        'drop-shadow(0 0 1.5px rgba(0,0,0,0.95))',
                        'important'
                    );
                }
            });
        };

        /*
         * Die obere Statusleiste der Original-SVG besitzt neun fest
         * vorgesehene Icon-Slots.
         *
         * Gemessen an der unveränderten Original-SVG (0.9.0):
         *   1: 102.85
         *   2: 174.49
         *   3: 242.98
         *   4: 301.14
         *   5: 353.41
         *   6: 412.06
         *   7: 469.65
         *   8: 527.56
         *   9: 587.85
         *
         * Die tatsächliche Breite der Icon-Leiste wird zur Laufzeit direkt
         * aus den Originalpositionen der Card ermittelt. Alle sichtbaren Icons
         * werden innerhalb genau dieses Bereichs gleichmäßig verteilt.
         * Tag/Nacht gehört NICHT zu dieser Reihe und wird daher bewusst
         * nicht verschoben.
         */
        const TOP_ICON_SLOTS = [
            102.85,
            174.49,
            242.98,
            301.14,
            353.41,
            412.06,
            469.65,
            527.56,
            587.85
        ];


        const applySetpointIcons = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;
            const settings = svg.querySelector('#gSettings');

            if (!settings) {
                return;
            }

            const createIcon = (
                id,
                shortLabel,
                functionName
            ) => {
                let group = svg.querySelector('#' + id);

                if (!group) {
                    group = document.createElementNS(
                        'http://www.w3.org/2000/svg',
                        'g'
                    );
                    group.setAttribute('id', id);

                    const circle = document.createElementNS(
                        'http://www.w3.org/2000/svg',
                        'circle'
                    );
                    circle.setAttribute('cx', '0');
                    circle.setAttribute('cy', '0');
                    circle.setAttribute('r', '17');
                    circle.setAttribute('fill', 'none');
                    circle.setAttribute('stroke-width', '2.6');

                    /*
                     * Zusatz-Sollwerticon in derselben Größe wie die
                     * Original-Icons. Im Kreis steht ausschließlich der
                     * Zahlenwert – ohne WW, ±, Einheit oder Gradzeichen.
                     */
                    const value = document.createElementNS(
                        'http://www.w3.org/2000/svg',
                        'text'
                    );
                    value.setAttribute('id', id + 'Value');
                    value.setAttribute('x', '0');
                    value.setAttribute('y', '0');
                    value.setAttribute('text-anchor', 'middle');
                    value.setAttribute('dominant-baseline', 'central');
                    value.setAttribute('alignment-baseline', 'central');
                    value.setAttribute('font-size', '11');
                    value.setAttribute('font-weight', '700');

                    group.appendChild(circle);
                    group.appendChild(value);
                    settings.appendChild(group);

                    group.style.setProperty(
                        'cursor',
                        'pointer',
                        'important'
                    );
                    group.style.setProperty(
                        'pointer-events',
                        'all',
                        'important'
                    );

                    bindTap(
                        group,
                        (event) =>
                            openNumericMenu(
                                functionName,
                                event
                            )
                    );
                }

                return group;
            };

            const definitions = [
                {
                    id: 'gSymconWarmWaterSetpoint',
                    label: 'WW',
                    functionName: 'warmWaterSetpoint'
                },
                {
                    id: 'gSymconHeatingCorrection',
                    label: '±',
                    functionName: 'heatingTemperatureCorrection'
                }
            ];

            definitions.forEach((definition) => {
                const control =
                    currentControls
                    && currentControls[definition.functionName];

                const group = createIcon(
                    definition.id,
                    definition.label,
                    definition.functionName
                );

                const visible = !!(
                    control
                    && control.configured
                );

                group.style.setProperty(
                    'display',
                    visible ? 'inline' : 'none',
                    'important'
                );

                if (!visible) {
                    return;
                }

                const color = resolveLayoutTextColor();
                setIconColor(group, color);

                const valueElement = svg.querySelector(
                    '#' + definition.id + 'Value'
                );

                if (valueElement) {
                    const numeric = Number(control.currentValue);

                    let formatted = Number.isFinite(numeric)
                        ? new Intl.NumberFormat('de-CH', {
                            minimumFractionDigits:
                                Math.abs(numeric % 1) > 0 ? 1 : 0,
                            maximumFractionDigits: 1
                        }).format(numeric)
                        : String(control.currentValue ?? '');

                    if (
                        definition.functionName
                        === 'heatingTemperatureCorrection'
                        && Number.isFinite(numeric)
                        && numeric > 0
                    ) {
                        formatted = '+' + formatted;
                    }

                    /*
                     * Im Kreis ausschließlich den Zahlenwert anzeigen.
                     * Keine Einheit und kein Gradzeichen.
                     */
                    valueElement.textContent = formatted;
                }
            });
        };

        const layoutTopIconBar = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;
            const settings = svg.querySelector('#gSettings');
            const frame = svg.querySelector('#rectSettings');

            if (!settings || !frame) {
                return;
            }

            /*
             * EINHEITLICHES LAYOUT FÜR DESKTOP UND HANDY
             * ------------------------------------------------------------
             * Keine getBBox-/getCTM-Messung mehr.
             * Keine Mobile-Sonderbehandlung mehr.
             * Keine Abhängigkeit von Fenster- oder Bildschirmbreite.
             *
             * Sämtliche Icons werden ausschließlich in den festen
             * Koordinaten der Original-SVG angeordnet. Die SVG skaliert
             * danach selbst auf Desktop oder Handy.
             */

            const definitions = [
                {
                    selector: '#gHPStatusOff',
                    custom: false,
                    originalX: 101.371
                },
                {
                    selector: '#gHPStatusWW',
                    custom: false,
                    originalX: 158.047
                },
                {
                    selector: '#gHPStatusHeating',
                    custom: false,
                    originalX: 212.235
                },
                {
                    selector: '#gHPStatusCooling',
                    custom: false,
                    originalX: 258.245
                },
                {
                    selector: '#gDefrost',
                    custom: false,
                    originalX: 299.602
                },
                {
                    selector: '#gAdditionalHeating',
                    custom: false,
                    originalX: 346.000
                },
                {
                    selector: '#gWarning',
                    custom: false,
                    originalX: 391.560
                },
                {
                    selector: '#gHPStatusParty',
                    custom: false,
                    originalX: 437.380
                },
                {
                    selector: '#gHPStatusSave',
                    custom: false,
                    originalX: 485.077
                },
                {
                    selector: '#gSymconWarmWaterSetpoint',
                    custom: true
                },
                {
                    selector: '#gSymconHeatingCorrection',
                    custom: true
                }
            ];

            const isVisible = (element) => {
                if (!element) {
                    return false;
                }

                const style = getComputedStyle(element);

                return (
                    style.display !== 'none'
                    && style.visibility !== 'hidden'
                    && Number(style.opacity || 1) > 0
                );
            };

            /*
             * Originaltransformationen genau einmal sichern.
             */
            definitions.forEach((definition) => {
                if (definition.custom) {
                    return;
                }

                const element =
                    svg.querySelector(definition.selector);

                if (
                    element
                    && !element.hasAttribute(
                        'data-symcon-original-transform'
                    )
                ) {
                    element.setAttribute(
                        'data-symcon-original-transform',
                        element.getAttribute('transform') || ''
                    );
                }
            });

            /*
             * Alle aktuell sichtbaren Icons in der gewünschten Reihenfolge.
             */
            const visible = definitions.filter((definition) => {
                return isVisible(
                    svg.querySelector(definition.selector)
                );
            });

            /*
             * Die Original-Card besitzt neun Plätze.
             * Originalsymbole haben Vorrang. Zusätzliche Sollwertsymbole
             * werden nur verwendet, solange noch Platz vorhanden ist.
             */
            const visibleOriginals =
                visible.filter(
                    (definition) => !definition.custom
                );

            const visibleCustoms =
                visible.filter(
                    (definition) => definition.custom
                );

            const freeCustomSlots = Math.max(
                0,
                TOP_ICON_SLOTS.length
                - visibleOriginals.length
            );

            visibleCustoms.forEach((definition, index) => {
                const element =
                    svg.querySelector(definition.selector);

                if (!element) {
                    return;
                }

                if (index >= freeCustomSlots) {
                    element.style.setProperty(
                        'display',
                        'none',
                        'important'
                    );
                }
            });

            const shownCustoms =
                visibleCustoms.slice(0, freeCustomSlots);

            const shown = [
                ...visibleOriginals,
                ...shownCustoms
            ];

            if (shown.length === 0) {
                return;
            }

            /*
             * Tatsächlicher Rahmen der Statusleiste.
             */
            const frameX =
                Number(frame.getAttribute('x')) || 65.353;
            const frameWidth =
                Number(frame.getAttribute('width')) || 474.29;

            /*
             * Gleicher Innenabstand links und rechts.
             * Damit kleben weder Wasserhahn noch letztes Symbol am Rahmen.
             */
            const horizontalPadding = 36;
            const leftX =
                frameX + horizontalPadding;
            const rightX =
                frameX + frameWidth - horizontalPadding;

            /*
             * Vertikale Mitte der ORIGINALEN Statuszeile.
             *
             * rectSettings beginnt bei y=50.
             * Die Trennlinie liegt bei ungefähr y=100.
             * Somit ist y=75 die gemeinsame Mitte aller Statusicons.
             */
            /*
             * Reale gemeinsame Y-Mitte der Original-Statussymbole,
             * aus der unveränderten Original-SVG ermittelt.
             *
             * Die Originalicons liegen im Mittel bei y ~= 84.
             * Unsere beiden Sollwerticons werden deshalb exakt dort
             * zentriert und nicht mehr auf der geometrisch nur vermuteten
             * Rahmenmitte y=75.
             */
            const iconCenterY = 84;

            const gap =
                shown.length > 1
                    ? (rightX - leftX) / (shown.length - 1)
                    : 0;

            shown.forEach((definition, index) => {
                const element =
                    svg.querySelector(definition.selector);

                if (!element) {
                    return;
                }

                const targetX =
                    shown.length > 1
                        ? leftX + gap * index
                        : (leftX + rightX) / 2;

                if (definition.custom) {
                    /*
                     * Unsere Sollwerticons sind um (0/0) aufgebaut.
                     * Deshalb direkt auf die gemeinsame Mitte setzen.
                     */
                    element.setAttribute(
                        'transform',
                        'translate('
                        + targetX
                        + ' '
                        + iconCenterY
                        + ')'
                    );
                    return;
                }

                /*
                 * Originalicons horizontal verschieben, ihre originale
                 * vertikale Geometrie aber vollständig beibehalten.
                 */
                const original =
                    element.getAttribute(
                        'data-symcon-original-transform'
                    )
                    || '';

                /*
                 * originalX ist der tatsächlich gemessene Mittelpunkt des
                 * jeweiligen Symbols in der unveränderten Original-SVG.
                 * Dadurch werden alle Originalicons exakt auf targetX gesetzt.
                 */
                const deltaX =
                    targetX - Number(definition.originalX);

                element.setAttribute(
                    'transform',
                    'translate('
                    + deltaX
                    + ' 0)'
                    + (
                        original
                            ? ' ' + original
                            : ''
                    )
                );
            });
        };

        const bindTap = (element, handler) => {
            if (!element || element.dataset.symconTapBound) {
                return;
            }

            element.dataset.symconTapBound = '1';

            let pointerHandled = false;

            if (window.PointerEvent) {
                element.addEventListener(
                    'pointerup',
                    (event) => {
                        if (
                            event.pointerType === 'touch'
                            || event.pointerType === 'pen'
                        ) {
                            pointerHandled = true;
                            event.preventDefault();
                            event.stopPropagation();

                            handler(event);

                            window.setTimeout(() => {
                                pointerHandled = false;
                            }, 400);
                        }
                    }
                );
            }

            element.addEventListener(
                'click',
                (event) => {
                    if (pointerHandled) {
                        event.preventDefault();
                        event.stopPropagation();
                        return;
                    }

                    handler(event);
                }
            );
        };

        const applyControlIcons = (card) => {
            if (!card || !card.content) {
                return;
            }

            const enabledColor = resolveLayoutTextColor();

            const definitions = [
                {
                    functionName: 'heating',
                    selector: '#gHPStatusHeating',
                    dataKey: 'heatingPumpHeatingMode',
                    activeKey: 'heatingActive',
                    runningColor: '#ff9500'
                },
                {
                    functionName: 'hotwater',
                    selector: '#gHPStatusWW',
                    dataKey: 'heatingPumpHotWaterMode',
                    activeKey: 'hotwaterActive',
                    runningColor: '#ff9500'
                },
                {
                    functionName: 'cooling',
                    selector: '#gHPStatusCooling',
                    dataKey: 'heatingPumpCoolingMode',
                    activeKey: 'coolingActive',
                    runningColor: '#0a84ff'
                }
            ];

            definitions.forEach((definition) => {
                const group =
                    card.content.querySelector(definition.selector);

                if (!group) {
                    return;
                }

                const control =
                    currentControls
                    && currentControls[definition.functionName];

                /*
                 * Zwei Quellen absichtlich parallel:
                 * 1. der echte Binärstatus aus currentData
                 * 2. der von PHP aus dem zentralen Betriebsstatus berechnete
                 *    Active-Wert.
                 *
                 * Sobald EINE Quelle aktiv meldet, wird das Statussymbol
                 * eingefärbt.
                 */
                const running =
                    stateIsOn(definition.dataKey)
                    || !!(
                        currentControls
                        && currentControls[definition.activeKey] === true
                    );

                const hasControl = !!(
                    control
                    && control.configured
                    && Array.isArray(control.options)
                    && control.options.length > 0
                );

                const configuredMode =
                    !!currentConfig[definition.dataKey];

                const visible =
                    configuredMode
                    || hasControl
                    || (
                        currentControls
                        && currentControls.hasOperatingStatus
                    )
                    || running;

                group.style.setProperty(
                    'display',
                    visible ? 'inline' : 'none',
                    'important'
                );

                if (!visible) {
                    return;
                }

                group.style.setProperty(
                    'visibility',
                    'visible',
                    'important'
                );
                group.style.setProperty(
                    'opacity',
                    '1',
                    'important'
                );
                group.style.removeProperty('filter');

                /*
                 * Drei Zustände wie bisher gewünscht:
                 *
                 * 1. AUS / nicht eingeschaltet
                 *    -> Originalfarbe, aber matt
                 *
                 * 2. EINGESCHALTET / freigegeben
                 *    -> Originalfarbe, volle Helligkeit
                 *
                 * 3. AKTIV / läuft tatsächlich
                 *    -> Heizen + Warmwasser orange,
                 *       Kühlen blau
                 */
                let color = enabledColor;
                let opacity = 1;

                if (running) {
                    color = definition.runningColor;
                    opacity = 1;
                } else if (hasControl && !control.enabled) {
                    color = enabledColor;
                    opacity = 0.42;
                }

                group.style.setProperty(
                    'opacity',
                    String(opacity),
                    'important'
                );

                /*
                 * Warmwasserhahn ist im Original ein reines Füllsymbol.
                 * Ein zusätzlicher Stroke macht ihn sichtbar dicker und
                 * verformt genau den Eindruck, der im Screenshot auffällt.
                 */
                /*
                 * ALLE Symbole der oberen Statusleiste bleiben jetzt
                 * vollständig im Originalzustand der SVG.
                 *
                 * Die Original-Card färbt diese Symbole über
                 * var(--primary-text-color). Deshalb ändern wir bei
                 * Heizen, Warmwasser und Kühlen nur diese CSS-Variable
                 * am jeweiligen Originalelement.
                 *
                 * Keine Pfade klonen, keine Geometrie ändern,
                 * kein zusätzlicher Stroke.
                 */
                group.style.setProperty(
                    '--primary-text-color',
                    color,
                    'important'
                );
                group.style.setProperty(
                    'color',
                    color,
                    'important'
                );

                /*
                 * fill/currentColor zusätzlich am Container setzen, damit
                 * sowohl <g>-Symbole als auch das <use>-Warmwassersymbol
                 * zuverlässig reagieren.
                 */
                group.style.setProperty(
                    'fill',
                    'var(--primary-text-color)',
                    'important'
                );
                group.style.removeProperty('stroke');

                /*
                 * Bereits von älteren Versionen gesetzte Inline-Farben an
                 * den Originalpfaden entfernen. Danach greift wieder die
                 * Original-SVG-Logik über --primary-text-color.
                 */
                if (String(group.tagName || '').toLowerCase() !== 'use') {
                    group.querySelectorAll(
                        'path, rect, circle, ellipse, line, polyline, polygon'
                    ).forEach((element) => {
                        element.style.removeProperty('fill');
                        element.style.removeProperty('stroke');
                        element.style.removeProperty('color');
                        element.removeAttribute('fill');
                        element.removeAttribute('stroke');
                    });
                }

                if (running) {
                    group.style.setProperty(
                        'filter',
                        'brightness(1.08) saturate(1.15)',
                        'important'
                    );
                } else {
                    group.style.removeProperty('filter');
                }

                if (hasControl) {
                    group.style.setProperty(
                        'cursor',
                        'pointer',
                        'important'
                    );
                    group.style.setProperty(
                        'pointer-events',
                        'all',
                        'important'
                    );

                    if (!group.dataset.symconControlBound) {
                        group.dataset.symconControlBound = '1';

                        bindTap(
                            group,
                            (event) =>
                                openModeMenu(
                                    definition.functionName,
                                    event
                                )
                        );
                    }
                } else {
                    group.style.setProperty(
                        'cursor',
                        'default',
                        'important'
                    );
                }
            });
        };

        const applyFanAnimation = (card) => {
            if (!card || !card.content || currentConfig.heatingPumpType !== 'A2W') {
                return;
            }

            const fan = card.content.querySelector('#pathHPFan');
            if (!fan) {
                return;
            }

            const fanEntity = currentConfig.fanSpeed;

            if (!fanEntity || !currentData[fanEntity]) {
                fan.classList.remove('rotate');
                fan.style.animationDuration = '';
                return;
            }

            const rawState = currentData[fanEntity].value;
            const normalized = String(rawState ?? '').trim().toLowerCase();

            let rpm = 0;

            /*
             * Bool-Unterstützung:
             * true/on/ein/aktiv = mittlere Lüfterleistung, entspricht ca. 250 U/min.
             * false/off/aus     = Lüfter steht.
             */
            if (['true', 'on', 'yes', 'ja', 'ein', 'active', 'aktiv'].includes(normalized)) {
                rpm = 250;
            } else if (['false', 'off', 'no', 'nein', 'aus', 'inactive', 'inaktiv', ''].includes(normalized)) {
                rpm = 0;
            } else {
                rpm = Number(rawState);

                if (Number.isNaN(rpm)) {
                    rpm = 0;
                }
            }

            if (rpm <= 0) {
                fan.classList.remove('rotate');
                fan.style.animationDuration = '';
                return;
            }

            /*
             * Optische Kennlinie für reale 0–500 U/min.
             *
             * Ziel:
             *   ca. 100 U/min -> 2.5 s/Umdrehung
             *   ca. 200 U/min -> 1.8 s/Umdrehung
             *   ca. 300 U/min -> 1.2 s/Umdrehung
             *   ca. 400 U/min -> 0.8 s/Umdrehung
             *   ca. 500 U/min -> 0.5 s/Umdrehung
             *
             * Zwischenwerte werden stufenlos interpoliert.
             * Werte über 500 U/min werden auf die schnellste Darstellung begrenzt.
             */
            const clampedRpm = Math.max(0, Math.min(500, rpm));

            const points = [
                [0,   3.0],
                [100, 2.5],
                [200, 1.8],
                [300, 1.2],
                [400, 0.8],
                [500, 0.5]
            ];

            let duration = 3.0;

            for (let i = 0; i < points.length - 1; i++) {
                const [rpm1, duration1] = points[i];
                const [rpm2, duration2] = points[i + 1];

                if (clampedRpm >= rpm1 && clampedRpm <= rpm2) {
                    const factor = (clampedRpm - rpm1) / (rpm2 - rpm1);
                    duration = duration1 + (duration2 - duration1) * factor;
                    break;
                }
            }

            fan.classList.add('rotate');
            fan.style.animationDuration = duration.toFixed(2) + 's';
        };

        /*
         * Einheitliches Schließverhalten für ALLE Popups.
         *
         * Capture-Modus ist hier wichtig: Einige SVG-Elemente der Card
         * stoppen ihre Klick-Events selbst. Mit pointerdown + capture wird
         * ein Klick außerhalb des Menüs trotzdem sicher erkannt.
         */
        document.addEventListener(
            'pointerdown',
            (event) => {
                const menu = document.getElementById('wp-mode-menu');

                if (
                    menu
                    && menu.style.display === 'block'
                    && !menu.contains(event.target)
                ) {
                    closeModeMenu();
                }
            },
            true
        );

        /*
         * Zusätzlich lassen sich alle Menüs mit Escape schließen.
         */
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModeMenu();
            }
        });

        const applyCardData = () => {
            const card = document.getElementById('wp-card');

            if (!card) {
                showError('Das Element <heat-pump-card> wurde im HTML nicht gefunden.');
                return;
            }

            try {
                clearError();

                if (!card.content) {
                    card.mount(embeddedSvg, embeddedLocalization);
                }

                /*
                 * Alle Symcon-Erweiterungen direkt nach dem nativen
                 * Daten-Rendering ausführen. Keine HA-Schicht, keine Timer.
                 */
                if (!card.__symconSetDataHookInstalled && typeof card.setData === 'function') {
                    const originalSetData = card.setData;

                    card.setData = function(data) {
                        const result = originalSetData.call(this, data);

                        if (this.content) {
                            applyCoolingVisualization(this);
                            updateRefrigerantValues(this);
                            applyTerminology(this);
                            applyThemeColors(this);
                            applyRefrigerantCircuitMode(this);
                            applyRefrigerantTemperatureColors(this);
                            applyOptionalStatusVisibility(this);
                            applyWWValvePipeGeometry(this);
                            restoreOriginalTemperatureColors(this);
                            applyHeatingCircuitTemperatureColors(this);
                            applyTemperatureColorOpacity(this);
                            applyThreeHeaterRods(this);
                            disableOriginalSettingsLink(this);
                            applyControlIcons(this);
                            applySetpointIcons(this);
                            layoutTopIconBar(this);
                            applyFanAnimation(this);
                            applyHeatingReturnContinuity(this);
                            applySingleCircuitTemperatureDisplay(this);
                            applyThermalSolarVisualization(this);
                            applyAdditionalValues(this);
                            normalizeTemperatureUnits(this);
                            positionHeatingCircuitTemperatures(this);
                            applyFlowAnimations(this);

                            /*
                             * Die Kompaktansicht ist ein eigener Render.
                             * Nach jeder Datenaktualisierung neu aus dem
                             * fertig aufbereiteten Kopfbereich erzeugen.
                             */
                            renderCompactView(this);
                            applyViewMode(this);

                            /*
                             * Ganz zuletzt: Aktivfarben der oberen Statusleiste.
                             * So kann keine nachfolgende SVG-/Layout-Funktion
                             * die Farbe wieder überschreiben.
                             */
                            applyControlIcons(this);
                        }

                        return result;
                    };

                    card.__symconSetDataHookInstalled = true;
                }

                card.setConfig(currentConfig);
                card.setData(currentData);
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

            if (data.command === 'reloadHtml') {
                window.location.reload();
                return;
            }

            if (data.config) {
                currentConfig = data.config;
            }

            if (data.data) {
                currentData = data.data;
            }

            if (data.controls) {
                currentControls = data.controls;
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

        setupViewToggle();
        applyCardData();
  }
};
SYMC0N_HEATPUMP_JAVASCRIPT;
    }
}
