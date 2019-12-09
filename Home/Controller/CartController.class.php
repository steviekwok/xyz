<?php
/**
 * Created by PhpStorm.
 * User: gerrard
 * Date: 2017/10/9
 * Time: 21:49
 */

namespace Home\Controller;
use Think\Controller;

class CartController extends Controller{
    public function cart(){
        if(che()){
            $user_id = M('user')->where("username='%s'",cookie('username'))->find()['user_id'];
            $cart = M('cart')->field('cart.goods_id,cart.market_price,cart.shop_price,cart.goods_number as num,cart.is_select,goods.goods_name,goods.thumb_img,goods.goods_weight')->
            join('goods ON cart.goods_id=goods.goods_id')->where('cart.user_id='.$user_id)->select();
            if($cart) {
                $this->assign('cart_item', $cart);
                $this->assign('cart_qty', count($cart));
                $sum=$m_sum=0;
                foreach ($cart as $c) {
                    if($c['is_select'] == '1') {
                        $sum += $c['num'] * $c['shop_price'];
                        $m_sum += $c['num'] * $c['market_price'];//商场总价格
                    }
                }
                $this->assign('total', $sum);
                $this->assign('save',$m_sum-$sum);
                //$this->assign('user',1);//区分：未登录的cart数组goods_id是key,登录后goods_id是值
            }
        }else {
            $tool = \Home\Tool\AddTool::getIns();
            $cart=array();
            $sum=$m_sum=0;
            foreach($tool->item as $k=>$v){
                $v['goods_id'] = $k;
                $cart[] = $v;
                if($v['is_select'] == '1'){
                    $sum += $v['num'] * $v['shop_price'];
                    $m_sum += $v['num'] * $v['market_price'];//商场总价格
                }
            }
            $this->assign('cart_item', $cart);
            $this->assign('cart_qty', $tool->calcType());
            $this->assign('total',$sum);
            $this->assign('save',$m_sum-$sum);
        }

        $this->assign('cattree',D('Admin/Cat')->gettree());
        $cat_in_nav = M('cat')->field('cat_id,cat_name')->where('show_in_nav=1')->select();
        $this->assign('cat_in_nav',$cat_in_nav);
        $this->display();
    }

    public function navAddtocart(){
        $goods_id = I('post.goods_id');
        $num = I('post.quantity');
        if(!intval($goods_id) || !intval($num)){
            die(json_encode(['error'=>1, 'msg'=>'hackout']));
        }
        $goods = M('goods')->field('goods_id,goods_name,market_price,shop_price,thumb_img,goods_weight')->find($goods_id);
        if($goods) {
            if (che()) {
                $user_id = M('user')->where("username='%s'", cookie('username'))->find()['user_id'];
                if ($user_id && preg_match("/^[a-z0-9]*$/", cookie('PHPSESSID'))) {
                    if ($temp = M('cart')->where("goods_id='" . $goods['goods_id'] . "' and user_id='{$user_id}'")->find()) {
                        $num = $temp['goods_number'] + $num > 200 ? 200 : $temp['goods_number'] + $num;//购买数量大于200改成200
                        $cart = array('goods_number'=> $num);
                        M('cart')->where("goods_id='{$goods['goods_id']}' and user_id='{$user_id}'")->save($cart);
                    } else {
                        $cart = M('goods')->field('goods_id,goods_sn,market_price,shop_price')->where('goods_id=' . $goods['goods_id'])->find();
                        $cart['goods_number'] = $num;
                        $session_id = cookie('PHPSESSID');
                        $cart['session_id'] = $session_id;
                        $cart['user_id'] = $user_id;
                        M('cart')->add($cart);
                    }
                } else {
                    die(json_encode(['error'=>1, 'msg'=>'user non-existen']));
                }
            } else {
                $tool = \Home\Tool\AddTool::getIns();
                if($tool->item[$goods['goods_id']]){
                    if($tool->item[$goods['goods_id']]['num'] + $num > 200){
                        $tool->item[$goods['goods_id']]['num'] = 200;
                    }else {
                        $tool->item[$goods['goods_id']]['num'] += $num;
                    }
                }else {
                    $tool->add($goods['goods_id'], $goods['goods_name'], $goods['shop_price'], $goods['market_price'], $goods['thumb_img'], $goods['goods_weight']);
                    $tool->item[$goods['goods_id']]['num'] = $num;
                }
            }
        }else {
            die(json_encode(['error'=>1, 'msg'=>'not found product']));
        }
        die(json_encode(['error'=>0, 'goods_name'=>$goods['goods_name'], 'thumb_img'=>$goods['thumb_img']],JSON_UNESCAPED_UNICODE));
    }

    function del(){
        if($goods_id = I('post.goods_id')) {
            if(che()) {
                if (gettype($goods_id) == 'string') {
                    $user_id = M('user')->where("username='%s'",cookie('username'))->find()['user_id'];
                    $del = M('cart')->where("user_id=$user_id and goods_id=$goods_id")->delete();
                } elseif (gettype($goods_id) == 'array') {
                    $user_id = M('user')->where("username='%s'",cookie('username'))->find()['user_id'];
                    //if(count($goods_id)==1){
                    //    $del = M('cart')->where("user_id=$user_id and goods_id=$goods_id[0]")->delete();
                    //}else{
                        $map['goods_id'] = array('in',join(',',$goods_id));
                        $map['user_id'] = $user_id;
                        $del = M('cart')->where($map)->delete();
                    //}
                }
                if($del){
                    $count = M('cart')->where("user_id=$user_id")->count();
                    if(!$count){
                        make_json_response('refurbish');//如果购物车没商品，刷新
                    }
                    $cart = M('cart')->field('goods_number as num,shop_price,market_price,is_select')->where('user_id='.$user_id.' and is_select="1"')->select();
                    if($cart) {
                        $sum = $m_sum = 0;
                        foreach ($cart as $c) {
                            if ($c['is_select'] == '1') {
                                $sum += $c['num'] * $c['shop_price'];
                                $m_sum += $c['num'] * $c['market_price'];//商场总价格
                            }
                        }
                        make_json_response(array('total' => $sum, 'save' => $m_sum - $sum));
                    }elseif($cart!==false)
                        make_json_response(array('total' => '0', 'save' => '0'));
                    else{
                        make_json_response('',1,'delerror');
                    }
                }else{
                    make_json_response('',1,'delerror');
                }
            }else {
                $tool = \Home\Tool\AddTool::getIns();
                if (gettype($goods_id) == 'string') {
                    $tool->del($goods_id);
                } elseif (gettype($goods_id) == 'array') {
                    foreach ($goods_id as $g) {
                        $tool->del($g);
                    }
                }
                $sum=$m_sum=0;
                foreach($tool->item as $v){
                    if($v['is_select'] == '1'){
                        $sum += $v['num'] * $v['shop_price'];
                        $m_sum += $v['num'] * $v['market_price'];//商场总价格
                    }
                }
                make_json_response(array('total'=>$sum,'save'=>$m_sum-$sum));
            }
        }
        else{
            make_json_response('',1,'404 not found!');
        }
    }
    function navDel(){
        if($goods_id = I('goods_id')) {
            $rec = true;
            if (che()) {
                $user_id = M('user')->where("username='%s'", cookie('username'))->find()['user_id'];
                $rec = M('cart')->where("user_id=$user_id and goods_id=$goods_id")->delete();
            } else {
                $tool = \Home\Tool\AddTool::getIns();
                $tool->del($goods_id);
            }
            if($rec){
                die('done');
            }
        }
    }

    function num(){
        if(is_numeric(I('post.goods_id')) && is_numeric(I('post.count')) && I('post.count')>0){
            $goods_id = I('post.goods_id');
            $count = I('post.count');
            if(che()){
                $user_id = M('user')->where("username='%s'",cookie('username'))->find()['user_id'];
                M('cart')->where("user_id=$user_id and goods_id=$goods_id")->save(['goods_number'=>$count,'is_select'=>'1']);
                $cart = M('cart')->field('goods_number as num,shop_price,market_price,is_select')->where('user_id='.$user_id.' and is_select="1"')->select();
                if($cart) {
                    $sum = $m_sum = 0;
                    foreach ($cart as $c) {
                        if ($c['is_select'] == '1') {
                            $sum += $c['num'] * $c['shop_price'];
                            $m_sum += $c['num'] * $c['market_price'];//商场总价格
                        }
                    }
                    make_json_response(array('total' => $sum, 'save' => $m_sum - $sum));
                } else {
                    make_json_response('', 1, 'cart-numerror');
                }
            }else {
                $tool = \Home\Tool\AddTool::getIns();
                $tool->item[$goods_id]['num'] = $count;
                $tool->item[$goods_id]['is_select'] = '1';
                $sum=$m_sum=0;
                foreach($tool->item as $v){
                    if($v['is_select'] == '1'){
                        $sum += $v['num'] * $v['shop_price'];
                        $m_sum += $v['num'] * $v['market_price'];//商场总价格
                    }
                }
                make_json_response(array('total'=>$sum,'save'=>$m_sum-$sum));
            }
        }else{
            make_json_response('',1,'cart-num error');
        }
    }

    function check(){
        if($goods_id = I('post.goods_id')) {
            if(che()) {
                if (gettype($goods_id) == 'string') {
                    $user_id = M('user')->where("username='%s'",cookie('username'))->find()['user_id'];
                    $save = M('cart')->where("user_id=$user_id and goods_id=$goods_id")->save(['is_select'=>I('post.select')]);
                } elseif (gettype($goods_id) == 'array') {
                    $user_id = M('user')->where("username='%s'",cookie('username'))->find()['user_id'];
                    $map['goods_id'] = array('in',join(',',$goods_id));
                    $map['user_id'] = $user_id;
                    $save = M('cart')->where($map)->save(['is_select'=>I('post.select')]);
                }
                if($save){
                    $cart = M('cart')->field('goods_number as num,shop_price,market_price,is_select')->where('user_id='.$user_id.' and is_select="1"')->select();
                    if($cart) {
                        $sum = $m_sum = 0;
                        foreach ($cart as $c) {
                            if ($c['is_select'] == '1') {
                                $sum += $c['num'] * $c['shop_price'];
                                $m_sum += $c['num'] * $c['market_price'];//商场总价格
                            }
                        }
                        make_json_response(array('total' => $sum, 'save' => $m_sum - $sum));
                    }elseif($cart!==false)
                        make_json_response(array('total' => '0', 'save' => '0'));
                    else{
                        make_json_response('',1,'checkerror');
                    }
                }else{
                    make_json_response('',1,'checkerror');
                }
            }else {
                $tool = \Home\Tool\AddTool::getIns();
                if (gettype($goods_id) == 'string') {
                    $tool->item[$goods_id]['is_select'] = I('post.select');
                } elseif (gettype($goods_id) == 'array') {
                    foreach ($goods_id as $g) {
                        $tool->item[$g]['is_select'] = I('post.select');
                    }
                }
                $sum=$m_sum=0;
                foreach($tool->item as $v){
                    if($v['is_select'] == '1'){
                        $sum += $v['num'] * $v['shop_price'];
                        $m_sum += $v['num'] * $v['market_price'];//商场总价格
                    }
                }
                make_json_response(array('total'=>$sum,'save'=>$m_sum-$sum));
            }
        }
        else{
            make_json_response('',1,'404 not found!');
        }
    }

    function cart_nav(){
        if(che()){
            $user_id = M('user')->where("username='%s'",cookie('username'))->find()['user_id'];
            $cart = M('cart')->field('cart.goods_id,cart.shop_price,cart.goods_number as num,goods.goods_name,goods.thumb_img')->
            join('goods ON cart.goods_id=goods.goods_id')->where('cart.user_id='.$user_id)->select();
            $total = $amount = 0;
            foreach ($cart as $c) {
                $total += $c['num'] * $c['shop_price'];
                $amount +=  $c['num'];
            }
            $login = 1;
        }else {
            $tool = \Home\Tool\AddTool::getIns();
            $cart = array();
            $total = $amount = 0;
            foreach ($tool->items() as $k => $v) {
                $total += $v['num'] * $v['shop_price'];
                $amount +=  $v['num'];
                $v['goods_id'] = $k;
                $cart[] = $v;
            }
            $amount = $tool->calcType();
            $login = 0;
        }
        $res = [$cart, 'amount' => $amount, 'total' => $total, 'login' => $login];
        die(json_encode($res, JSON_UNESCAPED_UNICODE));
    }
}