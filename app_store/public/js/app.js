var M = {
  api: function (url, method, data, options) {
    options = options || {};
    return new Promise(function (resolve) {
      var xhr = new XMLHttpRequest();
      xhr.open(method || 'GET', url, true);
      xhr.withCredentials = true;
      xhr.setRequestHeader('Content-Type', 'application/json');
      xhr.onload = function () {
        try {
          var res = JSON.parse(xhr.responseText);
          resolve(res);
        } catch (e) {
          resolve({ code: 500, msg: '解析响应失败' });
        }
      };
      xhr.onerror = function () {
        resolve({ code: 500, msg: '网络错误' });
      };
      xhr.send(data ? JSON.stringify(data) : null);
    });
  },
  get: function (url) { return M.api(url, 'GET'); },
  post: function (url, data) { return M.api(url, 'POST', data); },
  put: function (url, data) { return M.api(url, 'PUT', data); },
  del: function (url) { return M.api(url, 'DELETE'); },

  upload: function (url, formData, onProgress) {
    return new Promise(function (resolve) {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', url, true);
      xhr.withCredentials = true;
      if (onProgress) {
        xhr.upload.onprogress = onProgress;
      }
      xhr.onload = function () {
        try {
          var res = JSON.parse(xhr.responseText);
          resolve(res);
        } catch (e) {
          resolve({ code: 500, msg: '解析响应失败' });
        }
      };
      xhr.onerror = function () {
        resolve({ code: 500, msg: '网络错误' });
      };
      xhr.send(formData);
    });
  },

  // Format file size
  formatSize: function (bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
  },

  // Format price
  formatPrice: function (price) {
    if (price === 0 || price === '0') return '<span class="card-price free">免费</span>';
    return '<span class="card-price">¥' + parseFloat(price).toFixed(2) + '</span>';
  },

  // Status badge
  statusBadge: function (status) {
    var map = {
      pending: '<span class="status-badge status-pending">待审核</span>',
      approved: '<span class="status-badge status-approved">已通过</span>',
      rejected: '<span class="status-badge status-rejected">已驳回</span>',
      suspended: '<span class="status-badge status-suspended">已下架</span>'
    };
    return map[status] || '<span class="status-badge">' + status + '</span>';
  },

  // Strip HTML tags for preview
  stripHtml: function (html) {
    var div = document.createElement('div');
    div.innerHTML = html || '';
    return div.textContent || div.innerText || '';
  },

  // Get current user
  currentUser: null,
  checkLogin: function () {
    return M.get('/api/auth/me').then(function (res) {
      if (res.code === 0) {
        M.currentUser = res.data;
      } else {
        M.currentUser = null;
      }
      return M.currentUser;
    });
  },

  // Render header
  renderHeader: function () {
    var header = document.getElementById('header-top');
    if (!header) return;

    var searchHtml = '';
    // Only show search on home page
    if (location.pathname === '/' || location.pathname === '/index.html' || location.pathname.endsWith('/index.html')) {
      searchHtml = '<div class="search-box"><input type="text" id="search-input" class="layui-input" placeholder="搜索插件/主题..."></div>';
    }

    var navHtml = '';
    if (M.currentUser) {
      var roleText = '';
      if (M.currentUser.role === 'admin') {
        roleText = '管理员';
        navHtml =
          '<div class="nav-right">' +
          '<a href="/submit.html">提交资源</a>' +
          '<a href="/developer.html">开发者中心</a>' +
          '<a href="/admin.html">管理后台</a>' +
          '<span class="user-info">' + M.currentUser.username + '(' + roleText + ')</span>' +
          '<a href="/password.html">修改密码</a>' +
          '<a href="javascript:;" onclick="M.logout()">退出</a>' +
          '</div>';
      } else {
        roleText = '开发者';
        navHtml =
          '<div class="nav-right">' +
          '<a href="/submit.html">提交资源</a>' +
          '<a href="/developer.html">开发者中心</a>' +
          '<span class="user-info">' + M.currentUser.username + '(' + roleText + ')</span>' +
          '<a href="/password.html">修改密码</a>' +
          '<a href="javascript:;" onclick="M.logout()">退出</a>' +
          '</div>';
      }
    } else {
      navHtml =
        '<div class="nav-right">' +
        '<a href="/login.html">登录</a>' +
        '<a href="/register.html">注册</a>' +
        '</div>';
    }

    header.innerHTML =
      '<div class="layui-container">' +
      '<a href="/" class="logo">MNBT Store<small>插件&amp;主题商店</small></a>' +
      '<div class="nav">' +
      '<a href="/" class="' + (location.pathname === '/' || location.pathname.endsWith('/index.html') ? 'layui-this' : '') + '">首页</a>' +
      '</div>' +
      searchHtml +
      navHtml +
      '</div>';

    // Bind search
    var searchInput = document.getElementById('search-input');
    if (searchInput) {
      var timer;
      searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
          var evt = new CustomEvent('store-search', { detail: searchInput.value });
          document.dispatchEvent(evt);
        }, 400);
      });
    }
  },

  logout: function () {
    M.post('/api/auth/logout').then(function () {
      M.currentUser = null;
      location.href = '/';
    });
  }
};

// Initialize header on page load
document.addEventListener('DOMContentLoaded', function () {
  M.checkLogin().then(function () {
    M.renderHeader();
    document.dispatchEvent(new Event('auth-ready'));
  });
});
