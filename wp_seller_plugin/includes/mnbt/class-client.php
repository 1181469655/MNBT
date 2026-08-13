<?php
/**
 * MNBT API 客户端（适配器实现）。
 *
 * 封装 MNBT `api/api.php` 的鉴权、请求、重试与错误归一化。
 * 鉴权参数：mn_bh（节点代号）/ mn_key（系统密钥）/ mn_keye（调用密钥 md5(ktmy.qmk)）/ mn_vs（版本号）。
 *
 * @package MnbtWp
 */

namespace MnbtWp\Mnbt;

use MnbtWp\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MNBT API 客户端。
 */
class Client implements HostProviderInterface {

	/**
	 * 节点配置。
	 *
	 * @var array
	 */
	private $config = array();

	/**
	 * 构造。
	 *
	 * @param array $config 节点配置（api_url/btdh/mn_key/mn_keye/mn_vs/provider_id）。
	 */
	public function __construct( array $config ) {
		$this->config = wp_parse_args(
			$config,
			array(
				'api_url'     => '',
				'btdh'        => '',
				'mn_key'      => '',
				'mn_keye'     => '',
				'mn_vs'       => 15,
				'provider_id' => 0,
			)
		);
	}

	/**
	 * 拼接 API 地址。
	 *
	 * @return string
	 */
	private function url() {
		return untrailingslashit( $this->config['api_url'] ) . '/api/api.php';
	}

	/**
	 * 连接测试。
	 *
	 * @return array
	 */
	public function testConnection() {
		return $this->request( 'cfif', array( 'username' => 'mnbtwp_test' ) );
	}

	/**
	 * 开通主机。
	 *
	 * @param array $params 开通参数。
	 * @return array
	 */
	public function createHost( array $params ) {
		$params = wp_parse_args(
			$params,
			array(
				'password' => '',
				'sizemax'  => 0,
				'dqtime'   => 0,
				'webdx'    => 0,
				'sqldx'    => 0,
				'ymbds'    => 0,
			)
		);
		return $this->request( 'kt', $params );
	}

	/**
	 * 暂停主机。
	 *
	 * @param string $username 主机用户名。
	 * @return array
	 */
	public function suspendHost( $username ) {
		return $this->request( 'zt', array( 'username' => $username ) );
	}

	/**
	 * 恢复主机。
	 *
	 * @param string $username 主机用户名。
	 * @return array
	 */
	public function resumeHost( $username ) {
		return $this->request( 'jc', array( 'username' => $username ) );
	}

	/**
	 * 删除主机。
	 *
	 * @param string $username 主机用户名。
	 * @return array
	 */
	public function deleteHost( $username ) {
		return $this->request( 'tz', array( 'username' => $username ) );
	}

	/**
	 * 续费。
	 *
	 * @param string $username    主机用户名。
	 * @param string $expire_date 到期日期 Y-m-d。
	 * @return array
	 */
	public function renewHost( $username, $expire_date ) {
		return $this->request(
			'xf',
			array(
				'username' => $username,
				'setdate'  => $expire_date,
			)
		);
	}

	/**
	 * 重置密码。
	 *
	 * @param string $username 主机用户名。
	 * @param string $password 新密码。
	 * @return array
	 */
	public function changePassword( $username, $password ) {
		return $this->request(
			'czmm',
			array(
				'username' => $username,
				'password' => $password,
			)
		);
	}

	/**
	 * 升降级。
	 *
	 * @param string $username 主机用户名。
	 * @param array  $quota    配额（websize/sqlsize/ll，MB）。
	 * @return array
	 */
	public function changePackage( $username, array $quota ) {
		$quota = wp_parse_args(
			$quota,
			array(
				'websize' => 0,
				'sqlsize' => 0,
				'll'      => 0,
			)
		);
		return $this->request(
			'zjmode',
			array(
				'username' => $username,
				'websize'  => $quota['websize'],
				'sqlsize'  => $quota['sqlsize'],
				'll'       => $quota['ll'],
			)
		);
	}

	/**
	 * 启动站点。
	 *
	 * @param string $username 主机用户名。
	 * @return array
	 */
	public function startSite( $username ) {
		return $this->request( 'start', array( 'username' => $username ) );
	}

	/**
	 * 停止站点。
	 *
	 * @param string $username 主机用户名。
	 * @return array
	 */
	public function stopSite( $username ) {
		return $this->request( 'stop', array( 'username' => $username ) );
	}

	/**
	 * 状态与配额查询。
	 *
	 * @param string $username 主机用户名。
	 * @return array
	 */
	public function getHostStatus( $username ) {
		return $this->request( 'ztcx', array( 'username' => $username ) );
	}

	/**
	 * 发起 MNBT API 请求。
	 *
	 * 网络层失败（超时/连接失败）自动重试 2 次（1s/2s 退避）；
	 * 业务失败（code!=200）不重试，抛出异常。
	 *
	 * @param string $gn     API 动作。
	 * @param array  $params 业务参数。
	 * @return array ['ok'=>true, 'msg'=>string, 'data'=>array]
	 *
	 * @throws Exception 请求失败时抛出。
	 */
	private function request( $gn, array $params ) {
		$body = array_merge(
			array(
				'mn_bh'  => $this->config['btdh'],
				'mn_key' => $this->config['mn_key'],
				'mn_keye' => $this->config['mn_keye'],
				'mn_vs'  => (int) $this->config['mn_vs'],
			),
			$params
		);
		if ( empty( $body['username'] ) ) {
			$body['username'] = 'mnbtwp_test';
		}

		$start       = microtime( true );
		$response    = null;
		$last_error  = '';

		// 网络层失败重试（最多 3 次尝试）。
		for ( $attempt = 0; $attempt < 3; $attempt++ ) {
			if ( $attempt > 0 ) {
				usleep( $attempt * 1000000 ); // 1s / 2s 退避。
			}
			$response = wp_remote_post(
				$this->url(),
				array(
					'timeout'   => 15,
					'body'      => $body,
					'sslverify' => true,
				)
			);
			if ( ! is_wp_error( $response ) ) {
				break;
			}
			$last_error = $response->get_error_message();
		}

		$duration = microtime( true ) - $start;

		if ( is_wp_error( $response ) ) {
			Logger::api( (int) $this->config['provider_id'], $gn, $body, 0, '网络错误：' . $last_error, $duration );
			throw ( new Exception( '网络错误：' . $last_error ) )->set_context(
				array( 'gn' => $gn, 'provider_id' => $this->config['provider_id'] )
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			Logger::api( (int) $this->config['provider_id'], $gn, $body, 0, 'HTTP ' . $code, $duration );
			throw ( new Exception( 'MNBT 接口返回 HTTP ' . $code ) )->set_context(
				array( 'gn' => $gn, 'provider_id' => $this->config['provider_id'], 'http_code' => $code )
			);
		}

		$raw  = wp_remote_retrieve_body( $response );
		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) ) {
			Logger::api( (int) $this->config['provider_id'], $gn, $body, 0, '响应解析失败', $duration );
			throw ( new Exception( 'MNBT 响应解析失败' ) )->set_context(
				array( 'gn' => $gn, 'provider_id' => $this->config['provider_id'] )
			);
		}

		$ok  = isset( $data['code'] ) && 200 === (int) $data['code'];
		$msg = isset( $data['msg'] ) ? (string) $data['msg'] : 'ok';

		Logger::api( (int) $this->config['provider_id'], $gn, $body, $ok ? 200 : 100, $msg, $duration );

		if ( ! $ok ) {
			throw ( new Exception( $msg ) )->set_context(
				array( 'gn' => $gn, 'provider_id' => $this->config['provider_id'], 'code' => isset( $data['code'] ) ? $data['code'] : null )
			);
		}

		return array(
			'ok'   => true,
			'msg'  => $msg,
			'data' => isset( $data['data'] ) && is_array( $data['data'] ) ? $data['data'] : array(),
		);
	}
}
