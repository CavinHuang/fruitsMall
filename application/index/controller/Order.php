<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/31 0031
 * Time: 下午 3:25
 */

namespace app\index\controller;


use controller\BaseHome;
use think\Db;

class Order extends BaseHome {


  public function order_detail () {
    $id = input('id');

    $order_info = Db::name('StoreOrder')->find($id);

    $order_goods = Db::name('StoreOrderGoods')->where('order_no', $order_info['order_no'])->select();

    $order_status_desc = [0 => '订单失效', 1 => '待付款', 2 => '待发货', 3 => '待收货', 4 => '已完成'];

    return $this->fetch('order_detail', ['order_info' => $order_info, 'order_goods' => $order_goods, 'order_status_desc' => $order_status_desc]);
  }
}
