<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');

$mode = mnbt_plugin_option_get('home_demo', 'mode', 'off');
if (!in_array($mode, ['off', 'redirect', 'render'], true)) {
	$mode = 'off';
}
$redirectTarget = mnbt_plugin_option_get('home_demo', 'redirect_target', '/user');
$siteBase = '/';
?>
<style>
.home-demo-mode-card{cursor:pointer;transition:all .2s;border:2px solid transparent;}
.home-demo-mode-card.active{border-color:#4094f6;background:#f3f9ff;}
.home-demo-mode-card:hover{background:#f8f9fa;}
.home-demo-mode-card.active:hover{background:#f3f9ff;}
</style>
<div class="container-fluid p-t-15">
  <div class="card">
    <div class="card-header"><h4>首页接管示例</h4></div>
    <div class="card-body">
      <p class="text-muted">本插件演示如何接管站点根路径 <code>/</code> 的响应。下方三种模式择一启用后，访问 <a href="<?php echo htmlspecialchars($siteBase, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">站点首页</a> 即可看到效果。</p>

      <h5 class="mt-3">模式选择</h5>
      <div class="row mt-2">
        <div class="col-md-4 mb-3">
          <div class="card home-demo-mode-card p-3" data-mode="off">
            <div class="d-flex align-items-center">
              <i class="mdi mdi-close-circle-outline mr-2" style="font-size:24px;color:#6c757d"></i>
              <div>
                <h6 class="mb-0">不接管</h6>
                <small class="text-muted">回退到默认 /user 跳转</small>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card home-demo-mode-card p-3" data-mode="redirect">
            <div class="d-flex align-items-center">
              <i class="mdi mdi-redirect mr-2" style="font-size:24px;color:#4094f6"></i>
              <div>
                <h6 class="mb-0">重定向</h6>
                <small class="text-muted">跳转到指定 URL</small>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card home-demo-mode-card p-3" data-mode="render">
            <div class="d-flex align-items-center">
              <i class="mdi mdi-monitor mr-2" style="font-size:24px;color:#28a745"></i>
              <div>
                <h6 class="mb-0">渲染首页</h6>
                <small class="text-muted">直接输出自定义 HTML</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div id="redirect-config" class="mt-3" style="display:none">
        <label>重定向目标 URL</label>
        <input type="text" class="form-control" id="redirect-target" value="<?php echo htmlspecialchars((string)$redirectTarget, ENT_QUOTES, 'UTF-8'); ?>" placeholder="/user 或 https://example.com" maxlength="500">
        <small class="form-text text-muted">支持相对路径（如 <code>/user</code>、<code>/admin</code>）或绝对 URL。</small>
      </div>

      <div class="mt-4">
        <button type="button" class="btn btn-primary" id="btn-save"><i class="mdi mdi-content-save mr-1"></i>保存配置</button>
        <a href="<?php echo htmlspecialchars($siteBase, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-outline-secondary ml-2"><i class="mdi mdi-open-in-new mr-1"></i>测试首页</a>
      </div>

      <hr class="mt-4">
      <h5>通用路由演示</h5>
      <p class="text-muted">本插件还注册了两个示例路由（需 Web 服务器配置 rewrite，详见 <a href="../plugin.php?p=home_demo&page=index#">PLUGIN_DEV.md</a>）：</p>
      <ul>
        <li><code>GET /landing</code> — 活动落地页，<a href="<?php echo htmlspecialchars($siteBase . 'landing', ENT_QUOTES, 'UTF-8'); ?>" target="_blank">点击测试</a></li>
        <li><code>GET /promo/{id}</code> — 带命名参数，<a href="<?php echo htmlspecialchars($siteBase . 'promo/mnbt2026', ENT_QUOTES, 'UTF-8'); ?>" target="_blank">点击测试</a></li>
      </ul>

      <div class="alert alert-info mt-3">
        <i class="mdi mdi-information mr-1"></i>
        <strong>提示：</strong>开发环境（<code>php -S localhost:8080 _router.php</code>）已自动支持路由；生产环境需配置 nginx/apache rewrite 把未命中文件的请求转发到 <code>index.php</code>。
      </div>
    </div>
  </div>
</div>
<script>
(function () {
  var currentMode = <?php echo json_encode($mode); ?>;
  var cards = document.querySelectorAll('.home-demo-mode-card');
  var redirectBox = document.getElementById('redirect-config');

  function refreshUI(mode) {
    cards.forEach(function (c) {
      c.classList.toggle('active', c.getAttribute('data-mode') === mode);
    });
    redirectBox.style.display = (mode === 'redirect') ? 'block' : 'none';
  }
  refreshUI(currentMode);

  cards.forEach(function (c) {
    c.addEventListener('click', function () {
      refreshUI(c.getAttribute('data-mode'));
    });
  });

  function getCurrentMode() {
    var active = document.querySelector('.home-demo-mode-card.active');
    return active ? active.getAttribute('data-mode') : 'off';
  }

  function parseRes(res) {
    try { return typeof res === 'string' ? JSON.parse(res) : res; } catch (e) { return { code: res }; }
  }
  function notify(msg, ok) {
    if (typeof $.notify === 'function') $.notify({ message: msg }, { type: ok ? 'success' : 'danger' });
    else alert(msg);
  }

  document.getElementById('btn-save').addEventListener('click', function () {
    var data = {
      gn: 'p_home_demo_save',
      mode: getCurrentMode(),
      redirect_target: document.getElementById('redirect-target').value
    };
    $.post('ajax.php', data, function (res) {
      var d = parseRes(res);
      var msg = d.msg || d.code || '完成';
      notify(msg, d.qk == 1 || d.success === true);
    });
  });
})();
</script>
