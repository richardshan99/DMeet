<?php

// 公共助手函数

use app\common\exception\AttendanceException;
use app\common\library\wechat\WxUser;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use think\exception\HttpResponseException;
use think\Response;

if (!function_exists('__')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array  $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
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
     * @param int    $size      大小
     * @param string $delimiter 分隔符
     * @param int    $precision 小数位数
     * @return string
     */
    function format_bytes($size, $delimiter = '', $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . $delimiter . $units[$i];
    }
}

if (!function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间
     * @param int    $time   时间戳
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
     * @param int $time  时间
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
     * @param string  $url    资源相对地址
     * @param boolean $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        $cdnurl = \think\Config::get('upload.cdnurl');
        $url = preg_match($regex, $url) || ($cdnurl && stripos($url, $cdnurl) === 0) ? $url : $cdnurl . $url;
        if ($domain && !preg_match($regex, $url)) {
            $domain = is_bool($domain) ? request()->domain() : $domain;
            $url = $domain . $url;
        }
        return $url;
    }
}


if (!function_exists('is_really_writable')) {

    /**
     * 判断文件或文件夹是否可写
     * @param string $file 文件或目录
     * @return    bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return true;
        } elseif (!is_file($file) or ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }
        fclose($fp);
        return true;
    }
}

if (!function_exists('rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname  目录
     * @param bool   $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
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
     * @param string $dest   目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $item
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
     * @param array $items  数据列表
     * @param mixed $fields 渲染的来源字段
     * @return array
     */
    function addtion($items, $fields)
    {
        if (!$items || !$fields) {
            return $items;
        }
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
            $result[$v['field']] = isset($ids[$v['field']]) ? $model->where($primary, 'in', $ids[$v['field']])->column($v['column'], $primary) : [];
        }

        foreach ($items as $k => &$v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $curr = array_flip(explode(',', $v[$n]));

                    $linedata = array_intersect_key($result[$n], $curr);
                    $v[$fieldsArr[$n]['display']] = $fieldsArr[$n]['column'] == '*' ? $linedata : implode(',', $linedata);
                }
            }
        }
        return $items;
    }
}

if (!function_exists('var_export_short')) {

    /**
     * 使用短标签打印或返回数组结构
     * @param mixed   $data
     * @param boolean $return 是否返回数据
     * @return string
     */
    function var_export_short($data, $return = true)
    {
        return var_export($data, $return);
        $replaced = [];
        $count = 0;

        //判断是否是对象
        if (is_resource($data) || is_object($data)) {
            return var_export($data, $return);
        }

        //判断是否有特殊的键名
        $specialKey = false;
        array_walk_recursive($data, function (&$value, &$key) use (&$specialKey) {
            if (is_string($key) && (stripos($key, "\n") !== false || stripos($key, "array (") !== false)) {
                $specialKey = true;
            }
        });
        if ($specialKey) {
            return var_export($data, $return);
        }
        array_walk_recursive($data, function (&$value, &$key) use (&$replaced, &$count, &$stringcheck) {
            if (is_object($value) || is_resource($value)) {
                $replaced[$count] = var_export($value, true);
                $value = "##<{$count}>##";
            } else {
                if (is_string($value) && (stripos($value, "\n") !== false || stripos($value, "array (") !== false)) {
                    $index = array_search($value, $replaced);
                    if ($index === false) {
                        $replaced[$count] = var_export($value, true);
                        $value = "##<{$count}>##";
                    } else {
                        $value = "##<{$index}>##";
                    }
                }
            }
            $count++;
        });

        $dump = var_export($data, true);

        $dump = preg_replace('#(?:\A|\n)([ ]*)array \(#i', '[', $dump); // Starts
        $dump = preg_replace('#\n([ ]*)\),#', "\n$1],", $dump); // Ends
        $dump = preg_replace('#=> \[\n\s+\],\n#', "=> [],\n", $dump); // Empties
        $dump = preg_replace('#\)$#', "]", $dump); //End

        if ($replaced) {
            $dump = preg_replace_callback("/'##<(\d+)>##'/", function ($matches) use ($replaced) {
                return isset($replaced[$matches[1]]) ? $replaced[$matches[1]] : "''";
            }, $dump);
        }

        if ($return === true) {
            return $dump;
        } else {
            echo $dump;
        }
    }
}

if (!function_exists('letter_avatar')) {
    /**
     * 首字母头像
     * @param $text
     * @return string
     */
    function letter_avatar($text)
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $bg = "rgb({$r},{$g},{$b})";
        $color = "#ffffff";
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="' . $color . '" text-anchor="middle" text-rights="admin" dominant-baseline="central">' . $first . '</text></svg>');
        $value = 'data:image/svg+xml;base64,' . $src;
        return $value;
    }
}

if (!function_exists('hsv2rgb')) {
    function hsv2rgb($h, $s, $v)
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }
}

if (!function_exists('check_nav_active')) {
    /**
     * 检测会员中心导航是否高亮
     */
    function check_nav_active($url, $classname = 'active')
    {
        $auth = \app\common\library\Auth::instance();
        $requestUrl = $auth->getRequestUri();
        $url = ltrim($url, '/');
        return $requestUrl === str_replace(".", "/", $url) ? $classname : '';
    }
}

if (!function_exists('check_cors_request')) {
    /**
     * 跨域检测
     */
    function check_cors_request()
    {
        if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN']) {
            $info = parse_url($_SERVER['HTTP_ORIGIN']);
            $domainArr = explode(',', config('fastadmin.cors_request_domain'));
            $domainArr[] = request()->host(true);
            if (in_array("*", $domainArr) || in_array($_SERVER['HTTP_ORIGIN'], $domainArr) || (isset($info['host']) && in_array($info['host'], $domainArr))) {
                header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
            } else {
                $response = Response::create('跨域检测无效', 'html', 403);
                throw new HttpResponseException($response);
            }

            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');

            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
                }
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
                }
                $response = Response::create('', 'html');
                throw new HttpResponseException($response);
            }
        }
    }
}

if (!function_exists('xss_clean')) {
    /**
     * 清理XSS
     */
    function xss_clean($content, $is_image = false)
    {
        return \app\common\library\Security::instance()->xss_clean($content, $is_image);
    }
}

if (!function_exists('check_ip_allowed')) {
    /**
     * 检测IP是否允许
     * @param string $ip IP地址
     */
    function check_ip_allowed($ip = null)
    {
        $ip = is_null($ip) ? request()->ip() : $ip;
        $forbiddenipArr = config('site.forbiddenip');
        $forbiddenipArr = !$forbiddenipArr ? [] : $forbiddenipArr;
        $forbiddenipArr = is_array($forbiddenipArr) ? $forbiddenipArr : array_filter(explode("\n", str_replace("\r\n", "\n", $forbiddenipArr)));
        if ($forbiddenipArr && \Symfony\Component\HttpFoundation\IpUtils::checkIp($ip, $forbiddenipArr)) {
            $response = Response::create('请求无权访问', 'html', 403);
            throw new HttpResponseException($response);
        }
    }
}

if (!function_exists('build_suffix_image')) {
    /**
     * 生成文件后缀图片
     * @param string $suffix 后缀
     * @param null   $background
     * @return string
     */
    function build_suffix_image($suffix, $background = null)
    {
        $suffix = mb_substr(strtoupper($suffix), 0, 4);
        $total = unpack('L', hash('adler32', $suffix, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $background = $background ? $background : "rgb({$r},{$g},{$b})";

        $icon = <<<EOT
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
            <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
            <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
            <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
            <path style="fill:{$background};" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16 V416z"/>
            <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            <g><text><tspan x="220" y="380" font-size="124" font-family="Verdana, Helvetica, Arial, sans-serif" fill="white" text-anchor="middle">{$suffix}</tspan></text></g>
        </svg>
EOT;
        return $icon;
    }
}



/**
 * 获取全局唯一标识符
 * @param bool $trim
 * @return string
 */
function getGuidV4($trim = true)
{
    // Windows
    if (function_exists('com_create_guid') === true) {
        $charid = com_create_guid();
        return $trim == true ? trim($charid, '{}') : $charid;
    }
    // OSX/Linux
    if (function_exists('openssl_random_pseudo_bytes') === true) {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    // Fallback (PHP 4.2+)
    mt_srand((double)microtime() * 10000);
    $charid = strtolower(md5(uniqid(rand(), true)));
    $hyphen = chr(45);                  // "-"
    $lbrace = $trim ? "" : chr(123);    // "{"
    $rbrace = $trim ? "" : chr(125);    // "}"
    $guidv4 = $lbrace .
        substr($charid, 0, 8) . $hyphen .
        substr($charid, 8, 4) . $hyphen .
        substr($charid, 12, 4) . $hyphen .
        substr($charid, 16, 4) . $hyphen .
        substr($charid, 20, 12) .
        $rbrace;
    return $guidv4;
}

/**
 * 计算年龄
 * @param $birthDate
 * @return false|string
 */
function DMCalculateAge($birthDate) {
    $age = date("Y") - date("Y", strtotime($birthDate));
    $birthMonth = date("m", strtotime($birthDate));
    $currentMonth = date("m");
    if ($currentMonth < $birthMonth) {
        $age--;
    } else if ($currentMonth == $birthMonth) {
        $birthDay = date("d", strtotime($birthDate));
        $currentDay = date("d");
        if ($currentDay < $birthDay) {
            $age--;
        }
    }
    return $age;
}

/**
 * 生成二维码
 * @param $text
 * @return \Endroid\QrCode\Writer\Result\ResultInterface
 * @throws Exception
 */
function DMQrcode($text)
{
    return Builder::create()
        ->writer(new PngWriter())
        ->writerOptions([])
        ->data((string)$text)
        ->encoding(new Encoding('UTF-8'))
        ->errorCorrectionLevel(new ErrorCorrectionLevelLow())
        ->size(300)
        ->margin(2)
        ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
        ->validateResult(false)
        ->build();
}

/**
 * 获取用户地区
 * @param $areaNew
 * @param $areaPath
 * @param int $offset
 * @return string
 */
function DMUserArea($areaNew, $areaPath, $offset = -2)
{
    $areaPath = trim($areaPath, ',');
    $areaPath = explode(',', $areaPath);
    array_shift($areaPath);
    $last = array_slice($areaPath, $offset);
    array_walk($last, function(&$item) use($areaNew) {
        $item = $areaNew[$item];
    });
    return implode($last);
}

/**
 * 获取用户地区 - 无前置
 * @param $areaNew
 * @param $areaPath
 * @param int $offset
 * @return string
 */
function DMUserAreaNo($areaNew, $areaPath, $offset = -2)
{
    $areaPath = trim($areaPath, ',');
    $areaPath = explode(',', $areaPath);
    $last = array_slice($areaPath, $offset);
    array_walk($last, function(&$item) use($areaNew) {
        $item = $areaNew[$item] ?? "";
    });
    return implode(array_filter($last));
}

/**
 * 分析身份证号，获取生日和性别
 * @param $idcard
 * @return array
 */
function DMAnalysisIdcard($idCard)
{
    if (strlen($idCard) == 18) {
        //性别
        $genderNumber = (int)substr($idCard, -2, 1);
        $gender = $genderNumber % 2 == 0 ? \app\common\library\Dict::GENDER_FEMALE : \app\common\library\Dict::GENDER_MALE;
        $birthday = substr($idCard, 6, 8);
    }

    // 判断是否为15位身份证，并转换为18位
    if (strlen($idCard) == 15) {
        //性别
        $genderNumber = (int)substr($idCard, -1);
        $gender = $genderNumber % 2 == 0 ? \app\common\library\Dict::GENDER_FEMALE : \app\common\library\Dict::GENDER_MALE;
        $birthday = "19".substr($idCard, 6, 6);
    }

    $age = DMCalculateAge($birthday);

    return [
        "birth" => date("Y-m-d", strtotime($birthday)),
        "gender" => $gender,
        "age"   => $age
    ];
}

/**
 * 七牛上传
 * @param $data
 * @param $no
 * @param $type
 * @return array
 */
function DMqiniu($data)
{
    $auth = new \Qiniu\Auth("JMEXzFFnB1O-pAnQIr-8F7lcYrCmRvLXfdeNB1jH", "14qqdPWaDG-sgrF6fgxytixDYnFxNRczi64qIzXZ");
    $bucket = 'xinglang';
    // 生成上传Token
    $token = $auth->uploadToken($bucket);
    // 构建 UploadManager 对象
    $uploadMgr = new \Qiniu\Storage\UploadManager();
    $key = "qrcode/".date("Ymd"). DS .time().mt_rand(1111,9999). ".png";
    list($ret, $err) = $uploadMgr->put($token, $key, $data);
    if($err !== null) {
        return DMqiniu($data);
    }
    return $ret;
}

/**
 * 向微信用户发送模板消息
 *
 * @param string $unionid 用户的微信 unionid
 * @param string $templateId 微信模板消息的模板 ID
 * @param string $page 点击模板消息后跳转的小程序页面路径
 * @param array $sendData 要发送的模板消息的数据
 */
 function DMSendWechatTempMessage($unionid, $templateId, $page, $sendData)
{
    if(empty($unionid)) return;
    $wechatUserOpenId = model('app\common\model\UserWechat')->where('unionid', $unionid)->value('openid');
    if(!$wechatUserOpenId) return;

    $wxUser = new WxUser(\think\Env::get('mini.wechat_appid'), \think\Env::get('mini.wechat_appsecret'));
    $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$wxUser->wechatAccessToken();
    $data['touser'] = $wechatUserOpenId;
    $data['template_id'] = $templateId;
    $data['url'] = "";
    $data['miniprogram'] = [
        "appid" => \think\Env::get('mini.appid'),
        "pagepath" => $page,
    ];
    $data['client_msg_id'] = "";
    $data['data'] = $sendData;
    $data_string = json_encode($data,JSON_UNESCAPED_UNICODE);
    // $data_string = $data;
    $curl_con = curl_init();
    curl_setopt($curl_con, CURLOPT_URL,$url);
    curl_setopt($curl_con, CURLOPT_HEADER, false);
    curl_setopt($curl_con, CURLOPT_POST, true);
    curl_setopt($curl_con, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl_con, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl_con, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
    );
    curl_setopt($curl_con, CURLOPT_POSTFIELDS, $data_string);
    $res = curl_exec($curl_con);
    curl_close($curl_con);
}

if (!function_exists('checkContent')) {
    function checkContent($openid,$content)
    {
        if(empty($openid)){
            return [false,'获取用户信息失败'];
        }

        $wxUser = new WxUser(\think\Env::get('mini.appid'), \think\Env::get('mini.appsecret'));
        // var_dump($wxUser->accessToken());die;
        $url = "https://api.weixin.qq.com/wxa/msg_sec_check?access_token=".$wxUser->accessToken();
        $data = [
            'content' => $content,
            'version' => 2,
            'scene' => 3,
            'openid'=> $openid
        ];

        $data_string = json_encode($data,JSON_UNESCAPED_UNICODE);
        $data = json_decode(httpRequest($url,[],'POST',$data_string),true);
        if($data['errcode'] != 0){
            return [false,'内容检测失败'];
        }
        if($data['result']['label'] != 100){
            return [false,'内容检测涉及违规，请重新输入'];
        }
        return [true,''];
    }
}

if (!function_exists('getArea')) {
    
   function getArea($longitude,$latitude)
   {
       // 高德API的key，需要在高德开放平台注册获取
       $amapKey = '57979b4d2bdd2f85e7084f4ef8d3dfc2';
       $url = "http://restapi.amap.com/v3/geocode/regeo?key={$amapKey}&location={$longitude},{$latitude}";
       $data = json_decode(httpRequest($url),true);

       if ($data['status'] == '1') {
           // 输出地理位置信息
           $geo = $data['regeocode'];
           if(empty($geo['addressComponent']['country'])){
               return '日本-东京';
           }

           $pattern = '/^(.*)(省|市|区)$/';
           $replacement = '$1';
           $country = $geo['addressComponent']['country'];
           $province = preg_replace($pattern, $replacement, $geo['addressComponent']['province']);
           if($geo['addressComponent']['city']){
              $city = preg_replace($pattern, $replacement, $geo['addressComponent']['city']);
           }else{
               $city = preg_replace($pattern, $replacement, $geo['addressComponent']['district']);
           }
           return $country.'-'.$province.'-'.$city;
       } else {
           return '日本-东京';
       }
   }
}

if (!function_exists('httpRequest')) {
    /**
     * http请求数据
     * 2020-09-07
     * @param string $url 请求地址
     * @param array $headers 头部
     * @param string $method 请求方式 GET\POST\PUT\DELETE
     * @param null $params 请求数据
     * @param int $time_out 超时时间
     * @return bool|mixed
     */
    function httpRequest(string $url = '', array $headers = [], string $method = 'GET', $params = null, int $time_out = 0)
    {
        if (is_array($params)) {
            if ($method == 'GET') {
                $requestString = http_build_query($params);
                if (strpos($url, '?') !== false) {
                    $url .= "&" . $requestString;
                } else {
                    $url .= "?" . $requestString;
                }
            } else {
                $requestString = json_encode($params);
            }
        } else {
            $requestString = $params ?: '';
        }
        if (empty($headers)) {
            $headers = array('Content-type: text/json');
        } elseif (!is_array($headers)) {
            parse_str($headers, $headers);
        }
        // setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, $time_out);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // setting the POST FIELD to curl
        switch ($method) {
            case "GET" :
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);
                break;
            case "PUT" :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);
                break;
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}

if (!function_exists('check_phone')) {
    /**
     * 检查手机号格式
     * @param $phone
     * @return bool
     */
    function check_phone($phone): bool
    {
        if (!preg_match("/^1[3456789]\d{9}$/", $phone)) {
            return false;
        }
        return true;
    }
}

if (!function_exists('getNumbers')) {
    /**
     * 获取编号
     * @param $type -生成类型
     * @param $length  -生成长度
     * @param $quantity -生成个数
     * @return string
     */
    function getNumbers($type, $length, $quantity): string {
        $numbers = [];

        for($q = 0; $q < $quantity; $q++) {
            $str = '';
            switch ($type){
                case 'order_sn':
                    $str .= 'O' . date('Ymd');
                    break;
                case 'number':
                    $str .= 'H' . date('Ymd');
                    break;
                case 'withdrawal':
                    $str .= 'W' . date('Ymd');
                    break;
                case 'voice':
                    $str .= 'V' . date('Ymd');
                    break;
            }
            for ($i = 0; $i < $length; $i++) {
                $str .= rand(0, 9);
            }
            $numbers[] = $str;
        }

        return implode(',', $numbers);
    }
}

if (!function_exists('timezongTime')) {
  
    function timezongTime($timezong,$date)
    {
        if($timezong != '-8'){
            $date = date('Y-m-d H:i', strtotime('+1 hour', strtotime($date)));
        }
        return $date;
    }
}

if (!function_exists('timezongTimestamp')) {
  
    function timezongTimestamp($timezong,$timestamp)
    {
       
        if($timezong != '-8'){
            $timestamp = strtotime('-1 hour', $timestamp);
        }
        return $timestamp;
    }
}