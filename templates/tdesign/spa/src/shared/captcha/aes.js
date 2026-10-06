import CryptoJS from 'crypto-js'

/**
 * 与服务端 AesUtils::encrypt 一致的 AES-128-ECB（Pkcs7）加密
 * @param {String} word 待加密内容
 * @param {String} keyWord 服务端返回的 16 位 secretKey
 */
export function aesEncrypt(word, keyWord = '') {
	const key = CryptoJS.enc.Utf8.parse(keyWord)
	const srcs = CryptoJS.enc.Utf8.parse(word)
	const encrypted = CryptoJS.AES.encrypt(srcs, key, {
		mode: CryptoJS.mode.ECB,
		padding: CryptoJS.pad.Pkcs7,
	})
	return encrypted.toString()
}
