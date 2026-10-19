#!/bin/sh
# usage: silb.sh <fileid> <t> [t ...]  — silences (-32 dB, >=0.2 s) in [t-4, t+4], decoded from the archive URL
fid=$1; shift
url=$(curl -sI "https://cdn.kabbalahmedia.info/$fid.mp4" | tr -d '\r' | awk 'tolower($1)=="location:"{print $2}' | tail -1)
for t in "$@"; do
  a=$(awk "BEGIN{print $t-4}")
  s=$(ffmpeg -nostdin -v info -ss "$a" -i "$url" -t 8 -vn -af silencedetect=n=-32dB:d=0.2 -f null - 2>&1 | grep -o "silence_\(start\|end\): [0-9.]*" | awk -v o="$a" '{split($0,x,": "); printf "%s=%.2f ", substr(x[1],9), x[2]+o}')
  echo "$fid @$t: $s"
done
