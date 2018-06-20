<?php

namespace service;

use think\Controller;
use think\Config;
/**
 * 物流查询结构
 */
class Shipping
{
  protected static $stance = null;
  public function getShipping($shipping_code = '', $logisticCode = '', $OrderCode = ''){
    if(!$shipping_code || !$logisticCode) $this->error('缺少必要参数');
    $requestData= "{'OrderCode':'{$OrderCode}','ShipperCode':'{$shipping_code}','LogisticCode':'{$logisticCode}'}";
    $datas = array(
          'EBusinessID' => config('shipping.EBusinessID'),
          'RequestType' => '1002',
          'RequestData' => urlencode($requestData) ,
          'DataType' => '2',
      );
    $datas['DataSign'] = self::encrypt($requestData, config('shipping.AppKey'));
    $result = $this->sendPost(config('shipping.ReqURL'), $datas);
    // dump();
    return json_decode($result, true);
  }
  public static function getInstance () {
    if (is_null(self::$stance)) {
      self::$stance = new self();
    }
    return self::$stance;
  }

  /**
   * 电商Sign签名生成
   * @param data 内容
   * @param appkey Appkey
   * @return DataSign签名
   */
  public static function encrypt($data, $appkey) {
      return urlencode(base64_encode(md5($data.$appkey)));
  }

  /**
   *  post提交数据
   * @param  string $url 请求Url
   * @param  array $datas 提交的数据
   * @return url响应返回的html
   */
  public function sendPost($url, $datas) {
    $temps = array();
    foreach ($datas as $key => $value) {
      $temps[] = sprintf('%s=%s', $key, $value);
    }
    $post_data = implode('&', $temps);
    $url_info = parse_url($url);
    // dump($url);
    // dump($url_info);
    if(empty($url_info['port']))
    {
      $url_info['port']=80;
    }
    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
    $httpheader.= "Host:" . $url_info['host'] . "\r\n";
    $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
    $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
    $httpheader.= "Connection:close\r\n\r\n";
    $httpheader.= $post_data;
    $fd = fsockopen($url_info['host'], $url_info['port']);
    fwrite($fd, $httpheader);
    $gets = "";
    $headerFlag = true;
    while (!feof($fd)) {
      if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
        break;
      }
    }
    while (!feof($fd)) {
      $gets.= fread($fd, 128);
    }
    fclose($fd);

    return $gets;
  }
}
