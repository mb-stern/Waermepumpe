"""Synthetic LCD frames and a local protocol peer; no controller is contacted."""
import base64
import re


class LCD:
    def __init__(self):
        self.pixels = [[0] * 160 for _ in range(64)]

    def rect(self, x, y, width, height, value=1):
        for yy in range(y, y + height):
            for xx in range(x, x + width):
                self.pixels[yy][xx] = value

    def text(self, text, x, y, value=1):
        # Stable 5x7 test glyphs, independently drawn rather than recognized by OCR.
        for char in text:
            for yy in range(7):
                for xx in range(5):
                    bit = yy in [0, 6] or xx in [0, 4] or ((ord(char) >> ((xx + yy) % 7)) & 1)
                    if bit and y + yy < 64 and x + xx < 160:
                        self.pixels[y + yy][x + xx] = value
            x += 6

    def arrow(self, side, selected=False, asymmetric=False):
        x, y, width, height = (0 if side == 'left' else 145), 49, 15, 15
        self.rect(x, y, width, height, int(selected))
        self.rect(x, y, width, 1)
        self.rect(x, y + height - 1, width, 1)
        self.rect(x, y, 1, height)
        self.rect(x + width - 1, y, 1, height)
        rows = ['0000001000','0000001100','0000001110','1111111111',
                '1111111111','1111111111','0000001110','0000001100','0000001000']
        if asymmetric:
            rows[6:] = ['0000011110','0000011100','0000011000']
        for yy, row in enumerate(rows):
            for xx, bit in enumerate(row if side == 'right' else row[::-1]):
                if bit == '1':
                    self.pixels[y + 3 + yy][x + 2 + xx] = 0 if selected else 1

    def packed(self):
        return base64.b64encode(bytes(
            sum(self.pixels[row * 8 + bit][column] << bit for bit in range(8))
            for row in range(8) for column in range(160))).decode()


def menu_frame(selected=0, duplicate=False, pitch=9, origin_x=18, heading='SERVICE'):
    lcd = LCD()
    lcd.text(heading, 20, 0)
    lcd.rect(15, 9, 1, 36)
    lcd.arrow('left', selected == -1)
    labels = ['INFO','SETTINGS','LANGUAGE','DATE','PROGRAM','CONFIG']
    if duplicate:
        labels[1] = labels[0]
    for index, text in enumerate(labels):
        y = 9 + index * pitch
        if index == selected and y + pitch <= 64:
            lcd.rect(origin_x, y, 159 - origin_x, pitch)
        lcd.text(text, origin_x + 2, y + 1, 0 if index == selected else 1)
    return lcd.packed()


def icons_frame(selected=0):
    lcd = LCD()
    lcd.text('STATUS', 42, 0)
    lcd.text('NO REQUEST', 34, 18)
    lcd.text('MENU', 20, 56)
    lcd.arrow('left', selected == 0)
    for index in range(4):
        x, y = 22 + index * 23, 35
        width = 6 if index == 0 else 12
        if selected == index + 1:
            lcd.rect(x - 1, y - 1, width + 2, 17)
        for yy in range(15):
            for xx in range(width):
                bit = ((yy in [0, 1] and xx in [2, 3]) or (3 <= yy <= 13 and xx == 3) or yy == 14) if index == 0 else (
                    xx == 5 or yy == 7 or (xx + yy * (index + 1)) % (7 + index) == 0)
                if bit:
                    lcd.pixels[y + yy][x + xx] = 0 if selected == index + 1 else 1
    return lcd.packed()


def check_click_navigation(browser, base_url, fixture_type, width):
    def menu(selected=0, heading='SERVICE'):
        return menu_frame(selected,pitch=10,origin_x=19,heading=heading)

    page = browser.new_page(viewport={'width': width, 'height': 850})
    peer = fixture_type()
    peer.image = menu()
    state = {'mode': 'menu', 'selected': 0, 'opened': [], 'ignore': False, 'wrong': False,
             'blink_steps': False, 'defer_open': False}

    def move(message, route, connection):
        value = int(message.split(';')[1])
        if state['ignore']:
            return
        if value in [0, 1]:
            delta = 1 if value == 0 else -1
            if state['wrong']:
                delta *= 2
            low, high = (-1, 5) if state['mode'] == 'menu' else (0, 4)
            state['selected'] = max(low, min(high, state['selected'] + delta))
        elif value == 2:
            state['opened'].append(state['selected'])
            # Opening changes the display; it must never activate the next screen too.
            if not state['defer_open']:
                blank = LCD()
                blank.text('OPEN', 30, 20)
                peer.image = blank.packed()
            return
        elif value == 6:
            return
        peer.image = menu(state['selected']) if state['mode'] == 'menu' else icons_frame(state['selected'])
        if state['blink_steps'] and state['mode']=='menu':
            route.send(f'<Values><item id="{connection["lcd"]}"><value>{menu(-2)}</value></item></Values>')

    peer.on_move = move
    page.route_web_socket(re.compile(r'^ws://127\.0\.0\.1:8214/$'), peer.connect)
    page.goto(base_url + '/configured.html')
    page.evaluate('(url)=>window.handleMessage({type:"update",config:{luxtronikWebInterfaceURL:url}})',base_url+'/luxtronik-test.html')
    page.locator('#gSettings').dispatch_event('click')
    page.wait_for_function('!document.querySelector("#wp-luxtronik-controls").disabled')

    def push(frame):
        peer.image = frame
        connection = peer.connections[-1]
        connection['route'].send(f'<Values><item id="{connection["lcd"]}"><value>{frame}</value></item></Values>')
        page.wait_for_timeout(50)

    def model(frame):
        return page.evaluate('(frame)=>LuxtronikDisplay.analyze(atob(frame))',frame)

    def settle():
        page.wait_for_function('!document.querySelector("#wp-luxtronik-targets button:disabled")')

    # Known menu shape, a selected arrow, and icons have distinct click regions.
    detected=model(menu_frame())
    assert detected['kind']=='menu' and len(detected['targets'])==7,detected
    for selected in range(5):
        detected=model(menu_frame(selected,pitch=10))
        assert detected['kind']=='menu' and len(detected['targets'])==6,detected
    for x in [17,18,19,20,22]:
        for pitch in [9,10]:
            detected=model(menu_frame(pitch=pitch,origin_x=x))
            assert detected['kind']=='menu' and detected['grid']['x']==x,(x,pitch,detected)
    for side in ['left','right']:
        for asymmetric in [False,True]:
            lcd=LCD()
            lcd.arrow(side,True,asymmetric=asymmetric)
            arrow=model(lcd.packed())
            assert len(arrow['targets'])==1 and arrow['selected']=='arrow:'+side,arrow
    for selected in range(5):
        icons=model(icons_frame(selected))
        assert icons['kind']=='icons' and len(icons['targets'])==5,icons
    assert not model(menu_frame(duplicate=True))['targets']
    assert not model(LCD().packed())['targets']
    edited=LCD()
    edited.text('MMMMMM',18,10)
    edited.rect(90,9,69,9)
    assert not model(edited.packed())['targets']

    # Hiding a cursor must retain the same menu geometry and DOM targets.
    phases=page.evaluate('''images=>{
        let previous=null;
        return images.map(image=>{
            previous=LuxtronikDisplay.analyze(atob(image),previous);
            return {count:previous.targets.length,layout:previous.layout,selected:previous.selected};
        });
    }''',[menu(),menu(-2),menu(),menu(-2),menu()])
    assert all(phase['count']==6 for phase in phases),phases
    assert len({phase['layout'] for phase in phases})==1,phases
    assert phases[0]['selected'] and phases[1]['selected'] is None,phases
    for frames in [[icons_frame(),icons_frame(-1),icons_frame()],
                   [menu(),menu(-2,heading='OTHER')]]:
        result=page.evaluate('''images=>{
            let previous=null;
            return images.map(image=>{
                previous=LuxtronikDisplay.analyze(atob(image),previous);
                return {count:previous.targets.length,layout:previous.layout,selected:previous.selected};
            });
        }''',frames)
        if len(frames)==3:
            assert all(frame['count']==5 for frame in result),result
            assert len({frame['layout'] for frame in result})==1,result
        else:
            assert not result[-1]['selected'] and result[0]['layout']!=result[-1]['layout'],result

    # Hold the mouse through an incoming blink frame, then release during that phase.
    target=page.get_by_role('button',name='Menüzeile 3 öffnen',exact=True)
    target.hover()
    target.evaluate("button=>button.dataset.identity='retained'")
    page.mouse.down()
    push(menu(-2))
    assert target.get_attribute('data-identity')=='retained'
    assert target.get_attribute('data-pointer-held')==''
    assert page.locator('#wp-luxtronik-targets button').count()==6
    page.mouse.up()
    page.wait_for_timeout(50)
    assert not any(message.startswith('MOVE;') for message in peer.commands),peer.commands
    assert 'Auswahlmarkierung' in page.locator('#wp-luxtronik-help').text_content()
    state['blink_steps']=True
    push(menu())
    page.wait_for_function('!document.querySelector("#wp-luxtronik-targets button")')
    assert state['opened']==[2],state
    state.update(selected=0,blink_steps=False)
    state['opened'].clear()
    peer.commands.clear()
    push(menu())

    # Neither a real heading change nor dragging outside may activate a captured target.
    for changed in [True,False]:
        target=page.get_by_role('button',name='Menüzeile 2 öffnen',exact=True)
        target.hover()
        page.mouse.down()
        if changed:
            push(menu(heading='OTHER'))
        else:
            page.mouse.move(0,0)
        page.mouse.up()
        page.wait_for_timeout(50)
        assert not any(message.startswith('MOVE;') for message in peer.commands),peer.commands
        push(menu())

    # A cursor blink after Enter is not a screen change or permission to repeat Enter.
    state['defer_open']=True
    target=page.get_by_role('button',name='Menüzeile 1 öffnen',exact=True)
    bounds=target.bounding_box()
    target.click()
    push(menu(-2))
    push(menu())
    assert page.locator('#wp-luxtronik-targets button:disabled').count()==6
    page.mouse.click(bounds['x']+bounds['width']/2,bounds['y']+bounds['height']/2)
    page.wait_for_timeout(50)
    assert state['opened']==[0],state
    blank=LCD()
    blank.text('OPEN',30,20)
    push(blank.packed())
    state.update(defer_open=False,selected=0)
    state['opened'].clear()
    peer.commands.clear()
    push(menu())

    # A double click on the current row opens it once, without confirming the next screen.
    page.get_by_role('button',name='Menüzeile 1 öffnen',exact=True).dblclick()
    page.wait_for_function('!document.querySelector("#wp-luxtronik-targets button")')
    assert state['opened']==[0],state
    state['opened'].clear()
    peer.commands.clear()
    push(menu())
    # Three confirmed steps, then exactly one Enter pair, on both viewport sizes.
    page.get_by_role('button',name='Menüzeile 4 öffnen',exact=True).click()
    page.wait_for_function('!document.querySelector("#wp-luxtronik-targets button")')
    assert state['opened']==[3],state
    moves=[message for message in peer.commands if message.startswith('MOVE;')]
    assert moves==['MOVE;0','MOVE;6']*3+['MOVE;2','MOVE;6'],moves

    # Direct arrow activation, followed by lower icon bar navigation.
    state.update(mode='menu',selected=0)
    push(menu())
    page.get_by_role('button',name='Zurück öffnen',exact=True).click()
    page.wait_for_function('!document.querySelector("#wp-luxtronik-targets button")')
    assert state['opened'][-1]==-1,state
    state.update(mode='icons',selected=0)
    push(icons_frame())
    page.get_by_role('button',name='Menüsymbol 3 öffnen',exact=True).click()
    page.wait_for_function('!document.querySelector("#wp-luxtronik-targets button")')
    assert state['opened'][-1]==3,state

    # The transparent click regions must not swallow the original wheel operation.
    state.update(mode='menu',selected=0)
    push(menu())
    before=len(state['opened'])
    command_count=len(peer.commands)
    page.get_by_role('button',name='Menüzeile 2 öffnen',exact=True).dispatch_event('wheel',{'deltaY':100})
    page.wait_for_timeout(50)
    moves=[message for message in peer.commands[command_count:] if message.startswith('MOVE;')]
    assert moves==['MOVE;0'],moves
    assert len(state['opened'])==before

    # No blind Enter when a step is ignored, skipped, or a different screen appears.
    for case in ['wrong','screen','manual','timeout','close']:
        state.update(mode='menu',selected=0,ignore=True,wrong=False)
        push(menu())
        before=len(state['opened'])
        page.get_by_role('button',name='Menüzeile 3 öffnen',exact=True).click()
        page.wait_for_timeout(50)
        if case=='wrong':
            push(menu(2))  # Skipped the expected immediate neighbor.
        elif case=='screen':
            push(icons_frame())
        elif case=='manual':
            page.locator('#wp-luxtronik-display').focus()
            page.keyboard.press('ArrowLeft')
            push(menu(1))
        elif case=='timeout':
            page.wait_for_timeout(3200)
        else:
            page.locator('#wp-luxtronik-close').click()
            assert not page.locator('#wp-luxtronik-targets button').count()
        if case!='close':
            settle()
        assert len(state['opened'])==before,(case,state)
    page.close()
    print({'click_navigation':'PASS','viewport':width})
