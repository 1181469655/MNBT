<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>梦奈宝塔主机系统(MNBT) · 安装向导</title>
    <link rel="stylesheet" href="./index.install.css" />
</head>
<body class="d-none">

<!-- 背景柔和装饰 -->
<div class="deco deco-1"></div>
<div class="deco deco-2"></div>

<div class="install-card">

    <!-- 顶部品牌栏 -->
    <header class="install-header">
        <div class="brand">
            <div class="brand-logo"><img src="/imsetes/images/logo-ico.png" alt="MNBT" /></div>
            <div>
                <h1>MNBT 梦奈宝塔主机系统</h1>
                <p>安装向导 · 欢迎使用</p>
            </div>
        </div>
        <span class="ver-badge">V<span class="mn-vs">1.84</span></span>
    </header>

    <!-- 横向步骤条 -->
    <div class="steps install-page-num">
        <div class="step">
            <div class="dot"><span>1</span></div>
            <div class="label"><b>欢迎</b><i>系统介绍</i></div>
        </div>
        <div class="step">
            <div class="dot"><span>2</span></div>
            <div class="label"><b>许可协议</b><i>阅读条款</i></div>
        </div>
        <div class="step">
            <div class="dot"><span>3</span></div>
            <div class="label"><b>环境监测</b><i>兼容检测</i></div>
        </div>
        <div class="step">
            <div class="dot"><span>4</span></div>
            <div class="label"><b>数据库</b><i>连接信息</i></div>
        </div>
        <div class="step">
            <div class="dot"><span>5</span></div>
            <div class="label"><b>站点配置</b><i>管理员</i></div>
        </div>
        <div class="step">
            <div class="dot"><span>6</span></div>
            <div class="label"><b>等待安装</b><i>执行安装</i></div>
        </div>
        <div class="step">
            <div class="dot"><span>7</span></div>
            <div class="label"><b>完成</b><i>安装成功</i></div>
        </div>
    </div>

    <!-- 主体 -->
    <div class="install-body">
        <div class="progress-row progress-text">
            <span>步骤 1 / 7</span>
            <span class="pct">0%</span>
        </div>
        <div class="progress-track">
            <div class="progress-fill progress-bar-anim" style="width: 0;"></div>
        </div>

        <div id="content-page-main">

            <!-- 1 欢迎 -->
            <div>
                <div class="panel-head">
                    <div class="panel-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>
                    </div>
                    <div>
                        <h2>欢迎使用 梦奈宝塔主机系统</h2>
                        <p>版本 V<span class="mn-vs">1.84</span> &middot; 约需 2 分钟</p>
                    </div>
                </div>
                <p class="welcome-lead">
                    欢迎使用由梦奈基于光年 V4 框架原创的 <b>MN 宝塔主机系统</b>（简称 MNBT）！
                    本系统免费发布于网络，提供虚拟主机全生命周期管理与自动化运维能力。
                </p>
                <p class="welcome-links">
                    官网：<a target="_blank" href="https://mf.mengnai.top/">mf.mengnai.top</a>
                    &nbsp;·&nbsp; QQ群：994752422
                </p>
                <div class="tip-box warn d-none mn-install-lock">
                    <span class="mdi">⚠</span>
                    <span>您已经安装了本系统，如需重新安装请删除 install 目录下的 install.lock 文件，然后刷新本页面。</span>
                </div>
            </div>

            <!-- 2 许可协议 -->
            <div>
                <div class="panel-head">
                    <div class="panel-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                    </div>
                    <div>
                        <h2>许可协议</h2>
                        <p>请阅读并接受以下条款</p>
                    </div>
                </div>
                <div class="license-box">
                    <iframe src="/xy.html"></iframe>
                </div>
                <div class="agree-card use-terms must">
                    <div class="check">
                        <svg viewBox="0 0 11 9" fill="none"><path d="M1 4L4 7L10 1" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div>
                        <b>我已阅读并同意上述许可协议的所有条款</b>
                        <small>继续安装即表示您接受本协议</small>
                    </div>
                </div>
            </div>

            <!-- 3 系统环境监测 -->
            <div>
                <div class="panel-head">
                    <div class="panel-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div>
                        <h2>系统环境监测</h2>
                        <p>检测系统环境是否满足运行要求</p>
                    </div>
                </div>
                <div class="sys-list install-system-info">
                    <div class="sys-item php_vs">
                        <div class="s-ico">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
                        </div>
                        <div>
                            <b>PHP</b>
                            <span>需要 PHP 7.4 及以上</span>
                        </div>
                    </div>
                    <div class="sys-item curl_exec">
                        <div class="s-ico">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H7a2 2 0 0 0-2 2v5a2 2 0 0 1-2 2 2 2 0 0 1 2 2v5c0 1.1.9 2 2 2h1"/><path d="M16 21h1a2 2 0 0 0 2-2v-5c0-1.1.9-2 2-2a2 2 0 0 1-2-2V5a2 2 0 0 0-2-2h-1"/></svg>
                        </div>
                        <div>
                            <b>curl_exec</b>
                            <span>用于与宝塔 API 通信</span>
                        </div>
                    </div>
                    <div class="sys-item mn_link">
                        <div class="s-ico">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                        </div>
                        <div>
                            <b>MNBT 更新支持</b>
                            <span>用户在线升级系统至最新版本</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4 数据库配置 -->
            <div>
                <div class="panel-head">
                    <div class="panel-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/></svg>
                    </div>
                    <div>
                        <h2>数据库配置</h2>
                        <p>请填写数据库连接信息</p>
                    </div>
                </div>
                <form class="form-database">
                    <div class="form-item">
                        <label for="db_host">数据库地址</label>
                        <input id="db_host" type="text" placeholder="数据库连接地址，例如 127.0.0.1" value="localhost" required />
                    </div>
                    <div class="form-item">
                        <label for="db_port">数据库端口</label>
                        <input id="db_port" type="text" placeholder="数据库端口，例如 3306" value="3306" required />
                    </div>
                    <div class="form-item">
                        <label for="db_user">数据库用户名</label>
                        <input id="db_user" type="text" placeholder="数据库用户名" value="" required />
                    </div>
                    <div class="form-item">
                        <label for="db_name">数据库名</label>
                        <input id="db_name" type="text" placeholder="数据库名" value="" required />
                    </div>
                    <div class="form-item">
                        <label for="db_pwd">数据库密码</label>
                        <input id="db_pwd" type="text" placeholder="数据库密码" value="" required />
                    </div>
                </form>
            </div>

            <!-- 5 站点与管理员 -->
            <div>
                <div class="panel-head">
                    <div class="panel-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    <div>
                        <h2>站点与管理员</h2>
                        <p>设置网站信息与后台登录账号</p>
                    </div>
                </div>
                <form class="form-site">
                    <div class="form-item">
                        <label for="site_name">控制面板名称</label>
                        <input id="site_name" type="text" placeholder="例如：梦奈主机控制面板" value="MNBT 控制面板" maxlength="80" required />
                    </div>
                    <div class="form-item">
                        <label for="site_qq">站长 QQ（选填）</label>
                        <input id="site_qq" type="text" placeholder="用于用户联系" value="" maxlength="20" />
                    </div>
                    <div class="form-item">
                        <label for="site_gg">网站公告（选填）</label>
                        <textarea id="site_gg" rows="2" placeholder="安装后显示在前台/后台的公告" maxlength="2000"></textarea>
                    </div>
                    <div class="form-item">
                        <label for="admin_user">管理员账号</label>
                        <input id="admin_user" type="text" placeholder="后台登录用户名" value="admin" maxlength="50" required />
                    </div>
                    <div class="form-item">
                        <label for="admin_pwd">管理员密码</label>
                        <input id="admin_pwd" type="password" placeholder="至少 6 位" value="" minlength="6" maxlength="64" required />
                    </div>
                    <div class="form-item">
                        <label for="admin_pwd2">确认密码</label>
                        <input id="admin_pwd2" type="password" placeholder="再次输入密码" value="" minlength="6" maxlength="64" required />
                    </div>
                </form>
            </div>

            <!-- 6 安装模式 -->
            <div>
                <div class="panel-head">
                    <div class="panel-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>
                    </div>
                    <div>
                        <h2>安装模式选择</h2>
                        <p>选择安装方式或点击下一步</p>
                    </div>
                </div>
                <div class="tip-box info new-install-tip">
                    <span class="mdi">✓</span>
                    <span>数据库与站点信息已就绪，点击「开始安装」即可完成安装。</span>
                </div>
                <div class="new-install-select d-none">
                    <div class="tip-box warn">
                        <span class="mdi">⚠</span>
                        <span>检测到您已安装过梦奈宝塔主机系统，请选择操作方式。</span>
                    </div>
                    <div class="choice-list install-type-btn">
                        <div class="choice-item use-terms mn-upgrade">
                            <div class="check"><svg viewBox="0 0 11 9" fill="none"><path d="M1 4L4 7L10 1" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                            <div>
                                <b>覆盖更新（保留数据升级到 V1.84）<span class="mn-upgrade-detail"></span></b>
                                <small>添加 V1.84 新增表和字段，自动补全缺失项，保留所有已有数据</small>
                            </div>
                        </div>
                        <div class="choice-item use-terms mn-repair">
                            <div class="check"><svg viewBox="0 0 11 9" fill="none"><path d="M1 4L4 7L10 1" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                            <div>
                                <b>修复数据库（补齐缺失的表和字段）</b>
                                <small>仅扫描并创建缺失的数据库表和字段，不覆盖已有数据</small>
                            </div>
                        </div>
                        <div class="choice-item use-terms mn-new-install">
                            <div class="check"><svg viewBox="0 0 11 9" fill="none"><path d="M1 4L4 7L10 1" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                            <div>
                                <b>强制全新安装</b>
                                <small>清空所有旧数据，全新创建数据库表（不可恢复）</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7 完成 -->
            <div>
                <div class="done-box">
                    <div class="done-ico">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
                    </div>
                    <h2>安装成功！</h2>
                    <p>梦奈宝塔主机系统已安装到您的站点中</p>
                    <p>请妥善保存管理员账号；为安全建议删除站点下的 install 目录</p>
                </div>
                <div class="admin-info">
                    <p class="ai-title">管理员登录信息</p>
                    <p class="ai-row">后台地址：<code>域名/admin</code></p>
                    <p class="ai-row">账号：<code class="install-admin-user">admin</code></p>
                    <p class="ai-row">密码：<code class="install-admin-pwd">（安装时设置）</code></p>
                    <p class="ai-row">控制面板名称：<code class="install-site-name">—</code></p>
                </div>
                <div class="entry-list">
                    <a class="entry primary" href="../admin" target="_blank">
                        <div class="e-ico"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg></div>
                        <div><b>访问应用后台</b><small>使用上方账号登录</small></div>
                        <span class="e-arrow"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg></span>
                    </a>
                    <a class="entry" href="../user" target="_blank">
                        <div class="e-ico"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg></div>
                        <div><b>访问主机控制面板</b><small>默认地址：域名/user</small></div>
                        <span class="e-arrow"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg></span>
                    </a>
                    <a class="entry" href="http://mf.mengnai.top/" target="_blank">
                        <div class="e-ico"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg></div>
                        <div><b>访问官网</b><small>梦奈宝塔主机系统官方网站</small></div>
                        <span class="e-arrow"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg></span>
                    </a>
                    <a class="entry" href="http://wpa.qq.com/msgrd?v=3&uin=994752422&site=qq&menu=yes" target="_blank">
                        <div class="e-ico"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.992 16.342a2 2 0 0 1 .094 1.167l-1.065 3.29a1 1 0 0 0 1.236 1.168l3.413-.998a2 2 0 0 1 1.099.092 10 10 0 1 0-4.777-4.719"/></svg></div>
                        <div><b>加入 QQ 交流群</b><small>与数千名用户交流分享</small></div>
                        <span class="e-arrow"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg></span>
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- 底部操作区 -->
    <footer class="install-foot">
        <div class="tip-msg next-tips">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
            <span>您必须填写完整后才能继续安装</span>
        </div>
        <button class="btn-next" id="btn-install-next">开始安装 →</button>
    </footer>

</div>

<script src="/imsetes/js/jquery.min.js"></script>
<script>
    let curr_index=1;
    const MAX_INDEX=6;
    const COUNT_INDEX=7;
    let siteConfigCache={};
    const NEXT_BTN_FUN={
        //禁用下一步按钮
        disabled: ()=>{
            $('#btn-install-next').prop('disabled',true);
        },
        //取消禁用下一步按钮
        removeDisabled: ()=>{
            $('#btn-install-next').prop('disabled',false).html(curr_index!==1?'下一步 →':'开始安装 →');
        },
        //按钮显示加载信息并禁用
        loading: (msg)=>{
            $('#btn-install-next').prop('disabled',true).html(msg || '加载中...');
        },
        //显示提示
        TipsShow:(msg)=>{
            $('.next-tips').show().children('span').html(msg).parents('div.mt-auto').removeClass('justify-end').addClass('justify-between');
        },
        //禁用提示
        TipsHide:()=>{
            $('.next-tips').hide().parents('div.mt-auto').removeClass('justify-between').addClass('justify-end');
        }
    }

    //初始化
    $(async ()=>{
        refresh();
        $(`#content-page-main>div:eq(${curr_index-1})`).addClass('page-active');
        $('body.d-none').removeClass('d-none');
        let info=await request('index');
        if(info.data){
            $('.mn-vs').html(info.data.vs);
            if(info.data.is_install){
                $('.mn-install-lock').removeClass('d-none');
                NEXT_BTN_FUN.loading('您已安装！');
            }
        }
    });

    const refresh=()=>{
        NEXT_BTN_FUN.TipsHide();
        $(`.install-page-num>div.curr-page-active`).removeClass('curr-page-active');
        $(`.install-page-num>div:eq(${curr_index-1})`).addClass('curr-page-active');

        let progress=Math.floor(((curr_index-1)/(COUNT_INDEX-1))*100);
        $('.progress-bar-anim').css('width',`${progress}%`);
        $('.progress-text>span:first,.progress-text-info').html(`步骤 ${curr_index} / 7`);
        $('.progress-text>span:last').html(`${progress}%`);
        let btn_text_arr={
            1:'开始安装 →',
            6:'完成安装 →',
        };
        let btn=$("#btn-install-next");
        btn.html(btn_text_arr[curr_index] || '下一步 →');
        if(curr_index===7)btn.hide();
        else btn.show();
    }

    //点击下一页按钮
    $('#btn-install-next').on('click', async function() {
        if (curr_index>MAX_INDEX)return;
        NEXT_BTN_FUN.disabled();

        //提交数据库配置信息
        if (curr_index===4){
            let data={};
            let inputTotal=$(".form-database input").each(function () {
                let ts=$(this);
                let val=$(this).val();
                if (val === '') return false;
                data[ts.attr('id')]=val;
            }).length;
            if (Object.keys(data).length<inputTotal) {
                return NEXT_BTN_FUN.TipsShow('请将表单填写完整，表单可下滑');
            }
            NEXT_BTN_FUN.loading('连接中....');
            let result = await request('database_info_wire',data);
            if (result.code!==1){
                NEXT_BTN_FUN.removeDisabled();
                return NEXT_BTN_FUN.TipsShow(result.msg);
            }
            if(result.data.in_table){
                console.log('[安装检测] 发现已有数据表', result.data);
                $('.new-install-select').removeClass('d-none');
                $('.new-install-tip').addClass('d-none');
                NEXT_BTN_FUN.disabled();
                NEXT_BTN_FUN.TipsShow('正在检测更新项...');
                let upgradeInfo = await request('check_upgrade');
                console.log('[升级检测] 结果:', upgradeInfo);
                if(upgradeInfo.code === 1 && upgradeInfo.data){
                    if(upgradeInfo.data.need_upgrade){
                        let detail = [];
                        if(upgradeInfo.data.missing_tables && upgradeInfo.data.missing_tables.length>0){
                            detail.push('缺'+upgradeInfo.data.missing_tables.length+'张表');
                        }
                        if(upgradeInfo.data.missing_columns && upgradeInfo.data.missing_columns.length>0){
                            detail.push('缺'+upgradeInfo.data.missing_columns.length+'个字段');
                        }
                        $('.mn-upgrade-detail').text(detail.length>0 ? '（'+detail.join('，')+'）' : '');
                        $('.mn-upgrade').parent().show();
                        $('.mn-repair').parent().show();
                    } else {
                        console.log('[升级检测] 数据完整，无需升级');
                        $('.mn-upgrade-detail').text('（当前已是 V1.84，无需升级）');
                        $('.mn-upgrade').parent().show();
                        $('.mn-repair').parent().show();
                    }
                } else {
                    console.warn('[升级检测] 检测失败，显示全部选项', upgradeInfo);
                    $('.mn-upgrade').parent().show();
                    $('.mn-repair').parent().show();
                }
                NEXT_BTN_FUN.TipsShow('请在上方选择一个操作方式');
            } else {
                console.log('[安装检测] 全新数据库，无旧表');
                $('.new-install-select').addClass('d-none');
                $('.new-install-tip').removeClass('d-none');
            }
        }else if(curr_index===5){
            let siteCheck=validateSiteForm(true);
            if(!siteCheck.ok){
                NEXT_BTN_FUN.removeDisabled();
                return NEXT_BTN_FUN.TipsShow(siteCheck.msg);
            }
            siteConfigCache=siteCheck.data;
        }else if(curr_index===6){
            let install_mode = 'install';
            let repair_only = false;
            if(!$('.new-install-select').hasClass('d-none')){
                if($('.mn-upgrade.use-terms').hasClass('terms-yes')){
                    install_mode = 'upgrade';
                } else if($('.mn-repair.use-terms').hasClass('terms-yes')){
                    install_mode = 'upgrade';
                    repair_only = true;
                } else if($('.mn-new-install.use-terms').hasClass('terms-yes')){
                    install_mode = 'install';
                } else {
                    install_mode = 'skip';
                }
            }
            if(repair_only){
                NEXT_BTN_FUN.loading('修复中...');
                let result = await request('repair');
                if(result.code!==1){
                    NEXT_BTN_FUN.removeDisabled();
                    return NEXT_BTN_FUN.TipsShow(result.msg);
                }
                $('.install-admin-user').text(siteConfigCache.admin_user || '（保持原设置）');
                $('.install-admin-pwd').text('（保持原设置）');
                $('.install-site-name').text(siteConfigCache.site_name || '—');
                next(curr_index++);
                refresh();
                if (curr_index===6)NEXT_BTN_FUN.removeDisabled();
                return;
            }
            NEXT_BTN_FUN.loading(install_mode==='upgrade'?'升级中...':'安装中...');
            let payload=Object.assign({
                install_mode: install_mode
            }, siteConfigCache);
            let result = await request('install', payload);
            if(result.code!==1){
                NEXT_BTN_FUN.removeDisabled();
                return NEXT_BTN_FUN.TipsShow(result.msg);
            }
            $('.install-admin-user').text(siteConfigCache.admin_user || 'admin');
            $('.install-admin-pwd').text(siteConfigCache.admin_pwd || '（已设置）');
            $('.install-site-name').text(siteConfigCache.site_name || '—');
        }
        //进入下一页
        next(curr_index++);
        refresh();
        if (curr_index===6){
            if(!$('.new-install-select').hasClass('d-none') && !$('.mn-upgrade.use-terms').hasClass('terms-yes') && !$('.mn-repair.use-terms').hasClass('terms-yes') && !$('.mn-new-install.use-terms').hasClass('terms-yes')){
                NEXT_BTN_FUN.disabled();
                NEXT_BTN_FUN.TipsShow('请在上方选择一个操作方式');
            } else {
                NEXT_BTN_FUN.removeDisabled();
            }
        }
        else if (curr_index===5){
            NEXT_BTN_FUN.TipsShow('请填写站点信息与管理员账号');
            checkSiteFormValidation();
        }
        else if (curr_index===4)NEXT_BTN_FUN.TipsShow('您必须将表单填写完整后才能继续安装，表单可下滑');
        else if (curr_index===3)await systemCheck();
        else if (curr_index===2) NEXT_BTN_FUN.TipsShow('您必须同意许可协议才能继续安装');
    });


    //进入到下一页（先移除旧面板激活态，避免多个面板同时在文档流中堆积）
    const next=(index)=>{
        let currDom=$('#content-page-main>div.page-active');
        let nextDom=$(`#content-page-main>div:eq(${index})`);
        currDom.removeClass('page-active').css('animation-name','exit');
        nextDom.css('animation-name','entry').addClass('page-active');
    }

    const request=async (action,data={})=>{
        try{
            return await $.ajax({
                url: './install.api.php?action='+action,
                type: 'POST',
                data: data,
                dataType: 'json',
            }).then((data)=>{
                if (data.redirect!==null){
                    if (data.redirect!==curr_index){
                        NEXT_BTN_FUN.TipsShow(data.msg);
                        setTimeout(()=>{
                            alert(data.msg)
                            window.location.reload();
                        },1000)
                    }
                }
                return data;
            });
        }catch(error){
            console.log(error);
            NEXT_BTN_FUN.TipsShow('安装模块异常，请联系官方，QQ群：994752422');
            alert('安装模块异常，请联系官方，QQ群：994752422');
            return {code: -1, msg: '网络异常，请刷新重试'};
        }
    }

    //协议相关监听
    $('.use-terms').on('click', function() {
        let thisClass=$(this);
        // 安装类型按钮：互斥选择（选升级则取消重装，选重装则取消升级）
        if(thisClass.hasClass('mn-upgrade') || thisClass.hasClass('mn-repair') || thisClass.hasClass('mn-new-install')){
            if(thisClass.hasClass('terms-yes')) {
                thisClass.removeClass('terms-yes');
                NEXT_BTN_FUN.disabled();
                NEXT_BTN_FUN.TipsShow('请在上方选择一个操作方式');
            } else {
                $('.install-type-btn .use-terms').removeClass('terms-yes');
                thisClass.addClass('terms-yes');
                NEXT_BTN_FUN.removeDisabled();
                NEXT_BTN_FUN.TipsHide();
            }
            return;
        }
        if(thisClass.hasClass('terms-yes')) {
            thisClass.removeClass('terms-yes').next().removeClass('d-none');
            if (thisClass.hasClass('must')) {
                NEXT_BTN_FUN.disabled();
                NEXT_BTN_FUN.TipsShow('您必须同意许可协议才能继续安装');
            }
        }else{
            thisClass.addClass('terms-yes').next().addClass('d-none');
            if (thisClass.hasClass('must')) {
                NEXT_BTN_FUN.removeDisabled();
                NEXT_BTN_FUN.TipsHide();
            }
        }
    });

    const checkFormValidation = function() {
        let isAllFilled = true;
        $(".form-database input").each(function () {
            if ($(this).val() === '') {
                isAllFilled = false;
                return false;
            }
        });
        if (isAllFilled) {
            NEXT_BTN_FUN.removeDisabled();
            NEXT_BTN_FUN.TipsHide();
            $('.next-tips').hide();
        } else {
            NEXT_BTN_FUN.disabled();
            $('.next-tips').show();
            NEXT_BTN_FUN.TipsShow('您必须将表单填写完整后才能继续安装，表单可下滑');
        }
        return isAllFilled;
    };

    $(".form-database input").on("change input", checkFormValidation);

    const validateSiteForm = function(strict) {
        let site_name=($('#site_name').val()||'').trim();
        let site_qq=($('#site_qq').val()||'').trim();
        let site_gg=($('#site_gg').val()||'').trim();
        let admin_user=($('#admin_user').val()||'').trim();
        let admin_pwd=$('#admin_pwd').val()||'';
        let admin_pwd2=$('#admin_pwd2').val()||'';
        if(!site_name) return {ok:false, msg:'请填写控制面板名称'};
        if(!admin_user) return {ok:false, msg:'请填写管理员账号'};
        if(admin_user.length<3) return {ok:false, msg:'管理员账号至少 3 位'};
        if(!/^[a-zA-Z0-9_\u4e00-\u9fa5-]+$/.test(admin_user)) return {ok:false, msg:'管理员账号含非法字符'};
        if(admin_pwd.length<6) return {ok:false, msg:'管理员密码至少 6 位'};
        if(admin_pwd!==admin_pwd2) return {ok:false, msg:'两次输入的密码不一致'};
        if(site_qq && !/^\d{5,15}$/.test(site_qq)) return {ok:false, msg:'QQ 号格式不正确'};
        return {
            ok:true,
            data:{site_name, site_qq, site_gg, admin_user, admin_pwd}
        };
    };

    const checkSiteFormValidation = function() {
        if(curr_index!==5) return;
        let r=validateSiteForm(true);
        if(r.ok){
            NEXT_BTN_FUN.removeDisabled();
            NEXT_BTN_FUN.TipsHide();
        }else{
            NEXT_BTN_FUN.disabled();
            NEXT_BTN_FUN.TipsShow(r.msg);
        }
        return r.ok;
    };

    $(".form-site input, .form-site textarea").on("change input", checkSiteFormValidation);

    const systemCheck=async ()=>{
        let result = await request('system');
        $('.install-system-info>div').addClass('yes');
        if (!result.data.vs.is_vs_install)$('.php_vs').addClass('no');
        if (!result.data.curl_exec)$('.curl_exec').addClass('no');
        if (!result.data.mn_link)$('.mn_link').addClass('mn-no');
        $('.install-system-info>div.no').length<=0 && NEXT_BTN_FUN.removeDisabled();
    }

    //防抖
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            if (timeout) clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(this, args);
            }, wait);
        };
    }

    //5秒后强制显示页面
    setTimeout(()=>{
        $('body.d-none').removeClass('d-none');
    },5000);
</script>

</body>
</html>
