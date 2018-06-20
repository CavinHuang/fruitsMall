<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/31 0031
 * Time: 下午 3:25
 */

namespace app\index\controller;


use app\index\model\StoreOrder;
use controller\BaseHome;
use service\DataService;
use service\Shipping;
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
    $order_express = Db::name('StoreOrderExpress')->where('order_no', $this->request->get('order_no'))->find();

    $this->assign('order_express', $order_express);

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

  /**
   * 订单列表
   * @return mixed|void
   * @throws \think\exception\DbException
   * @author 作者
   * @date   2018/6/15 0015 上午 11:27
   *
   */
  public function order_list () {
    $status = input('status', 0);
    $page = input('p', 1);
    $pagesize = input('pagesize', 10);
    $where = [];

    if ($status) {
      $where['status'] = $status;
    }

    $orderMdl = new StoreOrder();

    $result = $orderMdl->with('goods_list')->with('storeExpress')->where($where)->paginate($pagesize, false, ['page' => $page, 'query' => ['status' => $status]]);
    $order_status_desc = [0 => '订单失效', 1 => '待付款', 2 => '待发货', 3 => '待收货', 4 => '已完成'];

    if ($this->request->isAjax()) {
      return $this->AjaxSuccess('查询成功', $result);
    } else {
      return $this->fetch('order_list', ['lists' => $result, 'status' => $status, 'order_status_desc' => $order_status_desc]);
    }
  }

  /**
   *
   * @return mixed
   * @author 作者
   * @date   2018/6/15 0015 下午 4:16
   *
   */
  public function order_topay () {

    $order_sn = $this->request->get('order_no');
    return $this->fetch('order_topay', ['order_no' => $order_sn]);
  }

  /**
   * 订单处理
   * @author 作者
   * @date   2018/6/15 0015 上午 11:28
   *
   */
  public function order_manger () {
    if ($this->request->get('order_no')) {
      $order_info = Db::name('StoreOrder')->where('order_no', $this->request->get('order_no'))->find();

      $order_goods = Db::name('StoreOrderGoods')->where('order_no', $this->request->get('order_no'))->select();

      $order_express = Db::name('StoreOrderExpress')->where('order_no', $this->request->get('order_no'))->find();

      $order_status_desc = [0 => '订单失效', 1 => '待付款', 2 => '待发货', 3 => '待收货', 4 => '已完成'];

      $express = Db::name('StoreExpress')->where(['status' => 1, 'is_deleted' => 0])->order('status desc,sort desc,id desc')->select();

      $send_address = '';
      $send_address_ids = '';
      if (sysconf('send_province')) {
        $send_address_ids .= sysconf('send_province') .',';
      }

      if (sysconf('send_city')) {
        $send_address_ids .= sysconf('send_city') . ',';
      }

      if (sysconf('send_area')) {
        $send_address_ids .= sysconf('send_area') . ',';
      }

      $address_info = Db::name('StoreRegion')->whereIn('id', $send_address_ids)->select();

      foreach ($address_info as $k => $v) {
        $send_address .= $v['name'];
      }

      $send_address .= sysconf('send_address');
      $this->assign('express', $express);
      $this->assign('order_info', $order_info);
      $this->assign('order_goods', $order_goods);
      $this->assign('order_status_desc', $order_status_desc);
      $this->assign('send_address', $send_address);
      $this->assign('order_express', $order_express);
    }
    return $this->fetch();
  }

  /**
   * 订单处理
   * @author 作者
   * @date   2018/6/15 0015 下午 4:14
   *
   */
  public function order_handle () {
    $order_no = $this->request->get('order_no');
    if (!$order_no) {
      return $this->error('缺少订单号');
    }
    $status = $this->request->get('status');

    $orderMdl = new StoreOrder();
    $order_info = $orderMdl->where('order_no', $order_no)->find();

    if (!$order_info) {
      return $this->error('没有这样的订单');
    }

    $order_info->status = $status;
    $order_info->is_pay = 1;

    if ($order_info->save()) {
      return $this->success('操作成功');
    } else {
      return $this->error('操作失败');
    }

  }


  /**
   * 发货
   * @author 作者
   * @date   2018/6/19 0019 下午 3:35
   *
   */
  public function setExpress () {

    $data = $this->request->post();

    $data['send_at'] = date('Y-m-d H:i:s');
    $data['status'] = 1;
    $express = Db::name('StoreExpress')->where('express_code', $data['company_code'])->find();
    $data['send_company_title'] = $express['express_title'];
    $data['send_company_code'] = $data['company_code'];
    Db::startTrans();

    try {
      Db::name('StoreOrderExpress')->where('order_no', $data['order_no'])->update($data);
      Db::name('StoreOrder')->where('order_no', $data['order_no'])->update(['status' => 3]);
      Db::commit();

      return json(['code' => 1, 'msg' => '成功']);
    } catch (\Exception $e) {
      Log::write('----------------------发货'.$e);
      Db::rollback();

      return json(['code' => 1, 'msg' => '失败']);
    }

  }

  public function shipping () {
    $express_code = $this->request->get('express_code');
    $express_no = $this->request->get('express_no');
    return $this->fetch('shipping', ['express_code' => $express_code, 'express_no' => $express_no]);
  }
}
