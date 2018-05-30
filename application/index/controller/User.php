<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29 0029
 * Time: 上午 11:12
 */

namespace app\index\controller;

use app\index\service\userService;
use controller\BaseHome;
use think\Db;

/**
 * Class user
 *
 * @package app\index\controller
 */
class User extends BaseHome {

  /**
   * 会员中心页面
   * @methods
   * @desc
   * @author slide
   *
   */
  public function index () {
    $user = Db::name('StoreMember')->find($this->userId);
    // 更新session
    session('user_info', $user);
    return $this->fetch('index', ['user' => $user]);
  }

  /**
   * 会员信息修改页面
   * @methods
   * @desc
   * @author slide
   * @return mixed
   *
   */
  public function edit () {

    $user = Db::name('StoreMember')->find($this->userId);

    return $this->fetch('edit', ['user' => $user]);
  }

  /**
   * 修改用户信息
   * @methods
   * @desc
   * @author slide
   */
  public function doEdit () {
    $data = $this->request->post();

    $result = Db::name('StoreMember')->where('id', $this->userId)->strict(true)->update($data);

    if ($result) {
      return $this->AjaxSuccess('更新成功');
    } else {
      return $this->AjaxError('更新失败');
    }
  }

  /**
   * 收货地址列表
   * @methods
   * @desc
   * @author slide
   * @return mixed
   *
   */
  public function address_list () {
    $source = input('source');

    $lists = Db::name('StoreMemberAddress')->where(['mid' => $this->userId])->select();
    $region_list = Db::name('StoreRegion')->cache(true)->field('id,name')->select();

    return $this->fetch('address_list', ['lists' => $lists, 'region_list' => $region_list, 'source' => $source]);
  }

  /**
   * 添加收货地址
   * @methods
   * @desc
   * @author slide
   * @return mixed
   *
   */
  public function add_address ($id = '') {

    $source = input('source');

    if ($this->request->isPost()) {
      $post_data = input('post.');
      $logic = new userService();
      $data = $logic->add_address($this->userId, 0, $post_data);
      $goods_id = input('goods_id/d');
      $item_id = input('item_id/d');
      $goods_num = input('goods_num/d');
      $order_id = input('order_id/d');
      $action = input('action');
      if ($data['status'] != 1){
        $this->error($data['msg']);
      } elseif ($source == 'cart-index') {
        $data['url']=url('Index/Cart/index', array('address_id' => $data['result'],'goods_id'=>$goods_id,'goods_num'=>$goods_num,'item_id'=>$item_id,'action'=>$action));
        return json($data);
      } elseif ($_POST['source'] == 'integral') {
        $data['url']=url('/Mobile/Cart/integral', array('address_id' => $data['result'],'goods_id'=>$goods_id,'goods_num'=>$goods_num,'item_id'=>$item_id));
        return json($data);
      } elseif($source == 'pre_sell_cart'){
        $data['url']=url('/Mobile/Cart/pre_sell_cart', array('address_id' => $data['result'],'act_id'=>$post_data['act_id'],'goods_num'=>$post_data['goods_num']));
        return json($data);
      } elseif($_POST['source'] == 'team'){
        $data['url']= url('/Mobile/Team/order', array('address_id' => $data['result'],'order_id'=>$order_id));
        return json($data);
      }else{
        $data['url']= url('/Mobile/User/address_list');
        return json($data);
      }

    }

    $address = Db::name('StoreMemberAddress')->find($id);
    $p = Db::name('StoreRegion')->where(array('parent_id' => 0, 'level' => 1))->select();
    $this->assign('province', $p);
    $this->assign('source', $source);
    return $this->fetch('add_address', ['address' => $address]);
  }

  /**
   * 编辑地址
   * @methods
   * @desc
   * @author slide
   *
   * @param int $id
   *
   * @return mixed
   *
   */
  public function edit_address() {
    $id = input('id');
    $address = Db::name('StoreMemberAddress')->where(['id' => $id])->find();
    if ($this->request->isPost()) {
      $source = input('source');
      $goods_id = input('goods_id/d');
      $item_id = input('item_id/d');
      $goods_num = input('goods_num/d');
      $action = input('action');
      $order_id = input('order_id/d');
      $post_data = input('post.');
      $logic = new userService();
      $data = $logic->add_address($this->userId, $id, $post_data);
      if ($post_data['source'] == 'cart-index') {
        $data['url']=url('Cart/index', array('address_id' => $data['result'],'goods_id'=>$goods_id,'goods_num'=>$goods_num,'item_id'=>$item_id,'action'=>$action));
        return json($data);
      } elseif ($_POST['source'] == 'integral') {
        $data['url'] = url('/Mobile/Cart/integral', array('address_id' => $data['result'],'goods_id'=>$goods_id,'goods_num'=>$goods_num,'item_id'=>$item_id));
        return json($data);
      } elseif($source == 'pre_sell_cart'){
        $data['url'] = url('/Mobile/Cart/pre_sell_cart', array('address_id' => $data['result'],'act_id'=>$post_data['act_id'],'goods_num'=>$post_data['goods_num']));
        return json($data);
      } elseif($_POST['source'] == 'team'){
        $data['url']= url('/Mobile/Team/order', array('address_id' => $data['result'],'order_id'=>$order_id));
        return json($data);
      } else{
        $data['url']= url('/Mobile/User/address_list');
        return json($data);
      }
    }
    //获取省份
    $p = Db::name('StoreRegion')->where(array('parent_id' => 0, 'level' => 1))->select();
    $c = Db::name('StoreRegion')->where(array('parent_id' => $address['province'], 'level' => 2))->select();
    $d = Db::name('StoreRegion')->where(array('parent_id' => $address['city'], 'level' => 3))->select();
    if (isset($address['twon'])) {
      $e = Db::name('StoreRegion')->where(array('parent_id' => $address['district'], 'level' => 4))->select();
      $this->assign('twon', $e);
    }
    $this->assign('province', $p);
    $this->assign('city', $c);
    $this->assign('district', $d);
    $this->assign('address', $address);
    return $this->fetch();
  }
}
