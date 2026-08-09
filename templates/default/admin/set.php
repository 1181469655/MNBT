<?php mnbt_admin_include('head'); ?>
<?php header("Cache-Control: no-cache, must-revalidate"); ?>
<script type="text/javascript" src="<?=mnbt_asset_url('js/md5.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/xtset.js')?>?hc=<? echo $date?>"></script>
<link rel="stylesheet" href="<?=mnbt_theme_asset('set-page.css', 'admin')?>">

<div class="mn-set-page">
<?php
$set = isset($_GET['gn']) ? $_GET['gn'] : null;
if ($set == 'wz') {
?>
<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-earth"></i></div>
    <div>
      <h4>网站配置</h4>
      <p>公告、联系方式与登录安全</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="mn-set-field">
      <label for="wzgg">网站公告</label>
      <textarea name="wzgg" rows="8" id="wzgg" class="form-control" placeholder="请在这填写网站公告"><?php echo $conf['gg']; ?></textarea>
    </div>
    <div class="mn-set-field">
      <label for="qq">站长 QQ</label>
      <input type="text" name="qq" id="qq" value="<?php echo $conf['qqh']; ?>" class="form-control" placeholder="请在这填写您的QQ号" required/>
    </div>
    <div class="mn-set-field">
      <div class="mn-set-switch">
        <div class="mn-set-switch-txt">
          <strong>后台登录验证码</strong>
          <span>开启后管理员登录需填写验证码</span>
        </div>
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="yzmkg" <?php if ($conf['yzm'] == 'true') echo 'checked'; ?>>
          <label class="custom-control-label" for="yzmkg"></label>
        </div>
      </div>
      <div class="mn-set-switch">
        <div class="mn-set-switch-txt">
          <strong>主机邮箱绑定</strong>
          <span>要求用户绑定邮箱后方可使用部分功能</span>
        </div>
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="zjyxbd" <?php if ($conf['zjyxbd'] == 'true') echo 'checked'; ?>>
          <label class="custom-control-label" for="zjyxbd"></label>
        </div>
      </div>
    </div>
    <div class="mn-set-actions">
      <button class="btn btn-primary btn-block" type="button" onclick="setwz()"><i class="mdi mdi-content-save-outline"></i> 保存修改</button>
    </div>
  </div>
</div>

<?php } elseif ($set == 'api') { ?>
<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-key-variant"></i></div>
    <div>
      <h4>API 设置</h4>
      <p>接口密钥、默认 PHP 与建站目录</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="mn-set-field">
      <label for="apimy">API 密钥</label>
      <div class="input-group mn-set-input-group">
        <input type="text" class="form-control" name="apimy" id="apimy" value="<?php echo $conf['api']; ?>" placeholder="API密钥(推荐随机生成)"/>
        <div class="input-group-append"><button class="btn btn-outline-secondary" type="button" onclick="apisc()">随机生成</button></div>
      </div>
    </div>
    <div class="mn-set-field">
      <label for="linuxml">Linux 建站目录</label>
      <input type="text" name="linuxml" id="linuxml" value="<?php echo $conf['hxi']; ?>" class="form-control" placeholder="Linux宝塔面板的建站目录" required/>
      <small>默认 /www/wwwroot</small>
    </div>
    <div class="mn-set-field">
      <label for="winml">Windows 建站目录</label>
      <input type="text" name="winml" id="winml" value="<?php echo $conf['hxo']; ?>" class="form-control" placeholder="Windows宝塔面板的建站目录" required/>
      <small>默认 D:/wwwroot</small>
    </div>
    <div class="mn-set-field">
      <div class="mn-set-switch">
        <div class="mn-set-switch-txt">
          <strong>API 接口开关</strong>
          <span>关闭后外部系统将无法调用接口</span>
        </div>
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="apikg" <?php if ($conf['apiqk'] == 'true') echo 'checked'; ?>>
          <label class="custom-control-label" for="apikg"></label>
        </div>
      </div>
    </div>
    <div class="mn-set-actions">
      <button class="btn btn-primary btn-block" type="button" onclick="setapi()"><i class="mdi mdi-content-save-outline"></i> 保存修改</button>
    </div>
    <div class="mn-set-note">
      <b>注意：</b>建站目录请勿随意修改，已开通主机可能受影响。API 密钥修改后，监控 URL 与外部对接均需同步更新。默认 PHP 版本需在宝塔软件商店中已安装。
    </div>
  </div>
</div>

<?php } elseif ($set == 'kzmb') { ?>
<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-view-dashboard-outline"></i></div>
    <div>
      <h4>控制面板</h4>
      <p>名称、FTP 面板、Logo 与开关</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="mn-set-field">
      <label for="kzmbname">控制面板名称</label>
      <input type="text" name="kzmbname" id="kzmbname" value="<?php echo $conf['name']; ?>" class="form-control" placeholder="请在这填写控制面板的名称" required/>
    </div>
    <div class="mn-set-field">
      <label for="ftp">FTP 操作面板</label>
      <select class="form-control" id="ftp" name="ftp" size="1">
        <?php
        $acd = '';
        $acd2 = '';
        if ($conf['hxw'] == '' || $conf['hxw'] == 'amftp') {
          $acd = 'selected';
        } else {
          $acd2 = 'selected';
        }
        echo '
        <option value="amftp" ' . $acd . '>AMFTP 操作面板</option>
        <option value="mnftp" ' . $acd2 . '>MN 操作面板（推荐）</option>
        ';
        ?>
      </select>
    </div>
    <div class="mn-set-field">
      <label for="bq">显示版权</label>
      <input type="text" name="bq" id="bq" value="<?php echo htmlspecialchars($conf['hxp']); ?>" class="form-control" placeholder="可以使用HTML标签" required/>
      <small>例如：Copyright © 梦奈云 2026</small>
    </div>
    <div class="mn-set-field">
      <label for="logoa">登录页 Logo</label>
      <div class="custom-file">
        <input type="file" name="logoa" id="logoa" class="custom-file-input">
        <label class="custom-file-label" for="logoa">选择文件…</label>
      </div>
    </div>
    <div class="mn-set-field">
      <label for="logob">侧栏 Logo</label>
      <div class="custom-file">
        <input type="file" name="logob" id="logob" class="custom-file-input">
        <label class="custom-file-label" for="logob">选择文件…</label>
      </div>
    </div>
    <div class="mn-set-field">
      <label for="logoc">用户头像 Logo</label>
      <div class="custom-file">
        <input type="file" name="logoc" id="logoc" class="custom-file-input">
        <label class="custom-file-label" for="logoc">选择文件…</label>
      </div>
    </div>
    <div class="mn-set-field">
      <div class="mn-set-switch">
        <div class="mn-set-switch-txt">
          <strong>用户登录验证码</strong>
          <span>控制面板登录是否需要验证码</span>
        </div>
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="yzmkzmb" <?php if ($conf['yzme'] == 'true') echo 'checked'; ?>>
          <label class="custom-control-label" for="yzmkzmb"></label>
        </div>
      </div>
      <div class="mn-set-switch">
        <div class="mn-set-switch-txt">
          <strong>控制面板开关</strong>
          <span>关闭后用户无法进入控制面板</span>
        </div>
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="kzmbkg" <?php if ($conf['kzmbqk'] == 'true') echo 'checked'; ?>>
          <label class="custom-control-label" for="kzmbkg"></label>
        </div>
      </div>
    </div>
    <div class="mn-set-actions">
      <button class="btn btn-primary btn-block" type="button" onclick="setkzmb()"><i class="mdi mdi-content-save-outline"></i> 保存修改</button>
    </div>
    <div class="mn-set-note">
      AMFTP 仅支持本机宝塔；MN 面板支持本地与远程。不上传 Logo 则沿用原图。上传后请清理浏览器/CDN 缓存。
    </div>
  </div>
</div>

<?php } elseif ($set == 'gl') { ?>
<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-account-key"></i></div>
    <div>
      <h4>管理账号</h4>
      <p>修改后台登录账号与密码</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="mn-set-field">
      <label for="ysuser">原账号</label>
      <input type="text" name="ysuser" id="ysuser" class="form-control" placeholder="原来的账号" required/>
    </div>
    <div class="mn-set-field">
      <label for="yspass">原密码</label>
      <input type="password" name="yspass" id="yspass" class="form-control" placeholder="原来的密码" required/>
    </div>
    <div class="mn-set-field">
      <label for="huser">新账号</label>
      <input type="text" name="huser" id="huser" placeholder="不修改请留空" class="form-control"/>
    </div>
    <div class="mn-set-field">
      <label for="hpass">新密码</label>
      <input type="password" name="hpass" id="hpass" placeholder="不修改请留空" class="form-control"/>
    </div>
    <div class="mn-set-actions">
      <button class="btn btn-primary btn-block" type="button" onclick="setgl()"><i class="mdi mdi-content-save-outline"></i> 保存修改</button>
    </div>
  </div>
</div>

<?php } elseif ($set == 'yzf') { ?>
<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-credit-card-outline"></i></div>
    <div>
      <h4>支付配置已迁移</h4>
      <p>支付设置已改为插件化架构</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="mn-set-note">
      自 V1.81 P3 起，支付方式改由<b>支付插件</b>提供。请前往 <a href="pay_settings.php" class="alert-link">支付设置</a> 页面启用付款方式，并在 <a href="plugin.php" class="alert-link">插件管理</a> 中配置各支付插件的 API 凭证。
    </div>
    <div class="mn-set-actions">
      <a href="pay_settings.php" class="btn btn-primary btn-block"><i class="mdi mdi-arrow-right"></i> 前往支付设置</a>
    </div>
  </div>
</div>

<?php } elseif ($set == 'mail') { ?>
<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-email-outline"></i></div>
    <div>
      <h4>邮箱配置</h4>
      <p>SMTP 发信参数</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="mn-set-field">
      <label for="mailhost">SMTP 服务器</label>
      <input type="text" name="mailhost" id="mailhost" class="form-control" value="<?php echo $conf['mailhost']; ?>" placeholder="请输入邮箱服务器地址" required/>
    </div>
    <div class="mn-set-field">
      <label for="mailuser">邮箱账号</label>
      <input type="text" name="mailuser" id="mailuser" class="form-control" value="<?php echo $conf['mailuser']; ?>" placeholder="请输入邮箱账号" required/>
    </div>
    <div class="mn-set-field">
      <label for="mailpassword">邮箱密码 / 授权码</label>
      <input type="text" name="mailpassword" id="mailpassword" placeholder="请输入邮箱密码" value="<?php echo $conf['mailpassword']; ?>" class="form-control" required/>
    </div>
    <div class="mn-set-field">
      <label for="mailport">端口</label>
      <input type="text" name="mailport" id="mailport" placeholder="请输入邮箱端口" value="<?php echo $conf['mailport']; ?>" class="form-control" required/>
    </div>
    <div class="mn-set-actions">
      <button class="btn btn-primary btn-block" type="button" onclick="mailmode()"><i class="mdi mdi-content-save-outline"></i> 保存修改</button>
    </div>
  </div>
</div>

<?php } elseif ($set == 'jk') { ?>
<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-timer-sand"></i></div>
    <div>
      <h4>自动处理主机</h4>
      <p>域名 / 文件监控到期后的删除或暂停策略</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="mn-set-field">
      <div class="mn-set-switch">
        <div class="mn-set-switch-txt">
          <strong>域名监控 — 删除/处理开关</strong>
          <span>达到阈值后按下方策略处理主机</span>
        </div>
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="ymkga" <?php if ($conf['ymjkkg'] == 'true') echo 'checked'; ?>>
          <label class="custom-control-label" for="ymkga"></label>
        </div>
      </div>
      <div class="mn-set-switch">
        <div class="mn-set-switch-txt">
          <strong>域名监控 — 邮件通知</strong>
          <span>处理前发送邮件提醒</span>
        </div>
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="ymyjkga" <?php if ($conf['mtyxfskg'] == 'true') echo 'checked'; ?>>
          <label class="custom-control-label" for="ymyjkga"></label>
        </div>
      </div>
    </div>
    <div class="mn-set-field">
      <label for="ymtsyza">域名删除天数阈值</label>
      <input type="text" name="ymtsyza" id="ymtsyza" value="<?php echo $conf['ymjktsyz']; ?>" class="form-control" placeholder="请输入天数" required/>
    </div>
    <div class="mn-set-field">
      <div class="mn-set-switch">
        <div class="mn-set-switch-txt">
          <strong>文件监控 — 删除/处理开关</strong>
          <span>达到阈值后按下方策略处理主机</span>
        </div>
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="wjkga" <?php if ($conf['wjjkkg'] == 'true') echo 'checked'; ?>>
          <label class="custom-control-label" for="wjkga"></label>
        </div>
      </div>
      <div class="mn-set-switch">
        <div class="mn-set-switch-txt">
          <strong>文件监控 — 邮件通知</strong>
          <span>处理前发送邮件提醒</span>
        </div>
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="wjyjkga" <?php if ($conf['mtwjfskg'] == 'true') echo 'checked'; ?>>
          <label class="custom-control-label" for="wjyjkga"></label>
        </div>
      </div>
    </div>
    <div class="mn-set-field">
      <label for="wjtsyza">文件删除天数阈值</label>
      <input type="text" name="wjtsyza" id="wjtsyza" value="<?php echo $conf['wjjktsyz']; ?>" class="form-control" placeholder="请输入天数" required/>
    </div>
    <div class="mn-set-field">
      <label for="option1">处理方式</label>
      <select class="form-control selectpicker" name="option1" id="option1">
        <?php
        if ($conf['optionzc'] == 'del') {
          echo '<option value="del" selected>删除主机</option>';
          echo '<option value="stop">暂停主机</option>';
        } else {
          echo '<option value="stop" selected>暂停主机</option>';
          echo '<option value="del">删除主机</option>';
        }
        ?>
      </select>
    </div>
    <div class="mn-set-actions">
      <button class="btn btn-primary btn-block" type="button" onclick="jkscsz()"><i class="mdi mdi-content-save-outline"></i> 保存修改</button>
    </div>
    <div class="mn-set-note">
      开启处理开关后按天数阈值执行；仅通知可只开邮件、关闭处理开关。天数请勿填 0 或负数。执行前一天会发送邮件提醒。
    </div>
  </div>
</div>

<?php } elseif ($set == 'theme') {
  $userThemes = mnbt_theme_list('user');
  $adminThemes = mnbt_theme_list('admin');
  $dockerThemes = mnbt_theme_list('docker');
  $curUserTheme = mnbt_theme_name('user');
  $curAdminTheme = mnbt_theme_name('admin');
  $curDockerTheme = mnbt_theme_name('docker');
  $homeThemes = mnbt_theme_list('home');
  $curHomeTheme = mnbt_theme_name('home');
  // 主页内容配置（V1.84 独立主页系统，主页模板跟随用户端主题）
  $hp = function ($key, $def = '') use ($conf) {
    return isset($conf[$key]) && $conf[$key] !== '' ? $conf[$key] : $def;
  };
  $homePrimary = preg_match('/^#[0-9a-fA-F]{6}$/', (string)$hp('home_primary')) ? $hp('home_primary') : '#4f46e5';
?>
<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-palette-outline"></i></div>
    <div>
      <h4>前端模板</h4>
      <p>切换用户端 / 管理端主题皮肤</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="mn-set-field">
      <label for="usertheme">用户端主题</label>
      <select class="form-control" id="usertheme" name="usertheme">
        <?php foreach ($userThemes as $t): ?>
        <option value="<?=htmlspecialchars($t['name'])?>" <?=$curUserTheme === $t['name'] ? 'selected' : ''?>>
          <?=htmlspecialchars($t['title'])?><?=$t['version'] ? ' v'.htmlspecialchars($t['version']) : ''?> (<?=htmlspecialchars($t['name'])?>)
        </option>
        <?php endforeach; ?>
      </select>
      <small>当前：<?=htmlspecialchars($curUserTheme)?> · 目录 templates/</small>
    </div>
    <div class="mn-set-field">
      <label for="admintheme">管理端主题</label>
      <select class="form-control" id="admintheme" name="admintheme">
        <?php foreach ($adminThemes as $t): ?>
        <option value="<?=htmlspecialchars($t['name'])?>" <?=$curAdminTheme === $t['name'] ? 'selected' : ''?>>
          <?=htmlspecialchars($t['title'])?><?=$t['version'] ? ' v'.htmlspecialchars($t['version']) : ''?> (<?=htmlspecialchars($t['name'])?>)
        </option>
        <?php endforeach; ?>
      </select>
      <small>当前：<?=htmlspecialchars($curAdminTheme)?> · 缺页回退 default</small>
    </div>
    <div class="mn-set-field">
      <label for="dockertheme">Docker 控制台主题</label>
      <select class="form-control" id="dockertheme" name="dockertheme">
        <?php if (empty($dockerThemes)): ?>
        <option value="">（暂无可用 Docker 主题）</option>
        <?php else: foreach ($dockerThemes as $t): ?>
        <option value="<?=htmlspecialchars($t['name'])?>" <?=$curDockerTheme === $t['name'] ? 'selected' : ''?>>
          <?=htmlspecialchars($t['title'])?><?=$t['version'] ? ' v'.htmlspecialchars($t['version']) : ''?> (<?=htmlspecialchars($t['name'])?>)
        </option>
        <?php endforeach; endif; ?>
      </select>
      <small>当前：<?=htmlspecialchars($curDockerTheme)?> · Docker 控制台（/docker/）皮肤</small>
    </div>
    <div class="mn-set-field">
      <label for="hometheme">主页主题</label>
      <select class="form-control" id="hometheme" name="hometheme">
        <?php if (empty($homeThemes)): ?>
        <option value="">（暂无可用主页主题）</option>
        <?php else: foreach ($homeThemes as $t): ?>
        <option value="<?=htmlspecialchars($t['name'])?>" <?=$curHomeTheme === $t['name'] ? 'selected' : ''?>>
          <?=htmlspecialchars($t['title'])?><?=$t['version'] ? ' v'.htmlspecialchars($t['version']) : ''?> (<?=htmlspecialchars($t['name'])?>)
        </option>
        <?php endforeach; endif; ?>
      </select>
      <small>当前：<?=htmlspecialchars($curHomeTheme)?> · 站点根路径 / 的落地页皮肤（templates/主题/home/）</small>
    </div>
    <div class="mn-set-field">
      <label>已安装主题</label>
      <div class="table-responsive">
        <table class="table table-hover mn-set-table">
          <thead>
            <tr>
              <th>目录</th>
              <th>名称</th>
              <th>版本</th>
              <th>用户端</th>
              <th>管理端</th>
              <th>Docker端</th>
              <th>主页</th>
              <th>说明</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $all = mnbt_theme_list(null);
          if (empty($all)): ?>
            <tr><td colspan="8" class="text-center text-muted">未发现主题</td></tr>
          <?php else: foreach ($all as $t): ?>
            <tr>
              <td><code><?=htmlspecialchars($t['name'])?></code></td>
              <td><?=htmlspecialchars($t['title'])?></td>
              <td><?=htmlspecialchars($t['version'] ?: '-')?></td>
              <td><?=!empty($t['has_user']) ? '<span class="text-success">支持</span>' : '<span class="text-muted">—</span>'?></td>
              <td><?=!empty($t['has_admin']) ? '<span class="text-success">支持</span>' : '<span class="text-muted">—</span>'?></td>
              <td><?=!empty($t['has_docker']) ? '<span class="text-success">支持</span>' : '<span class="text-muted">—</span>'?></td>
              <td><?=!empty($t['has_home']) ? '<span class="text-success">支持</span>' : '<span class="text-muted">—</span>'?></td>
              <td><?=htmlspecialchars($t['description'] ?: '-')?></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="mn-set-actions">
      <button class="btn btn-primary btn-block" type="button" onclick="settheme()"><i class="mdi mdi-content-save-outline"></i> 保存主题设置</button>
    </div>
    <div class="mn-set-note">
      保存后用户端立即生效；管理端建议整页刷新。主题包放在 <code>templates/主题名/</code> 下即可被扫描。
    </div>
  </div>
</div>

<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-home-city-outline"></i></div>
    <div>
      <h4>主页内容</h4>
      <p>内置主页落地页配置，模板跟随上方所选主页主题（templates/主题/home/）</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="mn-set-field">
      <div class="mn-set-switch">
        <div class="mn-set-switch-txt">
          <strong>启用内置主页</strong>
          <span>关闭后恢复旧行为：插件接管或跳转用户面板</span>
        </div>
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="home_enable" <?php if ($hp('home_enable', 'true') == 'true') echo 'checked'; ?>>
          <label class="custom-control-label" for="home_enable"></label>
        </div>
      </div>
    </div>
    <div class="mn-set-field">
      <label for="home_title">站点标题</label>
      <input type="text" name="home_title" id="home_title" value="<?php echo htmlspecialchars($hp('home_title')); ?>" class="form-control" placeholder="留空使用系统名称"/>
    </div>
    <div class="mn-set-field">
      <label for="home_hero">Hero 标语</label>
      <input type="text" name="home_hero" id="home_hero" value="<?php echo htmlspecialchars($hp('home_hero')); ?>" class="form-control" placeholder="高性能虚拟主机，即买即用"/>
      <small>主页首屏大标题</small>
    </div>
    <div class="mn-set-field">
      <label for="home_primary">主色调</label>
      <div style="display:flex;align-items:center;gap:10px;">
        <input type="color" id="home_primary" value="<?php echo htmlspecialchars($homePrimary); ?>" style="width:46px;height:34px;padding:2px;border:1px solid #ced4da;border-radius:4px;background:#fff;cursor:pointer;">
        <input type="text" class="form-control" id="home_primary_hex" value="<?php echo htmlspecialchars($homePrimary); ?>" style="max-width:140px;" placeholder="#4f46e5"/>
      </div>
      <small>十六进制色值（#rrggbb），应用到主页按钮与强调元素</small>
    </div>
    <div class="mn-set-field">
      <label for="home_logo">站点 Logo</label>
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:8px;">
        <img id="logo_preview" src="<?php echo htmlspecialchars(mnbt_asset_url($hp('home_logo', 'upload_logo/logo.index.png'))); ?>" alt="logo" style="width:36px;height:36px;object-fit:contain;border:1px solid #e5e7eb;border-radius:8px;background:#fff;">
        <input type="file" id="logo_file" accept=".png,.jpg,.jpeg,.gif,.ico" style="display:none;">
        <button type="button" class="btn btn-outline-secondary" onclick="pickHomeIcon('logo')"><i class="mdi mdi-upload"></i> 上传</button>
        <button type="button" class="btn btn-outline-secondary" onclick="$('#home_logo').val('');$('#logo_preview').attr('src','../imsetes/upload_logo/logo.index.png')">恢复默认</button>
      </div>
      <input type="text" name="home_logo" id="home_logo" value="<?php echo htmlspecialchars($hp('home_logo')); ?>" class="form-control" placeholder="上传后自动填入，或手动填写 URL"/>
      <small>留空使用系统控制面板 Logo（imsetes/upload_logo/logo.index.png）</small>
    </div>
    <div class="mn-set-field">
      <label for="home_favicon">Favicon</label>
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:8px;">
        <input type="file" id="favicon_file" accept=".png,.jpg,.jpeg,.gif,.ico" style="display:none;">
        <button type="button" class="btn btn-outline-secondary" onclick="pickHomeIcon('favicon')"><i class="mdi mdi-upload"></i> 上传</button>
        <button type="button" class="btn btn-outline-secondary" onclick="$('#home_favicon').val('')">清除</button>
      </div>
      <input type="text" name="home_favicon" id="home_favicon" value="<?php echo htmlspecialchars($hp('home_favicon')); ?>" class="form-control" placeholder="上传后自动填入，或手动填写 URL"/>
      <small>留空使用系统默认图标</small>
    </div>
    <div class="mn-set-field">
      <label for="home_footer">底部版权</label>
      <input type="text" name="home_footer" id="home_footer" value="<?php echo htmlspecialchars($hp('home_footer')); ?>" class="form-control" placeholder="留空使用系统版权（hxp）"/>
    </div>
    <div class="mn-set-field">
      <div class="mn-set-switch">
        <div class="mn-set-switch-txt">
          <strong>显示公告区</strong>
          <span>展示系统网站公告（MN_config.gg）</span>
        </div>
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="home_show_notice" <?php if ($hp('home_show_notice', 'true') == 'true') echo 'checked'; ?>>
          <label class="custom-control-label" for="home_show_notice"></label>
        </div>
      </div>
      <div class="mn-set-switch">
        <div class="mn-set-switch-txt">
          <strong>显示套餐区</strong>
          <span>hosting_shop 启用且存在有效套餐时展示</span>
        </div>
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="home_show_plans" <?php if ($hp('home_show_plans', 'true') == 'true') echo 'checked'; ?>>
          <label class="custom-control-label" for="home_show_plans"></label>
        </div>
      </div>
    </div>
    <div class="mn-set-actions">
      <button class="btn btn-primary btn-block" type="button" onclick="saveHome()"><i class="mdi mdi-content-save-outline"></i> 保存主页内容</button>
    </div>
    <div class="mn-set-note">
      <b>提示：</b>主页模板位于 <code>templates/当前主页主题/home/index.php</code>，缺页回退
      <code>templates/default/home/index.php</code>。插件可通过 <code>home.blocks</code> 过滤器注入扩展区块；
      启用 shop_frontend 等插件时，插件主页优先接管。
    </div>
  </div>
</div>
<script>
document.getElementById('home_primary').addEventListener('input', function () {
  document.getElementById('home_primary_hex').value = this.value;
});
function saveHome() {
  var data = {
    gn: 'save_home_settings',
    home_enable: $('#home_enable').is(':checked') ? 'true' : 'false',
    home_title: $('#home_title').val() || '',
    home_hero: $('#home_hero').val() || '',
    home_primary: $('#home_primary_hex').val() || '',
    home_logo: $('#home_logo').val() || '',
    home_favicon: $('#home_favicon').val() || '',
    home_footer: $('#home_footer').val() || '',
    home_show_notice: $('#home_show_notice').is(':checked') ? 'true' : 'false',
    home_show_plans: $('#home_show_plans').is(':checked') ? 'true' : 'false',
  };
  $.post('./ajax.php', data, function (r) {
    var j = JSON.parse(r);
    msalert(j.code === '修改成功' ? 1 : 4, j.code || '保存失败', 2000);
  });
}
function pickHomeIcon(target) {
  document.getElementById(target + '_file').click();
}
function bindHomeIcon(target) {
  var input = document.getElementById(target + '_file');
  input.addEventListener('change', function () {
    if (!input.files || !input.files.length) return;
    var fd = new FormData();
    fd.append('gn', 'home_upload_icon');
    fd.append('target', target);
    fd.append('icon', input.files[0]);
    $.ajax({
      url: './ajax.php',
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      success: function (r) {
        var j;
        try { j = typeof r === 'string' ? JSON.parse(r) : r; } catch (e) { j = { code: '响应解析失败' }; }
        if (j.code === '上传成功') {
          var rel = 'imsetes/upload_logo/home_' + target + (target === 'favicon' ? '.ico' : '.png');
          $('#' + target + '_value').val(rel);
          if (target === 'logo') { $('#logo_preview').attr('src', '../' + rel).show(); }
          msalert(1, '上传成功，请点击保存', 2000);
        } else {
          msalert(4, j.code || '上传失败', 2000);
        }
      },
      error: function () { msalert(4, '网络错误，请重试', 2000); }
    });
  });
}
bindHomeIcon('logo');
bindHomeIcon('favicon');
</script>

<?php } else { ?>
<div class="mn-set-card">
  <div class="mn-set-card-bd text-center text-muted py-5">
    请从左侧菜单选择设置项
  </div>
</div>
<?php } ?>
</div>
