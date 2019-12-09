<?php
namespace Home\Controller;
use Think\Controller;

class GoodsController extends Controller{
    public function goods(){
        $goods = D('Admin/goods');
        $goodsinfo = $goods->field('goods.*,brand.brand_name')->join('brand ON goods.brand_id=brand.brand_id')->find(I('goods_id'));
        $this->assign('goodsinfo',$goodsinfo);
        /*if($goodsinfo){
            $this->history($goodsinfo);
        }*/

        $comment = $goods->relationGet('comment');
        $rank_sum = $sum = 0;
        if($comment) {
            foreach ($comment as $k1 => $v1) {
                foreach ($comment as $k2 => $v2) {
                    if ($v1['comment_id'] == $v2['parent_id']) {
                        $comment[$k1]['parent_content'] = $v2['content'];
                        unset($comment[$k2]);
                    }
                }
                if ($v1['username'] != 'admin') {
                    $rank_sum += $v1['comment_rank'];
                }
            }
            $sum = count($comment);
            $this->assign('comment', $comment);
        }
        $this->assign('sum',$sum);//评价商品的条数
        $this->assign('rank', round($rank_sum/$sum));//计算用户评价等级（最高5）

        $mbx = D('Admin/cat')->parentList($goodsinfo['cat_id']);//面包屑 假如  手机分类>GSM手机>XXX....
        if($mbx){
            $this->assign('mbx',$mbx);
        }
        $cat_in_nav = M('cat')->field('cat_id,cat_name')->where('show_in_nav=1')->select();
        $this->assign('cat_in_nav',$cat_in_nav);

        if(che()){
            $user_id = M('user')->where("username='%s'",cookie('username'))->find()['user_id'];
            $this->assign('user_id',$user_id);
        }

        $likegoods = M('goods')->field('goods_id,goods_name,shop_price,market_price,goods_img')->where('goods_name like "%iphone%"')->select();
        $this->assign('likegoods',$likegoods);
        $this->display();
    }

//利用sessison取近6个最近浏览记录
    public function history($g){
        $history = session('history');
        if(empty($history)){
            $history = array();
        }
        if(isset($history[$g['goods_id']])){
            unset($history[$g['goods_id']]);
        }
        $row = array();
        $row['goods_name'] = $g['goods_name'];
        $row['thumb_img'] = $g['thumb_img'];
        $row['shop_price'] = $g['shop_price'];

        $history[$g['goods_id']] = $row;

        if(count($history) > 6){
            $key = key($history);//第一个单元的键
            unset($history[$key]);
        }
        session('history',$history);
    }

    public function comment(){
        if(IS_POST){
            if(!(intval($_POST['goods_id']) && intval($_POST['user_id']) && intval($_POST['comment_rank']))){
                http_response_code(404);
            }
            if(!I('content')) {
                $_POST['content'] = htmlspecialchars(trim(I('content')));
            }
            $comment = D('comment');
            if(!$comment->create()){
                $this->error($comment->getError());
            }
            else{
                $comment->ip_address = sprintf('%u', ip2long(getRealIp()));
                $temp = M('user')->field('username,email')->find($comment->user_id);
                $comment->username = $temp['username'];
                $comment->email = $temp['email'];
                if($comment->add()){
                   $this->success('评论成功','',1);
                }else
                    $this->error('评论失败','',0);
            }
        }
    }
}