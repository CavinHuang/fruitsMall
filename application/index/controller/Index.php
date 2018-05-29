<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\index\controller;

use app\store\service\GoodsService;
use think\Controller;
use think\Db;

/**
 * 应用入口控制器
 * @author Anyon <zoujingli@qq.com>
 */
class Index extends Controller
{

  protected $userId = 1;
  /**
   * 首页
   * @methods
   * @desc
   * @author slide
   * @return mixed
   *
   */
    public function index()
    {

      $banner = Db::name('CmsBanner')->where(['type' => 1, 'status' => 1])->select();

      $goods = $db = Db::name('StoreGoods')->field('id, goods_desc, goods_title, goods_logo, goods_content, brand_id, cate_id')->where(['is_deleted' => '0'])->order('status desc,sort asc,id desc')->paginate(10, true, ['page' => 1]);

      $list = $goods->all();
      $result = GoodsService::buildGoodsList($list);

      // dump($result['list'][0]['spec']);die;

      return $this->fetch('index', ['banner' => $banner, 'goods' => $result['list']]);
    }

  /**
   * 商品详情
   * @methods
   * @desc
   * @author slide
   *
   * @param int $gid
   *
   * @return mixed|void
   *
   */
    public function goods_info ($gid = 0) {

      if (!$gid) return $this->error('没有这样的商品', url('index'));

      $goods = $db = Db::name('StoreGoods')->field('id, goods_desc, goods_image, goods_title, goods_logo, goods_content, brand_id, cate_id')->where(['is_deleted' => '0'])->order('status desc,sort asc,id desc')->where('id', $gid)->select();

      $result = GoodsService::buildGoodsList($goods);
      $goods = $result['list'][0];
      $goods['goods_image'] = explode('|', $goods['goods_image']);

      // dump($goods);die;
      $shopCartNumber = Db::name('StoreShopcart')->where('user_id', $this->userId)->count();
      return $this->fetch('goods_info', ['good' => $goods, 'shopNum' => $shopCartNumber]);
    }

    public function addShopCart () {
      $data = $this->request->post();

      // todo 此处需要调用表里的数据进行校验和数据组合
      $data['user_id'] = $this->userId;
      $result = Db::name( 'StoreShopcart' )->insert( $data );

      if ( $result ) {
        return json( [ 'code' => 2000, 'msg' => '加入购物车成功' ] );
      } else {
        return json( [ 'code' => 4000, 'msg' => '加入购物车失败' ] );
      }
    }
}
