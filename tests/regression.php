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
// Outdoor-temperature Ident rechecked against mb-stern/Luxtronik development 874688b.
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
    'Verdichterdrehzahl' => 62012,
    'Kondensationstemperatur' => 62013,
];
foreach ([62000 => 7, 62001 => 37.5, 62002 => 28.9, 62003 => 30.0, 62004 => 10.0,
          62005 => 48.0, 62007 => 250, 62008 => 0, 62009 => 50.0, 62010 => -1.0, 62011 => true, 62012 => 3000, 62013 => 42.5] as $variableID => $value) {
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

// Reproduce an existing instance: source and other links saved, these fields still zero.
$liveIdentProperties = ['LuxtronikInstanceID' => 61000, 'OperatingStatusVariable' => 62000];
$liveIdentModule = createModule($liveIdentProperties);
$before = [$writes, $actions, $liveIdentModule->properties];
$liveIdentModule->ApplyChanges();
$liveIdentData = callPrivate($liveIdentModule, 'BuildVisualizationData');
foreach (['outdoorTemperature' => 62004, 'compressorValue' => 62012, 'condenserTemperature' => 62013] as $key => $variableID) {
    check(($liveIdentData[$key]['variableId'] ?? 0) === $variableID, 'Empty saved field not resolved directly by Ident: ' . $key);
    check(isset($liveIdentModule->messages[$variableID]), 'Live Ident variable not subscribed: ' . $key);
    check(callPrivate($liveIdentModule, 'BuildCardConfig')[$key] === $key, 'Live Ident missing from card config: ' . $key);
}
check([$writes, $actions, $liveIdentModule->properties] === $before, 'Live Ident resolution modified stored assignments or operated a device');
$manualLive = createModule($liveIdentProperties + ['ManualConfiguration' => true]);
$manualData = callPrivate($manualLive, 'BuildVisualizationData');
check(!isset($manualData['compressorValue'], $manualData['condenserTemperature'], $manualData['outdoorTemperature']), 'Manual mode resolved unassigned Idents');
$ambiguousLive = createModule();
check(!isset(callPrivate($ambiguousLive, 'BuildVisualizationData')['compressorValue']), 'Live Ident chose an arbitrary source');
$otherLive = createModule(['LuxtronikInstanceID' => 61001]);
$otherData = callPrivate($otherLive, 'BuildVisualizationData');
check($otherData['outdoorTemperature']['variableId'] === 62050 && !isset($otherData['compressorValue'], $otherData['condenserTemperature']), 'Live Ident leaked from a different source');
$missingLive = createModule(['LuxtronikInstanceID' => 61999]);
check(!isset(callPrivate($missingLive, 'BuildVisualizationData')['compressorValue']), 'Live Ident ignored an unavailable selected source');

$instances[61234] = $luxtronikID;
$objectIdents[61234] = ['Verdichterdrehzahl' => 62070, 'Kondensationstemperatur' => 62071];
$variables[62070] = 1500;
$variables[62071] = -3.0;
$liveIdentModule->properties['LuxtronikInstanceID'] = 61234;
$liveIdentModule->ApplyChanges();
$liveIdentData = callPrivate($liveIdentModule, 'BuildVisualizationData');
check($liveIdentData['compressorValue']['value'] === 1500 && $liveIdentData['condenserTemperature']['value'] === -3.0, 'Live binding did not follow the selected instance');
check(isset($liveIdentModule->messages[62070], $liveIdentModule->messages[62071]) && !isset($liveIdentModule->messages[62012], $liveIdentModule->messages[62013]), 'Source switch retained old subscriptions');
$objectIdents[61234]['Verdichterdrehzahl'] = 62072;
$variables[62072] = 2100;
$liveIdentModule->ApplyChanges();
check(callPrivate($liveIdentModule, 'BuildVisualizationData')['compressorValue']['variableId'] === 62072 && isset($liveIdentModule->messages[62072]) && !isset($liveIdentModule->messages[62070]), 'Recreated Ident did not resolve to its new ID');
$liveIdentModule->properties['ManualConfiguration'] = true;
$liveIdentModule->properties['CompressorValue'] = 62012;
$liveIdentModule->properties['CondenserTemperature'] = 62013;
$liveIdentModule->ApplyChanges();
$manualLiveData = callPrivate($liveIdentModule, 'BuildVisualizationData');
check($manualLiveData['compressorValue']['variableId'] === 62012 && $manualLiveData['condenserTemperature']['variableId'] === 62013, 'Manual mode ignored explicitly selected variables');
unset($instances[61234], $objectIdents[61234]);

// Prior working Idents and the two reported fields share resolution, subscriptions and controls.
$commonModule = createModule(['LuxtronikInstanceID' => 61000, 'AdditionalValue000' => 62050]);
$before = [$writes, $actions, $commonModule->properties];
$commonModule->ApplyChanges();
$commonData = callPrivate($commonModule, 'BuildVisualizationData');
foreach (['operatingStatus' => 62000, 'supplyTemperature' => 62001,
          'refluxTemperatureHeating' => 62002, 'returnTemperatureSetpoint' => 62003,
          'outdoorTemperature' => 62004, 'tankTempWWUp' => 62005,
          'fanSpeed' => 62007, 'compressorRunning' => 62011,
          'compressorValue' => 62012, 'condenserTemperature' => 62013] as $key => $expected) {
    check($commonData[$key]['variableId'] === $expected, 'Inconsistent automatic Ident resolution: ' . $key);
    check(isset($commonModule->messages[$expected]), 'Inconsistent automatic subscription: ' . $key);
}
$commonControls = callPrivate($commonModule, 'BuildControlData');
check($commonControls['heating']['variableId'] === 62008 && $commonControls['warmWaterSetpoint']['variableId'] === 62009, 'Controls use different resolution than measurements');
check($commonData['heatingPumpCoolingMode']['value'] === true, 'Operating status decoding bypassed the common resolver');
check($commonData['additionalValue000']['variableId'] === 62050, 'Unmapped external field changed');
check([$writes, $actions, $commonModule->properties] === $before, 'Automatic resolution modified configuration or operated a device');
$commonModule->RequestAction('SetNumericControl', ['function' => 'warmWaterSetpoint', 'value' => 52.0]);
check(end($actions) === [62009, 52.0], 'Action target differs from displayed automatic control');
$commonModule->properties['ManualConfiguration'] = true;
$commonModule->properties['SupplyTemperature'] = 62050;
$commonModule->properties['CompressorValue'] = 62050;
$commonModule->ApplyChanges();
$commonManualData = callPrivate($commonModule, 'BuildVisualizationData');
check($commonManualData['supplyTemperature']['variableId'] === 62050 && $commonManualData['compressorValue']['variableId'] === 62050, 'Manual selection not respected consistently');

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
check($refresh->formUpdates['WarmWaterSetpointVariable']['value'] === 62009, 'Available control Ident did not follow the shared assignment path');
check([$writes, $actions, $refresh->properties] === $before, 'Refresh persisted settings or triggered actions');
$unsavedExternal = createModule(['OutdoorTemperature' => 62004, 'SupplyTemperature' => 62001]);
$unsavedExternal->AssignLuxtronik(61000, json_encode(['OutdoorTemperature' => 62060, 'SupplyTemperature' => 62060], JSON_THROW_ON_ERROR));
check($unsavedExternal->formUpdates['OutdoorTemperature']['value'] === 62004, 'External outdoor sensor not replaced by the heat pump Ident');
check($unsavedExternal->formUpdates['SupplyTemperature']['value'] === 62001, 'Available supply Ident did not follow the shared assignment path');

// An unavailable outdoor-temperature Ident must not discard a working sensor.
foreach ([0, 62998] as $unavailableID) {
    if ($unavailableID === 0) unset($objectIdents[61000]['Aussentemperatur']);
    else $objectIdents[61000]['Aussentemperatur'] = $unavailableID;
    $externalOutdoor = createModule(['OutdoorTemperature' => 62060]);
    $externalOutdoor->AssignLuxtronik(61000, '{}');
    check(!isset($externalOutdoor->formUpdates['OutdoorTemperature']['value']), 'Unavailable outdoor Ident replaced the external sensor');
}
$objectIdents[61000]['Aussentemperatur'] = 62004;
$deletedOutdoor = createModule(['OutdoorTemperature' => 62998]);
$deletedOutdoor->AssignLuxtronik(61000, '{}');
check($deletedOutdoor->formUpdates['OutdoorTemperature']['value'] === 62004, 'Available Ident did not replace unresolved saved ID');
$manualOutdoor = createModule(['OutdoorTemperature' => 62060, 'ManualConfiguration' => true]);
$before = [$writes, $actions, $manualOutdoor->properties];
$manualOutdoor->AssignLuxtronik(61000, '{}', true);
check($manualOutdoor->formUpdates === [] && [$writes, $actions, $manualOutdoor->properties] === $before, 'Manual outdoor sensor overwritten or device operated');
// All available Idents use the same automatic source precedence.
foreach (['CompressorValue' => 'Verdichterdrehzahl', 'CondenserTemperature' => 'Kondensationstemperatur'] as $property => $ident) {
    $expected = $objectIdents[61000][$ident];
    $reading = createModule([$property => 62060, 'SupplyTemperature' => 62060]);
    $before = [$writes, $actions, $reading->properties];
    $reading->AssignLuxtronik(61000, '{}');
    check($reading->formUpdates[$property]['value'] === $expected, 'Heat pump Ident did not replace external reading: ' . $property);
    check($reading->formUpdates[$property]['enabled'] === false, 'Imported heat pump reading not locked: ' . $property);
    check($reading->formUpdates['SupplyTemperature']['value'] === 62001, 'Available supply Ident treated differently from compressor/condensation');
    check([$writes, $actions, $reading->properties] === $before, 'Reading assignment persisted or operated a device');
    foreach ([0, 62998] as $unavailableID) {
        if ($unavailableID === 0) unset($objectIdents[61000][$ident]);
        else $objectIdents[61000][$ident] = $unavailableID;
        $reading = createModule([$property => 62060]);
        $reading->AssignLuxtronik(61000, '{}');
        check(!isset($reading->formUpdates[$property]['value']), 'Missing heat pump Ident discarded the external reading: ' . $property);
    }
    $objectIdents[61000][$ident] = $expected;
    $reading = createModule([$property => 62060, 'ManualConfiguration' => true]);
    $reading->AssignLuxtronik(61000, '{}', true);
    check($reading->formUpdates === [], 'Manual reading overwritten: ' . $property);
}
$unsavedLuxtronik = createModule(['OutdoorTemperature' => 62060]);
$unsavedLuxtronik->AssignLuxtronik(61000, json_encode(['OutdoorTemperature' => 62050], JSON_THROW_ON_ERROR));
check($unsavedLuxtronik->formUpdates['OutdoorTemperature']['value'] === 62004, 'Current form source not used for refresh');
$sourceChange = createModule(['OutdoorTemperature' => 62004, 'OperatingStatusVariable' => 62000]);
$sourceChange->AssignLuxtronik(61001, '{}');
check($sourceChange->formUpdates['OutdoorTemperature']['value'] === 62050, 'Selected source not used when changing units');
check($sourceChange->formUpdates['OperatingStatusVariable']['value'] === 0, 'Missing data point retained from the old unit');

unset($instances[61001]);
$variables[63000] = 18.5; // Free sensor without a parent instance.
$module = createModule(['OutdoorTemperature' => 63000]);
$before = [$writes, $actions, $module->properties];
// Include a manual edit that has not yet been saved in the instance.
$message = $module->AssignLuxtronik(0, json_encode(['SupplyTemperature' => 63001], JSON_THROW_ON_ERROR));
check($module->formUpdates['OutdoorTemperature']['value'] === 62004, 'Free outdoor sensor not replaced by the heat pump Ident');
check($module->formUpdates['SupplyTemperature']['value'] === 62001, 'Available supply Ident left unresolved');
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
$module = createModule(['OutdoorTemperature' => 62060, 'CompressorValue' => 62060, 'CondenserTemperature' => 62060]);
$module->AssignLuxtronik(0, '{}');
check($module->formUpdates['TankTempWWUp']['value'] === 62006, 'Specific tank sensor not preferred');
// Simulate pressing Apply: normal data, actions and subscriptions use the saved IDs.
foreach ($module->formUpdates as $property => $parameters) {
    if (array_key_exists('value', $parameters)) $module->properties[$property] = $parameters['value'];
}
$module->ApplyChanges();
$data = callPrivate($module, 'BuildVisualizationData');
check($data['outdoorTemperature']['value'] === 10.0 && isset($module->messages[62004]) && !isset($module->messages[62060]), 'Imported outdoor temperature not displayed or subscribed to the heat pump sensor');
check($data['returnTemperatureSetpoint']['value'] === 30.0 && $data['refluxTemperatureHeating']['value'] === 28.9, 'Imported return temperatures not used');
check($data['compressorValue']['value'] === 3000 && $data['condenserTemperature']['value'] === 42.5, 'Imported compressor speed or condensation temperature not displayed');
check(isset($module->messages[62012], $module->messages[62013]), 'Imported compressor/condensation readings not subscribed');
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
$variables[63100] = 21.0;
$OutdoorTemperature = 63100; // Outdoor temperature explicitly prefers the heat pump.
$SupplyTemperature = 63100; // A matching Ident uses the same automatic source.
$buttonScript = $singleForm['elements'][0]['items'][3]['onClick'];
ob_start();
try { eval($buttonScript); } finally { $buttonMessage = ob_get_clean(); }
check($assignmentModule->formUpdates['OutdoorTemperature']['value'] === 62004, 'Button did not replace the external outdoor sensor');
check($assignmentModule->formUpdates['OutdoorTemperature']['enabled'] === false, 'Imported outdoor Ident picker not locked');
check($assignmentModule->formUpdates['SupplyTemperature']['value'] === 62001, 'Button used different rules for supply temperature');
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
    'OutdoorTemperature' => 62060, // Live Ident takes precedence in automatic mode.
    'AmbientTemperatureActual' => 62060, // External module without a live override.
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
// The form must display the resolved source, rather than a disabled picker saying None.
$liveFormModule = createModule(['LuxtronikInstanceID' => 61000]);
$liveForm = namedFormItems(json_decode($liveFormModule->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR)['elements']);
foreach (['OutdoorTemperature' => ['Außentemperatur', 62004],
          'CompressorValue' => ['Verdichterdrehzahl', 62012],
          'CondenserTemperature' => ['Kondensationstemperatur', 62013],
          'SupplyTemperature' => ['WP Vorlauf', 62001],
          'OperatingStatusVariable' => ['Betriebsstatus', 62000],
          'HeatingControlVariable' => ['Heizen', 62008],
          'WarmWaterSetpointVariable' => ['Warmwasser-Solltemperatur', 62009]] as $property => [$caption, $variableID]) {
    check($liveForm[$property]['visible'] === false && $liveForm[$property]['enabled'] === false, 'Live Ident still shown as an empty picker: ' . $property);
    check($liveForm['Automatic' . $property]['visible'] === true && str_contains($liveForm['Automatic' . $property]['caption'], '#' . $variableID), 'Resolved source missing from form: ' . $property);
    check(reportRow($liveForm['AssignmentOverview']['values'], $caption)['status'] === 'Per Ident zugeordnet', 'Empty saved field incorrectly reported unassigned: ' . $property);
    check($liveFormModule->properties[$property] === 0 && !isset($liveFormModule->registered['Automatic' . $property]), 'Form overwrote saved configuration for derived source');
}
$liveFormModule->RefreshAssignmentStatus(61000, '{}', true);
foreach (['OutdoorTemperature', 'CompressorValue', 'CondenserTemperature'] as $property) {
    check($liveFormModule->formUpdates[$property]['visible'] === true && $liveFormModule->formUpdates[$property]['enabled'] === true, 'Manual toggle did not restore original picker');
    check($liveFormModule->formUpdates['Automatic' . $property]['visible'] === false, 'Manual toggle retained automatic source label');
}
$liveFormModule->RefreshAssignmentStatus(61001, '{}');
check($liveFormModule->formUpdates['CompressorValue']['visible'] === true && $liveFormModule->formUpdates['AutomaticCompressorValue']['visible'] === false, 'Source without speed Ident retained previous live display');
check(str_contains($liveFormModule->formUpdates['AutomaticOutdoorTemperature']['caption'], '#62050'), 'Unsaved source selection did not update live source display');

$statusRow = reportRow($overview['values'], 'Betriebsstatus');
check($statusRow['status'] === 'Per Ident zugeordnet' && $statusRow['ident'] === 'Betriebszustand' && $statusRow['rowColor'] === '#eeeeee', 'Correct Ident not highlighted');
check(str_contains($statusRow['variable'], '#62000'), 'Variable ID missing from report');
check(reportRow($overview['values'], 'Außentemperatur')['status'] === 'Per Ident zugeordnet', 'Live outdoor Ident not reflected in report');
check(reportRow($overview['values'], 'Raumtemperatur Ist (Fallback)')['status'] === 'Externe / manuelle Quelle', 'External source not distinguished');
check(reportRow($overview['values'], 'WP Vorlauf')['status'] === 'Per Ident zugeordnet', 'Supply temperature not resolved using the common Ident path');
check(reportRow($overview['values'], 'Verdichter aktiv')['status'] === 'Per Ident zugeordnet', 'Compressor status not resolved using the common Ident path');
check(reportRow($overview['values'], 'Rücklauf Soll')['status'] === 'Per Ident zugeordnet', 'Available return setpoint Ident not resolved');
$missingRow = reportRow($overview['values'], 'Niederdruck');
check($missingRow['status'] === 'Datenpunkt fehlt' && $missingRow['ident'] === 'Niederdruck', 'Missing Ident not named');
check($statusItems['OperatingStatusVariable']['enabled'] === false && $statusItems['HeatingControlVariable']['enabled'] === false, 'Correct Ident picker not locked');
foreach (['AmbientTemperatureActual', 'AdditionalValue000', 'HeaterRod1'] as $property) {
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
$ManualConfiguration = true; // External override is explicit manual mode.
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

// Read-only diagnostics expose exact running Idents, IDs and source without device values.
$diagnosticModule = createModule(['LuxtronikInstanceID' => 61000]);
$before = [$writes, $actions, $diagnosticModule->properties, $diagnosticModule->formUpdates];
$objectIdents[61000]['NotAVariable'] = 62998;
$diagnosis = json_decode($diagnosticModule->DiagnoseLuxtronik(61000), true, 512, JSON_THROW_ON_ERROR);
check($diagnosis['erkannteQuelle'] === 61000 && $diagnosis['zuordnungsstand'] === 'einheitlich-2026-10-20', 'Diagnostic source/build marker missing');
foreach (['CompressorValue' => 62012, 'CondenserTemperature' => 62013, 'SupplyTemperature' => 62001, 'HeatingControlVariable' => 62008] as $property => $expected) {
    check($diagnosis['zuordnungen'][$property]['gefunden'] === $expected && $diagnosis['zuordnungen'][$property]['verwendet'] === $expected, 'Diagnostic resolver mismatch: ' . $property);
    check($diagnosis['zuordnungen'][$property]['gespeichert'] === 0, 'Diagnostics concealed an empty saved assignment');
}
check(!in_array(62998, array_column($diagnosis['vorhandeneVariablen'], 'id'), true), 'Non-variable included in diagnostic variable list');
check(in_array('Verdichterdrehzahl', array_column($diagnosis['vorhandeneVariablen'], 'ident'), true), 'Actual child Idents missing from diagnostic output');
check([$writes, $actions, $diagnosticModule->properties, $diagnosticModule->formUpdates] === $before, 'Diagnostics changed state or operated a device');
$otherDiagnosis = json_decode($diagnosticModule->DiagnoseLuxtronik(61001), true, 512, JSON_THROW_ON_ERROR);
check($otherDiagnosis['erkannteQuelle'] === 61001 && $otherDiagnosis['zuordnungen']['OutdoorTemperature']['gefunden'] === 62050 && $otherDiagnosis['zuordnungen']['CompressorValue']['gefunden'] === 0, 'Diagnostic source was hard-coded or leaked');
check($otherDiagnosis['gespeicherteQuelle'] === 61000 && $otherDiagnosis['zuordnungen']['OutdoorTemperature']['verwendet'] === 62004, 'Diagnostics confused an unsaved source selection with the actual runtime source');
$ambiguousDiagnosis = json_decode($diagnosticModule->DiagnoseLuxtronik(0), true, 512, JSON_THROW_ON_ERROR);
check($ambiguousDiagnosis['erkannteQuelle'] === 0 && $ambiguousDiagnosis['vorhandeneVariablen'] === [], 'Diagnostics chose arbitrary source');
$invalidDiagnosis = json_decode($diagnosticModule->DiagnoseLuxtronik(61002), true, 512, JSON_THROW_ON_ERROR);
check($invalidDiagnosis['erkannteQuelle'] === 0 && $invalidDiagnosis['vorhandeneVariablen'] === [], 'Diagnostics read a foreign module');
unset($objectIdents[61000]['NotAVariable']);
function WP_DiagnoseLuxtronik(int $id, int $source): string {
    check($id === $GLOBALS['diagnosticModule']->InstanceID, 'Wrong diagnostic target');
    return $GLOBALS['diagnosticModule']->DiagnoseLuxtronik($source);
}
$diagnosticFields = namedFormItems(json_decode($diagnosticModule->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR)['elements']);
$id = $diagnosticModule->InstanceID;
$LuxtronikInstanceID = 61000;
ob_start();
try { eval($diagnosticFields['LuxtronikDiagnoseButton']['onClick']); } finally { $diagnosticOutput = ob_get_clean(); }
check(json_decode($diagnosticOutput, true, 512, JSON_THROW_ON_ERROR)['erkannteQuelle'] === 61000, 'Actual diagnostic button did not expose the selected source');

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
check(callPrivate(createModule(), 'BuildCardConfig')['luxtronikWebInterfaceURL'] === 'http://192.0.2.10/Webserver/index.html', 'Single module not detected for web interface');
foreach ([
    ['luxtronik.fritz.box', 'http://luxtronik.fritz.box/Webserver/index.html'],
    ['2001:db8::10', 'http://[2001:db8::10]/Webserver/index.html'],
    ['', ''], ['0.0.0.0', ''], ['::', ''], ['host/path', ''], ['javascript:invalid', ''],
] as [$address, $expected]) {
    $instanceProperties[61000]['IPAddress'] = $address;
    check(callPrivate(createModule(), 'BuildCardConfig')['luxtronikWebInterfaceURL'] === $expected, 'Invalid web address derivation');
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
    preg_match_all('~<script[^>]*>(.*?)</script>~s', $html, $scripts);
    foreach ($scripts[1] as $index => $js) file_put_contents($artifactDir . '/script-' . $index . '.js', $js);
}
echo "PASS: $checks checks; 88 retained main properties; " . count($selectors) . " selectable variables\n";
