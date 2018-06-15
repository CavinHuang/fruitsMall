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

namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use service\WechatService;
use think\Db;

/**
 * 后台参数配置控制器
 * Class Config
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:05
 */
class Config extends BasicAdmin
{

    /**
     * 当前默认数据模型
     * @var string
     */
    public $table = 'SystemConfig';

    /**
     * 当前页面标题
     * @var string
     */
    public $title = '系统参数配置';

    /**
     * 显示系统常规配置
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        if ($this->request->isGet()) {
            return $this->fetch('', ['title' => $this->title]);
        }
        if ($this->request->isPost()) {
            foreach ($this->request->post() as $key => $vo) {
                sysconf($key, $vo);
            }
            LogService::write('系统管理', '系统参数配置成功');
            $this->success('系统参数配置成功！', '');
        }
    }

    /**
     * 文件存储配置
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function file()
    {
        $this->title = '文件存储配置';
        return $this->index();
    }

  /**
   * 商城设置
   * @author 作者
   * @date   2018/6/1 0001 下午 9:36
   *
   */
    public function shop () {
      if ($this->request->isGet()) {
        $p = Db::name('StoreRegion')->where(array('parent_id' => 0, 'level' => 1))->select();
        $this->assign('province', $p);

        if (sysconf("send_province")) {
          $c = Db::name('StoreRegion')->where(array('parent_id' => sysconf("send_province"), 'level' => 2))->select();
          $this->assign('city', $c);
        }


        if (sysconf("send_city")) {
          $a = Db::name('StoreRegion')->where(array( 'parent_id' => sysconf("send_city"), 'level' => 3))->select();
          $this->assign('area', $a);
        }
        return $this->fetch('', ['title' => '商城配置']);
      }
      if ($this->request->isPost()) {
        foreach ($this->request->post() as $key => $vo) {
          sysconf($key, $vo);
        }
        LogService::write('系统管理', '商城参数配置成功');
        $this->success('商城参数配置成功！', '');
      }
    }

  /**
   * 获取市或者区
   */
  public function getRegionByParentId()
  {
    $parent_id = input('parent_id');
    $res = array('status' => 0, 'msg' => '获取失败，参数错误', 'result' => '');
    if($parent_id){
      $region_list = Db::name('StoreRegion')->field('id,name')->where(['parent_id'=>$parent_id])->select();
      $res = array('status' => 1, 'msg' => '获取成功', 'result' => $region_list);
    }
    exit(json_encode($res));
  }

}
