# -*- coding: utf-8 -*-
"""Генератор значков курсов МАК (направление A: градиентный диск сайта, без подписей)."""
import os, re, sys, math
OUT=os.path.dirname(os.path.abspath(__file__))
BLUE='#3C5D90'; SKY='#52B0D8'; PURPLE='#A42BB9'; PINK='#D63F8C'

def stroke_g(c, w):
    return f'<g fill="none" stroke="{c}" stroke-width="{w}" stroke-linecap="round" stroke-linejoin="round">'

# --- Часть 1: контурное дерево по мотивам логотипа (крона, земля, корни) ---
def ic_tree_outline(c='#fff', w=10):
    return stroke_g(c,w)+'''<path d="M204 214 A36 36 0 0 1 194 146 A44 44 0 0 1 256 108 A44 44 0 0 1 318 146 A36 36 0 0 1 308 214 Z"/>
<path d="M256 214 V156"/><path d="M256 192 L228 168"/><path d="M256 182 L286 158"/>
<path d="M256 214 V300"/><path d="M160 256 H352"/>
<path d="M256 278 L214 308 L198 340"/><path d="M256 290 L298 318 L314 350"/><path d="M256 304 L236 344"/><path d="M256 308 L280 350"/>
<path d="M214 308 L204 292"/><path d="M298 318 L310 302"/></g>'''

# --- Часть 2: сеть связей (узлы и рёбра) ---
NET_NODES=[(256,146),(338,192),(352,288),(284,358),(190,338),(158,236),(248,254)]
NET_EDGES=[(0,1),(1,2),(2,3),(3,4),(4,5),(5,0),(6,0),(6,1),(6,2),(6,3),(6,4),(6,5),(0,2),(3,5)]
def ic_network(c='#fff', w=10, fill='none'):
    s=stroke_g(c,8)
    for a,b in NET_EDGES: s+=f'<path d="M{NET_NODES[a][0]} {NET_NODES[a][1]} L{NET_NODES[b][0]} {NET_NODES[b][1]}"/>'
    s+='</g>'
    for x,y in NET_NODES: s+=f'<circle cx="{x}" cy="{y}" r="18" fill="{fill}" stroke="{c}" stroke-width="{w}"/>'
    return s

def ic_essay(c='#fff', w=10):
    return stroke_g(c,w)+'''<path d="M186 160 H286 L326 200 V352 H186 Z"/><path d="M286 160 V200 H326"/>
<path d="M214 240 H296 M214 272 H296 M214 304 H262"/>
<g transform="rotate(45 346 318)"><rect x="336" y="262" width="20" height="104" rx="5"/><path d="M336 366 L346 388 L356 366"/></g></g>'''

def ic_worlds(c='#fff', w=10):
    return stroke_g(c,w)+'''<circle cx="256" cy="286" r="26"/><circle cx="256" cy="286" r="54"/><circle cx="256" cy="286" r="82"/><circle cx="256" cy="286" r="110"/>
<path d="M256 132 V262 M232 240 L256 264 L280 240"/></g>'''

def ic_worlds_cone(c='#fff', w=10):
    # точка наверху, свет расходится конусом, четыре мира-ступени
    return stroke_g(c,w)+'''<circle cx="256" cy="134" r="12" fill="'''+c+'''"/>
<path d="M256 150 L146 372 M256 150 L366 372"/>
<path d="M228 206 H284 M209 246 H303 M190 286 H322 M171 326 H341"/></g>'''

def ic_worlds_arcs(c='#fff', w=10):
    # точка и четыре расходящихся от неё дуги-мира
    s=stroke_g(c,w)+f'<circle cx="256" cy="136" r="12" fill="{c}"/>'
    for r in (56,104,152,200):
        s+=f'<path d="M{256-r*0.78:.1f} {136+r*0.62:.1f} A{r} {r} 0 0 0 {256+r*0.78:.1f} {136+r*0.62:.1f}"/>'
    return s+'</g>'

def ic_book_light(c='#fff', w=10):
    return stroke_g(c,w)+'''<path d="M166 262 Q211 240 256 262 V372 Q211 350 166 372 Z"/><path d="M346 262 Q301 240 256 262 V372 Q301 350 346 372 Z"/>
<path d="M190 288 Q222 276 236 284 M190 316 Q222 304 236 312 M322 288 Q290 276 276 284 M322 316 Q290 304 276 312"/>
<path d="M256 142 V176 M212 156 L228 186 M300 156 L284 186 M186 200 L216 212 M326 200 L296 212"/></g>'''

COURSES=[('part1','Классическая каббала · Часть 1', ic_tree_outline, (SKY,BLUE), 26),
         ('part2','Классическая каббала · Часть 2', ic_network, (BLUE,PURPLE), 4),
         ('essay','Работа с первоисточниками / Эссе', ic_essay, (PURPLE,PINK), 0),
         ('worlds','Спецкурс: 4 круга (было)', ic_worlds, ('#2B6CB0',SKY), 0),
         ('worlds2','Спецкурс: точка и конус света, 4 мира', ic_worlds_cone, ('#2B6CB0',SKY), 0),
         ('worlds3','Спецкурс: точка и 4 расходящихся мира', ic_worlds_arcs, ('#2B6CB0',SKY), 0),
         ('book','Тайны Вечной Книги', ic_book_light, ('#7B3FA8',SKY), 0)]

def badge(key,icon,grad,dy):
    c1,c2=grad
    return f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512">
<defs><linearGradient id="g{key}" gradientUnits="userSpaceOnUse" x1="10" y1="10" x2="502" y2="502"><stop offset="0" stop-color="{c1}"/><stop offset="1" stop-color="{c2}"/></linearGradient></defs>
<circle cx="256" cy="256" r="246" fill="url(#g{key})"/>
<circle cx="256" cy="256" r="246" fill="none" stroke="#fff" stroke-opacity=".25" stroke-width="4"/>
<circle cx="256" cy="256" r="214" fill="none" stroke="#fff" stroke-opacity=".5" stroke-width="2"/>
<g transform="translate(0,{dy})">{icon(fill=f'url(#g{key})') if key=='part2' else icon()}</g>
</svg>'''

page=['<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial;margin:0;padding:24px;background:#fff;color:#222;width:1750px}.row{display:flex;gap:24px;align-items:flex-end}.b{width:200px;text-align:center;font-size:12px;color:#7A7A7A}.b svg{width:200px;height:200px}.mini svg{width:72px;height:72px}.cap{font-size:12px;color:#7A7A7A;margin-bottom:6px}</style></head><body><div class="row">']
minis=[]
for key,name,icon,grad,dy in COURSES:
    s=badge(key,icon,grad,dy)
    open(os.path.join(OUT,f'badge_{key}.svg'),'w',encoding='utf-8').write(s)
    page.append(f'<div class="b">{s}<div>{name}</div></div>'); minis.append(s)
page.append('<div class="mini" style="padding-bottom:30px"><div class="cap">72px</div><div style="display:flex;gap:8px">'+''.join(minis)+'</div></div></div></body></html>')
open(os.path.join(OUT,'preview.html'),'w',encoding='utf-8').write('\n'.join(page))
print('ok')
