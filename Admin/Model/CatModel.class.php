<?php
/**
 * Created by PhpStorm.
 * User: gerrard
 * Date: 2017/5/23
 * Time: 13:34
 */
namespace Admin\Model;
use Think\Model;

class CatModel extends  Model{
    //计算分类等级lv，顶级－二级－三级......
    public function gettree($p=0,$lv=0){
        $t = array();
        foreach($this->field('cat.*,COUNT(goods.cat_id) as num')->join('left join goods ON cat.cat_id = goods.cat_id')->group('cat.cat_id')
                    ->cache(true)->select() as $data){
            if($data['parent_id'] == $p){
                $data['lv'] = $lv;
                $t[] = $data;
                $t = array_merge($t,($this->gettree($data['cat_id'],$lv+1)));
            }
        }
        return $t;
    }

    //商品分类编辑下，计算‘所选择的上级分类不能是当前分类或者当前分类的下级分类’时用
    //取当前编辑的cat_id和下级分类的cat_id
    public function catList($p){
        //$t = array();
        $t[] = $p;
        foreach ($this->select() as $data) {
            if ($data['parent_id'] == $p) {
                $t = array_merge($t,($this->catList($data['cat_id'])));
            }
        }
        return $t;
    }
    //面包屑
    public function parentList($cat_id){
        $list = array();
        while($cat_id){
            $t = $this->field('cat_id,cat_name,parent_id')->find($cat_id);
            $cat_id = $t['parent_id'];
            $list[] = $t;
        }

        if(!empty($list)) {
            if(count($list) > 1){
                $list = array_reverse($list);
            }
            return $list;
        }
        else
            return false;
    }
}