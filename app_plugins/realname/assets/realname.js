/* ============================================================
 * realname 插件 - 前端逻辑
 * 图片压缩 → 本地 OCR（tesseract.js，同源模型）→ 回填 → 提交
 * ============================================================ */
(function () {
  'use strict';

  var $ = function (id) { return document.getElementById(id); };
  var RN = window.RN_OCR_URL || '';

  /* ---------- 图片压缩（canvas，最长边 1280） ---------- */
  function compressImage(file) {
    return new Promise(function (resolve, reject) {
      var img = new Image();
      var url = URL.createObjectURL(file);
      img.onload = function () {
        var MAX = 1280;
        var w = img.width, h = img.height;
        if (w > MAX || h > MAX) {
          if (w >= h) { h = Math.round(h * MAX / w); w = MAX; }
          else { w = Math.round(w * MAX / h); h = MAX; }
        }
        var canvas = document.createElement('canvas');
        canvas.width = w; canvas.height = h;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, w, h);
        canvas.toBlob(function (blob) {
          URL.revokeObjectURL(url);
          if (blob) resolve(blob); else reject(new Error('compress failed'));
        }, 'image/jpeg', 0.85);
      };
      img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('image load failed')); };
      img.src = url;
    });
  }

  /* ---------- 上传框交互 ---------- */
  var boxes = document.querySelectorAll('.rn-upload-box');
  var files = { front: null, back: null, hand: null };

  function setPreview(box, type, blobOrUrl) {
    var img = box.querySelector('.rn-upload-preview');
    if (!img) return;
    if (blobOrUrl instanceof Blob) {
      img.src = URL.createObjectURL(blobOrUrl);
    } else {
      img.src = blobOrUrl;
    }
    box.classList.add('has-file');
  }

  boxes.forEach(function (box) {
    var input = box.querySelector('input[type="file"]');
    var type = input.getAttribute('data-type');
    var clear = box.querySelector('.rn-upload-clear');

    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      if (!file) return;
      if (file.size > 8 * 1024 * 1024) { alert('图片不能超过 8MB'); input.value = ''; return; }
      compressImage(file).then(function (blob) {
        files[type] = blob;
        setPreview(box, type, blob);
        // 正面图选择后触发 OCR
        if (type === 'front') runOcr(blob);
      }).catch(function () { alert('图片处理失败，请更换图片'); });
    });

    clear.addEventListener('click', function (e) {
      e.stopPropagation();
      files[type] = null;
      input.value = '';
      box.classList.remove('has-file');
    });
  });

  /* ---------- 本地 OCR（tesseract.js） ---------- */
  var ocrRunning = false;

  function loadOcrLib() {
    return new Promise(function (resolve, reject) {
      if (window.Tesseract) { resolve(window.Tesseract); return; }
      if (!RN) { reject(new Error('OCR 库路径未配置')); return; }
      var s = document.createElement('script');
      s.src = RN;
      s.onload = function () { window.Tesseract ? resolve(window.Tesseract) : reject(new Error('OCR 加载失败')); };
      s.onerror = function () { reject(new Error('OCR 库加载失败')); };
      document.head.appendChild(s);
    });
  }

  function idcardCheck(id) {
    if (!/^\d{17}[\dXx]$/.test(id)) return false;
    var w = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
    var m = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
    var s = 0;
    for (var i = 0; i < 17; i++) s += parseInt(id[i], 10) * w[i];
    return m[s % 11] === id[17].toUpperCase();
  }

  function extractIdCard(text) {
    var t = (text || '').replace(/\s+/g, '');
    var m = t.match(/\d{17}[\dXx]/g) || [];
    for (var i = 0; i < m.length; i++) {
      if (idcardCheck(m[i])) return m[i].toUpperCase();
    }
    return '';
  }

  function extractName(text) {
    var lines = (text || '').split('\n');
    for (var i = 0; i < lines.length; i++) {
      var t = lines[i].replace(/\s+/g, '');
      var m = t.match(/姓名[:：]?([\u4e00-\u9fa5·]{1,10})/);
      if (m && m[1]) return m[1];
      if (t.indexOf('姓名') >= 0) {
        var rest = t.replace(/姓名[:：]?/, '');
        if (/^[\u4e00-\u9fa5·]{1,10}$/.test(rest)) return rest;
      }
    }
    return '';
  }

  function runOcr(blob) {
    if (ocrRunning) return;
    var state = $('rnOcrState');
    var box = $('rnFrontBox');
    if (state) state.style.display = 'flex';
    ocrRunning = true;

    loadOcrLib().then(function (Tesseract) {
      var options = {
        logger: function () { /* 静默进度 */ }
      };
      if (window.RN_OCR_WORKER_URL) options.workerPath = window.RN_OCR_WORKER_URL;
      if (window.RN_OCR_CORE_URL) options.corePath = window.RN_OCR_CORE_URL;
      if (window.RN_OCR_LANG_URL) options.langPath = window.RN_OCR_LANG_URL;

      return Tesseract.recognize(blob, 'chi_sim', options);
    }).then(function (result) {
      var text = (result && result.data && result.data.text) || '';
      var idcard = extractIdCard(text);
      var name = extractName(text);
      if (idcard) {
        $('rnIdCard').value = idcard;
        $('rnOcrIdCardVal').value = idcard;
      }
      if (name) {
        $('rnRealName').value = name;
        $('rnOcrNameVal').value = name;
      }
      var boxEl = $('rnOcrResult');
      if (boxEl && (idcard || name)) {
        $('rnOcrName').textContent = name || '未识别';
        $('rnOcrIdCard').textContent = idcard || '未识别';
        boxEl.style.display = 'block';
      }
      if (!idcard && !name) {
        alert('本地识别未提取到有效信息，请检查照片清晰度后重试，或手动填写（将转人工审核）。');
      }
    }).catch(function (e) {
      console.error('[realname] OCR failed:', e);
      alert('本地 OCR 加载失败，可手动填写后提交（将转人工审核）。');
    }).then(function () {
      ocrRunning = false;
      if (state) state.style.display = 'none';
    });
  }

  /* ---------- 表单提交 ---------- */
  var form = $('rnForm');
  var submitBtn = $('rnSubmit');

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var realName = ($('rnRealName').value || '').trim();
    var phone = ($('rnPhone').value || '').trim();
    var idCard = ($('rnIdCard').value || '').trim().toUpperCase();

    if (!/^[\u4e00-\u9fa5·]{2,20}$/.test(realName)) { alert('请填写正确的姓名'); return; }
    if (!/^1[3-9]\d{9}$/.test(phone)) { alert('请填写正确的 11 位手机号'); return; }
    if (!/^\d{17}[\dX]$/.test(idCard)) { alert('请填写正确的 18 位身份证号'); return; }
    if (!files.front) { alert('请上传身份证正面照片'); return; }
    if (!files.back) { alert('请上传身份证反面照片'); return; }
    if (!files.hand) { alert('请上传手持身份证照片'); return; }

    submitBtn.disabled = true;
    submitBtn.textContent = '提交中…';

    var fd = new FormData();
    fd.append('real_name', realName);
    fd.append('phone', phone);
    fd.append('id_card', idCard);
    fd.append('ocr_name', $('rnOcrNameVal').value || '');
    fd.append('ocr_id_card', $('rnOcrIdCardVal').value || '');
    fd.append('front_img', files.front, 'front.jpg');
    fd.append('back_img', files.back, 'back.jpg');
    fd.append('hand_img', files.hand, 'hand.jpg');
    var tokenEl = $('rnCsrf');
    if (tokenEl && tokenEl.value) fd.append('_csrf', tokenEl.value);

    fetch(window.RN_SUBMIT_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json().catch(function () { return { code: '响应解析失败' }; }); })
      .then(function (res) {
        if (res && res.code === 'ok') {
          alert(res.message || '提交成功');
          window.location.href = window.RN_STATUS_URL;
        } else if (res && res.code === 'not_login') {
          alert('登录已失效，请重新登录');
          window.location.reload();
        } else {
          alert(res && res.code ? res.code : '提交失败，请稍后重试');
          submitBtn.disabled = false;
          submitBtn.textContent = '提交认证';
        }
      })
      .catch(function () {
        alert('网络错误，请稍后重试');
        submitBtn.disabled = false;
        submitBtn.textContent = '提交认证';
      });
  });
})();
