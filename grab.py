import requests
import re
import time
from bs4 import BeautifulSoup
from datetime import datetime
from colorama import Fore, init

init(autoreset=True)

# Format: source_name : (base_url, url_mode)
# url_mode: "query", "archive_path", "direct_path"
base_sources = {
    "haxor.id": ("https://haxor.id", "query"),
    "defacer.net": ("https://defacer.net", "direct_path"),
    "defacer.id": ("https://defacer.id", "archive_path"),
}

paths = ["/archive", "/onhold", "/special"]
headers = {"User-Agent": "Mozilla/5.0"}
delay = 2

def get_domains_from_html(content):
    soup = BeautifulSoup(content, "html.parser")
    domains = set()
    for td in soup.find_all("td"):
        match = re.search(r"(https?://)?([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})", td.text)
        if match:
            domains.add(match.group(2))
    return domains

def auto_grab(base_url, path, mode, max_page):
    all_domains = set()
    for page in range(1, max_page + 1):
        if mode == "query":
            if path == "/archive":
                url = f"{base_url}/archive?page={page}"
            else:
                url = f"{base_url}/archive{path}?page={page}"

        elif mode == "archive_path":
            if path == "/archive":
                url = f"{base_url}/archive/{page}"
            else:
                url = f"{base_url}/archive{path}/{page}"

        elif mode == "direct_path":
            if path == "/archive":
                url = f"{base_url}/archive/{page}"
            else:
                url = f"{base_url}{path}/{page}"

        else:
            continue

        print(f"{Fore.YELLOW}  └─ Grabbing {url}")
        try:
            r = requests.get(url, headers=headers, timeout=10)
            if r.status_code != 200 or "No records found" in r.text or "data not found" in r.text:
                break

            domains = get_domains_from_html(r.text)
            if not domains:
                break

            all_domains.update(domains)
            time.sleep(delay)

        except Exception as e:
            print(f"{Fore.RED}  └─ Error: {e}")
            break

    return all_domains

def grab_all_sources():
    print(f"{Fore.CYAN}[*] Starting Grabber - ALL SOURCES & PATHS")
    try:
        max_page = int(input(f"{Fore.YELLOW}[?] Max page to grab (ex: 10): "))
    except:
        print(f"{Fore.RED}Invalid input.")
        return

    total_domains = set()

    for source, (base_url, mode) in base_sources.items():
        for path in paths:
            print(f"{Fore.GREEN}\n[+] {source} - {path}")
            domains = auto_grab(base_url, path, mode, max_page)
            total_domains.update(domains)

    save_result(total_domains)

def save_result(domains):
    now = datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
    filename = f"grabber_{now}.txt"
    with open(filename, "w") as f:
        for domain in sorted(domains):
            f.write(domain + "\n")
    print(f"\n{Fore.GREEN}[✓] Total: {len(domains)} domains saved to {filename}")

def menu():
    print(f"""{Fore.RED}
╭──>> {Fore.WHITE}Multi-Source Domain Grabber
│  {Fore.YELLOW}Created by RehanHaxor
│────────────────────────────
│({Fore.YELLOW}1{Fore.RED}). => {Fore.WHITE}Grabber All Pages
│({Fore.YELLOW}0{Fore.RED}). => {Fore.WHITE}Exit
╰────────────────────────────""")

    choice = input(f"{Fore.YELLOW}[ pilih menu ]~# ")
    if choice == "1":
        grab_all_sources()
    elif choice == "0":
        print(f"{Fore.CYAN}Exiting...")
        exit()
    else:
        print(f"{Fore.RED}Invalid choice!")

if __name__ == "__main__":
    while True:
        menu()
        print(f"\n{Fore.CYAN}Returning to main menu...\n")