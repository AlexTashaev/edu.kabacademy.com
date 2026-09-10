-- Mobile app: point mobilecssurl at the hosted CSS file.
UPDATE mdl_config
SET value = 'https://edu.kabacademy.com/mobileapp.css'
WHERE name = 'mobilecssurl';

-- Web (desktop + mobile browser): append responsive Google Drive iframe rule
-- to Boost Raw SCSS. Idempotent: skipped if the marker is already present.
UPDATE mdl_config_plugins
SET value = CONCAT(value, '\n\n/* Responsive Google Drive video embeds - mobile controls overlap fix */\niframe[src*="drive.google.com"][height="480"] {\n  display: block;\n  width: 100% !important;\n  height: auto !important;\n  aspect-ratio: #{"16 / 9"};\n  max-width: 100%;\n  border: 0;\n}\n')
WHERE plugin = 'theme_boost' AND name = 'scss'
  AND value NOT LIKE '%mobile controls overlap fix%';
