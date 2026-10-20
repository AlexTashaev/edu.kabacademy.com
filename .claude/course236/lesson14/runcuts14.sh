#!/bin/sh
# runcuts14.sh — cut the clips of cuts14.txt (NAMES="a b" limits to those); a leading VF=... token sets the extra filter
cd "$(dirname "$0")"
export OUT=${OUT:-./web14}
grep -v '^#' cuts14.txt | while read line; do
  set -- $line; vf=""; case "$1" in VF=*) vf="${1#VF=}"; shift;; esac
  [ $# -gt 0 ] || continue
  want=1; if [ -n "$NAMES" ]; then want=0; for n in $NAMES; do [ "$n" = "$2" ] && want=1; done; fi
  [ $want = 1 ] && { VF="$vf" sh cut14.sh "$@" || echo "FAIL $2"; }
done
echo ALLDONE
