<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class GoodsModel extends RelationModel{
    protected $_link = array(
        'comment' => array(
            'mapping_type' => self::HAS_MANY,
            'mapping_fields' => 'pubtime,comment_rank,content,parent_id,comment_id,username',
            'mapping_order' => 'pubtime desc',
            'condition' => 'status=1'
        )
    );

    public function gettree(){
        return $this->select();
    }

    //定义自动过滤
    //protected $insertFields='goods_name,goods_sn,shop_price';

    // 定义自动验证
    protected $_validate = array(
       //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
        array('goods_name','require','商品名称必须'),
        //array('goods_sn','c','商品货号必须',1,'callback'),
        array('goods_sn','','商品货号重复',1,'unique'),
        array('shop_price','require','商品价格必须'),
        array('shop_price','currency','商品价格错误',1),
        //array('goods_name','require','商品名称必须'),
    );

    // 定义自动完成
    protected $_auto = array(
        array('add_time','time',1,'function'),
        array('last_update','time',2,'function'),
        //array('goods_sn','c($this)',3,'callback'),
    );

    /**
     * 修改商品某字段值
     * @param   string  $goods_id   商品编号，可以为多个，用 ',' 隔开
     * @param   string  $field      字段名
     * @param   string  $value      字段值
     * @return  bool
     */
    function update_goods($goods_id, $field, $value)
    {
        if (empty($goods_id) && empty($field) && empty($value)) {
            return;
        }
        /* 清除缓存 */
        //clear_cache_files();
        $this->$field = $value;
        $map['goods_id'] = array('in',$goods_id);
        $this->where($map)->save();
        clearCache();
        A('index')->message('批量操作成功');
    }

    /**
     * 从回收站删除多个商品
     * @param   mix     $goods_id   商品id列表：可以逗号格开
     * @return  void
     */
    function delete_goods($goods_id)
    {
        if (empty($goods_id)) {
            return;
        }
        $map['goods_id'] = array('in',$goods_id);
        /* 取得图片路径 */
        $data = $this->field('thumb_img,goods_img')->where($map)->select();
        foreach($data as $v){
            if(!empty($v['thumb_img'])){
                @unlink('.' . $v['thumb_img']);
            }
            if(!empty($v['goods_img'])){
                @unlink('.' . $v['goods_img']);
            }
        }
        M('comment')->where($map)->delete();

        $this->where($map)->delete();
        clearCache();
        A('index')->message('批量操作成功');
    }
}