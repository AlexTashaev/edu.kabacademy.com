# -*- coding: utf-8 -*-
"""Генератор значков курсов МАК (направление A: градиентный диск сайта, без подписей)."""
import os, re, sys, math
OUT=os.path.dirname(os.path.abspath(__file__))
BLUE='#3C5D90'; SKY='#52B0D8'; PURPLE='#A42BB9'; PINK='#D63F8C'

def stroke_g(c, w):
    return f'<g fill="none" stroke="{c}" stroke-width="{w}" stroke-linecap="round" stroke-linejoin="round">'

# --- Часть 1: дерево с логотипа kabacademy.com (svg-symbols.svg#logo-icon-white, viewBox 0 0 21 40) ---
def ic_logo_tree(c='#fff', w=10):
    xml=open(os.path.join(OUT,'logo-icon-white.xml'),encoding='utf-8').read()
    inner=re.sub(r'^<symbol[^>]*>','',xml.strip()); inner=re.sub(r'</symbol>$','',inner)
    inner=inner.replace('fill="#fff"',f'fill="{c}"')
    H=340; s=H/40.0; W=21*s
    return f'<g transform="translate({256-W/2:.2f},{256-H/2:.2f}) scale({s:.4f})">{inner}</g>'

# --- Часть 2: как прежний значок — сфирот в кольце, стрела входит снизу ---
def ic_sefirot_ring(c='#fff', w=10):
    nodes=[(256,372),(256,318),(200,290),(312,290),(256,232),(200,204),(312,204),(200,140),(312,140),(256,110)]
    edges=[(0,1),(1,2),(1,3),(1,4),(2,4),(3,4),(2,5),(3,6),(2,3),(4,5),(4,6),(5,6),(5,7),(6,8),(4,7),(4,8),(7,8),(7,9),(8,9),(5,8),(6,7)]
    s=stroke_g(c,5)
    for a,b in edges: s+=f'<path d="M{nodes[a][0]} {nodes[a][1]} L{nodes[b][0]} {nodes[b][1]}"/>'
    s+='</g>'
    for x,y in nodes: s+=f'<circle cx="{x}" cy="{y}" r="17" fill="none" stroke="{c}" stroke-width="{w}"/>'
    # кольцо с разрывом внизу + стрела вверх
    R=178; cx,cy=256,250
    a1=math.radians(90+20); a2=math.radians(90-20)
    x1,y1=cx+R*math.cos(a1),cy+R*math.sin(a1); x2,y2=cx+R*math.cos(a2),cy+R*math.sin(a2)
    s+=stroke_g(c,w)+f'<path d="M{x1:.1f} {y1:.1f} A{R} {R} 0 1 1 {x2:.1f} {y2:.1f}"/>'
    s+='<path d="M256 448 V402 M228 426 L256 398 L284 426"/></g>'
    return s

def ic_essay(c='#fff', w=10):
    return stroke_g(c,w)+'''<path d="M186 160 H286 L326 200 V352 H186 Z"/><path d="M286 160 V200 H326"/>
<path d="M214 240 H296 M214 272 H296 M214 304 H262"/>
<g transform="rotate(45 346 318)"><rect x="336" y="262" width="20" height="104" rx="5"/><path d="M336 366 L346 388 L356 366"/></g></g>'''

def ic_worlds(c='#fff', w=10):
    return stroke_g(c,w)+'''<circle cx="256" cy="286" r="26"/><circle cx="256" cy="286" r="54"/><circle cx="256" cy="286" r="82"/><circle cx="256" cy="286" r="110"/>
<path d="M256 132 V262 M232 240 L256 264 L280 240"/></g>'''

def ic_book_light(c='#fff', w=10):
    return stroke_g(c,w)+'''<path d="M166 262 Q211 240 256 262 V372 Q211 350 166 372 Z"/><path d="M346 262 Q301 240 256 262 V372 Q301 350 346 372 Z"/>
<path d="M190 288 Q222 276 236 284 M190 316 Q222 304 236 312 M322 288 Q290 276 276 284 M322 316 Q290 304 276 312"/>
<path d="M256 142 V176 M212 156 L228 186 M300 156 L284 186 M186 200 L216 212 M326 200 L296 212"/></g>'''

COURSES=[('part1','Классическая каббала · Часть 1', ic_logo_tree, (SKY,BLUE), 2),
         ('part2','Классическая каббала · Часть 2', ic_sefirot_ring, (BLUE,PURPLE), -8),
         ('essay','Работа с первоисточниками / Эссе', ic_essay, (PURPLE,PINK), 0),
         ('worlds','Спецкурс «Система мироздания»', ic_worlds, ('#2B6CB0',SKY), 0),
         ('book','Тайны Вечной Книги', ic_book_light, ('#7B3FA8',SKY), 0)]

def badge(key,icon,grad,dy):
    c1,c2=grad
    return f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512">
<defs><linearGradient id="g{key}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="{c1}"/><stop offset="1" stop-color="{c2}"/></linearGradient></defs>
<circle cx="256" cy="256" r="246" fill="url(#g{key})"/>
<circle cx="256" cy="256" r="246" fill="none" stroke="#fff" stroke-opacity=".25" stroke-width="4"/>
<circle cx="256" cy="256" r="214" fill="none" stroke="#fff" stroke-opacity=".5" stroke-width="2"/>
<g transform="translate(0,{dy})">{icon()}</g>
</svg>'''

page=['<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial;margin:0;padding:24px;background:#fff;color:#222;width:1300px}.row{display:flex;gap:24px;align-items:flex-end}.b{width:220px;text-align:center;font-size:12px;color:#7A7A7A}.b svg{width:220px;height:220px}.mini svg{width:72px;height:72px}.cap{font-size:12px;color:#7A7A7A;margin-bottom:6px}</style></head><body><div class="row">']
minis=[]
for key,name,icon,grad,dy in COURSES:
    s=badge(key,icon,grad,dy)
    open(os.path.join(OUT,f'badge_{key}.svg'),'w',encoding='utf-8').write(s)
    page.append(f'<div class="b">{s}<div>{name}</div></div>'); minis.append(s)
page.append('<div class="mini" style="padding-bottom:30px"><div class="cap">72px</div><div style="display:flex;gap:8px">'+''.join(minis)+'</div></div></div></body></html>')
open(os.path.join(OUT,'preview.html'),'w',encoding='utf-8').write('\n'.join(page))
print('ok')
