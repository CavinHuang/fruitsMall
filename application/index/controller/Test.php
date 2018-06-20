<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19 0019
 * Time: 上午 11:14
 */

namespace app\index\controller;


use service\Shipping;
use think\Controller;

class Test extends Controller {

  public function index () {
    // dump(Shipping::getInstance()->getShipping('YTO', '12345678'));

    return $this->fetch();
  }
}
