#!/bin/bash
set -e

PLUGIN_DIR="/www/server/panel/plugin/mnbt_connector"
cd "$PLUGIN_DIR"

Install() {
  if [ ! -f config.json ]; then
    cat > config.json << 'EOF'
{
  "node_id": "",
  "api_url": "",
  "api_key": "",
  "interval_seconds": 10,
  "forbidden_scan": {
    "enabled": false,
    "content": "",
    "skip_dirs": "node_modules,.git,.svn,.idea,vendor,cache,logs,temp,upload,uploads,backup,bak",
    "skip_exts": "png,jpg,jpeg,gif,ico,svg,webp,mp4,mp3,avi,flv,rar,zip,7z,gz,tar,bz2,woff,woff2,ttf,eot,pdf,doc,docx,xls,xlsx,ppt,pptx,swf",
    "max_file_size": 5242880,
    "max_matches": 1000
  }
}
EOF
  fi

  chmod +x worker.py mnbt_connector_main.py || true
  touch worker.log || true

  if command -v systemctl &>/dev/null; then
    cat > /etc/systemd/system/mnbt-connector.service << EOF
[Unit]
Description=MNBT Connector Service
After=network.target

[Service]
Type=simple
ExecStart=/usr/bin/python $PLUGIN_DIR/worker.py
WorkingDirectory=$PLUGIN_DIR
Restart=on-failure
RestartSec=10
StandardOutput=append:$PLUGIN_DIR/worker.log
StandardError=inherit

[Install]
WantedBy=multi-user.target
EOF
    systemctl daemon-reload
    systemctl enable mnbt-connector.service
    systemctl start mnbt-connector.service
    echo "MNBT 连接器 systemd 服务已安装并启动（开机自启 + 崩溃自恢复）"
  else
    echo "MNBT 连接器已安装。请编辑 config.json 配置，然后在宝塔插件页面手动启动"
  fi
}

Uninstall() {
  echo "开始卸载 MNBT 连接器..."

  if command -v systemctl &>/dev/null; then
    systemctl stop mnbt-connector.service 2>/dev/null || true
    systemctl disable mnbt-connector.service 2>/dev/null || true
    rm -f /etc/systemd/system/mnbt-connector.service
    systemctl daemon-reload
    echo "已移除 systemd 服务"
  fi

  rm -f /www/server/panel/vhost/nginx/mnbt_cc.conf
  rm -f /www/server/panel/vhost/nginx/mnbt_waf.conf

  if command -v nginx &>/dev/null; then
    nginx -t && nginx -s reload 2>/dev/null || true
    echo "已重载 Nginx"
  fi

  echo "卸载完成"
}

if [ "${1}" == 'install' ]; then
  Install
elif [ "${1}" == 'update' ]; then
  Install
elif [ "${1}" == 'uninstall' ]; then
  Uninstall
fi
