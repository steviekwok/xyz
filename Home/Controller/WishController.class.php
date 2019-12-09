<?php
/**
 * Created by PhpStorm.
 * User: gerrard
 * Date: 2018/5/25
 * Time: 14:16
 */
namespace Home\Controller;
use Think\Controller;

class WishController extends Controller{
    public function index(){
        if (che()) {
            $user_id = M('user')->where("username='%s'", cookie('username'))->find()['user_id'];
            if (!($user_id && preg_match("/^[a-z0-9]*$/", cookie('PHPSESSID')))) {
                http_response_code(404);
            }
            $goods = M('wishlist')->field('goods.goods_id,goods_name,shop_price,goods_img')->where(['user_id' => $user_id])->
            join('goods ON wishlist.goods_id = goods.goods_id')->select();
            $this->assign('goods', $goods);
            $this->assign('count', count($goods));
            $this->assign('user_id',$user_id);
        }
        $cat_in_nav = M('cat')->field('cat_id,cat_name')->where('show_in_nav=1')->select();
        $this->assign('cat_in_nav', $cat_in_nav);
        $his = array_reverse(session('history'), true);
        $this->assign('history', $his);
        $sale = M('goods')->field('goods_id,goods_name,thumb_img,shop_price,goods_number')->order('goods_number desc')->limit(10)->select();
        $this->assign('sale', $sale);
        $this->assign('cattree', D('Admin/Cat')->gettree());
        $this->display();
    }

    public function navAddtowish(){
        $goods_id = I('post.goods_id');
        if(!intval($goods_id)){
            die(json_encode(['error'=>1, 'msg'=>'hackout']));
        }
        $goods = M('goods')->field('goods_name,thumb_img')->find($goods_id);
        if($goods) {
            if (che()) {
                $user_id = M('user')->where("username='%s'", cookie('username'))->find()['user_id'];
                if ($user_id && preg_match("/^[a-z0-9]*$/", cookie('PHPSESSID'))) {
                    if(M('wishlist')->where(['user_id'=>$user_id, 'goods_id'=>$goods_id])->find()){
                        die(json_encode(['error'=>0, 'login' => 1, 'exist'=>1, 'goods_name' => $goods['goods_name'], 'thumb_img' => $goods['thumb_img']], JSON_UNESCAPED_UNICODE));
                    }else {
                        M('wishlist')->add(['goods_id' => $goods_id, 'user_id' => $user_id]);
                        die(json_encode(['error' => 0, 'login' => 1, 'goods_name' => $goods['goods_name'], 'thumb_img' => $goods['thumb_img']], JSON_UNESCAPED_UNICODE));
                    }
                }else{
                    die(json_encode(['error'=>1, 'msg'=>'user non-existen']));
                }
            } else {
                die(json_encode(['error' => 0, 'login' => 0, 'msg' => 'no login']));
            }
        }else {
            die(json_encode(['error'=>1, 'msg'=>'not found product']));
        }
    }

    public function del(){
        $goods_id = I('post.goods_id');
        if(!intval($goods_id)){
            die(json_encode(['error'=>1, 'msg'=>'hackout']));
        }
        $goods = M('goods')->field('goods_name,thumb_img')->find($goods_id);
        if($goods) {
            if (che()) {
                $user_id = M('user')->where("username='%s'", cookie('username'))->find()['user_id'];
                if ($user_id && preg_match("/^[a-z0-9]*$/", cookie('PHPSESSID'))) {
                    if(M('wishlist')->where(['user_id'=>$user_id, 'goods_id'=>$goods_id])->find()){
                        M('wishlist')->where(['user_id'=>$user_id, 'goods_id'=>$goods_id])->delete();
                        die(json_encode(['error'=>0, 'login' => 1]));
                    }else {
                        die(json_encode(['error'=>1, 'msg'=>'error']));
                    }
                }else{
                    die(json_encode(['error'=>1, 'msg'=>'user non-existen']));
                }
            } else {
                die(json_encode(['error' => 0, 'login' => 0, 'msg' => 'no login']));
            }
        }else {
            die(json_encode(['error'=>1, 'msg'=>'not found product']));
        }
    }
}