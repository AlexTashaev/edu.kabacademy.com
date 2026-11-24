#!/usr/bin/env python3
"""Telegram links of the old WordPress site -> auth_kabtelegram_links (Moodle).

No data and no hosts live here: the exports are made on the servers and kept
outside the repository (they contain e-mails and Telegram ids).

  wp.tsv      on the WP server:  wp db query < wp_export.sql > wp.tsv
              columns: wpid, email, moodle_user_id, tgid, tgusername, tgfirstname
  fetch.sql   python3 build.py fetch wp.tsv > fetch.sql
  moodle.tsv  on the Moodle server: mysql ... < fetch.sql > moodle.tsv
              columns: id, email, deleted, suspended, confirmed, auth, mnethostid
  import.sql  python3 build.py import wp.tsv moodle.tsv > import.sql   (report on stderr)
              then mysql ... < import.sql; rerunning it is harmless.

Matching, per WP user with a Telegram id:
  1. moodle_user_id (the Edwiser link) points to a live account with the same e-mail;
  2. otherwise the only live local account with that e-mail;
  3. otherwise not imported (reported by WP user id only).
A Telegram id or a Moodle account met twice is not imported at all.
"""
import sys

WP_COLS = ['wpid', 'email', 'mid', 'tgid', 'tguser', 'tgfirst']
MOODLE_COLS = ['id', 'email', 'deleted', 'suspended', 'confirmed', 'auth', 'mnethostid']


def read_tsv(path, cols):
    rows = []
    with open(path, encoding='utf-8') as f:
        for n, line in enumerate(f):
            line = line.rstrip('\r\n')
            if not line:
                continue
            parts = line.split('\t')
            if n == 0 and not parts[0].strip().isdigit():
                continue  # header line
            if len(parts) < len(cols):
                parts += [''] * (len(cols) - len(parts))
            rows.append({c: ('' if parts[i] == 'NULL' else parts[i].strip()) for i, c in enumerate(cols)})
    return rows


def q(value):
    """MySQL string literal."""
    return "'" + value.replace('\\', '\\\\').replace("'", "\\'").replace('\n', ' ').replace('\r', ' ') + "'"


def wp_rows(path):
    out = []
    for r in read_tsv(path, WP_COLS):
        if not r['tgid'].isdigit() or int(r['tgid']) <= 0:
            continue
        r['email_l'] = r['email'].lower()
        out.append(r)
    return out


def cmd_fetch(wppath):
    rows = wp_rows(wppath)
    ids = sorted({int(r['mid']) for r in rows if r['mid'].isdigit()})
    emails = sorted({r['email_l'] for r in rows if r['email_l']})
    where = []
    if ids:
        where.append('id IN (' + ','.join(str(i) for i in ids) + ')')
    if emails:
        where.append('email IN (' + ','.join(q(e) for e in emails) + ')')
    print('SET NAMES utf8mb4;')
    print('SELECT id, email, deleted, suspended, confirmed, auth, mnethostid FROM mdl_user WHERE '
          + ' OR '.join(where or ['0']) + ';')


def cmd_import(wppath, moodlepath):
    wp = wp_rows(wppath)
    users = read_tsv(moodlepath, MOODLE_COLS)
    byid = {int(u['id']): u for u in users}
    byemail = {}
    for u in users:
        if u['deleted'] == '0' and u['mnethostid'] == '1':
            byemail.setdefault(u['email'].lower(), []).append(u)

    report = {'total': len(wp), 'by moodle_user_id + e-mail': 0, 'by e-mail only': 0,
              'e-mail ambiguous': 0, 'not found': 0, 'duplicate tgid or account': 0,
              'suspended/unconfirmed (imported, cannot sign in)': 0,
              'auth not manual/email (imported, cannot sign in)': 0}
    problems = []
    picks = []
    for r in wp:
        target = None
        if r['mid'].isdigit():
            u = byid.get(int(r['mid']))
            if u and u['deleted'] == '0' and u['email'].lower() == r['email_l']:
                target = u
                report['by moodle_user_id + e-mail'] += 1
        if target is None:
            cands = byemail.get(r['email_l'], [])
            if len(cands) == 1:
                target = cands[0]
                report['by e-mail only'] += 1
            elif len(cands) > 1:
                report['e-mail ambiguous'] += 1
                problems.append(('ambiguous', r['wpid']))
            else:
                report['not found'] += 1
                problems.append(('notfound', r['wpid']))
        if target is not None:
            picks.append((r, target))

    tgcount, ucount = {}, {}
    for r, u in picks:
        tgcount[r['tgid']] = tgcount.get(r['tgid'], 0) + 1
        ucount[u['id']] = ucount.get(u['id'], 0) + 1
    final = []
    for r, u in picks:
        if tgcount[r['tgid']] > 1 or ucount[u['id']] > 1:
            report['duplicate tgid or account'] += 1
            problems.append(('duplicate', r['wpid']))
            continue
        if u['suspended'] != '0' or u['confirmed'] != '1':
            report['suspended/unconfirmed (imported, cannot sign in)'] += 1
        if u['auth'] not in ('manual', 'email'):
            report['auth not manual/email (imported, cannot sign in)'] += 1
        final.append((r, u))

    print('SET NAMES utf8mb4;')
    print('SET @now = UNIX_TIMESTAMP();')
    for r, u in final:
        uid, tgid = int(u['id']), int(r['tgid'])
        user = r['tguser'][:64]
        name = r['tgfirst'][:255]
        print('INSERT INTO mdl_auth_kabtelegram_links '
              '(userid, tgid, tgusername, tgname, source, lastauthdate, timecreated, timemodified, timelastlogin) '
              f'SELECT {uid}, {tgid}, {q(user)}, {q(name)}, \'wp\', 0, @now, @now, 0 FROM DUAL '
              f'WHERE NOT EXISTS (SELECT 1 FROM mdl_auth_kabtelegram_links l WHERE l.tgid = {tgid} OR l.userid = {uid});')
    print("SELECT source, COUNT(*) AS links FROM mdl_auth_kabtelegram_links GROUP BY source;")

    for k, v in report.items():
        print(f'{k}: {v}', file=sys.stderr)
    print(f'to insert: {len(final)}', file=sys.stderr)
    for kind, wpid in problems:
        print(f'  {kind}: WP user {wpid}', file=sys.stderr)


if __name__ == '__main__':
    sys.stdout.reconfigure(encoding='utf-8', newline='\n')
    sys.stderr.reconfigure(encoding='utf-8')
    if len(sys.argv) == 3 and sys.argv[1] == 'fetch':
        cmd_fetch(sys.argv[2])
    elif len(sys.argv) == 4 and sys.argv[1] == 'import':
        cmd_import(sys.argv[2], sys.argv[3])
    else:
        print(__doc__, file=sys.stderr)
        sys.exit(2)
