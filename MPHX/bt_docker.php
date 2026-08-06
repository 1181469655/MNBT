<?php
if (!defined('IN_CRONLITE')) exit();

/**
 * 宝塔面板 Docker API 封装类
 *
 * 独立于 bt_api，专用于宝塔 Docker 模块。
 * - GET  路由：/btdocker/<module>/<method>，签名(request_token/request_time)拼入 query string + cookie
 * - POST 路由：/mod/docker/com/<method>/stype，签名入 body + cookie
 *
 * 签名算法与 bt_api 一致（md5(time . md5(key)) + time），但 transport 自持，不修改 bt_api。
 *
 * 参考文档：https://docs.bt.cn/api/docker/
 * 注意：宝塔官方注明 Docker 接口可能随版本变更、不保证稳定性，仅供 MNBT 内部使用。
 */

class bt_docker
{
    /** @var string 宝塔面板地址（含协议+端口，无尾斜杠） */
    public $BT_PANEL;
    /** @var string 宝塔接口密钥 */
    public $BT_KEY;

    public function __construct($bt_panel = null, $bt_key = null)
    {
        $this->BT_PANEL = rtrim((string)$bt_panel, '/');
        $this->BT_KEY = (string)$bt_key;
    }

    // ========================================================================
    //  安装与配置（GET /btdocker/setup/）
    // ========================================================================

    /** Docker 服务状态（service_status / docker_installed / docker_compose_installed） */
    public function get_config()
    {
        return $this->HttpGet('setup', 'get_config');
    }

    /** 安装 Docker 程序 */
    public function install_docker_program()
    {
        return $this->HttpGet('setup', 'install_docker_program');
    }

    /** 镜像加速配置 */
    public function get_registry_mirrors()
    {
        return $this->HttpGet('setup', 'get_registry_mirrors');
    }

    /** 设置镜像加速 */
    public function set_registry_mirrors($mirrors)
    {
        return $this->HttpGet('setup', 'set_registry_mirrors', ['mirrors' => $mirrors]);
    }

    /** 监控数据保留天数 */
    public function set_monitor_save_date($day)
    {
        return $this->HttpGet('setup', 'set_monitor_save_date', ['day' => $day]);
    }

    // ========================================================================
    //  容器管理（GET /btdocker/container/）
    // ========================================================================

    /** 所有容器列表 */
    public function container_list()
    {
        return $this->HttpGet('container', 'get_list');
    }

    /** 启动容器 */
    public function container_start($id, $name)
    {
        return $this->HttpGet('container', 'start', ['id' => $id, 'name' => $name]);
    }

    /** 停止容器 */
    public function container_stop($id, $name)
    {
        return $this->HttpGet('container', 'stop', ['id' => $id, 'name' => $name]);
    }

    /** 重启容器 */
    public function container_restart($id, $name)
    {
        return $this->HttpGet('container', 'restart', ['id' => $id, 'name' => $name]);
    }

    /** 删除容器（按 id+name） */
    public function container_del($id, $name)
    {
        return $this->HttpGet('container', 'del', ['id' => $id, 'name' => $name]);
    }

    /** 清理无用容器 */
    public function container_prune()
    {
        return $this->HttpGet('container', 'prune');
    }

    /** 容器执行日志（轮询安装进度用） */
    public function container_cmd_log($id = null, $name = null)
    {
        $params = [];
        if ($id !== null) $params['id'] = $id;
        if ($name !== null) $params['name'] = $name;
        return $this->HttpGet('container', 'get_cmd_log', $params);
    }

    // ========================================================================
    //  镜像管理（GET /btdocker/image/）
    // ========================================================================

    /** 本地镜像列表 */
    public function image_list()
    {
        return $this->HttpGet('image', 'image_list');
    }

    /** 清理无用镜像 */
    public function image_prune()
    {
        return $this->HttpGet('image', 'prune');
    }

    // ========================================================================
    //  存储卷（GET /btdocker/volume/）
    // ========================================================================

    /** 存储卷列表 */
    public function volume_list()
    {
        return $this->HttpGet('volume', 'get_volume_list');
    }

    /** 创建存储卷 */
    public function volume_add($name, $driver = 'local')
    {
        return $this->HttpGet('volume', 'add', ['name' => $name, 'driver' => $driver]);
    }

    /** 清理无用存储卷 */
    public function volume_prune()
    {
        return $this->HttpGet('volume', 'prune');
    }

    // ========================================================================
    //  网络（GET /btdocker/network/）
    // ========================================================================

    /** 网络列表 */
    public function network_list()
    {
        return $this->HttpGet('network', 'get_host_network');
    }

    /** 创建网络 */
    public function network_create($name, $driver = 'bridge')
    {
        return $this->HttpGet('network', 'create_network', ['name' => $name, 'driver' => $driver]);
    }

    /** 清理无用网络 */
    public function network_prune()
    {
        return $this->HttpGet('network', 'prune');
    }

    // ========================================================================
    //  仓库（GET /btdocker/registry/）
    // ========================================================================

    /** 镜像仓库列表 */
    public function registry_list()
    {
        return $this->HttpGet('registry', 'registry_list');
    }

    /** 设置仓库备注 */
    public function registry_set_remark($id, $remark)
    {
        return $this->HttpGet('registry', 'set_remark', ['id' => $id, 'remark' => $remark]);
    }

    // ========================================================================
    //  Compose 模板与项目（GET /btdocker/compose|project/）
    // ========================================================================

    /** Compose 模板列表 */
    public function template_list()
    {
        return $this->HttpGet('compose', 'template_list');
    }

    /** Docker 项目列表 */
    public function project_list()
    {
        return $this->HttpGet('project', 'get_project_list');
    }

    // ========================================================================
    //  应用商店（POST /mod/docker/com/）
    // ========================================================================

    /**
     * 应用列表（289 个应用及参数定义）
     * 返回 data 数组，每项含 appname/apptitle/apptype/appversion/depend/env/field
     */
    public function app_list()
    {
        return $this->HttpPost('get_apps');
    }

    /**
     * 安装应用（异步任务：返回"等待 1-5 分钟初始化"，前端轮询 get_cmd_log 跟进）
     * @param array $params create_app 参数（app_name/service_name/m_version/s_version/allow_access/cpus/memory_limit 及应用专属参数）
     */
    public function app_create($params)
    {
        return $this->HttpPost('create_app', $params);
    }

    /** 查询依赖应用安装状态 */
    public function app_dependence($app)
    {
        return $this->HttpPost('get_dependence_apps', ['app_name' => $app]);
    }

    /**
     * 已安装应用列表（含容器详情、端口、应用参数）
     * 返回 data 数组，每项含 service_name/appname/apptitle/status/port[]/host_ip/server_ip/container_id/appinfo[] 等
     * 宝塔 get_list 的 ports 字段为空数组，端口信息实际来自此接口
     */
    public function installed_apps()
    {
        return $this->HttpPost('get_installed_apps');
    }

    /** 应用商店配置 */
    public function apphub_config()
    {
        return $this->HttpPost('get_apphub_config');
    }

    /** 初始化应用商店环境 */
    public function install_apphub()
    {
        return $this->HttpPost('install_apphub');
    }

    /**
     * 卸载应用（删除容器及数据）
     * @param array $params remove_app 参数（service_name 必填）
     */
    public function app_remove($params)
    {
        return $this->HttpPost('remove_app', $params);
    }

    // ========================================================================
    //  文件/磁盘（GET /files?action=get_path_size）
    // ========================================================================

    /**
     * 获取指定路径的磁盘占用大小（字节）
     * 调用宝塔面板文件 API：/files?action=get_path_size
     * @param string $path 容器安装目录（来自 get_installed_apps 的 path 字段）
     * @return array 含 size（字节）字段
     */
    public function get_path_size($path)
    {
        $keyData = $this->GetKeyData();
        $keyData['path'] = $path;
        $url = $this->BT_PANEL . '/files?action=get_path_size';
        return $this->request($url, $keyData, 30);
    }

    // ========================================================================
    //  内部工具（签名与请求 transport，自持，不改 bt_api）
    // ========================================================================

    /**
     * 签名数据（与 bt_api 一致：request_token = md5(time . md5(key))，request_time = time）
     */
    private function GetKeyData()
    {
        $now_time = time();
        return [
            'request_token' => md5($now_time . '' . md5($this->BT_KEY)),
            'request_time'  => $now_time,
        ];
    }

    /**
     * GET 请求 /btdocker/<module>/<method>，签名入 query string + cookie
     * @param string $module 模块（setup/container/image/volume/network/registry/compose/project）
     * @param string $method 方法名
     * @param array  $params 业务参数
     * @param int    $timeout
     * @return array 解码后的响应
     */
    private function HttpGet($module, $method, $params = [], $timeout = 30)
    {
        $query = array_merge($this->GetKeyData(), $params);
        $url = $this->BT_PANEL . '/btdocker/' . $module . '/' . $method . '?' . http_build_query($query);
        return $this->request($url, null, $timeout);
    }

    /**
     * POST 请求 /mod/docker/com/<method>/stype，签名入 body + cookie
     * @param string $method 方法名（get_apps/create_app/get_dependence_apps 等）
     * @param array  $params 业务参数
     * @param int    $timeout
     * @return array 解码后的响应
     */
    private function HttpPost($method, $params = [], $timeout = 60)
    {
        $url = $this->BT_PANEL . '/mod/docker/com/' . $method . '/stype';
        $data = array_merge($this->GetKeyData(), $params);
        $r = $this->request($url, $data, $timeout);
        // 宝塔 POST 接口通常返回 {status, msg, data} 包装，有 data 数组时提取
        if (is_array($r) && isset($r['data']) && is_array($r['data'])) {
            return $r['data'];
        }
        return $r;
    }

    /**
     * 统一 cURL 请求（带 cookie jar，复用 bt_api 的 cookie 目录）
     * @param string      $url     完整 URL
     * @param array|null  $postData null=GET，数组=POST
     * @param int         $timeout
     * @return array 解码后的响应；解析失败时返回原始字符串包一层
     */
    private function request($url, $postData, $timeout)
    {
        $cookie_file = ROOT . 'api/cookie/' . md5($this->BT_PANEL) . '.cookie';
        if (!is_dir(dirname($cookie_file))) {
            @mkdir(dirname($cookie_file), 0755, true);
        }
        if (!file_exists($cookie_file)) {
            $fp = fopen($cookie_file, 'w+');
            fclose($fp);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($postData !== null) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            return ['status' => false, 'msg' => 'cURL 错误(' . $errno . ')：' . $error];
        }

        $decoded = json_decode($output, true);
        return $decoded !== null ? $decoded : ['status' => false, 'msg' => '响应解析失败', 'raw' => $output];
    }
}
