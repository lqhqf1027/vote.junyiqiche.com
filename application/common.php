<?php

// 公共助手函数

error_reporting(E_PARSE | E_ERROR | E_WARNING);

use think\Request;

// 应用公共文件
function pr($var)
{
    $template = PHP_SAPI !== 'cli' ? '<pre>%s</pre>' : "\n%s\n";
    printf($template, print_r($var, true));
}

//php 转对象
function arrayToObject($e)
{
    if (gettype($e) != 'array') return;
    foreach ($e as $k => $v) {
        if (gettype($v) == 'array' || getType($v) == 'object')
            $e[$k] = (object)arrayToObject($v);
    }
    return (object)$e;
}

//php 转数组
function objectToArray($e)
{
    $e = (array)$e;
    foreach ($e as $k => $v) {
        if (gettype($v) == 'resource') return;
        if (gettype($v) == 'object' || gettype($v) == 'array')
            $e[$k] = (array)objectToArray($v);
    }
    return $e;
}

/**
 * 将一个数组转换为 XML 结构的字符串
 * @param array $arr 要转换的数组
 * @param int $level 节点层级, 1 为 Root.
 * @return string XML 结构的字符串
 */
function array2xml($arr, $level = 1)
{
    $s = $level == 1 ? "<xml>" : '';
    foreach ($arr as $tagname => $value) {
        if (is_numeric($tagname)) {
            $tagname = $value['TagName'];
            unset($value['TagName']);
        }
        if (!is_array($value)) {
            $s .= "<{$tagname}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
        } else {
            $s .= "<{$tagname}>" . array2xml($value, $level + 1) . "</{$tagname}>";
        }
    }
    $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
    return $level == 1 ? $s . "</xml>" : $s;
}

function istrlen($string, $charset = 'UTF8')
{

    if (strtolower($charset) == 'gbk') {
        $charset = 'gbk';
    } else {
        $charset = 'utf8';
    }
    if (function_exists('mb_strlen')) {
        return mb_strlen($string, $charset);
    } else {
        $n = $noc = 0;
        $strlen = strlen($string);

        if ($charset == 'utf8') {

            while ($n < $strlen) {
                $t = ord($string[$n]);
                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $n++;
                    $noc++;
                } elseif (194 <= $t && $t <= 223) {
                    $n += 2;
                    $noc++;
                } elseif (224 <= $t && $t <= 239) {
                    $n += 3;
                    $noc++;
                } elseif (240 <= $t && $t <= 247) {
                    $n += 4;
                    $noc++;
                } elseif (248 <= $t && $t <= 251) {
                    $n += 5;
                    $noc++;
                } elseif ($t == 252 || $t == 253) {
                    $n += 6;
                    $noc++;
                } else {
                    $n++;
                }
            }

        } else {

            while ($n < $strlen) {
                $t = ord($string[$n]);
                if ($t > 127) {
                    $n += 2;
                    $noc++;
                } else {
                    $n++;
                    $noc++;
                }
            }

        }

        return $noc;
    }
}

function url_encode($str)
{
    if (is_array($str)) {
        foreach ($str as $key => $value) {
            $str[urlencode($key)] = url_encode($value);
        }
    } else {
        $str = urlencode($str);
    }

    return $str;
}

/**
 * 转义字符串的HTML
 * @param string or array $var
 * @return string or array
 *             返回转义后的字符串或是数组
 */
function ihtmlspecialchars($var)
{
    if (is_array($var)) {
        foreach ($var as $key => $value) {
            $var[htmlspecialchars($key)] = ihtmlspecialchars($value);
        }
    } else {
        $var = str_replace('&amp;', '&', htmlspecialchars($var, ENT_QUOTES));
    }
    return $var;
}

/**
 * 是否包含子串
 */

function strexists($string, $find)
{
    return !(strpos($string, $find) === FALSE);
}

function ihttp_request($url, $post = '', $extra = array(), $timeout = 60)
{
    $urlset = parse_url($url);
    if (empty($urlset['path'])) {
        $urlset['path'] = '/';
    }
    if (!empty($urlset['query'])) {
        $urlset['query'] = "?{$urlset['query']}";
    }
    if (empty($urlset['port'])) {
        $urlset['port'] = $urlset['scheme'] == 'https' ? '443' : '80';
    }
    if (strexists($url, 'https://') && !extension_loaded('openssl')) {
        if (!extension_loaded("openssl")) {
            //die('请开启您PHP环境的openssl');
        }
    }
    if (function_exists('curl_init') && function_exists('curl_exec')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlset['scheme'] . '://' . $urlset['host'] . ($urlset['port'] == '80' ? '' : ':' . $urlset['port']) . $urlset['path'] . $urlset['query']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            if (is_array($post)) {
                $post = http_build_query($post);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        if (defined('CURL_SSLVERSION_TLSv1')) {
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
        if (!empty($extra) && is_array($extra)) {
            $headers = array();
            foreach ($extra as $opt => $value) {
                if (strexists($opt, 'CURLOPT_')) {
                    curl_setopt($ch, constant($opt), $value);
                } elseif (is_numeric($opt)) {
                    curl_setopt($ch, $opt, $value);
                } else {
                    $headers[] = "{$opt}: {$value}";
                }
            }
            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
        }
        $data = curl_exec($ch);
        $status = curl_getinfo($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($errno || empty($data)) {
            //return error(1, $error);
        } else {
            return ihttp_response_parse($data);
        }
    }
    $method = empty($post) ? 'GET' : 'POST';
    $fdata = "{$method} {$urlset['path']}{$urlset['query']} HTTP/1.1\r\n";
    $fdata .= "Host: {$urlset['host']}\r\n";
    if (function_exists('gzdecode')) {
        $fdata .= "Accept-Encoding: gzip, deflate\r\n";
    }
    $fdata .= "Connection: close\r\n";
    if (!empty($extra) && is_array($extra)) {
        foreach ($extra as $opt => $value) {
            if (!strexists($opt, 'CURLOPT_')) {
                $fdata .= "{$opt}: {$value}\r\n";
            }
        }
    }
    $body = '';
    if ($post) {
        if (is_array($post)) {
            $body = http_build_query($post);
        } else {
            $body = urlencode($post);
        }
        $fdata .= 'Content-Length: ' . strlen($body) . "\r\n\r\n{$body}";
    } else {
        $fdata .= "\r\n";
    }
    if ($urlset['scheme'] == 'https') {
        $fp = fsockopen('ssl://' . $urlset['host'], $urlset['port'], $errno, $error);
    } else {
        $fp = fsockopen($urlset['host'], $urlset['port'], $errno, $error);
    }
    stream_set_blocking($fp, true);
    stream_set_timeout($fp, $timeout);
    if (!$fp) {
        //return error(1, $error);
    } else {
        fwrite($fp, $fdata);
        $content = '';
        while (!feof($fp))
            $content .= fgets($fp, 512);
        fclose($fp);
        return ihttp_response_parse($content, true);
    }
}

function ihttp_response_parse($data, $chunked = false)
{
    $rlt = array();
    $pos = strpos($data, "\r\n\r\n");
    $split1[0] = substr($data, 0, $pos);
    $split1[1] = substr($data, $pos + 4, strlen($data));

    $split2 = explode("\r\n", $split1[0], 2);
    preg_match('/^(\S+) (\S+) (\S+)$/', $split2[0], $matches);
    $rlt['code'] = $matches[2];
    $rlt['status'] = $matches[3];
    $rlt['responseline'] = $split2[0];
    $header = explode("\r\n", $split2[1]);
    $isgzip = false;
    $ischunk = false;
    foreach ($header as $v) {
        $row = explode(':', $v);
        $key = trim($row[0]);
        $value = trim($row[1]);
        if (is_array($rlt['headers'][$key])) {
            $rlt['headers'][$key][] = $value;
        } elseif (!empty($rlt['headers'][$key])) {
            $temp = $rlt['headers'][$key];
            unset($rlt['headers'][$key]);
            $rlt['headers'][$key][] = $temp;
            $rlt['headers'][$key][] = $value;
        } else {
            $rlt['headers'][$key] = $value;
        }
        if (!$isgzip && strtolower($key) == 'content-encoding' && strtolower($value) == 'gzip') {
            $isgzip = true;
        }
        if (!$ischunk && strtolower($key) == 'transfer-encoding' && strtolower($value) == 'chunked') {
            $ischunk = true;
        }
    }
    if ($chunked && $ischunk) {
        $rlt['content'] = ihttp_response_parse_unchunk($split1[1]);
    } else {
        $rlt['content'] = $split1[1];
    }
    if ($isgzip && function_exists('gzdecode')) {
        $rlt['content'] = gzdecode($rlt['content']);
    }

    //$rlt['meta'] = $data;
    if ($rlt['code'] == '100') {
        return ihttp_response_parse($rlt['content']);
    }
    return $rlt;
}

function ihttp_response_parse_unchunk($str = null)
{
    if (!is_string($str) or strlen($str) < 1) {
        return false;
    }
    $eol = "\r\n";
    $add = strlen($eol);
    $tmp = $str;
    $str = '';
    do {
        $tmp = ltrim($tmp);
        $pos = strpos($tmp, $eol);
        if ($pos === false) {
            return false;
        }
        $len = hexdec(substr($tmp, 0, $pos));
        if (!is_numeric($len) or $len < 0) {
            return false;
        }
        $str .= substr($tmp, ($pos + $add), $len);
        $tmp = substr($tmp, ($len + $pos + $add));
        $check = trim($tmp);
    } while (!empty($check));
    unset($tmp);
    return $str;
}


function ihttp_get($url)
{
    return ihttp_request($url);
}

function ihttp_post($url, $data)
{
    $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
    return ihttp_request($url, $data, $headers);
}

function gets($url = NULL)
{
    if ($url) {
        $rslt = ihttp_get($url);
        if (strtolower(trim($rslt['status'])) == 'ok') {
            //pr($rslt) ;exit;
            if (is_json($rslt['content'])) { //返回格式是json 直接返回数组
                $return = json_decode($rslt['content'], true);
                if ($return['errcode']) //有错误
                    exit('Error:<br>Api:' . $url . '  <br>errcode:' . $return['errcode'] . '<br>errmsg:' . $return['errmsg']);
                return $return;
            } else {  //先暂时直接返回，以后其它格式再增加
                return $rslt['content'];
            }
        }
        exit('远程请求失败：' . $url);
    }
    exit('未发现远程请求地址');
}

/**
 * 远程post请求
 */
function posts($url = NULL, $data = NULL)
{
    if ($url && $data) {
        $rslt = ihttp_post($url, $data);
        if (strtolower(trim($rslt['status'])) == 'ok') {
            //pr($rslt) ;
            if (is_json($rslt['content'])) { //返回格式是json 直接返回数组
                $return = json_decode($rslt['content'], true);
                if ($return['errcode']) //有错误
                    exit('Error:<br>Api:' . $url . '  <br>errcode:' . $return['errcode'] . '<br>errmsg:' . $return['errmsg']);
                return $return;
            } else {  //先暂时直接返回，以后其它格式再增加
                return $rslt['content'];
            }
        }
        exit('远程请求失败：' . $url);
    }
    exit('post远程请求，参数错误');
}

function is_json($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

if (!function_exists('__')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name)
            return $name;
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\Lang::get($name, $vars, $lang);
    }

}

if (!function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本
     * @param int $size 大小
     * @param string $delimiter 分隔符
     * @return string
     */
    function format_bytes($size, $delimiter = '')
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++)
            $size /= 1024;
        return round($size, 2) . $delimiter . $units[$i];
    }

}

if (!function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间
     * @param int $time 时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }

}

if (!function_exists('human_date')) {

    /**
     * 获取语义化时间
     * @param int $time 时间
     * @param int $local 本地时间
     * @return string
     */
    function human_date($time, $local = null)
    {
        return \fast\Date::human($time, $local);
    }

}

if (!function_exists('cdnurl')) {

    /**
     * 获取上传资源的CDN的地址
     * @param string $url 资源相对地址
     * @param boolean $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $url = preg_match("/^https?:\/\/(.*)/i", $url) ? $url : \think\Config::get('upload.cdnurl') . $url;
        if ($domain && !preg_match("/^(http:\/\/|https:\/\/)/i", $url)) {
            if (is_bool($domain)) {
                $public = \think\Config::get('view_replace_str.__PUBLIC__');
                $url = rtrim($public, '/') . $url;
                if (!preg_match("/^(http:\/\/|https:\/\/)/i", $url)) {
                    $url = request()->domain() . $url;
                }
            } else {
                $url = $domain . $url;
            }
        }
        return $url;
    }

}


if (!function_exists('is_really_writable')) {

    /**
     * 判断文件或文件夹是否可写
     * @param    string $file 文件或目录
     * @return    bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === FALSE) {
                return FALSE;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return TRUE;
        } elseif (!is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE) {
            return FALSE;
        }
        fclose($fp);
        return TRUE;
    }

}

if (!function_exists('rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname 目录
     * @param bool $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname))
            return false;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }

}

if (!function_exists('copydirs')) {

    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest 目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest . DS . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }

}

if (!function_exists('mb_ucfirst')) {

    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }

}

if (!function_exists('addtion')) {

    /**
     * 附加关联字段数据
     * @param array $items 数据列表
     * @param mixed $fields 渲染的来源字段
     * @return array
     */
    function addtion($items, $fields)
    {
        if (!$items || !$fields)
            return $items;
        $fieldsArr = [];
        if (!is_array($fields)) {
            $arr = explode(',', $fields);
            foreach ($arr as $k => $v) {
                $fieldsArr[$v] = ['field' => $v];
            }
        } else {
            foreach ($fields as $k => $v) {
                if (is_array($v)) {
                    $v['field'] = isset($v['field']) ? $v['field'] : $k;
                } else {
                    $v = ['field' => $v];
                }
                $fieldsArr[$v['field']] = $v;
            }
        }
        foreach ($fieldsArr as $k => &$v) {
            $v = is_array($v) ? $v : ['field' => $v];
            $v['display'] = isset($v['display']) ? $v['display'] : str_replace(['_ids', '_id'], ['_names', '_name'], $v['field']);
            $v['primary'] = isset($v['primary']) ? $v['primary'] : '';
            $v['column'] = isset($v['column']) ? $v['column'] : 'name';
            $v['model'] = isset($v['model']) ? $v['model'] : '';
            $v['table'] = isset($v['table']) ? $v['table'] : '';
            $v['name'] = isset($v['name']) ? $v['name'] : str_replace(['_ids', '_id'], '', $v['field']);
        }
        unset($v);
        $ids = [];
        $fields = array_keys($fieldsArr);
        foreach ($items as $k => $v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $ids[$n] = array_merge(isset($ids[$n]) && is_array($ids[$n]) ? $ids[$n] : [], explode(',', $v[$n]));
                }
            }
        }
        $result = [];
        foreach ($fieldsArr as $k => $v) {
            if ($v['model']) {
                $model = new $v['model'];
            } else {
                $model = $v['name'] ? \think\Db::name($v['name']) : \think\Db::table($v['table']);
            }
            $primary = $v['primary'] ? $v['primary'] : $model->getPk();
            $result[$v['field']] = $model->where($primary, 'in', $ids[$v['field']])->column("{$primary},{$v['column']}");
        }

        foreach ($items as $k => &$v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $curr = array_flip(explode(',', $v[$n]));

                    $v[$fieldsArr[$n]['display']] = implode(',', array_intersect_key($result[$n], $curr));
                }
            }
        }
        return $items;
    }

}

if (!function_exists('var_export_short')) {

    /**
     * 返回打印数组结构
     * @param string $var 数组
     * @param string $indent 缩进字符
     * @return string
     */
    function var_export_short($var, $indent = "")
    {
        switch (gettype($var)) {
            case "string":
                return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
            case "array":
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                        . ($indexed ? "" : var_export_short($key) . " => ")
                        . var_export_short($value, "$indent    ");
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
            case "boolean":
                return $var ? "TRUE" : "FALSE";
            default:
                return var_export($var, TRUE);
        }
    }

}

if (!function_exists('emoji_encode')) {

    /**
     * emoji 表情转义
     * @param $nickname
     * @return string
     */
    function emoji_encode($nickname)
    {
        $strEncode = '';
        $length = mb_strlen($nickname, 'utf-8');
        for ($i = 0; $i < $length; $i++) {
            $_tmpStr = mb_substr($nickname, $i, 1, 'utf-8');
            if (strlen($_tmpStr) >= 4) {
                $strEncode .= '[[EMOJI:' . rawurlencode($_tmpStr) . ']]';
            } else {
                $strEncode .= $_tmpStr;
            }
        }
        return $strEncode;
    }
}
if (!function_exists('emoji_decode')) {


    function emoji_decode($str)
    {
        $strDecode = preg_replace_callback('|\[\[EMOJI:(.*?)\]\]|', function ($matches) {
            return rawurldecode($matches[1]);
        }, $str);

        return $strDecode;
    }
}