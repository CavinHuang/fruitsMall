<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/1 0001
 * Time: 上午 11:09
 */
namespace  app\index\model;

class StoreOrder extends \think\Model {


  public function goodsList () {

    return $this->hasMany('StoreOrderGoods', 'order_no', 'order_no');
  }
}
