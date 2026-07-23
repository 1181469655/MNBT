#!/usr/bin/env python3
# coding: utf-8

import logging
import logging.handlers
import os
import re
import sqlite3
import time
from datetime import datetime


BASE_DIR = os.path.dirname(os.path.abspath(__file__))
STATS_DB_PATH = os.path.join(BASE_DIR, "maxiaole.db")

_stats_logger_inited = False


def get_stats_logger():
    global _stats_logger_inited
    logger = logging.getLogger("mnbt_stats")
    if _stats_logger_inited:
        return logger
    logger.setLevel(logging.DEBUG)
    logger.propagate = True
    root_logger = logging.getLogger()
    if not root_logger.handlers and not logger.handlers:
        try:
            log_path = os.path.join(BASE_DIR, "worker.log")
            handler = logging.handlers.RotatingFileHandler(
                log_path,
                maxBytes=10 * 1024 * 1024,
                backupCount=5,
                encoding="utf-8",
            )
            handler.setLevel(logging.INFO)
            fmt = logging.Formatter(
                "%(asctime)s [%(levelname)s] %(message)s",
                datefmt="%Y-%m-%d %H:%M:%S",
            )
            handler.setFormatter(fmt)
            root_logger.addHandler(handler)
            root_logger.setLevel(logging.DEBUG)
        except Exception:
            pass
    _stats_logger_inited = True
    return logger


def log_info(msg, *args):
    get_stats_logger().info(msg, *args)


def log_warn(msg, *args):
    get_stats_logger().warning(msg, *args)


def log_error(msg, *args):
    get_stats_logger().error(msg, *args)
LOG_DIR = os.path.join(BASE_DIR, "../wwwlogs") if os.path.isdir(os.path.join(BASE_DIR, "../wwwlogs")) else "/www/wwwlogs"

SPIDER_AGENTS = {
    "Baiduspider", "Googlebot", "bingbot", "YandexBot", "Sogou",
    "360Spider", "Bytespider", "Amazonbot", "AhrefsBot", "SemrushBot",
    "DotBot", "BLEXBot", "Exabot", "MJ12bot", "SeznamBot",
}

HOUR_DATE_RE = re.compile(r'(\d+)/(\w+)/(\d+):(\d+):(\d+):(\d+)')

MONTH_MAP = {
    "Jan":1,"Feb":2,"Mar":3,"Apr":4,"May":5,"Jun":6,
    "Jul":7,"Aug":8,"Sep":9,"Oct":10,"Nov":11,"Dec":12,
}


def now_text():
    return datetime.now().strftime("%Y-%m-%d %H:%M:%S")


def init_stats_db():
    conn = sqlite3.connect(STATS_DB_PATH)
    conn.execute("PRAGMA journal_mode=WAL")
    conn.execute("PRAGMA synchronous=NORMAL")
    cursor = conn.cursor()
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS log_position (
            site_name TEXT PRIMARY KEY NOT NULL,
            inode REAL NOT NULL DEFAULT 0,
            size INTEGER NOT NULL DEFAULT 0,
            mtime REAL NOT NULL DEFAULT 0,
            updated_at TEXT NOT NULL
        )
    """)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS site_hourly_stats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_name TEXT NOT NULL,
            hour TEXT NOT NULL,
            pv INTEGER NOT NULL DEFAULT 0,
            uv INTEGER NOT NULL DEFAULT 0,
            total_bytes INTEGER NOT NULL DEFAULT 0,
            UNIQUE(site_name, hour)
        )
    """)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS site_uri_stats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_name TEXT NOT NULL,
            date TEXT NOT NULL,
            uri TEXT NOT NULL,
            request_count INTEGER NOT NULL DEFAULT 0,
            total_bytes INTEGER NOT NULL DEFAULT 0,
            UNIQUE(site_name, date, uri)
        )
    """)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS site_ip_stats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_name TEXT NOT NULL,
            date TEXT NOT NULL,
            ip TEXT NOT NULL,
            request_count INTEGER NOT NULL DEFAULT 0,
            total_bytes INTEGER NOT NULL DEFAULT 0,
            UNIQUE(site_name, date, ip)
        )
    """)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS site_spider_stats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_name TEXT NOT NULL,
            date TEXT NOT NULL,
            spider_name TEXT NOT NULL,
            request_count INTEGER NOT NULL DEFAULT 0,
            UNIQUE(site_name, date, spider_name)
        )
    """)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS site_client_stats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_name TEXT NOT NULL,
            date TEXT NOT NULL,
            client_type TEXT NOT NULL,
            client_name TEXT NOT NULL DEFAULT '',
            request_count INTEGER NOT NULL DEFAULT 0,
            UNIQUE(site_name, date, client_type, client_name)
        )
    """)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS site_status_stats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_name TEXT NOT NULL,
            date TEXT NOT NULL,
            status_code INTEGER NOT NULL,
            request_count INTEGER NOT NULL DEFAULT 0,
            total_bytes INTEGER NOT NULL DEFAULT 0,
            UNIQUE(site_name, date, status_code)
        )
    """)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS site_method_stats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_name TEXT NOT NULL,
            date TEXT NOT NULL,
            method TEXT NOT NULL,
            request_count INTEGER NOT NULL DEFAULT 0,
            UNIQUE(site_name, date, method)
        )
    """)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS site_error_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_name TEXT NOT NULL,
            date TEXT NOT NULL DEFAULT '',
            time_local TEXT NOT NULL,
            ip TEXT NOT NULL,
            method TEXT NOT NULL,
            uri TEXT NOT NULL,
            status INTEGER NOT NULL,
            bytes INTEGER NOT NULL DEFAULT 0,
            referer TEXT NOT NULL DEFAULT '',
            ua TEXT NOT NULL DEFAULT ''
        )
    """)
    try:
        cursor.execute("ALTER TABLE site_error_logs ADD COLUMN date TEXT NOT NULL DEFAULT ''")
    except sqlite3.OperationalError:
        pass
    cursor.execute("CREATE INDEX IF NOT EXISTS idx_hourly_site ON site_hourly_stats(site_name)")
    cursor.execute("CREATE INDEX IF NOT EXISTS idx_uri_site ON site_uri_stats(site_name, date)")
    cursor.execute("CREATE INDEX IF NOT EXISTS idx_ip_site ON site_ip_stats(site_name, date)")
    cursor.execute("CREATE INDEX IF NOT EXISTS idx_spider_site ON site_spider_stats(site_name, date)")
    cursor.execute("CREATE INDEX IF NOT EXISTS idx_client_site ON site_client_stats(site_name, date)")
    cursor.execute("CREATE INDEX IF NOT EXISTS idx_status_site ON site_status_stats(site_name, date)")
    cursor.execute("CREATE INDEX IF NOT EXISTS idx_method_site ON site_method_stats(site_name, date)")
    cursor.execute("CREATE INDEX IF NOT EXISTS idx_error_site ON site_error_logs(site_name, status)")
    cursor.execute("CREATE INDEX IF NOT EXISTS idx_error_date ON site_error_logs(site_name, date)")
    # 清理 60 天前的统计数据
    cutoff = time.strftime("%Y-%m-%d", time.localtime(time.time() - 60 * 86400))
    for table in ("site_uri_stats", "site_ip_stats",
                  "site_spider_stats", "site_client_stats", "site_status_stats",
                  "site_method_stats", "site_error_logs"):
        cursor.execute(f"DELETE FROM {table} WHERE date<?", (cutoff,))
    # site_hourly_stats 用 hour 列（格式 YYYY-MM-DD HH）
    hour_cutoff = cutoff + " 00"
    cursor.execute("DELETE FROM site_hourly_stats WHERE hour<?", (hour_cutoff,))
    conn.commit()
    conn.close()


def ts_to_hour_key(time_local):
    m = HOUR_DATE_RE.match(time_local)
    if not m:
        return None, None
    day, mon_str, year, hour = m.group(1), m.group(2), m.group(3), m.group(4)
    mon = MONTH_MAP.get(mon_str)
    if not mon:
        return None, None
    hour_key = f"{year}-{mon:02d}-{int(day):02d} {int(hour):02d}"
    date_key = f"{year}-{mon:02d}-{int(day):02d}"
    return hour_key, date_key


def detect_spider(ua):
    if not ua:
        return None
    for name in SPIDER_AGENTS:
        if name.lower() in ua.lower():
            return name
    return None


def detect_client(ua):
    if not ua:
        return "unknown", "unknown"
    ua_lower = ua.lower()
    mobile = any(k in ua_lower for k in ("mobile", "android", "iphone", "ipad", "ipod"))
    client_type = "mobile" if mobile else "pc"
    if "chrome" in ua_lower and "edge" not in ua_lower:
        client_name = "Chrome"
    elif "firefox" in ua_lower:
        client_name = "Firefox"
    elif "safari" in ua_lower and "chrome" not in ua_lower:
        client_name = "Safari"
    elif "edge" in ua_lower:
        client_name = "Edge"
    elif "msie" in ua_lower or "trident" in ua_lower:
        client_name = "IE"
    else:
        client_name = "unknown"
    return client_type, client_name


# 宝塔 Nginx 默认日志格式:
#   log_format main '$remote_addr - $remote_user [$time_local] "$request" '
#                   '$status $body_bytes_sent "$http_referer" '
#                   '"$http_user_agent" "$http_x_forwarded_for"';
# 示例: 192.168.1.1 - - [14/Jul/2026:10:30:00 +0800] "GET /index.php HTTP/1.1" 200 1234 "-" "Mozilla/5.0 ..." "-"
# 兼容 $http_x_forwarded_for 可选、referer/UA 为 "-"、HTTP/2.0 等变体。

_NGINX_RE = re.compile(
    r'^(\S+)'                          # 1: IP
    r'\s+\S+\s+\S+'                    # ident user (通常为 - -)
    r'\s+\[([^\]]+)\]'                 # 2: time_local
    r'\s+"([^"]*)"'                    # 3: request (method uri protocol)
    r'\s+(\d{3})'                      # 4: status
    r'\s+(\d+)'                        # 5: body_bytes_sent
    r'(?:\s+"([^"]*)")?'              # 6: referer (可选)
    r'(?:\s+"([^"]*)")?'              # 7: user_agent (可选)
)

def parse_nginx_line(line):
    try:
        m = _NGINX_RE.match(line.strip())
        if not m:
            return None
        ip = m.group(1)
        time_local = m.group(2)
        request = m.group(3)
        status = int(m.group(4))
        body_bytes = int(m.group(5))
        referer = m.group(6) or "-"
        ua = m.group(7) or "-"
        req_parts = request.split()
        method = req_parts[0] if req_parts else "-"
        uri = req_parts[1] if len(req_parts) > 1 else "-"
        return {
            "ip": ip,
            "time_local": time_local,
            "method": method,
            "uri": uri,
            "status": status,
            "bytes": body_bytes,
            "referer": referer,
            "ua": ua,
        }
    except Exception:
        return None


def _get_stats_conn():
    conn = sqlite3.connect(STATS_DB_PATH)
    conn.execute("PRAGMA journal_mode=WAL")
    conn.execute("PRAGMA synchronous=NORMAL")
    return conn

def get_log_positions():
    conn = _get_stats_conn()
    cursor = conn.cursor()
    cursor.execute("SELECT site_name, inode, size, mtime FROM log_position")
    rows = cursor.fetchall()
    conn.close()
    return {r[0]: {"inode": r[1], "size": r[2], "mtime": r[3]} for r in rows}


def update_log_position(site_name, inode_val, size_val, mtime_val):
    conn = _get_stats_conn()
    cursor = conn.cursor()
    cursor.execute(
        "INSERT OR REPLACE INTO log_position (site_name, inode, size, mtime, updated_at) VALUES (?, ?, ?, ?, ?)",
        (site_name, inode_val, size_val, mtime_val, now_text())
    )
    conn.commit()
    conn.close()


def reset_site_stats(site_name):
    """重置指定站点的所有统计数据和位置记录，下次运行时会重新回溯全部历史"""
    conn = _get_stats_conn()
    cursor = conn.cursor()
    tables = [
        "site_hourly_stats",
        "site_ip_stats",
        "site_uri_stats",
        "site_spider_stats",
        "site_client_stats",
        "site_method_stats",
        "site_error_logs",
    ]
    deleted = {}
    for table in tables:
        cursor.execute(f"DELETE FROM {table} WHERE site_name=?", (site_name,))
        deleted[table] = cursor.rowcount
    cursor.execute("DELETE FROM log_position WHERE site_name=?", (site_name,))
    deleted["log_position"] = cursor.rowcount
    conn.commit()
    conn.close()
    log_info("站点 [%s] 统计数据已重置，删除记录: %s", site_name, deleted)
    return deleted


def upsert_hourly_stats(site_name, hour_key, pv_inc, bytes_inc, unique_ips):
    conn = _get_stats_conn()
    cursor = conn.cursor()
    cursor.execute(
        "UPDATE site_hourly_stats SET pv=pv+?, uv=uv+?, total_bytes=total_bytes+? WHERE site_name=? AND hour=?",
        (pv_inc, len(unique_ips), bytes_inc, site_name, hour_key))
    if cursor.rowcount == 0:
        cursor.execute(
            "INSERT INTO site_hourly_stats (site_name, hour, pv, uv, total_bytes) VALUES (?, ?, ?, ?, ?)",
            (site_name, hour_key, pv_inc, len(unique_ips), bytes_inc))
    conn.commit()
    conn.close()


def upsert_uri_stats(site_name, date_key, uri, req_inc, bytes_inc):
    conn = _get_stats_conn()
    cursor = conn.cursor()
    cursor.execute(
        "UPDATE site_uri_stats SET request_count=request_count+?, total_bytes=total_bytes+? WHERE site_name=? AND date=? AND uri=?",
        (req_inc, bytes_inc, site_name, date_key, uri))
    if cursor.rowcount == 0:
        cursor.execute(
            "INSERT INTO site_uri_stats (site_name, date, uri, request_count, total_bytes) VALUES (?, ?, ?, ?, ?)",
            (site_name, date_key, uri, req_inc, bytes_inc))
    conn.commit()
    conn.close()


def upsert_ip_stats(site_name, date_key, ip, req_inc, bytes_inc):
    conn = _get_stats_conn()
    cursor = conn.cursor()
    cursor.execute(
        "UPDATE site_ip_stats SET request_count=request_count+?, total_bytes=total_bytes+? WHERE site_name=? AND date=? AND ip=?",
        (req_inc, bytes_inc, site_name, date_key, ip))
    if cursor.rowcount == 0:
        cursor.execute(
            "INSERT INTO site_ip_stats (site_name, date, ip, request_count, total_bytes) VALUES (?, ?, ?, ?, ?)",
            (site_name, date_key, ip, req_inc, bytes_inc))
    conn.commit()
    conn.close()


def upsert_spider_stats(site_name, date_key, spider_name):
    conn = _get_stats_conn()
    cursor = conn.cursor()
    cursor.execute(
        "UPDATE site_spider_stats SET request_count=request_count+1 WHERE site_name=? AND date=? AND spider_name=?",
        (site_name, date_key, spider_name))
    if cursor.rowcount == 0:
        cursor.execute(
            "INSERT INTO site_spider_stats (site_name, date, spider_name, request_count) VALUES (?, ?, ?, 1)",
            (site_name, date_key, spider_name))
    conn.commit()
    conn.close()


def upsert_client_stats(site_name, date_key, client_type, client_name):
    conn = _get_stats_conn()
    cursor = conn.cursor()
    cursor.execute(
        "UPDATE site_client_stats SET request_count=request_count+1 WHERE site_name=? AND date=? AND client_type=? AND client_name=?",
        (site_name, date_key, client_type, client_name))
    if cursor.rowcount == 0:
        cursor.execute(
            "INSERT INTO site_client_stats (site_name, date, client_type, client_name, request_count) VALUES (?, ?, ?, ?, 1)",
            (site_name, date_key, client_type, client_name))
    conn.commit()
    conn.close()


def upsert_status_stats(site_name, date_key, status_code, bytes_inc):
    conn = _get_stats_conn()
    cursor = conn.cursor()
    cursor.execute(
        "UPDATE site_status_stats SET request_count=request_count+1, total_bytes=total_bytes+? WHERE site_name=? AND date=? AND status_code=?",
        (bytes_inc, site_name, date_key, status_code))
    if cursor.rowcount == 0:
        cursor.execute(
            "INSERT INTO site_status_stats (site_name, date, status_code, request_count, total_bytes) VALUES (?, ?, ?, 1, ?)",
            (site_name, date_key, status_code, bytes_inc))
    conn.commit()
    conn.close()


def upsert_method_stats(site_name, date_key, method):
    conn = _get_stats_conn()
    cursor = conn.cursor()
    cursor.execute(
        "UPDATE site_method_stats SET request_count=request_count+1 WHERE site_name=? AND date=? AND method=?",
        (site_name, date_key, method))
    if cursor.rowcount == 0:
        cursor.execute(
            "INSERT INTO site_method_stats (site_name, date, method, request_count) VALUES (?, ?, ?, 1)",
            (site_name, date_key, method))
    conn.commit()
    conn.close()


def insert_error_log(site_name, date_key, time_local, ip, method, uri, status, bytes_val, referer, ua):
    conn = _get_stats_conn()
    cursor = conn.cursor()
    cursor.execute(
        "INSERT INTO site_error_logs (site_name, date, time_local, ip, method, uri, status, bytes, referer, ua) "
        "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        (site_name, date_key, time_local, ip, method, uri, status, bytes_val, referer, ua)
    )
    conn.commit()
    conn.close()


def get_site_name_from_logpath(log_path):
    basename = os.path.basename(log_path)
    if basename.endswith(".log"):
        return basename[:-4]
    return basename


def _find_site_log_files(log_dir, site_name):
    """查找站点的所有日志文件（当前 + 轮转），返回 (path, mtime) 列表，按 mtime 倒序"""
    prefix = f"{site_name}.log"
    candidates = []
    try:
        for entry in os.listdir(log_dir):
            if entry == prefix or (entry.startswith(prefix + ".") or entry.startswith(prefix + "-")):
                fpath = os.path.join(log_dir, entry)
                try:
                    candidates.append((fpath, os.path.getmtime(fpath)))
                except OSError:
                    continue
    except OSError:
        pass
    candidates.sort(key=lambda x: x[1], reverse=True)
    return candidates


def _process_log_lines(site_name, filename, lines):
    total_lines = len(lines)
    hour_batch = {}
    ip_batch = {}
    uri_batch = {}
    spider_batch = {}
    client_batch = {}
    status_batch = {}
    method_batch = {}
    error_entries = []
    for idx, line in enumerate(lines):
        if idx % 10000 == 0 and idx > 0:
            log_info("文件 [%s] 已处理 %d/%d 行", filename, idx, total_lines)
        parsed = parse_nginx_line(line.strip())
        if not parsed:
            continue
        hour_key, date_key = ts_to_hour_key(parsed["time_local"])
        if not hour_key:
            continue
        hour_batch.setdefault((site_name, hour_key), {"pv": 0, "bytes": 0, "ips": set()})
        hour_batch[(site_name, hour_key)]["pv"] += 1
        hour_batch[(site_name, hour_key)]["bytes"] += parsed["bytes"]
        hour_batch[(site_name, hour_key)]["ips"].add(parsed["ip"])
        uri_key = (site_name, date_key, parsed["uri"])
        uri_batch[uri_key] = uri_batch.get(uri_key, {"req": 0, "bytes": 0})
        uri_batch[uri_key]["req"] += 1
        uri_batch[uri_key]["bytes"] += parsed["bytes"]
        ip_key = (site_name, date_key, parsed["ip"])
        ip_batch[ip_key] = ip_batch.get(ip_key, {"req": 0, "bytes": 0})
        ip_batch[ip_key]["req"] += 1
        ip_batch[ip_key]["bytes"] += parsed["bytes"]
        spider_name = detect_spider(parsed["ua"])
        if spider_name:
            sp_key = (site_name, date_key, spider_name)
            spider_batch[sp_key] = spider_batch.get(sp_key, 0) + 1
        client_type, client_name = detect_client(parsed["ua"])
        cl_key = (site_name, date_key, client_type, client_name)
        client_batch[cl_key] = client_batch.get(cl_key, 0) + 1
        st_key = (site_name, date_key, parsed["status"])
        status_batch[st_key] = status_batch.get(st_key, {"req": 0, "bytes": 0})
        status_batch[st_key]["req"] += 1
        status_batch[st_key]["bytes"] += parsed["bytes"]
        md_key = (site_name, date_key, parsed["method"])
        method_batch[md_key] = method_batch.get(md_key, 0) + 1
        if parsed["status"] >= 400:
            error_entries.append((
                site_name, date_key, parsed["time_local"], parsed["ip"],
                parsed["method"], parsed["uri"], parsed["status"],
                parsed["bytes"], parsed["referer"], parsed["ua"]
            ))
    log_info("文件 [%s] 行处理完成，共解析 %d 行，begin write...", filename, total_lines)
    wconn = sqlite3.connect(STATS_DB_PATH)
    wconn.execute("PRAGMA journal_mode=WAL")
    wconn.execute("PRAGMA synchronous=NORMAL")
    wcur = wconn.cursor()
    log_info("文件 [%s] 写入 hour stats (%d 条)...", filename, len(hour_batch))
    for (sname, hk), data in hour_batch.items():
        wcur.execute(
            "UPDATE site_hourly_stats SET pv=pv+?, uv=uv+?, total_bytes=total_bytes+? WHERE site_name=? AND hour=?",
            (data["pv"], len(data["ips"]), data["bytes"], sname, hk))
        if wcur.rowcount == 0:
            wcur.execute(
                "INSERT INTO site_hourly_stats (site_name, hour, pv, uv, total_bytes) VALUES (?, ?, ?, ?, ?)",
                (sname, hk, data["pv"], len(data["ips"]), data["bytes"]))
    log_info("文件 [%s] 写入 uri stats (%d 条)...", filename, len(uri_batch))
    for (sname, dk, uri), data in uri_batch.items():
        wcur.execute(
            "UPDATE site_uri_stats SET request_count=request_count+?, total_bytes=total_bytes+? WHERE site_name=? AND date=? AND uri=?",
            (data["req"], data["bytes"], sname, dk, uri))
        if wcur.rowcount == 0:
            wcur.execute(
                "INSERT INTO site_uri_stats (site_name, date, uri, request_count, total_bytes) VALUES (?, ?, ?, ?, ?)",
                (sname, dk, uri, data["req"], data["bytes"]))
    log_info("文件 [%s] 写入 ip stats (%d 条)...", filename, len(ip_batch))
    for (sname, dk, ip), data in ip_batch.items():
        wcur.execute(
            "UPDATE site_ip_stats SET request_count=request_count+?, total_bytes=total_bytes+? WHERE site_name=? AND date=? AND ip=?",
            (data["req"], data["bytes"], sname, dk, ip))
        if wcur.rowcount == 0:
            wcur.execute(
                "INSERT INTO site_ip_stats (site_name, date, ip, request_count, total_bytes) VALUES (?, ?, ?, ?, ?)",
                (sname, dk, ip, data["req"], data["bytes"]))
    log_info("文件 [%s] 写入 spider stats (%d 种)...", filename, len(spider_batch))
    for (sname, dk, sp), cnt in spider_batch.items():
        wcur.execute(
            "UPDATE site_spider_stats SET request_count=request_count+? WHERE site_name=? AND date=? AND spider_name=?",
            (cnt, sname, dk, sp))
        if wcur.rowcount == 0:
            wcur.execute(
                "INSERT INTO site_spider_stats (site_name, date, spider_name, request_count) VALUES (?, ?, ?, ?)",
                (sname, dk, sp, cnt))
    log_info("文件 [%s] 写入 client stats (%d 条)...", filename, len(client_batch))
    for (sname, dk, ct, cn), cnt in client_batch.items():
        wcur.execute(
            "UPDATE site_client_stats SET request_count=request_count+? WHERE site_name=? AND date=? AND client_type=? AND client_name=?",
            (cnt, sname, dk, ct, cn))
        if wcur.rowcount == 0:
            wcur.execute(
                "INSERT INTO site_client_stats (site_name, date, client_type, client_name, request_count) VALUES (?, ?, ?, ?, ?)",
                (sname, dk, ct, cn, cnt))
    log_info("文件 [%s] 写入 status stats (%d 条)...", filename, len(status_batch))
    for (sname, dk, sc), data in status_batch.items():
        wcur.execute(
            "UPDATE site_status_stats SET request_count=request_count+?, total_bytes=total_bytes+? WHERE site_name=? AND date=? AND status_code=?",
            (data["req"], data["bytes"], sname, dk, sc))
        if wcur.rowcount == 0:
            wcur.execute(
                "INSERT INTO site_status_stats (site_name, date, status_code, request_count, total_bytes) VALUES (?, ?, ?, ?, ?)",
                (sname, dk, sc, data["req"], data["bytes"]))
    log_info("文件 [%s] 写入 method stats (%d 条)...", filename, len(method_batch))
    for (sname, dk, md), cnt in method_batch.items():
        wcur.execute(
            "UPDATE site_method_stats SET request_count=request_count+? WHERE site_name=? AND date=? AND method=?",
            (cnt, sname, dk, md))
        if wcur.rowcount == 0:
            wcur.execute(
                "INSERT INTO site_method_stats (site_name, date, method, request_count) VALUES (?, ?, ?, ?)",
                (sname, dk, md, cnt))
    log_info("文件 [%s] 写入 error logs (%d 条)...", filename, len(error_entries))
    for err in error_entries:
        wcur.execute(
            "INSERT INTO site_error_logs (site_name, date, time_local, ip, method, uri, status, bytes, referer, ua) "
            "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", err
        )
    wconn.commit()
    wconn.close()
    log_info("文件 [%s] 数据库写入完成", filename)


def collect_site_stats(config):
    log_dir = LOG_DIR
    if not os.path.isdir(log_dir):
        log_warn("日志目录 %s 不存在，跳过站点统计", log_dir)
        return
    positions = get_log_positions()
    log_files = sorted(
        [f for f in os.listdir(log_dir) if f.endswith(".log")],
        key=lambda f: os.path.getmtime(os.path.join(log_dir, f)),
        reverse=True
    )
    log_info("找到 %d 个日志文件", len(log_files))
    processed = 0
    for filename in log_files:
        log_path = os.path.join(log_dir, filename)
        site_name = get_site_name_from_logpath(filename)
        try:
            st = os.stat(log_path)
            inode = st.st_ino
            size = st.st_size
            mtime = st.st_mtime
        except OSError:
            continue
        pos = positions.get(site_name)
        first_time = pos is None
        log_info("处理文件 [%s] 大小=%d 字节 (first_time=%s)", filename, size, first_time)

        if first_time:
            log_info("首次发现站点 [%s]，开始回溯全部历史日志", site_name)
            all_site_files = _find_site_log_files(log_dir, site_name)
            all_site_files.sort(key=lambda x: x[1])
            total_all = len(all_site_files)
            log_info("找到 %d 个历史日志文件，将按时间顺序全部处理", total_all)

            for idx, (rpath, rmtime) in enumerate(all_site_files, 1):
                rname = os.path.basename(rpath)
                if not os.path.exists(rpath):
                    continue
                try:
                    rst = os.stat(rpath)
                    rinode = rst.st_ino
                    rsize = rst.st_size
                except OSError:
                    continue
                if rsize == 0:
                    log_info("[%d/%d] 历史文件 [%s] 为空，跳过", idx, total_all, rname)
                    continue
                log_info("[%d/%d] 处理历史文件 [%s] 大小=%.2fMB",
                         idx, total_all, rname, rsize / 1024 / 1024)
                try:
                    with open(rpath, "r", encoding="utf-8", errors="ignore") as handle:
                        all_lines = handle.readlines()
                except (OSError, UnicodeDecodeError) as exc:
                    log_error("读取历史文件 [%s] 失败: %s", rname, exc)
                    continue
                if all_lines:
                    _process_log_lines(site_name, rname, all_lines)
                    log_info("[%d/%d] 历史文件 [%s] 处理完成 (%d 行)",
                             idx, total_all, rname, len(all_lines))
                update_log_position(site_name, rinode, rsize, rmtime)
            log_info("站点 [%s] 历史日志回溯完成", site_name)
            processed += 1
            continue

        if pos and pos["inode"] == inode and pos["size"] == size:
            log_info("文件 [%s] 无变化，跳过", filename)
            continue

        offset = pos["size"] if (pos and pos["inode"] == inode) else 0
        if offset > size:
            offset = 0
            log_warn("文件 [%s] 大小回退（可能被截断/轮转），从头重读", filename)
        if offset == size:
            log_info("文件 [%s] offset=%d == size=%d，跳过", filename, offset, size)
            continue
        log_info("文件 [%s] 从 offset=%d 读取增量 (size=%d, 增量=%.2fKB)",
                 filename, offset, size, (size - offset) / 1024)
        try:
            with open(log_path, "r", encoding="utf-8", errors="ignore") as handle:
                handle.seek(offset)
                lines = handle.readlines()
        except OSError as exc:
            log_error("读取文件 [%s] 失败: %s", filename, exc)
            continue
        log_info("文件 [%s] 读取了 %d 行增量数据", filename, len(lines))
        if not lines:
            continue
        _process_log_lines(site_name, filename, lines)
        update_log_position(site_name, inode, size, mtime)
        processed += 1
        log_info("站点 [%s] 增量处理完成，新增 %d 行", site_name, len(lines))
    if processed == 0:
        log_info("站点统计：无新日志")
    else:
        log_info("站点统计完成，共处理 %d 个站点", processed)
