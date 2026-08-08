/* qmzl_domain 插件共享 JS 工具 */
window.QZ = (function () {
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function toast(msg, type) {
    type = type || 'info';
    var old = document.querySelector('.qz-toast');
    if (old) old.remove();
    var el = document.createElement('div');
    el.className = 'qz-toast qz-toast--' + type;
    el.textContent = msg;
    document.body.appendChild(el);
    requestAnimationFrame(function () { el.classList.add('show'); });
    setTimeout(function () { el.classList.remove('show'); setTimeout(function () { el.remove(); }, 300); }, 2600);
  }

  /** POST 到指定路由 URL（自动带 CSRF），成功回调 res，失败回调 (msg) */
  function post(url, params, ok, fail) {
    params = params || {};
    $.ajax({
      url: url,
      type: 'POST',
      data: params,
      dataType: 'json',
      timeout: 90000
    }).done(function (res) {
      if (res && (res.qk === 1 || res.success === true)) {
        if (typeof ok === 'function') ok(res);
      } else {
        if (res && res.login_url) { window.location.href = res.login_url; return; }
        if (typeof fail === 'function') fail((res && res.msg) || '请求失败');
        else toast((res && res.msg) || '请求失败', 'error');
      }
    }).fail(function (xhr) {
      var msg = '网络错误，请稍后重试';
      try {
        var j = xhr.responseJSON;
        if (j && j.msg) msg = j.msg;
        if (j && j.login_url) { window.location.href = j.login_url; return; }
      } catch (e) {}
      if (typeof fail === 'function') fail(msg);
      else toast(msg, 'error');
    });
  }

  /** FormData POST（文件上传），成功回调 res，失败回调 (msg) */
  function postForm(url, formData, ok, fail) {
    formData = formData || new FormData();
    $.ajax({
      url: url,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      timeout: 120000
    }).done(function (res) {
      if (res && (res.qk === 1 || res.success === true)) {
        if (typeof ok === 'function') ok(res);
      } else {
        if (res && res.login_url) { window.location.href = res.login_url; return; }
        if (typeof fail === 'function') fail((res && res.msg) || '请求失败');
        else toast((res && res.msg) || '请求失败', 'error');
      }
    }).fail(function (xhr) {
      var msg = '网络错误，请稍后重试';
      try {
        var j = xhr.responseJSON;
        if (j && j.msg) msg = j.msg;
        if (j && j.login_url) { window.location.href = j.login_url; return; }
      } catch (e) {}
      if (typeof fail === 'function') fail(msg);
      else toast(msg, 'error');
    });
  }

  function loading(show) {
    var el = document.getElementById('qz-loading');
    if (!el) {
      el = document.createElement('div');
      el.id = 'qz-loading';
      el.style.cssText = 'position:fixed;inset:0;background:rgba(255,255,255,.7);z-index:999;display:none;align-items:center;justify-content:center;';
      el.innerHTML = '<span style="font-size:14px;color:#6b7280;">处理中...</span>';
      document.body.appendChild(el);
    }
    el.style.display = show ? 'flex' : 'none';
  }

  return { esc: esc, toast: toast, post: post, postForm: postForm, loading: loading };
})();
