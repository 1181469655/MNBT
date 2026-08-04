import { apiGn } from '@/shared/api/http'

// 站点统计 (gn=site_stats, act=overview/trend/ip_rank/uri_rank/errors, range=today/yesterday/7days/30days)
export function getSiteStats(act = 'overview', range = 'today') {
  return apiGn('site_stats', { act, range }, { silent: true })
}
