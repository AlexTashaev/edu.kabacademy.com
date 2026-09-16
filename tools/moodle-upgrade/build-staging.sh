#!/bin/sh
# Build a staging copy of a Moodle dirroot for a minor/weekly core upgrade.
#
#   build-staging.sh BASE NEW LIVE STAGING
#
#   BASE    pristine upstream tree the site is currently on (git archive of the
#           exact weekly-release commit, e.g. codeload.github.com/moodle/moodle/tar.gz/<sha>)
#   NEW     pristine upstream tree to upgrade to (official download.moodle.org package)
#   LIVE    current dirroot on the server (read only here)
#   STAGING output directory, must not exist
#
# Result: STAGING = NEW + everything that exists only in LIVE (config.php, add-on
# plugins, our static pages, backups) + our core patches carried over from LIVE.
# A core file we patched is carried over only if upstream did not touch it between
# BASE and NEW; otherwise the script stops and lists it for a manual 3-way merge.
# Root tooling dotfiles (.github, .grunt, ...) always come from NEW, even if LIVE has
# stale copies of them. Nothing in LIVE is modified.
set -eu

BASE=$1 NEW=$2 LIVE=$3 STAGING=$4
[ -d "$BASE" ] && [ -d "$NEW" ] && [ -d "$LIVE" ] || { echo "missing input dir" >&2; exit 2; }
[ ! -e "$STAGING" ] || { echo "$STAGING already exists" >&2; exit 2; }

WORK=$(mktemp -d)
LC_ALL=C diff -rq "$BASE" "$LIVE" > "$WORK/base_live.txt" || true

# Paths present only in LIVE (relative to dirroot).
sed -n "s#^Only in $LIVE/*\(.*\): \(.*\)#\1/\2#p" "$WORK/base_live.txt" | sed 's#^/##' > "$WORK/onlylive.txt"
# Core files that differ between BASE and LIVE = our patches (plus stale dotfiles).
sed -n "s#^Files $BASE/\(.*\) and $LIVE/.* differ#\1#p" "$WORK/base_live.txt" > "$WORK/patched.txt"

cp -a "$NEW" "$STAGING"

echo "== carried over from LIVE (not in upstream):"
while IFS= read -r p; do
    [ -n "$p" ] || continue
    echo "  $p"
    (cd "$LIVE" && rsync -a --relative "./$p" "$STAGING/")
done < "$WORK/onlylive.txt"

echo "== core patches:"
conflicts=0
while IFS= read -r p; do
    [ -n "$p" ] || continue
    if [ ! -e "$NEW/$p" ]; then
        echo "  drop (not in release package): $p"
    elif [ "${p#.}" != "$p" ]; then
        echo "  upstream (tooling dotfile):    $p"
    elif cmp -s "$BASE/$p" "$NEW/$p"; then
        echo "  carry (upstream unchanged):    $p"
        cp -p "$LIVE/$p" "$STAGING/$p"
    else
        echo "  CONFLICT (upstream changed):   $p"
        conflicts=$((conflicts + 1))
    fi
done < "$WORK/patched.txt"

cp "$WORK"/*.txt "$STAGING/../" 2>/dev/null || true
rm -rf "$WORK"

if [ "$conflicts" -gt 0 ]; then
    echo "$conflicts patched file(s) changed upstream: merge them into $STAGING by hand" >&2
    exit 1
fi
echo "staging ready: $STAGING"
