#!/bin/sh
# Web versions of the lesson-4 clips: masters (~12 Mbit/s) -> x264 CRF 18, High, AAC 128k, faststart;
# poster = frame at POSTER_AT seconds (default: a third of the clip), max 1280 px wide.
# Usage: encode.sh <src.mp4> <out_dir> [poster_seconds] [copy]   ("copy": only remux with faststart)
set -e
src=$1; out=$2; at=$3; mode=$4
name=$(basename "$src" .mp4)
mkdir -p "$out"
dur=$(ffprobe -v error -show_entries format=duration -of default=nw=1:nk=1 "$src")
[ -n "$at" ] || at=$(awk "BEGIN{printf \"%.2f\", $dur/3}")
if [ "$mode" = copy ]; then
  ffmpeg -v error -y -i "$src" -c copy -movflags +faststart "$out/$name.mp4"
else
  ffmpeg -v error -y -i "$src" -c:v libx264 -preset slow -crf 18 -profile:v high -pix_fmt yuv420p \
    -c:a aac -b:a 128k -movflags +faststart "$out/$name.mp4"
fi
ffmpeg -v error -y -ss "$at" -i "$out/$name.mp4" -frames:v 1 -vf "scale='min(1280,iw)':-2" -q:v 3 "$out/$name.jpg"
ls -la "$out/$name.mp4" "$out/$name.jpg"
