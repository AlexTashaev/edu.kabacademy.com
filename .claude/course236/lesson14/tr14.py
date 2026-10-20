# Segments + words of archive windows: python -I tr14.py fid:a:b [...]  -> tr14/<fid>_<a>.txt
import os, subprocess, sys
from faster_whisper import WhisperModel
sys.stdout.reconfigure(encoding='utf-8')
m = WhisperModel('small', device='cpu', compute_type='int8')
os.makedirs('tr14', exist_ok=True)
for spec in sys.argv[1:]:
    fid, a, b = spec.split(':'); a = float(a); b = float(b)
    h = subprocess.run(['curl', '-sI', 'https://cdn.kabbalahmedia.info/%s.mp4' % fid], capture_output=True, text=True).stdout
    loc = [l.split(' ', 1)[1].strip() for l in h.splitlines() if l.lower().startswith('location:')]
    url = loc[-1] if loc else 'https://cdn.kabbalahmedia.info/%s.mp4' % fid
    subprocess.run(['ffmpeg', '-nostdin', '-v', 'error', '-y', '-ss', str(a), '-i', url, '-t', str(b - a), '-vn', '-ac', '1', '-ar', '16000', 'tr14.wav'], check=True)
    segs, _ = m.transcribe('tr14.wav', language='ru', word_timestamps=True, beam_size=5)
    out = []
    for s in segs:
        out.append('[%.1f-%.1f] %s' % (s.start + a, s.end + a, s.text.strip()))
        out.append('   ' + ' '.join('%.2f:%s' % (w.start + a, w.word.strip()) for w in s.words))
    open('tr14/%s_%d.txt' % (fid, a), 'w', encoding='utf-8').write('\n'.join(out) + '\n')
    print('done', fid, a, flush=True)
