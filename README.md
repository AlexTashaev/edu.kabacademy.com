# edu.kabacademy.com — Repository

Moodle-based site **edu.kabacademy.com**.  
This repository contains **only the custom code** that belongs to the project: themes and plugins.  
Standard Moodle core, user-uploaded files, and sensitive configuration files are **not** stored here.

---

## Repository structure

```
edu.kabacademy.com/
└── theme/
    └── kabacademy/          ← Custom child-theme (extends Boost)
        ├── config.php
        ├── lib.php
        ├── version.php
        ├── lang/
        │   ├── en/theme_kabacademy.php
        │   └── ru/theme_kabacademy.php
        └── templates/
            └── block_myoverview/
                └── progress-bar.mustache
```

---

## Theme: kabacademy

A child theme of **Boost** (Moodle 4.5+) that adds a visual progress bar to the "My Courses" dashboard block.

### Features
- Bootstrap 5 progress bar on the `block_myoverview` dashboard block.
- Percentage label in Russian and English.
- All standard Boost settings are inherited automatically.

### Installation
1. Copy the `theme/kabacademy` folder into the `theme/` directory of your Moodle installation.
2. Visit **Site administration → Notifications** to install/upgrade the plugin.
3. Go to **Site administration → Appearance → Themes → Theme selector** and activate **Kabacademy**.

---

## Why server files are not imported automatically

The production site runs on a server accessible only via **SFTP through an SSH bastion host** using a private `.ppk` key.  
The GitHub Copilot coding agent operates in a sandboxed cloud environment **without**:
- network access to `web-18` / `bastion.eu.kbb1.com`,
- your private SSH/SFTP key,
- any stored passwords.

Because of this, the agent **cannot** connect to the server and pull files directly.

---

## How to import the full site code yourself (10-minute guide)

### Step 1 — Download files from the server
Using **WinSCP** (with the bastion-tunnel settings shown in the screenshot):
1. Connect to `web-18` via the bastion `bastion.eu.kbb1.com:22` with the `.ppk` key.
2. Navigate to the Moodle web root (usually `/var/www/html/moodle` or `/home/edu.kabacademy.com/www`).
3. Download **only** the custom directories:
   - `theme/` — your custom themes
   - `local/` — local plugins (if any)
   - `mod/` — custom activity modules (if any)
   - `blocks/` — custom blocks (if any)
   - `auth/` — custom auth plugins (if any)
4. **Do NOT download**: `moodledata/`, `config.php`, logs, cache.

### Step 2 — Add files to this repository
```bash
# Clone the repo (if not already done)
git clone https://github.com/AlexTashaev/edu.kabacademy.com.git
cd edu.kabacademy.com

# Create a branch for the import
git checkout -b import/site-code-$(date +%Y%m%d)

# Copy your downloaded files into the repo directory
# (replace /path/to/downloaded with your actual path)
cp -r /path/to/downloaded/theme/* theme/
# cp -r /path/to/downloaded/local  local/
# cp -r /path/to/downloaded/mod    mod/

# Review what will be committed
git status
git diff --stat

# Stage and commit
git add .
git commit -m "Import site code from server"
git push origin import/site-code-$(date +%Y%m%d)
```

### Step 3 — Open a Pull Request
Open a PR from your import branch into `main` on GitHub.  
Review the diff to make sure no secrets (`config.php`, passwords, `.ppk` files) were accidentally included.

---

## What must never be committed

| File / directory | Reason |
|---|---|
| `config.php` | Contains DB password and secret salt |
| `moodledata/` | User uploads — too large, contains personal data |
| `*.ppk` / `*.pem` / `id_rsa` | Private SSH keys |
| `.env` / `.env.*` | Environment secrets |
| `*.sql` | Database dumps may contain passwords |
| Cache / temp / session dirs | Build artefacts, no value in git |

The `.gitignore` in this repository already excludes all of the above.

---

## License

Custom code in this repository is copyright © 2026 Kabacademy and released under the  
[GNU GPL v3 or later](https://www.gnu.org/copyleft/gpl.html), consistent with Moodle's own license.
