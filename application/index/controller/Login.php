<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29 0029
 * Time: 上午 9:42
 */

namespace app\index\controller;


use controller\BaseHome;
use service\DataService;
use think\Db;
use think\facade\Session;

/**
 * Class Login
 *
 * @package app\index\controller
 */
class Login extends BaseHome {

  /**
   * 登录页面
   * @methods
   * @desc
   * @author slide
   * @return mixed
   *
   */
  public function index () {

    return $this->fetch();
  }

  /**
   * 注册页面
   * @methods
   * @desc
   * @author slide
   * @return mixed
   *
   */
  public function register () {

    return $this->fetch();
  }

  /**
   * 登录
   * @methods
   * @desc
   * @author slide
   *
   */
  public function doLogin () {

    $data = $this->request->post();

    if (!isset($data['phone']) || $data['phone'] == '') return $this->AjaxError('电话不能为空');
    if (!isset($data['password']) || $data['password'] == '') return $this->AjaxError('密码不能为空');

    $user = Db::name('StoreMember')->where('phone', $data['phone'])->find();

    if (!$user) return $this->AjaxError('用户不存在,请先注册!');

    if ($user['password'] != md5($data['password'])) return $this->AjaxError('用户名和密码不匹配');

    session('user_id', $user['id']);
    session('user_info', $user);

    return $this->AjaxSuccess('登录成功');

  }

  /**
   * 注册
   * @methods
   * @desc
   * @author slide
   *
   */
  public function doRegister () {

    $data = $this->request->post();

    if( !captcha_check($data['verfiy'] )) return $this->AjaxError('验证码错误');
    if (!isset($data['phone']) || $data['phone'] == '') return $this->AjaxError('电话不能为空');
    if (!isset($data['password']) || $data['password'] == '') return $this->AjaxError('密码不能为空');
    if ($data['password'] != $data['password_confirm']) return $this->AjaxError('两次密码不一致');

    $user = Db::name('StoreMember')->where('phone', $data['phone'])->find();

    if ($user) return $this->AjaxError('用户已经存在，请登录!');

    $data['password'] = md5($data['password']);
    $result = DataService::save('StoreMember', $data);

    if ($result) {
      return $this->AjaxSuccess('注册成功');
    } else {
      return $this->AjaxError('注册失败');
    }

  }

  public function login_out () {
    session('user_id', null);
    session('user_info', null);
    Session::delete('user_id');
    Session::delete('user_info');
  }
}
