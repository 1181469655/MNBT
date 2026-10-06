import { apiGn } from '@/shared/api/http'

// 站点统计 (gn=site_stats, act=overview/trend/ip_rank/uri_rank/errors, range=today/yesterday/7days/30days)
export function getSiteStats(act = 'overview', range = 'today', page = 1, page_size = 15) {
  return apiGn('site_stats', { act, range, page, page_size }, { silent: true })
}
