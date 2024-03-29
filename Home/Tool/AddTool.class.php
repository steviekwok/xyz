<?php
/**
 * Created by PhpStorm.
 * User: gerrard
 * Date: 2017/6/2
 * Time: 15:32
 */
namespace Home\Tool;

abstract class ACartTool {
    /**
     * 向购物车中添加1个商品
     * @param $goods_id int 商品id
     * @param $goods_name String 商品名
     * @param $shop_price float 价格
     * @return boolean
     */
    abstract public function add($goods_id, $goods_name, $shop_price,$market_price,$thumb_img,$goods_weight);

    /**
     * 减少购物中1个商品的数量, 如果减至0, 则从购物车删除此商品
     * @param $goods_id int 商品id
     */
    abstract public function decr($goods_id) ;

    /**
     * 从购物车删除某商品
     * @param $goods_id 商品id
     */
    abstract public function del($goods_id) ;

    /**
     * 列出购物车所有的商品
     * @return Array
     */
    abstract public function items() ;

    /**
     * 返回购物车中有几种商品
     * @return int
     */
    abstract public function calcType() ;

    /**
     * 返回购物中商品的个数
     * @return int
     */
    abstract public function calcCnt() ;

    /**
     * 返回购物车中商品的总价格
     * @return float
     */

    abstract public function calcMoney() ;
    /**
     * 清空购物车
     * @return void
     */
    abstract public function clear() ;
}

class AddTool extends ACartTool{
    public $item = array();
    static $ins = null;

    public static function getIns(){
        if (self::$ins == null) {
            self::$ins = new self();
            //echo '--------1--------';
        }
        return self::$ins;
    }

    final protected function __construct(){
        //echo '-----new------';
        $this->item  = session('?cart')?session('cart'):array();
    }
    /**
     * 向购物车中添加1个商品
     * @param $goods_id int 商品id
     * @param $goods_name String 商品名
     * @param $shop_price float 价格
     * @return boolean
     */
    public function add($goods_id, $goods_name, $shop_price,$market_price,$thumb_img,$goods_weight){
        //有时会把全空变量加进购物车，未解
        if(!empty($goods_id)) {
            if ($this->item[$goods_id]) {
                $this->item[$goods_id]['num']++;
            } else {
                $goods['goods_name'] = $goods_name;
                $goods['shop_price'] = $shop_price;
                $goods['market_price'] = $market_price;
                $goods['thumb_img'] = $thumb_img;
                $goods['goods_weight'] = $goods_weight;
                $goods['num'] = 1;
                $goods['is_select'] = 1;
                $this->item[$goods_id] = $goods;
            }
        }
    }

    /**
     * 减少购物中1个商品的数量, 如果减至0, 则从购物车删除此商品
     * @param $goods_id int 商品id
     */
    public function decr($goods_id){
        if($this->item[$goods_id]){
            $this->item[$goods_id]['num'] -= 1;
        }
        if($this->item[$goods_id]['num'] <=0){
            $this->del($goods_id);
        }
    }

    /**
     * 从购物车删除某商品
     * @param $goods_id 商品id
     */
    public function del($goods_id){
        unset($this->item[$goods_id]);
    }

    /**
     * 列出购物车所有的商品
     * @return Array
     */
    public function items(){
        return $this->item;
    }

    /**
     * 返回购物车中有几种商品
     * @return int
     */
    public function calcType(){
        return count($this->item);
    }

    /**
     * 返回购物中商品的个数
     * @return int
     */
    public function calcCnt(){
        $n = 0;
        foreach($this->item as $i) {
            $n += $i['num'];
        }
        return $n;
    }

    /**
     * 返回购物车中商品的总价格
     * @return float
     */

    public function calcMoney(){
        $n = 0;
        foreach($this->item as $i) {
            $n += $i['num'] * $i['shop_price'];
        }
        return $n;
    }
    /**
     * 清空购物车
     * @return void
     */
    public function clear(){
        $this->item = array();
    }

    public function __destruct(){
        //sleep(10);
        session('cart', $this->item);
    }
}



