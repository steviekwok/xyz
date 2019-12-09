<?php
/**
 * Created by PhpStorm.
 * User: gerrard
 * Date: 2018/2/18
 * Time: 15:39
 */

namespace Admin\Controller;
use Think\Controller;


class PrivilegeController extends Controller
{
    public function login(){
        if(acc()){
            $this->redirect('index/index');
        }
        C('TOKEN_ON',true);
        if(IS_POST) {
            //恶意重复提交表单，打入404
            if(!M("goods")->autoCheckToken($_POST)) {
                http_response_code(404);
            }
            $memcache = new \Memcache;
            $memcache->connect('localhost', 11211);
            $name = trim($_POST['username']);
            $pwd = trim($_POST['pwd']);
            if(!empty($name) && $memcache->get($name) && $memcache->get($name) >= 2) {
                if (!check_verify(I('post.verify'))) {
                    $this->assign('err', '验证码错误');
                    $memcache->get($name) ? $memcache->set($name, $memcache->get($name) + 1) : $memcache->set($name, 1);
                    if ($memcache->get($name) > 10) {
                        $log = "-----------------------------------------\n" . date('Y/m/d H:i:s') . ", hack att" . $name . "\n-----------------------------------------";
                        error_log($log);
                    }
                    $this->assign('err_verify', 1);
                    $this->display();
                    exit;
                }
            }

            if (empty($name)) {
                $this->assign('err', '请输入用户名');
            } else if (empty($pwd)) {
                $this->assign('err', '请输入密码');
                $memcache->get($name) ? $memcache->set($name, $memcache->get($name) + 1) : $memcache->set($name, 1);
            } else if (!preg_match("/^[a-zA-Z]{6}$/", $name)) {
                $this->assign('err', '用户名或密码错误');
                $memcache->get($name) ? $memcache->set($name, $memcache->get($name) + 1) : $memcache->set($name, 1);
            } else if (!preg_match("/^[a-zA-Z0-9!@#$%^&*,.]{6,16}$/", $pwd)) {
                $this->assign('err', '用户名或密码错误');
                $memcache->get($name) ? $memcache->set($name, $memcache->get($name) + 1) : $memcache->set($name, 1);
            } else {
                $rs = M('admin')->find($name);
                if ($rs === false) {
                    exit('数据库错误');
                } elseif (!$rs) {
                    $this->assign('err', '用户名或密码错误');
                    $memcache->get($name) ? $memcache->set($name, $memcache->get($name) + 1) : $memcache->set($name, 1);
                } else {
                    if (md5($pwd . $rs['salt']) === $rs['password']) {
                        if ($memcache->get($name)) {
                            $memcache->delete($name);
                        }
                        $this->assign('go', 1);//控制 显示loading滚动条 变量
                        setcookie('name' , substr_replace($name,'',-1),time()+86400, rtrim(dirname(_PHP_FILE_),'/') . '/index.php/Admin/','',0,1);
                        setcookie('ccode' , cCode($name),time()+86400, rtrim(dirname(_PHP_FILE_),'/') . '/index.php/Admin/','',0,1);//cookie过期时间一天
                        echo "<meta http-equiv='refresh' content='3;url=../index/index'>";
                        //$this->redirect('admin/index/index', '', 2, '页面跳转中...');
                    } else {
                        $this->assign('err', '用户名或密码错误');
                        $memcache->get($name) ? $memcache->set($name, $memcache->get($name) + 1) : $memcache->set($name, 1);
                    }
                }
            }

            //同一用户名输错2次或以上，增加验证码
            if($memcache->get($name) && $memcache->get($name) >= 2) {
                $this->assign('err_verify', 1);
            }
        }
        $this->display();
    }

    public function yzm(){
        $verify = new \Think\Verify();
        $verify->fontSize = 40; // 验证码字体大小
        $verify->length = 4; // 验证码位数
        $verify->entry();
    }

}