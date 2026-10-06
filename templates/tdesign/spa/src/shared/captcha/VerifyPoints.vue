<template>
  <div style="position: relative">
    <div class="verify-img-out">
      <div
        class="verify-img-panel"
        :style="{
          'width': setSize.imgWidth,
          'height': setSize.imgHeight,
          'background-size': setSize.imgWidth + ' ' + setSize.imgHeight,
          'margin-bottom': vSpace + 'px',
        }"
      >
        <div v-show="showRefresh" class="verify-refresh" style="z-index:3" @click="refresh">
          <i class="iconfont icon-refresh" />
        </div>
        <img
          ref="canvas"
          :src="pointBackImgBase ? 'data:image/png;base64,' + pointBackImgBase : defaultImg"
          alt=""
          style="width:100%;height:100%;display:block"
          @click="bindingClick ? canvasClick($event) : undefined"
        />
        <div
          v-for="(tempPoint, index) in tempPoints"
          :key="index"
          class="point-area"
          :style="{
            'background-color':'#1abd6c',
            'color':'#fff',
            'z-index':9999,
            'width':'20px',
            'height':'20px',
            'text-align':'center',
            'line-height':'20px',
            'border-radius': '50%',
            'position':'absolute',
            'top':parseInt(tempPoint.y - 10) + 'px',
            'left':parseInt(tempPoint.x - 10) + 'px',
          }"
        >
          {{ index + 1 }}
        </div>
      </div>
    </div>
    <div
      class="verify-bar-area"
      :style="{ width: setSize.imgWidth, color: barAreaColor, 'border-color': barAreaBorderColor, 'line-height': barSize.height }"
    >
      <span class="verify-msg">{{ text }}</span>
    </div>
  </div>
</template>

<script>
/**
 * VerifyPoints 文字点选（官方组件 Vue3 适配：$parent 事件改为 emits）
 */
import { resetSize } from './util'
import { aesEncrypt } from './aes'
import { reqGet, reqCheck } from './api'

export default {
  name: 'VerifyPoints',
  emits: ['ready', 'success', 'error'],
  props: {
    mode: { type: String, default: 'fixed' },
    captchaType: { type: String, default: 'clickWord' },
    vSpace: { type: Number, default: 5 },
    imgSize: {
      type: Object,
      default() {
        return { width: '310px', height: '155px' }
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
      checkNum: 3,
      fontPos: [],
      checkPosArr: [],
      num: 1,
      pointBackImgBase: '',
      poinTextList: [],
      backToken: '',
      setSize: { imgHeight: 0, imgWidth: 0, barHeight: 0, barWidth: 0 },
      tempPoints: [],
      text: '',
      barAreaColor: undefined,
      barAreaBorderColor: undefined,
      showRefresh: true,
      bindingClick: true,
    }
  },
  watch: {
    captchaType: {
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
  methods: {
    init() {
      this.fontPos.splice(0, this.fontPos.length)
      this.checkPosArr.splice(0, this.checkPosArr.length)
      this.num = 1
      this.getPictrue()
      this.$nextTick(() => {
        this.setSize = resetSize(this)
        this.$emit('ready', this)
      })
    },
    canvasClick(e) {
      this.checkPosArr.push(this.getMousePos(this.$refs.canvas, e))
      if (this.num === this.checkNum) {
        this.num = this.createPoint(this.getMousePos(this.$refs.canvas, e))
        // 按比例转换坐标值后提交校验
        this.checkPosArr = this.pointTransfrom(this.checkPosArr, this.setSize)
        setTimeout(() => {
          const captchaVerification = this.secretKey
            ? aesEncrypt(this.backToken + '---' + JSON.stringify(this.checkPosArr), this.secretKey)
            : this.backToken + '---' + JSON.stringify(this.checkPosArr)
          const data = {
            captchaType: this.captchaType,
            pointJson: this.secretKey ? aesEncrypt(JSON.stringify(this.checkPosArr), this.secretKey) : JSON.stringify(this.checkPosArr),
            token: this.backToken,
          }
          reqCheck(data).then((res) => {
            if (res.repCode === '0000') {
              this.barAreaColor = '#4cae4c'
              this.barAreaBorderColor = '#5cb85c'
              this.text = '验证成功'
              this.bindingClick = false
              this.$emit('success', { captchaVerification })
            } else {
              this.$emit('error', this)
              this.barAreaColor = '#d9534f'
              this.barAreaBorderColor = '#d9534f'
              this.text = '验证失败'
              setTimeout(() => {
                this.refresh()
              }, 700)
            }
          })
        }, 400)
      }
      if (this.num < this.checkNum) {
        this.num = this.createPoint(this.getMousePos(this.$refs.canvas, e))
      }
    },
    getMousePos(obj, e) {
      return { x: e.offsetX, y: e.offsetY }
    },
    createPoint(pos) {
      this.tempPoints.push(Object.assign({}, pos))
      return ++this.num
    },
    refresh() {
      this.tempPoints.splice(0, this.tempPoints.length)
      this.barAreaColor = '#000'
      this.barAreaBorderColor = '#ddd'
      this.bindingClick = true
      this.fontPos.splice(0, this.fontPos.length)
      this.checkPosArr.splice(0, this.checkPosArr.length)
      this.num = 1
      this.getPictrue()
      this.showRefresh = true
    },
    getPictrue() {
      const data = {
        captchaType: this.captchaType,
        clientUid: localStorage.getItem('point'),
        ts: Date.now(),
      }
      reqGet(data).then((res) => {
        if (res.repCode === '0000') {
          this.pointBackImgBase = res.repData.originalImageBase64
          this.backToken = res.repData.token
          this.secretKey = res.repData.secretKey
          this.poinTextList = res.repData.wordList
          this.checkNum = this.poinTextList.length
          this.text = '请依次点击【' + this.poinTextList.join(',') + '】'
        } else {
          this.text = res.repMsg || '获取验证码失败'
        }
      })
    },
    pointTransfrom(pointArr, imgSize) {
      return pointArr.map((p) => {
        const x = Math.round((310 * p.x) / parseInt(imgSize.imgWidth))
        const y = Math.round((155 * p.y) / parseInt(imgSize.imgHeight))
        return { x, y }
      })
    },
  },
}
</script>
