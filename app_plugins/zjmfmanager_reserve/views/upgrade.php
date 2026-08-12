<?php
/**
 * 用户端 - 主机升级（配置升级 / 产品升降级）
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '主机升级';
$host = $host ?? null;
$kind = $kind ?? 'config';
$options = $options ?? ['ok' => false, 'msg' => ''];
$optionsData = !empty($options['ok']) && is_array($options['data'])
	? $options['data'] : [];

function zjf_config_options($data)
{
	$opts = $data['configoptions'] ?? null;
	if (!is_array($opts)) {
		return [];
	}
	$out = [];
	foreach ($opts as $o) {
		if (!is_array($o)) {
			continue;
		}
		$id = $o['id'] ?? $o['configid'] ?? 0;
		$name = (string)($o['name'] ?? $o['optionname'] ?? ('选项 ' . $id));
		$choices = [];
		$raw = $o['options'] ?? $o['values'] ?? null;
		if (is_array($raw)) {
			foreach ($raw as $c) {
				if (is_array($c)) {
					$v = $c['value'] ?? $c['id'] ?? '';
					$l = (string)($c['name'] ?? $c['label'] ?? $v);
				} else {
					$v = $c;
					$l = (string)$c;
				}
				if ($v !== '') {
					$choices[] = ['value' => $v, 'label' => $l];
				}
			}
		}
		if ($id && $choices) {
			$out[] = ['id' => $id, 'name' => $name, 'choices' => $choices];
		}
	}
	return $out;
}

function zjf_upgrade_products($data)
{
	$list = $data['list'] ?? $data['products'] ?? null;
	if (!is_array($list) && isset($data[0]) && is_array($data[0])) {
		$list = $data;
	}
	if (!is_array($list)) {
		return [];
	}
	$out = [];
	foreach ($list as $p) {
		if (!is_array($p)) {
			continue;
		}
		$id = (int)($p['id'] ?? 0);
		if ($id <= 0) {
			continue;
		}
		$cycles = [];
		$raw = $p['cycles'] ?? null;
		if (is_array($raw)) {
			foreach ($raw as $c) {
				if (!is_array($c)) {
					continue;
				}
				$bc = (string)($c['billingcycle'] ?? $c['cycle'] ?? '');
				if ($bc === '') {
					continue;
				}
				$cycles[$bc] = (string)($c['name'] ?? $bc);
			}
		}
		$out[] = ['id' => $id, 'name' => (string)($p['name'] ?? ''), 'cycles' => $cycles];
	}
	return $out;
}

$configOptions = zjf_config_options($optionsData);
$productList = zjf_upgrade_products($optionsData);
ob_start();
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
  <h1 style="font-size:20px;color:#222;margin:0;">升级：<?= htmlspecialchars($host['name']) ?></h1>
  <a class="layui-btn layui-btn-xs layui-btn-primary"
     href="<?= zjmf_url('reserve/hosts/' . (int)$host['id']) ?>">返回主机详情</a>
</div>

<div class="zj-msg" id="zjf-msg"></div>

<div class="layui-btn-group" style="margin-bottom:16px;">
  <a class="layui-btn <?= $kind === 'config' ? '' : 'layui-btn-primary' ?>"
     href="<?= zjmf_url('reserve/hosts/' . (int)$host['id'] . '/upgrade?kind=config') ?>">配置升级</a>
  <a class="layui-btn <?= $kind === 'product' ? '' : 'layui-btn-primary' ?>"
     href="<?= zjmf_url('reserve/hosts/' . (int)$host['id'] . '/upgrade?kind=product') ?>">产品升级</a>
</div>

<?php if (empty($options['ok'])): ?>
  <div class="layui-card">
    <div class="layui-card-body" style="color:#999;">
      获取升级选项失败：<?= htmlspecialchars($options['msg'] ?? '未知错误') ?>
    </div>
  </div>
<?php elseif ($kind === 'config'): ?>
  <div class="layui-card">
    <div class="layui-card-header">选择配置项</div>
    <div class="layui-card-body">
      <?php if ($configOptions === []): ?>
        <p class="zj-muted">
          未解析到可配置项（上游返回结构需联调确认，见 PRD Q1）。请在上游后台直接调整配置。
        </p>
      <?php else: ?>
        <form id="zjf-cfg-form">
          <?php foreach ($configOptions as $opt): ?>
            <div class="form-group" style="margin-bottom:14px;">
              <label style="display:block;font-weight:600;margin-bottom:6px;">
                <?= htmlspecialchars($opt['name']) ?>
              </label>
              <select class="form-control zjf-cfg-select"
                      data-id="<?= (int)$opt['id'] ?>"
                      style="max-width:360px;">
                <?php foreach ($opt['choices'] as $c): ?>
                  <option value="<?= htmlspecialchars($c['value'], ENT_QUOTES) ?>">
                    <?= htmlspecialchars($c['label']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endforeach; ?>
          <div id="zjf-price" class="zj-muted" style="margin:10px 0;"></div>
          <button type="button" class="layui-btn" id="zjf-cfg-preview">试算差额</button>
          <button type="button" class="layui-btn layui-btn-danger" id="zjf-cfg-confirm">确认升级</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
<?php else: ?>
  <div class="layui-card">
    <div class="layui-card-header">选择目标产品</div>
    <div class="layui-card-body">
      <?php if ($productList === []): ?>
        <p class="zj-muted">
          未解析到可升级产品（上游返回结构需联调确认，见 PRD Q1）。
        </p>
      <?php else: ?>
        <form id="zjf-prod-form">
          <?php foreach ($productList as $p): ?>
            <label class="zj-choice" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
              <input type="radio" name="newpid" value="<?= (int)$p['id'] ?>" class="zjf-prod-pid">
              <?= htmlspecialchars($p['name']) ?>
              <select class="form-control form-control-sm zjf-prod-cycle" style="width:140px;">
                <?php foreach ($p['cycles'] as $bc => $cn): ?>
                  <option value="<?= htmlspecialchars($bc, ENT_QUOTES) ?>">
                    <?= htmlspecialchars($cn) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>
          <?php endforeach; ?>
          <div id="zjf-price" class="zj-muted" style="margin:10px 0;"></div>
          <button type="button" class="layui-btn" id="zjf-prod-preview">试算差额</button>
          <button type="button" class="layui-btn layui-btn-danger" id="zjf-prod-confirm">确认升级</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<script>
(function () {
  var hostId = '<?= (int)$host['id'] ?>';
  var kind = '<?= $kind === 'product' ? 'product' : 'config' ?>';
  var msg = document.getElementById('zjf-msg');

  function show(text, ok) {
    msg.textContent = text;
    msg.className = 'zj-msg zj-msg-show ' + (ok ? 'zj-msg-success' : 'zj-msg-error');
  }
  function buildBody(preview) {
    var body = new URLSearchParams();
    body.append('host_id', hostId);
    body.append('kind', kind);
    body.append('preview', preview);
    if (kind === 'config') {
      var cfg = {};
      document.querySelectorAll('.zjf-cfg-select').forEach(function (s) {
        cfg[s.getAttribute('data-id')] = s.value;
      });
      body.append('config_json', JSON.stringify(cfg));
    } else {
      var pid = document.querySelector('input[name="newpid"]:checked');
      if (!pid) return null;
      var sel = pid.closest('.zj-choice').querySelector('.zjf-prod-cycle');
      body.append('newpid', pid.value);
      body.append('billingcycle', sel.value);
    }
    return body;
  }
  function post(preview, confirmText) {
    var body = buildBody(preview);
    if (!body) {
      show('请先选择升级目标', false);
      return;
    }
    if (confirmText && !confirm(confirmText)) {
      return;
    }
    fetch('<?= zjmf_url('reserve/api/upgrade') ?>', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: body.toString()
    }).then(function (r) { return r.json(); }).then(function (res) {
      var ok = res.code === 'ok' || res.success;
      if (preview && ok) {
        document.getElementById('zjf-price').textContent =
          '本次升级需支付：¥' + res.price;
        return;
      }
      show(res.code || '完成', ok);
      if (ok) setTimeout(function () {
        window.location.href = '<?= zjmf_url('reserve/hosts/' . (int)$host['id']) ?>';
      }, 800);
    }).catch(function () {
      show('网络错误，请重试', false);
    });
  }

  var previewId = kind === 'config' ? 'zjf-cfg-preview' : 'zjf-prod-preview';
  var confirmId = kind === 'config' ? 'zjf-cfg-confirm' : 'zjf-prod-confirm';
  var pBtn = document.getElementById(previewId);
  var cBtn = document.getElementById(confirmId);
  if (pBtn) pBtn.addEventListener('click', function () { post('1', ''); });
  if (cBtn) cBtn.addEventListener('click', function () {
    post('0', '确认按试算差额扣除余额并执行升级吗？');
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
