# Transcribe windows of the lesson-9 sources (faster-whisper small, CPU) with word timestamps -> tr9/<unit>.txt
# usage: python -I tr9.py <src_dir> <out_dir> unit:start:end [unit:start:end ...]   (seconds)
import os, subprocess, sys
from faster_whisper import WhisperModel

src, out = sys.argv[1], sys.argv[2]
os.makedirs(out, exist_ok=True)
model = WhisperModel('small', device='cpu', compute_type='int8')
for spec in sys.argv[3:]:
    unit, a, b = spec.split(':')
    a, b = float(a), float(b)
    wav = os.path.join(out, unit + '.wav')
    subprocess.run(['ffmpeg', '-v', 'error', '-y', '-ss', str(a), '-t', str(b - a), '-i', os.path.join(src, unit + '.mp4'),
                    '-ac', '1', '-ar', '16000', wav], check=True)
    segs, _ = model.transcribe(wav, language='ru', word_timestamps=True, vad_filter=False, beam_size=5)
    with open(os.path.join(out, unit + '.txt'), 'w', encoding='utf-8') as fh:
        for s in segs:
            t0, t1 = s.start + a, s.end + a
            fh.write('[%02d:%05.2f-%02d:%05.2f] %s\n' % (t0 // 60, t0 % 60, t1 // 60, t1 % 60, s.text.strip()))
    os.remove(wav)
    print('done', unit, flush=True)
