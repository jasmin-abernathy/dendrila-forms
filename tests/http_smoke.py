#!/usr/bin/env python3
"""End-to-end HTTP smoke test using only Python's standard library."""
import html
import http.cookiejar
import os
import re
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
with socket.socket() as sock:
    sock.bind(("127.0.0.1", 0))
    port = sock.getsockname()[1]
BASE = "http://127.0.0.1:" + str(port)
with tempfile.TemporaryDirectory(prefix="dendrila-http-") as temp:
    env = dict(os.environ, DENDRILA_DATA_DIR=temp)
    server = subprocess.Popen(
        ["php", "-S", "127.0.0.1:" + str(port), "-t", str(ROOT / "public")],
        cwd=ROOT, env=env, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL,
    )
    jar = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))

    def request(path, fields=None):
        data = None if fields is None else urllib.parse.urlencode(fields, doseq=True).encode()
        req = urllib.request.Request(BASE + path, data=data)
        try:
            with opener.open(req, timeout=5) as response:
                return response.status, response.read().decode()
        except urllib.error.HTTPError as exc:
            return exc.code, exc.read().decode()

    def csrf(page):
        match = re.search(r'name="csrf" value="([^"]+)"', page)
        assert match, "Missing CSRF token"
        return html.unescape(match.group(1))

    try:
        for _ in range(70):
            if server.poll() is not None:
                raise RuntimeError("PHP development server stopped")
            try:
                status, page = request("/")
                break
            except (urllib.error.URLError, TimeoutError):
                time.sleep(0.1)
        else:
            raise RuntimeError("PHP development server did not start")
        assert status == 200 and "Dendrila Forms" in page
        status, page = request("/?form=survey-demo")
        assert status == 200 and "Commencer" in page
        token = csrf(page)
        status, page = request("/?form=survey-demo", {"action": "start", "csrf": token})
        assert status == 200 and "Question 1 / 3" in page, "Start failed"
        code = re.search(r"<code>([a-f0-9]{32}\.[a-f0-9]{64})</code>", page)
        assert code, "Missing secure resume code"
        resume_code = code.group(1)
        assert "name=\"csrf\"" in page

        status, page = request("/?form=survey-demo", {
            "action": "next", "question_id": "frequency", "answer": "daily", "csrf": "forged"
        })
        assert status == 200 and "Session expirée" in page, "CSRF was accepted"
        token = csrf(page)
        status, page = request("/?form=survey-demo", {
            "action": "next", "question_id": "frequency", "answer": "daily", "csrf": token
        })
        assert "Question 2 / 4" in page and "simplicité" in page

        token = csrf(page)
        status, page = request("/?form=survey-demo", {
            "action": "next", "question_id": "difficulty", "answer": "4", "csrf": token
        })
        assert "Question 3 / 4" in page
        token = csrf(page)
        status, page = request("/?form=survey-demo", {
            "action": "next", "question_id": "features", "answer[]": ["notes", "tasks"], "csrf": token
        })
        assert "Question 4 / 4" in page
        token = csrf(page)
        status, page = request("/?form=survey-demo", {
            "action": "next", "question_id": "feedback", "answer": "", "csrf": token
        })
        assert "Relire avant l'envoi" in page and "Notes, Tâches" in page
        token = csrf(page)
        status, page = request("/?form=survey-demo", {"action": "leave", "csrf": token})
        assert "Reprendre avec mon code" in page
        token = csrf(page)
        status, page = request("/?form=survey-demo", {
            "action": "resume", "code": resume_code, "csrf": token
        })
        assert "Relire avant l'envoi" in page, "Resume did not preserve answers"
        token = csrf(page)
        status, page = request("/?form=survey-demo", {"action": "submit", "csrf": token})
        assert "Réponses enregistrées" in page
        token = csrf(page)
        status, page = request("/?form=survey-demo", {"action": "delete", "csrf": token})
        assert "Commencer" in page
        status, page = request("/?form[]=invalid")
        assert status == 404 and "Formulaire introuvable" in page

        # Real POST without selected optional checkboxes must not fail.
        # The corresponding value coercion is asserted in smoke.php.
        print("Dendrila Forms : HTTP smoke OK")
    finally:
        server.terminate()
        try:
            server.wait(timeout=5)
        except subprocess.TimeoutExpired:
            server.kill()
