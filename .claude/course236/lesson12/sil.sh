#!/bin/sh
# silent runs (< -38 dB, >= 0.1 s) in an archive file window: sil.sh fid from dur
u=$(curl -sI "https://cdn.kabbalahmedia.info/$1.mp4" | tr -d '\r' | awk 'tolower($1)=="location:"{print $2}' | tail -1)
ffmpeg -nostdin -v error -ss $2 -i "$u" -t $3 -vn -ac 1 -ar 16000 -af "asetnsamples=n=800,astats=metadata=1:reset=1,ametadata=print:key=lavfi.astats.Overall.RMS_level:file=-" -f null - 2>/dev/null | awk -v o=$2 '/pts_time/{split($3,a,":"); t=a[2]+o} /RMS_level/{split($1,b,"="); v=b[2]+0; if (v<-38) {if (s=="") s=t; e=t+0.05} else {if (s!="" && e-s>=0.1) printf "%.2f-%.2f ", s, e; s=""}} END{if (s!="" && e-s>=0.1) printf "%.2f-%.2f", s, e; print ""}'
