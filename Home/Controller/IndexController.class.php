<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        //取商品展示栏所有分类
        $hot_cat = M('cat')->field('cat_recommend.cat_id,cat.cat_name')->join('cat_recommend ON cat.cat_id=cat_recommend.cat_id')->
        where('recommend_type=3')->select();
        $best_cat = M('cat')->field('cat_recommend.cat_id,cat.cat_name')->join('cat_recommend ON cat.cat_id=cat_recommend.cat_id')->
        where('recommend_type=1')->select();
        $new_cat = M('cat')->field('cat_recommend.cat_id,cat.cat_name')->join('cat_recommend ON cat.cat_id=cat_recommend.cat_id')->
        where('recommend_type=2')->select();
        array_unshift($best_cat,['cat_id'=>'0','cat_name'=>'全部商品']);
        array_unshift($new_cat,['cat_id'=>'0','cat_name'=>'全部商品']);
        array_unshift($hot_cat,['cat_id'=>'0','cat_name'=>'全部商品']);
        $this->assign('hot_cat',$hot_cat);
        $this->assign('best_cat',$best_cat);
        $this->assign('new_cat',$new_cat);

        //销售量，先按库存量顶替
        $sale = M('goods')->field('goods_id,goods_name,thumb_img,shop_price,goods_number')->order('goods_number desc')->limit(10)->select();
        $this->assign('sale',$sale);
        //最近浏览记录，
        $his = array_reverse(session('history'),true);

        $this->assign('cattree',D('Admin/Cat')->gettree());
        $cat_in_nav = M('cat')->field('cat_id,cat_name')->where('show_in_nav=1')->select();
        $this->assign('history',$his);
        $this->assign('cat_in_nav',$cat_in_nav);

        $this->display();
    }

    public function getCat(){
        $rec_array = array(1 => 'best', 2 => 'new', 3 => 'hot');
        $rec_type = !empty($_REQUEST['rec']) ? intval($_REQUEST['rec']) : '1';
        $cat_id = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : '0';

        $result   = array('error' => 0, 'content' => '', 'type' => $rec_type, 'cat_id' => $cat_id);
        $res_type_name = $rec_array[$rec_type];
        if($cat_id != 0) {
            $children = D('Admin/cat')->catList($cat_id);
            $map['cat_id'] = array('in', $children);
        }
        $map['is_'.$res_type_name] = 1;
        $map['goods_number'] = array('gt',0);
        $map['is_on_sale'] = 1;
        $item = M('goods')->field('goods_id,goods_name,shop_price,market_price,goods_img')->where($map)->order('sort_order,goods_id')->select();
        $this->assign('item',$item);
        $result['content'] = $this->fetch('ltab');

        die(json_encode($result,JSON_UNESCAPED_UNICODE));//Json不要编码Unicode,包括中文
    }
}