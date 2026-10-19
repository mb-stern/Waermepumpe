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
$selectors = [];
$walk = function (array $item) use (&$walk, &$fields, &$selectors, $module): void {
    if (isset($item['name'])) {
        check(!isset($fields[$item['name']]), 'Duplicate field: ' . $item['name']);
        check(isset($module->registered[$item['name']]), 'Unregistered field: ' . $item['name']);
        $fields[$item['name']] = true;
        if ($item['type'] === 'SelectVariable') $selectors[] = $item['name'];
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
