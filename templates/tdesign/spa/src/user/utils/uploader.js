/**
 * 分片上传器（SPA 版，对齐 imsetes/js/mnbt-uploader.js 行为）
 * 自适应分片 1MB → 8MB，断点续传，进度/速度/剩余时间回调
 *
 * 用法：
 *   const up = new ChunkUploader(file, path, { onProgress, onDone, onError })
 *   up.start()
 *   up.stop()
 */
import { uploadPrepare, uploadChunk } from '@/user/api/ftp'

const CHUNK_INIT = 1024 * 1024
const CHUNK_MAX = 1024 * 1024 * 8

export function fmtSize(bytes) {
  let v = Number(bytes) || 0
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  let i = 0
  while (v >= 1024 && i < units.length - 1) {
    v /= 1024
    i += 1
  }
  return v.toFixed(2) + units[i]
}

export class ChunkUploader {
  constructor(file, path, opts = {}) {
    this.file = file
    this.path = path || '/'
    this.opts = opts
    this.offset = 0
    this.chunk = CHUNK_INIT
    this.startTime = 0
    this.stopped = false
    this.controller = null
  }

  stop() {
    this.stopped = true
    if (this.controller) this.controller.abort()
  }

  async start() {
    this.startTime = Date.now()
    const prep = await uploadPrepare(this.path, this.file.name, this.file.size)
    if (this.stopped) return
    if (!prep.ok) {
      this._error(prep.code || '无法开始上传')
      return
    }
    this.offset = Math.min(prep.size || 0, this.file.size)
    this._progress()
    await this._loop()
  }

  async _loop() {
    while (!this.stopped && this.offset < this.file.size) {
      this.controller = new AbortController()
      const end = Math.min(this.offset + this.chunk, this.file.size)
      const blob = this.file.slice(this.offset, end)
      let raw
      try {
        raw = await uploadChunk(this.path, this.file.name, this.offset, this.file.size, blob, this.controller.signal)
      } catch (e) {
        if (this.stopped) return
        this._error((e && e.message) || '网络错误，分片上传失败')
        return
      }
      if (this.stopped) return
      if (!raw || !(raw.qk === 1 || raw.qk === '1')) {
        this._error((raw && raw.code) || '上传失败')
        return
      }
      if (raw.done) {
        this._finish()
        return
      }
      const next = Number(raw.size) || 0
      if (next <= this.offset) {
        this._error('上传未推进，请稍后重试')
        return
      }
      if (this.chunk < CHUNK_MAX) this.chunk = Math.min(this.chunk * 2, CHUNK_MAX)
      this.offset = next
      this._progress()
    }
    if (!this.stopped) this._finish()
  }

  _progress() {
    if (typeof this.opts.onProgress !== 'function') return
    const percent = this.file.size > 0 ? Math.round((this.offset / this.file.size) * 10000) / 100 : 100
    const elapsed = Math.max(0.001, (Date.now() - this.startTime) / 1000)
    const speed = this.offset / elapsed
    const rest = speed > 0 ? Math.round((this.file.size - this.offset) / speed) : -1
    this.opts.onProgress(
      percent,
      fmtSize(speed) + '/s',
      fmtSize(this.offset) + ' / ' + fmtSize(this.file.size),
      rest >= 0 ? rest + ' 秒' : '计算中…',
    )
  }

  _finish() {
    if (typeof this.opts.onProgress === 'function') {
      this.opts.onProgress(100, '', fmtSize(this.file.size) + ' / ' + fmtSize(this.file.size), '')
    }
    if (typeof this.opts.onDone === 'function') this.opts.onDone()
  }

  _error(msg) {
    if (typeof this.opts.onError === 'function') this.opts.onError(msg)
  }
}
