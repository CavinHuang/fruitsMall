<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/26 0026
 * Time: 下午 2:17
 */

namespace app\cms\controller;

use controller\BasicAdmin;
use service\DataService;
use think\Db;

class Banner extends BasicAdmin {

  public $table = 'cmsBanner';
  public $space = [1 => '首页顶部'];

  /**
   * 轮播图列表
   * @return array|string
   * @throws \think\Exception
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\ModelNotFoundException
   * @throws \think\exception\DbException
   * @author 作者
   * @date   xxx
   *
   */
  public function index() {
    $this->title = '轮播图列表';
    $db = Db::name($this->table)->where('is_deleted', 0);
    return parent::_list($db->order('sort asc,id asc'), false, true, false, ['space' => $this->space]);
  }

  /**
   * 添加轮播图
   * @return array|string
   * @throws \think\Exception
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\ModelNotFoundException
   * @throws \think\exception\DbException
   * @author 作者
   * @date   xxx
   *
   */
  public function add () {

    return $this->_form($this->table, 'form', 'id', [], ['space' => $this->space]);
  }

  /**
   * 表单提交数据处理
   * @param array $data
   */
  protected function _form_filter($data)
  {
    if ($this->request->isPost()) {
      empty($data['title']) && $this->error('请填写标题');
      empty($data['img_path']) && $this->error('请上传轮播图片');
      $data['status'] = 1;
      $data['type'] = 1;
    }
  }

  /**
   * 添加成功回跳处理
   * @param bool $result
   */
  protected function _form_result($result)
  {
    if ($result !== false) {
      $this->success('数据保存成功！', "");
    }
  }

  /**
   * 删除轮播图
   * @throws \think\Exception
   * @throws \think\exception\PDOException
   */
  public function del()
  {
    if (DataService::update($this->table)) {
      $this->success("轮播图删除成功！", '');
    }
    $this->error("轮播图删除失败，请稍候再试！");
  }

  /**
   * 轮播图禁用
   * @throws \think\Exception
   * @throws \think\exception\PDOException
   */
  public function forbid()
  {
    if (DataService::update($this->table)) {
      $this->success("轮播图禁用成功！", '');
    }
    $this->error("轮播图禁用失败，请稍候再试！");
  }

  /**
   * 轮播图禁用
   * @throws \think\Exception
   * @throws \think\exception\PDOException
   */
  public function resume()
  {
    if (DataService::update($this->table)) {
      $this->success("轮播图启用成功！", '');
    }
    $this->error("轮播图启用失败，请稍候再试！");
  }
}
