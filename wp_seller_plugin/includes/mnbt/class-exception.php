<?php
/**
 * MNBT 对接异常：错误归一化后统一抛出。
 *
 * @package MnbtWp
 */

namespace MnbtWp\Mnbt;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MNBT API 异常。
 */
class Exception extends \Exception {

	/**
	 * 原始响应数据（脱敏后），便于上层记录上下文。
	 *
	 * @var array
	 */
	protected $context = array();

	/**
	 * 设置上下文。
	 *
	 * @param array $context 上下文。
	 * @return $this
	 */
	public function set_context( array $context ) {
		$this->context = $context;
		return $this;
	}

	/**
	 * 获取上下文。
	 *
	 * @return array
	 */
	public function get_context() {
		return $this->context;
	}
}
