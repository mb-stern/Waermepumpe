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
