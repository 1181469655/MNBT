<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>梦奈宝塔主机系统(MNBT)-安装向导</title>
    <meta name="description" content="Paico Generated Project" />
    <meta name="author" content="Paico" />
    <meta property="og:image" content="/og-image.png" />
    <link rel="stylesheet" crossorigin="" href="./index.install.css" />
</head>
<style>
    #WelcomeStep *{
        overflow-x: hidden;
    }
    #content-page-main{
        width: 100%;
        height: 100%;
        min-height: 478px;
        position: relative;
        overflow-y: auto;
        overflow-x: hidden;
    }
    @media (max-height: 800px) {
        #content-page-main {
            min-height: calc(100vh - 350px);
        }
    }

    #content-page-main>div{
        width: 100%;
        height: 100%;
        animation-duration: 1s; /* 动画持续时间 */
        animation-fill-mode: forwards; /* 保持动画结束时的状态 */
        position: absolute;
        top: 0;
        left: 0;
    }
    #content-page-main>div:not(.page-active){
        /*display: none;*/
        left: -100%;
        display: none;
        /*animation-name: entry;*/
    }
    #content-page-main>div.page-active{
        opacity: 1;
        left: 0;
        /*animation-name: exit;*/
    }
    .button-group{
        padding-top: 10px;
    }
    iframe{
        width: 100%;
        height: 100%;
    }
    .d-none{
        display: none;
    }



    @keyframes exit {
        from {
            left: 0;
            opacity: 1;
        }
        to {
            left: -100%;
            opacity: 0;
        }
    }
    @keyframes entry {
        from {
            left: 100%;
            opacity: 0;
        }
        to {
            left: 0;
            opacity: 1;
        }
    }
    .terms-yes{
        border-color: var(--primary) !important;
        background: var(--accent) !important;
    }
    .terms-yes>div:first-child{
        background: var(--primary) !important;
        border-color: var(--primary) !important;
    }
    .terms-yes>div:first-child>svg{
        display: block !important;
    }
    button:disabled{
        opacity: 0.45 !important;
        cursor: not-allowed !important;
    }


    .install-page-num>div>div:first-child>div:first-child{
        background: var(--wizard-step-done) !important;
        color: rgb(10, 17, 32) !important;
    }
    .install-page-num>div>div:first-child>div:first-child::after{
        content: '';
        width: 15px;
        height: 15px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 6 9 17l-5-5'/%3E%3C/svg%3E");
    }
    .install-page-num>div>div:first-child>div:last-child{
        background: var(--wizard-step-done) !important;
    }
    .install-page-num>div>div:first-child>div:first-child>span{
        display: none !important;
    }
    .install-page-num>div>div:last-child>p:first-child{
        color: rgba(255, 255, 255, 0.8) !important;
    }
    .install-page-num>div>div:last-child>p:last-child{
        color: rgba(255, 255, 255, 0.3) !important;
    }
    .install-page-num>div.curr-page-active ~ div>div:first-child>div:first-child{
        background: rgba(255, 255, 255, 0.15) !important;
        color: rgba(255, 255, 255, 0.5) !important;
    }
    .install-page-num>div.curr-page-active ~ div>div:first-child>div:first-child::after{
        display: none;
    }
    .install-page-num>div.curr-page-active ~ div>div:first-child>div:first-child>span{
        display: block !important;
    }
    .install-page-num>div.curr-page-active ~ div>div:first-child>div:last-child{
        min-height: 20px !important;
        background: rgba(255, 255, 255, 0.15) !important;
    }
    .install-page-num>div.curr-page-active ~ div>div:last-child>p:first-child{
        color: rgba(255, 255, 255, 0.4) !important;
    }
    .install-page-num>div.curr-page-active ~ div>div:last-child>p:last-child{
        color: rgba(255, 255, 255, 0.3) !important;
    }
    .install-page-num>div.curr-page-active>div:first-child>div:last-child{
        min-height: 20px !important;
        background: rgba(255, 255, 255, 0.15) !important;
    }
    .install-page-num>div.curr-page-active>div:first-child>div:first-child{
        background: rgb(255, 255, 255) !important;
        color: var(--primary) !important;
    }
    .install-page-num>div.curr-page-active>div:first-child>div:first-child::after{
        display: none;
    }
    .install-page-num>div.curr-page-active>div:first-child>div:first-child>span{
        display: block !important;
    }
    .install-page-num>div.curr-page-active>div:last-child>p:first-child{
        color: rgba(255, 255, 255, 1) !important;
    }
    .install-page-num>div.curr-page-active>div:last-child>p:last-child{
        color: rgba(255, 255, 255, 0.7) !important;
    }

    .not-transition * {
        transition: none !important;
    }

    .install-system-info>div{
        position: relative;
    }
    .install-system-info>div::after{
        content: '监测中...';
        position: absolute;
        top: calc(50% - 10px);
        right: 0;
        width: auto;
        height: 20px;
        padding: 0 10px;
        font-size: 14px;
        color: var(--primary);
    }
    .install-system-info>div.yes{
        border: 1px solid var(--primary) !important;
        box-shadow: 0 0 8px -3px var(--primary) !important;
    }
    .install-system-info>div.yes::after{
        content: '支持';
        color: var(--primary) !important;
    }
    .install-system-info>div.no::after{
        content: '不支持';
        color: var(--red) !important;
    }
    .install-system-info>div.mn-no::after{
        content: '非必须，不影响继续安装';
        color: #fdaa02 !important;
    }

    .tips-er{
        width: 100%;
        border-radius: 5px;
        padding: 20px 15px;
        margin: 5px 0;
    }
    .tips-er.tips-type-blue{
        background-color: #4094f6;
        color: #fff;
    }
    .tips-er.tips-type-warning{
        background-color: #efc12d;
        color: #fff;
    }
    .tips-er.tips-type-blue::before{
        content: '       ';
        width: 15px;
        height: 15px;
        display: inline-block;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 6 9 17l-5-5'/%3E%3C/svg%3E");
    }
    .tips-er.tips-type-warning::before{
        content: '(・_・;)';
    }

    .cursor-pointer-button>button{
        cursor: pointer;
    }
</style>
<body class="d-none">
<div id="root">
    <div class="min-h-screen bg-background flex items-center justify-center p-4 md:p-8">
        <div class="wizard-card bg-card rounded-2xl overflow-hidden w-full" style="max-width: 900px;">
            <div class="flex flex-col md:flex-row" style="min-height: 560px;">
                <div class="sidebar-gradient flex flex-col p-6 md:p-8 w-full md:w-64 flex-shrink-0">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-9 h-9 rounded-xl bg-primary-foreground/20 flex items-center justify-center">
                            <img src="/imsetes/images/logo-ico.png" alt="MNBT-LOGO" width="30" height="30" />
                        </div>
                        <div>
                            <p class="text-primary-foreground font-bold text-base leading-none">MNBT</p>
                            <p class="text-xs leading-none mt-1" style="color: rgba(255, 255, 255, 0.5);">V<span class="mn-vs">1.81</span> 安装向导</p>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div data-cmp="StepIndicator" class="flex flex-col gap-2 w-full install-page-num">
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold flex-shrink-0 transition-all duration-1000" style="background: rgb(255, 255, 255); color: var(--primary);">
                                        <span>1</span>
                                    </div>
                                    <div class="w-0.5 flex-1 mt-1" style="min-height: 20px; background: rgba(255, 255, 255, 0.15);"></div>
                                </div>
                                <div class="pb-4">
                                    <p class="text-sm font-semibold leading-none mb-0.5" style="color: rgb(255, 255, 255);">欢迎</p>
                                    <p class="text-xs leading-tight" style="color: rgba(255, 255, 255, 0.7);"> 系统介绍</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold flex-shrink-0 transition-all duration-1000" style="background: rgba(255, 255, 255, 0.15); color: rgba(255, 255, 255, 0.5);">
                                        <span>2</span>
                                    </div>
                                    <div class="w-0.5 flex-1 mt-1" style="min-height: 20px; background: rgba(255, 255, 255, 0.15);"></div>
                                </div>
                                <div class="pb-4">
                                    <p class="text-sm font-semibold leading-none mb-0.5" style="color: rgba(255, 255, 255, 0.4);">许可协议</p>
                                    <p class="text-xs leading-tight" style="color: rgba(255, 255, 255, 0.3);"> 阅读并接受条款</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold flex-shrink-0 transition-all duration-1000" style="background: rgba(255, 255, 255, 0.15); color: rgba(255, 255, 255, 0.5);">
                                        <span>3</span>
                                    </div>
                                    <div class="w-0.5 flex-1 mt-1" style="min-height: 20px; background: rgba(255, 255, 255, 0.15);"></div>
                                </div>
                                <div class="pb-4">
                                    <p class="text-sm font-semibold leading-none mb-0.5" style="color: rgba(255, 255, 255, 0.4);">系统监测</p>
                                    <p class="text-xs leading-tight" style="color: rgba(255, 255, 255, 0.3);"> 监测系统环境是否支持</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold flex-shrink-0 transition-all duration-1000" style="background: rgba(255, 255, 255, 0.15); color: rgba(255, 255, 255, 0.5);">
                                        <span>4</span>
                                    </div>
                                    <div class="w-0.5 flex-1 mt-1" style="min-height: 20px; background: rgba(255, 255, 255, 0.15);"></div>
                                </div>
                                <div class="pb-4">
                                    <p class="text-sm font-semibold leading-none mb-0.5" style="color: rgba(255, 255, 255, 0.4);">数据库配置</p>
                                    <p class="text-xs leading-tight" style="color: rgba(255, 255, 255, 0.3);"> 配置数据库连接信息</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold flex-shrink-0 transition-all duration-1000" style="background: rgba(255, 255, 255, 0.15); color: rgba(255, 255, 255, 0.5);">
                                        <span>5</span>
                                    </div>
                                    <div class="w-0.5 flex-1 mt-1" style="min-height: 20px; background: rgba(255, 255, 255, 0.15);"></div>
                                </div>
                                <div class="pb-4">
                                    <p class="text-sm font-semibold leading-none mb-0.5" style="color: rgba(255, 255, 255, 0.4);">站点配置</p>
                                    <p class="text-xs leading-tight" style="color: rgba(255, 255, 255, 0.3);"> 网站信息与管理员</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold flex-shrink-0 transition-all duration-1000" style="background: rgba(255, 255, 255, 0.15); color: rgba(255, 255, 255, 0.5);">
                                        <span>6</span>
                                    </div>
                                    <div class="w-0.5 flex-1 mt-1" style="min-height: 20px; background: rgba(255, 255, 255, 0.15);"></div>
                                </div>
                                <div class="pb-4">
                                    <p class="text-sm font-semibold leading-none mb-0.5" style="color: rgba(255, 255, 255, 0.4);">等待安装</p>
                                    <p class="text-xs leading-tight" style="color: rgba(255, 255, 255, 0.3);"> 选择模式并等待安装</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold flex-shrink-0 transition-all duration-1000" style="background: rgba(255, 255, 255, 0.15); color: rgba(255, 255, 255, 0.5);">
                                        <span>7</span>
                                    </div>
                                </div>
                                <div class="pb-4">
                                    <p class="text-sm font-semibold leading-none mb-0.5" style="color: rgba(255, 255, 255, 0.4);">完成</p>
                                    <p class="text-xs leading-tight" style="color: rgba(255, 255, 255, 0.3);"> 安装成功</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex md:hidden items-center justify-center gap-2 mb-2">
                        <div class="rounded-full transition-all duration-300" style="width: 20px; height: 8px; background: rgb(255, 255, 255);"></div>
                        <div class="rounded-full transition-all duration-300" style="width: 8px; height: 8px; background: rgba(255, 255, 255, 0.25);"></div>
                        <div class="rounded-full transition-all duration-300" style="width: 8px; height: 8px; background: rgba(255, 255, 255, 0.25);"></div>
                        <div class="rounded-full transition-all duration-300" style="width: 8px; height: 8px; background: rgba(255, 255, 255, 0.25);"></div>
                        <div class="rounded-full transition-all duration-300" style="width: 8px; height: 8px; background: rgba(255, 255, 255, 0.25);"></div>
                        <div class="rounded-full transition-all duration-300" style="width: 8px; height: 8px; background: rgba(255, 255, 255, 0.25);"></div>
                        <div class="rounded-full transition-all duration-300" style="width: 8px; height: 8px; background: rgba(255, 255, 255, 0.25);"></div>
                    </div>
                    <p class="text-center text-xs md:hidden progress-text-info" style="color: rgba(255, 255, 255, 0.6);">步骤 1 / 7</p>
                    <div class="hidden md:block mt-auto pt-8">
                        <div class="rounded-xl p-3 text-xs" style="background: rgba(255, 255, 255, 0.08); color: rgba(255, 255, 255, 0.55);">
                            <p class="font-semibold text-primary-foreground mb-1">版权&联系方式</p>
                            <p>官网：<a target="_blank" href="https://mf.mengnai.top/">mf.mengnai.top</a></p>
                            <p>QQ群：994752422</p>
                            <p>采用 MIT 开源协议发布</p>
                            <p>©2026 梦奈</p>
                        </div>
                    </div>
                </div>
                <div class="flex-1 p-6 md:p-10 flex flex-col overflow-hidden">
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-1.5 progress-text">
                            <span class="text-xs font-semibold text-muted-foreground">步骤 1 / 7</span>
                            <span class="text-xs font-semibold text-primary">0%</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-secondary overflow-hidden">
                            <div class="h-full rounded-full progress-bar-anim" style="width: 0; background: var(--primary);"></div>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto overflow-x-hidden">
                        <div data-cmp="WelcomeStep" class="step-fade-in flex flex-col h-full">

                            <div id="content-page-main">

                                <!--介绍-->
                                <div>
                                    <div class="flex items-center gap-4 mb-6">
                                        <div class="w-14 h-14 rounded-2xl bg-primary flex items-center justify-center shadow-custom flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rocket text-primary-foreground" aria-hidden="true">
                                                <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path>
                                                <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path>
                                                <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path>
                                                <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h2 class="text-2xl font-bold text-foreground leading-tight">欢迎使用 梦奈宝塔主机系统</h2>
                                            <p class="text-muted-foreground text-sm mt-0.5">版本 V<span class="mn-vs">1.79</span> &middot; 约需 2 分钟</p>
                                        </div>
                                    </div>
                                    <p class="text-foreground text-sm leading-relaxed mb-6">
                                        欢迎使用由梦奈基于光年V4框架原创的MN宝塔主机系统(简称MNBT)！本系统免费发布于网络！<br/>
                                        <b>官网：<a target="_blank" href="https://mf.mengnai.top/">mf.mengnai.top</a></b><br/>
                                        <b>QQ群：994752422</b>
                                    </p>

                                    <div class="tips-er tips-type-warning d-none mn-install-lock">
                                        <span>您已经安装了本系统，如需重新安装则请删除掉install目录下的install.lock文件，然后刷新本页面</span>
                                    </div>
                                </div>

                                <!--协议-->
                                <div>
                                    <div data-cmp="LicenseStep" class="step-fade-in flex flex-col h-full">
                                        <div class="flex items-center gap-3 mb-5">
                                            <div class="w-10 h-10 rounded-xl bg-secondary flex items-center justify-center flex-shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text text-primary" aria-hidden="true">
                                                    <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
                                                    <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                                                    <path d="M10 9H8"></path>
                                                    <path d="M16 13H8"></path>
                                                    <path d="M16 17H8"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h2 class="text-xl font-bold text-foreground">许可协议</h2>
                                                <p class="text-muted-foreground text-xs">请阅读并接受以下条款</p>
                                            </div>
                                        </div>
                                        <div class="flex-1 border border-border rounded-xl p-4 overflow-y-auto text-xs text-muted-foreground leading-relaxed mb-4 bg-muted" style="min-height: 180px; max-height: 260px;">
                                            <iframe src="/xy.html" class="whitespace-pre-wrap font-sans">

                                            </iframe>
                                        </div>
                                        <div class="flex items-start gap-3 p-3 rounded-xl border mb-6 cursor-pointer transition-all duration-200 use-terms must" style="border-color: var(--border); background: var(--card); user-select: none;">
                                            <div class="w-5 h-5 rounded flex items-center justify-center flex-shrink-0 mt-0.5 transition-all duration-200 border-2" style="background: transparent; border-color: var(--border);">
                                                <svg style="display: none;" width="11" height="9" viewBox="0 0 11 9" fill="none"><path d="M1 4L4 7L10 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-foreground">我已阅读并同意上述许可协议的所有条款</p>
                                                <p class="text-xs text-muted-foreground mt-0.5">继续安装即表示您接受本协议</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!--系统环境监测-->
                                <div>

                                    <div data-cmp="LicenseStep" class="step-fade-in flex flex-col h-full">
                                        <div class="flex items-center gap-3 mb-5">
                                            <div class="w-10 h-10 rounded-xl bg-secondary flex items-center justify-center flex-shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text text-primary" aria-hidden="true">
                                                    <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
                                                    <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                                                    <path d="M10 9H8"></path>
                                                    <path d="M16 13H8"></path>
                                                    <path d="M16 17H8"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h2 class="text-xl font-bold text-foreground">系统环境监测</h2>
                                                <p class="text-muted-foreground text-xs">监测系统环境是否支持</p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-2 mb-8 install-system-info">
                                            <div class="flex items-start gap-3 p-3 rounded-xl bg-accent border border-border php_vs">
                                                <div class="w-8 h-8 rounded-lg bg-secondary flex items-center justify-center flex-shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield text-primary" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path></svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-foreground">PHP</p>
                                                    <p class="text-xs text-muted-foreground mt-0.5">需要 PHP 7.4 及以上</p>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-3 p-3 rounded-xl bg-accent border border-border curl_exec">
                                                <div class="w-8 h-8 rounded-lg bg-secondary flex items-center justify-center flex-shrink-0">
                                                    <svg  xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield text-primary" aria-hidden="true">
                                                        <path d="M8 3H7a2 2 0 0 0-2 2v5a2 2 0 0 1-2 2 2 2 0 0 1 2 2v5c0 1.1.9 2 2 2h1" />
                                                        <path d="M16 21h1a2 2 0 0 0 2-2v-5c0-1.1.9-2 2-2a2 2 0 0 1-2-2V5a2 2 0 0 0-2-2h-1" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-foreground">curl_exec</p>
                                                    <p class="text-xs text-muted-foreground mt-0.5">用于与宝塔API通信</p>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-3 p-3 rounded-xl bg-accent border border-border mn_link">
                                                <div class="w-8 h-8 rounded-lg bg-secondary flex items-center justify-center flex-shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe text-primary" aria-hidden="true">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                                                        <path d="M2 12h20"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-foreground">MNBT更新支持</p>
                                                    <p class="text-xs text-muted-foreground mt-0.5">用户在线升级系统至最新版本</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!--数据库配置信息填写-->
                                <div>

                                    <div data-cmp="InstallPathStep" class="step-fade-in flex flex-col h-full">
                                        <div class="flex items-center gap-3 mb-5">
                                            <div class="w-10 h-10 rounded-xl bg-secondary flex items-center justify-center flex-shrink-0">

                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-folder-open text-primary" aria-hidden="true">
                                                    <path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83z"></path><path d="M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 12"></path><path d="M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 17"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h2 class="text-xl font-bold text-foreground">数据库配置</h2>
                                                <p class="text-muted-foreground text-xs">请填写数据库配置信息</p>
                                            </div>
                                        </div>
                                        <form class="form-database">
                                            <div class="mb-4">
                                                <label class="text-xs font-semibold text-foreground mb-1.5 block">数据库地址</label>
                                                <div class="flex gap-2">
                                                    <input id="db_host" type="text" class="input-field flex-1 text-sm" placeholder="数据库连接地址，例如127.0.0.1" value="localhost" required/>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label class="text-xs font-semibold text-foreground mb-1.5 block">数据库端口</label>
                                                <div class="flex gap-2">
                                                    <input id="db_port" type="text" class="input-field flex-1 text-sm" placeholder="数据库端口，例如3306" value="3306" required/>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label class="text-xs font-semibold text-foreground mb-1.5 block">数据库用户名</label>
                                                <div class="flex gap-2">
                                                    <input id="db_user" type="text" class="input-field flex-1 text-sm" placeholder="数据库用户名" value="" required/>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label class="text-xs font-semibold text-foreground mb-1.5 block">数据库名</label>
                                                <div class="flex gap-2">
                                                    <input id="db_name" type="text" class="input-field flex-1 text-sm" placeholder="数据库名" value="" required/>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label class="text-xs font-semibold text-foreground mb-1.5 block">数据库密码</label>
                                                <div class="flex gap-2">
                                                    <input id="db_pwd" type="text" class="input-field flex-1 text-sm" placeholder="数据库密码" value="" required/>
                                                </div>
                                            </div>
                                        </form>

                                    </div>

                                </div>

                                <!--站点与管理员配置-->
                                <div>
                                    <div data-cmp="SiteConfigStep" class="step-fade-in flex flex-col h-full">
                                        <div class="flex items-center gap-3 mb-5">
                                            <div class="w-10 h-10 rounded-xl bg-secondary flex items-center justify-center flex-shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings text-primary" aria-hidden="true">
                                                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </div>
                                            <div>
                                                <h2 class="text-xl font-bold text-foreground">站点与管理员</h2>
                                                <p class="text-muted-foreground text-xs">设置网站信息与后台登录账号</p>
                                            </div>
                                        </div>
                                        <form class="form-site">
                                            <div class="mb-4">
                                                <label class="text-xs font-semibold text-foreground mb-1.5 block">控制面板名称</label>
                                                <input id="site_name" type="text" class="input-field w-full text-sm" placeholder="例如：梦奈主机控制面板" value="MNBT 控制面板" required maxlength="80"/>
                                            </div>
                                            <div class="mb-4">
                                                <label class="text-xs font-semibold text-foreground mb-1.5 block">站长 QQ</label>
                                                <input id="site_qq" type="text" class="input-field w-full text-sm" placeholder="选填，用于用户联系" value="" maxlength="20"/>
                                            </div>
                                            <div class="mb-4">
                                                <label class="text-xs font-semibold text-foreground mb-1.5 block">网站公告（选填）</label>
                                                <textarea id="site_gg" class="input-field w-full text-sm" rows="2" placeholder="安装后显示在前台/后台的公告" maxlength="2000"></textarea>
                                            </div>
                                            <div class="mb-4">
                                                <label class="text-xs font-semibold text-foreground mb-1.5 block">管理员账号</label>
                                                <input id="admin_user" type="text" class="input-field w-full text-sm" placeholder="后台登录用户名" value="admin" required maxlength="50"/>
                                            </div>
                                            <div class="mb-4">
                                                <label class="text-xs font-semibold text-foreground mb-1.5 block">管理员密码</label>
                                                <input id="admin_pwd" type="password" class="input-field w-full text-sm" placeholder="至少 6 位" value="" required minlength="6" maxlength="64"/>
                                            </div>
                                            <div class="mb-4">
                                                <label class="text-xs font-semibold text-foreground mb-1.5 block">确认密码</label>
                                                <input id="admin_pwd2" type="password" class="input-field w-full text-sm" placeholder="再次输入密码" value="" required minlength="6" maxlength="64"/>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!--安装确认-->
                                <div>

                                    <div data-cmp="InstallPathStep" class="step-fade-in flex flex-col h-full">
                                        <div class="flex items-center gap-3 mb-5">
                                            <div class="w-10 h-10 rounded-xl bg-secondary flex items-center justify-center flex-shrink-0">

                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-folder-open text-primary" aria-hidden="true">
                                                    <path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83z"></path><path d="M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 12"></path><path d="M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 17"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h2 class="text-xl font-bold text-foreground">安装模式选择</h2>
                                                <p class="text-muted-foreground text-xs">选择模式或点击下一步</p>
                                            </div>
                                        </div>
                                        <div class="tips-er tips-type-blue">
                                            <span>数据库与站点信息已就绪，点击完成开始安装</span>
                                        </div>
                                        <div class="new-install-select">
                                            <div class="tips-er tips-type-warning">
                                                <span>检测到您已安装过梦奈宝塔主机系统，请查看以下选项</span>
                                            </div>
                                            <div class="install-type-btn">
                                                <div class="flex items-start gap-3 p-3 rounded-xl border mb-6 cursor-pointer transition-all duration-200 use-terms mn-new-install" style="border-color: var(--border); background: var(--card); user-select: none;">
                                                    <div class="w-5 h-5 rounded flex items-center justify-center flex-shrink-0 mt-0.5 transition-all duration-200 border-2" style="background: transparent; border-color: var(--border);">
                                                        <svg style="display: none;" width="11" height="9" viewBox="0 0 11 9" fill="none"><path d="M1 4L4 7L10 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-foreground">确认进行重装</p>
                                                        <p class="text-xs text-muted-foreground mt-0.5">勾选则表示您强制进行重装（将会清空所有旧数据）</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <!--安装完成-->
                                <div>
                                    <div class="flex-1 overflow-y-auto">
                                        <div data-cmp="CompleteStep" class="step-fade-in flex flex-col h-full items-center">
                                            <div class="flex flex-col items-center mb-6 pt-2">
                                                <div class="w-20 h-20 rounded-full flex items-center justify-center mb-4" style="background: rgba(102, 219, 177, 0.15); border: 3px solid var(--chart-3);">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-big" aria-hidden="true" style="color: var(--chart-3);">
                                                        <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                                        <path d="m9 11 3 3L22 4"></path>
                                                    </svg>
                                                </div>
                                                <h2 class="text-2xl font-bold text-foreground mb-1">安装成功！</h2>
                                                <p class="text-muted-foreground text-sm text-center">梦奈宝塔主机系统已安装到您的站点中</p>
                                                <p class="text-muted-foreground text-sm text-center">请妥善保存管理员账号；为安全建议删除站点下的 install 目录</p>
                                            </div>
                                            <div class="w-full mb-4 p-4 rounded-xl border install-admin-box" style="background: var(--secondary); border-color: var(--border);">
                                                <p class="text-sm font-semibold text-foreground mb-2">管理员登录信息</p>
                                                <p class="text-xs text-muted-foreground mb-1">后台地址：域名/admin</p>
                                                <p class="text-sm text-foreground">账号：<code class="install-admin-user">admin</code></p>
                                                <p class="text-sm text-foreground">密码：<code class="install-admin-pwd">（安装时设置）</code></p>
                                                <p class="text-xs text-muted-foreground mt-2">控制面板名称：<span class="install-site-name">—</span></p>
                                            </div>
                                            <div class="w-full flex flex-col gap-2 mb-6 cursor-pointer-button">
                                                <a href="../admin" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-xl border text-left transition-all duration-200 w-full" style="background: var(--primary); border-color: var(--primary);">
                                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(255, 255, 255, 0.2);">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link text-primary-foreground" aria-hidden="true">
                                                            <path d="M15 3h6v6"></path>
                                                            <path d="M10 14 21 3"></path>
                                                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-semibold" style="color: var(--primary-foreground);">访问应用后台</p>
                                                        <p class="text-xs" style="color: rgba(255, 255, 255, 0.7);">使用上方账号登录</p>
                                                    </div>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link ml-auto flex-shrink-0" aria-hidden="true" style="color: rgba(255, 255, 255, 0.5);">
                                                        <path d="M15 3h6v6"></path>
                                                        <path d="M10 14 21 3"></path>
                                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                    </svg></a>

                                                <a href="../user" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-xl border text-left transition-all duration-200 w-full" style="background: var(--card); border-color: var(--border);">
                                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background: var(--secondary);">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle text-primary" aria-hidden="true">
                                                            <path d="M15 3h6v6"></path>
                                                            <path d="M10 14 21 3"></path>
                                                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-semibold" style="color: var(--foreground);">访问主机控制面板</p>
                                                        <p class="text-xs" style="color: var(--muted-foreground);">默认主机控制面板地址：域名/user</p>
                                                    </div>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link ml-auto flex-shrink-0" aria-hidden="true" style="color: var(--muted-foreground);">
                                                        <path d="M15 3h6v6"></path>
                                                        <path d="M10 14 21 3"></path>
                                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                    </svg></a>

                                                <a href="http://mf.mengnai.top/" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-xl border text-left transition-all duration-200 w-full" style="background: var(--card); border-color: var(--border);">
                                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background: var(--secondary);">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe text-primary" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-semibold" style="color: var(--foreground);">访问官网</p>
                                                        <p class="text-xs" style="color: var(--muted-foreground);">访问梦奈宝塔主机系统官方网站</p>
                                                    </div>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link ml-auto flex-shrink-0" aria-hidden="true" style="color: var(--muted-foreground);">
                                                        <path d="M15 3h6v6"></path>
                                                        <path d="M10 14 21 3"></path>
                                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                    </svg></a>

                                                <a href="http://wpa.qq.com/msgrd?v=3&uin=994752422&site=qq&menu=yes" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-xl border text-left transition-all duration-200 w-full" style="background: var(--card); border-color: var(--border);">
                                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background: var(--secondary);">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle text-primary" aria-hidden="true">
                                                            <path d="M2.992 16.342a2 2 0 0 1 .094 1.167l-1.065 3.29a1 1 0 0 0 1.236 1.168l3.413-.998a2 2 0 0 1 1.099.092 10 10 0 1 0-4.777-4.719"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-semibold" style="color: var(--foreground);">加入QQ交流群</p>
                                                        <p class="text-xs" style="color: var(--muted-foreground);">与数千名用户交流分享</p>
                                                    </div>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link ml-auto flex-shrink-0" aria-hidden="true" style="color: var(--muted-foreground);">
                                                        <path d="M15 3h6v6"></path>
                                                        <path d="M10 14 21 3"></path>
                                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                    </svg></a>

                                            </div>
                                        </div>
                                    </div>
                                </div>



                            </div>
                            <div class="mt-auto flex justify-between button-group">
                                <div class="flex items-center gap-2 text-xs mb-4 next-tips" style="color: var(--destructive); width: 55%">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-alert" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" x2="12" y1="8" y2="12"></line>
                                        <line x1="12" x2="12.01" y1="16" y2="16"></line>
                                    </svg>
                                    <span>您必须填写完整后才能继续安装</span>
                                </div>
                                <button class="btn-primary" id="btn-install-next">开始安装 →</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
        //展示页面
        $('body.d-none').removeClass('d-none');
        let info=await request('index');
        $('.mn-vs').html(info.data.vs);
        if(info.data.is_install){
            $('.mn-install-lock').removeClass('d-none');
            NEXT_BTN_FUN.loading('您已安装！');
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
            if(result.data.in_table)$('.new-install-select').removeClass('d-none');
            else $('.new-install-select').addClass('d-none');
        }else if(curr_index===5){
            let siteCheck=validateSiteForm(true);
            if(!siteCheck.ok){
                NEXT_BTN_FUN.removeDisabled();
                return NEXT_BTN_FUN.TipsShow(siteCheck.msg);
            }
            siteConfigCache=siteCheck.data;
        }else if(curr_index===6){
            NEXT_BTN_FUN.loading('安装中....');
            let payload=Object.assign({
                is_install:$('.new-install-select').hasClass('d-none') || $('.mn-new-install.use-terms').hasClass('terms-yes')
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
        if (curr_index===6)NEXT_BTN_FUN.removeDisabled();
        else if (curr_index===5){
            NEXT_BTN_FUN.TipsShow('请填写站点信息与管理员账号');
            checkSiteFormValidation();
        }
        else if (curr_index===4)NEXT_BTN_FUN.TipsShow('您必须将表单填写完整后才能继续安装，表单可下滑');
        else if (curr_index===3)await systemCheck();
        else if (curr_index===2) NEXT_BTN_FUN.TipsShow('您必须同意许可协议才能继续安装');
    });


    //进入到下一页
    const next=(index)=>{
        let currDom=$('#content-page-main>div.page-active');
        let nextDom=$(`#content-page-main>div:eq(${index})`);
        currDom.css('animation-name','exit');
        nextDom.css('animation-name','entry');
        nextDom.addClass('page-active');
    }

    const request=async (action,data={})=>{
        try{
            return $.ajax({
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
        }
    }

    //协议相关监听
    $('.use-terms').on('click', function() {
        let thisClass=$(this);
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