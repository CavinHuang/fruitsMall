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
  public $userId = null;
  public $userInfo = null;
  public $class_url = '';
  protected function initialize () {
    parent::initialize();
    $this->class_url = $this->request->module().'/'.$this->request->controller().'/'.$this->request->action();

    $this->assign('class_url', $this->class_url);

    // 校验登录
    if (!in_array($this->class_url, config('app.not_include_path')) && !session('user_id')) {
      $this->redirect('index/Login/index');
    }

    $this->userId = session('user_id');
    $this->userInfo = session('user_info');

  }

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
