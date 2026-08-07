import { apiGn } from '@/shared/api/http'

const S = { silent: true }

/** 支付方式列表(若支付插件提供) */
export function listPaymentMethods() {
  return apiGn('list_payment_methods', {}, S)
}

/** 保存支付方式 */
export function savePaymentMethods(methods) {
  return apiGn('setpaymethods', { methods })
}
