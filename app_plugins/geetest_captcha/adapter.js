/**
 * 极验行为验证 4.0 适配器（MNBT 人机验证契约）
 *
 * 契约：window.MNBT_CAPTCHA_ADAPTER = { mount(el, { onSuccess, onFail }), reset() }
 *  - mount：在容器内渲染极验官方验证按钮（product=popup，appendTo），用户点击官方按钮
 *           弹出验证窗口；成功后把 getValidate() 结果 JSON 串交给 onSuccess
 *           （作为 captchaToken 提交登录接口）
 *  - reset：销毁当前实例（下次 mount 重新初始化，保证验证结果一次性）
 *
 * captchaId 从 window.__TD_BOOT__.captcha.captchaId 读取（由插件经 spa.boot 过滤器注入）。
 * 协议参考：https://docs.geetest.com/gt4/deploy/client/web
 */
(function () {
  'use strict'

  const GT4_SRC = 'https://static.geetest.com/v4/gt4.js'

  const boot = window.__TD_BOOT__ || {}
  const cfg = boot.captcha || {}
  const captchaId = String(cfg.captchaId || '')

  let captchaObj = null
  let gt4Promise = null

  function loadGt4() {
    if (window.initGeetest4) return Promise.resolve()
    if (!gt4Promise) {
      gt4Promise = new Promise((resolve, reject) => {
        const s = document.createElement('script')
        s.src = GT4_SRC
        s.onload = resolve
        s.onerror = () => {
          gt4Promise = null
          reject(new Error('gt4.js 加载失败'))
        }
        document.head.appendChild(s)
      })
    }
    return gt4Promise
  }

  function destroy() {
    try {
      if (captchaObj && typeof captchaObj.destroy === 'function') {
        captchaObj.destroy()
      }
    } catch (e) { /* 忽略销毁异常 */ }
    captchaObj = null
  }

  window.MNBT_CAPTCHA_ADAPTER = {
    async mount(el, cbs) {
      if (!captchaId) {
        throw new Error('极验 captchaId 未配置')
      }
      const callbacks = cbs || {}
      if (typeof el === 'string') {
        el = document.querySelector(el)
      }
      if (!el) {
        throw new Error('验证码容器不存在')
      }
      el.innerHTML = ''
      destroy()
      await loadGt4()
      await new Promise((resolve, reject) => {
        window.initGeetest4(
          {
            captchaId: captchaId,
            product: 'popup', // 官方验证按钮，appendTo 渲染，点击弹出验证窗口
          },
          function (obj) {
            captchaObj = obj
            obj.appendTo(el)
            obj.onSuccess(function () {
              const result = obj.getValidate()
              if (!result) return
              callbacks.onSuccess &&
                callbacks.onSuccess(JSON.stringify(result))
            })
            obj.onFail(function () {
              callbacks.onFail && callbacks.onFail()
            })
            obj.onError(function (err) {
              ;(window.console || {}).warn &&
                console.warn('[geetest_captcha]', err && (err.msg || err.code))
              callbacks.onFail && callbacks.onFail()
            })
            resolve()
          }
        )
      })
    },
    reset() {
      destroy()
    },
  }
})()

