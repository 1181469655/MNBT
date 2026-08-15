<?php
/**
 * 用户端 - 主机详情（实时状态 / 流量 / 操作）
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '主机详情';
$host = $host ?? null;
$info = $info ?? ['ok' => false, 'msg' => ''];
$traffic = $traffic ?? ['ok' => false, 'data' => []];
$dcim = $dcim ?? ['ok' => false, 'data' => []];
$config_options = $config_options ?? [];
$custom_fields = $custom_fields ?? [];
$os_list = $os_list ?? [];
$os_groups = $os_groups ?? [];
$os_error = $os_error ?? '';

$password = zjmf_decrypt((string)$host['password']);
$hasUpId = (int)$host['up_host_id'] > 0;

// 上游详情字段（label => [显示名, 分组]）。价格 / 付款相关字段一律不展示。
$up_fields = [
	'productname'              => ['产品名', '产品信息'],
	'groupname'                => ['产品组名', '产品信息'],
	'os'                       => ['操作系统', '产品信息'],
	'domain'                   => ['主机名', '产品信息'],
	'dedicatedip'              => ['独立 IP', '网络信息'],
	'assignedips'              => ['附加 IP', '网络信息'],
	'ip_num'                   => ['IP 数量', '网络信息'],
	'port'                     => ['端口', '网络信息'],
	'username'                 => ['服务器用户名', '账号信息'],
	'password'                 => ['服务器密码', '账号信息'],
	'regdate'                  => ['开通时间', '生命周期'],
	'nextduedate'              => ['到期时间', '生命周期'],
	'domainstatus'             => ['产品状态', '生命周期'],
	'suspendreason'            => ['暂停原因', '其他'],
	'auto_terminate_end_cycle' => ['到期自动取消', '其他'],
	'auto_terminate_reason'    => ['取消原因', '其他'],
	'bwusage'                  => ['当前使用流量', '其他'],
	'bwlimit'                  => ['流量上限', '其他'],
	'remark'                   => ['备注', '其他'],
	'allow_upgrade_config'     => ['支持升级配置', '其他'],
	'allow_upgrade_product'    => ['支持升级产品', '其他'],
	'show_traffic_usage'       => ['显示用量图', '其他'],
	'productid'                => ['产品 ID', '其他'],
	'serverid'                 => ['服务器 ID', '其他'],
	'ordernum'                 => ['上游订单号', '其他'],
];
$up_group_order = ['产品信息', '网络信息', '账号信息', '生命周期', '其他'];
$up_groups = [];
if (is_array($info['data']) && $info['data'] !== []) {
	foreach ($up_fields as $k => $cfg) {
		[$label, $group] = $cfg;
		if (!array_key_exists($k, $info['data'])) {
			continue;
		}
		$v = $info['data'][$k];
		if ($v === null || $v === '' || $v === []) {
			continue;
		}
		// 时间戳 → 本地日期时间
		if (in_array($k, ['regdate', 'nextduedate'], true) && is_numeric($v)) {
			$v = zjmf_normalize_date((string)$v);
		}
		// 布尔开关
		if (in_array($k, ['auto_terminate_end_cycle', 'allow_upgrade_config', 'allow_upgrade_product', 'show_traffic_usage'], true)) {
			$v = (string)$v === '1' ? '是' : ((string)$v === '0' ? '否' : $v);
		}
		// 流量上限 0 表示不限
		if ($k === 'bwlimit' && (string)$v === '0') {
			$v = '不限';
		}
		// 状态转中文
		if ($k === 'domainstatus') {
			$v = zjmf_host_status_label(zjmf_map_upstream_status((string)$v));
		}
		// 上游 password 为明文，直接展示（存本地库时才加密）
		if ($k === 'password') {
			$v = (string)$v === '' ? '(空)' : $v;
		}
		if (is_array($v)) {
			$v = json_encode($v, JSON_UNESCAPED_UNICODE);
		}
		if ((string)$v === '') {
			continue;
		}
		$up_groups[$group][] = ['label' => $label, 'value' => (string)$v];
	}
}

// 端口：host/product 的 host_data.port 是真实端口，优先使用；
// 为空 / 0 时再依次从配置选项、自定义字段中识别（名称含“端口/port”），同样排除无效值
$displayPort = '';
$rawPort = trim((string)($info['data']['port'] ?? ''));
if ($rawPort !== '' && $rawPort !== '0') {
	$displayPort = $rawPort;
}
if ($displayPort === '') {
	foreach (($config_options ?: []) as $opt) {
		$optName = strtolower((string)($opt['name'] ?? ''));
		if ($optName !== '' && (strpos($optName, '端口') !== false || strpos($optName, 'port') !== false)) {
			$pv = trim((string)($opt['sub_name'] ?? ''));
			if ($pv !== '' && $pv !== '0') {
				$displayPort = $pv;
				break;
			}
		}
	}
}
if ($displayPort === '') {
	foreach (($custom_fields ?: []) as $cf) {
		$cfName = strtolower((string)($cf['fieldname'] ?? $cf['name'] ?? ''));
		if ($cfName !== '' && (strpos($cfName, '端口') !== false || strpos($cfName, 'port') !== false)) {
			$cfv = trim((string)($cf['value'] ?? ''));
			if ($cfv !== '' && $cfv !== '0') {
				$displayPort = $cfv;
				break;
			}
		}
	}
}
// DCIM 交换机端口名兜底（dcim/detail 的 switch[].name）
if ($displayPort === '') {
	$switchList = $dcim['data']['detail']['switch'] ?? [];
	if (is_array($switchList) && isset($switchList[0]['name'])) {
		$displayPort = trim((string)$switchList[0]['name']);
	}
}
if ($displayPort === '0') {
	$displayPort = '';
}
// 端口行统一用正确端口，无效（空 / 0）则不展示
if ($displayPort !== '' && isset($up_groups['网络信息'])) {
	foreach ($up_groups['网络信息'] as $i => $r) {
		if ($r['label'] === '端口') {
			$up_groups['网络信息'][$i]['value'] = $displayPort;
			break;
		}
	}
}
// 配置选项：剔除已单独展示的端口项，避免重复
if (is_array($config_options)) {
	$config_options = array_values(array_filter($config_options, function ($opt) {
		$name = strtolower((string)($opt['name'] ?? ''));
		return !($name !== '' && (strpos($name, '端口') !== false || strpos($name, 'port') !== false));
	}));
}

$liveIp = (string)($info['data']['dedicatedip'] ?? $info['data']['ip'] ?? '');

function zjmf_view_show_traffic($data)
{
	if (!is_array($data) || $data === []) {
		return '暂无流量数据';
	}
	// 常见标量字段直接展示
	$rows = [];
	foreach (['total', 'used', 'free', 'percent', 'current', 'monthly_used'] as $k) {
		if (isset($data[$k]) && $data[$k] !== '' && $data[$k] !== null) {
			$rows[] = $k . '：' . htmlspecialchars((string)$data[$k]);
		}
	}
	if ($rows) {
		return implode('<br>', $rows);
	}
	return htmlspecialchars(json_encode($data, JSON_UNESCAPED_UNICODE));
}
ob_start();
?>
<style>
.zj-hd{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;
  margin-bottom:20px;padding:24px 26px;border-radius:12px;color:#fff;
  background:linear-gradient(120deg,#1677e0 0%,#3fa9ff 55%,#63c3ff 100%);}
.zj-hd-title{font-size:22px;font-weight:700;margin:0 0 6px;color:#fff;letter-spacing:.3px;}
.zj-hd-meta{font-size:12px;color:rgba(255,255,255,.88);}
.zj-hd-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.zj-btn-ghost{display:inline-flex;align-items:center;padding:7px 16px;border-radius:20px;
  background:rgba(255,255,255,.16);color:#fff;font-size:13px;text-decoration:none;transition:background .15s;}
.zj-btn-ghost:hover{background:rgba(255,255,255,.3);color:#fff;text-decoration:none;}
.zj-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;}
.zj-stat{background:#fff;border:1px solid #eef0f3;border-radius:12px;padding:16px 18px;
  box-shadow:0 1px 2px rgba(16,24,40,.04);}
.zj-stat-label{font-size:12px;color:#98a2b3;margin-bottom:8px;}
.zj-stat-value{font-size:15px;font-weight:600;color:#1f2329;word-break:break-all;line-height:1.5;}
.zj-panel{background:#fff;border:1px solid #eef0f3;border-radius:12px;margin-bottom:20px;
  overflow:hidden;box-shadow:0 1px 2px rgba(16,24,40,.04);}
.zj-panel-title{display:flex;align-items:center;gap:8px;font-size:15px;font-weight:600;color:#1f2329;
  padding:14px 20px;border-bottom:1px solid #f2f3f5;}
.zj-panel-title::before{content:'';width:4px;height:14px;border-radius:2px;background:#1e9fff;}
.zj-panel-body{padding:6px 20px 16px;}
.zj-group{margin-top:12px;}
.zj-group-title{font-size:12px;color:#98a2b3;font-weight:600;margin-bottom:2px;letter-spacing:.5px;}
.zj-kv{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 36px;}
.zj-kv-item{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;
  padding:9px 0;border-bottom:1px dashed #f0f1f3;font-size:13px;}
.zj-kv-label{color:#86909c;flex-shrink:0;}
.zj-kv-value{color:#1f2329;text-align:right;word-break:break-all;}
.zj-tip{font-size:12px;color:#98a2b3;}
.zj-panel-body .zj-desc2{padding:6px 0 0;font-size:13px;color:#555;line-height:1.7;}
@media(max-width:760px){
  .zj-stats{grid-template-columns:repeat(2,1fr);}
  .zj-kv{grid-template-columns:1fr;}
  .zj-kv-item{flex-direction:column;gap:2px;}
  .zj-kv-value{text-align:left;}
}
</style>

<div class="zj-hd">
  <div>
    <h1 class="zj-hd-title"><?= htmlspecialchars($host['name']) ?></h1>
    <div class="zj-hd-meta">
      <?= htmlspecialchars(($host['supplier_name'] ?? '') ?: '魔方财务上游') ?>
      <?php if (!empty($host['product_name'])): ?>
        · <?= htmlspecialchars($host['product_name']) ?>
      <?php endif; ?>
      · 上游主机 ID：<?= (int)$host['up_host_id'] ?>
    </div>
  </div>
  <div class="zj-hd-right">
    <a class="zj-btn-ghost" href="<?= zjmf_url('reserve/hosts') ?>">返回主机列表</a>
  </div>
</div>

<div class="zj-msg" id="zjf-msg"></div>

<div class="zj-stats">
  <div class="zj-stat">
    <div class="zj-stat-label">状态</div>
    <div class="zj-stat-value">
      <span class="zj-status zj-status-<?= htmlspecialchars($host['status']) ?>">
        <?= htmlspecialchars(zjmf_host_status_label($host['status'])) ?>
      </span>
      <?php if (!empty($info['ok']) && $info['status'] !== $host['status']): ?>
        <span class="zj-tip">实时：<?= htmlspecialchars(zjmf_host_status_label($info['status'])) ?></span>
      <?php endif; ?>
    </div>
  </div>
  <div class="zj-stat">
    <div class="zj-stat-label">IP 地址</div>
    <div class="zj-stat-value zj-mono"><?= htmlspecialchars($liveIp ?: '-') ?></div>
  </div>
  <div class="zj-stat">
    <div class="zj-stat-label">端口</div>
    <div class="zj-stat-value zj-mono"><?= htmlspecialchars($displayPort ?: '-') ?></div>
  </div>
  <div class="zj-stat">
    <div class="zj-stat-label">到期时间</div>
    <div class="zj-stat-value"><?= htmlspecialchars($host['renew_date'] ?: '-') ?></div>
  </div>
  <div class="zj-stat">
    <div class="zj-stat-label">用户名</div>
    <div class="zj-stat-value zj-mono"><?= htmlspecialchars($host['username'] ?: '-') ?></div>
  </div>
  <div class="zj-stat">
    <div class="zj-stat-label">密码</div>
    <div class="zj-stat-value zj-mono"><?= htmlspecialchars($password ?: '-') ?></div>
  </div>
  <div class="zj-stat">
    <div class="zj-stat-label">周期</div>
    <div class="zj-stat-value"><?= htmlspecialchars($host['cycle'] ?: '-') ?></div>
  </div>
  <div class="zj-stat">
    <div class="zj-stat-label">上游主机 ID</div>
    <div class="zj-stat-value zj-mono"><?= (int)$host['up_host_id'] ?></div>
  </div>
</div>

<?php if ($up_groups !== []): ?>
<div class="zj-panel">
  <div class="zj-panel-title">上游详情</div>
  <div class="zj-panel-body">
    <?php foreach ($up_group_order as $gname): ?>
      <?php if (empty($up_groups[$gname])) continue; ?>
      <div class="zj-group">
        <div class="zj-group-title"><?= htmlspecialchars($gname) ?></div>
        <div class="zj-kv">
          <?php foreach ($up_groups[$gname] as $r): ?>
            <div class="zj-kv-item">
              <span class="zj-kv-label"><?= htmlspecialchars($r['label']) ?></span>
              <span class="zj-kv-value <?= in_array($r['label'], ['独立 IP', '附加 IP', '服务器用户名', '服务器密码', '端口', '上游订单号'], true) ? 'zj-mono' : '' ?>"><?=
                htmlspecialchars($r['value'])
              ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if ($config_options !== []): ?>
<div class="zj-panel">
  <div class="zj-panel-title">配置选项</div>
  <div class="zj-panel-body">
    <div class="zj-kv">
      <?php foreach ($config_options as $opt): ?>
        <div class="zj-kv-item">
          <span class="zj-kv-label"><?= htmlspecialchars((string)($opt['name'] ?? '')) ?></span>
          <span class="zj-kv-value"><?= htmlspecialchars((string)($opt['sub_name'] ?? '')) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($custom_fields !== []): ?>
<div class="zj-panel">
  <div class="zj-panel-title">自定义字段</div>
  <div class="zj-panel-body">
    <div class="zj-kv">
      <?php foreach ($custom_fields as $cf): ?>
        <div class="zj-kv-item">
          <span class="zj-kv-label"><?= htmlspecialchars((string)($cf['fieldname'] ?? $cf['name'] ?? '')) ?></span>
          <span class="zj-kv-value"><?= htmlspecialchars((string)($cf['value'] ?? '')) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php
// DCIM 信息（电源/交换机端口/重装次数/任务进度），仅 DCIM 产品返回
$dcimData = is_array($dcim['data'] ?? null) ? $dcim['data'] : [];
$dcimPower = (string)($dcimData['power'] ?? '');
$dcimSwitch = is_array($dcimData['detail']['switch'] ?? null) ? $dcimData['detail']['switch'] : [];
$dcimReinstall = is_array($dcimData['reinstall'] ?? null) ? $dcimData['reinstall'] : [];
$dcimTask = is_array($dcimData['task'] ?? null) ? $dcimData['task'] : [];
$hasDcim = $dcimPower !== '' || $dcimSwitch !== [] || $dcimReinstall !== [] || isset($dcimTask['progress']) || !empty($dcimTask['step']);
$taskTypeMap = ['0' => '重装系统', '1' => '救援系统', '2' => '重置密码', '3' => '获取硬件信息'];
?>
<?php if ($hasDcim): ?>
<div class="zj-panel">
  <div class="zj-panel-title">DCIM 信息</div>
  <div class="zj-panel-body">
    <?php if ($dcimPower !== ''): ?>
      <div class="zj-kv">
        <div class="zj-kv-item">
          <span class="zj-kv-label">电源状态</span>
          <span class="zj-kv-value">
            <?php if ($dcimPower === 'on'): ?><span class="zj-status zj-status-active">已开机</span>
            <?php elseif ($dcimPower === 'off'): ?><span class="zj-status zj-status-suspend">已关机</span>
            <?php else: ?><span class="zj-tip"><?= htmlspecialchars($dcimPower . ($dcimData['power_msg'] !== '' ? '（' . $dcimData['power_msg'] . '）' : '')) ?></span>
            <?php endif; ?>
          </span>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($dcimSwitch !== []): ?>
      <div class="zj-group">
        <div class="zj-group-title">交换机端口</div>
        <div class="zj-kv">
          <?php foreach ($dcimSwitch as $sw): ?>
            <div class="zj-kv-item">
              <span class="zj-kv-label">交换机 #<?= htmlspecialchars((string)($sw['switch_id'] ?? '')) ?></span>
              <span class="zj-kv-value zj-mono"><?= htmlspecialchars((string)($sw['name'] ?? '-')) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($dcimReinstall !== []): ?>
      <div class="zj-group">
        <div class="zj-group-title">重装次数</div>
        <div class="zj-kv">
          <div class="zj-kv-item">
            <span class="zj-kv-label">本周已用 / 上限</span>
            <span class="zj-kv-value">
              <?= htmlspecialchars((string)($dcimReinstall['num'] ?? '-')) ?> /
              <?= htmlspecialchars((string)($dcimReinstall['max_times'] ?? '0')) ?>
              <?php if ((string)($dcimReinstall['max_times'] ?? '') === '0'): ?><span class="zj-tip">（不限）</span><?php endif; ?>
              <?php if (!empty($dcimReinstall['price'])): ?><span class="zj-tip">已达上限，可购买重装次数</span><?php endif; ?>
            </span>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <?php if (isset($dcimTask['progress']) || !empty($dcimTask['step'])): ?>
      <div class="zj-group">
        <div class="zj-group-title">当前任务</div>
        <div class="zj-kv">
          <div class="zj-kv-item">
            <span class="zj-kv-label">任务进度</span>
            <span class="zj-kv-value">
              <?= htmlspecialchars((string)($dcimTask['progress'] ?? '0')) ?>%
              <?php if (isset($taskTypeMap[(string)($dcimTask['task_type'] ?? '')])): ?>
                <span class="zj-tip">（<?= htmlspecialchars($taskTypeMap[(string)$dcimTask['task_type']]) ?>）</span>
              <?php endif; ?>
            </span>
          </div>
          <?php if (!empty($dcimTask['step'])): ?>
            <div class="zj-kv-item"><span class="zj-kv-label">当前步骤</span><span class="zj-kv-value"><?= htmlspecialchars((string)$dcimTask['step']) ?></span></div>
          <?php endif; ?>
          <?php if (!empty($dcimTask['last_result']['status'])): ?>
            <div class="zj-kv-item"><span class="zj-kv-label">上次结果</span><span class="zj-kv-value"><?= (int)$dcimTask['last_result']['status'] === 1 ? '成功' : '失败' ?></span></div>
          <?php endif; ?>
          <?php if (!empty($dcimTask['error_msg'])): ?>
            <div class="zj-kv-item"><span class="zj-kv-label">提示</span><span class="zj-kv-value" style="color:#c62828;"><?= htmlspecialchars((string)$dcimTask['error_msg']) ?></span></div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="zj-panel">
  <div class="zj-panel-title">流量使用</div>
  <div class="zj-panel-body">
    <?php if (!empty($traffic['ok'])): ?>
      <div class="zj-desc2"><?= zjmf_view_show_traffic($traffic['data']) ?></div>
    <?php else: ?>
      <span class="zj-tip"><?= (($traffic['msg'] ?? '') ?: '流量查询失败') ?></span>
    <?php endif; ?>
  </div>
</div>

<?php if ($hasUpId): ?>
<div class="zj-panel">
  <div class="zj-panel-title">主机操作</div>
  <div class="zj-panel-body">
    <p class="zj-tip" style="margin:8px 0 14px;">重启 / 重装 / 救援 / 重置密码为高危操作，请谨慎执行。</p>
    <button type="button" class="layui-btn layui-btn-sm" data-act="on">开机</button>
    <button type="button" class="layui-btn layui-btn-sm layui-btn-warm" data-act="off">关机</button>
    <button type="button" class="layui-btn layui-btn-sm layui-btn-warm" data-act="reboot">重启</button>
    <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" data-act="reinstall">重装系统</button>
    <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" data-act="rescue">救援系统</button>
    <button type="button" class="layui-btn layui-btn-sm" data-act="reset_password">重置密码</button>
    <button type="button" class="layui-btn layui-btn-sm" data-act="bmc">重置 BMC</button>
    <button type="button" class="layui-btn layui-btn-sm" data-act="cancel_task">取消任务</button>

    <div id="zjf-pass-panel" style="display:none;margin-top:14px;padding:14px;
      background:#fafbfc;border:1px solid #eee;border-radius:6px;">
      <input type="password" id="zjf-pass-input" class="layui-input" style="max-width:260px;"
             placeholder="输入新密码（至少 6 位）">
      <button type="button" class="layui-btn layui-btn-sm" id="zjf-pass-confirm"
              style="margin-top:10px;">确认重置</button>
    </div>

    <?php if ($os_list !== []): ?>
    <div id="zjf-reinstall-panel" style="display:none;margin-top:14px;padding:14px;
      background:#fafbfc;border:1px solid #eee;border-radius:6px;">
      <select id="zjf-os-select" class="layui-input" style="max-width:280px;display:inline-block;">
        <option value="">请选择操作系统</option>
        <?php
        // 系统列表按分组渲染（host/dedicatedserver 返回 cloud_os + cloud_os_group）
        $groupNames = [];
        foreach ($os_groups as $g) {
            $groupNames[(string)($g['id'] ?? '')] = (string)($g['name'] ?? '');
        }
        $osByGroup = [];
        foreach ($os_list as $os) {
            $osByGroup[(string)($os['group'] ?? '')][] = $os;
        }
        if ($osByGroup === []) {
            $osByGroup[''] = $os_list;
        }
        foreach ($osByGroup as $gid => $items):
          $gLabel = $gid !== '' ? (string)($groupNames[$gid] ?? $gid) : '';
        ?>
          <?php if ($gLabel !== ''): ?>
          <optgroup label="<?= htmlspecialchars($gLabel) ?>">
          <?php endif; ?>
          <?php foreach ($items as $os): ?>
            <option value="<?= (int)($os['id'] ?? 0) ?>" data-group="<?= htmlspecialchars($gLabel) ?>"><?= htmlspecialchars((string)($os['name'] ?? '')) ?></option>
          <?php endforeach; ?>
          <?php if ($gLabel !== ''): ?>
          </optgroup>
          <?php endif; ?>
        <?php endforeach; ?>
      </select>
      <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" id="zjf-reinstall-confirm"
              style="margin-top:10px;">确认重装</button>
    </div>
    <?php endif; ?>

    <div id="zjf-rescue-panel" style="display:none;margin-top:14px;padding:14px;
      background:#fafbfc;border:1px solid #eee;border-radius:6px;">
      <select id="zjf-rescue-system" class="layui-input" style="max-width:160px;display:inline-block;">
        <option value="1">Linux</option>
        <option value="2">Windows</option>
      </select>
      <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" id="zjf-rescue-confirm"
              style="margin-left:10px;">确认进入救援系统</button>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
(function () {
  var msg = document.getElementById('zjf-msg');
  var passPanel = document.getElementById('zjf-pass-panel');
  var passInput = document.getElementById('zjf-pass-input');
  var reinstallPanel = document.getElementById('zjf-reinstall-panel');
  var rescuePanel = document.getElementById('zjf-rescue-panel');
  var osCount = <?= is_array($os_list) ? count($os_list) : 0 ?>;
  var osError = <?= json_encode((string)$os_error, JSON_UNESCAPED_UNICODE) ?>;
  var pendingAction = '';

  function show(text, ok) {
    msg.textContent = text;
    msg.className = 'zj-msg zj-msg-show ' + (ok ? 'zj-msg-success' : 'zj-msg-error');
  }
  function hidePanels() {
    if (passPanel) passPanel.style.display = 'none';
    if (reinstallPanel) reinstallPanel.style.display = 'none';
    if (rescuePanel) rescuePanel.style.display = 'none';
  }
  function call(action, extra) {
    var body = new URLSearchParams();
    body.append('host_id', '<?= (int)$host['id'] ?>');
    body.append('action', action);
    for (var k in (extra || {})) body.append(k, extra[k]);
    fetch('<?= zjmf_url('reserve/api/host_action') ?>', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: body.toString()
    }).then(function (r) { return r.json(); }).then(function (res) {
      var ok = res.code === 'ok' || res.success;
      show(res.msg || res.code || '操作成功', ok);
      if (ok) setTimeout(function () { location.reload(); }, 800);
    }).catch(function () {
      show('网络错误，请重试', false);
    });
  }

  document.querySelectorAll('[data-act]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var act = btn.getAttribute('data-act');
      hidePanels();
      if (act === 'reset_password') {
        pendingAction = act;
        passPanel.style.display = 'block';
        passInput.focus();
        return;
      }
      if (act === 'reinstall') {
        // 系统列表来自 host/dedicatedserver，重装统一走 dcim/reinstall（共用重装端点）
        if (osCount > 0 && reinstallPanel) {
          reinstallPanel.style.display = 'block';
          return;
        }
        show('无法获取系统列表：' + osError, false);
        return;
      }
      if (act === 'rescue') {
        if (rescuePanel) {
          rescuePanel.style.display = 'block';
          return;
        }
        if (!confirm('确定进入救援系统（Linux）吗？')) return;
        call('rescue', {system: 1});
        return;
      }
      if (act === 'bmc') {
        if (!confirm('确定要重置该主机的 BMC 吗？')) return;
        call('bmc', {});
        return;
      }
      if (act === 'cancel_task') {
        if (!confirm('确定要取消当前的重装/救援/重置密码任务吗？')) return;
        call('cancel_task', {});
        return;
      }
      if (act === 'reboot') {
        if (!confirm('确定要重启该主机吗？')) return;
      }
      call(act, {});
    });
  });

  document.getElementById('zjf-pass-confirm').addEventListener('click', function () {
    var pwd = passInput.value;
    if (!pwd || pwd.length < 6) {
      show('密码至少 6 位', false);
      return;
    }
    call('reset_password', {password: pwd});
  });

  if (reinstallPanel) {
    document.getElementById('zjf-reinstall-confirm').addEventListener('click', function () {
      var sel = document.getElementById('zjf-os-select');
      var os = sel.value;
      if (!os) { show('请选择操作系统', false); return; }
      var group = '';
      if (sel.selectedIndex >= 0 && sel.options[sel.selectedIndex]) {
        group = sel.options[sel.selectedIndex].getAttribute('data-group') || '';
      }
      if (!confirm('确定要重装该系统吗？重装将清空数据且不可恢复！')) return;
      call('dcim_reinstall', {os: os, os_group: group});
    });
  }

  if (rescuePanel) {
    document.getElementById('zjf-rescue-confirm').addEventListener('click', function () {
      var system = document.getElementById('zjf-rescue-system').value;
      if (!confirm('确定进入救援系统吗？当前数据可能受影响，请谨慎操作。')) return;
      call('rescue', {system: system});
    });
  }
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
