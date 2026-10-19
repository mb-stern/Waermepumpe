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
$objectIdents = [];
function IPS_GetInstanceListByModuleID(string $moduleID): array {
    return array_keys(array_filter($GLOBALS['instances'], static fn(string $id): bool => $id === $moduleID));
}
function IPS_GetObjectIDByIdent(string $ident, int $parent): int|false {
    return $GLOBALS['objectIdents'][$parent][$ident] ?? false;
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
        if (!in_array($item['type'], ['Label', 'List', 'Button'], true)) {
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

// Optional Luxtronik assignment is a form suggestion, never a device action.
// Exact Idents checked against mb-stern/Luxtronik development 1fa55a2.
$luxtronikID = '{3884C1C3-F4FF-9A56-B982-B986E934A5E7}';
$module = createModule();
$before = [$writes, $actions, $module->properties];
check(str_contains($module->AssignLuxtronik(0, '{}'), 'Keine Luxtronik'), 'Missing Luxtronik not explained');
check($module->formUpdates === [] && [$writes, $actions, $module->properties] === $before, 'Missing Luxtronik changed manual setup');
$instances = [61000 => $luxtronikID, 61001 => $luxtronikID, 61002 => '{OTHER-MODULE}'];
check(str_contains($module->AssignLuxtronik(0, '{}'), 'Mehrere'), 'Ambiguous Luxtronik selection accepted');
check($module->formUpdates === [], 'Ambiguous selection changed form');
foreach ([61002, 61999] as $invalidInstance) {
    $rejected = false;
    try { $module->AssignLuxtronik($invalidInstance, '{}'); }
    catch (InvalidArgumentException) { $rejected = true; }
    check($rejected && $module->formUpdates === [], 'Wrong or deleted Luxtronik instance accepted');
}
$objectIdents[61000] = [
    'Betriebszustand' => 62000,
    'Vorlauftemperatur_Heizkreis' => 62001,
    'Ruecklauftemperatur_Heizkreis' => 62002,
    'Ruecklauf_Soll_Heizkreis' => 62003,
    'Aussentemperatur' => 62004,
    'Warmwasser_Ist_Temperatur' => 62005,
    'Ventilator_Drehzahl' => 62007,
    'Mode_Heizung' => 62008,
    'Anpassung_WW' => 62009,
    'Anpassung_Temp' => 62010,
    'Ausgang_Verdichter_1' => 62011,
];
foreach ([62000 => 7, 62001 => 37.5, 62002 => 28.9, 62003 => 30.0, 62004 => 10.0,
          62005 => 48.0, 62007 => 250, 62008 => 0, 62009 => 50.0, 62010 => -1.0, 62011 => true] as $variableID => $value) {
    $variables[$variableID] = $value;
}
$variableMetadata[62008] = ['VariableType' => 1, 'VariableAction' => 61000];
$variableMetadata[62009] = ['VariableAction' => 61000];
$variableMetadata[62010] = ['VariableCustomAction' => 61000];
$objectIdents[61001] = ['Aussentemperatur' => 62050];
$variables[62050] = -2.0;
$explicit = createModule();
$explicit->AssignLuxtronik(61001, '{}');
check($explicit->formUpdates['OutdoorTemperature']['value'] === 62050, 'Explicit instance ignored');
check(!isset($explicit->formUpdates['OperatingStatusVariable']['value']), 'Variables leaked from another instance');

// Refresh incorrect Luxtronik links and switch units while retaining external modules.
$objectIdents[61002] = ['ExternalRoomSensor' => 62060];
$variables[62060] = 22.0;
$refresh = createModule([
    'SupplyTemperature' => 62002, // Wrong Ident, but from a Luxtronik instance.
    'OutdoorTemperature' => 62050, // Previously linked to the other Luxtronik unit.
    'WarmWaterSetpointVariable' => 62060, // Variable supplied by another module.
]);
$before = [$writes, $actions, $refresh->properties];
$refresh->AssignLuxtronik(61000, '{}');
check($refresh->formUpdates['SupplyTemperature']['value'] === 62001, 'Incorrect Luxtronik mapping not refreshed');
check($refresh->formUpdates['OutdoorTemperature']['value'] === 62004, 'Old Luxtronik source not replaced');
check(!isset($refresh->formUpdates['WarmWaterSetpointVariable']['value']), 'External module assignment overwritten');
check([$writes, $actions, $refresh->properties] === $before, 'Refresh persisted settings or triggered actions');
$unsavedExternal = createModule(['OutdoorTemperature' => 62004]);
$unsavedExternal->AssignLuxtronik(61000, json_encode(['OutdoorTemperature' => 62060], JSON_THROW_ON_ERROR));
check(!isset($unsavedExternal->formUpdates['OutdoorTemperature']['value']), 'Unsaved external assignment overwritten');
$unsavedLuxtronik = createModule(['OutdoorTemperature' => 62060]);
$unsavedLuxtronik->AssignLuxtronik(61000, json_encode(['OutdoorTemperature' => 62050], JSON_THROW_ON_ERROR));
check($unsavedLuxtronik->formUpdates['OutdoorTemperature']['value'] === 62004, 'Current form source not used for refresh');
$sourceChange = createModule(['OutdoorTemperature' => 62004, 'OperatingStatusVariable' => 62000]);
$sourceChange->AssignLuxtronik(61001, '{}');
check($sourceChange->formUpdates['OutdoorTemperature']['value'] === 62050, 'Selected source not used when changing units');
check($sourceChange->formUpdates['OperatingStatusVariable']['value'] === 0, 'Missing data point retained from the old unit');

unset($instances[61001]);
$module = createModule(['OutdoorTemperature' => 63000]);
$before = [$writes, $actions, $module->properties];
// Include a manual edit that has not yet been saved in the instance.
$message = $module->AssignLuxtronik(0, json_encode(['SupplyTemperature' => 63001], JSON_THROW_ON_ERROR));
check(!isset($module->formUpdates['OutdoorTemperature']['value']) && !isset($module->formUpdates['SupplyTemperature']['value']), 'Manual assignment overwritten');
check($module->formUpdates['LuxtronikInstanceID']['value'] === 61000, 'Single Luxtronik instance not detected');
foreach (['OperatingStatusVariable' => 62000, 'RefluxTemperatureHeating1' => 62002,
          'ReturnTemperatureSetpointVariable' => 62003, 'TankTempWWUp' => 62005,
          'FanSpeed' => 62007, 'HeatingControlVariable' => 62008,
          'WarmWaterSetpointVariable' => 62009, 'HeatingTemperatureCorrectionVariable' => 62010] as $property => $expected) {
    check($module->formUpdates[$property]['value'] === $expected, 'Wrong Luxtronik Ident mapping: ' . $property);
}
check(!isset($module->formUpdates['ExpansionValveOpening']['value']) && !isset($module->formUpdates['HeaterRod1']['value']), 'Uncertain physical mapping guessed');
check([$writes, $actions, $module->properties] === $before, 'Assignment persisted configuration or operated a device');
check(str_contains($message, 'Übernehmen'), 'Saving instructions missing');

$objectIdents[61000]['Temperatur_Brauchwasser_Oben'] = 62006;
$variables[62006] = 49.0;
$module = createModule();
$module->AssignLuxtronik(0, '{}');
check($module->formUpdates['TankTempWWUp']['value'] === 62006, 'Specific tank sensor not preferred');
// Simulate pressing Apply: normal data, actions and subscriptions use the saved IDs.
foreach ($module->formUpdates as $property => $parameters) {
    if (array_key_exists('value', $parameters)) $module->properties[$property] = $parameters['value'];
}
$module->ApplyChanges();
$data = callPrivate($module, 'BuildVisualizationData');
check($data['returnTemperatureSetpoint']['value'] === 30.0 && $data['refluxTemperatureHeating']['value'] === 28.9, 'Imported return temperatures not used');
check($data['heatingPumpCoolingMode']['value'], 'Wrong operating-status Ident or decoding');
check(isset($module->messages[62000], $module->messages[62002], $module->messages[62003], $module->messages[62009]), 'Imported variables not subscribed');
$module->RequestAction('SetNumericControl', ['function' => 'warmWaterSetpoint', 'value' => 51.0]);
check(end($actions) === [62009, 51.0], 'Imported control did not use its normal action');
$module->formUpdates = [];
$module->AssignLuxtronik(61000, '{}');
foreach ($module->formUpdates as $property => $parameters) {
    if (array_key_exists('value', $parameters)) {
        check($module->properties[$property] === $parameters['value'], 'Repeated import changed an already correct assignment');
    }
}

// Read-only or legacy string values must not be imported as device controls.
$variableMetadata[62009] = [];
$variableMetadata[62010] = ['VariableType' => 3, 'VariableAction' => 61000];
$module = createModule();
$module->AssignLuxtronik(0, '{}');
check(!isset($module->formUpdates['WarmWaterSetpointVariable']['value']) && !isset($module->formUpdates['HeatingTemperatureCorrectionVariable']['value']), 'Unsupported control imported');
$objectIdents[61000]['Ruecklauf_Soll_Heizkreis'] = 62999;
$module = createModule();
$module->AssignLuxtronik(0, '{}');
check(!isset($module->formUpdates['ReturnTemperatureSetpointVariable']['value']), 'Deleted variable imported');

$singleForm = json_decode(createModule()->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
check(str_contains(json_encode($singleForm, JSON_THROW_ON_ERROR), 'Automatischer Vorschlag: Test instance 61000'), 'Single-instance proposal missing');
$sourcePicker = $singleForm['elements'][0]['items'][2];
check($sourcePicker['type'] === 'SelectInstance' && $sourcePicker['validModules'] === [$luxtronikID], 'Picker not limited to supported Luxtronik module');
$instances[61001] = $luxtronikID;
$multipleForm = json_decode(createModule()->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
check(str_contains(json_encode($multipleForm, JSON_THROW_ON_ERROR), 'Mehrere Luxtronik'), 'Ambiguous source not shown in form');
unset($instances[61001]);
$missingForm = createModule(['LuxtronikInstanceID' => 61999])->GetConfigurationForm();
check(str_contains(json_encode(json_decode($missingForm, true, 512, JSON_THROW_ON_ERROR), JSON_UNESCAPED_UNICODE), 'nicht verfügbar'), 'Deleted saved source not explained');

// Exercise the actual generated button script with unsaved form values.
function WP_AssignLuxtronik(int $id, int $source, string $formValues, bool $manual = false): string {
    check($id === $GLOBALS['assignmentModule']->InstanceID, 'Wrong target instance in button script');
    return $GLOBALS['assignmentModule']->AssignLuxtronik($source, $formValues, $manual);
}
$assignmentModule = createModule();
foreach ($assignmentModule->properties as $property => $value) $$property = $value;
$id = $assignmentModule->InstanceID;
$OutdoorTemperature = 63100; // Unsaved user choice must survive the button.
$buttonScript = $singleForm['elements'][0]['items'][3]['onClick'];
ob_start();
try { eval($buttonScript); } finally { $buttonMessage = ob_get_clean(); }
check(!isset($assignmentModule->formUpdates['OutdoorTemperature']['value']), 'Button overwrote unsaved manual input');
check(isset($assignmentModule->formUpdates['RefluxTemperatureHeating1']), 'Button did not populate matching Idents');
check(str_contains($buttonMessage, 'Übernehmen'), 'Button did not explain how to save');

// Live assignment report: correct Idents are locked; external and missing values stay editable.
function namedFormItems(array $elements): array {
    $items = [];
    foreach ($elements as $element) {
        if (isset($element['name'])) $items[$element['name']] = $element;
        if (isset($element['items'])) $items += namedFormItems($element['items']);
    }
    return $items;
}
function reportRow(array $rows, string $fieldCaption): array {
    foreach ($rows as $row) {
        if (str_ends_with($row['field'], ' · ' . $fieldCaption)) return $row;
    }
    throw new RuntimeException('Missing assignment report row: ' . $fieldCaption);
}
$objectIdents[61000]['Ruecklauf_Soll_Heizkreis'] = 62003;
$variableMetadata[62009] = ['VariableAction' => 61000];
$instances[61001] = $luxtronikID;
$statusProperties = [
    'LuxtronikInstanceID' => 61000,
    'OperatingStatusVariable' => 62000,
    'HeatingControlVariable' => 62008,
    'SupplyTemperature' => 62002, // A different Luxtronik Ident.
    'OutdoorTemperature' => 62060, // External module.
    'CompressorRunning' => 62050, // Other Luxtronik instance.
    'ReturnTemperatureSetpointVariable' => 62998, // Deleted variable.
    'AdditionalValue000' => 62060, // Manual field without an automatic mapping.
];
$assignmentModule = createModule($statusProperties);
$statusForm = json_decode($assignmentModule->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
$statusItems = namedFormItems($statusForm['elements']);
$overview = $statusItems['AssignmentOverview'];
check(count($overview['values']) === count($selectors), 'Assignment report does not cover every variable field');
check($overview['add'] === false && $overview['delete'] === false && $overview['loadValuesFromConfiguration'] === false, 'Report may edit or load persisted configuration');
foreach ($overview['columns'] as $column) check($column['save'] === false, 'Report column persists derived status');
check(!isset($assignmentModule->registered['AssignmentOverview']), 'Derived report registered as configuration');
$statusRow = reportRow($overview['values'], 'Betriebsstatus');
check($statusRow['status'] === 'Per Ident zugeordnet' && $statusRow['ident'] === 'Betriebszustand' && $statusRow['rowColor'] === '#eeeeee', 'Correct Ident not highlighted');
check(str_contains($statusRow['variable'], '#62000'), 'Variable ID missing from report');
check(reportRow($overview['values'], 'Außentemperatur')['status'] === 'Externe / manuelle Quelle', 'External source not distinguished');
check(reportRow($overview['values'], 'WP Vorlauf')['status'] === 'Luxtronik, manuell', 'Wrong Luxtronik Ident reported as automatic');
check(reportRow($overview['values'], 'Verdichter aktiv')['status'] === 'Andere Luxtronik-Instanz', 'Other source reported as automatic');
check(reportRow($overview['values'], 'Rücklauf Soll')['status'] === 'Ungültige Variablen-ID', 'Deleted assignment not distinguished');
$missingRow = reportRow($overview['values'], 'Niederdruck');
check($missingRow['status'] === 'Datenpunkt fehlt' && $missingRow['ident'] === 'Niederdruck', 'Missing Ident not named');
check($statusItems['OperatingStatusVariable']['enabled'] === false && $statusItems['HeatingControlVariable']['enabled'] === false, 'Correct Ident picker not locked');
foreach (['OutdoorTemperature', 'SupplyTemperature', 'CompressorRunning', 'ReturnTemperatureSetpointVariable', 'HeaterRod1'] as $property) {
    check($statusItems[$property]['enabled'] === true, 'Non-automatic picker locked: ' . $property);
}
check($statusItems['OperatingStatusVariable']['caption'] === 'Betriebsstatus', 'Technical integer wording retained');
check($statusItems['HeatingControlVariable']['caption'] === 'Heizen', 'Control label not simplified');
check($statusItems['OperatingStatusVariable']['name'] !== $statusItems['HeatingControlVariable']['name'], 'Operating state and controls conflated');

function WP_RefreshAssignmentStatus(int $id, int $source, string $formValues, bool $manual = false): void {
    check($id === $GLOBALS['assignmentModule']->InstanceID, 'Wrong target instance in status script');
    $GLOBALS['assignmentModule']->RefreshAssignmentStatus($source, $formValues, $manual);
}
foreach ($assignmentModule->properties as $property => $value) $$property = $value;
$before = [$writes, $actions, $assignmentModule->properties];
$OperatingStatusVariable = 62060; // A current, unsaved change to an external source.
ob_start();
try { eval($statusItems['OperatingStatusVariable']['onChange']); }
finally { $statusOutput = ob_get_clean(); }
check($statusOutput === '', 'Status change opened a popup');
$liveRows = json_decode($assignmentModule->formUpdates['AssignmentOverview']['values'], true, 512, JSON_THROW_ON_ERROR);
check(reportRow($liveRows, 'Betriebsstatus')['status'] === 'Externe / manuelle Quelle', 'Report ignored unsaved selection');
check($assignmentModule->formUpdates['OperatingStatusVariable']['enabled'] === true, 'Changed external source remained locked');
check([$writes, $actions, $assignmentModule->properties] === $before, 'Status refresh persisted values or triggered actions');
foreach ($assignmentModule->formUpdates as $parameters) check(!array_key_exists('value', $parameters), 'Status refresh changed a selection');
$ManualConfiguration = true;
eval($statusItems['ManualConfiguration']['onChange']);
foreach ($selectors as $property) check($assignmentModule->formUpdates[$property]['enabled'] === true, 'Manual mode did not unlock picker: ' . $property);
check($assignmentModule->formUpdates['LuxtronikAssignButton']['enabled'] === false, 'Import button available in manual mode');
$beforeUpdates = $assignmentModule->formUpdates;
check(str_contains($assignmentModule->AssignLuxtronik(61000, '{}', true), 'Manuelle Konfiguration'), 'Manual import not explained');
check($assignmentModule->formUpdates === $beforeUpdates, 'Import changed form in manual mode');
$manualForm = namedFormItems(json_decode(createModule($statusProperties + ['ManualConfiguration' => true])->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR)['elements']);
check($manualForm['OperatingStatusVariable']['enabled'] === true && $manualForm['LuxtronikAssignButton']['enabled'] === false, 'Saved manual mode not respected');

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
    preg_match_all('~<script[^>]*>(.*?)</script>~s', $html, $scripts);
    foreach ($scripts[1] as $index => $js) file_put_contents($artifactDir . '/script-' . $index . '.js', $js);
}
echo "PASS: $checks checks; 88 retained main properties; " . count($selectors) . " selectable variables\n";
