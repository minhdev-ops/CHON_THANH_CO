#!/usr/bin/env python3
import time
import subprocess
import os
import json
from datetime import datetime

LOG_FILE = os.path.expanduser("~/1.Work/03.Project/2026/CHON_THANH_CO/CHON_THANH_CO/.gemini/tasks/coordinator.log")
os.makedirs(os.path.dirname(LOG_FILE), exist_ok=True)

WORKERS = {
    "ningyo": {
        "handle": "term_eee635d8-6a3f-48a0-a12c-a57878e4d6b2",
        "last_prompt": "",
        "state": "init"
    },
    "periwinkle": {
        "handle": "term_8fa44c9f-0b75-4587-9ad7-7bcd2fc7450f",
        "last_prompt": "",
        "state": "init"
    },
    "spookfish": {
        "handle": "term_6f438009-c8ee-4d88-a8c9-3bef42e8a29e",
        "last_prompt": "",
        "state": "init"
    }
}

def log(msg):
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    entry = f"[{timestamp}] {msg}\n"
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(entry)
    print(entry, end="")

def read_terminal(handle):
    try:
        res = subprocess.run(
            ["orca", "terminal", "read", "--terminal", handle, "--limit", "30"],
            capture_output=True,
            text=True,
            timeout=15
        )
        return res.stdout
    except Exception as e:
        return f"Error reading terminal: {e}"

def send_terminal(handle, text):
    try:
        res = subprocess.run(
            ["orca", "terminal", "send", "--terminal", handle, "--text", text, "--enter"],
            capture_output=True,
            text=True,
            timeout=15
        )
        return res.returncode == 0
    except Exception as e:
        log(f"Failed to send to {handle}: {e}")
        return False

log("=== AUTONOMOUS ORCA COORDINATOR DAEMON STARTED ===")

iteration = 0
while True:
    iteration += 1
    log(f"--- Supervision Loop Cycle #{iteration} ---")
    
    # 1. Check Ningyo
    ningyo_out = read_terminal(WORKERS["ningyo"]["handle"])
    if "could not find driver" in ningyo_out and "sqlite" in ningyo_out.lower() and WORKERS["ningyo"]["state"] != "switch_mysql_sent":
        prompt = "Chuyen DB_CONNECTION sang mysql trong phpunit.xml va chay lai php artisan test"
        log(f"Ningyo encountered SQLite driver error. Auto-dispatching: {prompt}")
        send_terminal(WORKERS["ningyo"]["handle"], prompt)
        WORKERS["ningyo"]["state"] = "switch_mysql_sent"
    elif "passed" in ningyo_out.lower() and WORKERS["ningyo"]["state"] == "switch_mysql_sent":
        log("Ningyo: Tests passed! Dispatching Pint style check...")
        prompt = "cd CHON_THANH_CO && vendor/bin/pint --test"
        send_terminal(WORKERS["ningyo"]["handle"], prompt)
        WORKERS["ningyo"]["state"] = "pint_verify"

    # 2. Check Periwinkle
    periwinkle_out = read_terminal(WORKERS["periwinkle"]["handle"])
    if "Build completed successfully" in periwinkle_out and WORKERS["periwinkle"]["state"] == "init":
        prompt = "Kiem tra cac trang Vue de xac nhan khong con component translation-section va chay lai npm run build"
        log(f"Periwinkle completed previous build. Auto-dispatching: {prompt}")
        send_terminal(WORKERS["periwinkle"]["handle"], prompt)
        WORKERS["periwinkle"]["state"] = "vue_clean_check"
    elif "built in" in periwinkle_out.lower() and WORKERS["periwinkle"]["state"] == "vue_clean_check":
        prompt = "Kiem tra sitemap va robots.txt trong thu muc public"
        log(f"Periwinkle build verified. Auto-dispatching SEO check: {prompt}")
        send_terminal(WORKERS["periwinkle"]["handle"], prompt)
        WORKERS["periwinkle"]["state"] = "seo_check"

    # 3. Check Spookfish
    spookfish_out = read_terminal(WORKERS["spookfish"]["handle"])
    if ("Permission required" in spookfish_out or "enter confirm" in spookfish_out) and WORKERS["spookfish"]["state"] != "unblocked":
        log("Spookfish is waiting for input or terminal confirmation.")

    time.sleep(15)
