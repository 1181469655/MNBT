#!/usr/bin/python
# coding: utf-8

from datetime import datetime, timedelta
import os
import re
import sqlite3
import subprocess
import sys
import time

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
LOG_DIR = os.path.join(BASE_DIR, "../wwwlogs") if os.path.isdir(os.path.join(BASE_DIR, "../wwwlogs")) else "/www/wwwlogs"

SPIDER_AGENTS = {
    "Baiduspider", "Googlebot", "bingbot", "YandexBot", "Sogou",
    "360Spider", "Bytespider", "Amazonbot", "AhrefsBot", "SemrushBot",
    "DotBot", "BLEXBot", "Exabot", "MJ12bot", "SeznamBot",
}

MONTH_MAP = {
    "Jan": 1, "Feb": 2, "Mar": 3, "Apr": 4, "May": 5, "Jun": 6,
    "Jul": 7, "Aug": 8, "Sep": 9, "Oct": 10, "Nov": 11, "Dec": 12,
}

# 宝塔 Nginx 默认日志格式（与 stats_collector.py 保持一致）
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

STATS_LABELS = [
    ("pv", "浏览量(PV)"),
    ("uv", "访客数(UV)"),
    ("ip_count", "IP数"),
    ("total_bytes", "总流量"),
    ("requests", "请求数"),
    ("error_count", "错误请求"),
    ("spider_count", "蜘蛛请求"),
    ("qps", "平均QPS"),
]

def format_bytes(n):
    for unit in ("B", "KB", "MB", "GB", "TB"):
        if abs(n) < 1024.0:
            return f"{n:.1f} {unit}" if unit != "B" else f"{n:.0f} {unit}"
        n /= 1024.0
    return f"{n:.1f} PB"


def format_number(n):
    return f"{n:,}"


class StatsAPIMixin:
    STATS_DB = os.path.join(BASE_DIR, "maxiaole.db")
    _site_cache = None
    _site_cache_ts = 0

    def _ensure_stats_db(self):
        if os.path.exists(self.STATS_DB):
            return
        try:
            subprocess.run(
                [sys.executable, os.path.join(BASE_DIR, "worker.py"), "--once"],
                capture_output=True, timeout=10
            )
        except Exception as exc:
            print(f"[mnbt] DB 初始化异常: {exc}", file=sys.stderr)

    def _stats_query(self, sql, params=None):
        if not os.path.exists(self.STATS_DB):
            self._ensure_stats_db()
        if not os.path.exists(self.STATS_DB):
            return []
        conn = sqlite3.connect(self.STATS_DB)
        conn.execute("PRAGMA journal_mode=WAL")
        cursor = conn.cursor()
        try:
            if params:
                cursor.execute(sql, params)
            else:
                cursor.execute(sql)
            rows = cursor.fetchall()
            return rows
        except sqlite3.Error as exc:
            print(f"[mnbt] SQLite 查询失败: {exc}; sql={sql}", file=sys.stderr)
            return []
        finally:
            conn.close()

    def _stats_query_one(self, sql, params=None):
        rows = self._stats_query(sql, params)
        return rows[0] if rows else None

    @staticmethod
    def _date_range(range_key):
        today = datetime.now().strftime("%Y-%m-%d")
        if range_key == "today":
            return today, today
        if range_key == "yesterday":
            yesterday = (datetime.now() - timedelta(days=1)).strftime("%Y-%m-%d")
            return yesterday, yesterday
        if range_key == "7d":
            start = (datetime.now() - timedelta(days=6)).strftime("%Y-%m-%d")
            return start, today
        if range_key == "30d":
            start = (datetime.now() - timedelta(days=29)).strftime("%Y-%m-%d")
            return start, today
        return today, today

    def _paginate(self, count_sql, count_params, data_sql, data_params, page, page_size):
        count_row = self._stats_query_one(count_sql, count_params)
        total = count_row[0] if count_row else 0
        page = max(1, int(page))
        page_size = min(max(1, int(page_size)), 200)
        offset = (page - 1) * page_size
        safe_params = tuple(data_params or ()) + (page_size, offset)
        rows = self._stats_query(data_sql + " LIMIT ? OFFSET ?", safe_params)
        return rows, total, page, page_size

    def _paginate_response(self, rows, total, page, page_size, field_map, source="sqlite", fallback=False, msg=""):
        response = {
            "status": True,
            "data": [dict(zip(field_map, r)) for r in rows],
            "total": total, "page": page, "page_size": page_size,
            "source": source, "fallback": bool(fallback),
        }
        if msg:
            response["msg"] = msg
        return response

    def _format_stat_labels(self, data, labels):
        result = []
        for key, name in labels:
            result.append({"key": key, "name": name, "value": data.get(key, 0)})
        return result

    def _calc_qps(self, requests, range_key):
        if requests <= 0:
            return 0
        if range_key in ("today",):
            now = time.localtime()
            elapsed = now.tm_hour * 3600 + now.tm_min * 60 + now.tm_sec
            if elapsed < 1:
                return 0
            return round(requests / elapsed, 2)
        return round(requests / 86400, 2)

    # ---------- 站点列表（合并面板站点 + 统计数据，单次 GROUP BY 查询）----------

    def get_site_list(self, args):
        date = time.strftime("%Y-%m-%d")
        now_ts = time.time()
        if self._site_cache and (now_ts - self._site_cache_ts) < 30:
            return self._site_cache
        rows = self._stats_query(
            "SELECT site_name, COALESCE(SUM(pv),0), COALESCE(SUM(uv),0), COALESCE(SUM(total_bytes),0) "
            "FROM site_hourly_stats GROUP BY site_name ORDER BY site_name"
        )
        sites = [{"site_name": r[0], "pv": r[1], "uv": r[2], "total_bytes": r[3]} for r in rows]
        result = {"status": True, "data": sites}
        self._site_cache = result
        self._site_cache_ts = now_ts
        return result

    # ---------- 统计标签（前后端字段名解耦） ----------

    def get_stat_labels(self, args):
        return {"status": True, "data": STATS_LABELS}

    # ---------- 站点概览 ----------

    def _query_date_metrics(self, site, date_str):
        """查询某天的 PV/UV/流量/IP/错误/蜘蛛，返回 dict"""
        row = self._stats_query_one(
            "SELECT COALESCE(SUM(pv),0), COALESCE(SUM(uv),0), COALESCE(SUM(total_bytes),0) "
            "FROM site_hourly_stats WHERE site_name=? AND hour>=? AND hour<=?",
            (site, date_str + " 00", date_str + " 23")
        )
        pv, uv, total_bytes = row or (0, 0, 0)

        ip_row = self._stats_query_one(
            "SELECT COUNT(DISTINCT ip) FROM site_ip_stats WHERE site_name=? AND date>=? AND date<=?",
            (site, date_str, date_str)
        )
        ip_count = ip_row[0] if ip_row else 0

        error_row = self._stats_query_one(
            "SELECT COALESCE(SUM(request_count),0) FROM site_status_stats "
            "WHERE site_name=? AND date>=? AND date<=? AND status_code>=400",
            (site, date_str, date_str)
        )
        error_count = error_row[0] if error_row else 0

        spider_row = self._stats_query_one(
            "SELECT COALESCE(SUM(request_count),0) FROM site_spider_stats "
            "WHERE site_name=? AND date>=? AND date<=?",
            (site, date_str, date_str)
        )
        spider_count = spider_row[0] if spider_row else 0

        return {
            "pv": pv, "uv": uv, "total_bytes": total_bytes,
            "ip_count": ip_count, "error_count": error_count,
            "spider_count": spider_count,
        }

    def _query_metrics_range(self, site, start_date, end_date):
        """范围聚合：从各分表独立查询"""
        pv, uv, total_bytes = self._stats_query_one(
            "SELECT COALESCE(SUM(pv),0), COALESCE(SUM(uv),0), COALESCE(SUM(total_bytes),0) "
            "FROM site_hourly_stats WHERE site_name=? AND hour>=? AND hour<=?",
            (site, start_date + " 00", end_date + " 23")
        ) or (0, 0, 0)

        ip_row = self._stats_query_one(
            "SELECT COUNT(DISTINCT ip) FROM site_ip_stats "
            "WHERE site_name=? AND date>=? AND date<=?",
            (site, start_date, end_date)
        )
        ip_count = ip_row[0] if ip_row else 0

        error_row = self._stats_query_one(
            "SELECT COALESCE(SUM(request_count),0) FROM site_status_stats "
            "WHERE site_name=? AND date>=? AND date<=? AND status_code>=400",
            (site, start_date, end_date)
        )
        error_count = error_row[0] if error_row else 0

        spider_row = self._stats_query_one(
            "SELECT COALESCE(SUM(request_count),0) FROM site_spider_stats "
            "WHERE site_name=? AND date>=? AND date<=?",
            (site, start_date, end_date)
        )
        spider_count = spider_row[0] if spider_row else 0

        return {
            "pv": int(pv), "uv": int(uv), "total_bytes": int(total_bytes),
            "ip_count": int(ip_count), "error_count": int(error_count),
            "spider_count": int(spider_count),
        }

    def get_site_overview(self, args):
        site = args.get("site", "")
        if not site:
            return {"status": False, "msg": "缺少站点名称"}
        range_key = args.get("range", "today")
        start_date, end_date = self._date_range(range_key)

        today = time.strftime("%Y-%m-%d")
        yesterday = time.strftime("%Y-%m-%d", time.localtime(time.time() - 86400))
        day_before = time.strftime("%Y-%m-%d", time.localtime(time.time() - 172800))

        # 按范围聚合：单次查询而非逐日循环
        if start_date == today:
            curr = self._query_date_metrics(site, today)
            if not any((curr.get("pv", 0), curr.get("ip_count", 0), curr.get("total_bytes", 0))):
                recent = self._recent_metrics(site, today, today)
                if recent.get("pv", 0) > 0:
                    curr = recent
            prev = self._query_date_metrics(site, yesterday)
            prev2 = self._query_date_metrics(site, day_before)
        else:
            span_days = (time.mktime(time.strptime(end_date, "%Y-%m-%d")) - time.mktime(time.strptime(start_date, "%Y-%m-%d"))) // 86400 + 1
            prev_end = time.strftime("%Y-%m-%d", time.localtime(time.mktime(time.strptime(start_date, "%Y-%m-%d")) - 86400))
            prev_start = time.strftime("%Y-%m-%d", time.localtime(time.mktime(time.strptime(start_date, "%Y-%m-%d")) - span_days * 86400))
            curr = self._query_metrics_range(site, start_date, end_date)
            if not any((curr.get("pv", 0), curr.get("ip_count", 0), curr.get("total_bytes", 0))):
                recent = self._recent_metrics(site, start_date, end_date)
                if recent.get("pv", 0) > 0:
                    curr = recent
            prev = self._query_metrics_range(site, prev_start, prev_end)
            prev2 = {}

        qps = self._calc_qps(curr.get("pv", 0), range_key)

        data = {
            "pv": curr.get("pv", 0), "uv": curr.get("uv", 0),
            "ip_count": curr.get("ip_count", 0), "total_bytes": curr.get("total_bytes", 0),
            "requests": curr.get("pv", 0), "error_count": curr.get("error_count", 0),
            "spider_count": curr.get("spider_count", 0), "qps": qps,
        }
        comparison = {
            "prev": {
                "pv": prev.get("pv", 0), "uv": prev.get("uv", 0),
                "ip_count": prev.get("ip_count", 0), "total_bytes": prev.get("total_bytes", 0),
                "error_count": prev.get("error_count", 0), "spider_count": prev.get("spider_count", 0),
            },
            "prev2": {
                "pv": prev2.get("pv", 0), "uv": prev2.get("uv", 0),
                "ip_count": prev2.get("ip_count", 0), "total_bytes": prev2.get("total_bytes", 0),
                "error_count": prev2.get("error_count", 0), "spider_count": prev2.get("spider_count", 0),
            },
        }
        has_cov, cov_min, cov_max, cov_rate, cov_days = self._sql_coverage_info(
            site, start_date, end_date, "site_hourly_stats")
        used_fallback = not any((curr.get("pv", 0), curr.get("ip_count", 0), curr.get("total_bytes", 0)))
        resp = {
            "status": True,
            "data": data,
            "comparison": comparison,
            "top": [{
                "name": site,
                "ip": curr.get("ip_count", 0), "uv": curr.get("uv", 0),
                "pv": curr.get("pv", 0), "request": curr.get("pv", 0),
                "traffic": curr.get("total_bytes", 0),
            }],
            "list": self._build_seven_day_list(site),
            "labels": self._format_stat_labels(data, STATS_LABELS),
            "source": "recent_log" if used_fallback else "sqlite",
            "fallback": used_fallback,
            "coverage": {
                "has_data": has_cov,
                "min_date": cov_min,
                "max_date": cov_max,
                "data_days": cov_days,
                "rate": round(cov_rate, 2),
            },
        }
        if not has_cov and used_fallback:
            resp["msg"] = "聚合数据为空，已从最近访问日志临时统计"
        elif cov_rate < 1.0:
            resp["msg"] = f"统计数据覆盖 {cov_days} 天（{round(cov_rate * 100, 1)}%），数据可能不完整"
        return resp

    def _build_seven_day_list(self, site):
        """构建近7天趋势数据"""
        seven_dates = [time.strftime("%Y-%m-%d", time.localtime(time.time() - i * 86400)) for i in range(6, -1, -1)]
        result = []
        for d in seven_dates:
            m = self._query_date_metrics(site, d)
            result.append({
                "date": d, "uv": m["uv"], "pv": m["pv"],
                "ip": m["ip_count"], "request": m["pv"],
                "traffic": m["total_bytes"],
            })
        return result

    # ---------- 趋势数据 ----------

    def get_site_trend(self, args):
        site = args.get("site", "")
        if not site:
            return {"status": False, "msg": "缺少站点名称"}
        range_key = args.get("range", "today")
        start_date, end_date = self._date_range(range_key)
        page = int(args.get("page", 1))
        page_size = int(args.get("page_size", 10))

        # 趋势数据：按小时聚合 pv/uv/total_bytes + 错误计数
        count_sql = "SELECT COUNT(*) FROM site_hourly_stats WHERE site_name=? AND hour>=? AND hour<=?"
        count_params = (site, start_date + " 00", end_date + " 23")
        data_sql = (
            "SELECT t.hour, t.pv, t.uv, t.total_bytes, "
            "COALESCE(e.err_count,0) "
            "FROM site_hourly_stats t "
            "LEFT JOIN ("
            "  SELECT date, COALESCE(SUM(request_count),0) AS err_count "
            "  FROM site_status_stats WHERE site_name=? AND status_code>=400 "
            "  AND date>=? AND date<=? GROUP BY date"
            ") e ON e.date = SUBSTR(t.hour,1,10) "
            "WHERE t.site_name=? AND t.hour>=? AND t.hour<=? "
            "ORDER BY t.hour ASC"
        )
        data_params = (site, start_date, end_date, site, start_date + " 00", end_date + " 23")
        rows, total, page, page_size = self._paginate(
            count_sql, count_params, data_sql, data_params, page, page_size
        )
        points = []
        for hour, pv, uv, total_bytes, err_count in rows:
            ip_row = self._stats_query_one(
                "SELECT COUNT(DISTINCT ip) FROM site_ip_stats WHERE site_name=? AND date=?",
                (site, hour[:10])
            )
            ip_count = ip_row[0] if ip_row else 0
            points.append({
                "time": hour,
                "pv": pv, "uv": uv, "ip": ip_count,
                "total_bytes": total_bytes, "requests": pv,
                "traffic": total_bytes,
                "errors": err_count,
            })
        has_cov, cov_min, cov_max, cov_rate, cov_days = self._sql_coverage_info(
            site, start_date, end_date, "site_hourly_stats")
        resp = {
            "status": True,
            "data": points,
            "total": total,
            "page": page,
            "page_size": page_size,
            "source": "sqlite",
            "fallback": False,
            "coverage": {
                "has_data": has_cov,
                "min_date": cov_min,
                "max_date": cov_max,
                "data_days": cov_days,
                "rate": round(cov_rate, 2),
            },
        }
        if cov_rate < 1.0:
            resp["msg"] = f"统计数据覆盖 {cov_days} 天（{round(cov_rate * 100, 1)}%），数据可能不完整"
        return resp

    @staticmethod
    def _detect_spider(ua):
        if not ua:
            return None
        ua_lower = ua.lower()
        for name in SPIDER_AGENTS:
            if name.lower() in ua_lower:
                return name
        return None

    @staticmethod
    def _detect_client(ua):
        if not ua:
            return "pc", "unknown"
        ua_lower = ua.lower()
        client_type = "mobile" if any(k in ua_lower for k in ("mobile", "android", "iphone", "ipad", "ipod")) else "pc"
        if "edg" in ua_lower or "edge" in ua_lower:
            client_name = "Edge"
        elif "chrome" in ua_lower and "chromium" not in ua_lower:
            client_name = "Chrome"
        elif "firefox" in ua_lower:
            client_name = "Firefox"
        elif "safari" in ua_lower and "chrome" not in ua_lower:
            client_name = "Safari"
        elif "msie" in ua_lower or "trident" in ua_lower:
            client_name = "IE"
        elif "go-http-client" in ua_lower:
            client_name = "Go-http-client"
        else:
            client_name = "unknown"
        return client_type, client_name

    @staticmethod
    def _nginx_date(time_local):
        try:
            m = re.search(r'(\d+)/(\w+)/(\d+):(\d+):(\d+):(\d+)', time_local or "")
            if not m:
                return ""
            day, mon, year = m.group(1), m.group(2), m.group(3)
            return f"{year}-{MONTH_MAP.get(mon, 1):02d}-{int(day):02d}"
        except Exception:
            return ""

    def _recent_parsed_logs(self, site, max_scan=2000):
        if "\0" in site or ".." in site or "/" in site or "\\" in site:
            return []
        prefix = f"{site}.log"
        base_path = os.path.normpath(os.path.join(LOG_DIR, prefix))
        if not os.path.exists(base_path):
            return []
        all_files = []
        try:
            for entry in os.listdir(LOG_DIR):
                if entry == prefix or entry.startswith(prefix + ".") or entry.startswith(prefix + "-"):
                    fpath = os.path.join(LOG_DIR, entry)
                    try:
                        all_files.append((fpath, os.path.getmtime(fpath)))
                    except OSError:
                        continue
        except OSError:
            all_files = [(base_path, 0)]
        all_files.sort(key=lambda x: x[1], reverse=True)
        collected = []
        remaining = max_scan
        for fpath, _ in all_files:
            if remaining <= 0:
                break
            lines = self._read_reverse_lines(fpath, remaining)
            collected.extend(reversed(lines))
            remaining -= len(lines)
        collected.reverse()
        result = []
        for line in collected:
            parsed = self._parse_nginx_line(line)
            if parsed:
                parsed["date"] = self._nginx_date(parsed.get("time"))
                result.append(parsed)
        return result

    @staticmethod
    def _date_in_range(date_str, start_date, end_date):
        return bool(date_str) and start_date <= date_str <= end_date

    def _recent_logs_in_range(self, site, start_date, end_date, max_scan=200000):
        return [
            item for item in self._recent_parsed_logs(site, max_scan)
            if self._date_in_range(item.get("date"), start_date, end_date)
        ]

    def _recent_metrics(self, site, start_date, end_date):
        logs = self._recent_logs_in_range(site, start_date, end_date)
        if not logs:
            return {"pv": 0, "uv": 0, "total_bytes": 0, "ip_count": 0, "error_count": 0, "spider_count": 0}
        ips = set()
        spider_count = 0
        error_count = 0
        total_bytes = 0
        for item in logs:
            ip = item.get("ip") or ""
            if ip:
                ips.add(ip)
            total_bytes += int(item.get("bytes") or 0)
            if int(item.get("status") or 0) >= 400:
                error_count += 1
            if self._detect_spider(item.get("ua")):
                spider_count += 1
        return {
            "pv": len(logs),
            "uv": len(ips),
            "total_bytes": total_bytes,
            "ip_count": len(ips),
            "error_count": error_count,
            "spider_count": spider_count,
        }

    def _recent_trend_points(self, site, start_date, end_date):
        buckets = {}
        for item in self._recent_logs_in_range(site, start_date, end_date):
            hour_key, _ = self._hour_from_parsed_log(item)
            if not hour_key:
                continue
            if hour_key not in buckets:
                buckets[hour_key] = {"pv": 0, "bytes": 0, "ips": set(), "errors": 0}
            buckets[hour_key]["pv"] += 1
            buckets[hour_key]["bytes"] += int(item.get("bytes") or 0)
            if item.get("ip"):
                buckets[hour_key]["ips"].add(item.get("ip"))
            if int(item.get("status") or 0) >= 400:
                buckets[hour_key]["errors"] += 1
        points = []
        for hour in sorted(buckets.keys()):
            data = buckets[hour]
            points.append({
                "time": hour,
                "pv": data["pv"],
                "uv": len(data["ips"]),
                "ip": len(data["ips"]),
                "total_bytes": data["bytes"],
                "requests": data["pv"],
                "traffic": data["bytes"],
                "errors": data["errors"],
            })
        return points

    @staticmethod
    def _hour_from_parsed_log(item):
        time_local = item.get("time") or ""
        try:
            m = re.search(r'(\d+)/(\w+)/(\d+):(\d+):(\d+):(\d+)', time_local)
            if not m:
                return None, None
            day, mon, year, hour = m.group(1), m.group(2), m.group(3), m.group(4)
            mon_num = MONTH_MAP.get(mon)
            if not mon_num:
                return None, None
            return f"{year}-{mon_num:02d}-{int(day):02d} {int(hour):02d}", f"{year}-{mon_num:02d}-{int(day):02d}"
        except Exception:
            return None, None

    @staticmethod
    def _empty_fallback_response(page, page_size, msg="聚合数据为空，最近日志中也没有当前时间范围的数据", sum_count=0):
        return {
            "status": True,
            "data": [],
            "total": 0,
            "page": page,
            "page_size": page_size,
            "sum_count": sum_count,
            "source": "recent_log",
            "fallback": True,
            "msg": msg,
        }

    @staticmethod
    def _slice_rank_data(data, page, page_size):
        start = (page - 1) * page_size
        return data[start:start + page_size]

    # ---------- 蜘蛛分析 ----------

    def get_site_spider_analysis(self, args):
        site = args.get("site", "")
        if not site:
            return {"status": False, "msg": "缺少站点名称"}
        range_key = args.get("range", "today")
        start_date, end_date = self._date_range(range_key)
        page = int(args.get("page", 1))
        page_size = int(args.get("page_size", 10))

        rows, total, page, page_size = self._paginate(
            "SELECT COUNT(DISTINCT spider_name) FROM site_spider_stats WHERE site_name=? AND date>=? AND date<=?",
            (site, start_date, end_date),
            "SELECT spider_name, COALESCE(SUM(request_count),0) FROM site_spider_stats "
            "WHERE site_name=? AND date>=? AND date<=? GROUP BY spider_name ORDER BY 2 DESC",
            (site, start_date, end_date),
            page, page_size
        )
        response = self._paginate_response(rows, total, page, page_size,
                                           ["spider", "count"])
        sum_row = self._stats_query_one(
            "SELECT COALESCE(SUM(request_count),0) FROM site_spider_stats "
            "WHERE site_name=? AND date>=? AND date<=?",
            (site, start_date, end_date)
        )
        response["sum_count"] = sum_row[0] if sum_row else 0
        has_sql_data, cov_min, cov_max, cov_rate, cov_days = self._sql_coverage_info(
            site, start_date, end_date, "site_spider_stats")
        response["coverage"] = {
            "has_data": has_sql_data,
            "min_date": cov_min,
            "max_date": cov_max,
            "data_days": cov_days,
            "rate": round(cov_rate, 2),
        }
        use_sql = self._should_use_sql_data(site, start_date, end_date, range_key, "site_spider_stats")
        if not use_sql:
            counter = {}
            parsed_logs = self._recent_parsed_logs(site, max_scan=self._fallback_max_scan(range_key))
            for item in parsed_logs:
                if not self._date_in_range(item.get("date"), start_date, end_date):
                    continue
                spider = self._detect_spider(item.get("ua"))
                if spider:
                    counter[spider] = counter.get(spider, 0) + 1
            data = sorted(({"spider": k, "count": v} for k, v in counter.items()), key=lambda x: x["count"], reverse=True)
            if not data:
                return self._empty_fallback_response(page, page_size)
            response.update({
                "data": self._slice_rank_data(data, page, page_size),
                "total": len(data),
                "sum_count": sum(counter.values()),
                "source": "recent_log",
                "fallback": True,
                "msg": "聚合数据为空，已从最近访问日志临时统计",
            })
        elif cov_rate < 1.0:
            response["msg"] = f"统计数据覆盖 {cov_days} 天（{round(cov_rate * 100, 1)}%），数据可能不完整"
        return response

    # ---------- 客户端统计 ----------

    def get_site_client_stats(self, args):
        site = args.get("site", "")
        if not site:
            return {"status": False, "msg": "缺少站点名称"}
        range_key = args.get("range", "today")
        start_date, end_date = self._date_range(range_key)
        page = int(args.get("page", 1))
        page_size = int(args.get("page_size", 10))

        rows, total, page, page_size = self._paginate(
            "SELECT COUNT(DISTINCT client_type || '|' || client_name) FROM site_client_stats "
            "WHERE site_name=? AND date>=? AND date<=?",
            (site, start_date, end_date),
            "SELECT client_type, client_name, COALESCE(SUM(request_count),0) FROM site_client_stats "
            "WHERE site_name=? AND date>=? AND date<=? GROUP BY client_type, client_name ORDER BY client_type, 3 DESC",
            (site, start_date, end_date),
            page, page_size
        )
        result = []
        for ctype, cname, cnt in rows:
            result.append({"client": f"{cname}({ctype})", "count": cnt, "os": cname})
        sum_row = self._stats_query_one(
            "SELECT COALESCE(SUM(request_count),0) FROM site_client_stats "
            "WHERE site_name=? AND date>=? AND date<=?",
            (site, start_date, end_date)
        )
        sum_count = sum_row[0] if sum_row else 0
        has_sql_data, cov_min, cov_max, cov_rate, cov_days = self._sql_coverage_info(
            site, start_date, end_date, "site_client_stats")
        coverage_info = {
            "has_data": has_sql_data,
            "min_date": cov_min,
            "max_date": cov_max,
            "data_days": cov_days,
            "rate": round(cov_rate, 2),
        }
        use_sql = self._should_use_sql_data(site, start_date, end_date, range_key, "site_client_stats")
        if not use_sql:
            counter = {}
            parsed_logs = self._recent_parsed_logs(site, max_scan=self._fallback_max_scan(range_key))
            for item in parsed_logs:
                if not self._date_in_range(item.get("date"), start_date, end_date):
                    continue
                ctype, cname = self._detect_client(item.get("ua"))
                key = (ctype, cname)
                counter[key] = counter.get(key, 0) + 1
            data = [
                {"client": f"{name}({ctype})", "count": count, "os": name, "client_type": ctype, "client_name": name}
                for (ctype, name), count in counter.items()
            ]
            data.sort(key=lambda x: x["count"], reverse=True)
            if not data:
                return self._empty_fallback_response(page, page_size)
            return {
                "status": True,
                "data": self._slice_rank_data(data, page, page_size),
                "total": len(data),
                "page": page,
                "page_size": page_size,
                "sum_count": sum(counter.values()),
                "source": "recent_log",
                "fallback": True,
                "msg": "聚合数据为空，已从最近访问日志临时统计",
                "coverage": coverage_info,
            }
        resp = {"status": True, "data": result, "total": total, "page": page, "page_size": page_size, "sum_count": sum_count, "source": "sqlite", "fallback": False, "coverage": coverage_info}
        if cov_rate < 1.0:
            resp["msg"] = f"统计数据覆盖 {cov_days} 天（{round(cov_rate * 100, 1)}%），数据可能不完整"
        return resp

    # ---------- 请求方式统计 ----------

    def get_site_method_stats(self, args):
        site = args.get("site", "")
        if not site:
            return {"status": False, "msg": "缺少站点名称"}
        range_key = args.get("range", "today")
        start_date, end_date = self._date_range(range_key)
        page = int(args.get("page", 1))
        page_size = int(args.get("page_size", 10))

        rows, total, page, page_size = self._paginate(
            "SELECT COUNT(DISTINCT method) FROM site_method_stats WHERE site_name=? AND date>=? AND date<=?",
            (site, start_date, end_date),
            "SELECT method, COALESCE(SUM(request_count),0) FROM site_method_stats "
            "WHERE site_name=? AND date>=? AND date<=? GROUP BY method ORDER BY 2 DESC",
            (site, start_date, end_date),
            page, page_size
        )
        response = self._paginate_response(rows, total, page, page_size,
                                           ["method", "count"])
        sum_row = self._stats_query_one(
            "SELECT COALESCE(SUM(request_count),0) FROM site_method_stats "
            "WHERE site_name=? AND date>=? AND date<=?",
            (site, start_date, end_date)
        )
        response["sum_count"] = sum_row[0] if sum_row else 0
        has_sql_data, cov_min, cov_max, cov_rate, cov_days = self._sql_coverage_info(
            site, start_date, end_date, "site_method_stats")
        response["coverage"] = {
            "has_data": has_sql_data,
            "min_date": cov_min,
            "max_date": cov_max,
            "data_days": cov_days,
            "rate": round(cov_rate, 2),
        }
        use_sql = self._should_use_sql_data(site, start_date, end_date, range_key, "site_method_stats")
        if not use_sql:
            counter = {}
            parsed_logs = self._recent_parsed_logs(site, max_scan=self._fallback_max_scan(range_key))
            for item in parsed_logs:
                if not self._date_in_range(item.get("date"), start_date, end_date):
                    continue
                method = item.get("method") or "-"
                counter[method] = counter.get(method, 0) + 1
            data = sorted(({"method": k, "count": v} for k, v in counter.items()), key=lambda x: x["count"], reverse=True)
            if not data:
                return self._empty_fallback_response(page, page_size)
            response.update({
                "data": self._slice_rank_data(data, page, page_size),
                "total": len(data),
                "sum_count": sum(counter.values()),
                "source": "recent_log",
                "fallback": True,
                "msg": "聚合数据为空，已从最近访问日志临时统计",
            })
        elif cov_rate < 1.0:
            response["msg"] = f"统计数据覆盖 {cov_days} 天（{round(cov_rate * 100, 1)}%），数据可能不完整"
        return response

    # ---------- IP 排行 ----------

    @staticmethod
    def _fallback_max_scan(range_key):
        if range_key == "30d":
            return 300000
        if range_key == "7d":
            return 100000
        return 50000

    def _sql_coverage_info(self, site, start_date, end_date, table):
        """获取 SQL 统计数据的覆盖信息，返回 (has_data, min_date, max_date, coverage_rate, data_days)"""
        row = self._stats_query_one(
            f"SELECT MIN(date), MAX(date), COUNT(DISTINCT date) FROM {table} WHERE site_name=? AND date>=? AND date<=?",
            (site, start_date, end_date)
        )
        if not row or not row[0] or not row[1]:
            return False, None, None, 0.0, 0
        min_date, max_date, data_days = row[0], row[1], int(row[2] or 0)
        expected = (datetime.strptime(end_date, "%Y-%m-%d") - datetime.strptime(start_date, "%Y-%m-%d")).days + 1
        coverage = data_days / expected if expected > 0 else 0.0
        return True, min_date, max_date, coverage, data_days

    def _should_use_sql_data(self, site, start_date, end_date, range_key, table):
        """判断是否应该使用 SQL 聚合数据（有数据就用，不要轻易 fallback）"""
        if range_key not in ("7d", "30d"):
            return True
        has_data, _, _, _, _ = self._sql_coverage_info(site, start_date, end_date, table)
        return has_data

    def get_site_ip_rank(self, args):
        site = args.get("site", "")
        if not site:
            return {"status": False, "msg": "缺少站点名称"}
        range_key = args.get("range", "today")
        start_date, end_date = self._date_range(range_key)
        page = int(args.get("page", 1))
        page_size = int(args.get("page_size", 10))

        rows, total, page, page_size = self._paginate(
            "SELECT COUNT(DISTINCT ip) FROM site_ip_stats WHERE site_name=? AND date>=? AND date<=?",
            (site, start_date, end_date),
            "SELECT ip, COALESCE(SUM(request_count),0), COALESCE(SUM(total_bytes),0) FROM site_ip_stats "
            "WHERE site_name=? AND date>=? AND date<=? GROUP BY ip ORDER BY 2 DESC",
            (site, start_date, end_date),
            page, page_size
        )
        response = self._paginate_response(rows, total, page, page_size,
                                           ["ip", "count", "bytes"])
        sum_row = self._stats_query_one(
            "SELECT COALESCE(SUM(request_count),0) FROM site_ip_stats "
            "WHERE site_name=? AND date>=? AND date<=?",
            (site, start_date, end_date)
        )
        response["sum_count"] = sum_row[0] if sum_row else 0
        has_sql_data, cov_min, cov_max, cov_rate, cov_days = self._sql_coverage_info(
            site, start_date, end_date, "site_ip_stats")
        response["coverage"] = {
            "has_data": has_sql_data,
            "min_date": cov_min,
            "max_date": cov_max,
            "data_days": cov_days,
            "rate": round(cov_rate, 2),
        }
        use_sql = self._should_use_sql_data(site, start_date, end_date, range_key, "site_ip_stats")
        if not use_sql:
            counter = {}
            parsed_logs = self._recent_parsed_logs(site, max_scan=self._fallback_max_scan(range_key))
            for item in parsed_logs:
                if not self._date_in_range(item.get("date"), start_date, end_date):
                    continue
                ip = item.get("ip") or "-"
                if ip not in counter:
                    counter[ip] = {"count": 0, "bytes": 0}
                counter[ip]["count"] += 1
                counter[ip]["bytes"] += int(item.get("bytes") or 0)
            data = [
                {"ip": ip, "count": item["count"], "bytes": item["bytes"]}
                for ip, item in counter.items()
            ]
            data.sort(key=lambda x: x["count"], reverse=True)
            if not data:
                return self._empty_fallback_response(page, page_size)
            response.update({
                "data": self._slice_rank_data(data, page, page_size),
                "total": len(data),
                "sum_count": sum(item["count"] for item in counter.values()),
                "source": "recent_log",
                "fallback": True,
                "msg": "聚合数据为空，已从最近访问日志临时统计",
            })
        elif cov_rate < 1.0:
            response["msg"] = f"统计数据覆盖 {cov_days} 天（{round(cov_rate * 100, 1)}%），数据可能不完整"
        return response

    # ---------- URL 排行 ----------

    def get_site_uri_rank(self, args):
        site = args.get("site", "")
        if not site:
            return {"status": False, "msg": "缺少站点名称"}
        range_key = args.get("range", "today")
        start_date, end_date = self._date_range(range_key)
        page = int(args.get("page", 1))
        page_size = int(args.get("page_size", 10))

        rows, total, page, page_size = self._paginate(
            "SELECT COUNT(DISTINCT uri) FROM site_uri_stats WHERE site_name=? AND date>=? AND date<=?",
            (site, start_date, end_date),
            "SELECT uri, COALESCE(SUM(request_count),0), COALESCE(SUM(total_bytes),0) FROM site_uri_stats "
            "WHERE site_name=? AND date>=? AND date<=? GROUP BY uri ORDER BY 2 DESC",
            (site, start_date, end_date),
            page, page_size
        )
        response = self._paginate_response(rows, total, page, page_size,
                                           ["uri", "count", "bytes"])
        sum_row = self._stats_query_one(
            "SELECT COALESCE(SUM(request_count),0) FROM site_uri_stats "
            "WHERE site_name=? AND date>=? AND date<=?",
            (site, start_date, end_date)
        )
        response["sum_count"] = sum_row[0] if sum_row else 0
        has_sql_data, cov_min, cov_max, cov_rate, cov_days = self._sql_coverage_info(
            site, start_date, end_date, "site_uri_stats")
        response["coverage"] = {
            "has_data": has_sql_data,
            "min_date": cov_min,
            "max_date": cov_max,
            "data_days": cov_days,
            "rate": round(cov_rate, 2),
        }
        use_sql = self._should_use_sql_data(site, start_date, end_date, range_key, "site_uri_stats")
        if not use_sql:
            counter = {}
            parsed_logs = self._recent_parsed_logs(site, max_scan=self._fallback_max_scan(range_key))
            for item in parsed_logs:
                if not self._date_in_range(item.get("date"), start_date, end_date):
                    continue
                uri = item.get("uri") or "-"
                if uri not in counter:
                    counter[uri] = {"count": 0, "bytes": 0}
                counter[uri]["count"] += 1
                counter[uri]["bytes"] += int(item.get("bytes") or 0)
            data = [
                {"uri": uri, "count": item["count"], "bytes": item["bytes"]}
                for uri, item in counter.items()
            ]
            data.sort(key=lambda x: x["count"], reverse=True)
            if not data:
                return self._empty_fallback_response(page, page_size)
            response.update({
                "data": self._slice_rank_data(data, page, page_size),
                "total": len(data),
                "sum_count": sum(item["count"] for item in counter.values()),
                "source": "recent_log",
                "fallback": True,
                "msg": "聚合数据为空，已从最近访问日志临时统计",
            })
        elif cov_rate < 1.0:
            response["msg"] = f"统计数据覆盖 {cov_days} 天（{round(cov_rate * 100, 1)}%），数据可能不完整"
        return response

    # ---------- 错误日志 ----------

    def get_site_error_logs(self, args):
        site = args.get("site", "")
        if not site:
            return {"status": False, "msg": "缺少站点名称"}
        range_key = args.get("range", "today")
        start_date, end_date = self._date_range(range_key)
        page = int(args.get("page", 1))
        page_size = int(args.get("page_size", 10))
        min_status = int(args.get("min_status", 400))

        rows, total, page, page_size = self._paginate(
            "SELECT COUNT(*) FROM site_error_logs "
            "WHERE site_name=? AND status>=? AND date>=? AND date<=?",
            (site, min_status, start_date, end_date),
            "SELECT time_local, ip, method, uri, status, bytes, referer, ua FROM site_error_logs "
            "WHERE site_name=? AND status>=? AND date>=? AND date<=? ORDER BY id DESC",
            (site, min_status, start_date, end_date),
            page, page_size
        )
        response = self._paginate_response(rows, total, page, page_size,
                                           ["time", "ip", "method", "uri", "status", "bytes", "referer", "ua"])
        has_sql_data, cov_min, cov_max, cov_rate, cov_days = self._sql_coverage_info(
            site, start_date, end_date, "site_error_logs")
        response["coverage"] = {
            "has_data": has_sql_data,
            "min_date": cov_min,
            "max_date": cov_max,
            "data_days": cov_days,
            "rate": round(cov_rate, 2),
        }
        use_sql = self._should_use_sql_data(site, start_date, end_date, range_key, "site_error_logs")
        if not use_sql:
            data = []
            parsed_logs = self._recent_parsed_logs(site, max_scan=self._fallback_max_scan(range_key))
            for item in parsed_logs:
                if self._date_in_range(item.get("date"), start_date, end_date) and int(item.get("status") or 0) >= min_status:
                    data.append(item)
            if not data:
                return self._empty_fallback_response(page, page_size)
            response.update({
                "data": self._slice_rank_data(data, page, page_size),
                "total": len(data),
                "sum_count": len(data),
                "source": "recent_log",
                "fallback": True,
                "msg": "聚合数据为空，已从最近访问日志临时筛选错误请求",
            })
        else:
            response["sum_count"] = total
            if cov_rate < 1.0:
                response["msg"] = f"统计数据覆盖 {cov_days} 天（{round(cov_rate * 100, 1)}%），数据可能不完整"
        return response

    # ---------- 网站日志（反向读取 nginx 日志，优化版）----------

    @staticmethod
    def _read_reverse_lines(path, n):
        try:
            with open(path, "rb") as handle:
                handle.seek(0, os.SEEK_END)
                size = handle.tell()
                if size == 0:
                    return []
                lines = []
                buf = b""
                pos = size
                chunk_size = min(max(65536, size // 64), 1048576)
                while pos > 0 and len(lines) < n:
                    chunk_size = min(chunk_size, pos)
                    pos -= chunk_size
                    handle.seek(pos)
                    buf = handle.read(chunk_size) + buf
                    while len(lines) < n:
                        idx = buf.rfind(b"\n")
                        if idx == -1:
                            break
                        line = buf[idx + 1:].decode("utf-8", errors="replace")
                        buf = buf[:idx]
                        if line or len(lines) > 0:
                            lines.append(line)
                if buf and len(lines) < n:
                    lines.append(buf.decode("utf-8", errors="replace"))
                lines.reverse()
                return lines[:n]
        except OSError:
            return []

    def get_site_recent_logs(self, args):
        site = args.get("site", "")
        if not site:
            return {"status": False, "msg": "缺少站点名称"}
        if "\0" in site or ".." in site or "/" in site or "\\" in site:
            return {"status": False, "msg": "非法的站点名称"}
        log_path = os.path.normpath(os.path.join(LOG_DIR, f"{site}.log"))
        if not log_path.startswith(os.path.normpath(LOG_DIR)):
            return {"status": False, "msg": "非法的站点名称"}
        page = int(args.get("page", 1))
        page_size = min(int(args.get("page_size", 10)), 200)

        if not os.path.exists(log_path):
            return {"status": False, "msg": f"日志文件不存在: {log_path}"}

        try:
            max_scan = min(max(page * page_size + 1, 500), 1000)
            raw_lines = list(reversed(self._read_reverse_lines(log_path, max_scan)))
            start = (page - 1) * page_size
            page_lines = raw_lines[start:start + page_size]
            if not page_lines:
                return {"status": True, "data": [], "total": len(raw_lines), "page": page, "page_size": page_size}
            parsed = []
            for line in page_lines:
                p = self._parse_nginx_line(line)
                if p:
                    parsed.append(p)
                else:
                    parsed.append({"raw": line})
            return {"status": True, "data": parsed, "total": len(raw_lines), "page": page, "page_size": page_size}
        except Exception as exc:
            return {"status": False, "msg": str(exc)}

    @staticmethod
    def _parse_nginx_line(line):
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
                "time": time_local, "ip": ip, "method": method,
                "uri": uri, "status": status, "bytes": body_bytes,
                "referer": referer, "ua": ua,
            }
        except Exception:
            return None
