# Word timestamps around a point of an archive file: python -I wordsurl.py fid:start:dur [...]
import subprocess, sys
from faster_whisper import WhisperModel
sys.stdout.reconfigure(encoding='utf-8')
m = WhisperModel('small', device='cpu', compute_type='int8')
for spec in sys.argv[1:]:
    fid, a, d = spec.split(':')
    h = subprocess.run(['curl', '-sI', 'https://cdn.kabbalahmedia.info/%s.mp4' % fid], capture_output=True, text=True).stdout
    loc = [l.split(' ', 1)[1].strip() for l in h.splitlines() if l.lower().startswith('location:')]
    url = loc[-1] if loc else 'https://cdn.kabbalahmedia.info/%s.mp4' % fid
    subprocess.run(['ffmpeg', '-nostdin', '-v', 'error', '-y', '-ss', a, '-i', url, '-t', d, '-vn', '-ac', '1', '-ar', '16000', 'w.wav'], check=True)
    segs, _ = m.transcribe('w.wav', language='ru', word_timestamps=True, beam_size=5)
    print('== %s @%s: %s' % (fid, a, ' '.join('%.1f:%s' % (w.start + float(a), w.word.strip()) for s in segs for w in s.words)))
