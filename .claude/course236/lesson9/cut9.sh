#!/bin/sh
# Cut a fragment from an archive source: re-encode x264 CRF 18 (High), AAC 128k, short audio/video fades, faststart; poster at POSTER s.
# usage: cut9.sh <src.mp4> <out_name> <start_s> <end_s> <poster_offset_s>
set -e
src=$1; name=$2; a=$3; b=$4; pa=$5
out=/c/Users/tashaev/AppData/Local/Temp/kabsc5/web9
mkdir -p "$out"
d=$(awk "BEGIN{printf \"%.3f\", $b-$a}")
fo=$(awk "BEGIN{printf \"%.3f\", $d-0.4}")
ffmpeg -v error -y -ss "$a" -i "$src" -t "$d" -c:v libx264 -preset slow -crf 18 -profile:v high -pix_fmt yuv420p \
  -vf "fade=t=in:st=0:d=0.3,fade=t=out:st=$fo:d=0.4" -af "afade=t=in:st=0:d=0.2,afade=t=out:st=$fo:d=0.4" \
  -c:a aac -b:a 128k -movflags +faststart "$out/$name.mp4"
ffmpeg -v error -y -ss "$pa" -i "$out/$name.mp4" -frames:v 1 -vf "scale='min(1280,iw)':-2" -q:v 3 "$out/$name.jpg"
ffprobe -v error -show_entries format=duration:stream=width,height -of compact "$out/$name.mp4" | tr '\n' ' '; echo " $name"
