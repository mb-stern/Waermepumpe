"""Optional browser regression: PHP, Playwright and Chromium are prerequisites.

Run from the repository: python3 tests/browser.py
Only local fixtures are served; no real Symcon or device actions are used.
"""
import functools
import http.server
import json
import os
from pathlib import Path
import shutil
import subprocess
import tempfile
import threading

from playwright.sync_api import sync_playwright

repository = Path(__file__).resolve().parent.parent
chromium = shutil.which("chromium") or shutil.which("chromium-browser")
if not chromium:
    raise RuntimeError("Chromium is required for this optional browser check")

class QuietHandler(http.server.SimpleHTTPRequestHandler):
    def log_message(self, *args):
        pass

with tempfile.TemporaryDirectory(prefix="wp-browser-") as artifacts:
    subprocess.run(["php", str(repository / "tests/regression.php")],
                   cwd=repository, check=True,
                   env={**os.environ, "WP_TEST_ARTIFACT_DIR": artifacts})
    handler = functools.partial(QuietHandler, directory=artifacts)
    server = http.server.ThreadingHTTPServer(("127.0.0.1", 0), handler)
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()
    try:
        with sync_playwright() as p:
            browser=p.chromium.launch(executable_path=chromium,headless=True,args=['--no-sandbox'])
            for width in [1200,390]:
                page=browser.new_page(viewport={'width':width,'height':850})
                errors=[]
                page.on('pageerror',lambda e: errors.append(str(e)))
                page.goto(f'http://127.0.0.1:{server.server_port}/configured.html')
                page.wait_for_timeout(250)
                assert page.locator('#wp-error').text_content()==''
                result=page.evaluate('''() => {
                    const box=document.querySelector('#info > rect');
                    const text=document.querySelector('#textReturnTemperatureValue');
                    const bounds=text.getBBox();
                    return {width:box.getAttribute('width'),actual:document.querySelector('#textReturnTemperatureActualValue').textContent,target:document.querySelector('#textReturnTemperatureSetpointValue').textContent,x:bounds.x,right:bounds.x+bounds.width};
                }''')
                assert result['width']=='650',result
                assert result['actual']=='28.9 °C' and result['target']==' / Soll 30.0 °C',result
                assert result['x']==462 and result['right']<=650.1,result
                payload=page.evaluate('''() => {
                    const script=[...document.scripts].find(s=>s.textContent.trim().startsWith('SymconHeatPump.init('));
                    return JSON.parse(script.textContent.trim().slice('SymconHeatPump.init('.length,-2));
                }''')
                target_key=payload['config']['returnTemperatureSetpoint']
                actual_key=payload['config']['refluxTemperatureHeating']
                # Drive real numeric popups and capture only simulated SDK actions.
                page.evaluate('''() => {
                    window.testActions=[];
                    window.requestAction=(ident,value)=>window.testActions.push({ident,payload:JSON.parse(value)});
                }''')
                for function, selector in [
                    ('warmWaterSetpoint', '#gSymconWarmWaterSetpoint'),
                    ('heatingTemperatureCorrection', '#gSymconHeatingCorrection')
                ]:
                    for minimum, maximum, step, value, expected in [
                        (20,80,0.3,80,80),
                        (20.1,80,0.3,80,79.8),
                        (20.1,80,0.3,-100,20.1),
                        (-10,10,0.3,10,9.8),
                        (-10,10,0.3,-11,-10),
                        (20,80,0.5,50.5,50.5),
                        (0,10,1,4,4),
                    ]:
                        controls=json.loads(json.dumps(payload['controls']))
                        controls[function].update(min=minimum,max=maximum,step=step,currentValue=value)
                        page.evaluate('(controls)=>window.handleMessage({type:"update",controls})',controls)
                        page.locator(selector).dispatch_event('click')
                        field=page.locator('#wp-mode-menu input')
                        field.fill(str(value))
                        page.get_by_role('button',name='Übernehmen',exact=True).dispatch_event('click')
                        action=page.evaluate('window.testActions.at(-1)')
                        assert action=={'ident':'SetNumericControl','payload':{'function':function,'value':expected}},action
                        assert minimum<=action['payload']['value']<=maximum,action
                        # An empty correction must not turn into zero or a boundary.
                        page.locator(selector).dispatch_event('click')
                        field.fill('')
                        before=page.evaluate('window.testActions.length')
                        page.get_by_role('button',name='Übernehmen',exact=True).dispatch_event('click')
                        assert page.evaluate('window.testActions.length')==before
                        page.keyboard.press('Escape')
                page.evaluate('(controls)=>window.handleMessage({type:"update",controls})',payload['controls'])
                # Actual running variables drive overlays; updates keep their identity.
                assert page.locator('.wp-live-flow').count() > 0
                page.evaluate("document.querySelector('.wp-live-flow').dataset.testIdentity='retained'")
                page.evaluate('(p) => window.handleMessage({type:"update",data:p.data,controls:p.controls})',payload)
                assert page.locator('.wp-live-flow[data-test-identity="retained"]').count() == 1
                for theme in ['light', 'dark']:
                    page.evaluate("""(theme) => {
                        const dark=theme==='dark';
                        document.documentElement.style.setProperty('--content-color',dark?'#e6edf4':'#1f2937');
                        document.documentElement.style.setProperty('--card-background-color',dark?'#18222f':'#f8fafc');
                        document.documentElement.style.setProperty('--divider-color',dark?'#354255':'#d8e1ea');
                        document.documentElement.style.setProperty('background',dark?'#111923':'#f0f4f8','important');
                        document.body.style.setProperty('background',dark?'#111923':'#f0f4f8','important');
                    }""",theme)
                    page.evaluate('(p) => window.handleMessage({type:"update",data:p.data,controls:p.controls})',payload)
                    page.wait_for_timeout(250)  # Allow the theme transition to finish.
                    screenshot_dir=os.environ.get('WP_BROWSER_SCREENSHOT_DIR')
                    if screenshot_dir:
                        Path(screenshot_dir).mkdir(parents=True,exist_ok=True)
                        page.screenshot(path=str(Path(screenshot_dir)/f'{theme}-{width}.png'))
                    assert page.locator('#wp-error').text_content()==''
                page.emulate_media(reduced_motion='reduce')
                assert page.locator('.wp-live-flow').first.evaluate("e=>getComputedStyle(e).animationName")=='none'
                idle=json.loads(json.dumps(payload))
                for key in ['compressorRunning','storageChargingPumpRunning',
                            'heatingCircuitPumpRunning','heatingCircuitPumpRunning2',
                            'heatingCircuitPumpRunning3','thermalSolarPumpSpeed']:
                    entity=idle['config'].get(key)
                    if entity in idle['data']: idle['data'][entity]['value']=0
                page.evaluate('(p) => window.handleMessage({type:"update",data:p.data,controls:p.controls})',idle)
                assert page.locator('.wp-live-flow').count()==0
                payload['data'][target_key]['formatted']='-123.45 °C langer Profiltext'
                page.evaluate('(p) => window.handleMessage({type:"update",data:p.data,controls:p.controls})',payload)
                assert page.evaluate('document.querySelector("#textReturnTemperatureValue").getBBox().x+document.querySelector("#textReturnTemperatureValue").getBBox().width')<=650.1
                payload['data'].pop(target_key)
                page.evaluate('(p) => window.handleMessage({type:"update",data:p.data,controls:p.controls})',payload)
                assert page.locator('#textReturnTemperatureSetpointValue').text_content()==''
                payload['data'].pop(actual_key)
                page.evaluate('(p) => window.handleMessage({type:"update",data:p.data,controls:p.controls})',payload)
                assert page.locator('#textReturnTemperatureValue').text_content()==''
                assert not errors,errors
                print(json.dumps({'viewport':width,**result,'updates':'PASS'}))
                page.close()
            browser.close()
    finally:
        server.shutdown()
        server.server_close()
        thread.join()
