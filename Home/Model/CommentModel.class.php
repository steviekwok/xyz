<?php
/*
 * Created by PhpStorm.
 * User: gerrard
 * Date: 2017/6/5
 * Time: 15:14
 */

namespace Home\Model;
use Think\Model;

class CommentModel extends Model{
    protected $_validate = array(
        array('content','6,300','内容最少6字',0,'length'),
);

    protected $_auto = array(
        array('pubtime','time',1,'function'),
    );

}



