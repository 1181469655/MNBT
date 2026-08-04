		</div><!-- /.dk-content -->
	</main>
</div><!-- /.dk-app -->
<div class="dk-toast-wrap" id="dkToastWrap"></div>
<div class="dk-modal-mask" id="dkModalMask"></div>
<script>
// ===== Docker 控制台公共 JS =====
function dkToast(msg, type){
	type = type || 'info';
	var wrap = document.getElementById('dkToastWrap');
	var el = document.createElement('div');
	el.className = 'dk-toast ' + type;
	el.textContent = msg;
	wrap.appendChild(el);
	setTimeout(function(){ el.style.opacity='0'; el.style.transition='opacity .3s'; setTimeout(function(){ el.remove(); }, 300); }, 2600);
}
function dkAjax(gn, data, opts){
	opts = opts || {};
	data = data || {};
	if (typeof data === 'object' && !(data instanceof FormData)) {
		var fd = new FormData();
		for (var k in data) fd.append(k, data[k]);
		data = fd;
	}
	data.append('_csrf', window.MNBT_CSRF);
	return $.ajax({
		url: 'ajax.php?gn=' + encodeURIComponent(gn),
		type: 'POST', data: data, processData: false, contentType: false, dataType: 'json',
		timeout: opts.timeout || 60000
	}).then(function(r){
		if (r && r.success) return r;
		var msg = (r && r.msg) ? r.msg : '请求失败';
		if (!opts.silent) dkToast(msg, 'error');
		return $.Deferred().reject(r);
	}, function(xhr){
		if (!opts.silent) dkToast('网络错误：' + (xhr.statusText || ''), 'error');
		return $.Deferred().reject(xhr);
	});
}
function dockerLogout(){
	dkAjax('logout', {}).then(function(){ window.location.href = 'login.php'; });
}
function dkModal(html, title){
	$('#dkModalMask').html(
		'<div class="dk-modal"><div class="dk-modal-head"><h3>'+ (title||'') +'</h3><button class="dk-modal-close" onclick="dkCloseModal()">&times;</button></div><div class="dk-modal-body">'+ html +'</div></div>'
	).addClass('show');
}
function dkCloseModal(){ $('#dkModalMask').removeClass('show').empty(); }
$(document).on('click', '.dk-modal-mask', function(e){ if (e.target === this) dkCloseModal(); });
</script>
