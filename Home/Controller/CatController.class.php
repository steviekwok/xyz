<?php
namespace Home\Controller;
use Think\Controller;

class CatController extends Controller{
    public function cat(){
        //$p = I('get.p') ? I('get.p') : 1;
        $cat_id = intval(I('get.cat_id'));
        if(!$cat_id){
            http_response_code(404);
        }

        $display = cookie('display');
        if($display) {
            if (!preg_match("/^list|grid$/", $display)) {
                die('hack out');
            }else{
                if($display == 'list') {
                    $this->assign('display', 1);
                }else{
                    $this->assign('display', 0);
                }
            }
        }
        $this->assign('cat_id', $cat_id);
        $mbx = D('Admin/cat')->parentList($cat_id);
        if($mbx){
            $this->assign('mbx',$mbx);
        }

        $cat_in_nav = M('cat')->field('cat_id,cat_name')->where('show_in_nav=1')->select();
        $this->assign('cat_in_nav',$cat_in_nav);
        $his = array_reverse(session('history'),true);
        $this->assign('history',$his);
        $sale = M('goods')->field('goods_id,goods_name,thumb_img,shop_price,goods_number')->order('goods_number desc')->limit(10)->select();
        $this->assign('sale',$sale);
        $this->assign('cattree',D('Admin/Cat')->gettree());

        $this->assign('cat_name', M('cat')->field('cat_name')->find($cat_id)['cat_name']);

        $this->display();
    }

    function getCatGoods() {
        $cat_id = intval(I('post.cat_id'));
        $p = intval(I('post.p'));
        $per_page = intval(I('post.per_page'));
        $sort_data = I('post.sort_data');
        if(!($cat_id && $p && $per_page)) {
            die('hack out');
        }

        if($sort_data && !preg_match("/^[a-z_-]+$/", $sort_data)) {
            die('hack out');
        }
        $goodsModel = D('Admin/goods');
        $map['cat_id'] = array('in',D('Admin/cat')->catList($cat_id));
        if($sort_data) {
            $list = explode('-', $sort_data);
            $goodslist = $goodsModel->field('goods_id,goods_name,shop_price,market_price,goods_img')->where($map)->order($list[0].' '.$list[1])
                ->page($p . ',' . $per_page)->select();
        }else {
            $goodslist = $goodsModel->field('goods_id,goods_name,shop_price,market_price,goods_img')->where($map)->page($p . ',' . $per_page)->select();
        }
        $total_items = intval($goodsModel->where($map)->count());
        if($total_items % $per_page){
            $total_page = intval($total_items / $per_page) + 1;
        }else{
            $total_page = $total_items / $per_page;
        }
        $left_row = ($p-1)*$per_page + 1;
        if($total_page == $p){
            $right_row = $total_items;
        }else {
            $right_row =  $p * $per_page;
        }
        $res = ['goodslist' => $goodslist, 'total_items' => $total_items, 'total_page' => $total_page, 'current_page' => $p, 'left_row' => $left_row,
               'right_row' => $right_row];
        die(json_encode($res,JSON_UNESCAPED_UNICODE));
    }
}