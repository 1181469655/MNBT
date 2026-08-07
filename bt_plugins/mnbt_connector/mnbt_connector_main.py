#!/usr/bin/python
# coding: utf-8

import json
import os
import signal
import sqlite3
import subprocess
import sys
import time
from stats_api import StatsAPIMixin


BASE_DIR = os.path.dirname(os.path.abspath(__file__))
INDEX_DB_PATH = os.path.join(BASE_DIR, "file_index.db")


class mnbt_connector_main(StatsAPIMixin):
    def __init__(self):
        self.base_dir = os.path.dirname(os.path.abspath(__file__))
        self.config_path = os.path.join(self.base_dir, "config.json")
        self.worker_path = os.path.join(self.base_dir, "worker.py")
        self.pid_path = os.path.join(self.base_dir, "worker.pid")
        self.log_path = os.path.join(self.base_dir, "worker.log")

    def _try_write_log(self, msg):
        try:
            import public
            public.WriteLog('MNBT连接器', msg)
        except Exception:
            pass

    def get_site_list(self, args):
        try:
            import public
            panel_sites = public.M('sites').field('name').select()
            panel_names = {s["name"] for s in panel_sites}
        except Exception:
            panel_names = set()
        if panel_names:
            stats_data = super().get_site_list(args).get("data", [])
            stats_map = {s["site_name"]: s for s in stats_data}
            data = []
            for name in sorted(panel_names):
                merged = {"site_name": name, "pv": 0, "uv": 0, "total_bytes": 0}
                if name in stats_map:
                    merged.update(stats_map[name])
                data.append(merged)
            extra_names = {s["site_name"] for s in stats_data} - panel_names
            for name in sorted(extra_names):
                data.append(stats_map[name])
        else:
            data = super().get_site_list(args).get("data", [])
        return {"status": True, "data": data}

    def reset_site_stats(self, args):
        site = args.get("site", "").strip()
        if not site:
            return {"status": False, "msg": "缺少站点名称"}
        if ".." in site or "/" in site or "\\" in site:
            return {"status": False, "msg": "非法的站点名称"}
        try:
            from stats_collector import reset_site_stats as do_reset
            deleted = do_reset(site)
            self._try_write_log(f"重置站点统计: {site}")
            return {
                "status": True,
                "msg": "站点统计已重置，下次采集时将重新回溯全部历史日志",
                "deleted": deleted,
            }
        except Exception as exc:
            return {"status": False, "msg": f"重置失败: {exc}"}

    def _read_config(self):
        if not os.path.exists(self.config_path):
            return {}
        with open(self.config_path, "r", encoding="utf-8") as handle:
            return json.load(handle)

    def _read_pid(self):
        if not os.path.exists(self.pid_path):
            return 0
        try:
            with open(self.pid_path, "r", encoding="utf-8") as handle:
                return int(handle.read().strip() or "0")
        except (OSError, ValueError):
            return 0

    def _is_process_running(self, pid):
        if pid <= 0:
            return False
        try:
            os.kill(pid, 0)
            return True
        except OSError:
            return False

    def _clear_stale_pid(self, pid):
        if pid and not self._is_process_running(pid) and os.path.exists(self.pid_path):
            try:
                os.remove(self.pid_path)
            except OSError:
                pass

    def _tail_log(self, max_bytes=4000):
        if not os.path.exists(self.log_path):
            return ""
        try:
            with open(self.log_path, "rb") as handle:
                handle.seek(0, os.SEEK_END)
                size = handle.tell()
                handle.seek(max(0, size - max_bytes))
                return handle.read().decode("utf-8", errors="ignore")
        except OSError:
            return ""

    def _log_meta(self):
        if not os.path.exists(self.log_path):
            return {"log_size": 0, "log_mtime": ""}
        try:
            stat_info = os.stat(self.log_path)
            return {
                "log_size": stat_info.st_size,
                "log_mtime": time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(stat_info.st_mtime)),
            }
        except OSError:
            return {"log_size": 0, "log_mtime": ""}

    @staticmethod
    def _mask_secret_value(value):
        if value is None:
            return ""
        text = str(value)
        if len(text) <= 8:
            return "****" if text else ""
        return text[:4] + "****" + text[-4:]

    def _mask_config(self, value):
        secret_keys = {"platform_secret", "node_secret", "api_key", "secret", "token", "password", "key"}
        if isinstance(value, dict):
            masked = {}
            for key, item in value.items():
                key_text = str(key).lower()
                if key_text in secret_keys or "secret" in key_text or "token" in key_text or "password" in key_text:
                    masked[key] = self._mask_secret_value(item)
                else:
                    masked[key] = self._mask_config(item)
            return masked
        if isinstance(value, list):
            return [self._mask_config(item) for item in value]
        return value

    def get_runtime_status(self, args):
        pid = self._read_pid()
        running = self._is_process_running(pid)
        if not running:
            self._clear_stale_pid(pid)
            pid = 0
        return {
            "status": True,
            "running": running,
            "pid": pid,
            "log_path": self.log_path,
            "last_log": self._tail_log(),
        }

    def get_status(self, args):
        config = self._read_config()
        runtime = self.get_runtime_status(args)
        return {
            "status": True,
            "msg": "MNBT 连接器运行中" if runtime["running"] else "MNBT 连接器已停止",
            "node_id": config.get("node_id", ""),
            "version": config.get("version", "0.1.0"),
            "capabilities": config.get("capabilities", []),
            "running": runtime["running"],
            "pid": runtime["pid"],
            "last_log": runtime["last_log"],
        }

    def get_logs(self, args):
        max_bytes = int(args.get("max_bytes", 20000) or 20000)
        max_bytes = min(max(max_bytes, 1000), 200000)
        runtime = self.get_runtime_status(args)
        meta = self._log_meta()
        return {
            "status": True,
            "log_path": self.log_path,
            "log_text": self._tail_log(max_bytes),
            "running": runtime["running"],
            "pid": runtime["pid"],
            "max_bytes": max_bytes,
            "log_size": meta["log_size"],
            "log_mtime": meta["log_mtime"],
        }

    def get_log_list(self, args):
        log_dir = os.path.dirname(self.log_path)
        log_base = os.path.basename(self.log_path)
        log_files = []
        try:
            for entry in sorted(os.listdir(log_dir), reverse=True):
                if entry == log_base or entry.startswith(log_base + "."):
                    full_path = os.path.join(log_dir, entry)
                    try:
                        stat_info = os.stat(full_path)
                        log_files.append({
                            "name": entry,
                            "path": full_path,
                            "size": stat_info.st_size,
                            "size_mb": round(stat_info.st_size / 1024 / 1024, 2),
                            "mtime": time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(stat_info.st_mtime)),
                        })
                    except OSError:
                        pass
        except OSError as exc:
            return {"status": False, "msg": str(exc), "data": []}
        return {
            "status": True,
            "data": log_files,
            "total_count": len(log_files),
        }

    def get_log_content(self, args):
        target = args.get("file", "")
        offset = int(args.get("offset", 0) or 0)
        limit = int(args.get("limit", 50000) or 50000)
        limit = min(max(limit, 1000), 500000)
        keyword = args.get("keyword", "").strip()
        level = args.get("level", "").strip().upper()

        if target and os.sep in target:
            return {"status": False, "msg": "非法的日志文件名"}

        log_dir = os.path.dirname(self.log_path)
        log_path = os.path.join(log_dir, target) if target else self.log_path

        if not os.path.exists(log_path):
            return {"status": False, "msg": "日志文件不存在", "data": []}

        try:
            file_size = os.path.getsize(log_path)
            with open(log_path, "r", encoding="utf-8", errors="ignore") as handle:
                if offset > 0:
                    handle.seek(min(offset, file_size))
                raw = handle.read(limit)
        except OSError as exc:
            return {"status": False, "msg": str(exc), "data": []}

        lines = raw.splitlines()
        filtered_lines = []
        for line in lines:
            if not line.strip():
                continue
            if keyword and keyword not in line:
                continue
            if level and f"[{level}]" not in line:
                continue
            filtered_lines.append(line)

        current_offset = offset + len(raw.encode("utf-8", errors="ignore"))
        has_more = current_offset < file_size

        return {
            "status": True,
            "data": filtered_lines,
            "total_lines": len(filtered_lines),
            "file_size": file_size,
            "current_offset": current_offset,
            "has_more": has_more,
            "keyword": keyword,
            "level": level,
        }

    def clear_log(self, args):
        target = args.get("file", "")
        if target and os.sep in target:
            return {"status": False, "msg": "非法的日志文件名"}

        log_dir = os.path.dirname(self.log_path)
        log_path = os.path.join(log_dir, target) if target else self.log_path

        if not os.path.exists(log_path):
            return {"status": True, "msg": "日志文件不存在，无需清空"}

        try:
            with open(log_path, "w", encoding="utf-8"):
                pass
            self._try_write_log("日志已清空")
            return {"status": True, "msg": "日志已清空"}
        except OSError as exc:
            return {"status": False, "msg": f"清空失败: {exc}"}

    def get_log_level(self, args):
        config = self._read_config()
        return {
            "status": True,
            "level": config.get("log_level", "INFO"),
            "available_levels": ["DEBUG", "INFO", "WARNING", "ERROR"],
        }

    def set_log_level(self, args):
        level = args.get("level", "").strip().upper()
        if level not in ("DEBUG", "INFO", "WARNING", "ERROR"):
            return {"status": False, "msg": "无效的日志级别，可选: DEBUG, INFO, WARNING, ERROR"}
        config = self._read_config()
        config["log_level"] = level
        try:
            with open(self.config_path, "w", encoding="utf-8") as handle:
                json.dump(config, handle, ensure_ascii=False, indent=2)
            self._try_write_log(f"日志级别已设置为 {level}")
            return {"status": True, "msg": f"日志级别已设置为 {level}，重启 worker 后生效"}
        except OSError as exc:
            return {"status": False, "msg": str(exc)}

    def get_worker_log_stats(self, args):
        target = args.get("file", "")
        if target and os.sep in target:
            return {"status": False, "msg": "非法的日志文件名"}

        log_dir = os.path.dirname(self.log_path)
        log_path = os.path.join(log_dir, target) if target else self.log_path

        if not os.path.exists(log_path):
            return {"status": True, "data": {"DEBUG": 0, "INFO": 0, "WARNING": 0, "ERROR": 0, "total": 0}}

        counts = {"DEBUG": 0, "INFO": 0, "WARNING": 0, "ERROR": 0, "total": 0}
        try:
            with open(log_path, "r", encoding="utf-8", errors="ignore") as handle:
                for line in handle:
                    counts["total"] += 1
                    for level in ("DEBUG", "INFO", "WARNING", "ERROR"):
                        if f"[{level}]" in line:
                            counts[level] += 1
                            break
            return {"status": True, "data": counts}
        except OSError as exc:
            return {"status": False, "msg": str(exc), "data": counts}

    def get_config_info(self, args):
        config = self._read_config()
        masked_config = self._mask_config(config)
        return {
            "status": True,
            "config_path": self.config_path,
            "config": masked_config,
            "config_json": json.dumps(masked_config, ensure_ascii=False, indent=2),
            "exists": os.path.exists(self.config_path),
        }

    def start(self, args):
        if not os.path.exists(self.config_path):
            return {"status": False, "msg": "config.json 配置文件不存在"}
        runtime = self.get_runtime_status(args)
        if runtime["running"]:
            return {"status": True, "msg": "工作进程已在运行中", "pid": runtime["pid"]}
        process = subprocess.Popen(
            [sys.executable, self.worker_path],
            cwd=self.base_dir,
            stdout=open(self.log_path, "a", encoding="utf-8"),
            stderr=subprocess.STDOUT,
            stdin=subprocess.DEVNULL,
            start_new_session=True,
        )
        with open(self.pid_path, "w", encoding="utf-8") as handle:
            handle.write(str(process.pid))
        time.sleep(0.3)
        self._try_write_log(f'工作进程已启动 PID={process.pid}')
        return {
            "status": True,
            "msg": "工作进程已启动",
            "pid": process.pid,
        }

    def stop(self, args):
        pid = self._read_pid()
        if pid <= 0:
            return {"status": True, "msg": "工作进程未运行"}
        if self._is_process_running(pid):
            try:
                os.kill(pid, signal.SIGTERM)
            except OSError as exc:
                return {"status": False, "msg": str(exc)}
        try:
            if os.path.exists(self.pid_path):
                os.remove(self.pid_path)
        except OSError:
            pass

        return {"status": True, "msg": "工作进程已停止"}

    def run_once(self, args):
        if not os.path.exists(self.config_path):
            return {"status": False, "msg": "config.json 配置文件不存在"}
        try:
            result = subprocess.run(
                [sys.executable, self.worker_path, "--once"],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                text=True,
                timeout=120,
            )
            ok = result.returncode == 0
            self._try_write_log(f'执行一次{"成功" if ok else "失败"}')
            return {
                "status": ok,
                "msg": "执行成功" if ok else result.stderr,
                "stdout": result.stdout,
            }
        except subprocess.TimeoutExpired as exc:
            return {
                "status": False,
                "msg": "执行超时",
                "stdout": exc.stdout or "",
                "stderr": exc.stderr or "",
            }

    def get_index_stats(self, args):
        if not os.path.exists(INDEX_DB_PATH):
            return {
                "status": True,
                "data": {
                    "total_files": 0,
                    "total_size": 0,
                    "scan_modes": {},
                    "index_exists": False,
                },
            }

        try:
            conn = sqlite3.connect(INDEX_DB_PATH)
            cursor = conn.cursor()

            cursor.execute("SELECT COUNT(*) FROM file_index")
            total_files = cursor.fetchone()[0]

            cursor.execute("SELECT SUM(size) FROM file_index")
            total_size = cursor.fetchone()[0] or 0

            cursor.execute("SELECT scan_mode, COUNT(*) FROM file_index GROUP BY scan_mode")
            scan_modes = {row[0]: row[1] for row in cursor.fetchall()}

            conn.close()

            return {
                "status": True,
                "data": {
                    "total_files": total_files,
                    "total_size": total_size,
                    "total_size_mb": round(total_size / 1024 / 1024, 2),
                    "scan_modes": scan_modes,
                    "index_exists": True,
                },
            }
        except Exception as exc:
            return {
                "status": False,
                "msg": str(exc),
                "data": None,
            }

    def trigger_full_scan(self, args):
        config = self._read_config()
        if not config.get("mnbt_url"):
            return {"status": False, "msg": "未配置 MNBT 地址"}

        result = subprocess.run(
            [sys.executable, self.worker_path, "--full-scan"],
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            timeout=300,
        )
        return {
            "status": result.returncode == 0,
            "msg": "全量扫描已触发" if result.returncode == 0 else result.stderr,
        }


if __name__ == "__main__":
    import sys
    try:
        payload = json.loads(sys.argv[1]) if len(sys.argv) > 1 else {}
    except (IndexError, json.JSONDecodeError):
        payload = {}

    action = payload.get("s", "")
    if not action:
        print(json.dumps({"status": False, "msg": "缺少参数 s"}))
        sys.exit(1)

    args = {k: v for k, v in payload.items() if k not in ("s", "action")}

    plugin = mnbt_connector_main()
    ALLOWED_ACTIONS = {
        "get_status", "start", "stop", "run_once", "get_logs", "get_config_info",
        "get_site_list", "get_site_overview", "get_site_trend",
        "get_site_ip_rank", "get_site_uri_rank", "get_site_error_logs",
        "get_site_recent_logs", "get_site_spider_analysis",
        "get_site_client_stats", "get_site_method_stats",
        "get_index_stats",
        "get_log_list", "get_log_content", "clear_log",
        "get_log_level", "set_log_level", "get_worker_log_stats",
        "reset_site_stats",
    }
    if action not in ALLOWED_ACTIONS:
        print(json.dumps({"status": False, "msg": f"未知操作: {action}"}))
        sys.exit(1)
    method = getattr(plugin, action, None)

    try:
        result = method(args)
        print(json.dumps(result, ensure_ascii=False, default=str))
    except Exception as exc:
        print(json.dumps({"status": False, "msg": str(exc)}, ensure_ascii=False))
        sys.exit(1)
