<template>
  <div style="position: relative;">
    <div
      v-if="type === '2'"
      class="verify-img-out"
      :style="{ height: (parseInt(setSize.imgHeight) + vSpace) + 'px' }"
    >
      <div
        class="verify-img-panel"
        :style="{ width: setSize.imgWidth, height: setSize.imgHeight }"
      >
        <img
          :src="backImgBase ? 'data:image/png;base64,' + backImgBase : defaultImg"
          alt=""
          style="width:100%;height:100%;display:block"
        />
        <div v-show="showRefresh" class="verify-refresh" @click="refresh">
          <i class="iconfont icon-refresh" />
        </div>
        <transition name="tips">
          <span v-if="tipWords" class="verify-tips" :class="passFlag ? 'suc-bg' : 'err-bg'">{{ tipWords }}</span>
        </transition>
      </div>
    </div>
    <!-- 滑轨 -->
    <div
      class="verify-bar-area"
      :style="{ width: setSize.imgWidth, height: barSize.height, 'line-height': barSize.height }"
    >
      <span class="verify-msg" v-text="text" />
      <div
        class="verify-left-bar"
        :style="{ width: (leftBarWidth !== undefined) ? leftBarWidth : barSize.height, height: barSize.height, 'border-color': leftBarBorderColor, transition: transitionWidth }"
      >
        <span class="verify-msg" v-text="finishText" />
        <div
          class="verify-move-block"
          :style="{ width: barSize.height, height: barSize.height, 'background-color': moveBlockBackgroundColor, left: moveBlockLeft, transition: transitionLeft }"
          @touchstart="start"
          @mousedown="start"
        >
          <i :class="['verify-icon iconfont', iconClass]" :style="{ color: iconColor }" />
          <div
            v-if="type === '2'"
            class="verify-sub-block"
            :style="{
              'width': Math.floor(parseInt(setSize.imgWidth) * 47 / 310) + 'px',
              'height': setSize.imgHeight,
              'top': '-' + (parseInt(setSize.imgHeight) + vSpace) + 'px',
              'background-size': setSize.imgWidth + ' ' + setSize.imgHeight,
            }"
          >
            <img
              :src="'data:image/png;base64,' + blockBackImgBase"
              alt=""
              style="width:100%;height:100%;display:block"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * VerifySlide 滑动拼图（官方组件 Vue3 适配：$parent 事件改为 emits，$set 改直赋）
 */
import { aesEncrypt } from './aes'
import { resetSize } from './util'
import { reqGet, reqCheck } from './api'

export default {
  name: 'VerifySlide',
  emits: ['ready', 'success', 'error'],
  props: {
    captchaType: { type: String, default: 'blockPuzzle' },
    type: { type: String, default: '2' },
    mode: { type: String, default: 'fixed' },
    vSpace: { type: Number, default: 5 },
    explain: { type: String, default: '向右滑动完成验证' },
    imgSize: {
      type: Object,
      default() {
        return { width: '310px', height: '155px' }
      },
    },
    blockSize: {
      type: Object,
      default() {
        return { width: '50px', height: '50px' }
      },
    },
    barSize: {
      type: Object,
      default() {
        return { width: '310px', height: '40px' }
      },
    },
    defaultImg: { type: String, default: '' },
  },
  data() {
    return {
      secretKey: '',
      passFlag: '',
      backImgBase: '',
      blockBackImgBase: '',
      backToken: '',
      startMoveTime: 0,
      endMovetime: 0,
      tipWords: '',
      text: '',
      finishText: '',
      setSize: { imgHeight: 0, imgWidth: 0, barHeight: 0, barWidth: 0 },
      moveBlockLeft: undefined,
      leftBarWidth: undefined,
      moveBlockBackgroundColor: undefined,
      leftBarBorderColor: '#ddd',
      iconColor: undefined,
      iconClass: 'icon-right',
      status: false,
      isEnd: false,
      showRefresh: true,
      transitionLeft: '',
      transitionWidth: '',
    }
  },
  computed: {
    barArea() {
      return this.$el.querySelector('.verify-bar-area')
    },
  },
  watch: {
    type: {
      immediate: true,
      handler() {
        this.init()
      },
    },
  },
  mounted() {
    this.$el.onselectstart = function () {
      return false
    }
  },
  beforeUnmount() {
    this.unbindEvents()
  },
  methods: {
    init() {
      this.text = this.explain
      this.getPictrue()
      this.$nextTick(() => {
        const setSize = resetSize(this)
        for (const key in setSize) {
          this.setSize[key] = setSize[key]
        }
        this.$emit('ready', this)
      })
      this.unbindEvents()
      window.addEventListener('touchmove', this.onMove)
      window.addEventListener('mousemove', this.onMove)
      window.addEventListener('touchend', this.onEnd)
      window.addEventListener('mouseup', this.onEnd)
    },
    unbindEvents() {
      window.removeEventListener('touchmove', this.onMove)
      window.removeEventListener('mousemove', this.onMove)
      window.removeEventListener('touchend', this.onEnd)
      window.removeEventListener('mouseup', this.onEnd)
    },
    onMove(e) {
      this.move(e)
    },
    onEnd() {
      this.end()
    },
    start(e) {
      e = e || window.event
      const x = e.touches ? e.touches[0].pageX : e.clientX
      this.startLeft = Math.floor(x - this.barArea.getBoundingClientRect().left)
      this.startMoveTime = +new Date()
      if (this.isEnd === false) {
        this.text = ''
        this.moveBlockBackgroundColor = '#337ab7'
        this.leftBarBorderColor = '#337AB7'
        this.iconColor = '#fff'
        e.stopPropagation()
        this.status = true
      }
    },
    move(e) {
      e = e || window.event
      if (this.status && this.isEnd === false) {
        const x = e.touches ? e.touches[0].pageX : e.clientX
        const bar_area_left = this.barArea.getBoundingClientRect().left
        let move_block_left = x - bar_area_left
        if (move_block_left >= this.barArea.offsetWidth - parseInt(parseInt(this.blockSize.width) / 2) - 2) {
          move_block_left = this.barArea.offsetWidth - parseInt(parseInt(this.blockSize.width) / 2) - 2
        }
        if (move_block_left <= 0) {
          move_block_left = parseInt(parseInt(this.blockSize.width) / 2)
        }
        this.moveBlockLeft = move_block_left - this.startLeft + 'px'
        this.leftBarWidth = move_block_left - this.startLeft + 'px'
      }
    },
    end() {
      this.endMovetime = +new Date()
      if (this.status && this.isEnd === false) {
        let moveLeftDistance = parseInt((this.moveBlockLeft || '').replace('px', ''))
        moveLeftDistance = (moveLeftDistance * 310) / parseInt(this.setSize.imgWidth)
        const point = { x: moveLeftDistance, y: 5.0 }
        const data = {
          captchaType: this.captchaType,
          pointJson: this.secretKey ? aesEncrypt(JSON.stringify(point), this.secretKey) : JSON.stringify(point),
          token: this.backToken,
        }
        reqCheck(data).then((res) => {
          if (res.repCode === '0000') {
            this.moveBlockBackgroundColor = '#5cb85c'
            this.leftBarBorderColor = '#5cb85c'
            this.iconColor = '#fff'
            this.iconClass = 'icon-check'
            this.showRefresh = false
            this.isEnd = true
            this.passFlag = true
            this.tipWords = ((this.endMovetime - this.startMoveTime) / 1000).toFixed(2) + 's验证成功'
            const captchaVerification = this.secretKey
              ? aesEncrypt(this.backToken + '---' + JSON.stringify(point), this.secretKey)
              : this.backToken + '---' + JSON.stringify(point)
            this.$emit('success', { captchaVerification })
          } else {
            this.moveBlockBackgroundColor = '#d9534f'
            this.leftBarBorderColor = '#d9534f'
            this.iconColor = '#fff'
            this.iconClass = 'icon-close'
            this.passFlag = false
            this.tipWords = '验证失败'
            this.$emit('error', this)
            setTimeout(() => {
              this.refresh()
            }, 1000)
          }
          setTimeout(() => {
            this.tipWords = ''
          }, 1000)
        })
        this.status = false
      }
    },
    refresh() {
      this.showRefresh = true
      this.finishText = ''
      this.transitionLeft = 'left .3s'
      this.moveBlockLeft = 0
      this.leftBarWidth = undefined
      this.transitionWidth = 'width .3s'
      this.leftBarBorderColor = '#ddd'
      this.moveBlockBackgroundColor = '#fff'
      this.iconColor = '#000'
      this.iconClass = 'icon-right'
      this.isEnd = false
      this.getPictrue()
      setTimeout(() => {
        this.transitionWidth = ''
        this.transitionLeft = ''
        this.text = this.explain
      }, 300)
    },
    getPictrue() {
      const data = {
        captchaType: this.captchaType,
        clientUid: localStorage.getItem('slider'),
        ts: Date.now(),
      }
      reqGet(data).then((res) => {
        if (res.repCode === '0000') {
          this.backImgBase = res.repData.originalImageBase64
          this.blockBackImgBase = res.repData.jigsawImageBase64
          this.backToken = res.repData.token
          this.secretKey = res.repData.secretKey
        } else {
          this.tipWords = res.repMsg || '获取验证码失败'
        }
      })
    },
  },
}
</script>
