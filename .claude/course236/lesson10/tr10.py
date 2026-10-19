# Transcribe windows of archive files straight from the CDN (accurate seek), faster-whisper small, CPU.
# usage: python -I tr10.py <out_dir> name:fileid:start:end [...]
import os, subprocess, sys
from faster_whisper import WhisperModel

out = sys.argv[1]
os.makedirs(out, exist_ok=True)
model = WhisperModel('small', device='cpu', compute_type='int8')
for spec in sys.argv[2:]:
    name, fid, a, b = spec.split(':')
    a, b = float(a), float(b)
    url = subprocess.run(['curl', '-sI', 'https://cdn.kabbalahmedia.info/%s.mp4' % fid], capture_output=True, text=True).stdout
    loc = [l.split(' ', 1)[1].strip() for l in url.splitlines() if l.lower().startswith('location:')]
    src = loc[-1] if loc else 'https://cdn.kabbalahmedia.info/%s.mp4' % fid
    wav = os.path.join(out, name + '.wav')
    subprocess.run(['ffmpeg', '-nostdin', '-v', 'error', '-y', '-ss', str(a), '-i', src, '-t', str(b - a), '-vn', '-ac', '1', '-ar', '16000', wav], check=True)
    segs, _ = model.transcribe(wav, language='ru', word_timestamps=True, vad_filter=False, beam_size=5)
    with open(os.path.join(out, name + '.txt'), 'w', encoding='utf-8') as fh:
        for s in segs:
            t0, t1 = s.start + a, s.end + a
            fh.write('[%02d:%05.2f-%02d:%05.2f] %s\n' % (t0 // 60, t0 % 60, t1 // 60, t1 % 60, s.text.strip()))
    os.remove(wav)
    print('done', name, flush=True)
