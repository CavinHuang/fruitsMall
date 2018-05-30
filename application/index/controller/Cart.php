<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/30 0030
 * Time: 上午 11:09
 */

namespace app\index\controller;


use controller\BaseHome;
use think\Db;

class Cart extends BaseHome {

  public function index ( $address_id = 0) {

    $where = ['mid' => $this->userId];

    if ($address_id) {
      $where['id'] = $address_id;
    } else {
      $where['is_default'] = 1;
    }
    $address = Db::name('StoreMemberAddress')->where($where)->find();
    $cartList = Db::name('StoreShopcart')->where(['user_id' => $this->userId, 'checked' => 1])->select();

    return $this->fetch('index', ['address' => $address, 'cartList' => $cartList]);
  }
}
