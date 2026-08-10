/* ============================================================
 * realname 插件 - 前端逻辑
 * 图片压缩(1024px/0.8) → 灰度对比度预处理 → 本地 OCR(tesseract.js)
 * → 5级姓名提取 → base64 回传提交（x-www-form-urlencoded，无 FormData）
 * ============================================================ */
(function () {
  'use strict';

  var $ = function (id) { return document.getElementById(id); };

  /* ---------- 图片压缩（canvas，返回 { blob, base64 }） ---------- */
  function compressImage(file) {
    return new Promise(function (resolve, reject) {
      var img = new Image();
      var url = URL.createObjectURL(file);
      img.onload = function () {
        var MAX = 1024;
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
          if (!blob) return reject(new Error('compress failed'));
          var reader = new FileReader();
          reader.onload = function () { resolve({ blob: blob, base64: reader.result }); };
          reader.onerror = function () { reject(new Error('read failed')); };
          reader.readAsDataURL(blob);
        }, 'image/jpeg', 0.8);
      };
      img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('image load failed')); };
      img.src = url;
    });
  }

  /* ---------- 灰度化 + 对比度拉伸 ---------- */
  function preprocessForOcr(inputBlob) {
    return new Promise(function (resolve, reject) {
      var img = new Image();
      var url = URL.createObjectURL(inputBlob);
      img.onload = function () {
        var canvas = document.createElement('canvas');
        canvas.width = img.width; canvas.height = img.height;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);
        URL.revokeObjectURL(url);
        var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        var d = imageData.data;
        var min = 255, max = 0;
        for (var i = 0; i < d.length; i += 4) {
          var gray = d[i] * 0.299 + d[i + 1] * 0.587 + d[i + 2] * 0.114;
          d[i] = d[i + 1] = d[i + 2] = gray;
          if (gray < min) min = gray;
          if (gray > max) max = gray;
        }
        if (max > min) {
          var range = max - min;
          for (var j = 0; j < d.length; j += 4) {
            var v = ((d[j] - min) / range) * 255;
            d[j] = d[j + 1] = d[j + 2] = v;
          }
        }
        ctx.putImageData(imageData, 0, 0);
        canvas.toBlob(function (b) { b ? resolve(b) : reject(new Error('preprocess failed')); }, 'image/png');
      };
      img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('load failed')); };
      img.src = url;
    });
  }

  /* ---------- 上传框交互 ---------- */
  var boxes = document.querySelectorAll('.rn-upload-box');
  var files = { front: null, back: null, hand: null };
  var base64Images = { front: '', back: '', hand: '' };

  function setPreview(box, type, blob) {
    var img = box.querySelector('.rn-upload-preview');
    if (!img) return;
    img.src = URL.createObjectURL(blob);
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
      compressImage(file).then(function (result) {
        files[type] = result.blob;
        base64Images[type] = result.base64;
        setPreview(box, type, result.blob);
        if (type === 'front') runOcr(result.blob);
      }).catch(function () { alert('图片处理失败，请更换图片'); });
    });

    clear.addEventListener('click', function (e) {
      e.stopPropagation();
      files[type] = null;
      base64Images[type] = '';
      input.value = '';
      box.classList.remove('has-file');
    });
  });

  /* ---------- 身份证校验 ---------- */
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
    var matches = t.match(/\d{17}[\dXx]/g) || [];
    for (var i = 0; i < matches.length; i++) {
      if (idcardCheck(matches[i])) return matches[i].toUpperCase();
    }
    return '';
  }

  /* ---------- 姓名提取（5级降级策略） ---------- */
  function extractName(text) {
    var raw = (text || '').replace(/\s+/g, '');
    var m;
    // 策略1: "姓名" + 冒号 + 中文名
    m = raw.match(/姓名[：:]([\u4e00-\u9fa5·]{2,4})/);
    if (m) return m[1];
    // 策略2: "姓名" 后紧跟中文名（无冒号），排除后跟"性"
    m = raw.match(/姓名(?!性)([\u4e00-\u9fa5·]{2,4})/);
    if (m) return m[1];
    // 策略3: "姓"和"名"被噪声分隔
    m = raw.match(/姓.*?名.*?([\u4e00-\u9fa5·]{2,4})/);
    if (m) return m[1];
    // 策略4: 逐行找含"名"的行
    var lines = (text || '').split(/[\r\n]+/);
    for (var i = 0; i < lines.length; i++) {
      var clean = lines[i].replace(/\s+/g, '');
      if (clean.indexOf('名') >= 0 && clean.indexOf('民族') < 0) {
        var nm = clean.match(/名.*?([\u4e00-\u9fa5·]{2,4})/);
        if (nm) return nm[1];
      }
    }
    // 策略5: 全文 "姓名" 后 50 字符内找
    m = raw.match(/姓名.{0,50}?([\u4e00-\u9fa5·]{2,4})/);
    if (m) return m[1];
    return '';
  }

  /* ---------- 本地 OCR ---------- */
  var ocrRunning = false;

  function loadOcrLib() {
    return new Promise(function (resolve, reject) {
      if (window.Tesseract) { resolve(window.Tesseract); return; }
      var ocrUrl = window.RN_OCR_URL;
      if (!ocrUrl) { reject(new Error('OCR 库路径未配置')); return; }
      var s = document.createElement('script');
      s.src = ocrUrl;
      s.onload = function () { window.Tesseract ? resolve(window.Tesseract) : reject(new Error('OCR 加载失败')); };
      s.onerror = function () { reject(new Error('OCR 库加载失败')); };
      document.head.appendChild(s);
    });
  }

  function runOcr(blob) {
    if (ocrRunning) return;
    var state = $('rnOcrState');
    if (state) state.style.display = 'flex';
    ocrRunning = true;

    loadOcrLib().then(function (Tesseract) {
      return preprocessForOcr(blob).then(function (processed) {
        var options = {
          logger: function () {},
          preserve_interword_spaces: '1',
          tessedit_pageseg_mode: '6'
        };
        if (window.RN_OCR_WORKER_URL) options.workerPath = window.RN_OCR_WORKER_URL;
        if (window.RN_OCR_CORE_URL) options.corePath = window.RN_OCR_CORE_URL;
        if (window.RN_OCR_LANG_URL) options.langPath = window.RN_OCR_LANG_URL;
        return Tesseract.recognize(processed, 'chi_sim', options);
      });
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

  /* ---------- 表单提交（base64 x-www-form-urlencoded） ---------- */
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
    if (!base64Images.front) { alert('请上传身份证正面照片'); return; }
    if (!base64Images.back) { alert('请上传身份证反面照片'); return; }
    if (!base64Images.hand) { alert('请上传手持身份证照片'); return; }

    submitBtn.disabled = true;
    submitBtn.textContent = '提交中…';

    var body = new URLSearchParams();
    body.append('real_name', realName);
    body.append('phone', phone);
    body.append('id_card', idCard);
    body.append('ocr_name', ($('rnOcrNameVal').value || ''));
    body.append('ocr_id_card', ($('rnOcrIdCardVal').value || ''));
    body.append('front_img', base64Images.front);
    body.append('back_img', base64Images.back);
    body.append('hand_img', base64Images.hand);
    var tokenEl = $('rnCsrf');
    if (tokenEl && tokenEl.value) body.append('_csrf', tokenEl.value);

    fetch(window.RN_SUBMIT_URL, {
      method: 'POST',
      body: body,
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      credentials: 'same-origin'
    })
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
