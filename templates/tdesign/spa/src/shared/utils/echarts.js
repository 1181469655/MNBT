/**
 * echarts 引入（V1.87 起 SPA 由 vue3-sfc-loader 免构建加载）
 * UMD 全量构建已预注册全部图表/组件，这里直接复用全局 echarts，
 * 不再从 echarts/core 等子路径按需引入（loader 模式下无摇树）。
 */
const echarts = window.echarts || {}

export default echarts
export { echarts }
