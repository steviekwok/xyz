<?php
namespace Home\Controller;
use Think\Controller;

class OrderController extends Controller{
    /** 结算函数
     * @param $rid 订单编号
     */
    public function checkout($rid){
        //订单处理页超过10小时，再刷新会失效，防坏人
        if(time() - 36000 > substr($rid,0,10) || !che()) {
            $this->assign('error',true);
            $this->display();
            die;
        }
        $goods_id = !empty($_POST['checkItem']) ? join(',', $_POST['checkItem']) : 0;
        //有goods_id是购物车页提交过来的，没有goods_id是弹出购物车提交过来的（全部商品结算）
        if($goods_id){
            $user_id = M('user')->where("username='%s'", cookie('username'))->find()['user_id'];
            $map['cart.user_id'] = $user_id;
            $map['cart.goods_id'] = array('in', $goods_id);

            $cart = M('cart')->field('cart.goods_id,cart.shop_price,cart.goods_number as num,goods.goods_name,goods.thumb_img,goods.goods_weight')->
            join('goods ON cart.goods_id=goods.goods_id')->where($map)->select();
            if(!count($cart)){
                $this->assign('error',true);
                $this->display();
                die;
            }
            $this->assign('goods_id',$goods_id);//用于提交订单后删除购物车表 对应 的sku
        }else{
            $user_id = M('user')->where("username='%s'", cookie('username'))->find()['user_id'];
            $map['cart.user_id'] = $user_id;

            $cart = M('cart')->field('cart.goods_id,cart.market_price,cart.shop_price,cart.goods_number as num,goods.goods_name,goods.thumb_img,goods.goods_weight')->
            join('goods ON cart.goods_id=goods.goods_id')->where($map)->select();
            if(!count($cart)){
                $this->assign('error',true);
                $this->display();
                die;
            }
            $goods_id = $cart[0]['goods_id'];
            for($i = 1;$i < count($cart);$i++) {
                $goods_id .= ','.$cart[$i]['goods_id'];
            }
            $this->assign('goods_id',$goods_id);//用于提交订单后删除购物车表 对应 的sku
        }
        $this->assign('cart', $cart);
        $total = $total_qty  = 0;
        $each_qty = '';
        foreach ($cart as $c) {
            $total += $c['num'] * $c['shop_price'];
            $total_qty += $c['num'];
            $each_qty .= ','. $c['num'];
        }
        $each_qty = substr($each_qty,1);
        $this->assign('each_qty', $each_qty);
        $this->assign('total', $total);
        $this->assign('total_qty', $total_qty);
        $this->assign('user_id',$user_id);
        $this->assign('rid', $rid);
        $cat_in_nav = M('cat')->field('cat_id,cat_name')->where('show_in_nav=1')->select();
        $this->assign('cat_in_nav',$cat_in_nav);
        $this->assign('cattree',D('Admin/Cat')->gettree());

        $this->display();
    }

    /** 支付页面函数*/
    public function done(){
        if($_POST) {
            //入订单表 数据库
            $ordinfo = D('ordinfo');
            if ($ordinfo->create()) {
                if(!ctype_digit($ordinfo->address_id) || !ctype_digit($ordinfo->ord_sn) || !ctype_digit($ordinfo->user_id)){
                    $this->assign('error',true);
                    $this->assign('msg','大佬别搞事！');
                    $this->display();
                    die;
                }
                //防刷新
                if(!D('ordinfo')->field('ordinfo_id')->where(['ord_sn'=>$ordinfo->ord_sn])->find()) {
                    $time = $ordinfo->ordtime = time();
                    if (!$ordinfo->add()) {
                        $this->assign('error', true);
                        $this->assign('msg', 'ordinfo入库失败，请联系客服。');
                        $this->display();
                        die;
                    } else {
                        //入订单商品表 数据库
                        $ordinfo_id = $ordinfo->where(['ordtime' => $time])->getField('ordinfo_id');
                        $goods_id = explode(',', $_POST['goods_id']);
                        $each_qty = explode(',', $_POST['each_qty']);
                        $ordgoods = M('ordgoods');
                        foreach ($goods_id as $k => $g) {
                            $ordgoods->data(['ordinfo_id' => $ordinfo_id, 'goods_id' => $g, 'buy_qty' => $each_qty[$k]])->add();
                        }
                    }
                }
            }else {
                $this->assign('error',true);
                $this->assign('msg','未知错误，请联系客服。');
                $this->display();
                die;
            }
        }else{
            redirect(__ROOT__);
        }
        if($total = I('post.total')) {
            if($goods_id = I('post.goods_id')){
                $map['goods_id'] = array('in', $goods_id);
                M('cart')->where($map)->delete();
            }
            $jdpay = new \Home\Pay\JDPay('20151121257', $total);
            $this->assign('form', $jdpay->form());

            $this->assign('rid',$_POST['ord_sn']);
            $this->assign('total', $total);
            $cat_in_nav = M('cat')->field('cat_id,cat_name')->where('show_in_nav=1')->select();
            $this->assign('cat_in_nav', $cat_in_nav);

            $this->display();
        }else{
            $this->assign('error',true);
            $this->assign('msg','pay-error未知错误，请联系客服。');
            $this->display();
            die;
        }
    }

    /* 我的订单里取 某用户 所有订单  */
    function getOrder($user_id){
        if(!ctype_digit($user_id)){
            die(json_encode(['error'=>1, 'msg'=>'getout']));
        }
        if($res = M('ordinfo')->where(['user_id'=>$user_id])->select()){
            foreach($res as $k => $v) {
                $res[$k]['ordtime'] = date("Y-m-d H:i",$v['ordtime']);
                $res[$k]['name'] = M('user_address')->field('consignee')->find($v['address_id'])['consignee'];
                $res[$k]['goods'] = M('ordgoods')->field('ordgoods.goods_id,ordgoods.buy_qty,goods.goods_name,goods.shop_price,goods.thumb_img')->join('goods ON goods.goods_id = ordgoods.goods_id')->where(['ordgoods.ordinfo_id'=>$v['ordinfo_id']])->select();
            }
            die(json_encode($res,JSON_UNESCAPED_UNICODE));
        }else{
            die;
        }
    }
}
