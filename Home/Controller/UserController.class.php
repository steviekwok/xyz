<?php
namespace Home\Controller;
use Think\Controller;

class UserController extends Controller{
    public function login(){
        if(I('post.act')=='login'){
            $username = trim(I('post.username'));
            $pwd = trim(I('post.password'));
            if(!check_verify(I('post.verify')) && I('post.ext')!='cart') {//购物车界面登录没设验证码
                die("验证码错误");
            }
            if(!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]{1,20}$/u", $username)){
                die('用户名或密码错误');
            }
            if(!preg_match("/^[a-zA-Z0-9!@#$%^&*,.]{6,30}$/", $pwd)){
                die('用户名或密码错误');
            }
            $userinfo = M('User')->where(['username'=>$username])->find();
            if(!$userinfo || $userinfo['password'] !== md5($pwd.$userinfo['salt'])) {
                die('用户名或密码错误');
            }

            cookie('userid',$userinfo['password'],'expire=86400&httponly=true');
            cookie('username',$userinfo['username'],'expire=86400&httponly=true');
            cookie('key',jm($userinfo['username'].$userinfo['password'].C('COO_KIE')),'expire=86400&httponly=true');
            //登录后把session cart的购物商品放进 所属用户字段的购物车表
            $tool = \Home\Tool\AddTool::getIns();
            if($tool->calcType()) {
                //防止PHPSESSID被修改成java代码或标签
                if(preg_match("/^[a-z0-9]*$/",cookie('PHPSESSID'))){
                    $session_id = cookie('PHPSESSID');
                    foreach($tool->items() as $k=>$s){
                        $cart = M('goods')->field('goods_id,goods_sn,market_price,shop_price')->where('goods_id='.$k)->find();
                        $cart['goods_number'] = $s['num'];
                        $cart['is_select'] = $s['is_select'];
                        $cart['session_id'] = $session_id;
                        $cart['user_id'] = $userinfo['user_id'];
                        //$Model = new \Think\Model();
                        //$Model->execute("REPLACE INTO cart('goods_id','goods_number','session_id','user_id') VALUES('".$k."','".$s['num']."','".$session_id."','".$user_id."')");
                        //如果购物车表原来有这sku，在原纪录改变数量
                        //if是原纪录和现在同session_id，更新就好~~~elseif原纪录和现在session_id不同，在原纪录（原session）上更新，废弃现session_id
                        if($temp = M('cart')->where("session_id='{$session_id}' and goods_id='".$k."' and user_id='{$cart['user_id']}'")->find()){
                            if($temp['goods_number'] != $cart['goods_number'] || $temp['is_select'] != $cart['is_select']) {
                                M('cart')->where("goods_id='{$k}' and user_id='{$cart['user_id']}'")->save($cart);
                            }
                        } elseif($temp = M('cart')->where("goods_id='".$k."' and user_id='{$cart['user_id']}'")->find()) {//不同session、同用户同商品放一起（放在旧session记录
                            unset($cart['session_id']);
                            if($temp['goods_number'] != $cart['goods_number'] || $temp['is_select'] != $cart['is_select']) {
                                M('cart')->where("goods_id='{$k}' and user_id='{$cart['user_id']}'")->save($cart);
                            }
                        }
                        else {
                            M('cart')->add($cart);
                        }
                    }
                    $tool->clear();
                }
            }
            die("done");
        }
        $this->assign('cattree',D('Admin/Cat')->gettree());
        $cat_in_nav = M('cat')->field('cat_id,cat_name')->where('show_in_nav=1')->cache(true,360)->select();
        $this->assign('cat_in_nav',$cat_in_nav);
        $this->display();
    }

    public function myAccout(){
        if(che()){
            $user_id = M('user')->where("username='%s'",cookie('username'))->find()['user_id'];
            $this->assign('user_id',$user_id);
            $this->assign('cattree',D('Admin/Cat')->gettree());
            $cat_in_nav = M('cat')->field('cat_id,cat_name')->where('show_in_nav=1')->select();
            $this->assign('cat_in_nav',$cat_in_nav);
            $this->display();
        }else{
            $this->assign('error', 1);
            $this->display();
        }
    }

    //设置默认地址
    function setDt($address_id){
        if(ctype_digit($address_id)){
            if(M('user_address')->where(['default'=>1])->find()) {
                M('user_address')->where(['default'=>1])->setField('default','0');
            }
            if(M('user_address')->where(['address_id'=>$address_id])->setField('default','1')) {
                die('done');
            }
        }
        die;
    }

    //取某用户所有地址
    function getAddress($user_id){
        if(!ctype_digit($user_id)){
            die(json_encode(['error'=>1, 'msg'=>'getout']));
        }
        if($res = M('user_address')->where(['user_id'=>$user_id])->select()){
            foreach($res as $k => $v) {
                $province_name = M('region')->field('region_name')->where(['region_id'=>$v['province']])->find()['region_name'];
                $city_name = M('region')->field('region_name')->where(['region_id'=>$v['city']])->find()['region_name'] . '市';
                $district_name = M('region')->field('region_name')->where(['region_id'=>$v['district']])->find()['region_name'];
                $res[$k]['province_name'] = $province_name;
                $res[$k]['city_name'] = $city_name;
                $res[$k]['district_name'] = $district_name;
                if ($v['default'] == 1) {
                    $temp = $res[$k];
                    unset($res[$k]);
                    array_unshift($res, $temp);
                }
            }

            die(json_encode($res,JSON_UNESCAPED_UNICODE));
        }else{
            die;
        }
    }

    //取一条地址记录
    function getAddressOne($address_id){
        if(!ctype_digit($address_id)){
            die(json_encode(['error'=>1, 'msg'=>'getout']));
        }
        if($res = M('user_address')->where(['address_id'=>$address_id])->find()){
            $province_name = M('region')->field('region_name')->where(['region_id'=>$res['province']])->find()['region_name'];
            $city_name = M('region')->field('region_name')->where(['region_id'=>$res['city']])->find()['region_name'] . '市';
            $district_name = M('region')->field('region_name')->where(['region_id'=>$res['district']])->find()['region_name'];
            $res['province_name'] = $province_name;
            $res['city_name'] = $city_name;
            $res['district_name'] = $district_name;
            die(json_encode($res,JSON_UNESCAPED_UNICODE));
        }else{
            die(json_encode(['error'=>1, 'msg'=>'getout']));
        }
    }

    function delAddress($address_id){
        if(!ctype_digit($address_id)  || !che()){
            die;
        }
        if($res = M('user_address')->where(['address_id'=>$address_id])->delete()){
            die('done');
        }else{
            die;
        }
    }

    function editArName()
    {
        $user_address = M('user_address');
        $user_address->address_id = intval(I('post.address_id'));
        $user_address->address_name = I('post.address_name');
        if (!$user_address->address_id || !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9_-]{1,50}$/u", $user_address->address_name)) {
            die('hack out');
        }
        if ($user_address->save()) {
            die('done');
        } else {
            die('保存失败');
        }
    }

    function getRegion($region_type,$parent_id){
        if(!intval($region_type) || !intval($parent_id)){
            die('hack out');
        }

        if($res = M('region')->where(['region_type'=>$region_type,'parent_id'=>$parent_id])->select()){
            die(json_encode($res,JSON_UNESCAPED_UNICODE));
        }
        die;
    }

    function addAddress(){
        if($_POST) {
            $user_address = D('user_address');
            if ($user_address->create()) {
                if(!ctype_digit($user_address->user_id) || !M('user_address')->where(['user_id'=>$user_address->user_id])->find()){
                    die;
                }
                if (!ctype_digit($user_address->mobile)) {
                    die;
                }
                if ($user_address->email && !preg_match("/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/", $user_address->email)) {
                    die;
                }
                if($user_address->tel && !preg_match("/^[0-9-]{8,15}$/", $user_address->tel)) {
                    die;
                }
                $user_address->address_name = htmlspecialchars($user_address->address_name, ENT_QUOTES);
                $user_address->consignee = htmlspecialchars($user_address->consignee, ENT_QUOTES);
                $user_address->address = htmlspecialchars($user_address->address, ENT_QUOTES);
                $user_address->province = htmlspecialchars($_POST['sjld'][0], ENT_QUOTES);
                $user_address->city = htmlspecialchars($_POST['sjld'][1], ENT_QUOTES);
                $user_address->district = htmlspecialchars($_POST['sjld'][2], ENT_QUOTES);

                if ($user_address->add()) {
                    die('done');
                }
            }
        }
        die;
    }

    function editAddress() {
        if($_POST) {
            $user_address = D('user_address');
            $full_user_id =  M('user')->getField('user_id',true);
            if ($user_address->create()) {
                if(!ctype_digit($user_address->address_id)){
                    die;
                }
                if(!ctype_digit($user_address->user_id) || !in_array($user_address->user_id,$full_user_id)){
                    die;
                }
                if (!ctype_digit($user_address->mobile)) {
                    die;
                }
                if ($user_address->email && !preg_match("/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/", $user_address->email)) {
                    die;
                }
                if($user_address->tel && !preg_match("/^[0-9-]{8,15}$/", $user_address->tel)) {
                    die;
                }
                $user_address->address_name = htmlspecialchars($user_address->address_name, ENT_QUOTES);
                $user_address->consignee = htmlspecialchars($user_address->consignee, ENT_QUOTES);
                $user_address->address = htmlspecialchars($user_address->address, ENT_QUOTES);
                $user_address->province = htmlspecialchars($_POST['sjld'][0], ENT_QUOTES);
                $user_address->city = htmlspecialchars($_POST['sjld'][1], ENT_QUOTES);
                $user_address->district = htmlspecialchars($_POST['sjld'][2], ENT_QUOTES);
                if ($res = $user_address->save()) {
                    die('done');
                } elseif($res === false) {
                    die('error');
                }
                die('nochange');
            }
        }
        die('nopost');
    }

    public function logOut(){
        cookie('userid',null);
        cookie('username',null);
        cookie('key',null);
        $this->redirect('login');
    }

    public function reg(){
        if($_POST) {
            if(!check_verify(I('post.verify'))) {//购物车界面登录没设验证码
                $this->error('验证码错误');
            }
            $userModel = D('user');
            if ($userModel->create()) {
                if(!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]{1,20}$/u",$userModel->username)){
                    $this->error('用户名不符合注册原则');
                }
                if (!preg_match("/^[a-zA-Z0-9!@#$%^&*,.]+$/", $userModel->password)) {
                    $this->error('密码不符合注册原则');
                }
                if($userModel->telephone){
                    if(!intval($userModel->telephone)){
                        $this->error('电话号码格式错误');
                    }
                }
                $s = $this->salt();
                $userModel->password = md5($userModel->password . $s);
                $userModel->salt = $s;
                if ($userModel->add()) {
                    $this->assign('go',1);
                } else {
                    $this->error('写入错误！');
                }
            } else {
                $this->error($userModel->getError());
            }
            $this->assign('go',1);
        }
        $this->assign('cattree',D('Admin/Cat')->gettree());
        $cat_in_nav = M('cat')->field('cat_id,cat_name')->where('show_in_nav=1')->select();
        $this->assign('cat_in_nav',$cat_in_nav);
        $this->display();
    }

    public function salt(){
        $str = 'UasdfnpO*ISDMVMVREOuUWER,C(#$%@^)';
        return substr(str_shuffle($str),0,8);
    }
    public function yzm(){
        $verify = new \Think\Verify();
        $verify->fontSize = 40; // 验证码字体大小
        $verify->length = 4; // 验证码位数
        $verify->entry();
    }
    function logindialog(){
        die(json_encode($this->fetch(),JSON_UNESCAPED_UNICODE));
    }
    function logindialog1(){
        die(json_encode($this->fetch(),JSON_UNESCAPED_UNICODE));
    }
}