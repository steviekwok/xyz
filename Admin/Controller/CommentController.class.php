<?php
/**
 * Created by PhpStorm.
 * User: gerrard
 * Date: 2017/8/17
 * Time: 18:59
 */

namespace Admin\Controller;
use Think\Controller;


class CommentController extends Controller
{
    public function commentlist()
    {
        if(!acc()){
            http_response_code(404);
        }
        $p = I('p') ? I('p') : 1;
        $com = M('comment')->field('comment.*,goods.goods_name')->where('parent_id=0')->join('goods ON comment.goods_id = goods.goods_id')
            ->page($p . ',20')->order('comment_id desc')->cache(true,360)->select();
        $count = M('comment')->where('parent_id=0')->cache(true,360)->count();
        $status = array('隐藏', '显示');
        $this->assign('page', getPage($count, 20));// 赋值分页输出
        $this->assign('comment', $com);
        $this->assign('status', $status);
        $this->display();
    }

    public function del()
    {
        if(!acc()){
            http_response_code(404);
        }
        if (M('comment')->delete(I('get.comment_id'))) {
            clearCache();
            header("location:" . $_SERVER['HTTP_REFERER']);
        } else {
            $this->error('删除失败！', '', 2);
        }
    }

    //‘查看详情’窗口
    public function reply()
    {
        if(!acc()){
            http_response_code(404);
        }
        $id = I('get.comment_id');
        $msg = M('comment')->field('comment.*,goods.goods_name')->join('goods ON comment.goods_id = goods.goods_id')->find($id);
        $reply = M('comment')->field('comment.*,goods.goods_name')->join('goods ON comment.goods_id = goods.goods_id')
            ->where('parent_id=' . $id)->find();
        $this->assign('msg', $msg);
        if ($reply) {
            $this->assign('reply', $reply);
        }
        $this->assign('send_fail', !empty($_REQUEST['send_ok']));
        $this->display('commentinfo');
    }

    //管理员回复评论
    public function action()
    {
        if(!acc()){
            http_response_code(404);
        }
        if (IS_POST) {
            $msg = M('comment');
            $id = I('post.comment_id');
            if($msg->where('parent_id='.$id)->find()){
                /* 更新回复的内容 */
                if (!$msg->create()) {//第二参数，1＝新增，2－更新，因存在主键comment_id所以这里会自动选择（默认）2(后来不想管理员也限字数，不用自动完成
                   $this->error($msg->getError(), '', 2);
                }
                unset($msg->comment_id);
                $msg->ip_address = sprintf('%u', ip2long(getRealIp()));
                $msg->status = 1;
                if ($msg->where('parent_id='.$id)->save()===false) {
                    $this->error('写入错误');
                }
            }else {
                /* 插入回复的评论内容 *///if (!$msg->create($_POST, 1)) {
                if(!$msg->create()){
                    $this->error($msg->getError(), '', 2);
                }
                $msg->parent_id = $msg->comment_id;
                $msg->comment_id = '';
                $msg->pubtime = time();
                $msg->ip_address = sprintf('%u', ip2long(getRealIp()));
                $msg->status = 1;
                if (!$msg->add()) {
                    $this->error('写入错误');
                }
                /* 更新当前的评论状态为“允许显示”此条评论 */
                $msg->where('comment_id='.$id)->setField('status',1);
            }
            /* 邮件通知处理流程 */
            if (!empty($_POST['send_email_notice']) or isset($_POST['remail']))
            {
                //获取邮件中的必要内容
                $comment_info = M('comment')->field('username,email,content')->find($id);
                /* 设置留言回复模板所需要的内容信息 */
                $template = M('mail_templates')->field('template_subject,template_content,is_html')->where("template_code='recomment'")->find();

                //$this->assign('user_name',   $comment_info['username']);
                //$this->assign('recomment', $_POST['content']);
                //$this->assign('comment', $comment_info['content']);
                //$this->assign('shop_name',   "<a href='http://shop.cn/home/index'>" . C('shop_name') . '</a>');
                //$this->assign('send_date',   date('Y-m-d'));

                //$content = $this->show($template['template_content']);//show只会输出渲染后内容，不能赋值
                $content = str_replace(
                    array('{$user_name}', '{$recomment}', '{$comment}', '{$shop_name}', '{$send_date}'),
                    array($comment_info['username'], $_POST['content'], $comment_info['content'], "<a href='http://shop.cn/home/index'>" . C('shop_name') . '</a>', date('Y-m-d')),
                    $template['template_content']);

                $path = realpath('./PHPMailer/mail.php');
                require_once($path);
                // 发送邮件
                if(smtp_mail($comment_info['username'],$comment_info['email'], $template['template_subject'],$content, array('is_html'=>$template['is_html']))){
                    //发送成功
                    $send_ok = 0;
                }else{
                    //echo 0;
                    $send_ok = 1;
                }
            }
            clearCache();
            $this->redirect('admin/comment/reply/comment_id/'. $id . '/send_ok/' . $send_ok);
        }
    }

    //允许、禁止评论显示
    public function check()
    {
        if(!acc()){
            http_response_code(404);
        }
        $msg = M('comment');
        $id = I('get.comment_id');
        $status = $msg->where('comment_id='.$id)->getField('status');
        if($status){
            $msg->where('comment_id='.$id)->setField('status',0);
        }else{
            $msg->where('comment_id='.$id)->setField('status',1);
        }
        clearCache();
        $this->redirect('admin/comment/reply/comment_id/'. $id);
    }
}