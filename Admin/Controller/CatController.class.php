<?php
namespace Admin\Controller;
use Think\Controller;

class CatController extends Controller
{
    public function catadd()
    {
        if(!acc()){
            http_response_code(404);
        }
        if (IS_POST) {
            $catModel = D('cat');
            if($catModel->create()){
                $catlist = $catModel->select();
                foreach($catlist as $c){
                    if($catModel->cat_name == $c['cat_name'] && $catModel->parent_id == $c['parent_id']){
                        A('index')->message('已存在相同分类名称',1);
                        die;
                    }
                }
                if($result = $catModel->add()) {
                    if(count($_POST['cat_recommend'])){
                        foreach($_POST['cat_recommend'] as $v){
                            $dataList[] = array('cat_id'=>$result,'recommend_type'=>$v);
                        }
                        if(!M('cat_recommend')->addAll($dataList)){
                            A('index')->message('写入失败！error:cr表',1);
                            die;
                        }
                    }
                    clearcache();
                    A('Index')->message('添加分类成功！');
                }else{
                    A('index')->message('写入失败！error:c表',1);
                }
            } else{
                $this->error($catModel->getError());
            }
        }else{
            $this->assign('catList',D('cat')->gettree());
            $this->display();
        }
    }

    public function catlist()
    {
        if(!acc()){
            http_response_code(404);
        }
        $catModel = D('cat');
        $cat = S('cat');
        if(!$cat){
            $cat = $catModel->gettree();
            S('cat',$cat,1800000);
        }
        //S('cat',null);
        $this->assign('catlist',D('cat')->gettree());
        $this->display();
    }

    public function catedit($cat_id = 0){
        if(!acc()){
            http_response_code(404);
        }
        $catModel = D('cat');
        if (IS_POST) {
            if ($catModel->create()) {
                if (in_array($catModel->parent_id, D('cat')->catList($cat_id))) {
                    A('index')->message('所选择的上级分类不能是当前分类或者当前分类的下级分类');
                    exit();
                }
                $catlist = $catModel->select();
                foreach($catlist as $c){
                    if($catModel->cat_name == $c['cat_name'] && $catModel->parent_id == $c['parent_id'] && $cat_id != $c['cat_id']){
                        A('index')->message('已存在相同分类名称',1);
                        exit();
                    }
                }
                if ($catModel->where('cat_id=' . $cat_id)->save() !== false) {
                    $cr = M('cat_recommend')->field('recommend_type')->where('cat_id='.$cat_id)->select();
                    if(count($_POST['cat_recommend']) && !count($cr)) {
                        foreach ($_POST['cat_recommend'] as $v) {
                            $dataList[] = array('cat_id' => $cat_id, 'recommend_type' => $v);
                        }
                    }else{
                        foreach($cr as $v){
                            $cat_recommend[$v['recommend_type']] = true ;
                        }
                        foreach($_POST['cat_recommend'] as $v) {
                            $new_cr[$v] = true;
                        }
                        for($i = 1; $i<= 3; $i++) {
                            if ($cat_recommend[$i] != $new_cr[$i]) {
                                //新勾选加上，新取消勾选删除
                                $cat_recommend[$i] < $new_cr[$i] ? $dataList[] = array('cat_id' => $cat_id, 'recommend_type' => $i) :
                                    $del_rtype = $del_rtype ? $del_rtype. ','. $i : "$i";
                            }
                        }
                    }
                    if($del_rtype) {
                        $map['recommend_type'] = array('in', $del_rtype);
                        $map['cat_id'] = $cat_id;
                        M('cat_recommend')->where($map)->delete();
                    }
                    if(count($dataList)){
                        if(!M('cat_recommend')->addAll($dataList)){
                            A('index')->message('写入失败！error:cr表',1);
                            die;
                        }
                    }
                    clearcache();
                    A('index')->message('修改成功！');
                } else {
                    A('index')->message('修改失败error:c表',1);
                }
            } else {
                $this->error($catModel->getError());
            }
        }else{
            $this->assign('catlist',$catModel->gettree());
            $this->assign('cat_item',$catModel->find($cat_id));
            $cr = M('cat_recommend')->field('recommend_type')->where('cat_id='.$cat_id)->select();
            //$cat_recommend = array();
            foreach($cr as $v){
                $cat_recommend[$v['recommend_type']] = true;
            }
            $this->assign('cat_recommend',$cat_recommend);
            $this->display();
        }
    }

    public function catdel(){
        if(!acc()){
            http_response_code(404);
        }
        $catModel = M('cat');
        $cat_id = I('cat_id');
        if ($catModel->delete($cat_id)) {
            $cr = M('cat_recommend')->where('cat_id='.$cat_id)->select();
            if($cr){
                if(!M('cat_recommend')->where('cat_id='.$cat_id)->delete()){
                    A('index')->message('删除失败！error:cr表',1);
                }
            }
            clearCache();
            $this->redirect('Admin/cat/catlist');
        }else{
            A('index')->message('删除失败！error:c表',1);
        }
    }

    public function catmove(){
        if(!acc()){
            http_response_code(404);
        }
        if(IS_POST){
            $cat_id        = !empty($_POST['cat_id'])        ? intval($_POST['cat_id'])        : 0;
            $target_cat_id = !empty($_POST['target_cat_id']) ? intval($_POST['target_cat_id']) : 0;

            /* 商品分类不允许为空 */
            if ($cat_id == 0 || $target_cat_id == 0){
                A('index')->message('你没有正确选择商品分类',1);
                die;
            }

            /* 更新商品分类 */
            $goodsModel = M('goods');
            $goodsModel->cat_id = $target_cat_id;
            if($goodsModel->where('cat_id=' . $cat_id)->save() !== false){
                clearCache();
                $link[] = array('text' => '返回上一页', 'href' => 'http://shop.cn/admin/cat/catlist');
                A('index')->message('转移商品分类已成功完成！', 0, $link);
            }
        } else{
            $cat_id = I('get.cat_id');
            $this->assign('cat_id',$cat_id);
            $this->assign('catlist',D('cat')->gettree());
            $this->display();
        }
    }

    public function aajax(){
        if(!acc()){
            http_response_code(404);
        }
        if ($_REQUEST['act'] == 'toggle_show_in_nav' || $_REQUEST['act'] == 'toggle_is_show') {
            $catModel = M('cat');
            $cat_id = intval($_POST['id']);
            $item = substr($_REQUEST['act'],7);
            $val = $catModel->$item = intval(I('post.val'));
            if($catModel->where('cat_id=' . $cat_id)->save()){
                clearCache();
                make_json_response($val);
            }
        } elseif($_REQUEST['act'] == 'edit_sort_order'){
            $catModel = M('cat');
            $cat_id   = intval($_POST['id']);
            $sort_order  = $catModel->sort_order = intval($_POST['val']);
            if($catModel->where('cat_id=' . $cat_id)->save()){
                clearCache();
                make_json_response($sort_order);
            }
            else{
                A('index')->message('您没有选择任何操作',1);
            }
        }
    }
}