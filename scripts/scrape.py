#!/usr/bin/env python3
"""Сборщик контента со старого сайта dol-hleb.ru (Megagroup / shop2).

Собирает категории, товары, страницы и фотографии в database/data/catalog.json
и public/images/. Запускается GitHub Action'ом (.github/workflows/scrape.yml),
потому что старый сайт отдаёт страницы медленно и не закрывает соединение.

    python3 scripts/scrape.py [--limit-folders N] [--no-images]
"""
import argparse
import concurrent.futures as cf
import hashlib
import io
import json
import os
import re
import sys
import time
import subprocess
import tempfile
from urllib.parse import urljoin, urlparse, unquote

from bs4 import BeautifulSoup

BASE = "https://dol-hleb.ru"
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT_JSON = os.path.join(ROOT, "database", "data", "catalog.json")
IMG_DIR = os.path.join(ROOT, "public", "images")
UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126 Safari/537.36"

PAGES = {
    "o-nas": "О нас",
    "otzyvy": "Отзывы",
    "dostavka": "Доставка",
    "oplata": "Оплата",
    "aktsii": "Акции",
    "kontakty": "Контакты",
}

def fetch(url, binary=False, timeout=75):
    """Старый сайт по HTTP/1.1 «зависает» посреди страницы, а по HTTP/2 отдаёт
    её целиком, но не закрывает поток. Поэтому качаем curl'ом (HTTP/2, gzip)
    во временный файл и обрываем загрузку, как только пришёл </html>."""
    for attempt in range(3):
        fd, tmp = tempfile.mkstemp()
        os.close(fd)
        cmd = ["curl", "-sL", "--compressed", "--http2", "-A", UA, "-m", str(timeout), "-o", tmp, url]
        if binary:
            cmd[1:1] = ["-f"]
        proc = subprocess.Popen(cmd)
        started = time.time()
        try:
            while proc.poll() is None and time.time() - started < timeout + 5:
                time.sleep(0.4)
                if not binary and os.path.getsize(tmp) > 1000:
                    with open(tmp, "rb") as fh:
                        fh.seek(max(0, os.path.getsize(tmp) - 4000))
                        if b"</html>" in fh.read():
                            break
            if proc.poll() is None:
                proc.kill()
                proc.wait()
            with open(tmp, "rb") as fh:
                data = fh.read()
        finally:
            os.unlink(tmp)
        if binary:
            # 28 = таймаут curl: файл мог прийти целиком, битые картинки отсеет Pillow
            if proc.returncode in (0, 28) and data:
                return data
        elif b"</html>" in data[-4000:]:
            return data.decode("utf-8", errors="ignore")
        print(f"  ! {url}: attempt {attempt + 1} failed ({len(data)} bytes)", file=sys.stderr)
        time.sleep(2 + attempt * 3)
    return None


def soup(html):
    return BeautifulSoup(html, "lxml")


def clean_text(el):
    if el is None:
        return ""
    txt = el.get_text("\n", strip=True)
    return re.sub(r"\n{3,}", "\n\n", txt)


def clean_html(el):
    """Чистый HTML описания: только простые теги, без стилей."""
    if el is None:
        return ""
    el = BeautifulSoup(str(el), "lxml")
    for bad in el.select("script,style,iframe,form,button,input,noscript"):
        bad.decompose()
    allowed = {"p", "br", "ul", "ol", "li", "b", "strong", "i", "em", "h2", "h3", "h4", "table", "tr", "td", "th", "tbody"}
    for tag in el.find_all(True):
        if tag.name in ("html", "body"):
            continue
        if tag.name not in allowed:
            tag.unwrap()
        else:
            tag.attrs = {}
    body = el.body or el
    html = "".join(str(c) for c in body.contents).strip()
    html = re.sub(r"(<br/?>\s*){3,}", "<br><br>", html)
    html = re.sub(r"<p>\s*</p>", "", html)
    return html


def slug_from(url, kind):
    m = re.search(rf"/magazin/{kind}/([^/?#]+)", url)
    return unquote(m.group(1)) if m else None


def original_image(url):
    """/thumb/2/HASH/450r450/d/file.jpg -> /d/file.jpg (оригинал)."""
    if not url or "spacer.gif" in url:
        return None
    url = urljoin(BASE, url)
    m = re.search(r"/thumb/[^/]+/[^/]+/[^/]+/d/(.+)$", url)
    if m:
        return f"{BASE}/d/{m.group(1)}"
    return url


# ---------------------------------------------------------------- категории

def parse_folders(home):
    s = soup(home)
    folders, seen = [], set()
    for a in s.select('a[href*="/magazin/folder/"]'):
        slug = slug_from(a["href"], "folder")
        name = a.get_text(" ", strip=True)
        if not slug or not name or slug in seen:
            continue
        # родитель: ближайший li выше, у которого есть своя ссылка на папку
        parent = None
        li = a.find_parent("li")
        up = li.find_parent("li") if li else None
        if up:
            pa = up.find("a", href=re.compile(r"/magazin/folder/"))
            if pa:
                parent = slug_from(pa["href"], "folder")
        seen.add(slug)
        folders.append({"slug": slug, "name": name, "parent": parent, "position": len(folders)})
    return folders


def folder_pages(slug):
    """Все страницы пагинации папки."""
    url = f"{BASE}/magazin/folder/{slug}"
    html = fetch(url)
    if not html:
        return []
    pages = [html]
    s = soup(html)
    nums = set()
    for a in s.select(f'a[href*="/magazin/folder/{slug}/p/"]'):
        m = re.search(r"/p/(\d+)", a["href"])
        if m:
            nums.add(int(m.group(1)))
    if nums:
        for n in range(1, max(nums) + 1):
            h = fetch(f"{url}/p/{n}")
            if h:
                pages.append(h)
    return pages


def parse_cards(html):
    s = soup(html)
    items = []
    for card in s.select(".shop2-product-item, .product-item"):
        a = card.select_one('a[href*="/magazin/product/"]')
        if not a:
            continue
        slug = slug_from(a["href"], "product")
        name = ""
        for link in card.select('a[href*="/magazin/product/"]'):
            name = link.get_text(" ", strip=True)
            if name:
                break
        img = card.select_one("img")
        img_url = original_image((img.get("data-src") or img.get("src")) if img else None)
        price = parse_price(card)
        items.append({"slug": slug, "name": name, "price": price, "image": img_url})
    return items


def parse_price(el):
    p = el.select_one(".price-current strong") or el.select_one(".price-current") or el.select_one("[class*=price] strong")
    if not p:
        return None
    digits = re.sub(r"[^\d,.]", "", p.get_text()).replace(",", ".")
    try:
        return float(digits) if digits else None
    except ValueError:
        return None


# ------------------------------------------------------------------ товары

def parse_product(slug):
    html = fetch(f"{BASE}/magazin/product/{slug}")
    if not html:
        return None
    s = soup(html)
    main = s.select_one("main") or s
    h1 = main.select_one("h1")
    images = []
    for el in main.select("a[href], img"):
        u = el.get("href") if el.name == "a" else (el.get("data-src") or el.get("src"))
        if not u or not re.search(r"\.(jpe?g|png|webp|gif)$", u, re.I):
            continue
        if "/d/" not in u:
            continue
        o = original_image(u)
        if o and o not in images:
            images.append(o)
    # Пары «параметр: значение» (вес, начинка и т.п.)
    params = []
    for row in main.select(".shop2-product-params tr, .product-params tr, .option-item"):
        t = row.select_one("th, .option-title")
        v = row.select_one("td, .option-body")
        if t and v:
            k, val = t.get_text(" ", strip=True), v.get_text(" ", strip=True)
            if k and val and len(val) < 300 and {"name": k, "value": val} not in params:
                params.append({"name": k, "value": val})
    desc_el = (
        main.select_one("#shop2-tabs-2")
        or main.select_one(".shop2-product-desc")
        or main.select_one(".product-desc")
        or main.select_one("[id^=shop2-tabs] .text")
        or main.select_one(".shop2-product__desc")
    )
    note = main.select_one(".gr-product-anonce, .product-anonce, .shop2-product-anonce")
    return {
        "slug": slug,
        "name": h1.get_text(" ", strip=True) if h1 else slug,
        "price": parse_price(main),
        "images": images[:6],
        "params": params[:12],
        "description": clean_html(desc_el),
        "anons": clean_text(note),
        "is_new": bool(main.select_one(".product-label .new, .new-label, [class*=label-new]")),
    }


# ---------------------------------------------------------------- страницы

def parse_page(slug, title):
    html = fetch(f"{BASE}/{slug}")
    if not html:
        return None
    s = soup(html)
    main = s.select_one("main") or s
    body = main.select_one(".site-main__inner") or main
    for bad in body.select("h1, .site-path, .page-path, nav, form, script, style"):
        bad.decompose()
    images = [original_image(i.get("data-src") or i.get("src")) for i in body.select("img")]
    return {
        "slug": slug,
        "title": title,
        "html": clean_html(body),
        "images": [i for i in images if i and "/d/" in i][:10],
    }


# --------------------------------------------------------------- картинки

def image_path(url, prefix):
    name = os.path.basename(urlparse(url).path)
    stem = re.sub(r"[^a-z0-9_-]+", "-", os.path.splitext(name)[0].lower()).strip("-")[:50] or "img"
    h = hashlib.md5(url.encode()).hexdigest()[:6]
    return f"{prefix}/{stem}-{h}.webp"


def save_image(url, rel):
    from PIL import Image

    dest = os.path.join(IMG_DIR, rel)
    if os.path.exists(dest):
        return True
    data = fetch(url, binary=True, timeout=90)
    if not data:
        return False
    try:
        im = Image.open(io.BytesIO(data))
        im = im.convert("RGB")
        im.thumbnail((1200, 1200))
        os.makedirs(os.path.dirname(dest), exist_ok=True)
        im.save(dest, "WEBP", quality=80, method=6)
        return True
    except Exception as e:  # битые файлы просто пропускаем
        print(f"  ! image {url}: {e}", file=sys.stderr)
        return False


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--limit-folders", type=int, default=0)
    ap.add_argument("--no-images", action="store_true")
    ap.add_argument("--workers", type=int, default=8)
    args = ap.parse_args()

    print("home…")
    home = fetch(BASE + "/")
    folders = parse_folders(home)
    if args.limit_folders:
        folders = folders[: args.limit_folders]
    print(f"{len(folders)} folders")

    products = {}
    with cf.ThreadPoolExecutor(args.workers) as ex:
        pages_by_folder = dict(zip([f["slug"] for f in folders], ex.map(lambda f: folder_pages(f["slug"]), folders)))
    for f in folders:
        pages = pages_by_folder.get(f["slug"]) or []
        if pages:
            d = soup(pages[0]).select_one(".shop2-folder-desc, .folder-desc, .shop2-group-desc")
            f["description"] = clean_html(d)
        for html in pages:
            for c in parse_cards(html):
                p = products.setdefault(c["slug"], {**c, "folders": []})
                if f["slug"] not in p["folders"]:
                    p["folders"].append(f["slug"])
        print(f"  {f['slug']}: {sum(1 for p in products.values() if f['slug'] in p['folders'])} products")

    print(f"{len(products)} products, fetching details…")
    with cf.ThreadPoolExecutor(args.workers) as ex:
        details = dict(zip(products.keys(), ex.map(parse_product, products.keys())))
    for slug, d in details.items():
        if d:
            base = products[slug]
            products[slug] = {**base, **{k: v for k, v in d.items() if v not in (None, "", [])}}
            if not products[slug].get("images") and base.get("image"):
                products[slug]["images"] = [base["image"]]

    pages = [p for p in (parse_page(s, t) for s, t in PAGES.items()) if p]

    # Логотип и баннеры с главной
    hs = soup(home)
    logo = hs.select_one(".site-logo img, .logo img, header img")
    site = {
        "logo": original_image((logo.get("data-src") or logo.get("src")) if logo else None),
        "banners": [],
    }
    for img in hs.select(".slider img, .swiper img, [class*=slider] img, [class*=banner] img"):
        u = original_image(img.get("data-src") or img.get("src"))
        if u and "/d/" in u and u not in site["banners"]:
            site["banners"].append(u)

    # Картинки -> public/images/*, в JSON кладём локальные пути
    jobs = []
    for p in products.values():
        local = []
        for u in p.get("images") or []:
            rel = image_path(u, "products")
            jobs.append((u, rel))
            local.append(rel)
        p["images_local"] = local
        p["source_images"] = p.pop("images", [])
        p.pop("image", None)
    for pg in pages:
        pg["images_local"] = []
        for u in pg["images"]:
            rel = image_path(u, "pages")
            jobs.append((u, rel))
            pg["images_local"].append(rel)
    if site["logo"]:
        site["logo_local"] = image_path(site["logo"], "site")
        jobs.append((site["logo"], site["logo_local"]))
    site["banners_local"] = []
    for u in site["banners"][:8]:
        rel = image_path(u, "banners")
        jobs.append((u, rel))
        site["banners_local"].append(rel)

    ok = set()
    if not args.no_images:
        print(f"{len(jobs)} images…")
        with cf.ThreadPoolExecutor(args.workers) as ex:
            for (u, rel), res in zip(jobs, ex.map(lambda j: save_image(*j), jobs)):
                if res:
                    ok.add(rel)
        for p in products.values():
            p["images_local"] = [r for r in p["images_local"] if r in ok]
        for pg in pages:
            pg["images_local"] = [r for r in pg["images_local"] if r in ok]
        site["banners_local"] = [r for r in site["banners_local"] if r in ok]
        if site.get("logo_local") not in ok:
            site["logo_local"] = None

    os.makedirs(os.path.dirname(OUT_JSON), exist_ok=True)
    with open(OUT_JSON, "w", encoding="utf-8") as fh:
        json.dump(
            {"folders": folders, "products": list(products.values()), "pages": pages, "site": site},
            fh, ensure_ascii=False, indent=1,
        )
    print(f"done: {len(folders)} folders, {len(products)} products, {len(pages)} pages, {len(ok)} images")


if __name__ == "__main__":
    main()
