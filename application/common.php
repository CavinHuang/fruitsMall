<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

use service\DataService;
use service\NodeService;
use think\Db;

/**
 * 打印输出数据到文件
 * @param mixed $data 输出的数据
 * @param bool $force 强制替换
 * @param string|null $file
 */
function p($data, $force = false, $file = null)
{
    is_null($file) && $file = env('runtime_path') . date('Ymd') . '.txt';
    $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . PHP_EOL;
    $force ? file_put_contents($file, $str) : file_put_contents($file, $str, FILE_APPEND);
}

/**
 * RBAC节点权限验证
 * @param string $node
 * @return bool
 */
function auth($node)
{
    return NodeService::checkAuthNode($node);
}

/**
 * 设备或配置系统参数
 * @param string $name 参数名称
 * @param bool $value 默认是null为获取值，否则为更新
 * @return string|bool
 * @throws \think\Exception
 * @throws \think\exception\PDOException
 */
function sysconf($name, $value = null)
{
    static $config = [];
    if ($value !== null) {
        list($config, $data) = [[], ['name' => $name, 'value' => $value]];
        return DataService::save('SystemConfig', $data, 'name');
    }
    if (empty($config)) {
        $config = Db::name('SystemConfig')->column('name,value');
    }
    return isset($config[$name]) ? $config[$name] : '';
}

/**
 * 日期格式标准输出
 * @param string $datetime 输入日期
 * @param string $format 输出格式
 * @return false|string
 */
function format_datetime($datetime, $format = 'Y年m月d日 H:i:s')
{
    return date($format, strtotime($datetime));
}

/**
 * UTF8字符串加密
 * @param string $string
 * @return string
 */
function encode($string)
{
    list($chars, $length) = ['', strlen($string = iconv('utf-8', 'gbk', $string))];
    for ($i = 0; $i < $length; $i++) {
        $chars .= str_pad(base_convert(ord($string[$i]), 10, 36), 2, 0, 0);
    }
    return $chars;
}

/**
 * UTF8字符串解密
 * @param string $string
 * @return string
 */
function decode($string)
{
    $chars = '';
    foreach (str_split($string, 2) as $char) {
        $chars .= chr(intval(base_convert($char, 36, 10)));
    }
    return iconv('gbk', 'utf-8', $chars);
}

/**
 * 下载远程文件到本地
 * @param string $url 远程图片地址
 * @return string
 */
function local_image($url)
{
    return \service\FileService::download($url)['url'];
}

/**
 * 过滤数组元素前后空格 (支持多维数组)
 * @param $array 要过滤的数组
 * @return array|string
 */
function trim_array_element($array){
  if(!is_array($array))
    return trim($array);
  return array_map('trim_array_element',$array);
}

/**
 * 检查手机号码格式
 * @param $mobile 手机号码
 */
function check_mobile($mobile){
  if(preg_match('/1[34578]\d{9}$/',$mobile))
    return true;
  return false;
}

/**
 * 检查固定电话
 * @param $mobile
 * @return bool
 */
function check_telephone($mobile){
  if(preg_match('/^([0-9]{3,4}-)?[0-9]{7,8}$/',$mobile))
    return true;
  return false;
}

/**
 * 检查邮箱地址格式
 * @param $email 邮箱地址
 */
function check_email($email){
  if(filter_var($email,FILTER_VALIDATE_EMAIL))
    return true;
  return false;
}


/**
 *   实现中文字串截取无乱码的方法
 */
function getSubstr($string, $start, $length) {
  if(mb_strlen($string,'utf-8')>$length){
    $str = mb_substr($string, $start, $length,'utf-8');
    return $str.'...';
  }else{
    return $string;
  }
}


/**
 * 判断当前访问的用户是  PC端  还是 手机端  返回true 为手机端  false 为PC 端
 * @return boolean
 */
/**
　　* 是否移动端访问访问
　　*
　　* @return bool
　　*/
function isMobile()
{
  // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
  if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    return true;

  // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
  if (isset ($_SERVER['HTTP_VIA']))
  {
    // 找不到为flase,否则为true
    return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
  }
  // 脑残法，判断手机发送的客户端标志,兼容性有待提高
  if (isset ($_SERVER['HTTP_USER_AGENT']))
  {
    $clientkeywords = array ('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile');
    // 从HTTP_USER_AGENT中查找手机浏览器的关键字
    if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
      return true;
  }
  // 协议法，因为有可能不准确，放到最后判断
  if (isset ($_SERVER['HTTP_ACCEPT']))
  {
    // 如果只支持wml并且不支持html那一定是移动设备
    // 如果支持wml和html但是wml在html之前则是移动设备
    if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
    {
      return true;
    }
  }
  return false;
}

function is_weixin() {
  if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
    return true;
  } return false;
}


function is_qq() {
  if (strpos($_SERVER['HTTP_USER_AGENT'], 'QQ') !== false) {
    return true;
  } return false;
}
function is_alipay() {
  if (strpos($_SERVER['HTTP_USER_AGENT'], 'AlipayClient') !== false) {
    return true;
  } return false;
}

//php获取中文字符拼音首字母
function getFirstCharter($str){
  if(empty($str))
  {
    return '';
  }
  $fchar=ord($str{0});
  if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
  $s1=iconv('UTF-8','gb2312//TRANSLIT//IGNORE',$str);
  $s2=iconv('gb2312','UTF-8//TRANSLIT//IGNORE',$s1);
  $s=$s2==$str?$s1:$str;
  $asc=ord($s{0})*256+ord($s{1})-65536;
  if($asc>=-20319&&$asc<=-20284) return 'A';
  if($asc>=-20283&&$asc<=-19776) return 'B';
  if($asc>=-19775&&$asc<=-19219) return 'C';
  if($asc>=-19218&&$asc<=-18711) return 'D';
  if($asc>=-18710&&$asc<=-18527) return 'E';
  if($asc>=-18526&&$asc<=-18240) return 'F';
  if($asc>=-18239&&$asc<=-17923) return 'G';
  if($asc>=-17922&&$asc<=-17418) return 'H';
  if($asc>=-17417&&$asc<=-16475) return 'J';
  if($asc>=-16474&&$asc<=-16213) return 'K';
  if($asc>=-16212&&$asc<=-15641) return 'L';
  if($asc>=-15640&&$asc<=-15166) return 'M';
  if($asc>=-15165&&$asc<=-14923) return 'N';
  if($asc>=-14922&&$asc<=-14915) return 'O';
  if($asc>=-14914&&$asc<=-14631) return 'P';
  if($asc>=-14630&&$asc<=-14150) return 'Q';
  if($asc>=-14149&&$asc<=-14091) return 'R';
  if($asc>=-14090&&$asc<=-13319) return 'S';
  if($asc>=-13318&&$asc<=-12839) return 'T';
  if($asc>=-12838&&$asc<=-12557) return 'W';
  if($asc>=-12556&&$asc<=-11848) return 'X';
  if($asc>=-11847&&$asc<=-11056) return 'Y';
  if($asc>=-11055&&$asc<=-10247) return 'Z';
  return null;
}

/**
 * 获取整条字符串汉字拼音首字母
 * @param $zh
 * @return string
 */
function pinyin_long($zh){
  $ret = "";
  $s1 = iconv("UTF-8","gb2312", $zh);
  $s2 = iconv("gb2312","UTF-8", $s1);
  if($s2 == $zh){$zh = $s1;}
  for($i = 0; $i < strlen($zh); $i++){
    $s1 = substr($zh,$i,1);
    $p = ord($s1);
    if($p > 160){
      $s2 = substr($zh,$i++,2);
      $ret .= getFirstCharter($s2);
    }else{
      $ret .= $s1;
    }
  }
  return $ret;
}

/**
 * @methods
 * @desc
 * @author slide
 *
 */
function returnNeedFields ($data, $fields = '') {
  if (!$fields || $fields == '') return $data;
  $newData = [];

  $fieldsArr = explode(',', $fields);

  foreach ($fieldsArr as $k => $v) {
    if (isset($data[$v]))
      $newData[$v] = $data[$v];
  }

  return $newData;
}
