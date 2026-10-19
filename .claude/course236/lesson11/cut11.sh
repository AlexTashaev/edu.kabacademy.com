#!/bin/sh
# Cut a clip from an archive file straight from the CDN: accurate seek, x264 CRF 18, AAC 128k, short fades, faststart.
# One segment: cut11.sh <fileid> <name> <a> <b> <poster_s>
# Two segments joined (cut out the middle): cut11.sh <fileid> <name> <a> <b> <poster_s> <c> <d>
set -e
fid=$1; name=$2; a=$3; b=$4; pa=$5; c=$6; d=$7
out=${OUT:-./web11}
mkdir -p "$out"
url=$(curl -sI "https://cdn.kabbalahmedia.info/$fid.mp4" | tr -d '\r' | awk 'tolower($1)=="location:"{print $2}' | tail -1)
[ -n "$url" ] || url="https://cdn.kabbalahmedia.info/$fid.mp4"
enc="-c:v libx264 -preset slow -crf 18 -profile:v high -pix_fmt yuv420p -c:a aac -b:a 128k"
seg() {  # seg <from> <to> <file> : fades on both ends
  dd=$(awk "BEGIN{printf \"%.3f\", $2-$1}"); fo=$(awk "BEGIN{printf \"%.3f\", $dd-0.35}")
  ffmpeg -nostdin -v error -y -ss "$1" -i "$url" -t "$dd" $enc \
    -vf "fade=t=in:st=0:d=0.3,fade=t=out:st=$fo:d=0.35" -af "afade=t=in:st=0:d=0.2,afade=t=out:st=$fo:d=0.35" "$3"
}
if [ -z "$c" ]; then
  seg "$a" "$b" "$out/$name.tmp.mp4"
else
  seg "$a" "$b" "$out/$name.p1.mp4"; seg "$c" "$d" "$out/$name.p2.mp4"
  # names relative to the list file: Windows ffmpeg does not understand /c/... paths
  printf "file '%s'\nfile '%s'\n" "$name.p1.mp4" "$name.p2.mp4" > "$out/$name.list"
  ffmpeg -nostdin -v error -y -f concat -safe 0 -i "$out/$name.list" -c copy "$out/$name.tmp.mp4"
  rm -f "$out/$name.p1.mp4" "$out/$name.p2.mp4" "$out/$name.list"
fi
ffmpeg -nostdin -v error -y -i "$out/$name.tmp.mp4" -c copy -movflags +faststart "$out/$name.mp4" && rm -f "$out/$name.tmp.mp4"
ffmpeg -nostdin -v error -y -ss "$pa" -i "$out/$name.mp4" -frames:v 1 -vf "scale='min(1280,iw)':-2" -q:v 3 "$out/$name.jpg"
echo "$name $(ffprobe -v error -show_entries format=duration:stream=width,height -of compact "$out/$name.mp4" | tr '\n' ' ')"
