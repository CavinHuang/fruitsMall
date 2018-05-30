<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/30 0030
 * Time: 下午 4:37
 */

namespace app\index\service;

use think\Db;

class userService {

  /**
   * 地址添加/编辑
   * @param $user_id 用户id
   * @param $user_id 地址id(编辑时需传入)
   * @return array
   */
  public function add_address($user_id, $address_id=0, $data){
    $post = returnNeedFields($data, 'mid,username,phone,province,city,area,address,status,is_default');

    if($address_id == 0)
    {
      $c = Db::name('StoreMemberAddress')->where("mid", $user_id)->count();
      if($c >= 20)
        return array('status'=>-1,'msg'=>'最多只能添加20个收货地址','result'=>'');
    }

    //检查手机格式
    if($post['username'] == '')
      return array('status'=>-1,'msg'=>'收货人不能为空','result'=>'');
    if (!(intval($post['province']) > 0)|| !(intval($post['city'])>0) || !(intval($post['area'])>0))
      return array('status'=>-1,'msg'=>'所在地区不能为空','result'=>'');
    if(!$post['address'])
      return array('status'=>-1,'msg'=>'地址不能为空','result'=>'');
    if(!check_mobile($post['phone']) && !check_telephone($post['phone']))
      return array('status'=>-1,'msg'=>'手机号码格式有误','result'=>'');

    //编辑模式
    if($address_id > 0){

      $address = Db::name('StoreMemberAddress')->where(array('id'=>$address_id,'mid'=> $user_id))->find();
      if($post['is_default'] == 1 && $address['is_default'] != 1)
        Db::name('StoreMemberAddress')->where(array('mid'=>$user_id))->save(array('is_default'=>0));
      $row = Db::name('StoreMemberAddress')->where(array('id'=>$address_id,'mid'=> $user_id))->update($post);
      if($row !== false){
        return array('status'=>1,'msg'=>'编辑成功','result'=>$address_id);
      }else{
        return array('status'=>-1,'msg'=>'操作完成','result'=>$address_id);
      }

    }
    //添加模式
    $post['mid'] = $user_id;

    // 如果目前只有一个收货地址则改为默认收货地址
    $c = Db::name('StoreMemberAddress')->where("mid", $user_id)->count();
    if($c == 0)  $post['is_default'] = 1;

    $address_id = Db::name('StoreMemberAddress')->insert($post);
    //如果设为默认地址
    $insert_id = DB::name('user_address')->getLastInsID();
    $map['mid'] = $user_id;
    $map['id'] = array('neq',$insert_id);

    if($post['is_default'] == 1)
      Db::name('StoreMemberAddress')->where($map)->update(array('is_default'=>0));
    if(!$address_id)
      return array('status'=>-1,'msg'=>'添加失败','result'=>'');


    return array('status'=>1,'msg'=>'添加成功','result'=>$address_id);
  }
}
