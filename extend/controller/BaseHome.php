<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29 0029
 * Time: 上午 9:43
 */

namespace controller;


use service\ToolsService;
use think\Controller;

class BaseHome extends Controller {

  /**
   * 返回成功的操作
   * @param string $msg 消息内容
   * @param array $data 返回数据
   */
  protected function AjaxSuccess($msg, $data = [])
  {
    ToolsService::success($msg, $data);
  }

  /**
   * 返回失败的请求
   * @param string $msg 消息内容
   * @param array $data 返回数据
   */
  protected function AjaxError($msg, $data = [])
  {
    ToolsService::error($msg, $data);
  }
}