/* Geometry of the 160 x 64 LCD. No OCR, guessed menu IDs or device writes. */
class LuxtronikDisplay {
    static bands(values) {
        const bands = [];
        let start = -1;
        for (let i = 0; i <= values.length; i++) {
            if (values[i] && start < 0) start = i;
            if (!values[i] && start >= 0) {
                bands.push({start, length: i - start});
                start = -1;
            }
        }
        return bands;
    }

    static analyze(bytes, previous = null) {
        if (bytes.length !== 1280) return {targets: [], selected: null};
        const pixel = (x, y) => x >= 0 && x < 160 && y >= 0 && y < 64
            ? (bytes.charCodeAt((y >> 3) * 160 + x) >> (y & 7)) & 1 : 0;
        const signature = (box, inverted = false, inset = 0) => {
            const rows = [];
            for (let y = box.y + inset; y < box.y + box.h - inset; y++) {
                const row = [];
                for (let x = box.x + inset; x < box.x + box.w - inset; x++) row.push(pixel(x, y) ^ Number(inverted));
                rows.push(row);
            }
            while (rows.length && !rows[0].some(Boolean)) rows.shift();
            while (rows.length && !rows.at(-1).some(Boolean)) rows.pop();
            if (!rows.length) return null;
            const columns = rows[0].map((_, x) => rows.some(row => row[x]));
            const first = columns.indexOf(true), last = columns.lastIndexOf(true);
            const bits = rows.map(row => row.slice(first, last + 1));
            return {w: last - first + 1, h: rows.length, bits,
                id: `${last - first + 1}x${rows.length}:` + bits.map(row => row.join('')).join('/')};
        };
        const arrowShape = (glyph) => {
            if (!glyph || glyph.w < 8 || glyph.w > 12 || glyph.h < 7 || glyph.h > 11) return null;
            const widths = glyph.bits.map(row => row.reduce((a, b) => a + b, 0));
            if (widths[0] > 3 || widths.at(-1) > 3 || Math.max(...widths) < glyph.w - 1) return null;
            let mismatch = 0;
            for (let y = 0; y < glyph.h; y++) {
                const row = glyph.bits[y], indices = row.flatMap((bit, x) => bit ? [x] : []);
                if (indices.length && indices.at(-1) - indices[0] + 1 !== indices.length) return null;
                for (let x = 0; x < glyph.w; x++) mismatch += row[x] !== glyph.bits[glyph.h - 1 - y][x];
            }
            if (mismatch > glyph.w * glyph.h * .18) return null;
            const center = row => row.reduce((sum, bit, x) => sum + bit * x, 0) / row.reduce((a, b) => a + b, 0);
            const offset = (center(glyph.bits[0]) + center(glyph.bits.at(-1))) / 2 - (glyph.w - 1) / 2;
            return Math.abs(offset) >= 1 ? (offset > 0 ? 'right' : 'left') : null;
        };
        const arrows = [];
        for (const side of ['left', 'right']) {
            const candidates = [];
            for (let w = 14; w <= 18; w++) for (let h = 14; h <= 18; h++) {
                for (let y = 45; y + h <= 64; y++) for (let margin = 0; margin <= 3; margin++) {
                    const x = side === 'left' ? margin : 160 - margin - w;
                    let edge = 0;
                    for (let xx = x; xx < x + w; xx++) edge += pixel(xx, y) + pixel(xx, y + h - 1);
                    for (let yy = y + 1; yy < y + h - 1; yy++) edge += pixel(x, yy) + pixel(x + w - 1, yy);
                    if (edge < (2 * w + 2 * h - 4) * .95) continue;
                    const selected = [[2, 2], [w - 3, 2], [2, h - 3], [w - 3, h - 3]]
                        .reduce((sum, [dx, dy]) => sum + pixel(x + dx, y + dy), 0) >= 3;
                    for (const inset of [1, 2]) {
                        const direction = arrowShape(signature({x, y, w, h}, selected, inset));
                        if (direction) candidates.push({x, y, w, h, selected, direction,
                            id: 'arrow:' + direction, kind: 'arrow', label: direction === 'left' ? 'Zurück' : 'Weiter'});
                    }
                }
            }
            candidates.sort((a, b) => b.w * b.h - a.w * a.h);
            if (candidates.length) arrows.push(candidates[0]);
        }

        // Infer the rectangle from its empty top/bottom margin, not a fixed x coordinate.
        // A real controller can start the same menu at x=19 rather than x=18.
        const rectangles = new Map();
        for (let y = 8; y < 64; y++) {
            const runs = this.bands(Array.from({length: 160}, (_, x) => pixel(x, y)));
            for (const run of runs) {
                if (run.start < 14 || run.start > 28 || run.length < 110 || run.start + run.length < 156) continue;
                const right = run.start + run.length - 1;
                const inside = yy => {
                    if (yy < 8 || yy >= 64 || !pixel(run.start, yy) || !pixel(right, yy)) return false;
                    let white = 0;
                    for (let x = run.start; x <= right; x++) white += pixel(x, yy);
                    return white >= run.length * .5;
                };
                let top = y, bottom = y;
                while (inside(top - 1)) top--;
                while (inside(bottom + 1)) bottom++;
                const height = bottom - top + 1;
                if (height < 7 || height > 11) continue;
                let valid = true;
                for (let x = run.start; x <= right; x += 20) {
                    const width = Math.min(20, right - x + 1);
                    let white = 0;
                    for (let yy = top; yy <= bottom; yy++) for (let dx = 0; dx < width; dx++) white += pixel(x + dx, yy);
                    if (white < height * Math.min(2, width)) valid = false;
                }
                if (valid) rectangles.set(`${run.start}:${top}:${run.length}:${height}`,
                    {x: run.start, start: top, w: run.length, length: height});
            }
        }
        const inverse = Array.from(rectangles.values());
        const context = signature({x: 0, y: 0, w: 160, h: 8})?.id || '';
        let grid = inverse.length === 1 ? {x: inverse[0].x, w: inverse[0].w, y: inverse[0].start, pitch: inverse[0].length} : null;
        if (!grid && previous?.kind === 'menu' && previous.context === context) grid = previous.grid;
        if (grid) {
            const rows = [];
            for (let y = grid.y % grid.pitch; y + grid.pitch <= 64; y += grid.pitch) {
                if (y < 8) continue; // Heading is not a selectable line.
                const selected = inverse.length === 1 && y === inverse[0].start;
                const glyph = signature({x: grid.x, y, w: grid.w, h: grid.pitch}, selected);
                if (glyph && glyph.h >= 5 && glyph.h <= 9 && glyph.w >= 5) rows.push({
                    x: grid.x, y, w: grid.w, h: grid.pitch, selected, kind: 'row',
                    id: 'row:' + glyph.id, label: 'Menüzeile ' + (rows.length + 1)
                });
            }
            const targets = [...arrows.filter(arrow => arrow.direction === 'left'), ...rows];
            const oldRows = previous?.targets.filter(target => target.kind === 'row').map(target => target.id).sort().join('|');
            const sameRows = rows.map(target => target.id).sort().join('|') === oldRows;
            if (rows.length >= 2 && (inverse.length === 1 || sameRows)) {
                return this.model('menu', targets, {grid, context,
                    allowUnselected: sameRows && previous?.context === context});
            }
        }

        // The standard menu has a separated lower icon band and a footer caption.
        const rowCounts = Array.from({length: 64}, (_, y) => {
            let count = 0;
            for (let x = 19; x < 141; x++) count += pixel(x, y);
            return count;
        });
        const iconBands = this.bands(rowCounts).filter(band => band.start >= 28 && band.start + band.length <= 55
            && band.length >= 11 && band.length <= 20);
        for (const band of iconBands) {
            const columns = Array.from({length: 122}, (_, dx) => {
                for (let y = band.start; y < band.start + band.length; y++) if (pixel(19 + dx, y)) return true;
                return false;
            });
            // A one-pixel break may belong to one icon; two empty columns separate icons.
            for (let x = 1; x + 1 < columns.length; x++) if (columns[x - 1] && columns[x + 1]) columns[x] = true;
            const groups = this.bands(columns);
            if (groups.length < 3 || groups.some(group => group.length < 5 || group.length > 21)) continue;
            const icons = groups.map((group, index) => {
                const box = {x: 19 + group.start, y: band.start, w: group.length, h: band.length};
                let white = 0;
                for (let y = box.y; y < box.y + box.h; y++) for (let x = box.x; x < box.x + box.w; x++) white += pixel(x, y);
                const selected = white / (box.w * box.h) > .65
                    && [[0, 0], [box.w - 1, 0], [0, box.h - 1], [box.w - 1, box.h - 1]]
                        .every(([dx, dy]) => pixel(box.x + dx, box.y + dy));
                const glyph = signature(box, selected);
                return glyph && {...box, selected, kind: 'icon', id: 'icon:' + glyph.id, label: 'Menüsymbol ' + (index + 1)};
            });
            if (icons.some(icon => !icon)) continue;
            return this.model('icons', [...arrows.filter(arrow => arrow.direction === 'left'), ...icons], {allowUnselected: true});
        }
        return this.model('arrows', arrows, {allowUnselected: true});
    }

    static model(kind, targets, extra = {}) {
        const selected = targets.filter(target => target.selected);
        const ids = targets.map(target => target.id);
        // Ambiguous geometry or duplicate labels cannot drive a click transaction.
        const valid = (selected.length === 1 || (extra.allowUnselected && selected.length === 0))
            && new Set(ids).size === ids.length;
        return {kind, targets: valid ? targets : [], selected: valid && selected.length === 1 ? selected[0].id : null,
            layout: kind + ':' + (extra.context || '') + ':' + ids.join('|'), ...extra};
    }
}

class LuxtronikClickNavigation {
    constructor({move, refresh, render, status}) {
        Object.assign(this, {move, refresh, render, status});
        this.model = {targets: [], selected: null};
        this.pending = null;
        this.timer = null;
        this.frame = '';
    }

    cancel(message = '') {
        clearTimeout(this.timer);
        this.timer = null;
        this.pending = null;
        this.render(this.model, false);
        if (message) this.status(message);
    }

    reset() {
        this.model = {targets: [], selected: null};
        this.frame = '';
        this.cancel();
    }

    update(bytes) {
        const model = LuxtronikDisplay.analyze(bytes, this.model);
        this.frame = bytes;
        this.model = model;
        if (!this.pending) { this.render(model, false); return; }
        const pending = this.pending;
        if (pending.opened) {
            // A blinking cursor is not proof that the requested screen has opened.
            if (model.layout !== pending.layout) this.cancel();
            return;
        }
        if (model.layout !== pending.layout) {
            this.cancel('Die Anzeige hat sich geändert. Bitte das gewünschte Ziel erneut anklicken.');
            return;
        }
        if (!model.selected) return; // Keep the requested target through a cursor blink.
        if (pending.waiting) { this.advance(); return; }
        if (model.selected === pending.before) return; // Wait for a confirmed step.
        if (model.selected !== pending.expected) {
            this.cancel('Die Auswahl wurde nicht eindeutig bestätigt. Bitte die Pfeiltasten verwenden.');
            return;
        }
        this.advance();
    }

    activate(id) {
        if (this.pending || !this.model.targets.some(target => target.id === id)) return;
        this.pending = {id, layout: this.model.layout, steps: 0};
        this.advance();
    }

    advance() {
        const pending = this.pending;
        if (!pending) return;
        clearTimeout(this.timer);
        this.render(this.model, true);
        if (!this.model.selected) {
            pending.waiting = true;
            this.status('Warte auf die Auswahlmarkierung des Reglers …');
            this.timer = setTimeout(() => this.cancel('Die Auswahlmarkierung wurde nicht bestätigt. Bitte die Pfeiltasten verwenden.'), 3000);
            return;
        }
        pending.waiting = false;
        if (this.model.selected === pending.id) {
            pending.opened = true;
            pending.frame = this.frame;
            this.move(2);
            this.move(6);
            this.refresh();
            // Do not let a double click confirm the old screen a second time.
            this.timer = setTimeout(() => this.cancel(), 3000);
            return;
        }
        const targets = this.model.targets;
        const from = targets.findIndex(target => target.id === this.model.selected);
        const to = targets.findIndex(target => target.id === pending.id);
        if (from < 0 || to < 0 || pending.steps++ >= 8) {
            this.cancel('Das Ziel konnte nicht eindeutig erreicht werden. Bitte die Pfeiltasten verwenden.');
            return;
        }
        const delta = to > from ? 1 : -1;
        pending.before = this.model.selected;
        pending.expected = targets[from + delta].id;
        this.move(delta > 0 ? 0 : 1);
        this.move(6);
        this.refresh();
        this.status('Wähle den angeklickten Eintrag …');
        this.timer = setTimeout(() => this.cancel('Keine bestätigte Auswahl erhalten. Bitte erneut anklicken oder die Pfeiltasten verwenden.'), 3000);
    }
}
