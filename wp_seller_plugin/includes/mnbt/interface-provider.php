<?php
/**
 * 主机服务商适配器接口。
 *
 * 业务层只依赖本接口，便于未来扩展其他面板/系统
 * （如直接对接宝塔、其他财务系统），不修改业务代码。
 *
 * @package MnbtWp
 */

namespace MnbtWp\Mnbt;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 主机服务商接口。
 */
interface HostProviderInterface {

	/**
	 * 连接测试。
	 *
	 * @return array ['ok'=>bool, 'msg'=>string, 'data'=>array]
	 */
	public function testConnection();

	/**
	 * 开通主机（建站 + FTP + 数据库）。
	 *
	 * @param array $params 开通参数（password/sizemax/dqtime/webdx/sqldx/ymbds 等）。
	 * @return array
	 */
	public function createHost( array $params );

	/**
	 * 暂停主机。
	 *
	 * @param string $username 主机用户名。
	 * @return array
	 */
	public function suspendHost( $username );

	/**
	 * 恢复主机。
	 *
	 * @param string $username 主机用户名。
	 * @return array
	 */
	public function resumeHost( $username );

	/**
	 * 删除主机。
	 *
	 * @param string $username 主机用户名。
	 * @return array
	 */
	public function deleteHost( $username );

	/**
	 * 续费（更新到期时间）。
	 *
	 * @param string $username   主机用户名。
	 * @param string $expire_date 到期日期 Y-m-d。
	 * @return array
	 */
	public function renewHost( $username, $expire_date );

	/**
	 * 重置密码（FTP + 控制面板）。
	 *
	 * @param string $username 主机用户名。
	 * @param string $password 新密码。
	 * @return array
	 */
	public function changePassword( $username, $password );

	/**
	 * 升降级（更新配额）。
	 *
	 * @param string $username 主机用户名。
	 * @param array  $quota    配额 ['websize'=>, 'sqlsize'=>, 'll'=>]（MB）。
	 * @return array
	 */
	public function changePackage( $username, array $quota );

	/**
	 * 启动站点。
	 *
	 * @param string $username 主机用户名。
	 * @return array
	 */
	public function startSite( $username );

	/**
	 * 停止站点。
	 *
	 * @param string $username 主机用户名。
	 * @return array
	 */
	public function stopSite( $username );

	/**
	 * 查询主机状态与配额用量。
	 *
	 * @param string $username 主机用户名。
	 * @return array
	 */
	public function getHostStatus( $username );
}
