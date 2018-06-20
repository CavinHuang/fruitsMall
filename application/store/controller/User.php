<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/20 0020
 * Time: 上午 9:48
 */

namespace app\store\controller;


use controller\BasicAdmin;
use service\DataService;
use think\Db;

class User extends BasicAdmin {

  public $table = "StoreMember";

  /**
   * 商城会员列表
   * @return array|string
   * @throws \think\Exception
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\ModelNotFoundException
   * @throws \think\exception\DbException
   * @author 作者
   * @date   2018/6/20 0020 上午 9:51
   *
   */
  public function index () {
    $this->title = "商城会员管理";
    list($get, $db) = [$this->request->get(), Db::name($this->table)];
    foreach (['nickname', 'phone', 'openid'] as $key) {
      (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
    }
    if (isset($get['date']) && $get['date'] !== '') {
      list($start, $end) = explode(' - ', $get['date']);
      $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
    }
    return parent::_list($db->where(['status' => '1']));
  }

  /**
   * 用户添加
   * @return array|string
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\ModelNotFoundException
   * @throws \think\exception\DbException
   * @throws \think\Exception
   */
  public function add()
  {
    return $this->_form($this->table, 'form');
  }

  /**
   * 表单数据默认处理
   * @param array $data
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\ModelNotFoundException
   * @throws \think\exception\DbException
   */
  public function _form_filter(&$data)
  {
    if ($this->request->isPost()) {
      if (isset($data['id'])) {
        unset($data['phone']);
      } elseif (Db::name($this->table)->where(['phone' => $data['phone']])->count() > 0) {
        $this->error('用户账号已经存在，请使用其它账号！');
      }
    }
  }
  /**
   * 用户编辑
   * @return array|string
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\ModelNotFoundException
   * @throws \think\exception\DbException
   * @throws \think\Exception
   */
  public function edit()
  {
    return $this->_form($this->table, 'form');
  }
  /**
   * 用户密码修改
   * @return array|string
   * @throws \think\Exception
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\ModelNotFoundException
   * @throws \think\exception\DbException
   * @throws \think\exception\PDOException
   */
  public function pass()
  {
    if ($this->request->isGet()) {
      $this->assign('verify', false);
      return $this->_form($this->table, 'pass');
    }
    $post = $this->request->post();
    if ($post['password'] !== $post['repassword']) {
      $this->error('两次输入的密码不一致！');
    }
    $data = ['id' => $post['id'], 'password' => md5($post['password'])];
    if (DataService::save($this->table, $data, 'id')) {
      $this->success('密码修改成功，下次请使用新密码登录！', '');
    }
    $this->error('密码修改失败，请稍候再试！');
  }

  /**
   * 删除用户
   * @throws \think\Exception
   * @throws \think\exception\PDOException
   */
  public function del()
  {
    if (in_array('10000', explode(',', $this->request->post('id')))) {
      $this->error('系统超级账号禁止删除！');
    }
    if (DataService::update($this->table)) {
      $this->success("用户删除成功！", '');
    }
    $this->error("用户删除失败，请稍候再试！");
  }

  /**
   * 用户禁用
   * @throws \think\Exception
   * @throws \think\exception\PDOException
   */
  public function forbid()
  {
    if (DataService::update($this->table)) {
      $this->success("用户禁用成功！", '');
    }
    $this->error("用户禁用失败，请稍候再试！");
  }

  /**
   * 用户禁用
   * @throws \think\Exception
   * @throws \think\exception\PDOException
   */
  public function resume()
  {
    if (DataService::update($this->table)) {
      $this->success("用户启用成功！", '');
    }
    $this->error("用户启用失败，请稍候再试！");
  }
}
