<?php mnbt_admin_include('head'); ?>
<?php header("Cache-Control: no-cache, must-revalidate"); ?>
<script type="text/javascript" src="../imsetes/js/md5.js"></script>
<script type="text/javascript" src="../imsetes/js/xtset.js?hc=<? echo $date?>"></script>
<link rel="stylesheet" href="<?=mnbt_theme_url('assets/set-page.css', 'admin')?>">

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
      <label for="mrphp">默认 PHP 版本</label>
      <select class="form-control" id="mrphp" name="mrphp" size="1">
        <option value="52" <?php if ($conf['hxu'] == '52') echo 'selected'; ?>>PHP-5.2</option>
        <option value="53" <?php if ($conf['hxu'] == '53') echo 'selected'; ?>>PHP-5.3</option>
        <option value="54" <?php if ($conf['hxu'] == '54') echo 'selected'; ?>>PHP-5.4</option>
        <option value="55" <?php if ($conf['hxu'] == '55') echo 'selected'; ?>>PHP-5.5</option>
        <option value="56" <?php if ($conf['hxu'] == '56') echo 'selected'; ?>>PHP-5.6</option>
        <option value="70" <?php if ($conf['hxu'] == '70') echo 'selected'; ?>>PHP-7.0</option>
        <option value="71" <?php if ($conf['hxu'] == '71') echo 'selected'; ?>>PHP-7.1</option>
        <option value="72" <?php if ($conf['hxu'] == '72') echo 'selected'; ?>>PHP-7.2</option>
        <option value="73" <?php if ($conf['hxu'] == '73') echo 'selected'; ?>>PHP-7.3</option>
        <option value="74" <?php if ($conf['hxu'] == '74') echo 'selected'; ?>>PHP-7.4</option>
        <option value="80" <?php if ($conf['hxu'] == '80') echo 'selected'; ?>>PHP-8.0</option>
        <option value="81" <?php if ($conf['hxu'] == '81') echo 'selected'; ?>>PHP-8.1</option>
      </select>
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
      <h4>支付配置</h4>
      <p>易支付对接参数</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="mn-set-field">
      <label for="yurl">易支付地址</label>
      <input type="text" name="yurl" id="yurl" value="<?php echo $conf['hxe']; ?>" class="form-control" placeholder="易支付对接地址" required/>
    </div>
    <div class="mn-set-field">
      <label for="yid">商户 ID</label>
      <input type="text" name="yid" id="yid" value="<?php echo $conf['hxr']; ?>" class="form-control" placeholder="易支付商户ID" required/>
    </div>
    <div class="mn-set-field">
      <label for="ykey">商户 KEY</label>
      <input type="text" name="ykey" id="ykey" value="<?php echo $conf['hxt']; ?>" class="form-control" placeholder="易支付站点中您的密钥（KEY）" required/>
    </div>
    <div class="mn-set-actions">
      <button class="btn btn-primary btn-block" type="button" onclick="setzf()"><i class="mdi mdi-content-save-outline"></i> 保存修改</button>
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
  $curUserTheme = mnbt_theme_name('user');
  $curAdminTheme = mnbt_theme_name('admin');
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
              <th>说明</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $all = mnbt_theme_list(null);
          if (empty($all)): ?>
            <tr><td colspan="6" class="text-center text-muted">未发现主题</td></tr>
          <?php else: foreach ($all as $t): ?>
            <tr>
              <td><code><?=htmlspecialchars($t['name'])?></code></td>
              <td><?=htmlspecialchars($t['title'])?></td>
              <td><?=htmlspecialchars($t['version'] ?: '-')?></td>
              <td><?=!empty($t['has_user']) ? '<span class="text-success">支持</span>' : '<span class="text-muted">—</span>'?></td>
              <td><?=!empty($t['has_admin']) ? '<span class="text-success">支持</span>' : '<span class="text-muted">—</span>'?></td>
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
<?php } else { ?>
<div class="mn-set-card">
  <div class="mn-set-card-bd text-center text-muted py-5">
    请从左侧菜单选择设置项
  </div>
</div>
<?php } ?>
</div>
