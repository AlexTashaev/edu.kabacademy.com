# edu.kabacademy.com — Source Code Repository

This repository holds the **custom Moodle theme and plugins** for [edu.kabacademy.com](https://edu.kabacademy.com).  
It is **not** a full Moodle installation — only the site-specific customisations are tracked here.

---

## Repository structure

```
.
├── theme/
│   └── kabacademy/          ← Custom theme (child of Boost)
│       ├── config.php
│       ├── lib.php
│       ├── version.php
│       ├── lang/
│       │   ├── en/
│       │   └── ru/
│       └── templates/
│           └── block_myoverview/
│               └── progress-bar.mustache
├── local/                   ← Local plugins (to be added from server)
├── mod/                     ← Custom activity modules (to be added from server)
├── blocks/                  ← Custom blocks (to be added from server)
├── auth/                    ← Custom authentication plugins (to be added from server)
├── enrol/                   ← Custom enrolment plugins (to be added from server)
└── course/format/           ← Custom course formats (to be added from server)
```

Directories marked *"to be added from server"* exist on the live Moodle installation
and have not yet been imported.  See **[Importing the remaining code](#importing-the-remaining-code)** below.

---

## What is already in this repository

| Component | Location | Status |
|---|---|---|
| Theme `kabacademy` (Boost child) | `theme/kabacademy/` | ✅ Present |
| Custom plugins | `local/`, `mod/`, `blocks/`, … | ⏳ Needs import from server |

---

## What is intentionally excluded

The following are **never committed** (see `.gitignore`):

| Item | Reason |
|---|---|
| `config.php` | Contains DB credentials and Moodle secret salt |
| `moodledata/` | Runtime data, user uploads — can be gigabytes |
| `cache/`, `localcache/`, `temp/`, `sessions/` | Generated at runtime |
| `*.sql`, `*.sql.gz` | May contain private user data |
| `*.pem`, `*.ppk`, `*.key` | Private keys |
| `.env`, `.env.*` | Environment secrets |

---

## Importing the remaining code

Because the live server is only reachable via **SFTP through an SSH bastion**, the remaining
plugin and theme files must be pulled from the server manually once, then pushed here.

### Prerequisites

* WinSCP (Windows) **or** `sftp`/`rsync` (Linux/macOS)
* SSH tunnel credentials (bastion: `<BASTION_HOST>:22`, user `<BASTION_USER>`, private key `.ppk`)
* Inner host: `<INTERNAL_HOST>:22`, user `<SSH_USER>`

### Step 1 — Connect and identify the Moodle root

Open a connection in WinSCP with the settings shown in the project's connection screenshot,
then navigate to the Moodle web root (typically `/var/www/html`, `/home/edu.kabacademy.com/public_html`,
or similar). You can confirm it is the Moodle root by the presence of `config.php`, `index.php`, and
a `lib/` directory at the top level.

### Step 2 — Download custom directories only

Download **only** the directories that contain custom (non-core) code:

```
theme/
local/
mod/          (only sub-folders you created yourself)
blocks/       (only custom blocks)
auth/         (only custom auth plugins)
enrol/        (only custom enrolment plugins)
course/format/ (only custom formats)
admin/tool/   (only custom tools)
```

> ⚠️ Do **not** download `moodledata/`, `cache/`, `config.php`, or core Moodle directories.

### Step 3 — Clone this repository locally

```bash
git clone https://github.com/AlexTashaev/edu.kabacademy.com.git
cd edu.kabacademy.com
git checkout -b import/plugins-from-server
```

### Step 4 — Copy downloaded files into the clone

Copy the downloaded directories into the corresponding locations inside the cloned repository, e.g.:

```
edu.kabacademy.com/
  theme/kabacademy/      ← already present, overwrite if updated
  local/myplugin/        ← paste downloaded plugin here
  blocks/mycustomblock/  ← paste downloaded block here
  …
```

### Step 5 — Review, commit and push

```bash
# Double-check nothing sensitive slipped in
grep -r 'password\|secret\|salt' --include='*.php' .

git add .
git commit -m "Import custom plugins and theme from live server"
git push origin import/plugins-from-server
```

Then open a Pull Request on GitHub and merge into `main`.

---

## Local development workflow

1. Install Moodle locally (or use a Docker image such as `moodlehq/moodle-php-apache`).
2. Clone this repository into your Moodle installation root (or symlink the sub-directories).
3. Activate the `kabacademy` theme in **Site administration → Appearance → Themes**.
4. Install any custom plugins via **Site administration → Plugins → Install plugins**.

---

## Contributing

* Branch off `main` for each change.
* Keep secrets out of commits (the `.gitignore` helps, but double-check).
* Open a Pull Request and request review before merging.
