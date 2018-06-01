<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/30 0030
 * Time: 上午 11:09
 */

namespace app\index\controller;


use controller\BaseHome;
use service\DataService;
use think\Db;
use think\facade\Log;

class Cart extends BaseHome {

  public $freight_price = 0;

  /**
   * 购物车结算页面
   * @methods
   * @desc
   * @author slide
   *
   * @param int $address_id
   *
   * @return mixed
   *
   */
  public function index ( $address_id = 0) {

    $where = ['mid' => $this->userId];

    if ($address_id) {
      $where['id'] = $address_id;
    } else {
      $where['is_default'] = 1;
    }
    $address = Db::name('StoreMemberAddress')->where($where)->find();

    // 补全省市区
    $address_ids = '';
    if ($address['province']) {
      $address_ids .= $address['province'].',';
    }
    if ($address['city']) {
      $address_ids .= $address['city'].',';
    }
    if ($address['area']) {
      $address_ids .= $address['area'];
    }
    $address_res = Db::name('StoreRegion')->field('name')->whereIn('id', $address_ids)->select();
    $address_str = '';
    foreach ($address_res as $k => $v) {
      $address_str.=$v['name'];
    }
    $address['address'] = $address_str . $address['address'];

    // 商品
    $cartList = Db::name('StoreShopcart')->where(['user_id' => $this->userId, 'checked' => 1, 'is_deleted' => 0])->select();

    return $this->fetch('index', ['address' => $address, 'cartList' => $cartList]);
  }

  public function cart2 () {
    // get goods and address, computed goods price, add order table

    $post_data = $this->request->post();

    // get address
    $address = Db::name('StoreMemberAddress')->find($post_data['address_id']);
    // 补全省市区
    $address_ids = '';
    if ($address['province']) {
      $address_ids .= $address['province'].',';
    }
    if ($address['city']) {
      $address_ids .= $address['city'].',';
    }
    if ($address['area']) {
      $address_ids .= $address['area'];
    }
    $address_res = Db::name('StoreRegion')->field('name')->whereIn('id', $address_ids)->select();
    $address_str = '';
    foreach ($address_res as $k => $v) {
      $address_str.=$v['name'];
    }
    $address['address'] = $address_str . $address['address'];


    // get goods
    $cartList = Db::name('StoreShopcart')->where(['user_id' => $this->userId, 'checked' => 1, 'is_deleted' => 0])->select();

    // computed price
    $total_selling_price = 0;
    $total_market_price = 0;
    $total_number = 0;

    $cart_goods = [];

    $order_sn = DataService::createSequence(8, 'ORDER', 'E');

    // shop cart ids
    $shop_cart_ids = '';

    $spec_ids = "";

    $stock_sql = "UPDATE store_goods_list SET goods_stock = CASE id ";

    foreach ($cartList as $k => $v) {

      $stock_sql .= " WHEN ". $v['spec_id'] ." THEN goods_stock - ". $v['number'];
      $spec_ids .= $v['spec_id'] . ",";

      $self_total_selling_price = $v['selling_price'] * $v['number'];
      $self_total_market_price = $v['market_price'] * $v['number'];

      $total_selling_price += $self_total_selling_price;
      $total_market_price += $self_total_market_price;
      $total_number += $v['number'];

      $shop_cart_ids .= $v['id'] .',';

      // set order goods data
      $cart_goods[] = [
        'mid'           => $this->userId,
        'order_no'      => $order_sn,
        'goods_id'      => $v['goods_id'],
        'goods_title'   => $v['goods_title'],
        'goods_spec'    => $v['goods_specifition_name_value'],
        'goods_spec_id' => $v['spec_id'],
        'goods_logo'    => $v['list_pic_url'],
        'market_price'  => $v['market_price'],
        'selling_price' => $v['selling_price'],
        'price_field'   => 'selling_price',
        'number'        => $v['number'],
        'status'        => 1
      ];
    }
    $spec_ids = substr($spec_ids, 0, strlen($spec_ids) - 1);
    $stock_sql .= " END WHERE id IN (" . $spec_ids .")";

    // set order data
    $order_data = [
      'consignee'     => $address['username'],
      'phone'         => $address['phone'],
      'address'       => $address['address'],
      'type'          => 1, // order type
      'mid'           => $this->userId,
      'order_no'      => $order_sn,
      'freight_price' => $this->freight_price,
      'goods_price'   => $total_selling_price,
      'real_price'    => $total_selling_price + $this->freight_price,
      'is_pay'        => 0,
      'desc'          => '下单成功',
      'status'        => 1
    ];

    Db::startTrans();

    try{

      // save order
      Db::name('StoreOrder')->insert($order_data);

      // get last order insert id
      $last_order_id = Db::name('StoreOrder')->getLastInsID();

      // save order goods
      Db::name('StoreOrderGoods')->insertAll($cart_goods);

      // delete shop cart item
      Db::name('StoreShopcart')->whereIn('id', $shop_cart_ids)->update(['is_deleted' => 1]);

      //TODO update goods stock
      Log::write($stock_sql);
      Db::execute($stock_sql);

      Db::commit();

      return json(['status' => 1, 'msg' => '下单成功', 'data' => ['order_no' => $order_sn, 'id' => $last_order_id]]);
    }catch (\Exception $e) {
      Log::write('下单失败'.$e);
      Db::rollback();
      $this->AjaxError('下单失败，请重试');
    }
  }
}
