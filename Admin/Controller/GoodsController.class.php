<?php
namespace Admin\Controller;
use Think\Controller;

class GoodsController extends Controller
{
    public $gm;

    public function __construct()
    {
        parent::__construct();
        $this->gm = D('goods');
    }

    public function goodslist()
    {
        if(!acc()){
            http_response_code(404);
        }
        if(I('post.act') == 'batch'){
            $goods_id = !empty($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0;

            if (isset($_POST['type']))
            {
                /* 删除商品（批量操作) */
                if ($_POST['type'] == 'trash')
                {
                    /* 检查权限 */
                    //admin_priv('remove_back');
                    $this->gm->delete_goods($goods_id);
                    /* 记录日志 */
                    //admin_log('', 'batch_trash', 'goods');
                }
                /* 上架 */
                elseif ($_POST['type'] == 'on_sale')
                {
                    $this->gm->update_goods($goods_id, 'is_on_sale', '1');
                }

                /* 下架 */
                elseif ($_POST['type'] == 'not_on_sale')
                {
                    $this->gm->update_goods($goods_id, 'is_on_sale', '0');
                }

                /* 设为精品 */
                elseif ($_POST['type'] == 'best')
                {
                    $this->gm->update_goods($goods_id, 'is_best', '1');
                }

                /* 取消精品 */
                elseif ($_POST['type'] == 'not_best')
                {
                    $this->gm->update_goods($goods_id, 'is_best', '0');
                }

                /* 设为新品 */
                elseif ($_POST['type'] == 'new')
                {
                    $this->gm->update_goods($goods_id, 'is_new', '1');
                }

                /* 取消新品 */
                elseif ($_POST['type'] == 'not_new')
                {
                    $this->gm->update_goods($goods_id, 'is_new', '0');
                }

                /* 设为热销 */
                elseif ($_POST['type'] == 'hot')
                {
                    $this->gm->update_goods($goods_id, 'is_hot', '1');
                }

                /* 取消热销 */
                elseif ($_POST['type'] == 'not_hot')
                {
                    $this->gm->update_goods($goods_id, 'is_hot', '0');
                }

                /* 转移到分类 */
                elseif ($_POST['type'] == 'move_to')
                {
                    //echo  I('post.target_cat');
                    $this->gm->update_goods($goods_id, 'cat_id', I('post.target_cat'));
                }
            }
        }else{
            $p = I('get.p') ? I('get.p') : 1;
            // 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
            $filter = $this->getFilter();
            if (I('post.act') == 'query') {//按上面列头排序or搜索
                if ($filter['cat_id']) {
                    $map['cat_id'] = $filter['cat_id'];
                }
                switch ($filter['intro_type']) {
                    case 'is_best':
                        $map['is_best'] = 1;
                        break;
                    case 'is_hot':
                        $map['is_hot'] = 1;
                        break;
                    case 'is_new':
                        $map['is_new'] = 1;
                        break;
                    case 'all_type';
                        $map['is_best'] = 1;
                        $map['is_hot'] = 1;
                        $map['is_new'] = 1;
                }
                if($filter['brand_id']){
                    $map['brand_id'] = $filter['brand_id'];
                }
                if($filter['is_on_sale'] != ''){
                    $map['is_on_sale'] = $filter['is_on_sale'];
                }
                if($filter['keyword'] != ''){
                    $map['goods_name|goods_sn'] = array('like','%'.$filter['keyword'].'%');
                }

                if(count($map)){
                    $list = $this->gm->where($map)->order($filter['sort_by'].' '.$filter['sort_order'])->page($p.',10')->select();
                    $count = $this->gm->where($map)->count();// 查询满足要求的总记录数
                }
                else{
                    $list = $this->gm->order($filter['sort_by'].' '.$filter['sort_order'])->page($p.',10')->select();
                    $count = $this->gm->count();// 查询满足要求的总记录数
                }
            }
            elseif(I('cat_id')){//商品分类页面点击商品名（cat_name)跳到商品列表页面专用
                $list = $this->gm->order($filter['sort_by'].' '.$filter['sort_order'])->where('cat_id='.I('cat_id'))->page($p.',10')->select();
                $count = $this->gm->where('cat_id='.I('cat_id'))->count();// 查询满足要求的总记录数
                $this->assign('catselect',I('cat_id'));
            }
            else{
                $list = $this->gm->order($filter['sort_by'].' '.$filter['sort_order'])->page($p.',10')->cache(true,360)->select();
                $count = $this->gm->count();// 查询满足要求的总记录数
            }
            $this->assign('goodslist', $list);// 赋值数据集
            $this->assign('page', getPage($count));// 赋值分页输出
            $this->assign('catlist',D('cat')->gettree());

            /* 排序标记 */
            $sort_flag  = $this->sortFlag($filter);
            $this->assign($sort_flag['tag'], $sort_flag['img']);
            if (I('post.act') == 'query'){//按上面列头排序or搜索
                make_json_response($this->fetch() , 0, '', array('filter' => $filter , 'page'=> $p));
            }
            $this->assign('full_page',1);
            $this->display(); // 输出模板

        }
    }

    public function goodsadd()
    {
        if(!acc()){
            http_response_code(404);
        }
        if (IS_POST) {
            //商品号没填写－自动填
            if(!I('goods_sn')) {
                $where['goods_sn'] = array('like','ECS%');
                $data = $this->gm->field('goods_sn')->where($where)->limit(1)->order('goods_sn desc')->find();
                $_POST['goods_sn'] = ++$data['goods_sn'];
            }
            if ($this->gm->create()) {
                if(!$msg = $this->func()) {
                    $this->error($msg);
                }else {
                    $result = $this->gm->add();
                    if ($result) {
                        A('index')->message('添加成功');
                    }
                    else {
                        $this->error('写入错误！');
                    }
                }
            } else {
                $this->error($this->gm->getError(),'',2);
            }
        }else {
            $this->assign('catlist',D('cat')->gettree());
            $this->assign('brand', M('brand')->field('brand_id,brand_name')->select());
            clearCache();
            $this->display();
        }
    }

    public function goodsedit()
    {
        if(!acc()){
            http_response_code(404);
        }
        //判断地址栏传来的goods_id 是否合法
        if(!$goods_id = intval(I('goods_id'))){
            $this->redirect('Admin/index/index');
        }
        if (IS_POST) {
            if (!I('goods_sn')) {
                $data = $this->gm->field('goods_sn')->limit(1)->order('goods_sn desc')->find();
                $_POST['goods_sn'] = ++$data['goods_sn'];
            }
            if ($this->gm->create()) {
                if(!$msg = $this->func()) {
                    $this->error($msg);
                }else {
                    if ($this->gm->where('goods_id=' . $goods_id)->save()) {
                        A('index')->message('修改成功');
                    }
                }
            }else{
                $this->error($this->gm->getError(),'',2);
            }
        }else {
            $goodsItem = $this->gm->find($goods_id);
            $this->assign('goods_item', $goodsItem);
            $this->assign('catlist', D('cat')->gettree());
            $this->assign('brand', M('brand')->field('brand_id,brand_name')->select());
            clearCache();
            $this->display();
        }
    }

    protected function func(){
        $this->gm->goods_desc = $_POST['goods_desc'];
        $this->gm->goods_weight *= floatval($_POST['weight_unit']);
        //var_dump($this->gm->goods_weight);
        if(!isset($_POST['is_best'])){
            $this->gm->is_best = 0;
        }
        if(!isset($_POST['is_new'])){
            $this->gm->is_new = 0;
        }
        if(!isset($_POST['is_hot'])){
            $this->gm->is_hot = 0;
        }
        if(!isset($_POST['is_on_sale'])){
            $this->gm->is_on_sale = 0;
        }
        if(!isset($_POST['is_shipping'])){
            $this->gm->is_shipping = 0;
        }

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = './Uploads/'; // 设置附件上传根目录
        $upload->savePath = ''; // 设置附件上传（子）目录
        // 上传文件
        $info = $upload->upload();
        if (!$info) {// 上传错误提示错误信息
            return $upload->getError();
        }else{
            // 上传成功
            $img_path1 = '/Uploads/' . $info['goods_img']['savepath'];
            $img_path2 = $info['goods_img']['savename'];

            $image = new \Think\Image();
            $image->open('.'. $img_path1 . $img_path2);
            $img_thumb = '/Uploads/thumb/' . $img_path2;
            // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
            $image->thumb(230, 230)->save('.'. $img_thumb);

            $this->gm->goods_img = $img_path1 . $img_path2;
            $this->gm->thumb_img = $img_thumb;
            return true;
        }
    }

    public function goodscopy()
    {
        if(!acc()){
            http_response_code(404);
        }
        //判断地址栏传来的goods_id 是否合法
        if(!$goods_id = intval(I('goods_id'))){
            $this->redirect('admin/index/index');
        }
        $goodsItem = $this->gm->find($goods_id);
        $this->assign('goods_item', $goodsItem );
        $FCKeditor = create_html_editor('goods_desc',$goodsItem['goods_desc']);
        $this->assign('FCKeditor', $FCKeditor );
        $this->assign('catlist',D('cat')->gettree());
        $this->display();
    }

    public function del()
    {
        if(!acc()){
            http_response_code(404);
        }
        $result = $this->gm->where('goods_id=' . I('get.goods_id'))->delete();
        if($result) {
            M('comment')->where('goods_id='.I('goods_id'))->delete();
            clearCache();
            header("location:".$_SERVER['HTTP_REFERER']);
        }else {
            A('index')->message('删除失败！',1);
        }
    }

    //商品编辑里的浏览图片功能
    public function goodsimg(){
        if(!$goods_id = intval(I('goods_id'))){
            $this->redirect('Admin/index/index');
        }
        $this->assign('img', M('goods')->field('goods_img')->find($goods_id)['goods_img']);
        $this->display();
    }

    public function aajax(){
        /*------------------------------------------------------ */
        //-- 编辑修改
        /*------------------------------------------------------ */
        if(!acc()){
            http_response_code(404);
        }
        if ($_REQUEST['act'] == 'edit_goods_name') {
            $goods_id   = intval($_POST['id']);
            $goods_name = $this->gm->goods_name = trim($_POST['val']);
            $this->gm->last_update = time();
            if($this->gm->where('goods_id=' . $goods_id)->save()){
               clearCache();
               make_json_response(stripslashes($goods_name));
            }
        } elseif($_REQUEST['act'] == 'edit_goods_sn') {
            $goods_id   = intval($_POST['id']);
            $goods_sn = $this->gm->goods_sn = trim($_POST['val']);
            $this->gm->last_update = time();
            if($this->gm->where(['goods_sn'=>$goods_sn])->count()){
                make_json_response('',1,'您输入的货号已存在，请换一个');
            }
            if($this->gm->where('goods_id=' . $goods_id)->save()){
                clearCache();
                make_json_response(stripslashes($goods_sn));
            }
        } elseif($_REQUEST['act'] == 'edit_goods_price'){
            $goods_id   = intval($_POST['id']);
            $shop_price = floatval($_POST['val']);
            if($shop_price < 0 || !is_numeric($_POST['val'])){
                make_json_response('',1,'您输入了一个非法的市场价格');
            }
            $this->gm->shop_price = $shop_price;
            $this->gm->market_price = $shop_price * 1.2;
            $this->gm->last_update = time();
            if($this->gm->where('goods_id=' . $goods_id)->save()){
                clearCache();
                make_json_response(number_format($shop_price, 2, '.', ''));
            }
        } elseif($_REQUEST['act'] == 'edit_sort_order'){
            $goods_id   = intval($_POST['id']);
            $sort_order  = $this->gm->sort_order = intval($_POST['val']);
            $this->gm->last_update = time();
            if($this->gm->where('goods_id=' . $goods_id)->save()){
                clearCache();
                make_json_response($sort_order);
            }
        } elseif($_REQUEST['act'] == 'edit_goods_number'){
            $goods_id   = intval($_POST['id']);
            $goods_number = intval($_POST['val']);
            if($goods_number < 0 || !is_numeric($_POST['val'])){
                make_json_response('',1,'商品库存数量错误');
            }
            $this->gm->goods_number = $goods_number;//用intval因为防止输入是小数
            $this->gm->last_update = time();
            if($this->gm->where('goods_id=' . $goods_id)->save()){
                clearCache();
                make_json_response($goods_number);
            }
        } elseif ($_REQUEST['act'] == 'toggle_on_sale' || $_REQUEST['act'] == 'toggle_best' || $_REQUEST['act'] == 'toggle_new' || $_REQUEST['act'] == 'toggle_hot') {
            $goods_id       = intval($_POST['id']);
            $item = 'is'.substr($_REQUEST['act'],6);
            $val = $this->gm->$item = intval($_POST['val']);
            $this->gm->last_update = time();
            if($this->gm->where('goods_id=' . $goods_id)->save()){
                clearCache();
                make_json_response($val);
            }
        } else{
            A('index')->message('您没有选择任何操作',1);
        }
    }

    /**
     * 根据过滤条件获得排序的标记
     *
     * @access  public
     * @param   array   $filter
     * @return  array
     */
    protected function sortFlag($filter) {
        $flag['tag']    = 'sort_' . preg_replace('/^.*\./', '', $filter['sort_by']);
        $flag['img']    = '<img src="' . __ROOT__ . '/Public/Admin/images/' . ($filter['sort_order'] == "DESC" ? 'sort_desc.gif' : 'sort_asc.gif') . '">';

        return $flag;
    }

    protected function getFilter() {
        $filter['sort_by']          = empty($_REQUEST['sort_by']) ? 'goods_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order']       = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['cat_id']           = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
        $filter['intro_type']       = empty($_REQUEST['intro_type']) ? '' : trim($_REQUEST['intro_type']);
        //$filter['is_promote']       = empty($_REQUEST['is_promote']) ? 0 : intval($_REQUEST['is_promote']);
        $filter['brand_id']         = empty($_REQUEST['brand_id']) ? 0 : intval($_REQUEST['brand_id']);
        $filter['keyword']          = $_REQUEST['keyword'] == '' ?  '' : trim($_REQUEST['keyword']);
        $filter['is_on_sale'] = $_REQUEST['is_on_sale'] == '' ? '' : trim($_REQUEST['is_on_sale']);
        return $filter;
    }
}
