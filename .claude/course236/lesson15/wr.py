# words of tr12/<file> between a and b: python -I wr.py file a b
import re, sys
sys.stdout.reconfigure(encoding='utf-8')
f, a, b = sys.argv[1], float(sys.argv[2]), float(sys.argv[3])
ws = [(float(t), w) for line in open(f, encoding='utf-8') if line.startswith('   ') for t, w in re.findall(r'(\d+\.\d+):(\S+)', line)]
print(' '.join('%.2f:%s' % (t, w) for t, w in ws if a <= t <= b))
