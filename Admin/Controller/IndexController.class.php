<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        if(!acc()){
            http_response_code(404);
        }
        $this->display();
    }

    public function orderlist(){
        if(!acc()){
            http_response_code(404);
        }
        $this->assign('orderlist',M('ordinfo')->select());
        $this->display();
    }

    public function user(){
        if(!acc()){
            http_response_code(404);
        }
        $this->assign('user',M('user')->field('user_id,username,email,pubtime')->select());
        $this->display();
    }

    public function clearCache(){
        if(!acc()){
            http_response_code(404);
        }
        clearCache();
        $links = array(array('href'=>'javascript:history.go(-1)','text'=>'上一页'),array('href'=>'main','text'=>'主页'));
        $this->message('页面缓存已清除',0,$links);
    }

    public function logOut(){
        if(!acc()){
            http_response_code(404);
        }
        clearCache();
        setcookie('name',null,0,'/admin/');
        setcookie('ccode',null,0,'/admin/');
        header("Location:" . dirname(_PHP_FILE_) . "/admin/privilege/login");
    }

    /**
     * 系统提示信息
     *
     * @access      public
     * @param       string      msg_detail      消息内容
     * @param       int         msg_type        消息类型， 0消息，1错误，2询问
     * @param       array       links           可选的链接
     * @param       boolen      $auto_redirect  是否需要自动跳转
     * @return      void
     */
    public function message($msg_detail, $msg_type = 0, $links = array(), $auto_redirect = true){
        if (count($links) == 0)
        {
            $links[0]['text'] = '上一页';
            $links[0]['href'] = 'javascript:history.go(-1)';
            $links[1]['text'] = '主页';
            $links[1]['href'] = '../Index/main.html';
        }
        $this->assign('msg_detail',$msg_detail);
        $this->assign('msg_type',$msg_type);
        $this->assign('auto_redirect',$auto_redirect);
        $this->assign('links',$links);
        $this->assign('default_url',$links[0]['href']);
        $this->display('Index:message');
    }

}