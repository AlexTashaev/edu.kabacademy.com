// Runs in the admin's browser on edu.kabacademy.com (any page of course 236).
// buildLesson(N) creates the five subsections of lesson N, copies the lesson-2 modules
// into them and moves the lesson's zoom meetings and last-year modules.
const COURSE = 236;
const SUBS = ['ВЕБИНАР', 'Как устроен урок', '🟢 ОСНОВА',
    '📒 Дополнительно – если есть время на этой неделе', '💠 Факультатив – когда угодно'];
// Lesson-2 sources per subsection (order = order on the page).
const SRC = [[13481], [13465], [13466, 13467, 13468, 13469, 13470, 13471], [13472, 13474], [13475]];
const SEMINAR = 13471;       // seminar recordings exist for lessons 1–5 only
const HIDDEN_SECTION = 1748; // "*" — holding area for last year's modules

async function ws(methodname, args) {
    const r = await fetch(M.cfg.wwwroot + '/lib/ajax/service.php?sesskey=' + M.cfg.sesskey,
        {method: 'POST', body: JSON.stringify([{index: 0, methodname, args}])}).then(r => r.json());
    if (r[0].error) throw new Error(methodname + ': ' + JSON.stringify(r[0].exception));
    return r[0].data;
}
const update = (action, ids, target = {}) =>
    ws('core_courseformat_update_course', {action, courseid: COURSE, ids, ...target});

async function state() {
    return JSON.parse(await ws('core_courseformat_get_state', {courseid: COURSE}));
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

// lesson: {n, sectionid, zooms: [cmid...], keep: [cmid...] (to ОСНОВА end), old: [cmid...]}
async function buildLesson(lesson) {
    let st = await state();
    const before = new Set(st.section.map(s => s.id));
    for (const name of SUBS) {
        await addSubsection(lesson.n, name);
    }
    st = await state();
    // New subsections of this lesson, in creation order (other editors may add sections meanwhile).
    const subsections = st.section
        .filter(s => !before.has(s.id) && s.component === 'mod_subsection' && s.parentsectionid == lesson.sectionid)
        .sort((a, b) => a.number - b.number);
    if (subsections.length !== 5) throw new Error('expected 5 new sections, got ' + subsections.length);
    for (let i = 0; i < 5; i++) {
        const ids = SRC[i].filter(id => id !== SEMINAR || lesson.n <= 5);
        for (const id of ids) {
            await update('cm_duplicate', [id], {targetsectionid: subsections[i].id});
        }
    }
    st = await state();
    const web = st.section.find(s => s.id == subsections[0].id);
    if (lesson.zooms.length) {
        await update('cm_move', lesson.zooms, {targetcmid: web.cmlist[0]});
    }
    if (lesson.keep && lesson.keep.length) {
        await update('cm_move', lesson.keep, {targetsectionid: subsections[2].id});
    }
    if (lesson.old && lesson.old.length) {
        await update('cm_move', lesson.old, {targetsectionid: HIDDEN_SECTION});
    }
    st = await state();
    return subsections.map(s => {
        const sec = st.section.find(x => x.id == s.id);
        return {sectionid: s.id, title: sec.title, cms: sec.cmlist};
    });
}
