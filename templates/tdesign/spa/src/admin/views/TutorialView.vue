<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-school"></i>教程与监控</h3>
        <p class="td-page-subtitle">监控 URL 配置与各平台对接教程</p>
      </div>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-bd">
        <t-tabs v-model="active">
          <!-- 监控教程 -->
          <t-tab-panel value="monitor" label="监控教程">
            <div class="tab-inner">
              <div v-if="!apiKey" class="td-form-note">
                <b>提示:</b> 当前未配置 API 密钥,请先在「API 设置」生成密钥后再使用监控功能。
              </div>

              <div v-for="m in monitorItems" :key="m.path" class="mono-block">
                <div class="mono-head">
                  <strong>{{ m.title }}</strong>
                  <span class="td-text-mute td-text-xs">{{ m.freq }}</span>
                </div>
                <div class="mono-desc td-text-mute td-text-sm">{{ m.desc }}</div>
                <div class="td-code-block mono-url">
                  <span>{{ m.url }}</span>
                  <t-button theme="primary" variant="text" size="small" @click="copy(m.url)">
                    <i class="mdi mdi-content-copy"></i> 复制
                  </t-button>
                </div>
              </div>
            </div>
          </t-tab-panel>

          <!-- 添加宝塔教程 -->
          <t-tab-panel value="baota" label="添加宝塔教程">
            <div class="tab-inner">
              <ol class="step-list">
                <li>登录宝塔面板,进入「软件商店」搜索并安装对应版本的宝塔插件。</li>
                <li>在 MNBT 后台「宝塔列表 → 添加宝塔」中填写宝塔面板地址、登录账号与 API Key。</li>
                <li>确认宝塔面板已开启 API 接口,并将本服务器 IP 加入宝塔 IP 白名单。</li>
                <li>保存后系统会自动测试连接,连接成功后即可在主机管理中调用宝塔接口。</li>
                <li>如出现连接失败,请检查面板端口是否放行、API Key 是否正确。</li>
              </ol>
            </div>
          </t-tab-panel>

          <!-- 添加主机教程 -->
          <t-tab-panel value="host" label="添加主机教程">
            <div class="tab-inner">
              <ol class="step-list">
                <li>先在「节点与宝塔 → 宝塔列表」中添加一台可用宝塔面板。</li>
                <li>进入「主机管理 → 添加主机」,填写主机域名、绑定目录、套餐空间等。</li>
                <li>选择对应的宝塔面板与节点,系统将通过宝塔 API 自动创建站点。</li>
                <li>配置数据库与 FTP(可选),提交后等待系统部署完成。</li>
                <li>部署完成后,可在主机列表中查看主机状态,并管理到期、续费等。</li>
              </ol>
            </div>
          </t-tab-panel>

          <!-- SWAPIDC 对接 -->
          <t-tab-panel value="swapidc" label="SWAPIDC 对接教程">
            <div class="tab-inner">
              <ol class="step-list">
                <li>下载 SWAPIDC 对接模块压缩包,解压后将文件上传至 SWAPIDC 对应目录。</li>
                <li>在 SWAPIDC 后台「模块管理」中启用 MNBT 对接模块。</li>
                <li>填写 MNBT 系统的 API 地址与本服务器的 API 密钥(在「API 设置」获取)。</li>
                <li>配置对应商品与套餐,绑定到 MNBT 中的程序或主机规格。</li>
                <li>测试下单流程,确认订单能正确回执到 MNBT 系统。</li>
              </ol>
              <div class="dl-row">
                <t-button theme="primary" @click="download('sw')">
                  <i class="mdi mdi-download"></i> 下载 SWAPIDC 对接模块
                </t-button>
              </div>
            </div>
          </t-tab-panel>

          <!-- 魔方对接 -->
          <t-tab-panel value="mofang" label="魔方对接教程">
            <div class="tab-inner">
              <ol class="step-list">
                <li>下载魔方对接模块压缩包,按说明上传到魔方系统对应目录。</li>
                <li>在魔方后台「服务器管理」新增服务器,接口地址填写本 MNBT 系统 API。</li>
                <li>填入在「API 设置」中获取的 API 密钥,并配置好套餐映射。</li>
                <li>保存后进行连接测试,确认接口通信正常。</li>
                <li>在前台下单测试,验证开通 / 到期 / 续费流程是否同步。</li>
              </ol>
              <div class="dl-row">
                <t-button theme="primary" @click="download('mr')">
                  <i class="mdi mdi-download"></i> 下载魔方对接模块
                </t-button>
              </div>
            </div>
          </t-tab-panel>
        </t-tabs>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'

const boot = window.__TD_BOOT__ || {}
const conf = boot.conf || {}

const serverProto = boot.serverProto || window.location.protocol.replace(':', '')
const serverHost = boot.serverHost || window.location.host

const baseHttpUrl = `${serverProto}://${serverHost}/`

const apiKey = computed(() => conf.api || '')

const active = ref('monitor')

const monitorItems = computed(() => {
  if (!apiKey.value) return []
  const my = apiKey.value
  const items = [
    {
      title: '网页监控',
      path: 'jk.php?my=' + my + '&gn=web',
      desc: '上报网页可用性数据,统计站点在线状态',
      freq: '建议频率:1 分钟 / 次',
    },
    {
      title: 'SQL 监控',
      path: 'jk.php?my=' + my + '&gn=sql',
      desc: '上报数据库可用性与连接耗时',
      freq: '建议频率:1 分钟 / 次',
    },
    {
      title: '负载监控',
      path: 'jk.php?my=' + my + '&gn=fh',
      desc: '上报系统负载、CPU、内存使用率',
      freq: '建议频率:1 分钟 / 次',
    },
    {
      title: '负载历史',
      path: 'jk.php?my=' + my + '&gn=fhq',
      desc: '查询历史负载曲线数据',
      freq: '查询接口,无需定时',
    },
    {
      title: '监控主机删除',
      path: 'jk.php?my=' + my + '&gn=ywjkdel',
      desc: '触发监控到期主机的删除 / 暂停策略',
      freq: '建议频率:1 小时 / 次',
    },
    {
      title: '综合监控入口',
      path: 'jk_monitor.php?my=' + my,
      desc: '综合监控上报与查询入口',
      freq: '视具体监控场景而定',
    },
  ]
  return items.map((it) => ({ ...it, url: baseHttpUrl + it.path }))
})

async function copy(text) {
  try {
    await navigator.clipboard.writeText(text)
    MessagePlugin.success('已复制')
  } catch (_) {
    // 兜底
    const ta = document.createElement('textarea')
    ta.value = text
    document.body.appendChild(ta)
    ta.select()
    try {
      document.execCommand('copy')
      MessagePlugin.success('已复制')
    } catch (_) {
      MessagePlugin.error('复制失败,请手动复制')
    }
    document.body.removeChild(ta)
  }
}

function download(ne) {
  window.open('./wjxz.php?ne=' + encodeURIComponent(ne), '_blank')
}
</script>

<style scoped>
.tab-inner {
  padding: 8px 4px 4px;
}
.mono-block {
  margin-bottom: 16px;
}
.mono-head {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 4px;
  font-size: 13px;
}
.mono-head strong {
  color: var(--td-text);
}
.mono-desc {
  margin-bottom: 6px;
}
.mono-url {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}
.mono-url > span {
  flex: 1;
  min-width: 0;
  word-break: break-all;
}
.step-list {
  margin: 0;
  padding-left: 20px;
  line-height: 1.9;
  font-size: 13px;
  color: var(--td-text);
}
.step-list li {
  margin-bottom: 6px;
}
.dl-row {
  margin-top: 14px;
}
</style>
