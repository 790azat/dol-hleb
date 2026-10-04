from bs4 import BeautifulSoup
import sys
s=BeautifulSoup(open(sys.argv[1],encoding='utf-8',errors='ignore').read(),'lxml')
links=s.select('a[href*="/magazin/product/"]')
print(len(links))
a=links[0]
card=a
while card is not None and 'product' not in ' '.join(card.get('class') or []).lower(): card=card.parent
print('CARD', card.name, card.get('class'))
for el in card.find_all(True):
    t=el.get_text(' ',strip=True)[:30] if not el.find(True) else ''
    c=(el.get('class') or [''])[0]
    print(el.name, c, (el.get('src') or el.get('href') or '')[:70], t)
