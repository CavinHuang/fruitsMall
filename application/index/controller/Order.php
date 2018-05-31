<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/31 0031
 * Time: 下午 3:25
 */

namespace app\index\controller;


use controller\BaseHome;
use service\DataService;
use think\Db;
use think\facade\Log;

class Order extends BaseHome {

  /**
   * 订单信息
   * @return mixed
   * @author 作者
   * @date   2018/5/31 0031 下午 5:52
   *
   */
  public function order_detail () {
    $id = input('id');

    $order_info = Db::name('StoreOrder')->find($id);

    $order_goods = Db::name('StoreOrderGoods')->where('order_no', $order_info['order_no'])->select();

    $order_status_desc = [0 => '订单失效', 1 => '待付款', 2 => '待发货', 3 => '待收货', 4 => '已完成'];

    return $this->fetch('order_detail', ['order_info' => $order_info, 'order_goods' => $order_goods, 'order_status_desc' => $order_status_desc]);
  }

  /**
   * 取消订单
   * @author 作者
   * @date   2018/5/31 0031 下午 5:56
   *
   */
  public function cancel_order () {
    $id = input('id');

    Db::startTrans();

    try {

      $order_no = Db::name('StoreOrder')->where('id', $id)->value('order_no');

      Db::name('StoreOrder')->where('id', $id)->update(['status' => 0, 'is_deleted' => 1]);

      // 返还库存
      $cartList = Db::name('StoreOrderGoods')->where('order_no', $order_no)->select();

      $spec_ids = "";

      $stock_sql = "UPDATE store_goods_list SET goods_stock = CASE id ";

      foreach ($cartList as $k => $v) {

        $stock_sql .= " WHEN ". $v['goods_spec_id'] ." THEN goods_stock + ". $v['number'];
        $spec_ids .= $v['goods_spec_id'] . ",";
      }
      $spec_ids = substr($spec_ids, 0, strlen($spec_ids) - 1);
      $stock_sql .= " END WHERE id IN (" . $spec_ids .")";

      Db::execute($stock_sql);

      Db::commit();

      return json(['status' => 1, 'msg' => '取消订单成功']);
    } catch (\Exception $e) {
      Log::write('取消订单失败'.$e);
      Db::rollback();
      $this->AjaxError('取消订单失败，请重试');
    }
  }
}
