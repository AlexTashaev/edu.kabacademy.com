# Transcribe short pieces of local clips: python -I chk.py file:start:dur [...]
import subprocess, sys, os
from faster_whisper import WhisperModel
m = WhisperModel('small', device='cpu', compute_type='int8')
sys.stdout.reconfigure(encoding='utf-8')
for spec in sys.argv[1:]:
    f, a, d = spec.rsplit(':', 2)
    subprocess.run(['ffmpeg', '-nostdin', '-v', 'error', '-y', '-ss', a, '-i', f, '-t', d, '-vn', '-ac', '1', '-ar', '16000', 'chk.wav'], check=True)
    segs, _ = m.transcribe('chk.wav', language='ru', beam_size=5)
    print('== %s @%s+%s: %s' % (os.path.basename(f), a, d, ' '.join(s.text.strip() for s in segs)))
os.remove('chk.wav')
