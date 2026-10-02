/*
 * MNBT TDesign 主题 —— vue3-sfc-loader 免构建引导（V1.87）
 *
 * 各端 _spa_boot.php 先注入 window.__TD_BOOT__（含 themeBase / vendorBase / scope），
 * 再按顺序加载 vendor UMD（vue → vue-router → axios → tdesign → [echarts] → vue3-sfc-loader），
 * 最后加载本文件。本文件把 src/ 下的 .vue/.js 源码在浏览器内编译运行，
 * 取代原 Vite 构建链：无需 npm install / build，修改 src 后刷新页面即生效。
 */
(function () {
    'use strict';

    var boot = window.__TD_BOOT__ || {};
    var scope = boot.scope || 'user';
    var themeBase = boot.themeBase || '../templates/tdesign/';
    var srcBase = themeBase + 'spa/src/';

    var statusEl = document.getElementById('td-boot-status');

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
        });
    }

    function bootFail(title, detail) {
        if (!statusEl) return;
        statusEl.className = 'td-boot-msg td-boot-error';
        statusEl.innerHTML = '<h2>' + esc(title) + '</h2><pre>' + esc(String(detail || '')).slice(0, 3000) + '</pre>';
    }

    function setStatus(text) {
        if (statusEl) statusEl.innerHTML = '<p>' + esc(text) + '</p>';
    }

    // ------------------------------------------------------------------
    //  UMD 全局 → ES 模块桥接（补 default 导出，兼容 import X from '...'）
    // ------------------------------------------------------------------
    var Vue = window.Vue || {};
    var VueRouter = window.VueRouter || {};
    var TDesign = window.TDesign || {};
    var axiosMod = window.axios || {};
    var echartsMod = window.echarts || {};
    if (!Vue.default) Vue.default = Vue;
    if (!VueRouter.default) VueRouter.default = VueRouter;
    if (!TDesign.default) TDesign.default = TDesign;
    if (!axiosMod.default) axiosMod.default = axiosMod;
    if (!echartsMod.default) echartsMod.default = echartsMod;

    // 无原型对象：loader 会接管并写入加载过的模块
    var moduleCache = Object.assign(Object.create(null), {
        'vue': Vue,
        'vue-router': VueRouter,
        'tdesign-vue-next': TDesign,
        'axios': axiosMod,
        'echarts': echartsMod,
        // 防御映射：src 内不再直接引用这些子路径，避免旧代码 404
        'echarts/core': echartsMod,
        'echarts/charts': echartsMod,
        'echarts/components': echartsMod,
        'echarts/renderers': echartsMod,
        'tdesign-vue-next/es/style/index.css': {}
    });

    // ------------------------------------------------------------------
    //  路径解析：@/ → src/；相对路径基于引用方所在目录折叠 ../
    // ------------------------------------------------------------------
    function normPath(p) {
        var out = [];
        var parts = String(p).split('/');
        for (var i = 0; i < parts.length; i++) {
            var seg = parts[i];
            if (seg === '' || seg === '.') {
                if (i === 0 && seg === '') out.push('');
                continue;
            }
            if (seg === '..') {
                if (out.length && out[out.length - 1] !== '' && out[out.length - 1] !== '..' && out[out.length - 1].indexOf(':') === -1) out.pop();
                else if (out.length === 0 || out[out.length - 1] === '..') out.push('..');
                continue;
            }
            out.push(seg);
        }
        return out.join('/');
    }

    function pathResolve(pathCx) {
        var relPath = String(pathCx.relPath || '');
        var refPath = pathCx.refPath ? String(pathCx.refPath) : '';
        var isRelative = relPath === '.' || relPath === '..' || relPath.indexOf('./') === 0 || relPath.indexOf('../') === 0;
        if (isRelative) {
            var baseDir = refPath ? refPath.replace(/[^/]*$/, '') : srcBase;
            return normPath(baseDir + relPath);
        }
        if (relPath.indexOf('@/') === 0) {
            return normPath(srcBase + relPath.slice(2));
        }
        // 裸模块名（vue / axios 等）原样返回，命中 moduleCache
        return relPath;
    }

    // ------------------------------------------------------------------
    //  文件获取：无扩展名导入按 node 顺序探测；结果按 URL 记忆
    // ------------------------------------------------------------------
    var CANDIDATES = ['', '.js', '/index.js', '.vue', '.css', '.json'];
    var filePromises = Object.create(null);

    function fetchFile(path) {
        if (filePromises[path] === undefined) {
            filePromises[path] = fetch(new URL(path, window.location.href))
                .then(function (res) {
                    if (!res.ok) {
                        var err = new Error('HTTP ' + res.status + ' ' + path);
                        err.status = res.status;
                        throw err;
                    }
                    return res;
                });
        }
        return filePromises[path];
    }

    function extOf(path) {
        var m = /\.(vue|js|mjs|css|json|svg|png|jpe?g|gif|webp|txt)$/i.exec(path);
        return m ? '.' + m[1].toLowerCase() : '';
    }

    var options = {
        moduleCache: moduleCache,
        pathResolve: pathResolve,

        // 图片等静态资产：import bg from '@/shared/assets/x.webp' → 返回 URL 字符串
        handleModule: function (type, getContentData, path) {
            if (['.webp', '.jpg', '.jpeg', '.png', '.gif', '.svg', '.ico'].indexOf(type) !== -1) {
                return Promise.resolve({ default: new URL(String(path), window.location.href).href });
            }
            return Promise.resolve(null);
        },

        getFile: function (path) {
            var base = String(path);
            var attempt = 0;

            function tryNext() {
                if (attempt >= CANDIDATES.length) {
                    return Promise.reject(new Error('模块不存在: ' + base));
                }
                var p = base + CANDIDATES[attempt++];
                return fetchFile(p).then(function (res) {
                    return {
                        path: p,
                        type: extOf(p) || '.js',
                        getContentData: function (asBinary) {
                            return asBinary ? res.arrayBuffer() : res.text();
                        }
                    };
                }).catch(function (e) {
                    if (e.status === 404) return tryNext();
                    throw e;
                });
            }

            return tryNext();
        },

        addStyle: function (style) {
            var el = document.createElement('style');
            el.textContent = String(style || '');
            document.head.appendChild(el);
        },

        log: function (type) {
            var args = Array.prototype.slice.call(arguments, 1);
            var fn = type === 'error' ? 'error' : (type === 'warn' ? 'warn' : 'log');
            console[fn].apply(console, ['[td-loader]'].concat(args));
        }
    };

    // ------------------------------------------------------------------
    //  启动
    // ------------------------------------------------------------------
    var loadModule = window['vue3-sfc-loader'] && window['vue3-sfc-loader'].loadModule;
    if (!loadModule) {
        bootFail('加载器缺失', '未找到 vue3-sfc-loader，请检查 imsetes/vendor/vue3-sfc-loader/ 目录是否完整。');
        return;
    }

    var entry = normPath(srcBase + 'main-' + scope + '.js');
    setStatus('正在加载应用模块…');

    loadModule(entry, options).then(function () {
        if (statusEl && statusEl.parentNode) statusEl.parentNode.removeChild(statusEl);
    }).catch(function (e) {
        console.error('[td-boot] 应用加载失败:', e);
        bootFail('应用加载失败', (e && (e.message || e.msg)) || e);
    });
})();
