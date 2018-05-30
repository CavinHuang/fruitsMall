<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use service\ImgcompressService;
// use think\Upload;

class UploadFiles extends Controller
{
  protected function _initialize() {
      parent::_initialize();

  }
  /**
   * [uploadAjax ajax上传]
   * @param    [type]                   $file         [description]
   * @param    [type]                   $to_dir       [description]
   * @param    integer                  $ajaxSuccCode [description]
   * @param    integer                  $ajaxErrCode  [description]
   * @return   {[type]                  [description]
   * @Author:  slade
   * @DateTime :2017-03-27T11:26:48+080
   */
  public function uploadAjax($file, $to_dir, $ajaxSuccCode = 1, $ajaxErrCode = 0, $compress = false){

    $file = request()->file($file);

    $info = $file->validate(['size'=>Config::get('upload_size'),'ext'=>Config::get('upload_ext')])->rule(Config::get('upload_file_name'))->move(Config::get('upload_main_path'). DS .$to_dir);

    if($info){
        // 压缩
        if(boolval($compress)){
          $img = (new ImgcompressService(WEB_ROOT.'/public/upload'. DS .$to_dir.DS.$info->getSaveName(), 1))->compressImg(WEB_ROOT.'/public/upload'. DS .$to_dir.DS.$info->getSaveName());
        }
        $path = '/public/upload/'.$to_dir.'/'.$info->getSaveName();
        $data = [
          'code' => $ajaxSuccCode,
          'path' => $path,
          'msg' => '上传成功',
          'success' => true,
        ];

    }else{
        // 上传失败获取错误信息
        $data = [
          "code" => $ajaxErrCode,
          "path" => '',
          'msg' => $file->getError()
        ];
    }
    return json($data);
  }

  /**
   * [saveBase64Image 上传base64]
   * @return   {[type]                  [description]
   * @Author:  slade
   * @DateTime :2017-03-27T11:26:21+080
   */
  function saveBase64Image($to_dir, $compress = false){
      $base64_image_content = input('formFile');
      if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
          //图片后缀
          $type = $result[2];
          //保存位置--图片名
          $image_name=date('His').str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT).".".$type;
          $path =  '/' .$to_dir. '/'.date('Y/m-d'). '/' .$image_name;
          $image_url = config('app.upload_main_path').$path;
          if(!is_dir(dirname($image_url))){
                mkdir(dirname($image_url), 0777, true);
                //chmod(dirname($image_url), 0777);
                //umask($oldumask);
          }
          //解码
          $decode=base64_decode(str_replace($result[1], '', $base64_image_content));
          if (file_put_contents($image_url, $decode)){
            $data['code']=0;
            $data['imageName']=$image_name;
            $data['url']='/static/upload'.$path;
            $data['msg']='保存成功！';
          }else{
            $data['code']=1;
            $data['imgageName']='';
            $data['url']='';
            $data['msg']='图片保存失败！';
          }
      }else{
          $data['code']=1;
          $data['imgageName']='';
          $data['url']='';
          $data['msg']='base64图片格式有误！';
      }
      return json($data);
  }
  /**
   * 删除图片
   * @param    [type]                   $path [图片地址]
   * @return   [type]                   [description]
   * @Author:  slade
   * @DateTime :2017-04-27T17:28:55+080
   */
  public function delImg($path){
    if($this->request->isPost()){
      $path = input('post.path');
      $path= str_replace('../','',$path);
      $path= trim($path,'.');
      $path= trim($path,'/');
      if($path && file_exists($path)){
        $table = input('post.table'); //获取删除的sql表
        if($table){
          //存在则进行表删除
          $id = input('post.id') ? input('post.id') : 0;
          if($id){
            $table_res = Db::name($table)->where("id='{$id}'")->delete();
            if(!$table_res){
              $this->error('删除表记录失败');
            }
          }else{
            $this->error('没有这样的记录');
          }
        }
        $size = getimagesize($path);
        $filetype = explode('/',$size['mime']);
        if($filetype[0]!='image'){
          $this->error('没有这样的图片');
        }
        if(unlink($path)){
          $this->success('删除成功');
        }else{
          $this->error('删除失败');
        }

      }else{
        $this->error('请选择需要删除的图片');
      }
    }else{
      $this->error('删除图片的方式错误');
    }
  }

  /**
   * 会员推广二维码
   * @author: slide
   *
   * @param $uid
   *
   */
  public function downloadImg( $uid, $re_name='qr_code',  $url = '', $retry=false){
    $path = WEB_ROOT.'/public/upload/user_qrcode/';
    $file = $path.$re_name.'_'.$uid.".png";
    $http_path = WEB_DOMAIN.'/public/upload/user_qrcode/'.$re_name.'_'.$uid.".png";

    $url = $url ? $url : "http://qr.topscan.com/api.php?text=http://car.weix360.cn/home/login?uid={$uid}";

    if(file_exists($file)){
      // 有文件
      if($retry){
        if(!is_dir($path)){
          mkdir($path, 0777);
        }
        $res = dlfile($url, $file);
        if($res){
          return json([
            'code' => 2000,
            'file_path' =>  $http_path
          ]);
        }else{
          return json([
            'code' => 4000,
            'file_path' => ''
          ]);
        }
      } else {
        return json([
          'code' => 2000,
          'file_path' =>  $http_path
        ]);
      }
    }else{

      if(!is_dir($path)){
        mkdir($path, 0777);
      }
      $res = dlfile($url, $file);
      if($res){
        return json([
          'code' => 2000,
          'file_path' =>  $http_path
        ]);
      }else{
        return json([
          'code' => 4000,
          'file_path' => ''
        ]);
      }
    }
  }
}
