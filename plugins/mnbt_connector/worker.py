#!/usr/bin/env python3
# coding: utf-8

import hashlib
import hmac
import json
import logging
import logging.handlers
import os
import re
import secrets
import sqlite3
import sys
import threading
import time
import urllib.error
import urllib.request
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import datetime
from urllib.parse import urlparse
from stats_collector import init_stats_db, collect_site_stats


BASE_DIR = os.path.dirname(os.path.abspath(__file__))
CONFIG_PATH = os.path.join(BASE_DIR, "config.json")
INDEX_DB_PATH = os.path.join(BASE_DIR, "file_index.db")
WORKER_LOG_PATH = os.path.join(BASE_DIR, "worker.log")

_logger = None
_log_lock = threading.Lock()

SCAN_THREAD_COUNT = min(8, max(2, (os.cpu_count() or 4)))
SCAN_BATCH_SIZE = 50
SCAN_PROGRESS_INTERVAL = 5
MAX_SCAN_MEMORY_MB = 512


def get_logger():
    global _logger
    if _logger is not None:
        return _logger
    logger = logging.getLogger("mnbt_worker")
    logger.setLevel(logging.DEBUG)
    logger.propagate = True
    root_logger = logging.getLogger()
    if not root_logger.handlers:
        try:
            handler = logging.handlers.RotatingFileHandler(
                WORKER_LOG_PATH,
                maxBytes=10 * 1024 * 1024,
                backupCount=5,
                encoding="utf-8",
            )
        except OSError:
            handler = logging.StreamHandler(sys.stdout)
        config = {}
        try:
            if os.path.exists(CONFIG_PATH):
                with open(CONFIG_PATH, "r", encoding="utf-8") as handle:
                    config = json.load(handle)
        except Exception:
            pass
        level_name = (config.get("log_level") or "INFO").upper()
        level_map = {
            "DEBUG": logging.DEBUG,
            "INFO": logging.INFO,
            "WARNING": logging.WARNING,
            "ERROR": logging.ERROR,
        }
        handler.setLevel(level_map.get(level_name, logging.INFO))
        fmt = logging.Formatter(
            "%(asctime)s [%(levelname)s] %(message)s",
            datefmt="%Y-%m-%d %H:%M:%S",
        )
        handler.setFormatter(fmt)
        root_logger.addHandler(handler)
        root_logger.setLevel(logging.DEBUG)
    _logger = logger
    return logger


class _StdoutToLogger:
    def __init__(self, level=logging.INFO):
        self.level = level
        self._buf = ""

    def write(self, msg):
        if not isinstance(msg, str):
            msg = str(msg)
        self._buf += msg
        while "\n" in self._buf:
            line, self._buf = self._buf.split("\n", 1)
            if line.strip():
                logging.getLogger("stdout").log(self.level, line)

    def flush(self):
        if self._buf.strip():
            logging.getLogger("stdout").log(self.level, self._buf)
            self._buf = ""


def log_debug(msg, *args):
    get_logger().debug(msg, *args)


def log_info(msg, *args, **kwargs):
    get_logger().info(msg, *args, **kwargs)


def log_warn(msg, *args, **kwargs):
    get_logger().warning(msg, *args, **kwargs)


def log_error(msg, *args, **kwargs):
    get_logger().error(msg, *args, **kwargs)


def setup_worker_log():
    get_logger()
    sys.stdout = _StdoutToLogger(logging.INFO)
    sys.stderr = _StdoutToLogger(logging.ERROR)
    log_info("worker 日志系统已初始化完成，日志文件：%s", WORKER_LOG_PATH)

# 默认文本扩展名
TEXT_EXTENSIONS = {
    ".php", ".html", ".htm", ".js", ".css", ".txt", ".json", ".md",
    ".xml", ".vue", ".tpl", ".ini", ".conf", ".yml", ".yaml"
}

# 默认跳过目录
DEFAULT_SKIP_DIRS = {"cache", "runtime", "logs", ".git", "node_modules", "vendor", "__pycache__"}

# 默认跳过后缀
DEFAULT_SKIP_EXTS = {".jpg", ".png", ".gif", ".webp", ".mp4", ".zip", ".rar", ".7z", ".pdf", ".woff", ".ttf", ".mp3", ".avi", ".mov"}


def now_text():
    return datetime.now().strftime("%Y-%m-%d %H:%M:%S")


def load_config():
    with open(CONFIG_PATH, "r", encoding="utf-8") as handle:
        return json.load(handle)


def body_hash(body):
    return hashlib.sha256(body).hexdigest()


def signing_key(platform_secret, node_secret):
    return hmac.new(
        platform_secret.encode("utf-8"),
        node_secret.encode("utf-8"),
        hashlib.sha256,
    ).hexdigest()


def signature(method, path, body, platform_secret, node_secret, timestamp, nonce):
    canonical = "\n".join([
        method.upper(),
        path,
        body_hash(body),
        str(timestamp),
        nonce,
    ])
    return hmac.new(
        signing_key(platform_secret, node_secret).encode("utf-8"),
        canonical.encode("utf-8"),
        hashlib.sha256,
    ).hexdigest()


def signed_post(config, action, payload):
    url = config.get("mnbt_url", "").rstrip("/") + "/api/node.php?act=" + action
    log_debug("signed_post 开始请求 %s -> %s", action, url)
    parsed = urlparse(url)
    path = parsed.path + ("?" + parsed.query if parsed.query else "")
    body = json.dumps(payload, ensure_ascii=False, separators=(",", ":")).encode("utf-8")
    timestamp = int(time.time())
    nonce = secrets.token_hex(12)
    headers = {
        "Content-Type": "application/json",
        "X-MNBT-Node": config.get("node_id", ""),
        "X-MNBT-Time": str(timestamp),
        "X-MNBT-Nonce": nonce,
        "X-MNBT-Sign": signature(
            "POST",
            path,
            body,
            config.get("platform_secret", ""),
            config.get("node_secret", ""),
            timestamp,
            nonce,
        ),
    }
    request = urllib.request.Request(url, data=body, headers=headers, method="POST")
    try:
        with urllib.request.urlopen(request, timeout=5) as response:
            data = response.read().decode("utf-8")
        log_debug("signed_post %s 成功", action)
        return json.loads(data)
    except Exception as e:
        log_error("signed_post %s 失败: %s", action, e)
        raise


def heartbeat(config):
    return signed_post(config, "heartbeat", {
        "node_name": config.get("node_name", ""),
        "version": config.get("version", "0.1.0"),
        "capabilities": config.get("capabilities", []),
    })


def get_forbidden_config(config):
    """从 MNBT 获取违禁词扫描配置"""
    try:
        response = signed_post(config, "get_config", {})
        if not response.get("success"):
            return None
        return response.get("data", {}).get("forbidden_scan", {})
    except Exception as e:
        print(now_text(), "获取违禁词配置失败：", e, file=sys.stderr)
        return None


def pull_task(config):
    response = signed_post(config, "pull_task", {})
    if not response.get("success"):
        return None
    return (response.get("data") or {}).get("task")


def report_result(config, task_id, status, result=None, error=""):
    return signed_post(config, "report_result", {
        "task_id": task_id,
        "status": status,
        "result": result or {},
        "error": error,
    })


# ==================== 文件索引数据库 ====================

def init_index_db():
    """初始化文件索引 SQLite 数据库"""
    conn = sqlite3.connect(INDEX_DB_PATH)
    conn.execute("PRAGMA journal_mode=WAL")
    cursor = conn.cursor()
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS file_index (
            path TEXT PRIMARY KEY NOT NULL,
            size INTEGER NOT NULL,
            mtime REAL NOT NULL,
            fingerprint TEXT NOT NULL,
            last_scanned REAL NOT NULL,
            scan_mode TEXT NOT NULL
        )
    """)
    cursor.execute("CREATE INDEX IF NOT EXISTS idx_fingerprint ON file_index(fingerprint)")
    cursor.execute("CREATE INDEX IF NOT EXISTS idx_mtime ON file_index(mtime)")
    conn.commit()
    conn.close()


def get_index_conn():
    """获取索引数据库连接"""
    conn = sqlite3.connect(INDEX_DB_PATH)
    conn.execute("PRAGMA journal_mode=WAL")
    return conn


def compute_file_fingerprint(path):
    """计算文件指纹: path + size + mtime + sha256"""
    try:
        stat_info = os.stat(path)
        size = stat_info.st_size
        mtime = stat_info.st_mtime

        # 先快速检查 size + mtime，避免频繁计算 sha256
        fast_hash = f"{path}|{size}|{mtime}"

        # 对于小文件才计算 sha256（避免大文件拖慢扫描）
        if size < 1024 * 1024:  # 1MB 以下才算 sha256
            sha256 = hashlib.sha256()
            with open(path, "rb") as f:
                for chunk in iter(lambda: f.read(8192), b""):
                    sha256.update(chunk)
            return f"{fast_hash}|{sha256.hexdigest()}"
        else:
            return f"{fast_hash}|{size}"
    except OSError:
        return None


def get_file_index(path):
    """获取文件的索引记录"""
    conn = get_index_conn()
    cursor = conn.cursor()
    cursor.execute("SELECT size, mtime, fingerprint, last_scanned, scan_mode FROM file_index WHERE path = ?", (path,))
    row = cursor.fetchone()
    conn.close()
    if row:
        return {
            "size": row[0],
            "mtime": row[1],
            "fingerprint": row[2],
            "last_scanned": row[3],
            "scan_mode": row[4],
        }
    return None


def update_file_index(path, scan_mode="incremental"):
    """更新文件索引"""
    fingerprint = compute_file_fingerprint(path)
    if not fingerprint:
        return False

    stat_info = os.stat(path)
    now = time.time()

    conn = get_index_conn()
    cursor = conn.cursor()
    cursor.execute("""
        INSERT OR REPLACE INTO file_index (path, size, mtime, fingerprint, last_scanned, scan_mode)
        VALUES (?, ?, ?, ?, ?, ?)
    """, (path, stat_info.st_size, stat_info.st_mtime, fingerprint, now, scan_mode))
    conn.commit()
    conn.close()
    return True


def delete_file_index(path):
    """删除文件索引"""
    conn = get_index_conn()
    cursor = conn.cursor()
    cursor.execute("DELETE FROM file_index WHERE path = ?", (path,))
    conn.commit()
    conn.close()


def clean_orphaned_index(root):
    """清理索引中不存在的文件记录"""
    conn = get_index_conn()
    cursor = conn.cursor()
    cursor.execute("SELECT path FROM file_index")
    rows = cursor.fetchall()
    deleted = 0
    for (path,) in rows:
        if not os.path.exists(path):
            cursor.execute("DELETE FROM file_index WHERE path = ?", (path,))
            deleted += 1
    conn.commit()
    conn.close()
    return deleted


# ==================== 扫描逻辑 ====================

def safe_join(root, relative):
    root = os.path.abspath(root)
    target = os.path.abspath(os.path.join(root, relative.lstrip("/")))
    root_norm = os.path.abspath(root).rstrip(os.sep) or os.sep
    if target != root_norm and not target.startswith(root_norm + os.sep):
        raise ValueError("文件路径超出扫描根目录范围")
    return target


def parse_skip_dirs(skip_dirs_str):
    """解析跳过目录字符串"""
    if not skip_dirs_str:
        return DEFAULT_SKIP_DIRS.copy()
    dirs = [d.strip() for d in skip_dirs_str.split(",") if d.strip()]
    return set(dirs) or DEFAULT_SKIP_DIRS.copy()


def parse_skip_exts(skip_exts_str):
    """解析跳过后缀字符串"""
    if not skip_exts_str:
        return DEFAULT_SKIP_EXTS.copy()
    exts = [e.strip().lower() for e in skip_exts_str.split(",") if e.strip()]
    exts = [e if e.startswith(".") else "." + e for e in exts]
    return set(exts) or DEFAULT_SKIP_EXTS.copy()


def parse_keywords(content):
    """解析违禁词内容，每行一个"""
    keywords = []
    for line in content.split("\n"):
        line = line.strip()
        if line and not line.startswith("#"):
            keywords.append(line)
    return keywords


def iter_scan_files(root, max_file_size, skip_dirs=None, skip_exts=None):
    """遍历需要扫描的文件"""
    if skip_dirs is None:
        skip_dirs = DEFAULT_SKIP_DIRS.copy()
    if skip_exts is None:
        skip_exts = DEFAULT_SKIP_EXTS.copy()

    for current_root, dirs, files in os.walk(root):
        # 过滤跳过的目录
        dirs[:] = [item for item in dirs if item not in skip_dirs and not item.startswith(".")]

        for filename in files:
            ext = os.path.splitext(filename)[1].lower()
            if ext in skip_exts:
                continue
            path = os.path.join(current_root, filename)
            try:
                if os.path.getsize(path) > max_file_size:
                    continue
                yield path
            except OSError:
                continue


def should_scan_file(path, scan_changed_only=True, scan_mode="incremental"):
    """判断文件是否需要扫描"""
    if not scan_changed_only:
        return True

    # 检查索引
    index = get_file_index(path)
    if not index:
        return True  # 新文件，需要扫描

    # 检查文件是否变化
    current_fingerprint = compute_file_fingerprint(path)
    if not current_fingerprint:
        return False

    if current_fingerprint != index["fingerprint"]:
        return True  # 文件已修改，需要扫描

    return False  # 文件未变化，跳过


def excerpt_for_line(line, keyword, width=80):
    index = line.find(keyword)
    if index < 0:
        return line[:width]
    start = max(0, index - width // 2)
    end = min(len(line), index + len(keyword) + width // 2)
    return line[start:end].strip()


def scan_single_file(path, root, keywords, max_matches, scan_mode, _match_lock, _matches, stop_event):
    """单文件扫描函数（线程池调用），返回 (path, file_matches, error)"""
    if stop_event.is_set():
        return path, [], None
    try:
        file_matches = []
        with open(path, "r", encoding="utf-8", errors="ignore") as handle:
            for line_no, line in enumerate(handle, 1):
                if stop_event.is_set():
                    break
                for keyword in keywords:
                    if keyword in line:
                        relative_path = os.path.relpath(path, root)
                        file_matches.append({
                            "site": relative_path,
                            "type": "file",
                            "path": relative_path,
                            "line": line_no,
                            "keyword": keyword,
                            "excerpt": excerpt_for_line(line, keyword),
                        })
                        break
        try:
            update_file_index(path, scan_mode)
        except Exception:
            pass
        return path, file_matches, None
    except Exception as e:
        return path, [], str(e)


def collect_scan_files(root, max_file_size, skip_dirs, skip_exts,
                       scan_changed_only, scan_mode):
    """收集所有需要扫描的文件路径列表"""
    all_files = []
    skipped_index = 0
    for path in iter_scan_files(root, max_file_size, skip_dirs, skip_exts):
        if not should_scan_file(path, scan_changed_only, scan_mode):
            skipped_index += 1
            continue
        all_files.append(path)
    log_info("扫描文件收集完成：待扫描 %d 个，索引跳过 %d 个", len(all_files), skipped_index)
    return all_files


def forbidden_scan_incremental(root, keywords, max_file_size, max_matches,
                               skip_dirs=None, skip_exts=None,
                               scan_changed_only=True, scan_mode="incremental",
                               thread_count=None):
    """多线程增量扫描违禁词"""
    root = os.path.abspath(root)
    if not keywords:
        raise ValueError("违禁词列表为空")
    if not os.path.isdir(root):
        raise ValueError("扫描目录不存在")

    if thread_count is None:
        thread_count = SCAN_THREAD_COUNT

    log_info("开始%s扫描：root=%s, 线程数=%d, 关键词=%d个, max_matches=%d",
             "全量" if scan_mode == "full" else "增量",
             root, thread_count, len(keywords), max_matches)

    start_time = time.time()
    all_files = collect_scan_files(root, max_file_size, skip_dirs, skip_exts,
                                   scan_changed_only, scan_mode)
    total_files = len(all_files)
    if total_files == 0:
        log_info("没有需要扫描的文件，直接结束")
        return {
            "site": os.path.basename(root),
            "summary": {
                "scanned_files": 0,
                "scanned_rows": 0,
                "matches": 0,
                "finished_at": now_text(),
                "scan_mode": scan_mode,
                "duration_sec": 0,
                "thread_count": thread_count,
                "total_files": 0,
            },
            "matches": [],
        }

    matches = []
    match_lock = threading.Lock()
    stop_event = threading.Event()
    scanned_count = 0
    error_count = 0
    last_progress_time = 0

    def on_file_done(_path, _file_matches):
        nonlocal scanned_count, error_count, last_progress_time
        with match_lock:
            scanned_count += 1
            if _file_matches:
                if len(matches) < max_matches:
                    remaining = max_matches - len(matches)
                    matches.extend(_file_matches[:remaining])
                    if len(matches) >= max_matches:
                        stop_event.set()
                        log_info("命中数已达上限 %d，停止扫描", max_matches)
            now = time.time()
            if now - last_progress_time >= SCAN_PROGRESS_INTERVAL:
                last_progress_time = now
                pct = (scanned_count / total_files) * 100 if total_files > 0 else 0
                log_info("扫描进度：%d/%d (%.1f%%), 当前命中 %d 条",
                         scanned_count, total_files, pct, len(matches))

    thread_count = min(thread_count, max(1, total_files))
    log_info("启动 %d 个扫描线程，共 %d 个文件待扫描", thread_count, total_files)

    with ThreadPoolExecutor(max_workers=thread_count, thread_name_prefix="scan_worker") as executor:
        future_map = {executor.submit(
            scan_single_file, path, root, keywords, max_matches,
            scan_mode, match_lock, matches, stop_event
        ): path for path in all_files}

        for future in as_completed(future_map):
            path = future_map[future]
            try:
                fpath, file_matches, err = future.result()
                if err:
                    error_count += 1
                    log_warn("文件扫描出错 %s: %s", path, err)
                on_file_done(fpath, file_matches)
            except Exception as e:
                error_count += 1
                log_error("扫描任务异常 %s: %s", path, e)

            if stop_event.is_set():
                for f in future_map:
                    f.cancel()
                break

    duration = time.time() - start_time
    speed = scanned_count / duration if duration > 0 else 0
    log_info("扫描完成：扫描文件 %d 个，出错 %d 个，命中 %d 条，耗时 %.1f 秒 (%.1f 文件/秒)",
             scanned_count, error_count, len(matches), duration, speed)

    return {
        "site": os.path.basename(root),
        "summary": {
            "scanned_files": scanned_count,
            "scanned_rows": 0,
            "matches": len(matches),
            "finished_at": now_text(),
            "scan_mode": scan_mode,
            "duration_sec": round(duration, 2),
            "thread_count": thread_count,
            "total_files": total_files,
            "error_count": error_count,
            "speed_files_per_sec": round(speed, 2),
        },
        "matches": matches,
    }


# ==================== 任务执行 ====================

def execute_task(task, config):
    """执行任务"""
    action = task.get("action")
    payload = task.get("payload") or {}

    if action == "ping":
        return {"message": "pong", "time": now_text()}

    if action == "forbidden_scan":
        # 兼容旧的扫描方式
        return forbidden_scan_incremental(
            root=payload["root"],
            keywords=payload.get("keywords", []),
            max_file_size=int(payload.get("max_file_size", 5 * 1024 * 1024)),
            max_matches=int(payload.get("max_matches", 1000)),
            scan_changed_only=payload.get("scan_changed_only", True),
            scan_mode=payload.get("scan_mode", "incremental"),
        )

    raise ValueError("不支持的任务类型：" + str(action))


def run_forbidden_scan(config):
    """自动执行违禁词扫描任务"""
    fb_config = get_forbidden_config(config)
    if not fb_config or not fb_config.get("enabled"):
        return

    keywords = parse_keywords(fb_config.get("content", ""))
    if not keywords:
        log_info("违禁词列表为空，跳过扫描")
        return

    log_info("开始执行自动违禁词扫描")
    init_index_db()
    clean_orphaned_index(fb_config.get("scan_dir", "/www/wwwroot"))

    task_id = f"scan_auto_{int(time.time())}"

    try:
        result = forbidden_scan_incremental(
            root=fb_config.get("scan_dir", "/www/wwwroot"),
            keywords=keywords,
            max_file_size=fb_config.get("max_file_size", 5242880),
            max_matches=fb_config.get("max_matches", 1000),
            skip_dirs=parse_skip_dirs(fb_config.get("skip_dirs")),
            skip_exts=parse_skip_exts(fb_config.get("skip_exts")),
            scan_changed_only=fb_config.get("scan_changed_only", True),
            scan_mode="incremental",
        )
        report_result(config, task_id, "success", result)
        summary = result["summary"]
        log_info("自动违禁词扫描完成：扫描文件 %d 个，命中 %d 条，耗时 %.1f 秒",
                 summary["scanned_files"], summary["matches"], summary.get("duration_sec", 0))
    except Exception as e:
        report_result(config, task_id, "failed", {}, str(e))
        log_error("自动违禁词扫描失败：%s", e, exc_info=True)


_last_full_scan_minute = None

def should_run_full_scan(fb_config):
    """判断是否应该进行全量扫描（同一分钟仅触发一次）"""
    global _last_full_scan_minute
    if not fb_config or not fb_config.get("full_scan_enabled"):
        return False

    cron_str = fb_config.get("full_scan_cron", "0 3 * * *")
    try:
        parts = cron_str.split()
        if len(parts) >= 2:
            minute, hour = parts[0], parts[1]
            now = datetime.now()
            if now.minute == int(minute) and now.hour == int(hour):
                key = now.strftime("%Y%m%d%H%M")
                if _last_full_scan_minute == key:
                    return False
                _last_full_scan_minute = key
                return True
    except (ValueError, IndexError):
        pass

    return False


def run_once(config):
    """执行一次完整的工作周期"""
    log_info("====== 开始工作周期 ======")
    log_info("步骤1: heartbeat")
    try:
        heartbeat(config)
        log_info("heartbeat 成功")
    except Exception as exc:
        log_warn("heartbeat 失败（继续）：%s", exc)

    log_info("步骤2: collect_site_stats")
    try:
        collect_site_stats(config)
        log_info("collect_site_stats 完成")
    except Exception as exc:
        log_error("collect_site_stats 异常：%s", exc, exc_info=True)

    log_info("步骤3: get_forbidden_config")
    fb_config = get_forbidden_config(config)
    if fb_config and fb_config.get("enabled"):
        log_info("违禁词扫描已启用，检查是否需要扫描")
        if should_run_full_scan(fb_config):
            log_info("执行全量扫描...")
            run_forbidden_scan_with_mode(config, "full")
        else:
            log_info("执行增量扫描...")
            run_forbidden_scan_with_mode(config, "incremental")
    else:
        log_info("违禁词扫描未启用或配置为空")

    log_info("步骤4: pull_task")
    try:
        task = pull_task(config)
        log_debug("pull_task 成功")
    except Exception as exc:
        log_warn("pull_task 失败（跳过）：%s", exc)
        log_info("====== 工作周期结束 ======")
        return
    if not task:
        log_info("无待执行任务")
        log_info("====== 工作周期结束 ======")
        return

    task_id = task.get("task_id")
    log_info("步骤5: 执行任务 %s (action=%s)", task_id, task.get("action"))
    try:
        result = execute_task(task, config)
        report_result(config, task_id, "success", result)
        log_info("任务 %s 执行成功", task_id)
    except Exception as exc:
        report_result(config, task_id, "failed", {}, str(exc))
        log_error("任务 %s 执行失败: %s", task_id, exc, exc_info=True)

    log_info("====== 工作周期结束 ======")


def run_forbidden_scan_with_mode(config, scan_mode):
    """按指定模式执行违禁词扫描"""
    fb_config = get_forbidden_config(config)
    if not fb_config or not fb_config.get("enabled"):
        log_info("违禁词扫描未启用，跳过 %s 扫描", scan_mode)
        return

    keywords = parse_keywords(fb_config.get("content", ""))
    if not keywords:
        log_info("违禁词列表为空，跳过扫描")
        return

    log_info("开始%s违禁词扫描", "全量" if scan_mode == "full" else "增量")
    init_index_db()
    clean_orphaned_index(fb_config.get("scan_dir", "/www/wwwroot"))

    task_id = f"scan_{scan_mode}_{int(time.time())}"

    try:
        result = forbidden_scan_incremental(
            root=fb_config.get("scan_dir", "/www/wwwroot"),
            keywords=keywords,
            max_file_size=fb_config.get("max_file_size", 5242880),
            max_matches=fb_config.get("max_matches", 1000),
            skip_dirs=parse_skip_dirs(fb_config.get("skip_dirs")),
            skip_exts=parse_skip_exts(fb_config.get("skip_exts")),
            scan_changed_only=(scan_mode != "full"),
            scan_mode=scan_mode,
        )
        report_result(config, task_id, "success", result)
        summary = result["summary"]
        log_info("%s扫描完成：扫描文件 %d 个，命中 %d 条，耗时 %.1f 秒",
                 "全量" if scan_mode == "full" else "增量",
                 summary["scanned_files"], summary["matches"],
                 summary.get("duration_sec", 0))
    except Exception as e:
        report_result(config, task_id, "failed", {}, str(e))
        log_error("%s扫描失败：%s", "全量" if scan_mode == "full" else "增量", e, exc_info=True)


def do_full_scan(config):
    """执行一次全量扫描"""
    fb_config = get_forbidden_config(config)
    if not fb_config or not fb_config.get("enabled"):
        log_warn("违禁词扫描未启用，无法执行全量扫描")
        return
    run_forbidden_scan_with_mode(config, "full")


def main():
    once = "--once" in sys.argv
    full_scan = "--full-scan" in sys.argv

    setup_worker_log()

    log_info("====== worker.py 启动 ======")
    log_info("参数: %s", sys.argv)

    log_info("初始化索引数据库...")
    init_index_db()
    log_info("初始化站点统计数据库...")
    init_stats_db()

    log_info("加载配置...")
    config = load_config()
    log_info("配置加载完成, mnbt_url=%s, node_id=%s",
             config.get("mnbt_url", ""), config.get("node_id", ""))

    if full_scan:
        log_info("执行全量扫描...")
        do_full_scan(config)
        log_info("全量扫描完成")
        return

    while True:
        try:
            run_once(config)
        except (urllib.error.URLError, TimeoutError, ValueError, OSError, json.JSONDecodeError, KeyError) as exc:
            log_error("工作进程异常：%s", exc, exc_info=True)
        except Exception as exc:
            log_error("工作进程未预期异常：%s", exc, exc_info=True)
        if once:
            break
        time.sleep(int(config.get("interval_seconds", 10)))

    log_info("====== worker.py 退出 ======")


if __name__ == "__main__":
    main()