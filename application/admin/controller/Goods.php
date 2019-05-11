<?php

/**
 * Created by PhpStorm.
 * User: CHEN
 * Date: 2018/7/11
 * Time: 16:12
 */

namespace app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use app\admin\model\Good;
use app\admin\model\GoodsImages;
use think\Session;
use think\Loader;
use think\paginator\driver\Bootstrap;

class Goods extends Controller
{


    /**
     * [商品列表显示]
     * lilu
     */
    public function goods_index(Request $request)
    {
        //获取商品
        $goods = db("goods")->order("id asc")->select();
        $num = db("goods")->count();        //获取商品总数
        $all_idents = $goods;               //获取分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前端分页传值
        $listRow = 10;//每页10行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $goods = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Goods/goods_index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $goods->appends($_GET);
        $this->assign('listpage', $goods->render());
        return view("goods_index", ["goods" => $goods,"num" => $num]);

    }



    /**
     * [商品列表添加组]
     * GY
     */
    public function goods_add()
    {
        return view("goods_add");
    }



    /**
     * [商品列表组保存]
     * GY
     * 
     */
    public function goods_add_do(Request $request)
    {
        if ($request->isPost()) {                       //判断请求类型
            $goods_data = $request->param();
            $show_images = $request->file("goods_show_images");        //商品大图
            //统一规格图片
            $goods_images_one = $request->file("goods_images_one");        //商品1
            $goods_images_two = $request->file("goods_images_two");        //商品2
            $goods_images_three = $request->file("goods_images_three");        //商品3
            //特殊规格图片
            $imgs_one = $request->file("imgs_one");
            $imgs_two = $request->file("imgs_two");
            $imgs_three = $request->file("imgs_three");
            //处理商品规格图片
            if (!empty($imgs_one)) {
                foreach ($imgs_one as $k=>$v) {
                    $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $goods_data['image_one'][] = str_replace("\\", "/", $info->getSaveName());
                }
            }
            //处理商品规格图片
            if (!empty($imgs_two)) {
                foreach ($imgs_two as $k=>$v) {
                    $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $goods_data['image_two'][] = str_replace("\\", "/", $info->getSaveName());
                }
            }
            //处理商品规格图片
            if (!empty($imgs_three)) {
                foreach ($imgs_three as $k=>$v) {
                    $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $goods_data['image_three'][] = str_replace("\\", "/", $info->getSaveName());
                }
            }
            $list = [];
            unset($goods_data["aaa"]);
            if (!empty($show_images)) {
                foreach ($show_images as $k=>$v) {
                    $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $list[] = str_replace("\\", "/", $info->getSaveName());
                }
                $goods_data["goods_show_image"] =  $list[0];
                $goods_data["goods_show_images"] = implode(',', $list);
            }
            if ($goods_data["goods_standard"] == "0") {         //统一规格
                if ((!empty($show_images))) {
                    if (!empty($goods_images_one)) {
                        foreach ($goods_images_one as $k=>$v) {
                            $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                            $goods_data['goods_images_one'] = str_replace("\\", "/", $info->getSaveName());
                        }
                    }
                    if (!empty($goods_images_two)) {
                        foreach ($goods_images_two as $k=>$v) {
                            $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                            $goods_data['goods_images_two'] = str_replace("\\", "/", $info->getSaveName());
                        }
                    }
                    if (!empty($goods_images_three)) {
                        foreach ($goods_images_three as $k=>$v) {
                            $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                            $goods_data['goods_images_three'] = str_replace("\\", "/", $info->getSaveName());
                        }
                    }
                    $goods_data['brand']=$goods_data['produce'];
                    $goods_data['start_date']=strtotime($goods_data['start_date']);
                    $goods_data['end_date']=strtotime($goods_data['end_date']);
                    //随机生成商品编码（6位）
                    $goods_data['goods_number']=randomkeys();
                    $bool = db("goods")->insert($goods_data);
                    if($bool){
                        $this->success("添加成功", url("admin/Goods/goods_index"));
                    }else{
                        $this->error("添加失败");
                    }
                } else {
//                    $this->success("添加失败", url('admin/Goods/goods_add'));
                      $this->error("添加失败");
                }
            }
            if ($goods_data["goods_standard"] == "1") {          //特殊规格
                $goods_special = [];
                $goods_special["goods_name"] = $goods_data["goods_name"];
                $goods_special["produce"] = $goods_data["produce"];
                $goods_special["brand"] = $goods_data["produce"];
                $goods_special["start_date"] = strtotime($goods_data["start_date"]);
                $goods_special["end_date"] = strtotime($goods_data["end_date"]);
                $goods_special['goods_number']=randomkeys();                            //商品编号
                $goods_special["goods_standard"] = $goods_data["goods_standard"];    //商品规格
                $goods_special["goods_sign"] = $goods_data["goods_sign"];
                $goods_special["goods_share_describe"] = $goods_data["goods_share_describe"];
                $goods_special["goods_share_title"] = $goods_data["goods_share_title"];
                $goods_special["video_link"] = $goods_data["video_link"];             //视频链接
                $goods_special["goods_freight"] = $goods_data["goods_freight"];
                $goods_special["label"] = $goods_data['label'];                      //上下架   默认上架
                $goods_special["goods_setting"] = $goods_data['goods_setting'];     //上下架   默认上架

                $goods_special["goods_detail"] = $goods_data["goods_detail"];  //商品详情
                $goods_special["goods_show_images"] = $goods_data["goods_show_images"];
                $goods_special["goods_show_image"] = $goods_data["goods_show_image"];
                $goods_id = db('goods')->insertGetId($goods_special);       //添加商品数据,返回商品id
                $result = implode(",", $goods_data["lv1"]);         //商品规格title
                if (!empty($goods_data)) {
                    $attr=[];
                    $i=0;
                    foreach ($goods_data as $kn => $nl) {
                        if (substr($kn, 0, 3) == "sss") {      //判断是否是规格记录
                               $attr[$i]['stock']=$nl['stock'];            //库存
                               $attr[$i]['coding']=$nl['coding'];          //规格
                               $attr[$i]['cost']=$nl['cost'];              //成本价
                               $attr[$i]['line']=$nl['line'];              //划线价
                               $attr[$i]['total']=$nl['total'];            //积分
                               $attr[$i]['jilt']=$nl['jilt'];              //帮甩费用
                               $attr[$i]['status']=$nl['status'];          //上下架
                               $attr[$i]['goods_id']=$goods_id;
                               $attr[$i]['lv1']=$result;                    //规格title
                               $attr[$i]['name']=$nl['name'];                //规格名称
                               $attr[$i]['image_one']=$goods_data['image_one'][$i];                //规格图片
                               $attr[$i]['image_two']=$goods_data['image_two'][$i];                //规格图片
                               $attr[$i]['image_three']=$goods_data['image_three'][$i];                //规格图片
                               $i++;
                        }
                    }
                }
                foreach ($attr as $kz => $vw) {
                    $rest = db('special')->insertGetId($vw);
                }    
                if ($rest && (!empty($show_images))) {
                    $this->success("添加成功", url("admin/Goods/goods_index"));
                } else {
                    $this->success("添加失败", url('admin/Goods/add'));
                }
            }
        }
    }


    /**
     * [商品修改]
     * lilu
     * @parsm  goods_id
     */
    public function goods_edit(Request $request, $id)
    {
        $goods = db("goods")->where("id", $id)->select();
//        $scope = db("member_grade")->field("member_grade_name")->select();    //获取会员列表
        $goods_standard = db("special")->where("goods_id", $id)->select();      //获取该商品规格
//        $expenses = db("express")->field("id,name")->select();                //获取快递列表
        foreach ($goods as $key => $value) {
            if(!empty($goods)){
            $goods[$key]["goods_show_images"] = explode(',', $goods[$key]["goods_show_images"]);
            $goods[$key]["goods_attr"] =  $goods_standard; //商品规格记录
//            $goods[$key]["scope"] = explode(',', $goods[$key]["scope"]);                   //面向会员范围
//            $goods[$key]["unit"] = explode(',', $goods[$key]["element"]);                    //单位名称
        }
     }
        $restel = $goods[0]["goods_standard"]; //判断是否为通用或特殊
        if ($restel == 0) {                 //统一规格
            return view("goods_edit", ["goods" => $goods]);
        } else {
            return view("goods_edit", ["goods" => $goods ]);
        }
    }
    /**
     * [商品修改处理]
     * lilu
     * @parsm  id   商品id
     */
    public function goods_edit_do(Request $request, $id)
    {
        if ($request->isPost()) {                                  //判断请求类型
            $goods_data = $request->param();                         //获取表单数据
            if($goods_data['goods_standard']=='0'){                //判断商品规格
                $show_images = $request->file("goods_show_images");        //商品大图
                if (!empty($show_images)) {
                    foreach ($show_images as $k=>$v) {
                        $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                        $list[] = str_replace("\\", "/", $info->getSaveName());
                    }
                    $goods_data["goods_show_image"] =  $list[0];
                    $goods_data["goods_show_images"] = implode(',', $list);
                }
                //统一规格图片
                $goods_images_one = $request->file("goods_images_one");        //商品1
                $goods_images_two = $request->file("goods_images_two");        //商品2
                $goods_images_three = $request->file("goods_images_three");        //商品3
                if (!empty($goods_images_one)) {
                    foreach ($goods_images_one as $k=>$v) {
                        $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                        $goods_data['goods_images_one'] = str_replace("\\", "/", $info->getSaveName());
                    }
                }
                if (!empty($goods_images_two)) {
                    foreach ($goods_images_two as $k=>$v) {
                        $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                        $goods_data['goods_images_two'] = str_replace("\\", "/", $info->getSaveName());
                    }
                }
                if (!empty($goods_images_three)) {
                    foreach ($goods_images_three as $k=>$v) {
                        $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                        $goods_data['goods_images_three'] = str_replace("\\", "/", $info->getSaveName());
                    }
                }
                $goods_data['start_date']=strtotime($goods_data['start_date']);
                $goods_data['end_date']=strtotime($goods_data['end_date']);
                $re=db('goods')->where('id',$goods_data['id'])->update($goods_data);
                if($re){
                    $this->success("编辑成功", url("admin/Goods/goods_index"));
                }else{
                    $this->error("编辑失败");
                }
            }else{
                //特殊规格图片
                $imgs_one = $request->file("imgs_one");
                $imgs_two = $request->file("imgs_two");
                $imgs_three = $request->file("imgs_three");
                //处理商品规格图片
                if (!empty($imgs_one)) {
                    foreach ($imgs_one as $k=>$v) {
                        $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                        $list1['images_one'][] = str_replace("\\", "/", $info->getSaveName());
                    }
                }
                else{
                    $list1['images_one']=[];
                }
                //处理商品规格图片
                if (!empty($imgs_two)) {
                    foreach ($imgs_two as $k=>$v) {
                        $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                        $list2['images_two'][] = str_replace("\\", "/", $info->getSaveName());
                    }
                }else{
                    $list2['images_two']=[];
                }
                //处理商品规格图片
                if (!empty($imgs_three)) {
                    foreach ($imgs_three as $k=>$v) {
                        $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                        $list2['imgs_three'][] = str_replace("\\", "/", $info->getSaveName());
                    }
                }else{
                    $list2['imgs_three']=[];
                }
                $goods_special = [];                           //特殊商品基本信息
                $goods_special["goods_name"] = $goods_data["goods_name"];
                $goods_special["brand"] = $goods_data["brand"];
                $goods_special["start_date"] = strtotime($goods_data["start_date"]);
                $goods_special["end_date"] = strtotime($goods_data["end_date"]);
                $goods_special["goods_number"] = $goods_data["goods_number"];        //商品编号
                $goods_special["goods_standard"] = $goods_data["goods_standard"];    //商品规格
                $goods_special["goods_sign"] = $goods_data["goods_sign"];
                $goods_special["goods_share_describe"] = $goods_data["goods_share_describe"];
                $goods_special["goods_share_title"] = $goods_data["goods_share_title"];
                $goods_special["video_link"] = $goods_data["video_link"];             //视频链接
                $goods_special["goods_freight"] = $goods_data["goods_freight"];
                $goods_special["label"] = $goods_data['label'];                                //上下架
                $goods_special["goods_setting"] = $goods_data['goods_setting'];               //帮甩限制
                $goods_special["goods_detail"] = $goods_data["goods_detail"];
                $good_image=explode(',',$goods_data['goods_show_images']);
                $goods_special["goods_show_image"] = $good_image[0];
                //更新商品信息
                db('goods')->where('id',$goods_data['id'])->update($goods_special);
                if (!empty($goods_data)) {
                        $attr=[];
                        $i=0;
                        foreach ($goods_data as $kn => $nl) {
                            if (substr($kn, 0, 3) == "sss") {      //判断是否是规格记录
                                $attr[$i]['stock']=$nl['stock'];            //库存
                                $attr[$i]['coding']=$nl['coding'];          //规格
                                $attr[$i]['cost']=$nl['cost'];              //成本价
                                $attr[$i]['line']=$nl['line'];              //划线价
                                $attr[$i]['total']=$nl['total'];            //积分
                                $attr[$i]['jilt']=$nl['jilt'];              //帮甩费用
                                $attr[$i]['status']=$nl['status'];          //上下架
                                if(array_key_exists($i,$list1['images_one'])){
                                    $attr[$i]['image_one']=$list1['images_one'][$i];
                                }else{
                                    $attr[$i]['image_one']=$nl['image_one'];                //规格图片
                                }
                                if(array_key_exists($i,$list2['images_two'])){
                                    $attr[$i]['image_two']=$list2['images_two'][$i];
                                }else{
                                    $attr[$i]['image_two']=$nl['image_two'][$i];                //规格图片
                                }
                                if(array_key_exists($i,$list2['imgs_three'])){
                                    $attr[$i]['imgs_three']=$list2['imgs_three'][$i];
                                }else{
                                    $attr[$i]['imgs_three']=$nl['imgs_three'][$i];                //规格图片
                                }
                                $res=db('special')->where('id',$nl['id'])->update($attr[$i]);
                                $i++;
                            }
                        }
                    $this->success('编辑成功',url('admin/Goods/goods_index'));
                }else{
                    $this->error();
                }

            }
        }
    }


    /**
     * [商品列表组图片删除]
     * GY
     */
    public function images(Request $request)
    {
        if ($request->isPost()) {
            $tid = $request->param();
            $id = $tid["id"];
            $image = db("goods")->where("id", $tid['pid'])->field("goods_show_images")->find();
            if (!empty($image["goods_show_images"])) {
                $se = explode(",", $image["goods_show_images"]);
                foreach ($se as $key => $value) {
                    if ($value == $id) {
                        unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $value);
                    } else {
                        $new_image[] = $value;
                    }
                }
            }
            if (!empty($new_image)) {
                $new_imgs_url = implode(',', $new_image);
                $res = Db::name('goods')->where("id", $tid['pid'])->update(['goods_show_images' => $new_imgs_url]);
            } else {
                $res = Db::name('goods')->where("id", $tid['pid'])->update(['goods_show_images' => NULL,'goods_show_image' => NULL]);
            }
            if ($res) {
                return ajax_success('删除成功');
            } else {
                return ajax_success('删除失败');
            }
        }
    }



    /**
     * [商品列表组删除]
     * GY
     */
    public function del(Request $request)
    {
        $id = $request->only(["id"])["id"];
        $bool = db("goods")-> where("id", $id)->delete();
        $boole = db("special")->where("goods_id",$id)->delete();
        $res = db("commodity")->where("goods_id",$id)->find();

        if($res) {
            db("commodity")->where("goods_id", $id)->delete();
        }

        if ($bool || $boole) {
            $this->success("删除成功", url("admin/Goods/index"));
        } else {
            $this->success("删除失败", url('admin/Goods/add'));
        }
    }



    /**
     * [商品列表组更新]
     * GY
     * 
     */
    public function updata(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $goods_data = $request->param();  
            unset($goods_data["aaa"]);
            $show_images = $request->file("goods_show_images");

            if(!empty($goods_data["scope"])){
                $goods_data["scope"] = implode(',', $goods_data["scope"]);
            } else {
                $goods_data["scope"] = "";
            }
            $goods_data["templet_id"] = isset($goods_data["templet_id"])?implode(",",$goods_data["templet_id"]):null;
            $goods_data["templet_name"] = isset($goods_data["templet_name"])?implode(",",$goods_data["templet_name"]):null;
            $list = [];
            if (!empty($show_images)) {
                foreach ($show_images as $k => $v) {
                    $show = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $list[] = str_replace("\\", "/", $show->getSaveName());
                }               
                    $liste = implode(',', $list);
                    $image = db("goods")->where("id", $id)->field("goods_show_images")->find();
                if(!empty($image["goods_show_images"]))
                {
                    $exper = $image["goods_show_images"];
                    $montage = $exper . "," . $liste;
                    $goods_data["goods_show_images"] = $montage;
                } else {                   
                    $montage = $liste;
                    $goods_data["goods_show_image"] = $list[0];
                    $goods_data["goods_show_images"] = $montage;
                }
            } else {
                    $image = db("goods")->where("id", $id)->field("goods_show_images")->find();
                if(!empty($image["goods_show_images"])){
                    $goods_data["goods_show_images"] = $image["goods_show_images"];
                } else {
                    $goods_data["goods_show_images"] = null;
                    $goods_data["goods_show_image"] = null;
                }
            } 

            if($goods_data["goods_standard"] == 1){
                $special_id = db("special")->where("goods_id",$id)->field("id")->select();

                foreach($special_id as $pp => $qq){
                    $special[$pp] = $qq["id"];
                }

                foreach ($goods_data as $kn => $nl) {
                    if(substr($kn,strrpos($kn,"_")+1) == "num"){
                        $num1[substr($kn,0,strrpos($kn,"_"))]["num"] = implode(",",$goods_data[$kn]);
                        $num[substr($kn,0,strrpos($kn,"_"))]["num"] = $goods_data[$kn];
                    } 
                    if(substr($kn,strrpos($kn,"_")+1) == "unit"){
                        $unit1[substr($kn,0,strrpos($kn,"_"))]["unit"] = implode(",",$goods_data[$kn]);
                        $unit[substr($kn,0,strrpos($kn,"_"))]["unit"] = $goods_data[$kn]; 
                    } 
                    
                    if(is_array($nl)){
                        unset($goods_data[$kn]);                    
                    }
                }
           
             foreach($special as $tt => $yy){ 
                 if(isset($num1)){
                    if(array_key_exists($yy,$num1)){        
                    $bools[$tt] = db("special")->where("id",$yy)->update(["unit"=>$unit1[$yy]["unit"],"num"=>$num1[$yy]["num"],"element"=>unit_comment($num[$yy]["num"],$unit[$yy]["unit"])]);
                    } else {
                    $bools[$tt] = db("special")->where("id",$yy)->update(["unit"=>null,"num"=>null,"element"=>null]);
                    }
               } else {
                    $bools[$tt] = db("special")->where("id",$yy)->update(["unit"=>null,"num"=>null,"element"=>null]);
               }
            }

             foreach($bools as $xx => $cc){
                 if($cc = 1){
                     $rest = 1;
                 } else {
                    $rest = 0;
                 }
             }

             $bool = db("goods")->where("id", $id)->update($goods_data);
             if ($bool || $rest) {
                 $this->success("更新成功", url("admin/Goods/index"));
             } else {
                 $this->success("更新失败", url('admin/Goods/index'));
             }
             
        } else {
            if(empty($goods_data["num"][1]) && empty($goods_data["unit"][0])){ //存空
                
                $goods_data["num"] = array();
                $goods_data["unit"] = array();
            } else {
                $goods_data["element"] = unit_comment($goods_data["num"],$goods_data["unit"]);
                $goods_data["num"] = implode(",",$goods_data["num"]);
                $goods_data["unit"] = implode(",",$goods_data["unit"]);
            }
        }
            
            $bool = db("goods")->where("id", $id)->update($goods_data);
            if ($bool) {
                $this->success("更新成功", url("admin/Goods/index"));
            } else {
                $this->success("更新失败", url('admin/Goods/index'));
            }

        }

    }



    /**
     * [商品列表运费模板编辑]
     * 郭杨
     */
    public function goods_templet(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $templet = db("goods")->where("id",$id)->field("templet_id,templet_name")->find();
            if(!empty($templet)){
                $templet_id = explode(",",$templet["templet_id"]);
                $templet["templet_id"] = $templet_id;
                foreach($templet_id as $ke => $val){
                    $temp[$ke] = db("express")->where("id",$val)->field("name,id")->find();
                }
                $rest["templet_unit"] = explode(",",$templet["templet_name"]);
                $rest["templet_name"] = $temp;
                return ajax_success('传输成功', $rest);
            } else {
                return ajax_error("数据为空");
            }
        }
    }

    /**
     * [众筹商品列表运费模板编辑]
     * 郭杨
     */
    public function crowd_templet(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $templet = db("crowd_goods")->where("id",$id)->field("templet_id,templet_name")->find();
            if(!empty($templet)){
                $templet_id = explode(",",$templet["templet_id"]);
                $templet["templet_id"] = $templet_id;
                foreach($templet_id as $ke => $val){
                    $temp[$ke] = db("express")->where("id",$val)->field("name,id")->find();
                }
                $rest["templet_unit"] = explode(",",$templet["templet_name"]);
                $rest["templet_name"] = $temp;
                return ajax_success('传输成功', $rest);
            } else {
                return ajax_error("数据为空");
            }
        }
    }


    /**
     * [商品上下架状态]
     * lilu
     * label  0->下架   1->上架
     */
    public function goods_label_edit(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods")->where("id", $id)->update(["label" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/goods_index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/goods_index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods")->where("id", $id)->update(["label" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/goods_index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/goods_index"));
                }
            }
        }
    }




    /**
     * [商品列表组批量删除]
     * lilu
     * $parsm id   商品id
     */
    public function goods_del(Request $request,$id)
    {
        if ($request->isGet()) {
            $id = $request->only(["id"])["id"];    //商品id
            //获取商品信息
            $goods_info=db('goods')->where('id',$id)->find();
            if($goods_info['goods_standard']=='1'){    //特殊规格
                  //1.删除商品信息
                $re=db('goods')->delete($id);
                //2.删除规格信息
                $res=db('special')->where('goods_id',$id)->delete();
                if($re && $res){
                   $this->success('删除成功',url('admin/Goods/goods_index'));
                }else{
                   $this->error('删除失败');
                }
            }else{
                //1.删除商品信息
                $re=db('goods')->delete($id);
                if($re){
                    $this->success('删除成功',url('admin/Goods/goods_index'));
                }else{
                    $this->error('删除失败');
                }
            }
        }else{
            $id = $request->only(["id"])["id"];
            $num=count($id);
            $i=0;
            foreach ($id as $k=>$v){
                $goods_info=db('goods')->where('id',$v)->find();
                if($goods_info['goods_standard']=='1'){    //特殊规格
                    //1.删除商品信息
                    $re=db('goods')->delete($v);
                    //2.删除规格信息
                    $res=db('special')->where('goods_id',$v)->delete();
                    if($re && $res){
                        $i++;
                    }
                }else{
                    //1.删除商品信息
                    $re=db('goods')->delete($v);
                    if($re){
                        $i++;
                    }
                }
            }
            if($i==$num){
                return ajax_success('批量删除成功');
            }else{
                return ajax_error('批量删除失败');
            }
        }
    }


    /**
     * [商品列表规格图片删除]
     * 郭杨
     */
    public function photos(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            if (!empty($id)) {
                $photo = db("special")->where("id", $id)->update(["images" => null]);
            }
            if ($photo) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }


    /**
     * [商品列表规格值修改]
     * 郭杨
     */
    public function value(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $value = $request->only(["value"])["value"];
            $key = $request->only(["key"])["key"];
            $valuet = db("special")->where("id", $id)->update([$key => $value]);

            if (!empty($valuet)) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }


    /**
     * [商品列表规格开关]
     * 郭杨
     */
    public function switches(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $status = $request->only(["status"])["status"];

            if (!empty($id)) {
                $ture = db("special")->where("id", $id)->update(["status" => $status]);
            }
            if ($ture) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }


    /**
     * [商品列表规格图片添加]
     * 郭杨
     */
    public function addphoto(Request $request)
    {
        if ($request->isPost()) {
            $id = $request -> only(["id"])["id"];
            $imag = $request-> file("file") -> move(ROOT_PATH . 'public' . DS . 'uploads');
            $images = str_replace("\\", "/", $imag->getSaveName());

            if(!empty($id)){
                $bool = db("special")->where("id", $id)->update(["images" => $images]);
            }
             if ($bool) {
                 return ajax_success('添加图片成功!');
             } else {
                 return ajax_error('添加图片失败');
             }
        }
    }



    /**
     * [商品列表分销设置加载]
     * 郭杨
     */
    public function goods_promote($id)
    {
        if ($request->isPost()) {
            $id = $request -> only(["id"])["id"];
            $imag = $request-> file("file") -> move(ROOT_PATH . 'public' . DS . 'uploads');
            $images = str_replace("\\", "/", $imag->getSaveName());

            if(!empty($id)){
                $bool = db("special")->where("id", $id)->update(["images" => $images]);
            }
             if ($bool) {
                 return ajax_success('添加图片成功!');
             } else {
                 return ajax_error('添加图片失败');
             }
        }
    }


    /**
     * [商品列表搜索]
     * lilu
     * goods_number  商品编号
     * goods_name    商品名称
     */
    public function goods_search()
    {
        $goods_number = input('goods_number');
        if ((!empty($goods_number))) {     //获取检索的商品
            $goods = db("goods")
                    ->where("goods_number",$goods_number)
                    ->whereOr('goods_name',$goods_number)
                    ->order("id asc")
                    ->select();
        }else {                             //获取不到则获取所有商品
            $goods = db("goods")->order("id desc")->select();
        }
        $all_idents = $goods;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 10;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $goods = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Goods/index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $goods->appends($_GET);
        $this->assign('listpage', $goods->render());
        return view("goods_index", ["goods" => $goods]);
    }



    /**
     * [普通商品多规格列表单位编辑]
     * 郭杨
     */
    public function offer(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $standard = db("goods")->where("id",$id)->value("goods_standard");
            if($standard == 1){
                $goods_standard = db("special")->where("goods_id", $id)->select();
                $offer = db("special")->where("goods_id", $id)->field("coding,id")->select();

                foreach($offer as $pp => $qq){
                    $offers[$pp] = $qq["coding"];
                    $specail_id[$pp] = $qq["id"];
                }

                foreach ($goods_standard as $k => $v) {
                    $goods_standard[$k]["title"] = explode('_', $v["name"]);
                    $res = explode(',', $v["lv1"]);      
                    $unit["unit"][] = explode(',', $v["unit"]);        
                    $num["num"][] = explode(',', $v["num"]);        
                }

                foreach($offers as $kk => $zz){
                    $rest1["unit"][$kk] = $unit["unit"][$kk];
                    $rest2["num"][$kk] = $num["num"][$kk];
                    $unit1[$kk]["unit"] =  $rest1["unit"][$kk];
                    $unit1[$kk]["num"] =  $rest2["num"][$kk];
                    $unit1[$kk]["number"] =  $offers[$kk];
                    $unit1[$kk]["id"] =  $specail_id[$kk];
                    
                             
                }
                
                if(!empty($unit1)){
                    return ajax_success('传输成功', $unit1);
                } else {
                    return ajax_error("数据为空");
                }

            } else {
                return ajax_error("该商品为统一规格商品");
            }
        }
    }
    


    /**
     * [普通商品多规格列表单位id查找]
     * 郭杨
     */
    public function standard(Request $request)
    {
        if ($request->isPost()) {
            $coding = $request->only(["coding"])["coding"];
            $id = $request->only(["id"])["id"];
            $special = db("special")->where("goods_id",$id)->where("coding",$coding)->value("id");
            if(!empty($special)){
                return ajax_success('传输成功', $special);
            } else {
                return ajax_error("数据为空");
            } 
        }             
    }


    /**
     * [众筹商品显示]
     * 郭杨
     */    
    public function crowd_index(){
        $crowd_data = db("crowd_goods")->select();
        if(!empty($crowd_data)){
            foreach ($crowd_data as $key => $value) {
                $sum[$key] = db("crowd_special")->where("goods_id", $crowd_data[$key]['id'])->sum("price");//众筹金额
                $crowd_data[$key]["sum_price"] = $sum[$key];
            }
        }   

        $url = 'admin/Goods/crowd_index';
        $pag_number = 20;
        $crowd = paging_data($crowd_data,$url,$pag_number);     
        return view("crowd_index",["crowd"=>$crowd]);
    }



    /**
     * [众筹商品添加]
     * 郭杨
     */    
    public function crowd_add(Request $request){
        if($request->isPost()) {
            $goods_data = $request->param();
            $goods_text =  isset($goods_data["goods_text"]) ? $goods_data["goods_text"]:null;
            $team =  isset($goods_data["team"]) ? $goods_data["team"]:null;
            $text =  isset($goods_data["text"]) ? $goods_data["text"]:null;
            $result = isset($goods_data["lv1"]) ? $goods_data["lv1"]:null;
            $scope = isset($goods_data["scope"]) ? implode(",",$goods_data["scope"]):null;
            $goods_sign = isset($goods_data["goods_sign"]) ? $goods_data["goods_sign"]:null;
            $goods_data["templet_id"] = isset($goods_data["templet_id"])?implode(",",$goods_data["templet_id"]):null;
            $goods_data["templet_name"] = isset($goods_data["templet_name"])?implode(",",$goods_data["templet_name"]):null;
            $show_images = $request->file("goods_show_images");
            $number_days = intval($goods_data["number_days"]);
            $imgs = $request->file("imgs");
            $time = time();
            $end_time = strtotime(date('Y-m-d', strtotime ("+ $number_days day", $time)));
            $list = [];
            if (!empty($show_images)) {              
                foreach ($show_images as $k=>$v) {
                    $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $list[] = str_replace("\\", "/", $info->getSaveName());
                }            
                $goods_data["goods_show_image"] =  $list[0];
                $goods_data["goods_show_images"] = implode(',', $list);
                $goods_data["time"] = $time;
            }
            $goods = array(
                "project_name" => $goods_data["project_name"],
                "number_days" => $goods_data["number_days"],
                "goods_sign" => $goods_sign,
                "goods_describe" => $goods_data["goods_describe"],
                "pid" => $goods_data["pid"],
                "sort_number" => $goods_data["sort_number"],
                "time"=> $time,
                "end_time"=>$end_time,
                "company_name" => $goods_data["company_name"],
                "company_name1" => $goods_data["company_name"],
                "company_time" => $goods_data["company_time"],
                "goods_show_image" => $goods_data["goods_show_image"],
                "goods_show_images" => $goods_data["goods_show_images"],
                "goods_member" => $goods_data["goods_member"],
                "video_link" => $goods_data["video_link"],
                "goods_text" => $goods_text,
                "team" => $team,
                "text" => $text,
                "goods_delivery" => $goods_data["goods_delivery"],
                "goods_franking" => $goods_data["goods_franking"],
                "templet_id" => $goods_data["templet_id"],
                "templet_name" => $goods_data["templet_name"],
                "label" => $goods_data["label"],
                "status"=> $goods_data["status"],
                "scope"=> $scope
            );

            if(empty($result)){
                $this->error("请添加规格值", url('admin/Goods/crowd_add'));
            } else {
                $goods_id = db('crowd_goods')->insertGetId($goods);
                $standard = implode(",", $result);
                if (!empty($goods_data)) {
                    foreach ($goods_data as $kn => $nl) {
                        if (substr($kn, 0, 3) == "sss") 
                        {
                            $price[] = $nl["price"];
                            $stock[] = $nl["stock"];
                            $coding[] = $nl["coding"];
                            $story[] = $nl["story"];
                            $cost[] = $nl["cost"];
                            $offer[] = $nl["offer"];
                            $line[] = isset($nl["line"])?$nl["line"]:null;
                            $status[] = isset($nl["status"])? $nl["status"]:0;
                            $save[] = isset($nl["save"]) ? $nl["save"]:0; 
                        }
                        if(substr($kn,strrpos($kn,"_")+1) == "num")
                        {
                            $num1[substr($kn,0,strrpos($kn,"_"))]["num"] = implode(",",$goods_data[$kn]);
                            $num[substr($kn,0,strrpos($kn,"_"))]["num"] = $goods_data[$kn];
                        } 
                        if(substr($kn,strrpos($kn,"_")+1) == "unit")
                        {
                            $unit1[substr($kn,0,strrpos($kn,"_"))]["unit"] = implode(",",$goods_data[$kn]);
                            $unit[substr($kn,0,strrpos($kn,"_"))]["unit"] = $goods_data[$kn]; 
                        }                         
                    }
                }
                if (!empty($imgs)) {
                    foreach ($imgs as $k => $v) {
                        $shows = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                        $tab = str_replace("\\", "/", $shows->getSaveName());

                        if (is_array($goods_data)) {
                            foreach ($goods_data as $key => $value) {
                                if (substr($key, 0, 3) == "sss") {
                                    $str[] = substr($key, 3);
                                    $values[$k]["name"] = $str[$k];
                                    $values[$k]["price"] = $price[$k];
                                    $values[$k]["lv1"] = $standard;
                                    $values[$k]["stock"] = $stock[$k];
                                    $values[$k]["offer"] = $offer[$k];
                                    $values[$k]["coding"] = $coding[$k];
                                    if(isset($num1)){
                                        if(array_key_exists($coding[$k],$num1)){
                                            $values[$k]["num"] = $num1[$coding[$k]]["num"]; 
                                        } else {
                                            $values[$k]["num"] = null;
                                        }
                                    } else {
                                            $values[$k]["num"] = null;
                                    }
                                    if(isset($unit1)){
                                        if(array_key_exists($coding[$k],$unit1)){
                                            $values[$k]["unit"] = $unit1[$coding[$k]]["unit"];
                                            $values[$k]["element"] = unit_comment($num[$coding[$k]]["num"],$unit[$coding[$k]]["unit"]);
                                        } else {
                                            $values[$k]["unit"] = null;
                                            $values[$k]["element"] = null;
                                        }
                                    } else {
                                            $values[$k]["unit"] = null;
                                            $values[$k]["element"] = null;
                                    }
                                    $values[$k]["status"] = $status[$k];
                                    $values[$k]["story"] = $story[$k];
                                    $values[$k]["save"] = $save[$k];
                                    $values[$k]["cost"] = $cost[$k];
                                    $values[$k]["limit"] = $line[$k];                                    
                                    $values[$k]["images"] = $tab;
                                    $values[$k]["goods_id"] = $goods_id;                                   
                                }
                            }
                        }
                    }
                }
            }
            foreach ($values as $kz => $vw) {
                $rest = db('crowd_special')->insertGetId($vw);
            }
            if ($rest || $goods_id) {
                $this->success("添加成功", url("admin/Goods/crowd_index"));
            } else {
                $this->error("添加失败", url('admin/Goods/crowd_index'));
            }
            
   
        }
        $scope = db("member_grade")->field("member_grade_name")->select();
        $expenses = db("express")->field("id,name")->select();
        $goods_list = getSelectList("wares");      
        return view("crowd_add",["goods_list"=>$goods_list,"expenses"=>$expenses,"scope"=>$scope]);
    }


    /**
     * [众筹商品编辑]
     * 郭杨
     */    
    public function crowd_edit($id){
        $goods = db("crowd_goods") -> where("id",$id) -> select(); 
        $goods_standard = db("crowd_special")->where("goods_id", $id)->select();
        $goods_list = getSelectList("wares");
        $expenses = db("express")->field("id,name")->select();
        $scope = db("member_grade")->field("member_grade_name")->select();


        foreach ($goods as $key => $value) {
            if(!empty($goods[$key]["goods_show_images"])){
            $goods[$key]["goods_show_images"] = explode(',', $goods[$key]["goods_show_images"]);
            $goods[$key]["scope"] = explode(',', $goods[$key]["scope"]);
        }
     }

     foreach ($goods_standard as $k => $v) {
            $goods_standard[$k]["title"] = explode('_', $v["name"]);
            $res = explode(',', $v["lv1"]);         
        }
    
        
        return view("crowd_edit", ["goods" => $goods, "goods_list" => $goods_list, "res" => $res, "goods_standard" => $goods_standard,"expenses"=>$expenses,"scope" => $scope]);
    }


    /**
     * [众筹商品列表组更新]
     * GY
     * 
     */
    public function crowd_update(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $time = time();
            $goods_data = $request->param();
            unset($goods_data["aaa"]);
            $show_images = $request->file("goods_show_images");
            $number_days = intval($goods_data["number_days"]);
            $end_time = strtotime(date('Y-m-d', strtotime ("+ $number_days day", $time)));           
            $list = [];
            if (!empty($show_images)) {
                foreach ($show_images as $k => $v) {
                    $show = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $list[] = str_replace("\\", "/", $show->getSaveName());
                }               
                    $liste = implode(',', $list);
                    $image = db("crowd_goods")->where("id", $id)->field("goods_show_images")->find();
                if(!empty($image["goods_show_images"]))
                {
                    $exper = $image["goods_show_images"];
                    $montage = $exper . "," . $liste;
                    $goods_data["goods_show_images"] = $montage;
                } else {                   
                    $montage = $liste;
                    $goods_data["goods_show_image"] = $list[0];
                    $goods_data["goods_show_images"] = $montage;
                }
            } else {
                    $image = db("crowd_goods")->where("id", $id)->field("goods_show_images")->find();
                if(!empty($image["goods_show_images"])){
                    $goods_data["goods_show_images"] = $image["goods_show_images"];
                } else {
                    $goods_data["goods_show_images"] = null;
                    $goods_data["goods_show_image"] = null;
                }
            } 
            $goods_data["end_time"] = $end_time;
            $special_id = db("crowd_special")->where("goods_id",$id)->field("id")->select();
            foreach($special_id as $pp => $qq){
                $special[$pp] = $qq["id"];
            }
            foreach ($goods_data as $kn => $nl) {
                if(substr($kn,strrpos($kn,"_")+1) == "num"){
                    $num1[substr($kn,0,strrpos($kn,"_"))]["num"] = implode(",",$goods_data[$kn]);
                    $num[substr($kn,0,strrpos($kn,"_"))]["num"] = $goods_data[$kn];
                } 
                if(substr($kn,strrpos($kn,"_")+1) == "unit"){
                    $unit1[substr($kn,0,strrpos($kn,"_"))]["unit"] = implode(",",$goods_data[$kn]);
                    $unit[substr($kn,0,strrpos($kn,"_"))]["unit"] = $goods_data[$kn]; 
                }    
                if(is_array($nl)){
                    unset($goods_data[$kn]);                    
                }
            }
            
            foreach($special as $tt => $yy){ 
                 if(isset($num1)){
                    if(array_key_exists($yy,$num1)){        
                    $bools[$tt] = db("crowd_special")->where("id",$yy)->update(["unit"=>$unit1[$yy]["unit"],"num"=>$num1[$yy]["num"],"element"=>unit_comment($num[$yy]["num"],$unit[$yy]["unit"])]);
                    } else {
                    $bools[$tt] = db("crowd_special")->where("id",$yy)->update(["unit"=>null,"num"=>null,"element"=>null]);
                    }
               } else {
                    $bools[$tt] = db("crowd_special")->where("id",$yy)->update(["unit"=>null,"num"=>null,"element"=>null]);
               }
            }

            foreach($bools as $xx => $cc){
                if($cc = 1){
                     $rest = 1;
                } else {
                    $rest = 0;
                }
            }
             $bool = db("crowd_goods")->where("id", $id)->update($goods_data);
             if ($bool || $rest) {
                 $this->success("更新成功", url("admin/Goods/crowd_index"));
             } else {
                 $this->success("更新失败", url('admin/Goods/crowd_index'));
             }                         
        }
    }



    /**
     * [众筹商品列表组删除]
     * GY
     */
    public function crowd_delete($id)
    {
        $bool = db("crowd_goods")-> where("id", $id)->delete();
        $boole = db("crowd_special")->where("goods_id",$id)->delete();

        if ($bool || $boole) {
            $this->success("删除成功", url("admin/Goods/crowd_index"));
        } else {
            $this->success("删除失败", url('admin/Goods/crowd_index'));
        }
    }

    /**
     * [众筹商品图片删除]
     * GY
     */
    public function crowd_images(Request $request)
    {
        if ($request->isPost()) {
            $tid = $request->param();
            $id = $tid["id"];
            $image = db("crowd_goods")->where("id", $tid['pid'])->field("goods_show_images")->find();
            if (!empty($image["goods_show_images"])) {
                $se = explode(",", $image["goods_show_images"]);
                foreach ($se as $key => $value) {
                    if ($value == $id) {
                        unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $value);
                    } else {
                        $new_image[] = $value;
                    }
                }
            }
            if (!empty($new_image)) {
                $new_imgs_url = implode(',', $new_image);
                $res = Db::name('crowd_goods')->where("id", $tid['pid'])->update(['goods_show_images' => $new_imgs_url]);
            } else {
                $res = Db::name('crowd_goods')->where("id", $tid['pid'])->update(['goods_show_images' => NULL,'goods_show_image' => NULL]);
            }
            if ($res) {
                return ajax_success('删除成功');
            } else {
                return ajax_success('删除失败');
            }
        }
    }



    /**
     * [众筹商品多规格列表单位编辑]
     * 郭杨
     */
    public function crowd_offer(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $goods_standard = db("crowd_special")->where("goods_id", $id)->select();
            $offer = db("crowd_special")->where("goods_id", $id)->field("coding,id")->select();

            foreach($offer as $pp => $qq){
                $offers[$pp] = $qq["coding"];
                $specail_id[$pp] = $qq["id"];
            }

            foreach ($goods_standard as $k => $v) {
                $goods_standard[$k]["title"] = explode('_', $v["name"]);
                $res = explode(',', $v["lv1"]);      
                $unit["unit"][] = explode(',', $v["unit"]);        
                $num["num"][] = explode(',', $v["num"]);        
            }

            foreach($offers as $kk => $zz){
                $rest1["unit"][$kk] = $unit["unit"][$kk];
                $rest2["num"][$kk] = $num["num"][$kk];
                $unit1[$kk]["unit"] =  $rest1["unit"][$kk];
                $unit1[$kk]["num"] =  $rest2["num"][$kk];
                $unit1[$kk]["number"] =  $offers[$kk];
                $unit1[$kk]["id"] =  $specail_id[$kk];                           
            }  

            if(!empty($unit1)){
                return ajax_success('传输成功', $unit1);
            } else {
                return ajax_error("数据为空");
            }
        }
    }


    /**
     * [增值商品多规格列表单位id查找]
     * 郭杨
     */
    public function crowd_standard(Request $request)
    {
        if ($request->isPost()) {
            $coding = $request->only(["coding"])["coding"];
            $id = $request->only(["id"])["id"];
            $special = db("crowd_special")->where("goods_id",$id)->where("coding",$coding)->value("id");
            if(!empty($special)){
                return ajax_success('传输成功', $special);
            } else {
                return ajax_error("数据为空");
            } 
        }             
    }

    /**
     * [商品列表组首页轮播推荐]
     * 郭杨
     */
    public function crowd_status(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("crowd_goods")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/crowd_index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/crowd_index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("crowd_goods")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/crowd_index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/_index"));
                }
            }
        }
    }


    /**
     * [众筹商品列表组是否上架]
     * GY
     */
    public function crowd_ground(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("crowd_goods")->where("id", $id)->update(["label" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/crowd_index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/crowd_index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("crowd_goods")->where("id", $id)->update(["label" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/crowd_index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/crowd_index"));
                }
            }
        }
    }


    /**
     * [众筹商品列表组批量删除]
     * GY
     */
    public function crowd_dels(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            if (is_array($id)) {
                $where = 'id in(' . implode(',', $id) . ')';
            } else {
                $where = 'id=' . $id;
            }
            halt($where);
            $list = Db::name('crowd_goods')->where($where)->delete();
            if (empty($list)) {
                return ajax_success('成功删除!', ['status' => 1]);
            } else {
                return ajax_error('删除失败', ['status' => 0]);
            }
        }
    }


     /**
     * [众筹商品列表规格图片删除]
     * 郭杨
     */
    public function crowd_photos(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            if (!empty($id)) {
                $photo = db("crowd_special")->where("id", $id)->update(["images" => null]);
            }
            if ($photo) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }

    /**
     * [众筹商品列表规格值修改]
     * 郭杨
     */
    public function crowd_value(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $value = $request->only(["value"])["value"];
            $key = $request->only(["key"])["key"];
            $valuet = db("crowd_special")->where("id", $id)->update([$key => $value]);

            if (!empty($valuet)) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }

    /**
     * [众筹商品列表规格开关]
     * 郭杨
     */
    public function crowd_switches(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $status = $request->only(["status"])["status"];

            if (!empty($id)) {
                $ture = db("crowd_special")->where("id", $id)->update(["status" => $status]);
            }
            if ($ture) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }


    /**
     * [众筹商品列表规格图片添加]
     * 郭杨
     */
    public function crowd_addphoto(Request $request)
    {
        if ($request->isPost()) {
            $id = $request -> only(["id"])["id"];
            $imag = $request-> file("file") -> move(ROOT_PATH . 'public' . DS . 'uploads');
            $images = str_replace("\\", "/", $imag->getSaveName());

            if(!empty($id)){
                $bool = db("crowd_special")->where("id", $id)->update(["images" => $images]);
            }
             if ($bool) {
                 return ajax_success('添加图片成功!');
             } else {
                 return ajax_error('添加图片失败');
             }
        }
    }


    /**
     * [商品列表搜索]
     * 郭杨
     */
    public function crowd_search()
    {
        $goods_number = input('project_name');
        if(!empty($goods_number)){
               $crowd_data = db("crowd_goods")
                    ->where("project_name",$goods_number)
                    ->select();
            } else {
                $crowd_data = db("crowd_goods")->select();
            }
      
        if(!empty($crowd_data)){
            foreach ($crowd_data as $key => $value) {
                $sum[$key] = db("crowd_special")->where("goods_id", $crowd_data[$key]['id'])->sum("price");//众筹金额
                $crowd_data[$key]["sum_price"] = $sum[$key];
            }
        }   

        $url = 'admin/Goods/crowd_index';
        $pag_number = 20;
        $crowd = paging_data($crowd_data,$url,$pag_number);     
        return view("crowd_index",["crowd"=>$crowd]);

    }

    /**
     * [专属定制商品显示]
     * 郭杨
     */    
    public function exclusive_index(){     
        return view("exclusive_index");
    }



    /**
     * [专属定制商品添加]
     * 郭杨
     */    
    public function exclusive_add(){     
        return view("exclusive_add");
    }


    /**
     * [专属定制商品编辑]
     * 郭杨
     */    
    public function exclusive_edit(){     
        return view("exclusive_edit");
    }
   
}