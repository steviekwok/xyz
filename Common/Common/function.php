<?php
function importP($string){
    import('Class.' . $string, APP_PATH);
}

function jm($a){
    return md5($a);
}

function che(){
    return jm(cookie('username').cookie('userid').C('COO_KIE')) === cookie('key');
}

/**
 * 分页
 * @param $count 总数据条数
 * @param int $pagesize 每页查询条数
 * @return \Think\Page
 */
function getPage($count,$pagesize=10){
        $Page = new \Think\Page($count, $pagesize);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        $Page->listRows = $pagesize;
        $Page->lastSuffix = false;//不设置最后显示总页数
        $Page->setConfig('header','<span class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</span>');
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $show = $Page->show();// 分页显示输出
        return $show;
}

/**
 * home分页
 * @param $count 总数据条数
 * @param int $pagesize 每页查询条数
 * @return \Think\Page
 */
function getHomePage($count,$pagesize=10){
    $Page = new \Think\Page($count, $pagesize);// 实例化分页类 传入总记录数和每页显示的记录数
    $Page->setConfig('prev','<');
    $Page->setConfig('next','>');
    $Page->setConfig('first',"|<");
    $Page->setConfig('last','>|');
    $Page->listRows = $pagesize;
    $Page->lastSuffix = false;
    $Page->rollPage = 3;
    //$Page->setConfig('header','<span class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</span>');
    $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
    $show = $Page->show();// 分页显示输出
    return $show;
}
/**
 * 创建一个JSON格式的数据
 *
 * @access  public
 * @param   string      $content
 * @param   integer     $error
 * @param   string      $message
 * @param   array       $append
 * @return  void
 */
function make_json_response($content='', $error="0", $message='', $append=array())
{
    //import("Class.cls_json");
    include_once('./Class/cls_json.class.php');
    $json = new JSON;

        $res = array('error' => $error, 'message' => $message, 'content' => $content);

        if (!empty($append))
        {
            foreach ($append AS $key => $val)
            {
                $res[$key] = $val;
            }
        }

        $val = $json->encode($res);

        exit($val);
    }

/**
 * 获取来访者的真实IP
 *
 */
function getRealIp() {
    static $realip = null;
    if($realip !== null) {
        return $realip;
    }

    if(getenv('REMOTE_ADDR')) {
        $realip = getenv('REMOTE_ADDR');
    } else if(getenv('HTTP_CLIENT_IP')) {
        $realip = getenv('HTTP_CLIENT_IP');
    } else if (getenv('HTTP_X_FROWARD_FOR')) {
        $realip = getenv('HTTP_X_FROWARD_FOR');
    }

    return $realip;
}

/**
 * 验证码检查
 */
function check_verify($code, $id = ""){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}
