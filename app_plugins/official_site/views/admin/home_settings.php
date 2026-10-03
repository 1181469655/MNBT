<?php
/**
 * 管理员端 - 官网主页设置（V1.88 自核心「主页设置」迁入）
 *
 * 基础配置存插件 options（原 MN_config.home_* 列），内容设置存
 * home_theme_settings JSON（原主页主题自定义设置，字段见 lib/home.php）。
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

global $conf;

// —— 处理 POST（成功后 redirect，避免刷新重复提交）——
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$act = $_POST['act'] ?? '';

	if ($act === 'save') {
		foreach (['home_enable', 'home_show_notice', 'home_show_plans'] as $sw) {
			mnbt_plugin_option_set('official_site', $sw, isset($_POST[$sw]) && $_POST[$sw] === 'true' ? 'true' : 'false');
		}
		$primary = trim((string)($_POST['home_primary'] ?? ''));
		mnbt_plugin_option_set('official_site', 'home_primary', preg_match('/^#[0-9a-fA-F]{6}$/', $primary) ? $primary : '');
		foreach (['home_title', 'home_hero', 'home_logo', 'home_favicon', 'home_footer'] as $f) {
			mnbt_plugin_option_set('official_site', $f, trim((string)($_POST[$f] ?? '')));
		}
		// 内容设置（home_theme_settings JSON，增量合并）
		$values = official_site_home_settings_all();
		foreach (official_site_home_fields() as $f) {
			if ($f['type'] === 'image') {
				continue; // 图片字段的值由上传动作写入，避免清空未重新上传的图
			}
			$values[$f['key']] = trim((string)($_POST['ts_' . $f['key']] ?? ''));
		}
		mnbt_plugin_option_set('official_site', 'home_theme_settings', json_encode($values, JSON_UNESCAPED_UNICODE));
		header('Location: ' . site_admin_url('home_settings', 'saved=1'));
		exit;
	}

	if ($act === 'upload') {
		$target = (string)($_POST['target'] ?? '');
		$isIcon = $target === 'logo' || $target === 'favicon';
		$field = $isIcon ? $target : (string)($_POST['key'] ?? '');
		$fdef = null;
		foreach (official_site_home_fields() as $f) {
			if ($f['key'] === $field && $f['type'] === 'image') {
				$fdef = $f;
			}
		}
		if (!$isIcon && !$fdef) {
			$msg = '非法上传字段';
			$msg_type = 'danger';
		} elseif (empty($_FILES['file']) || (int)($_FILES['file']['error'] ?? 1) !== 0 || !is_uploaded_file((string)($_FILES['file']['tmp_name'] ?? ''))) {
			$msg = '未收到文件或上传失败';
			$msg_type = 'danger';
		} else {
			$ext = strtolower(pathinfo((string)$_FILES['file']['name'], PATHINFO_EXTENSION));
			$allow = $isIcon ? ['png', 'jpg', 'jpeg', 'gif', 'ico'] : ['png', 'jpg', 'jpeg', 'gif', 'webp', 'ico'];
			if (!in_array($ext, $allow, true)) {
				$msg = '仅支持 ' . implode(' / ', $allow) . ' 格式';
				$msg_type = 'danger';
			} else {
				$dir = ROOT . 'imsetes/upload_logo/';
				if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
					$msg = '上传目录不可写';
					$msg_type = 'danger';
				} elseif (!move_uploaded_file($_FILES['file']['tmp_name'], $dir . 'home_' . $field . '.png')) {
					$msg = '保存文件失败，请检查 imsetes/upload_logo 目录权限';
					$msg_type = 'danger';
				} else {
					$url = 'imsetes/upload_logo/home_' . $field . '.png';
					if ($isIcon) {
						mnbt_plugin_option_set('official_site', 'home_' . $target, $url);
					} else {
						$values = official_site_home_settings_all();
						$values[$field] = $url;
						mnbt_plugin_option_set('official_site', 'home_theme_settings', json_encode($values, JSON_UNESCAPED_UNICODE));
					}
					header('Location: ' . site_admin_url('home_settings', 'saved=1&msg=' . urlencode('上传成功')));
					exit;
				}
			}
		}
	}
}

// —— GET 提示 ——
$saved = isset($_GET['saved']);
$msg = $_GET['msg'] ?? ($msg ?? '');
$msg_type = $saved ? 'success' : ($msg_type ?? 'danger');
if ($saved && $msg === '') {
	$msg = '保存成功';
}

$home_enable     = official_site_home_option('home_enable', 'true') === 'true';
$home_show_notice = official_site_home_option('home_show_notice', 'true') === 'true';
$home_show_plans = official_site_home_option('home_show_plans', 'true') === 'true';
$home_title      = (string)official_site_home_option('home_title', '');
$home_hero       = (string)official_site_home_option('home_hero', '');
$home_primary    = (string)official_site_home_option('home_primary', '');
$home_logo       = (string)official_site_home_option('home_logo', '');
$home_favicon    = (string)official_site_home_option('home_favicon', '');
$home_footer     = (string)official_site_home_option('home_footer', '');
$settingsAll     = official_site_home_settings_all();

mnbt_admin_include('head');
?>
<div class="card">
	<div class="card-header"><h4 style="display:inline-block">官网主页设置</h4></div>
	<div class="card-body">
		<?php if (!empty($msg)): ?>
			<div class="alert alert-<?= htmlspecialchars($msg_type) ?>"><?= htmlspecialchars($msg) ?></div>
		<?php endif; ?>
		<p class="text-muted">站点根路径 <code>/</code> 的落地页由本插件渲染（V1.88 起承接原系统内置主页）。关闭「启用主页」后，访问根路径将跳转用户面板。</p>

		<form method="post" action="<?= htmlspecialchars(site_admin_url('home_settings')) ?>">
			<input type="hidden" name="act" value="save">
			<h5>基础配置</h5>
			<div class="form-group">
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" id="sw_enable" <?= $home_enable ? 'checked' : '' ?>
						onchange="document.getElementById('home_enable').value = this.checked ? 'true' : 'false'">
					<label class="custom-control-label" for="sw_enable">启用主页</label>
				</div>
				<input type="hidden" name="home_enable" id="home_enable" value="<?= $home_enable ? 'true' : 'false' ?>">
			</div>
			<div class="form-group">
				<label>站点标题</label>
				<input type="text" class="form-control" name="home_title" value="<?= htmlspecialchars($home_title) ?>" placeholder="留空使用系统名称">
			</div>
			<div class="form-group">
				<label>宣传语（Hero）</label>
				<input type="text" class="form-control" name="home_hero" value="<?= htmlspecialchars($home_hero) ?>" placeholder="高性能虚拟主机，即买即用">
			</div>
			<div class="form-group">
				<label>主题色</label>
				<div style="display:flex;align-items:center;gap:10px;">
					<input type="color" value="<?= htmlspecialchars($home_primary ?: '#4f46e5') ?>" style="width:46px;height:34px;padding:2px;border:1px solid #ced4da;border-radius:4px;cursor:pointer;"
						oninput="document.getElementById('home_primary').value=this.value">
					<input type="text" class="form-control" name="home_primary" id="home_primary" value="<?= htmlspecialchars($home_primary) ?>" style="max-width:140px;" placeholder="#4f46e5">
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label>主页 Logo</label>
					<div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
						<?php if ($home_logo !== ''): ?><img src="<?= htmlspecialchars(mnbt_home_asset($home_logo)) ?>" alt="" style="width:120px;height:40px;object-fit:contain;"><?php endif; ?>
						<input type="file" name="file" accept=".png,.jpg,.jpeg,.gif,.ico">
						<button type="submit" class="btn btn-outline-secondary" onclick="this.form.act.value='upload';this.form.target.value='logo'">上传 Logo</button>
					</div>
					<input type="hidden" name="target" value="logo">
					<input type="text" class="form-control" name="home_logo" value="<?= htmlspecialchars($home_logo) ?>" placeholder="上传后自动填入，或手动填写 URL">
				</div>
				<div class="form-group col-md-6">
					<label>Favicon</label>
					<div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
						<?php if ($home_favicon !== ''): ?><img src="<?= htmlspecialchars(mnbt_home_asset($home_favicon)) ?>" alt="" style="width:32px;height:32px;"><?php endif; ?>
						<input type="file" name="file" accept=".png,.jpg,.jpeg,.gif,.ico" onchange="this.form.target.value='favicon';this.form.act.value='upload';this.form.submit()">
					</div>
					<input type="text" class="form-control" name="home_favicon" value="<?= htmlspecialchars($home_favicon) ?>" placeholder="上传后自动填入，或手动填写 URL">
				</div>
			</div>
			<div class="form-group">
				<label>页脚版权</label>
				<textarea class="form-control" name="home_footer" rows="2" placeholder="留空使用系统版权（hxp）"><?= htmlspecialchars($home_footer) ?></textarea>
			</div>
			<div class="form-group">
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" id="sw_notice" <?= $home_show_notice ? 'checked' : '' ?>
						onchange="document.getElementById('home_show_notice').value = this.checked ? 'true' : 'false'">
					<label class="custom-control-label" for="sw_notice">显示公告</label>
				</div>
				<input type="hidden" name="home_show_notice" id="home_show_notice" value="<?= $home_show_notice ? 'true' : 'false' ?>">
			</div>
			<div class="form-group">
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" id="sw_plans" <?= $home_show_plans ? 'checked' : '' ?>
						onchange="document.getElementById('home_show_plans').value = this.checked ? 'true' : 'false'">
					<label class="custom-control-label" for="sw_plans">显示套餐区（需主机售卖插件）</label>
				</div>
				<input type="hidden" name="home_show_plans" id="home_show_plans" value="<?= $home_show_plans ? 'true' : 'false' ?>">
			</div>

			<h5 class="mt-4">页面内容</h5>
			<?php foreach (official_site_home_fields() as $f):
				$val = isset($settingsAll[$f['key']]) && $settingsAll[$f['key']] !== '' ? (string)$settingsAll[$f['key']] : (string)$f['default'];
				$pid = 'ts_' . $f['key']; ?>
				<div class="form-group">
					<label for="<?= htmlspecialchars($pid) ?>"><?= htmlspecialchars($f['label']) ?></label>
					<?php if ($f['type'] === 'textarea'): ?>
						<textarea class="form-control" name="<?= htmlspecialchars($pid) ?>" id="<?= htmlspecialchars($pid) ?>" rows="4" placeholder="<?= htmlspecialchars($f['placeholder'] ?? '') ?>"><?= htmlspecialchars($val) ?></textarea>
					<?php else: ?>
						<input type="text" class="form-control" name="<?= htmlspecialchars($pid) ?>" id="<?= htmlspecialchars($pid) ?>" value="<?= htmlspecialchars($val) ?>" placeholder="<?= htmlspecialchars($f['placeholder'] ?? '') ?>">
					<?php endif; ?>
					<?php if (!empty($f['hint'])): ?><small class="form-text text-muted"><?= htmlspecialchars($f['hint']) ?></small><?php endif; ?>
					<?php if ($f['type'] === 'image'): ?>
						<div style="display:flex;align-items:center;gap:10px;margin-top:6px;">
							<?php if ($val !== ''): ?><img src="<?= htmlspecialchars(mnbt_home_asset($val)) ?>" alt="" style="width:120px;height:76px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;"><?php endif; ?>
							<input type="file" name="file" accept=".png,.jpg,.jpeg,.gif,.webp,.ico" onchange="this.form.target.value='';this.form.key.value='<?= htmlspecialchars($f['key']) ?>';this.form.act.value='upload';this.form.submit()">
						</div>
						<input type="hidden" name="key" value="">
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

			<button type="submit" class="btn btn-primary">保存设置</button>
		</form>
	</div>
</div>
<?php mnbt_admin_include('foot'); ?>
