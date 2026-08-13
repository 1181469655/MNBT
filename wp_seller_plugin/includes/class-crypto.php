<?php
/**
 * 密钥加解密工具：基于 WordPress 盐派生密钥，AES-256-CBC 加密。
 *
 * @package MnbtWp
 */

namespace MnbtWp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 密钥加解密。
 */
class Crypto {

	/**
	 * 派生 32 字节 AES 密钥（基于 wp_salt('auth')，站点级唯一）。
	 *
	 * @return string
	 */
	private static function key() {
		return substr( hash( 'sha256', wp_salt( 'auth' ) ), 0, 32 );
	}

	/**
	 * 加密明文。
	 *
	 * @param string $plain 明文。
	 * @return string 密文（base64），空串原样返回。
	 */
	public static function encrypt( $plain ) {
		$plain = (string) $plain;
		if ( '' === $plain ) {
			return '';
		}
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			return base64_encode( $plain );
		}
		$iv     = openssl_random_pseudo_bytes( 16 );
		$cipher = openssl_encrypt( $plain, 'AES-256-CBC', self::key(), 0, $iv );
		if ( false === $cipher ) {
			return '';
		}
		return base64_encode( $iv . $cipher );
	}

	/**
	 * 解密密文。
	 *
	 * @param string $enc 密文（base64）。
	 * @return string 明文，失败返回空串。
	 */
	public static function decrypt( $enc ) {
		$enc = (string) $enc;
		if ( '' === $enc ) {
			return '';
		}
		$raw = base64_decode( $enc, true );
		if ( false === $raw || strlen( $raw ) < 16 ) {
			return '';
		}
		$iv     = substr( $raw, 0, 16 );
		$cipher = substr( $raw, 16 );
		if ( ! function_exists( 'openssl_decrypt' ) ) {
			return '';
		}
		$plain = openssl_decrypt( $cipher, 'AES-256-CBC', self::key(), 0, $iv );
		return ( false === $plain ) ? '' : $plain;
	}
}
