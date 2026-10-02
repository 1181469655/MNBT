/*
 * 梦奈宝塔主机系统(MNBT) - 在线文件管理分片上传器
 *
 * 依赖：jQuery
 * 后端：user/ajax.php → gn=file_upload_prepare / file_upload
 * 特性：断点续传、自适应分片、进度/速度/剩余时间回调
 *
 * 用法：
 *   var up = new MnbtUploader(file, path, {
 *       onProgress: function (percent, speed, uploaded, rest) {},
 *       onDone: function () {},
 *       onError: function (msg) {}
 *   });
 *   up.start();          // 开始上传
 *   up.stop();           // 停止后续分片
 */
(function () {
    'use strict';

    var CHUNK_INIT = 1024 * 1024;      // 初始分片 1MB
    var CHUNK_MAX = 1024 * 1024 * 8;   // 分片上限 8MB

    function fmtSize(bytes) {
        var v = Number(bytes) || 0;
        var units = ['B', 'KB', 'MB', 'GB', 'TB'];
        var i = 0;
        while (v >= 1024 && i < units.length - 1) {
            v /= 1024;
            i++;
        }
        return v.toFixed(2) + units[i];
    }

    function MnbtUploader(file, path, opts) {
        this.file = file;
        this.path = path || '/';
        this.opts = opts || {};
        this.offset = 0;
        this.chunk = CHUNK_INIT;
        this.startTime = 0;
        this.stopped = false;
    }

    MnbtUploader.prototype.stop = function () {
        this.stopped = true;
    };

    MnbtUploader.prototype.start = function () {
        var self = this;
        self.startTime = new Date().getTime();
        // 断点续传：查询节点上同名临时分片已接收的字节数
        $.post('./ajax.php', {
            gn: 'file_upload_prepare',
            path: self.path,
            name: self.file.name,
            size: String(self.file.size)
        }, function (res) {
            if (self.stopped) return;
            if (res && res.qk === 1) {
                self.offset = Math.min(Number(res.size) || 0, self.file.size);
                self._progress();
                self._next();
            } else {
                self._error((res && res.code) || '无法开始上传');
            }
        }, 'json').fail(function () {
            self._error('网络错误，无法开始上传');
        });
    };

    MnbtUploader.prototype._next = function () {
        if (this.stopped) return;
        if (this.offset >= this.file.size) {
            this._finish();
            return;
        }
        var self = this;
        var end = Math.min(this.offset + this.chunk, this.file.size);
        var fd = new FormData();
        fd.append('gn', 'file_upload');
        fd.append('path', this.path);
        fd.append('name', this.file.name);
        fd.append('start', String(this.offset));
        fd.append('size', String(this.file.size));
        fd.append('file', this.file.slice(this.offset, end));
        $.ajax({
            type: 'post',
            url: './ajax.php',
            data: fd,
            cache: false,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (self.stopped) return;
                if (!res || res.qk !== 1) {
                    self._error((res && res.code) || '上传失败');
                    return;
                }
                if (res.done) {
                    self._finish();
                    return;
                }
                var next = Number(res.size) || 0;
                if (next <= self.offset) {
                    self._error('上传未推进，请稍后重试');
                    return;
                }
                if (self.chunk < CHUNK_MAX) self.chunk = Math.min(self.chunk * 2, CHUNK_MAX);
                self.offset = next;
                self._progress();
                self._next();
            },
            error: function () {
                self._error('网络错误，分片上传失败');
            }
        });
    };

    MnbtUploader.prototype._progress = function () {
        if (typeof this.opts.onProgress !== 'function') return;
        var percent = this.file.size > 0 ? Math.round(this.offset / this.file.size * 10000) / 100 : 100;
        var elapsed = Math.max(0.001, (new Date().getTime() - this.startTime) / 1000);
        var speed = this.offset / elapsed;
        var rest = speed > 0 ? Math.round((this.file.size - this.offset) / speed) : -1;
        this.opts.onProgress(
            percent,
            fmtSize(speed) + '/s',
            fmtSize(this.offset) + ' / ' + fmtSize(this.file.size),
            rest >= 0 ? rest + ' 秒' : '计算中…'
        );
    };

    MnbtUploader.prototype._finish = function () {
        if (typeof this.opts.onProgress === 'function') {
            this.opts.onProgress(100, '', fmtSize(this.file.size) + ' / ' + fmtSize(this.file.size), '');
        }
        if (typeof this.opts.onDone === 'function') this.opts.onDone();
    };

    MnbtUploader.prototype._error = function (msg) {
        if (typeof this.opts.onError === 'function') this.opts.onError(msg);
    };

    window.MnbtUploader = MnbtUploader;
    window.mnbt_fmt_size = fmtSize;
})();
