<?php
if (!defined('IN_CRONLITE')) exit();

/**
 * 宝塔面板反向代理 API 封装类
 *
 * 独立类，专用于宝塔反向代理模块（/mod/proxy/com/）。
 * 签名算法与 bt_docker/bt_api 一致，transport 自持。
 *
 * 参考文档：https://docs.bt.cn/category/proxy-%E5%8F%8D%E5%90%91%E4%BB%A3%E7%90%86
 */

class bt_proxy
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
    //  反向代理站点管理
    // ========================================================================

    /**
     * 获取反向代理站点列表
     * @param int    $p      页码，默认 1
     * @param int    $limit  每页数量，默认 100
     * @param string $search 搜索关键词
     * @return array
     */
    public function proxy_list($p = 1, $limit = 100, $search = '')
    {
        $params = ['p' => $p, 'limit' => $limit];
        if ($search !== '') $params['search'] = $search;
        return $this->HttpPost('get_list', $params);
    }

    /**
     * 创建反向代理站点
     * @param array $params domains（换行分隔）, proxy_pass, proxy_path（默认/）, remark（可选）
     * @return array
     */
    public function proxy_create($params)
    {
        return $this->HttpPost('create', $params);
    }

    /**
     * 删除反向代理站点
     * @param int    $id        站点 ID
     * @param string $site_name 站点名称
     * @return array
     */
    public function proxy_delete($id, $site_name)
    {
        return $this->HttpPost('delete', [
            'id'        => $id,
            'site_name' => $site_name,
            'remove_path' => 0,
        ]);
    }

    // ========================================================================
    //  内部工具（签名与请求 transport，自持）
    // ========================================================================

    private function GetKeyData()
    {
        $now_time = time();
        return [
            'request_token' => md5($now_time . '' . md5($this->BT_KEY)),
            'request_time'  => $now_time,
        ];
    }

    /**
     * POST 请求 /mod/proxy/com/<method>/stype
     */
    private function HttpPost($method, $params = [], $timeout = 60)
    {
        $url = $this->BT_PANEL . '/mod/proxy/com/' . $method . '/stype';
        $data = array_merge($this->GetKeyData(), $params);
        $r = $this->request($url, $data, $timeout);
        if (is_array($r) && isset($r['data']) && is_array($r['data'])) {
            return $r['data'];
        }
        return $r;
    }

    /**
     * 统一 cURL 请求（带 cookie jar）
     */
    private function request($url, $postData, $timeout)
    {
        $cookie_file = ROOT . 'api/cookie/' . md5($this->BT_PANEL) . '.cookie';
        if (!is_dir(dirname($cookie_file))) {
            @mkdir(dirname($cookie_file), 0755, true);
        }
        if (!file_exists($cookie_file)) {
            $fp = fopen($cookie_file, 'w+');
            if (is_resource($fp)) {
                fclose($fp);
            }
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