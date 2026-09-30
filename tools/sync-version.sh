#!/usr/bin/env bash
# 版本同步：以 MPHX/BL.php 的 $WEBQB 为唯一数据源，回写仓库里的 Markdown 版本标记。
#
# 为什么需要它：PHP 运行时（安装向导 / 后台 / API）已全部改成从 $WEBQB 计算，
# 改版本只动 BL.php 一处；但 README 与 docs 的 shields.io 徽章是静态 Markdown，
# PHP 渲染不到，只能靠脚本同步。发版流程：改 BL.php 的 $WEBQB → 跑这个脚本 → 提交。
#
# 用法： bash tools/sync-version.sh
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
bl="$root/MPHX/BL.php"

n="$(grep -oE "\\\$WEBQB[[:space:]]*=[[:space:]]*'[0-9]+'" "$bl" | grep -oE '[0-9]+')"
[ -n "$n" ] || { echo "未能从 BL.php 解析 \$WEBQB" >&2; exit 1; }

# 与 PHP sprintf('%.2f', $WEBQB/1000) 保持一致
ver="$(awk "BEGIN{printf \"%.2f\", $n/1000}")"
disp="V$ver"

echo "BL.php \$WEBQB=$n  =>  显示版本 $disp"

# 1) README 标题行：锚定 "(MNBT) V<旧版本>"（版本在行尾）
sed -i -E "s/\(MNBT\) V[0-9][0-9.]*/(MNBT) ${disp}/" "$root/README.md"

# 2) 两个文件的 shields.io 版本徽章
sed -i -E "s#badge/version-[0-9][0-9.]*-green#badge/version-${ver}-green#g" "$root/README.md" "$root/docs/index.md"

# 3) docs 首页更新日志区间：锚定 "→ V<新版本>）"
sed -i -E "s/→ *V[0-9][0-9.]*）/→ ${disp}）/" "$root/docs/index.md"

echo "已同步 README.md 与 docs/index.md（changelog 各版本标题需按发版手工追加）"
