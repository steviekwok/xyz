<?php
namespace Home\Controller;
use Think\Controller;

class SearchController extends  Controller{
    function index(){
        $keywords = htmlspecialchars(trim(I('get.keyword')));
        $keywords = str_replace("_","\_",$keywords);
        $keywords = str_replace("%","\%",$keywords);
        $map['goods_name'] = ['LIKE',"%$keywords%"];
        $map['goods_number'] = ['gt', 0];
        $map['is_on_sale'] = ['eq', 1];
        $goods = M('goods')->field('goods_id,goods_name,shop_price,goods_img')->where($map)->select();
        $this->assign('keywords',$keywords);
        $this->assign('goods',$goods);
        $this->assign('count',count($goods));

        $cat_in_nav = M('cat')->field('cat_id,cat_name')->where('show_in_nav=1')->select();
        $this->assign('cat_in_nav',$cat_in_nav);
        $his = array_reverse(session('history'),true);
        $this->assign('history',$his);
        $sale = M('goods')->field('goods_id,goods_name,thumb_img,shop_price,goods_number')->order('goods_number desc')->limit(10)->select();
        $this->assign('sale',$sale);
        $this->assign('cattree',D('Admin/Cat')->gettree());

        $this->display();
    }

    public function search(){
        $keywords = htmlspecialchars(trim(I('post.keyword')));
        $keywords = str_replace("_","\_",$keywords);
        $keywords = str_replace("%","\%",$keywords);
        $map['goods_name'] = ['LIKE',"%$keywords%"];
        $map['goods_number'] = ['gt', 0];
        $map['is_on_sale'] = ['eq', 1];
        $goods = M('goods')->field('goods_name')->where($map)->order('sort_order,goods_name desc')->limit(10)->select();

        die(json_encode($goods,JSON_UNESCAPED_UNICODE));
    }
}