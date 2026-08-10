import { apiGet, apiPost } from './http'

/**
 * official_site 插件 API（官网内容）
 * 依赖插件启用：official_site
 */

/** 产品列表（可选 ?category= 筛选，返回 products + categories） */
export function getSiteProducts(category = '') {
  return apiGet('/site/api/products', category ? { category } : {})
}

/** 产品详情 */
export function getSiteProduct(productId) {
  return apiGet(`/site/api/products/${productId}`)
}

/** 新闻分页列表（page / per_page / category） */
export function getSiteNews({ page = 1, perPage = 6, category = '' } = {}) {
  return apiGet('/site/api/news', {
    page,
    per_page: perPage,
    ...(category ? { category } : {}),
  })
}

/** 热门新闻（按浏览量） */
export function getSiteNewsPopular() {
  return apiGet('/site/api/news/popular')
}

/** 新闻详情（累计浏览量） */
export function getSiteNewsDetail(newsId) {
  return apiGet(`/site/api/news/${newsId}`)
}

/** 提交联系留言 */
export function submitSiteContact(form) {
  return apiPost('/site/api/contact', form)
}
