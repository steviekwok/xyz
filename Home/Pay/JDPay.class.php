<?php
/**
 * Created by PhpStorm.
 * User: gerrard
 * Date: 2017/6/5
 * Time: 8:32
 */

namespace Home\Pay;


class JDPay{
    //v_amount v_moneytype v_old v_mid v_url key
    protected  $v_amount;//总金额
    protected  $v_oid;//订单编号

    protected  $v_moneytype = 'CNY';//币种

    protected  $v_mid;//商户编号
    protected  $v_url;//回调URL
    protected  $v_key;

    public function __construct($v_oid , $v_amount){
        $this->v_oid = $v_oid;
        $this->v_amount = $v_amount;

        $this->v_mid = C('V_MID');
        $this->v_url = C('V_URL');
        $this->v_key = C('V_KEY');
    }

    public function form(){
        $form = '<form action="https://pay3.chinabank.com.cn/PayGate" method="post">
                 <input type=hidden name=v_mid value="%s">
                 <input type=hidden name=v_oid value="%s">
                 <input type=hidden name=v_amount value="%s">
                 <input type=hidden name=v_moneytype value="CNY">
                 <input type=hidden name=v_url value="%s">
                 <input type=hidden name=v_md5info value="%s">
                 <input type="submit" value="支付">
                  </form> ';
        return sprintf($form , $this->v_mid , $this->v_oid , $this->v_amount , $this->v_url , $this->sign());
    }

    public function sign(){
        $sign = $this->v_amount . $this->v_moneytype . $this->v_oid . $this->v_mid . $this->v_url . $this->v_key;
        return strtoupper(md5($sign));
    }

}