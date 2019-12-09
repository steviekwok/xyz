<?php
/**
 * Created by PhpStorm.
 * User: gerrard
 * Date: 2017/7/5
 * Time: 16:47
 */
function clearCache(){
    deldir(TEMP_PATH);

}

function deldir($dir){
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {
            $fullpath = $dir . "/" . $file;
            if (!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }
}

/**
 * 检测用户是否登录
 */

function acc() {
    if(!isset($_COOKIE['name']) || !isset($_COOKIE['ccode'])){
        return false;
    }
    return $_COOKIE['ccode'] === cCode($_COOKIE['name'].'Q');
}

/**
 * 加密用户名
 * @param str $name 用户登陆时输入的用户名
 * @return str md5(用户名+salt)=>md5码
 */

function cCode($name) {
    return md5($name . '|' . C('salt'));
}