<?php

declare(strict_types=1);

// A strict local substitute, not a complete IP-Symcon implementation.
const KR_READY = 10103;
const VM_UPDATE = 10603;
const KL_ERROR = 10204;
class IPSModuleStrict
{
    public int $InstanceID = 12345;
    public array $properties = [];
    public array $registered = [];
    public array $buffers = [];
    public array $messages = [];
    public array $updates = [];
    public array $formUpdates = [];
    public function Create(): void {}
    public function ApplyChanges(): void {}
    public function __call(string $name, array $args): mixed
    {
        if (str_starts_with($name, 'RegisterProperty')) {
            $this->registered[$args[0]] = ['type' => substr($name, 16), 'default' => $args[1]];
            if (!array_key_exists($args[0], $this->properties)) $this->properties[$args[0]] = $args[1];
            return null;
        }
        if (str_starts_with($name, 'ReadProperty')) {
            return $this->properties[$args[0]] ?? throw new RuntimeException('Unknown property: ' . $args[0]);
        }
        return match ($name) {
            'SetVisualizationType' => null,
            'SetBuffer' => $this->buffers[$args[0]] = $args[1],
            'GetBuffer' => $this->buffers[$args[0]] ?? '',
            'RegisterMessage' => $this->messages[$args[0]] = $args[1],
            'UnregisterMessage' => $this->removeMessage($args[0]),
            'UpdateVisualizationValue' => $this->updates[] = json_decode($args[0], true, 512, JSON_THROW_ON_ERROR),
            'UpdateFormField' => $this->formUpdates[$args[0]][$args[1]] = $args[2],
            default => throw new RuntimeException('Unexpected Symcon API: ' . $name)
        };
    }
    private function removeMessage(int $id): null { unset($this->messages[$id]); return null; }
}
$variables = [];
$formattedValues = [];
$variableMetadata = [];
$profiles = [];
$writes = [];
$actions = [];
$instances = [];
$instanceProperties = [];
$objectIdents = [];
function IPS_GetProperty(int $id, string $property): mixed {
    return $GLOBALS['instanceProperties'][$id][$property] ?? ($property === 'IPAddress' ? '0.0.0.0' : null);
}
function IPS_GetInstanceListByModuleID(string $moduleID): array {
    return array_keys(array_filter($GLOBALS['instances'], static fn(string $id): bool => $id === $moduleID));
}
function IPS_GetObjectIDByIdent(string $ident, int $parent): int|false {
    return $GLOBALS['objectIdents'][$parent][$ident] ?? false;
}
function IPS_GetChildrenIDs(int $parent): array {
    return array_values($GLOBALS['objectIdents'][$parent] ?? []);
}
function IPS_GetName(int $id): string { return 'Test instance ' . $id; }
function IPS_GetObject(int $id): array {
    foreach ($GLOBALS['objectIdents'] as $children) {
        $ident = array_search($id, $children, true);
        if ($ident !== false) return ['ObjectIdent' => $ident];
    }
    return ['ObjectIdent' => ''];
}
function IPS_GetParent(int $id): int {
    foreach ($GLOBALS['objectIdents'] as $parent => $children) {
        if (in_array($id, $children, true)) return $parent;
    }
    return 0;
}
function IPS_GetKernelRunlevel(): int { return KR_READY; }
function IPS_VariableExists(int $id): bool { return isset($GLOBALS['variables'][$id]); }
function IPS_GetVariable(int $id): array {
    return ($GLOBALS['variableMetadata'][$id] ?? []) + ['VariableType' => 2, 'VariableProfile' => '', 'VariableCustomProfile' => '', 'VariableAction' => 0, 'VariableCustomAction' => 0];
}
function IPS_VariableProfileExists(string $name): bool { return isset($GLOBALS['profiles'][$name]); }
function IPS_GetVariableProfile(string $name): array { return $GLOBALS['profiles'][$name]; }
function GetValue(int $id): mixed { return $GLOBALS['variables'][$id]; }
function GetValueFormatted(int $id): string { return $GLOBALS['formattedValues'][$id] ?? (string) GetValue($id); }
function SetValue(int $id, mixed $value): void {
    $GLOBALS['writes'][] = [$id, $value];
    $GLOBALS['variables'][$id] = $value;
}
function RequestAction(int $id, mixed $value): void { $GLOBALS['actions'][] = [$id, $value]; }
require __DIR__ . '/../WP/module.php';
$checks = 0;
function check(bool $condition, string $message): void {
    $GLOBALS['checks']++;
    if (!$condition) throw new RuntimeException($message);
}
function callPrivate(Waermepumpe $module, string $method): array {
    return (new ReflectionMethod($module, $method))->invoke($module);
}
function createModule(array $properties = []): Waermepumpe {
    $module = new Waermepumpe();
    $module->properties = $properties;
    $module->Create();
    return $module;
}
$svgFiles = glob(__DIR__ . '/../WP/heat-pump/heat-pump-card/*.svg');
foreach ($svgFiles as $svgFile) {
    $document = new DOMDocument();
    check($document->load($svgFile, LIBXML_NONET), 'Invalid SVG XML');
    $ids = [];
    foreach ((new DOMXPath($document))->query('//*[@id]') as $element) {
        $id = $element->getAttribute('id');
        check(!isset($ids[$id]), 'Duplicate SVG ID: ' . $id);
        $ids[$id] = true;
    }
}
$module = createModule();
$legacy = json_decode(file_get_contents(__DIR__ . '/fixtures/main-properties.json'), true, 512, JSON_THROW_ON_ERROR);
$retained = array_intersect_key($legacy, $module->registered);
check(count($retained) === 88, 'Unexpected change to retained main properties');
foreach ($retained as $name => $definition) {
    check($module->registered[$name] === $definition, 'Changed property type/default: ' . $name);
}
$library = json_decode(file_get_contents(__DIR__ . '/../library.json'), true, 512, JSON_THROW_ON_ERROR);
$metadata = json_decode(file_get_contents(__DIR__ . '/../WP/module.json'), true, 512, JSON_THROW_ON_ERROR);
check($library['id'] === '{DB43F0BF-5550-99C6-0548-F3C87E9BBE2D}', 'Changed library identity');
check($metadata['id'] === '{DB43F0BF-5550-99C6-0548-F3C87E9BBE2D}', 'Changed module identity');
$form = json_decode($module->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
$fields = [];
$allFormNames = [];
$selectors = [];
$walk = function (array $item) use (&$walk, &$fields, &$allFormNames, &$selectors, $module): void {
    if (isset($item['name'])) {
        check(!isset($allFormNames[$item['name']]), 'Duplicate field: ' . $item['name']);
        $allFormNames[$item['name']] = $item;
        $derivedPicker = $item['type'] === 'SelectVariable' && str_starts_with($item['name'], 'Automatic');
        if ($derivedPicker) {
            check(!isset($module->registered[$item['name']]) && isset($module->registered[substr($item['name'], 9)]), 'Derived picker is not display-only');
            check($item['enabled'] === false && !isset($item['onChange']), 'Derived picker may edit or trigger actions');
        } elseif (!in_array($item['type'], ['Label', 'List', 'Button'], true)) {
            check(isset($module->registered[$item['name']]), 'Unregistered field: ' . $item['name']);
            $fields[$item['name']] = true;
            if ($item['type'] === 'SelectVariable') $selectors[] = $item['name'];
        }
    }
    foreach ($item['items'] ?? [] as $child) $walk($child);
};
foreach ($form['elements'] as $element) $walk($element);
// Keep stored main settings, but do not ask for inputs the modern renderer ignores.
$hidden = [
    'ManualConfiguration', 'LuxtronikWebInterfaceURL',
    'HeatingPumpStatusOnOff', 'HeatingPumpHeatingMode', 'HeatingPumpHotWaterMode',
    'HeatingPumpCoolingMode', 'DefrostMode', 'OperatingStatusHeatingValues',
    'OperatingStatusHotWaterValues', 'OperatingStatusCoolingValues', 'OperatingStatusDefrostValues',
    'HeaterRodWW', 'HeaterRodLevel1', 'HeaterRodLevel2', 'SingleCircuitHotWaterTemperatureSwitch',
    'ThermalSolarPump', 'ThermalSolarFluxTemp', 'ThermalSolarReturnTemp'
];
foreach ($hidden as $name) {
    check(!isset($fields[$name]), 'Unused property shown: ' . $name);
    $value = $module->registered[$name]['type'] === 'String' ? '8,9' : 12345;
    if ($module->registered[$name]['type'] === 'Boolean') $value = false;
    check(createModule([$name => $value])->properties[$name] === $value, 'Hidden setting reset: ' . $name);
}
check(count($fields) + count($hidden) === count($module->registered), 'Unexpected missing property');
check(isset($fields['ReturnTemperatureSetpointVariable']), 'Missing return setpoint selector');

foreach ($selectors as $index => $name) {
    $id = 20000 + $index;
    $variables[$id] = 1;
    $module = createModule([$name => $id]);
    check($module->properties[$name] === $id, 'Existing assignment reset: ' . $name);
    $module->ApplyChanges();
    check(isset($module->messages[$id]), 'No update subscription: ' . $name);
    $module->properties[$name] = 0;
    $module->ApplyChanges();
    check(!isset($module->messages[$id]), 'Old subscription retained: ' . $name);
}
$variables[30001] = 7;
$module = createModule(['OperatingStatusVariable' => 30001]);
$data = callPrivate($module, 'BuildVisualizationData');
check($data['heatingPumpCoolingMode']['value'] && !$data['heatingPumpHeatingMode']['value'], 'Central status decoding');
check(callPrivate($module, 'BuildCardConfig')['hpRunning'] === '', 'Phantom fan key from operating status');
$variables[30002] = true;
$module = createModule(['HeatingPumpHeatingMode' => 30002]);
check(callPrivate($module, 'BuildControlData')['heatingActive'], 'Legacy heating fallback lost');
$variables[30003] = 22.5;
$module = createModule(['AmbientTemperatureActual' => 30003]);
check(callPrivate($module, 'BuildVisualizationData')['ambientTemperatureNormal']['value'] === 22.5, 'Room temperature fallback lost');
foreach ([true, false, 'on', 'off', 250, 0, -10] as $value) {
    $variables[30004] = $value;
    $module = createModule(['FanSpeed' => 30004]);
    $expected = in_array($value, [true, 'on', 250], true);
    check(callPrivate($module, 'BuildVisualizationData')['hpRunning']['value'] === $expected, 'Fan status conversion');
}
$module = createModule(['FanSpeed' => 99999]);
check(callPrivate($module, 'BuildCardConfig')['hpRunning'] === '', 'Nonexistent fan variable');
$variables[30005] = 28.9;
$variables[30006] = 30.0;
$module = createModule(['RefluxTemperatureHeating1' => 30005, 'ReturnTemperatureSetpointVariable' => 30006]);
$data = callPrivate($module, 'BuildVisualizationData');
check($data['refluxTemperatureHeating']['value'] === 28.9, 'Existing return actual assignment lost');
check($data['returnTemperatureSetpoint']['value'] === 30.0, 'Return setpoint not in payload');
$module->ApplyChanges();
check(isset($module->messages[30005], $module->messages[30006]), 'Return values not subscribed');

// Numeric controls must reject invalid input before either side-effect route.
foreach ([
    ['warmWaterSetpoint', 'WarmWaterSetpointVariable', 20.0, 80.0],
    ['heatingTemperatureCorrection', 'HeatingTemperatureCorrectionVariable', -10.0, 10.0]
] as [$function, $property, $min, $max]) {
    foreach (['direct', 'standard', 'custom'] as $route) {
        $variables[50001] = 25.0;
        $variableMetadata[50001] = [
            'VariableAction' => $route === 'standard' ? 10001 : 0,
            'VariableCustomAction' => $route === 'custom' ? 10002 : 0,
        ];
        $module = createModule([$property => 50001]);
        foreach ([null, '', 'invalid', '1e999', true, false, [], NAN, INF, -INF, $min - 0.1, $max + 0.1] as $invalid) {
            $before = [$writes, $actions, $variables[50001]];
            $rejected = false;
            try {
                $module->RequestAction('SetNumericControl', ['function' => $function, 'value' => $invalid]);
            } catch (InvalidArgumentException) { $rejected = true; }
            check($rejected && [$writes, $actions, $variables[50001]] === $before, 'Invalid numeric input caused a side effect: ' . $function . '/' . $route);
        }
        $before = [$writes, $actions];
        $rejected = false;
        try { $module->RequestAction('SetNumericControl', ['function' => $function]); }
        catch (InvalidArgumentException) { $rejected = true; }
        check($rejected && [$writes, $actions] === $before, 'Missing numeric value accepted');
        foreach ([$min, $max, (string) (($min + $max) / 2)] as $valid) {
            $before = [count($writes), count($actions)];
            $module->RequestAction('SetNumericControl', json_encode(['function' => $function, 'value' => $valid], JSON_THROW_ON_ERROR));
            $calls = $route === 'direct' ? $writes : $actions;
            check(end($calls) === [50001, (float) $valid], 'Valid numeric value not delivered with correct type');
            check([count($writes), count($actions)] === [$before[0] + ($route === 'direct' ? 1 : 0), $before[1] + ($route === 'direct' ? 0 : 1)], 'Wrong numeric action route');
        }
    }
}
// The same custom profile limits must be used by the UI and action handler.
$profiles['Test.Setpoint'] = ['MinValue' => 21.2, 'MaxValue' => 60.2, 'StepSize' => 0.3, 'Suffix' => ' °C'];
$variableMetadata[50001] = ['VariableCustomProfile' => 'Test.Setpoint'];
$module = createModule(['WarmWaterSetpointVariable' => 50001]);
$info = callPrivate($module, 'BuildControlData')['warmWaterSetpoint'];
check($info['min'] === 21.2 && $info['max'] === 60.2 && $info['step'] === 0.3, 'Custom profile limits lost');
foreach ([21.2, 60.2] as $valid) {
    $module->RequestAction('SetNumericControl', ['function' => 'warmWaterSetpoint', 'value' => $valid]);
    check(end($writes) === [50001, $valid], 'Custom profile boundary rejected');
}
foreach ([20.0, 60.3] as $invalid) {
    $before = [$writes, $actions];
    $rejected = false;
    try { $module->RequestAction('SetNumericControl', ['function' => 'warmWaterSetpoint', 'value' => $invalid]); }
    catch (InvalidArgumentException) { $rejected = true; }
    check($rejected && [$writes, $actions] === $before, 'Custom profile limit bypassed');
}
// Numeric actions must not silently truncate fractions or target booleans/strings.
foreach ([0, 1, 3] as $type) {
    $variableMetadata[50001] = ['VariableType' => $type];
    $before = [$writes, $actions];
    $rejected = false;
    try { $module->RequestAction('SetNumericControl', ['function' => 'warmWaterSetpoint', 'value' => 25.5]); }
    catch (InvalidArgumentException) { $rejected = true; }
    check($rejected && [$writes, $actions] === $before, 'Incompatible numeric variable type accepted');
}
$variableMetadata[50001] = ['VariableType' => 1];
$module->RequestAction('SetNumericControl', ['function' => 'warmWaterSetpoint', 'value' => 25]);
check(end($writes) === [50001, 25], 'Integer setpoint type lost');

// Optional Luxtronik: only an explicit source enables Ident resolution.
function namedFormItems(array $elements): array {
    $items = [];
    foreach ($elements as $element) {
        if (isset($element['name'])) $items[$element['name']] = $element;
        if (isset($element['items'])) $items += namedFormItems($element['items']);
    }
    return $items;
}
$luxtronikID = '{3884C1C3-F4FF-9A56-B982-B986E934A5E7}';
$instances = [61000 => $luxtronikID, 61002 => '{OTHER-MODULE}'];
$objectIdents[61000] = [
    'Betriebszustand' => 62000, 'Vorlauftemperatur_Heizkreis' => 62001,
    'Ruecklauftemperatur_Heizkreis' => 62002, 'Ruecklauf_Soll_Heizkreis' => 62003,
    'Aussentemperatur' => 62004, 'Warmwasser_Ist_Temperatur' => 62005,
    'Ventilator_Drehzahl' => 62007, 'Mode_Heizung' => 62008,
    'Anpassung_WW' => 62009, 'Anpassung_Temp' => 62010,
    'Ausgang_Verdichter_1' => 62011, 'Verdichterdrehzahl' => 62012,
    'Kondensationstemperatur' => 62013,
];
foreach ([62000 => 7, 62001 => 37.5, 62002 => 28.9, 62003 => 30.0, 62004 => 10.0,
          62005 => 48.0, 62007 => 250, 62008 => 0, 62009 => 50.0, 62010 => -1.0,
          62011 => true, 62012 => 3000, 62013 => 42.5, 62050 => -2.0, 62060 => 22.0] as $id => $value) {
    $variables[$id] = $value;
}
$variableMetadata[62008] = ['VariableType' => 1, 'VariableAction' => 61000];
$variableMetadata[62009] = ['VariableAction' => 61000];
$variableMetadata[62010] = ['VariableCustomAction' => 61000];
$objectIdents[61002] = ['ExternalRoomSensor' => 62060];

// Even a single installed source must not override freely chosen variables.
$manualModule = createModule(['OutdoorTemperature' => 62060]);
$manualModule->ApplyChanges();
check(callPrivate($manualModule, 'BuildVisualizationData')['outdoorTemperature']['variableId'] === 62060, 'None auto-detected a single Luxtronik');
check(isset($manualModule->messages[62060]) && !isset($manualModule->messages[62004]), 'None subscribed to automatic source');
$manualFields = namedFormItems(json_decode($manualModule->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR)['elements']);
foreach ($selectors as $property) check($manualFields[$property]['enabled'] === true, 'None locked a variable picker');
foreach (['AssignmentOverview', 'AssignmentSummary', 'LuxtronikAssignmentHint', 'LuxtronikAssignButton',
          'ManualConfiguration', 'LuxtronikDiagnoseButton', 'LuxtronikWebInterfaceURL'] as $removed) {
    check(!isset($manualFields[$removed]), 'Redundant assignment UI remains: ' . $removed);
}
check($manualFields['LuxtronikInstanceID']['type'] === 'SelectInstance'
    && $manualFields['LuxtronikInstanceID']['validModules'] === [$luxtronikID], 'Luxtronik picker accepts foreign modules');

$sourceModule = createModule(['LuxtronikInstanceID' => 61000, 'OutdoorTemperature' => 62060,
    'AmbientTemperatureActual' => 62060, 'AdditionalValue000' => 62060,
    'ManualConfiguration' => true]); // Obsolete switch must not change picker semantics.
$before = [$writes, $actions, $sourceModule->properties];
$sourceModule->ApplyChanges();
$data = callPrivate($sourceModule, 'BuildVisualizationData');
foreach (['operatingStatus' => 62000, 'supplyTemperature' => 62001,
          'refluxTemperatureHeating' => 62002, 'returnTemperatureSetpoint' => 62003,
          'outdoorTemperature' => 62004, 'tankTempWWMiddle' => 62005,
          'fanSpeed' => 62007, 'compressorRunning' => 62011,
          'compressorValue' => 62012, 'condenserTemperature' => 62013] as $key => $expected) {
    check($data[$key]['variableId'] === $expected, 'Ident resolution mismatch: ' . $key);
    check(isset($sourceModule->messages[$expected]), 'Missing Ident subscription: ' . $key);
}
check($data['additionalValue000']['variableId'] === 62060, 'Unmapped manual field changed');
$liveFields = namedFormItems(json_decode($sourceModule->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR)['elements']);
foreach (['OutdoorTemperature' => 62004, 'CompressorValue' => 62012,
          'CondenserTemperature' => 62013, 'HeatingControlVariable' => 62008,
          'WarmWaterSetpointVariable' => 62009] as $property => $expected) {
    check($liveFields[$property]['visible'] === false, 'Manual picker shown alongside Ident');
    check($liveFields['Automatic' . $property]['visible'] === true
        && $liveFields['Automatic' . $property]['value'] === $expected
        && $liveFields['Automatic' . $property]['enabled'] === false, 'Incorrect displayed Ident picker');
}
foreach (['AmbientTemperatureActual', 'AdditionalValue000', 'HeaterRod1'] as $property) {
    check($liveFields[$property]['enabled'] === true, 'Missing or unmapped Ident prevents manual fallback');
}
check([$writes, $actions, $sourceModule->properties] === $before, 'Opening form changed saved assignments or operated device');
$sourceModule->RequestAction('SetNumericControl', ['function' => 'warmWaterSetpoint', 'value' => 52.0]);
check(end($actions) === [62009, 52.0], 'Control does not use displayed source');

// Boiler sensors keep their physical level; DHW actual belongs in the middle.
check(!isset($data['tankTempWWUp'], $data['tankTempWWDown']), 'DHW actual duplicated at another boiler level');
check($liveFields['AutomaticTankTempWWMiddle']['value'] === 62005
    && $liveFields['AutomaticTankTempWWMiddle']['visible'] === true, 'DHW actual not shown in middle picker');
$objectIdents[61000]['Temperatur_Brauchwasser_Oben'] = 62006;
$variables[62006] = 49.0;
$boilerData = callPrivate(createModule(['LuxtronikInstanceID' => 61000]), 'BuildVisualizationData');
check($boilerData['tankTempWWUp']['variableId'] === 62006
    && $boilerData['tankTempWWMiddle']['variableId'] === 62005, 'Upper and middle boiler sensors conflated');
unset($objectIdents[61000]['Temperatur_Brauchwasser_Oben']);

// Execute the actual source picker's onChange with unsaved manual values.
function WP_RefreshAssignmentStatus(int $id, int $source, string $values): void {
    check($id === $GLOBALS['assignmentModule']->InstanceID, 'Wrong source-change target');
    $GLOBALS['assignmentModule']->RefreshAssignmentStatus($source, $values);
}
$assignmentModule = $sourceModule;
foreach ($assignmentModule->properties as $property => $value) $$property = $value;
$id = $assignmentModule->InstanceID;
$OutdoorTemperature = 62050;
$LuxtronikInstanceID = 0;
$before = [$writes, $actions, $assignmentModule->properties];
ob_start();
try { eval($liveFields['LuxtronikInstanceID']['onChange']); } finally { $output = ob_get_clean(); }
check($output === '', 'Source change opened a dialog');
foreach ($selectors as $property) check($assignmentModule->formUpdates[$property]['enabled'] === true, 'Clearing source did not unlock picker');
check($assignmentModule->formUpdates['OutdoorTemperature']['visible'] === true
    && $assignmentModule->formUpdates['AutomaticOutdoorTemperature']['visible'] === false, 'Clearing source did not restore manual picker');
foreach ($assignmentModule->formUpdates as $property => $updates) {
    if (isset($assignmentModule->registered[$property])) check(!isset($updates['value']), 'Source change overwrote manual value');
}
check([$writes, $actions, $assignmentModule->properties] === $before, 'Source change saved values or operated device');
$LuxtronikInstanceID = 61000;
eval($liveFields['LuxtronikInstanceID']['onChange']);
check($assignmentModule->formUpdates['AutomaticOutdoorTemperature']['value'] === 62004
    && $assignmentModule->formUpdates['AutomaticOutdoorTemperature']['visible'] === true, 'Selecting source did not update picker immediately');

$instances[61001] = $luxtronikID;
$objectIdents[61001] = ['Aussentemperatur' => 62050];
$assignmentModule->RefreshAssignmentStatus(61001, '{}');
check($assignmentModule->formUpdates['AutomaticOutdoorTemperature']['value'] === 62050, 'Source change retained old source');
check($assignmentModule->formUpdates['AutomaticCompressorValue']['visible'] === false, 'Missing Ident retained old source picker');
$sourceModule->properties['LuxtronikInstanceID'] = 61001;
$sourceModule->ApplyChanges();
$data = callPrivate($sourceModule, 'BuildVisualizationData');
check($data['outdoorTemperature']['variableId'] === 62050 && !isset($data['compressorValue']), 'Source change leaked old values');
check(isset($sourceModule->messages[62050]) && !isset($sourceModule->messages[62012]), 'Source change retained old subscriptions');
$sourceModule->properties['LuxtronikInstanceID'] = 0;
$sourceModule->ApplyChanges();
check(callPrivate($sourceModule, 'BuildVisualizationData')['outdoorTemperature']['variableId'] === 62060, 'Clearing source lost saved manual assignment');
check(isset($sourceModule->messages[62060]) && !isset($sourceModule->messages[62050]), 'Clearing source retained subscriptions');

// Existing aliases, type checks and source validation still apply.
unset($objectIdents[61000]['Verdichterdrehzahl'], $objectIdents[61000]['Kondensationstemperatur']);
$objectIdents[61000]['Verdichterfrequenz'] = 62012;
$objectIdents[61000]['Liquefaction_Temperature'] = 62013;
$aliasData = callPrivate(createModule(['LuxtronikInstanceID' => 61000]), 'BuildVisualizationData');
check($aliasData['compressorValue']['variableId'] === 62012 && $aliasData['condenserTemperature']['variableId'] === 62013, 'Alternative Idents lost');
foreach ([61002, 61999] as $invalid) {
    $invalidData = callPrivate(createModule(['LuxtronikInstanceID' => $invalid, 'OutdoorTemperature' => 62060]), 'BuildVisualizationData');
    check($invalidData['outdoorTemperature']['variableId'] === 62060 && !isset($invalidData['compressorValue']), 'Invalid source used as Luxtronik');
}
$variableMetadata[62009] = [];
$control = callPrivate(createModule(['LuxtronikInstanceID' => 61000]), 'BuildControlData');
check($control['warmWaterSetpoint']['configured'] === false, 'Read-only measurement imported as control');
$variableMetadata[62009] = ['VariableAction' => 61000];

// Resolve the original web UI address from the selected module, without network I/O.
$instanceProperties[61000] = ['IPAddress' => '192.0.2.10', 'Port' => 8889];
$instanceProperties[61001] = ['IPAddress' => '192.0.2.20', 'Port' => 8888];
$before = [$writes, $actions];
check(callPrivate(createModule(['LuxtronikInstanceID' => 61000]), 'BuildCardConfig')['luxtronikWebInterfaceURL'] === 'http://192.0.2.10/Webserver/index.html', 'Selected IPAddress not used for web interface');
check(callPrivate(createModule(['LuxtronikInstanceID' => 61001]), 'BuildCardConfig')['luxtronikWebInterfaceURL'] === 'http://192.0.2.20/Webserver/index.html', 'Wrong web source or data port used');
check(callPrivate(createModule(), 'BuildCardConfig')['luxtronikWebInterfaceURL'] === '', 'Ambiguous source chosen for web interface');
check(callPrivate(createModule(['LuxtronikInstanceID' => 61999]), 'BuildCardConfig')['luxtronikWebInterfaceURL'] === '', 'Deleted web source used');
check(callPrivate(createModule(['LuxtronikInstanceID' => 61002]), 'BuildCardConfig')['luxtronikWebInterfaceURL'] === '', 'Foreign module used for web interface');
unset($instances[61001]);
check(callPrivate(createModule(), 'BuildCardConfig')['luxtronikWebInterfaceURL'] === '', 'None auto-detected a single source for web interface');
foreach ([
    ['luxtronik.fritz.box', 'http://luxtronik.fritz.box/Webserver/index.html'],
    ['2001:db8::10', 'http://[2001:db8::10]/Webserver/index.html'],
    ['', ''], ['0.0.0.0', ''], ['::', ''], ['host/path', ''], ['javascript:invalid', ''],
] as [$address, $expected]) {
    $instanceProperties[61000]['IPAddress'] = $address;
    check(callPrivate(createModule(['LuxtronikInstanceID' => 61000]), 'BuildCardConfig')['luxtronikWebInterfaceURL'] === $expected, 'Invalid web address derivation');
}
foreach ([
    'https://luxtronik.example:8443/interface/#remote',
    'http://192.0.2.10:8080/',
] as $url) {
    check(callPrivate(createModule(['LuxtronikWebInterfaceURL' => $url]), 'BuildCardConfig')['luxtronikWebInterfaceURL'] === $url, 'Explicit web URL not preserved');
}
foreach (['javascript:alert(1)', 'data:text/html,test', '//example.com/', 'http://user:password@example.com/', "http://exam\nple.com/", 'file:///tmp/test'] as $url) {
    check(callPrivate(createModule(['LuxtronikWebInterfaceURL' => $url]), 'BuildCardConfig')['luxtronikWebInterfaceURL'] === '', 'Unsupported web URL accepted');
}
check([$writes, $actions] === $before, 'Web address resolution caused device or variable actions');

$instances = $objectIdents = [];
$module = createModule();
$html = $module->GetVisualizationTile();
check(str_contains($html, 'SymconHeatPump.init('), 'Visualization not generated');
$artifactDir = getenv('WP_TEST_ARTIFACT_DIR');
if ($artifactDir !== false && $artifactDir !== '') {
    if (!is_dir($artifactDir)) mkdir($artifactDir, 0777, true);
    file_put_contents($artifactDir . '/visualization.html', '<meta charset="utf-8">' . $html);
    $configured = [];
    foreach ($selectors as $index => $name) {
        $configured[$name] = 40000 + $index;
        $variables[40000 + $index] = $name === 'OperatingStatusVariable' ? 7 : 25.5;
    }
    $configured += ['HeaterRodCount' => 3, 'ThermalSolarAvailable' => true,
        'HeatingCircuitType2' => 'radiator', 'HeatingCircuitType3' => 'underfloor'];
    $actualId = $configured['RefluxTemperatureHeating1'];
    $targetId = $configured['ReturnTemperatureSetpointVariable'];
    $variables[$actualId] = 28.9;
    $variables[$targetId] = 30.0;
    $formattedValues[$actualId] = '28.9 °C';
    $formattedValues[$targetId] = '30.0 °C';
    file_put_contents($artifactDir . '/configured.html', '<meta charset="utf-8">' . createModule($configured)->GetVisualizationTile());
    // A saved source with empty measurement properties must work without import.
    $instances = [61000 => $luxtronikID];
    $objectIdents[61000] = ['Betriebszustand' => 62000, 'Vorlauftemperatur_Heizkreis' => 62001, 'Ventilator_Drehzahl' => 62007, 'Mode_Heizung' => 62008, 'Anpassung_WW' => 62009, 'Aussentemperatur' => 62004, 'Verdichterdrehzahl' => 62012, 'Kondensationstemperatur' => 62013];
    $variables[62004] = 10.0;
    $variables[62012] = 3000;
    $variables[62013] = 42.5;
    $liveConfigured = $configured;
    $liveConfigured['LuxtronikInstanceID'] = 61000;
    foreach (['OutdoorTemperature', 'CompressorValue', 'CondenserTemperature'] as $property) $liveConfigured[$property] = 0;
    $formattedValues[62012] = '3000 rpm';
    $formattedValues[62013] = '42.5 °C';
    $liveModule = createModule($liveConfigured);
    $liveModule->ApplyChanges();
    file_put_contents($artifactDir . '/live-ident.html', '<meta charset="utf-8">' . $liveModule->GetVisualizationTile());
    $variables[62012] = 1500;
    $formattedValues[62012] = '1500 rpm';
    $liveModule->MessageSink(time(), 62012, VM_UPDATE, []);
    file_put_contents($artifactDir . '/live-ident-update.json', json_encode(end($liveModule->updates), JSON_THROW_ON_ERROR));
    $variables[62012] = 3000;
    $formattedValues[62012] = '3000 rpm';
    // The user's legacy names pass through the same map and data path.
    unset($objectIdents[61000]['Verdichterdrehzahl'], $objectIdents[61000]['Kondensationstemperatur']);
    $objectIdents[61000]['Verdichterfrequenz'] = 62012;
    $objectIdents[61000]['Liquefaction_Temperature'] = 62013;
    $objectIdents[61000]['Verdichterfrequenz_Soll'] = 62014;
    $variables[62014] = 75;
    $variables[62012] = 50;
    $formattedValues[62012] = '50 Hz';
    $aliasModule = createModule($liveConfigured);
    $aliasModule->ApplyChanges();
    file_put_contents($artifactDir . '/alias-ident.html', '<meta charset="utf-8">' . $aliasModule->GetVisualizationTile());
    $variables[62012] = 80;
    $formattedValues[62012] = '80 Hz';
    $aliasModule->MessageSink(time(), 62012, VM_UPDATE, []);
    file_put_contents($artifactDir . '/alias-ident-update.json', json_encode(end($aliasModule->updates), JSON_THROW_ON_ERROR));
    preg_match_all('~<script[^>]*>(.*?)</script>~s', $html, $scripts);
    foreach ($scripts[1] as $index => $js) file_put_contents($artifactDir . '/script-' . $index . '.js', $js);
}
echo "PASS: $checks checks; 88 retained main properties; " . count($selectors) . " selectable variables\n";
