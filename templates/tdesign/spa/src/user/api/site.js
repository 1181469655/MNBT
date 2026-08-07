import { apiGn } from '@/shared/api/http'

// ============ PHP 版本 ============
/** 获取 PHP 版本列表 (gn=set_init, section=php) */
export function getPhpList() {
  return apiGn('set_init', { section: 'php' }, { silent: true })
}
/** 切换 PHP 版本 (gn=phpxg, php=版本号) */
export function setPhpVersion(php) {
  return apiGn('phpxg', { php })
}

// ============ 域名 ============
/** 获取域名绑定列表 (gn=listurl) */
export function getDomainList() {
  return apiGn('listurl', {}, { silent: true })
}
/** 获取域名解析列表 (gn=hqzmlls) */
export function getDomainDnsList() {
  return apiGn('hqzmlls', {}, { silent: true })
}

// ============ 密码访问 ============
/** 获取密码访问目录列表 (gn=pass_list) */
export function getPassList() {
  return apiGn('pass_list', {}, { silent: true })
}
/** 添加密码访问目录 (gn=tjmmfw, name, mbml, user, pass) */
export function addPassDir(name, mbml, user, pass) {
  return apiGn('tjmmfw', { name, mbml, user, pass })
}
/** 删除密码访问目录 (gn=scmmfw, mb=目录名) */
export function delPassDir(mb) {
  return apiGn('scmmfw', { mb })
}

// ============ 默认文档 ============
/** 获取默认文档 (gn=set_init, section=mrwd) */
export function getDefaultDoc() {
  return apiGn('set_init', { section: 'mrwd' }, { silent: true })
}
/** 修改默认文档 (gn=xgmrwd, ml=文档内容) */
export function setDefaultDoc(ml) {
  return apiGn('xgmrwd', { ml })
}

// ============ 运行目录 ============
/** 获取运行目录 (gn=set_init, section=yxml) */
export function getRunDir() {
  return apiGn('set_init', { section: 'yxml' }, { silent: true })
}
/** 设置运行目录 (gn=setyxml, wb=目录路径) */
export function setRunDir(wb) {
  return apiGn('setyxml', { wb })
}

// ============ 伪静态 ============
/** 获取伪静态规则模板列表 (gn=set_init, section=wjt) */
export function getRewriteTemplates() {
  return apiGn('set_init', { section: 'wjt' }, { silent: true })
}
/** 获取伪静态规则内容 (gn=hqjt, xz=规则名; xz='0.当前' 读取当前站点规则) */
export function getRewrite(xz = '0.当前') {
  return apiGn('hqjt', { xz }, { silent: true })
}
/** 设置伪静态规则 (gn=setwjt, wb=规则内容) */
export function setRewrite(wb) {
  return apiGn('setwjt', { wb })
}

// ============ SSL ============
/** 获取 SSL 配置 (gn=getssl) */
export function getSsl() {
  return apiGn('getssl', {}, { silent: true })
}
/** 设置 SSL 证书 (gn=setssl, key=密钥, pem=证书PEM) */
export function setSsl(key, pem) {
  return apiGn('setssl', { key, pem })
}
/** 关闭 SSL (gn=clossl) */
export function closeSsl() {
  return apiGn('clossl')
}
/** 申请/续签 Let's Encrypt SSL (gn=sqssl, list=域名数组, type=false申请/true续签) */
export function applySsl(list, type = false) {
  return apiGn('sqssl', { list, type: type ? 'true' : 'false' })
}
/** 强制 HTTPS (gn=httpsqz, qk=开关) */
export function forceHttps(enabled) {
  return apiGn('httpsqz', { qk: enabled ? 'true' : 'false' })
}

// ============ 防盗链 ============
/** 获取防盗链配置 (gn=getfdl) */
export function getHotlink() {
  return apiGn('getfdl', {}, { silent: true })
}
/** 设置防盗链 (gn=fdlkg, fix=后缀, domains=域名, status=开关, return_rule, http_status) */
export function setHotlink(fix, domains, status, return_rule = '', http_status = '') {
  return apiGn('fdlkg', { fix, domains, status, return_rule, http_status })
}

// ============ Gzip ============
/** 设置 Gzip (gn=setgzip) */
export function setGzip() {
  return apiGn('setgzip')
}

// ============ 缓存 ============
/** 设置缓存规则 (gn=cache, suffix=后缀, time_out=缓存时间) */
export function setCache(suffix, time_out) {
  return apiGn('cache', { suffix, time_out })
}

// ============ 修改密码 ============
/** 修改 FTP/SQL 密码 (gn=xgpass, ftp=FTP密码, sql=SQL密码) */
export function changePassword(ftp = '', sql = '') {
  return apiGn('xgpass', { ftp, sql })
}

// ============ SQL 权限 ============
/** 设置数据库权限 (gn=databaseaq1) */
export function setSqlAuth() {
  return apiGn('databaseaq1')
}

// ============ 邮箱绑定 ============
/** 绑定邮箱 (gn=mailbd, mail=邮箱地址) */
export function bindEmail(mail) {
  return apiGn('mailbd', { mail })
}
