<?php
if (!defined('IN_CRONLITE')) exit();

/**
 * 统一宝塔面板API操作类
 * 合并自: class.php, admin/class.php, user/class.php, api/api.class.php
 */

class bt_api
{
    public $BT_PANEL;
    public $BT_KEY;

    public function __construct($bt_panel = null, $bt_key = null)
    {
        $this->BT_PANEL = $bt_panel;
        $this->BT_KEY = $bt_key;
    }

    // ========================================================================
    //  站点管理
    // ========================================================================

    public function webkt($userq, $passq, $btserw, $cptypr, $ftpr, $sqlr, $mrbb, $mrml)
    {
        $url = $this->BT_PANEL . '/site?action=AddSite';
        $p_data = $this->GetKeyData();
        $p_data['webname'] = '{"domain":"' . $btserw . '","domainlist":[],"count":0}';
        $p_data['path'] = $mrml;
        $p_data['type_id'] = '0';
        $p_data['type'] = 'PHP';
        $p_data['version'] = $mrbb;
        $p_data['port'] = '80';
        $p_data['ps'] = 'MNBT开通的' . $cptypr;
        $p_data['ftp'] = $ftpr;
        $p_data['ftp_username'] = $userq;
        $p_data['ftp_password'] = $passq;
        $p_data['sql'] = $sqlr;
        $p_data['codeing'] = 'utf8';
        $p_data['datauser'] = $userq;
        $p_data['datapassword'] = $passq;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function delsite($id, $webname)
    {
        $url = $this->BT_PANEL . '/site?action=DeleteSite';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        $p_data['webname'] = $webname;
        $p_data['ftp'] = '1';
        $p_data['database'] = '1';
        $p_data['path'] = '1';
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function stopjq($id, $name)
    {
        return $this->siteqt($id, $name, false);
    }

    public function ztweb($id, $name)
    {
        return $this->siteqt($id, $name, false);
    }

    public function qdweb($id, $name)
    {
        return $this->siteqt($id, $name, true);
    }

    public function siteqt($id, $name, $start)
    {
        $url = $this->BT_PANEL . ($start ? '/site?action=SiteStart' : '/site?action=SiteStop');
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        $p_data['name'] = $name;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function setdqsj($id, $edate)
    {
        $url = $this->BT_PANEL . '/site?action=SetEdate';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        $p_data['edate'] = $edate;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function get_site_domains($siteid)
    {
        $url = $this->BT_PANEL . '/site?action=GetSiteDomains';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $siteid;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function sitemsg($sitename)
    {
        $url = $this->BT_PANEL . '/data?action=getData';
        $p_data = $this->GetKeyData();
        $p_data['table'] = 'sites';
        $p_data['limit'] = 200;
        $p_data['p'] = 1;
        $p_data['search'] = $sitename;
        $p_data['type'] = -1;
        $result = json_decode($this->HttpPostCookie($url, $p_data), true);
        $data = $result['data'] ?? [];
        if (empty($data)) return ['code' => false, 'msg' => '无主机信息！'];
        foreach ($data as $val) {
            if ($val['name'] == $sitename) return ['code' => true, 'msg' => $val];
        }
        return ['code' => false, 'msg' => '无主机信息！'];
    }

    // ========================================================================
    //  域名管理
    // ========================================================================

    public function btapi_ym($zdid)
    {
        return $this->get_domain_list($zdid);
    }

    public function GetLogsy($zdid)
    {
        $url = $this->BT_PANEL . '/data?action=getData&table=domain';
        $p_data = $this->GetKeyData();
        $p_data['search'] = $zdid;
        $p_data['list'] = 'true';
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function get_domain_list($search)
    {
        $url = $this->BT_PANEL . '/data?action=getData';
        $p_data = $this->GetKeyData();
        $p_data['search'] = $search;
        $p_data['list'] = 'True';
        $p_data['table'] = 'domain';
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function Getymlist($id)
    {
        return $this->get_domain_list($id);
    }

    public function btapi_addym($siteid, $webname, $domain)
    {
        if (strpos($domain, "\n") !== false) {
            return ['status' => false, 'msg' => '不能使用换行符！'];
        }
        $url = $this->BT_PANEL . '/site?action=AddDomain';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $siteid;
        $p_data['webname'] = $webname;
        $p_data['domain'] = $domain;
        $result = json_decode($this->HttpPostCookie($url, $p_data), true);
        return $result['domains'][0] ?? $result;
    }

    public function btapi_delym($siteid, $webname, $domain, $port)
    {
        $url = $this->BT_PANEL . '/site?action=DelDomain';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $siteid;
        $p_data['webname'] = $webname;
        $p_data['domain'] = $domain;
        $p_data['port'] = $port;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function urlzmlls($siteid)
    {
        $url = $this->BT_PANEL . '/site?action=GetDirBinding';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $siteid;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function addzml($siteid, $domain, $dir, $sitepath)
    {
        if (strpos($domain, "\n") !== false) {
            return ['status' => false, 'msg' => '不能使用换行符！'];
        }
        $url = $this->BT_PANEL . '/site?action=AddDirBinding';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $siteid;
        $p_data['domain'] = $domain;
        $p_data['dirName'] = $dir;
        $data = json_decode($this->HttpPostCookie($url, $p_data), true);
        if ($data['status']) {
            $getyxml = $this->yxmlrhq($siteid, $sitepath);
            if ($getyxml['runPath']['runPath'] != '/') {
                $this->filecopy($sitepath . $getyxml['runPath']['runPath'] . '/.user.ini', $sitepath . $getyxml['runPath']['runPath'] . '/' . $dir . '/.user.ini');
            } else {
                $this->filecopy($sitepath . $getyxml['runPath']['runPath'] . '.user.ini', $sitepath . $getyxml['runPath']['runPath'] . $dir . '/.user.ini');
            }
        }
        return $data;
    }

    public function delzml($siteid, $domain, $sitepath)
    {
        $zmllist = $this->urlzmlls($siteid);
        $getyxml = $this->yxmlrhq($siteid, $sitepath);
        $urlid = false;
        $urlpath = null;
        foreach ($zmllist['binding'] as $vals) {
            if ($vals['domain'] == $domain) {
                $urlid = $vals['id'];
                $urlpath = $vals['path'];
                break;
            }
        }
        if ($urlid === false) return ["status" => false, "msg" => '域名不存在！'];
        if (substr($urlpath, 0, 3) == '../') $urlpaths = substr($urlpath, 3);
        if ($urlpath != false) {
            if ($getyxml['runPath']['runPath'] != '/' . $urlpaths) {
                if ($getyxml['runPath']['runPath'] != '/') {
                    if (substr($urlpath, 0, 3) == '../') {
                        $urlpath = substr($urlpath, 3);
                        $this->delwj($sitepath . '/' . $urlpath . '/.user.ini');
                    } else {
                        $this->delwj($sitepath . $getyxml['runPath']['runPath'] . '/' . $urlpath . '/.user.ini');
                    }
                } else {
                    $this->delwj($sitepath . '/' . $urlpath . '/.user.ini');
                }
            }
        }
        $url = $this->BT_PANEL . '/site?action=DelDirBinding';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $urlid;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    // ========================================================================
    //  PHP版本管理
    // ========================================================================

    public function btapi_listphp()
    {
        $url = $this->BT_PANEL . '/site?action=GetPHPVersion';
        $p_data = $this->GetKeyData();
        $p_data['s_type'] = '1';
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function btapi_phpnowz($siteName)
    {
        $url = $this->BT_PANEL . '/site?action=GetSitePHPVersion';
        $p_data = $this->GetKeyData();
        $p_data['siteName'] = $siteName;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function btapi_setphp($siteName, $version)
    {
        $url = $this->BT_PANEL . '/site?action=SetPHPVersion';
        $p_data = $this->GetKeyData();
        $p_data['siteName'] = $siteName;
        $p_data['version'] = $version;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    /**
     * 一键部署专用：设置站点 PHP 版本
     * @param array $dat [站点名, PHP版本号]
     */
    public function setphp($dat)
    {
        $siteName = (string)($dat[0] ?? '');
        $version = (string)($dat[1] ?? '');
        if ($siteName === '' || $version === '') {
            return ['status' => false, 'msg' => '站点名或PHP版本号不能为空'];
        }
        return $this->btapi_setphp($siteName, $version);
    }

    // ========================================================================
    //  FTP管理
    // ========================================================================

    public function ftpcx($user)
    {
        $url = $this->BT_PANEL . '/data?action=getData';
        $p_data = $this->GetKeyData();
        $p_data['table'] = 'ftps';
        $p_data['search'] = $user;
        $p_data['limit'] = 100;
        $p_data['p'] = 1;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function ftpxg($id, $username, $status)
    {
        return $this->setftpzt($id, $username, $status);
    }

    public function setftpzt($id, $username, $status)
    {
        $url = $this->BT_PANEL . '/ftp?action=SetStatus';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        $p_data['username'] = $username;
        $p_data['status'] = $status;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function setftppass($id, $username, $password)
    {
        $url = $this->BT_PANEL . '/ftp?action=SetUserPassword';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        $p_data['ftp_username'] = $username;
        $p_data['new_password'] = $password;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    // ========================================================================
    //  数据库管理
    // ========================================================================

    public function databascx($user)
    {
        $url = $this->BT_PANEL . '/data?action=getData';
        $p_data = $this->GetKeyData();
        $p_data['table'] = 'databases';
        $p_data['search'] = $user;
        $p_data['limit'] = 100;
        $p_data['p'] = 1;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function sqlkjhq($db_name)
    {
        $url = $this->BT_PANEL . '/database?action=GetInfo';
        $p_data = $this->GetKeyData();
        $p_data['db_name'] = $db_name;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function setmysqlpass($id, $name, $password)
    {
        $url = $this->BT_PANEL . '/database?action=ResDatabasePassword';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        $p_data['name'] = $name;
        $p_data['password'] = $password;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function mysqlqx($name, $access)
    {
        $url = $this->BT_PANEL . '/database?action=SetDatabaseAccess';
        $p_data = $this->GetKeyData();
        $p_data['name'] = $name;
        $p_data['dataAccess'] = '%';
        $p_data['access'] = $access;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function GetDatabaseAccess($name)
    {
        $url = $this->BT_PANEL . '/database?action=GetDatabaseAccess';
        $p_data = $this->GetKeyData();
        $p_data['name'] = $name;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function SetDatabaseAccess($name, $dataAccess)
    {
        $url = $this->BT_PANEL . '/database?action=SetDatabaseAccess';
        $p_data = $this->GetKeyData();
        $p_data['name'] = $name;
        $p_data['dataAccess'] = $dataAccess;
        $p_data['access'] = $dataAccess;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function Databasebackuplist($id)
    {
        $url = $this->BT_PANEL . '/data?action=getData';
        $p_data = $this->GetKeyData();
        $p_data['table'] = "backup";
        $p_data['search'] = $id;
        $p_data['type'] = "1";
        $p_data['limit'] = "200";
        $p_data['p'] = "1";
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function Databaseadd($id)
    {
        $url = $this->BT_PANEL . '/database?action=ToBackup';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function DatabaseDelete($id)
    {
        $url = $this->BT_PANEL . '/database?action=DelBackup';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function Databaserestore($file, $user)
    {
        $url = $this->BT_PANEL . '/database?action=InputSql';
        $p_data = $this->GetKeyData();
        $p_data['file'] = $file;
        $p_data['name'] = $user;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function drsql($wj)
    {
        $url = $this->BT_PANEL . '/database?action=InputSql';
        $p_data = $this->GetKeyData();
        $p_data['file'] = $wj[0];
        $p_data['name'] = strtolower($wj[1]);
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    // ========================================================================
    //  SSL/HTTPS
    // ========================================================================

    public function httpsfcz()
    {
        $url = $this->BT_PANEL . '/site?action=get_https_mode';
        $p_data = $this->GetKeyData();
        $result = $this->HttpPostCookie($url, $p_data);
        if ($result == 'false') {
            $url = $this->BT_PANEL . '/site?action=set_https_mode';
            return json_decode($this->HttpPostCookie($url, $p_data), true);
        }
        return ["status" => true, "msg" => 'https窜站未开启'];
    }

    public function sslsq($domains, $siteid, $sitename, $renew = false)
    {
        $this->httpsfcz();
        $url = $this->BT_PANEL . '/acme?action=apply_cert_api';
        $p_data = $this->GetKeyData();
        $p_data['domains'] = $domains;
        $p_data['auth_type'] = 'http';
        $p_data['auth_to'] = $siteid;
        $p_data['auto_wildcard'] = 0;
        $p_data['id'] = $siteid;
        if ($renew) {
            $p_data['siteName'] = $sitename;
        }
        $data = json_decode($this->HttpPostCookie($url, $p_data), true);
        if ($data['status']) {
            $zsps = $this->setsslpem($sitename, $data['private_key'], $data['cert'] . '\n' . $data['root']);
            return ["status" => $zsps['status'], "msg" => [$zsps['msg']]];
        }
        return $data;
    }

    public function setsslpem($sitename, $key, $csr)
    {
        $this->httpsfcz();
        $url = $this->BT_PANEL . '/site?action=SetSSL';
        $p_data = $this->GetKeyData();
        $p_data['type'] = 1;
        $p_data['siteName'] = $sitename;
        $p_data['key'] = $key;
        $p_data['csr'] = $csr;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function getsslpem($sitename)
    {
        $url = $this->BT_PANEL . '/site?action=GetSSL';
        $p_data = $this->GetKeyData();
        $p_data['siteName'] = $sitename;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function closessl($sitename)
    {
        $url = $this->BT_PANEL . '/site?action=CloseSSLConf';
        $p_data = $this->GetKeyData();
        $p_data['updateOf'] = 1;
        $p_data['siteName'] = $sitename;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function httpsqzf($sitename, $enable)
    {
        $url = $this->BT_PANEL . ($enable == 'true' ? '/site?action=HttpToHttps' : '/site?action=CloseToHttps');
        $p_data = $this->GetKeyData();
        $p_data['siteName'] = $sitename;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    // ========================================================================
    //  文件管理
    // ========================================================================

    public function hqwjnr($path)
    {
        $url = $this->BT_PANEL . '/files?action=GetFileBody';
        $p_data = $this->GetKeyData();
        $p_data['path'] = $path;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function setwj($wjxx)
    {
        $url = $this->BT_PANEL . '/files?action=SaveFileBody';
        $p_data = $this->GetKeyData();
        $p_data['data'] = $wjxx[0];
        $p_data['encoding'] = 'utf-8';
        $p_data['path'] = $wjxx[1];
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function setwjt($wjxx)
    {
        return $this->setwj($wjxx);
    }

    public function xjwj($path)
    {
        $url = $this->BT_PANEL . '/files?action=CreateFile';
        $p_data = $this->GetKeyData();
        $p_data['path'] = $path;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function xjwjj($path)
    {
        $url = $this->BT_PANEL . '/files?action=CreateDir';
        $p_data = $this->GetKeyData();
        $p_data['path'] = $path;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function delwj($path)
    {
        $url = $this->BT_PANEL . '/files?action=DeleteFile';
        $p_data = $this->GetKeyData();
        $p_data['path'] = $path;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function delwjj($path, $name, $arridpath)
    {
        $name = trim($name);
        if ($name === '/' || $name == '') return ["status" => false, "msg" => '无法删除站点目录！'];
        if (strpos($name, '/')) return ["status" => false, "msg" => '目录名不规范！'];
        $getfkz = $this->yxmlrhq($arridpath[0], $arridpath[1]);
        if ($getfkz['runPath']['runPath'] == $path . $name) return ["status" => false, "msg" => '禁止删除运行目录！'];
        $zmllist = $this->urlzmlls($arridpath[0])['binding'] ?? [];
        $yxml = $getfkz['runPath']['runPath'];
        if ($yxml != '/') $yxml .= '/';
        foreach ($zmllist as $val) {
            if (substr($val['path'], 0, 3) == '../') {
                $val['path'] = substr($val['path'], 3);
                $yxml = '/';
            }
            if ($yxml . $val['path'] == $path . $name) return ["status" => false, "msg" => '错误！您正在尝试删除的目录已被域名' . $val['domain'] . '绑定为子目录，禁止删除子目录！'];
            $yxml = $getfkz['runPath']['runPath'];
            if ($yxml != '/') $yxml .= '/';
        }
        $url = $this->BT_PANEL . '/files?action=DeleteDir';
        $p_data = $this->GetKeyData();
        $p_data['path'] = $arridpath[1] . $path . $name;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function xzdelwj($path, $file, $arridpath)
    {
        $getfkz = $this->yxmlrhq($arridpath[0], $arridpath[1]);
        $arrfile = json_decode($file, true);
        $file = $this->delval_array($arrfile, $getfkz['runPath']['runPath'], $path);
        if (!$file) return ["status" => false, "msg" => '禁止删除运行目录！无法删除名称不规范的目录/文件！'];
        $zmllist = $this->urlzmlls($arridpath[0])['binding'] ?? [];
        $yxml = $getfkz['runPath']['runPath'];
        if ($yxml != '/') $yxml .= '/';
        foreach ($zmllist as $val) {
            if (substr($val['path'], 0, 3) == '../') {
                $val['path'] = substr($val['path'], 3);
                $yxml = '/';
            }
            $file = $this->delval_array($file, $yxml . $val['path'], $path);
            $yxml = $getfkz['runPath']['runPath'];
            if ($yxml != '/') $yxml .= '/';
        }
        if (!$file) return ["status" => false, "msg" => '禁止删除子目录！'];
        $url = $this->BT_PANEL . '/files?action=SetBatchData';
        $p_data = $this->GetKeyData();
        $p_data['data'] = json_encode($file, JSON_UNESCAPED_UNICODE);
        $p_data['type'] = '4';
        $p_data['path'] = $arridpath[1] . $path;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function GetHostDirectory($path, $page = '1', $showRow = '500')
    {
        $url = $this->BT_PANEL . '/files?action=GetDir';
        $p_data = $this->GetKeyData();
        $p_data['p'] = $page;
        $p_data['showRow'] = $showRow;
        $p_data['path'] = $path;
        $p_data['is_operating'] = "true";
        $p_data['search'] = "";
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function GetFileContent($path)
    {
        return $this->hqwjnr($path);
    }

    public function GetLogswt($path)
    {
        return $this->hqwjnr($path);
    }

    public function GetLogswh($data, $path)
    {
        return $this->setwj([$data, $path]);
    }

    public function GetLogshqwjlo($stera, $sorting = 'False', $sort = 'name', $datasize = '2000', $page = '1')
    {
        $url = $this->BT_PANEL . '/files?action=GetDir';
        $p_data = $this->GetKeyData();
        $p_data['p'] = $page;
        $p_data['showRow'] = $datasize;
        $p_data['path'] = $stera;
        $p_data['reverse'] = $sorting;
        $p_data['sort'] = $sort;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function GetLogsworld($id, $username, $password)
    {
        return $this->setmysqlpass($id, $username, $password);
    }

    public function GetLogsftp($id, $username, $password)
    {
        return $this->setftppass($id, $username, $password);
    }

    public function webkjjs($wj)
    {
        return $this->hqsize($wj);
    }

    public function hqsize($path)
    {
        $url = $this->BT_PANEL . '/files?action=get_path_size';
        $p_data = $this->GetKeyData();
        $p_data['path'] = $path;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function fileupa($filename)
    {
        $url = $this->BT_PANEL . '/files?action=upload_file_exists';
        $p_data = $this->GetKeyData();
        $p_data['filename'] = $filename;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function fileups($uplj, $file, $f_start, $f_name, $f_size)
    {
        $url = $this->BT_PANEL . '/files?action=upload';
        $p_data = $this->GetKeyData();
        $p_data['f_path'] = $uplj;
        $p_data['f_name'] = $f_name;
        $p_data['f_size'] = $f_size;
        $p_data['f_start'] = $f_start;
        $p_data['blob'] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function filecopy($sfile, $dfile)
    {
        $url = $this->BT_PANEL . '/files?action=CopyFile';
        $p_data = $this->GetKeyData();
        $p_data['sfile'] = $sfile;
        $p_data['dfile'] = $dfile;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function fileysr($sfile, $dfile, $z_type, $path)
    {
        $url = $this->BT_PANEL . '/files?action=Zip';
        $p_data = $this->GetKeyData();
        $p_data['sfile'] = $sfile;
        $p_data['dfile'] = $dfile;
        $p_data['z_type'] = $z_type;
        $p_data['path'] = $path;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function GetLogsjywj($sfile, $dfile, $coding, $password)
    {
        $url = $this->BT_PANEL . '/files?action=UnZip';
        $p_data = $this->GetKeyData();
        $p_data['sfile'] = $sfile;
        $p_data['dfile'] = $dfile;
        $p_data['coding'] = $coding;
        $p_data['password'] = $password;
        $p_data['type'] = 'zip';
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function cxname($jwz)
    {
        global $yhc;
        if (strpos($jwz[2], '/')) return ["status" => false, "msg" => '被重命名的文件不存在！'];
        if (strpos($jwz[3], '/')) return ["status" => false, "msg" => '新文件名不规范！'];
        $getfkz = $this->yxmlrhq($yhc['btid'], $jwz[0]);
        if ($getfkz['runPath']['runPath'] == $jwz[1] . $jwz[2]) return ["status" => false, "msg" => '错误！您正在尝试重命名运行目录，这是不被允许的！'];
        if ($getfkz['runPath']['runPath'] == $jwz[1] . $jwz[3]) return ["status" => false, "msg" => '错误！此文件名已存在！'];
        $zmllist = $this->urlzmlls($yhc['btid'])['binding'] ?? [];
        $yxml = $getfkz['runPath']['runPath'];
        if ($yxml != '/') $yxml .= '/';
        foreach ($zmllist as $val) {
            if (substr($val['path'], 0, 3) == '../') {
                $val['path'] = substr($val['path'], 3);
                $yxml = '/';
            }
            if ($yxml . $val['path'] == $jwz[1] . $jwz[2]) return ["status" => false, "msg" => '错误！您正在尝试重命名的目录已被域名' . $val['domain'] . '绑定为子目录，禁止重命名子目录！'];
            if ($yxml . $val['path'] == $jwz[1] . $jwz[3]) return ["status" => false, "msg" => '错误！此文件名已存在！'];
            $yxml = $getfkz['runPath']['runPath'];
            if ($yxml != '/') $yxml .= '/';
        }
        $url = $this->BT_PANEL . '/files?action=MvFile';
        $p_data = $this->GetKeyData();
        $p_data['sfile'] = $jwz[0] . $jwz[1] . $jwz[2];
        $p_data['dfile'] = $jwz[0] . $jwz[1] . $jwz[3];
        $p_data['rename'] = true;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function zswjsc($file, $wz)
    {
        $url = $this->BT_PANEL . '/files?action=UploadFile&path=' . $wz . '&codeing=utf-8';
        $p_data = $this->GetKeyData();
        $p_data['zunfile'] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function wailkq($path, $name)
    {
        $url = $this->BT_PANEL . '/files?action=create_download_url';
        $p_data = $this->GetKeyData();
        $p_data['filename'] = $path . $name;
        $p_data['ps'] = $name;
        $p_data['password'] = '';
        $p_data['expire'] = 24;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function wailhq($id)
    {
        $url = $this->BT_PANEL . '/files?action=get_download_url_find';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function wailgb($id)
    {
        $url = $this->BT_PANEL . '/files?action=remove_download_url';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    // ========================================================================
    //  反向代理
    // ========================================================================

    public function fxdl_add($urlp, $site)
    {
        $url = $this->BT_PANEL . '/site?action=CreateProxy';
        $p_data = $this->GetKeyData();
        $p_data['type'] = '1';
        $p_data['proxyname'] = md5($urlp);
        $p_data['cachetime'] = '1';
        $p_data['proxydir'] = '/';
        $p_data['proxysite'] = 'http://' . $urlp;
        $p_data['todomain'] = $urlp;
        $p_data['cache'] = '0';
        $p_data['advanced'] = '0';
        $p_data['sitename'] = $site;
        $p_data['subfilter'] = '[{"sub1":"","sub2":""}]';
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function fxdl_del($urlp, $site)
    {
        $url = $this->BT_PANEL . '/site?action=RemoveProxy';
        $p_data = $this->GetKeyData();
        $p_data['sitename'] = $site;
        $p_data['proxyname'] = md5($urlp);
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    // ========================================================================
    //  防盗链
    // ========================================================================

    public function getfdlkg($id, $name)
    {
        $url = $this->BT_PANEL . '/site?action=GetSecurity';
        $p_data = $this->GetKeyData();
        $p_data["id"] = $id;
        $p_data["name"] = $name;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function Setfdlkg($id, $name, $fix, $domains, $status, $return_rule, $httpsta)
    {
        $url = $this->BT_PANEL . '/site?action=SetSecurity';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        $p_data['name'] = $name;
        $p_data['fix'] = $fix;
        $p_data['domains'] = $domains;
        $p_data['status'] = $status;
        $p_data['return_rule'] = $return_rule;
        $p_data['http_status'] = $httpsta;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    // ========================================================================
    //  目录密码访问 / 默认文档 / 运行目录 / 伪静态
    // ========================================================================

    public function GetLogs($id)
    {
        $url = $this->BT_PANEL . '/site?action=get_dir_auth';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function GetLogsr($id, $name)
    {
        $url = $this->BT_PANEL . '/site?action=delete_dir_auth';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        $p_data['name'] = $name;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function GetLogst($id, $name, $dir, $username, $password)
    {
        $url = $this->BT_PANEL . '/site?action=set_dir_auth';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        $p_data['name'] = $name;
        $p_data['site_dir'] = $dir;
        $p_data['username'] = $username;
        $p_data['password'] = $password;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function GetLogseb($id)
    {
        $url = $this->BT_PANEL . '/site?action=GetIndex';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function GetLogsea($id, $index)
    {
        $url = $this->BT_PANEL . '/site?action=SetIndex';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        $p_data['Index'] = $index;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function GetLogswr($siteName)
    {
        $url = $this->BT_PANEL . '/site?action=GetRewriteList';
        $p_data = $this->GetKeyData();
        $p_data['siteName'] = $siteName;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function yxmlrhq($id, $path)
    {
        $url = $this->BT_PANEL . '/site?action=GetDirUserINI';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        $p_data['path'] = $path;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function fkzup($siteid, $path)
    {
        $fkzqk = $this->yxmlrhq($siteid, $path);
        if (!$fkzqk['userini']) {
            $url = $this->BT_PANEL . '/site?action=SetDirUserINI';
            $p_data = $this->GetKeyData();
            $p_data['path'] = $path;
            return json_decode($this->HttpPostCookie($url, $p_data), true);
        }
        return ["status" => true, "msg" => "防跨站设置情况为开启"];
    }

    public function setyxml($dat)
    {
        $siteid = $dat[0];
        $runPath = $dat[1];
        $sitePath = $dat[2];
        $getfkz = $this->yxmlrhq($siteid, $sitePath);
        $oldRunPath = $getfkz['runPath']['runPath'];
        $zmllist = $this->urlzmlls($siteid)['binding'] ?? [];
        if ($oldRunPath != '/') {
            foreach ($zmllist as $vals) {
                if (substr($vals['path'], 0, 3) != '../') {
                    $this->delzml($siteid, $vals['domain'], $sitePath);
                }
            }
        }
        $url = $this->BT_PANEL . '/site?action=SetSiteRunPath';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $siteid;
        $p_data['runPath'] = $runPath;
        $result = $this->HttpPostCookie($url, $p_data);
        $this->fkzup($siteid, $sitePath);
        $getfkz = $this->yxmlrhq($siteid, $sitePath);
        $zmllist = $this->urlzmlls($siteid)['binding'] ?? [];
        $yxml = $getfkz['runPath']['runPath'];
        if ($yxml != '/') $yxml .= '/';
        foreach ($zmllist as $val) {
            if (substr($val['path'], 0, 3) == '../') {
                $val['path'] = substr($val['path'], 3);
                $yxml = '/';
            }
            if ($getfkz['runPath']['runPath'] == '/') {
                $this->filecopy($sitePath . $getfkz['runPath']['runPath'] . '.user.ini', $sitePath . $yxml . $val['path'] . '/.user.ini');
            } else {
                $this->filecopy($sitePath . $getfkz['runPath']['runPath'] . '/.user.ini', $sitePath . $yxml . $val['path'] . '/.user.ini');
            }
            $yxml = $getfkz['runPath']['runPath'];
            if ($yxml != '/') $yxml .= '/';
        }
        return json_decode($result, true);
    }

    // ========================================================================
    //  Nginx / 日志
    // ========================================================================

    public function Getnginx($name)
    {
        $url = $this->BT_PANEL . '/files?action=GetFileBody';
        $p_data = $this->GetKeyData();
        $p_data['path'] = "/www/server/panel/vhost/nginx/" . $name . ".conf";
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function getlog($siteName)
    {
        $url = $this->BT_PANEL . '/site?action=GetSiteLogs';
        $p_data = $this->GetKeyData();
        $p_data['siteName'] = $siteName;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    // ========================================================================
    //  网站 GZIP 压缩配置（宝塔网站 PHP/ServiceConf API）
    // ========================================================================

    public function get_gzip_status($siteName)
    {
        $url = $this->BT_PANEL . '/mod/php/serviceconf/get_nginx_gzip';
        $p_data = $this->GetKeyData();
        $p_data['site_name'] = $siteName;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function remove_gzip_status($siteName)
    {
        $url = $this->BT_PANEL . '/mod/php/serviceconf/remove_nginx_gzip';
        $p_data = $this->GetKeyData();
        $p_data['site_name'] = $siteName;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function set_gzip($siteName, $gzip_types, $comp_level, $min_length)
    {
        $url = $this->BT_PANEL . '/mod/php/serviceconf/set_nginx_gzip';
        $p_data = $this->GetKeyData();
        $p_data['site_name'] = $siteName;
        $p_data['gzip_types'] = $gzip_types;
        $p_data['comp_level'] = $comp_level;
        $p_data['min_length'] = $min_length;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function get_static_cache($siteName)
    {
        $url = $this->BT_PANEL . '/mod/php/serviceconf/get_nginx_static_cache';
        $p_data = $this->GetKeyData();
        $p_data['site_name'] = $siteName;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function set_static_cache($siteName, $suffix, $time_out, $old_suffix = null)
    {
        $url = $this->BT_PANEL . '/mod/php/serviceconf/set_nginx_static_cache';
        $p_data = $this->GetKeyData();
        $p_data['site_name'] = $siteName;
        $p_data['suffix'] = $suffix;
        $p_data['time_out'] = $time_out;
        if ($old_suffix !== null && $old_suffix !== '') {
            $p_data['old_suffix'] = $old_suffix;
        }
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function remove_static_cache($siteName, $suffix)
    {
        $url = $this->BT_PANEL . '/mod/php/serviceconf/remove_nginx_static_cache';
        $p_data = $this->GetKeyData();
        $p_data['site_name'] = $siteName;
        $p_data['suffix'] = $suffix;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    // ========================================================================
    //  PHPMyAdmin
    // ========================================================================

    public function api_sql_cf()
    {
        $url = $this->BT_PANEL . '/plugin?action=get_soft_find';
        $p_data = $this->GetKeyData();
        $p_data['sName'] = 'phpmyadmin';
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    public function api_sql_set($type)
    {
        $url = $this->BT_PANEL . '/system?action=ServiceAdmin';
        $p_data = $this->GetKeyData();
        $p_data['name'] = 'phpmyadmin';
        $p_data['type'] = $type;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    // ========================================================================
    //  一键部署
    // ========================================================================

    public function 获取部署程序的列表()
    {
        $url = $this->BT_PANEL . '/deployment?action=GetList';
        $p_data = $this->GetKeyData();
        $p_data['type'] = 0;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    /**
     * 获取可部署的软件包列表
     * @return array
     */
    public function deployment_get_list($timeout = 25)
    {
        $url = $this->BT_PANEL . '/deployment?action=GetList';
        $p_data = $this->GetKeyData();
        $p_data['action'] = 'GetList';
        $p_data['type'] = 0;
        return json_decode($this->HttpPostCookie($url, $p_data, $timeout), true);
    }

    /**
     * 获取可用于一键部署的网站和框架列表
     * @return array
     */
    public function deployment_get_site_list($timeout = 25)
    {
        $url = $this->BT_PANEL . '/deployment?action=GetSiteList';
        $p_data = $this->GetKeyData();
        $p_data['action'] = 'GetSiteList';
        return json_decode($this->HttpPostCookie($url, $p_data, $timeout), true);
    }

    /**
     * 部署软件包到指定网站
     * @param string $dname 软件包名称
     * @param string $site_name 目标网站域名
     * @param string $project_type 项目类型（php/java），默认 php
     * @return array
     */
    public function deployment_setup_package($dname, $site_name, $project_type = 'php', $timeout = 25)
    {
        $url = $this->BT_PANEL . '/deployment?action=SetupPackage';
        $p_data = $this->GetKeyData();
        $p_data['action'] = 'SetupPackage';
        $p_data['dname'] = $dname;
        $p_data['site_name'] = $site_name;
        if ($project_type !== 'php') {
            $p_data['project_type'] = $project_type;
        }
        $raw = $this->HttpPostCookie($url, $p_data, $timeout);
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['status' => false, 'msg' => '面板响应异常：' . mb_substr((string)$raw, 0, 200)];
        }
        return $decoded;
    }

    /**
     * 检测服务器环境
     * @return array
     */
    public function deployment_check_project_env($timeout = 25)
    {
        $url = $this->BT_PANEL . '/deployment?action=check_project_env';
        $p_data = $this->GetKeyData();
        $p_data['action'] = 'check_project_env';
        return json_decode($this->HttpPostCookie($url, $p_data, $timeout), true);
    }

    /**
     * 获取部署进度
     * @return array|null
     */
    public function deployment_get_speed($timeout = 25)
    {
        $url = $this->BT_PANEL . '/deployment?action=GetSpeed';
        $p_data = $this->GetKeyData();
        $p_data['action'] = 'GetSpeed';
        return json_decode($this->HttpPostCookie($url, $p_data, $timeout), true);
    }

    /**
     * 获取部署日志
     * @return array
     */
    public function deployment_get_in_log($timeout = 25)
    {
        $url = $this->BT_PANEL . '/deployment?action=GetInLog';
        $p_data = $this->GetKeyData();
        $p_data['action'] = 'GetInLog';
        return json_decode($this->HttpPostCookie($url, $p_data, $timeout), true);
    }

    /**
     * 添加自定义软件包（PHP 项目）
     * @param string $name 英文名称
     * @param string $title 显示标题
     * @param string $version 版本号
     * @param string $php PHP 版本，如 '74'
     * @param string $enable_functions 需要解禁的 PHP 函数
     * @param string $project_type 项目类型（php/java），默认 php
     * @param string $java_version JDK 版本（仅 Java）
     * @param string $mysql_version MySQL 版本（仅 Java）
     * @return array
     */
    public function deployment_add_package($name, $title, $version, $php = '', $enable_functions = '', $project_type = 'php', $java_version = '', $mysql_version = '', $timeout = 25)
    {
        $url = $this->BT_PANEL . '/deployment?action=AddPackage';
        $p_data = $this->GetKeyData();
        $p_data['action'] = 'AddPackage';
        $p_data['name'] = $name;
        $p_data['title'] = $title;
        $p_data['version'] = $version;
        $p_data['project_type'] = $project_type;
        if ($project_type === 'java') {
            $p_data['java_version'] = $java_version;
            $p_data['mysql_version'] = $mysql_version;
        } else {
            if ($php !== '') {
                $p_data['php'] = $php;
            }
            if ($enable_functions !== '') {
                $p_data['enable_functions'] = $enable_functions;
            }
        }
        return json_decode($this->HttpPostCookie($url, $p_data, $timeout), true);
    }

    /**
     * 获取自定义软件包详情
     * @param string $p_name 软件包英文名称
     * @return array
     */
    public function deployment_get_package_other($p_name, $timeout = 25)
    {
        $url = $this->BT_PANEL . '/deployment?action=GetPackageOther';
        $p_data = $this->GetKeyData();
        $p_data['action'] = 'GetPackageOther';
        $p_data['p_name'] = $p_name;
        return json_decode($this->HttpPostCookie($url, $p_data, $timeout), true);
    }

    /**
     * 删除通过一键部署安装的项目
     * @param string $dname 部署名称
     * @param string $site_name 网站名称
     * @return array
     */
    public function deployment_del_package($dname, $site_name, $timeout = 25)
    {
        $url = $this->BT_PANEL . '/deployment?action=DelPackage';
        $p_data = $this->GetKeyData();
        $p_data['action'] = 'DelPackage';
        $p_data['dname'] = $dname;
        $p_data['site_name'] = $site_name;
        return json_decode($this->HttpPostCookie($url, $p_data, $timeout), true);
    }

    // ========================================================================
    //  统计/列表
    // ========================================================================

    public function sjlist($table)
    {
        $url = $this->BT_PANEL . '/data?action=getData';
        $p_data = $this->GetKeyData();
        $p_data['table'] = $table;
        $p_data['limit'] = '9999';
        $p_data['p'] = '1';
        $p_data['search'] = '';
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    // ========================================================================
    //  Shell执行（通过宝塔计划任务）
    // ========================================================================

    private function shell_zx($id)
    {
        $url = $this->BT_PANEL . '/crontab?action=StartTask';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    private function shell_del($id)
    {
        $url = $this->BT_PANEL . '/crontab?action=DelCrontab';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    private function shell_list($name)
    {
        $url = $this->BT_PANEL . '/crontab?action=GetCrontab';
        $p_data = $this->GetKeyData();
        $result = $this->HttpPostCookie($url, $p_data);
        $data = json_decode($result, true);
        if (is_array($data)) {
            foreach ($data as $v) {
                if (is_array($v) && $v['name'] == $name) return ['code' => 1, 'msg' => '此任务存在', 'id' => $v['id']];
            }
        }
        return ['code' => 0, 'msg' => '此任务不存在'];
    }

    private function shell_log($id)
    {
        $url = $this->BT_PANEL . '/crontab?action=GetLogs';
        $p_data = $this->GetKeyData();
        $p_data['id'] = $id;
        $data = json_decode($this->HttpPostCookie($url, $p_data), true);
        if (!$data['status']) return ['code' => 0, 'msg' => $data['msg']];
        $jg = explode("\n", $data['msg']);
        foreach ($jg as $k => $v) {
            if (strpos($v, '★') !== false && isset($jg[$k + 1]) && isset($jg[$k - 1]) && strpos($jg[$k + 1], '-------') !== false && strpos($jg[$k - 1], '-------') !== false) {
                unset($jg[$k]);
                unset($jg[$k + 1]);
                unset($jg[$k - 1]);
                $jg[$k] = trim($jg[$k]);
            }
        }
        $jg = array_values($jg);
        $dat = count($jg);
        return ['code' => 1, 'msg' => $jg[$dat - 2] ?? ''];
    }

    private function shell_add($shell)
    {
        $url = $this->BT_PANEL . '/crontab?action=AddCrontab';
        $p_data = $this->GetKeyData();
        $p_data['name'] = 'MNBT的shell脚本';
        $p_data['type'] = 'day-n';
        $p_data['where1'] = 30;
        $p_data['hour'] = 1;
        $p_data['minute'] = 30;
        $p_data['week'] = '';
        $p_data['sType'] = 'toShell';
        $p_data['sBody'] = $shell;
        $p_data['sName'] = '';
        $p_data['backupTo'] = '';
        $p_data['save'] = '';
        $p_data['urladdress'] = '';
        $p_data['save_local'] = 1;
        $p_data['notice'] = '';
        $p_data['notice_channel'] = '';
        $p_data['datab_name'] = '';
        $p_data['tables_name'] = '';
        return json_decode($this->HttpPostCookie($url, $p_data), true);
    }

    private function shell_yx($shell, $cljgty = true)
    {
        if (!$shell) return ['code' => 0, 'msg' => '脚本错误！'];
        $re_rw = $this->shell_list('MNBT的shell脚本');
        if ($re_rw['code']) {
            $rs_del = $this->shell_del($re_rw['id']);
            if (!$rs_del['status']) return ['code' => 0, 'msg' => '原任务删除失败，返回：' . ($rs_del['msg'] ?? '')];
        }
        $re_add = $this->shell_add($shell);
        if (!$re_add['status']) return ['code' => 0, 'msg' => '任务添加失败，返回：' . ($re_add['msg'] ?? '')];
        $re_zx = $this->shell_zx($re_add['id']);
        if (!$re_zx['status']) {
            $this->shell_del($re_add['id']);
            return ['code' => 0, 'msg' => '任务执行失败，返回：' . ($re_zx['msg'] ?? '')];
        }
        $re_jg = [];
        if ($cljgty) {
            sleep(1);
            $re_jg = $this->shell_log($re_add['id']);
            if (!$re_jg['code']) return $re_jg;
        }
        $this->shell_del($re_add['id']);
        return ['code' => 1, 'msg' => $re_jg['msg'] ?? ''];
    }

    public function GetAllFile()
    {
        include(ROOT . 'bash.conf.php');
        return $this->shell_yx($shell_get_file);
    }

    public function GetFileCentent($name)
    {
        $shell_cat_file = '
        #!/bin/bash
        content=$(cat ' . $name . ')
        echo "$content"
        ';
        return $this->shell_yx($shell_cat_file);
    }

    // ========================================================================
    //  内部工具
    // ========================================================================

    /**
     * 调用宝塔插件 API
     * @param string $name 插件名称
     * @param string $action 操作名（对应插件的 s 参数）
     * @param array $args 业务参数
     * @return array 解码后的响应
     */
    public function pluginRequest($name, $action, $args = [])
    {
        $url = $this->BT_PANEL . '/plugin?action=a&name=' . urlencode($name) . '&s=' . urlencode($action);
        $p_data = $this->GetKeyData();
        foreach ($args as $k => $v) {
            $p_data[$k] = $v;
        }
        return json_decode($this->HttpPostCookie($url, $p_data), true) ?: [];
    }

    private function delval_array($val_array, $del_val, $path)
    {
        if (!is_array($val_array)) return $val_array;
        $result = [];
        foreach ($val_array as $v) {
            if ($path . $v != $del_val) {
                $result[] = $v;
            }
        }
        return $result;
    }

    private function GetKeyData()
    {
        $now_time = time();
        return [
            'request_token' => md5($now_time . '' . md5($this->BT_KEY)),
            'request_time' => $now_time
        ];
    }

    private function HttpPostCookie($url, $data, $timeout = 60, $connectTimeout = 10)
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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        if ($output === false) {
            $output = '{"status":false,"msg":"连接面板失败：' . addslashes((string)curl_error($ch)) . '"}';
        }
        curl_close($ch);
        return $output;
    }
}

// ========================================================================
//  向后兼容类
// ========================================================================

class bt_api_set extends bt_api {}
class win_bt_api extends bt_api {}
class bt_api_rj extends bt_api {}
