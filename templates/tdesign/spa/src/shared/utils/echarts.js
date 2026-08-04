/**
 * echarts 按需引入
 * 同时支持管理端仪表盘 (Gauge) 与用户端流量趋势图 (Bar/Line)
 */
import * as echarts from 'echarts/core'
import { BarChart, GaugeChart, LineChart } from 'echarts/charts'
import { CanvasRenderer } from 'echarts/renderers'
import {
  GridComponent,
  LegendComponent,
  TitleComponent,
  TooltipComponent,
} from 'echarts/components'

echarts.use([
  BarChart,
  GaugeChart,
  LineChart,
  CanvasRenderer,
  GridComponent,
  LegendComponent,
  TitleComponent,
  TooltipComponent,
])

export default echarts
export { echarts }
