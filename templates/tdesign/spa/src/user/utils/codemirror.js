/**
 * CodeMirror 动态加载（文件在线编辑）
 * 资源在 imsetes/codemirror/（全站共用同一份），首次打开编辑器时按需载入。
 * window.__TD_BOOT__.assetBase 为 imsetes/ 的 URL 前缀。
 */

let cmPromise = null

function loadCss(href) {
  return new Promise((resolve, reject) => {
    if (document.querySelector(`link[href="${href}"]`)) {
      resolve()
      return
    }
    const el = document.createElement('link')
    el.rel = 'stylesheet'
    el.href = href
    el.onload = () => resolve()
    el.onerror = () => reject(new Error('CSS 加载失败: ' + href))
    document.head.appendChild(el)
  })
}

function loadScript(src) {
  return new Promise((resolve, reject) => {
    if (document.querySelector(`script[src="${src}"]`)) {
      resolve()
      return
    }
    const el = document.createElement('script')
    el.src = src
    el.onload = () => resolve()
    el.onerror = () => reject(new Error('JS 加载失败: ' + src))
    document.head.appendChild(el)
  })
}

export function assetBase() {
  const boot = window.__TD_BOOT__ || {}
  if (boot.assetBase) return String(boot.assetBase)
  if (boot.vendorBase) return String(boot.vendorBase).replace(/vendor\/?$/, '')
  return '../imsetes/'
}

export function loadCodeMirror() {
  if (cmPromise) return cmPromise
  cmPromise = (async () => {
    const base = assetBase()
    await loadCss(base + 'codemirror/lib/codemirror.css')
    await loadCss(base + 'codemirror/addon/dialog/dialog.css')
    await loadScript(base + 'codemirror/lib/codemirror.js')
    const modes = [
      'clike/clike',
      'javascript/javascript',
      'xml/xml',
      'css/css',
      'htmlmixed/htmlmixed',
      'sql/sql',
      'php/php',
    ]
    for (const m of modes) {
      await loadScript(`${base}codemirror/mode/${m}.js`)
    }
    const addons = [
      'selection/active-line',
      'edit/matchbrackets',
      'display/autorefresh',
      'edit/closebrackets',
      'search/search',
      'search/searchcursor',
      'search/jump-to-line',
      'dialog/dialog',
    ]
    for (const a of addons) {
      await loadScript(`${base}codemirror/addon/${a}.js`)
    }
    if (!window.CodeMirror) throw new Error('CodeMirror 初始化失败')
    return window.CodeMirror
  })()
  cmPromise.catch(() => {
    cmPromise = null
  })
  return cmPromise
}

export function cmThemeCss(theme) {
  return assetBase() + 'codemirror/theme/' + theme + '.css'
}
