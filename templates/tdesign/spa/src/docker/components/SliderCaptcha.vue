<template>
  <div class="slider-captcha">
    <!-- 拼图区 -->
    <div class="slider-captcha__canvas">
      <div ref="canvasBox" class="slider-captcha__canvas-box">
        <canvas ref="bgCanvas" class="slider-captcha__canvas-bg" />
        <canvas
          ref="pieceCanvas"
          class="slider-captcha__canvas-piece"
          :style="{ left: pieceLeft + 'px' }"
        />
      </div>
      <button v-if="showRefresh" class="slider-captcha__refresh" @click="reset" title="刷新验证码">
        <i class="mdi mdi-refresh"></i>
      </button>
    </div>

    <!-- 滑轨区 -->
    <div
      ref="trackRef"
      class="slider-captcha__track"
      :class="{ 'is-success': status === 'success', 'is-error': status === 'error', 'is-dragging': isDragging }"
      @mousedown="onDragStart"
      @touchstart.prevent="onDragStart"
    >
      <span class="slider-captcha__track-text">
        <template v-if="status === 'success'">验证通过</template>
        <template v-else-if="status === 'error'">验证失败，请重试</template>
        <template v-else-if="isDragging">拖动中...</template>
        <template v-else>请按住滑块拖动到正确位置</template>
      </span>
      <div
        v-show="status !== 'success'"
        class="slider-captcha__thumb"
        :style="{ left: thumbLeft + 'px' }"
      >
        <i v-if="status === 'idle'" class="mdi mdi-arrow-right-bold"></i>
        <i v-else-if="status === 'error'" class="mdi mdi-close"></i>
        <i v-else class="mdi mdi-arrow-right-bold"></i>
      </div>
      <div
        v-show="status === 'success'"
        class="slider-captcha__thumb slider-captcha__thumb--success"
        :style="{ left: thumbLeft + 'px' }"
      >
        <i class="mdi mdi-check"></i>
      </div>
      <!-- 成功进度背景 -->
      <div class="slider-captcha__progress" :style="{ width: status === 'success' ? (thumbLeft + 28) + 'px' : '0' }"></div>
    </div>

    <div class="slider-captcha__status">
      <template v-if="status === 'loading'"><i class="mdi mdi-loading mdi-spin"></i> 正在加载验证码...</template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'

const emit = defineEmits(['success', 'reset'])

// 验证码图片基础路径（生产构建后图片在 dist/captcha-images/ 下）
const boot = window.__TD_BOOT__ || {}
const imgBase = boot.captchaBase || '/captcha-images/'

const bgCanvas = ref(null)
const pieceCanvas = ref(null)
const canvasBox = ref(null)
const trackRef = ref(null)

const pieceLeft = ref(0)
const thumbLeft = ref(0)
const isDragging = ref(false)
const status = ref('loading') // 'loading' | 'idle' | 'dragging' | 'success' | 'error'
const showRefresh = ref(false)

// 拼图配置
const PUZZLE_SIZE_RATIO = 0.14
const PIECE_WIDTH_RATIO = 0.18 // 滑块拼图宽度比例
const PUZZLE_OFFSET = 5 // 拼图内描边偏移

let targetX = 0        // 目标位置（像素）
let canvasWidth = 0
let canvasHeight = 0
let scaledTargetX = 0
let puzzleSize = 0
let pieceWidth = 0
let scale = 1
let startX = 0
let trackWidth = 0

// 监听窗口变化重新计算
let resizeTimer = null
function handleResize() {
  clearTimeout(resizeTimer)
  resizeTimer = setTimeout(() => {
    if (bgCanvas.value && imgCache) {
      drawCaptcha()
    }
  }, 300)
}

function drawPuzzlePath(ctx, x, y, size) {
  ctx.beginPath()
  ctx.moveTo(x, y)
  ctx.lineTo(x + size * 0.4, y)
  ctx.arc(x + size * 0.5, y, size * 0.12, Math.PI, 0, true)
  ctx.lineTo(x + size, y)
  ctx.lineTo(x + size, y + size * 0.4)
  ctx.arc(x + size, y + size * 0.5, size * 0.12, Math.PI * 1.5, Math.PI * 0.5, true)
  ctx.lineTo(x + size, y + size)
  ctx.lineTo(x, y + size)
  ctx.lineTo(x, y)
  ctx.closePath()
}

let imgCache = null

function loadRandomImage() {
  return new Promise((resolve, reject) => {
    if (imgCache) return resolve(imgCache)
    const img = new Image()
    img.crossOrigin = 'anonymous'
    const id = Math.floor(Math.random() * 10) + 1
    img.src = `${imgBase}${id}.jpg`
    img.onload = () => {
      imgCache = img
      resolve(img)
    }
    img.onerror = reject
  })
}

function drawCaptcha() {
  if (!bgCanvas.value || !pieceCanvas.value || !canvasBox.value) return

  const box = canvasBox.value
  canvasWidth = box.clientWidth
  canvasHeight = box.clientHeight
  if (canvasWidth === 0 || canvasHeight === 0) return

  const bg = bgCanvas.value
  bg.width = canvasWidth
  bg.height = canvasHeight
  const bgCtx = bg.getContext('2d')

  puzzleSize = Math.round(canvasWidth * PUZZLE_SIZE_RATIO)
  pieceWidth = Math.round(canvasWidth * PIECE_WIDTH_RATIO)

  // 随机目标位置
  targetX = Math.floor(Math.random() * (canvasWidth - puzzleSize - 40)) + 20
  scale = 1
  scaledTargetX = targetX

  const py = Math.round((canvasHeight - puzzleSize) / 2)

  const piece = pieceCanvas.value
  piece.width = pieceWidth
  piece.height = canvasHeight
  const pieceCtx = piece.getContext('2d')

  if (imgCache) {
    // 绘制背景
    bgCtx.drawImage(imgCache, 0, 0, canvasWidth, canvasHeight)

    // 背景上的拼图挖空（半透明黑色）
    bgCtx.save()
    drawPuzzlePath(bgCtx, scaledTargetX, py, puzzleSize)
    bgCtx.fillStyle = 'rgba(0, 0, 0, 0.35)'
    bgCtx.fill()
    bgCtx.restore()

    // 拼图滑块
    pieceCtx.clearRect(0, 0, piece.width, piece.height)
    pieceCtx.save()
    drawPuzzlePath(pieceCtx, PUZZLE_OFFSET, py, puzzleSize, true)
    pieceCtx.clip()
    pieceCtx.drawImage(
      imgCache,
      scaledTargetX - PUZZLE_OFFSET, 0,
      piece.width, canvasHeight,
      0, 0,
      piece.width, canvasHeight
    )
    pieceCtx.restore()

    // 滑块描边
    pieceCtx.save()
    drawPuzzlePath(pieceCtx, PUZZLE_OFFSET, py, puzzleSize)
    pieceCtx.strokeStyle = '#0052d9'
    pieceCtx.lineWidth = 2
    pieceCtx.stroke()
    pieceCtx.restore()
  }

  status.value = 'idle'
  showRefresh.value = true
}

async function initCaptcha() {
  status.value = 'loading'
  pieceLeft.value = 0
  thumbLeft.value = 0
  try {
    await loadRandomImage()
    await nextTick()
    drawCaptcha()
  } catch {
    status.value = 'error'
  }
}

function reset() {
  imgCache = null
  initCaptcha()
  emit('reset')
}

// ========== 拖动逻辑 ==========

function onDragStart(e) {
  if (status.value === 'success' || status.value === 'loading') return
  isDragging.value = true
  status.value = 'dragging'
  const clientX = e.touches ? e.touches[0].clientX : e.clientX
  startX = clientX - thumbLeft.value
  trackWidth = trackRef.value?.clientWidth || 300

  document.addEventListener('mousemove', onDragMove)
  document.addEventListener('mouseup', onDragEnd)
  document.addEventListener('touchmove', onDragMove, { passive: false })
  document.addEventListener('touchend', onDragEnd)
}

function onDragMove(e) {
  if (!isDragging.value) return
  e.preventDefault()
  const clientX = e.touches ? e.touches[0].clientX : e.clientX
  let x = clientX - startX
  x = Math.max(0, Math.min(x, trackWidth - 28))
  thumbLeft.value = x
  pieceLeft.value = x
}

async function onDragEnd() {
  if (!isDragging.value) return
  isDragging.value = false
  document.removeEventListener('mousemove', onDragMove)
  document.removeEventListener('mouseup', onDragEnd)
  document.removeEventListener('touchmove', onDragMove)
  document.removeEventListener('touchend', onDragEnd)

  const tolerance = 5
  const diff = Math.abs(thumbLeft.value - scaledTargetX)

  if (diff <= tolerance) {
    // 精确对齐
    pieceLeft.value = scaledTargetX
    thumbLeft.value = scaledTargetX
    status.value = 'success'
    emit('success')
  } else {
    status.value = 'error'
    setTimeout(() => {
      reset()
    }, 1200)
  }
}

onMounted(() => {
  initCaptcha()
  window.addEventListener('resize', handleResize)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousemove', onDragMove)
  document.removeEventListener('mouseup', onDragEnd)
  document.removeEventListener('touchmove', onDragMove)
  document.removeEventListener('touchend', onDragEnd)
  window.removeEventListener('resize', handleResize)
  clearTimeout(resizeTimer)
})

defineExpose({ reset })
</script>

<style scoped>
.slider-captcha {
  width: 100%;
  user-select: none;
}

/* ---- 拼图画布 ---- */
.slider-captcha__canvas {
  position: relative;
  margin-bottom: 12px;
}
.slider-captcha__canvas-box {
  position: relative;
  width: 100%;
  aspect-ratio: 4 / 3;
  background: #e8eaed;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: inset 0 1px 4px rgba(0,0,0,0.08);
}
.slider-captcha__canvas-bg {
  display: block;
  width: 100%;
  height: 100%;
}
.slider-captcha__canvas-piece {
  position: absolute;
  top: 0;
  height: 100%;
}
.slider-captcha__refresh {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 50%;
  background: rgba(255,255,255,0.8);
  color: #555;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.12);
  transition: background 0.2s;
}
.slider-captcha__refresh:hover {
  background: #fff;
  color: #0052d9;
}

/* ---- 滑轨 ---- */
.slider-captcha__track {
  position: relative;
  height: 40px;
  border-radius: 20px;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  cursor: default;
  transition: border-color 0.3s, background 0.3s;
}
.slider-captcha__track.is-dragging {
  border-color: #0052d9;
  background: #e8f0fe;
}
.slider-captcha__track.is-success {
  border-color: #2ba471;
  background: #e3f9e9;
}
.slider-captcha__track.is-error {
  border-color: #d54941;
  background: #fdece8;
  animation: captcha-shake 0.5s;
}

@keyframes captcha-shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-6px); }
  75% { transform: translateX(6px); }
}

.slider-captcha__track-text {
  flex: 1;
  text-align: center;
  font-size: 13px;
  color: #9e9e9e;
  pointer-events: none;
  transition: color 0.3s;
}
.is-dragging .slider-captcha__track-text { color: #0052d9; }
.is-success .slider-captcha__track-text { color: #2ba471; }
.is-error .slider-captcha__track-text { color: #d54941; }

.slider-captcha__progress {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  border-radius: 20px;
  background: rgba(43, 164, 113, 0.15);
  transition: width 0.3s;
  pointer-events: none;
}

/* ---- 滑块按钮 ---- */
.slider-captcha__thumb {
  position: absolute;
  top: 3px;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: #fff;
  border: 2px solid #d0d5dd;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: grab;
  box-shadow: 0 2px 6px rgba(0,0,0,0.12);
  transition: background 0.2s, border-color 0.2s, box-shadow 0.2s;
  z-index: 2;
}
.slider-captcha__thumb:hover {
  border-color: #0052d9;
  box-shadow: 0 2px 8px rgba(0,82,217,0.2);
}
.is-dragging .slider-captcha__thumb {
  border-color: #0052d9;
  background: #e8f0fe;
  cursor: grabbing;
  box-shadow: 0 3px 10px rgba(0,82,217,0.25);
}
.is-error .slider-captcha__thumb {
  border-color: #d54941;
  background: #fdece8;
}
.slider-captcha__thumb i {
  font-size: 16px;
  color: #888;
  transition: color 0.2s;
}
.is-dragging .slider-captcha__thumb i { color: #0052d9; }
.is-error .slider-captcha__thumb i { color: #d54941; }

.slider-captcha__thumb--success {
  background: #2ba471;
  border-color: #2ba471;
  cursor: default;
}
.slider-captcha__thumb--success i {
  font-size: 18px;
  color: #fff;
}

/* ---- 加载状态 ---- */
.slider-captcha__status {
  margin-top: 8px;
  text-align: center;
  font-size: 12px;
  color: #999;
  min-height: 18px;
}
</style>
