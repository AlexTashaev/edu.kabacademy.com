// Runs in the admin's browser on edu.kabacademy.com (any page of course 236), pasted into the
// console. Builds lessons in the background and logs into window.kablog — a long await in the
// devtools/CDP call times out, the in-page promise keeps going.
//
// buildLesson(L) creates the five subsections of lesson L.n, copies the lesson-2 modules into
// them (cm_duplicate with targetsectionid), moves the lesson's zoom meetings into ВЕБИНАР and
// last year's modules into the hidden section "*".
//
// Грабли: cm_duplicate of the quiz answers with an HTML page instead of JSON, although the copy
// is created and placed — dup() then checks the target section and carries on.
window.kablog = [];
window.kab = (() => {
    const COURSE = 236;
    const SUBS = ['ВЕБИНАР', 'Как устроен урок', '🟢 ОСНОВА',
        '📒 Дополнительно – если есть время на этой неделе', '💠 Факультатив – когда угодно'];
    // Lesson-2 sources per subsection (order = order on the page).
    const SRC = [[13481], [13465], [13466, 13467, 13468, 13469, 13470, 13471], [13472, 13474], [13475]];
    const SEMINAR = 13471;       // seminar recordings exist for lessons 1–5 only
    const HIDDEN_SECTION = 1748; // "*" — holding area for last year's modules

    async function ws(methodname, args) {
        const text = await fetch(M.cfg.wwwroot + '/lib/ajax/service.php?sesskey=' + M.cfg.sesskey,
            {method: 'POST', body: JSON.stringify([{index: 0, methodname, args}])}).then(r => r.text());
        const i = text.indexOf('[{"error"');
        if (i < 0) {
            window.kablast = text;
            throw new Error('NOJSON');
        }
        const r = JSON.parse(text.slice(i));
        if (r[0].error) throw new Error(methodname + ': ' + JSON.stringify(r[0].exception));
        return r[0].data;
    }
    const update = (action, ids, target = {}) =>
        ws('core_courseformat_update_course', {action, courseid: COURSE, ids, ...target});
    const state = async () => JSON.parse(await ws('core_courseformat_get_state', {courseid: COURSE}));
    const seclen = async (id) => (await state()).section.find(s => s.id == id).cmlist.length;

    async function dup(id, secid) {
        const n0 = await seclen(secid);
        try {
            await update('cm_duplicate', [id], {targetsectionid: secid});
        } catch (e) {
            if (e.message !== 'NOJSON') throw e;
            await new Promise(r => setTimeout(r, 5000));
            if (await seclen(secid) !== n0 + 1) throw new Error('dup ' + id + ' failed: ' + kablast.slice(0, 300));
            kablog.push('dup ' + id + ': HTML answer, copy is in place');
        }
    }

    async function addSubsection(sectionnum, name) {
        const url = `/course/modedit.php?add=subsection&type=&course=${COURSE}&section=${sectionnum}&return=0&sr=0`;
        const html = await fetch(url).then(r => r.text());
        const form = new DOMParser().parseFromString(html, 'text/html').querySelector('form.mform');
        const fd = new FormData(form);
        fd.set('name', name);
        fd.set('submitbutton2', 'save');
        const res = await fetch('/course/modedit.php', {method: 'POST', body: fd});
        if (!res.ok) throw new Error('modedit ' + res.status);
    }

    // skip = number of source modules already copied (to resume a lesson that stopped midway).
    async function fill(L, subs, skip = 0) {
        let k = 0;
        for (let i = 0; i < 5; i++) {
            for (const id of SRC[i].filter(id => id !== SEMINAR || L.n <= 5)) {
                if (k++ >= skip) await dup(id, subs[i]);
            }
        }
        const web = (await state()).section.find(s => s.id == subs[0]);
        await update('cm_move', L.zooms, {targetcmid: web.cmlist[0]});
        if (L.keep) await update('cm_move', L.keep, {targetsectionid: subs[2]});
        await update('cm_move', L.old, {targetsectionid: HIDDEN_SECTION});
        kablog.push(`L${L.n} DONE`);
    }

    async function buildLesson(L) {
        const before = new Set((await state()).section.map(s => s.id));
        for (const name of SUBS) await addSubsection(L.n, name);
        // New subsections of this lesson, in creation order (other editors may add sections meanwhile).
        const subs = (await state()).section
            .filter(s => !before.has(s.id) && s.component === 'mod_subsection' && s.parentsectionid == L.sectionid)
            .sort((a, b) => a.number - b.number).map(s => s.id);
        if (subs.length !== 5) throw new Error('expected 5 new sections, got ' + subs.length);
        kablog.push(`L${L.n} subsections ${subs}`);
        await fill(L, subs);
    }

    return {buildLesson, fill, state, update};
})();

// Lessons as built on 07.10.2026: section id, zoom cms, cms kept in ОСНОВА, last year's cms.
const LESSONS = [
    {n: 4, sectionid: 1743, zooms: [13130], old: [13107, 13114]},
    {n: 5, sectionid: 1742, zooms: [13113], old: [13090, 13097]},
    {n: 6, sectionid: 1741, zooms: [13096], old: [13078, 13080]},
    {n: 7, sectionid: 1740, zooms: [13079], old: [13066, 13068]},
    {n: 8, sectionid: 1739, zooms: [13067], old: [13054, 13056]},
    {n: 9, sectionid: 1738, zooms: [13055], old: [13042, 13044]},
    {n: 10, sectionid: 1737, zooms: [13043], old: [13030, 13032]},
    {n: 11, sectionid: 1736, zooms: [13031], old: [13017, 13020]},
    {n: 12, sectionid: 1735, zooms: [13019], old: [13005, 13007]},
    {n: 13, sectionid: 1734, zooms: [13006], old: [12993, 12995]},
    {n: 14, sectionid: 1733, zooms: [12994, 12981], old: [12980, 12983]},
    {n: 15, sectionid: 1732, zooms: [12982], keep: [12968], old: [12967, 12970]},
];
// (async () => { for (const L of LESSONS) await kab.buildLesson(L); kablog.push('ALL DONE'); })();
