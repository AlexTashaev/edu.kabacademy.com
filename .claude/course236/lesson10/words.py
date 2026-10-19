# Word timestamps of a short audio window: python -I words.py <wav> <offset_s>
import sys
from faster_whisper import WhisperModel
sys.stdout.reconfigure(encoding='utf-8')
m = WhisperModel('small', device='cpu', compute_type='int8')
segs, _ = m.transcribe(sys.argv[1], language='ru', word_timestamps=True, beam_size=5)
off = float(sys.argv[2])
print(' '.join('%.2f:%s' % (w.start + off, w.word.strip()) for s in segs for w in s.words))
