<?php
require("class.phpmailer.php");   
function smtp_mail($user_name, $sendto_email, $subject, $body, $extra_hdrs ){
    $mail = new PHPMailer();      
    $mail->IsSMTP();                // send via SMTP      
	//$mail->SMTPSecure = "tls"; 
    $mail->Host = "smtp.qq.com";   // SMTP servers      
    $mail->SMTPAuth = true;           // turn on SMTP authentication      
    $mail->Username = "2950893869";     // SMTP username  注意：普通邮件认证不需要加 @域名  这里是我的163邮箱  
    $mail->Password = "gxveffagxzwqdgjj"; // SMTP password    在这里输入邮箱的密码  客户端验证密码
    $mail->From = "2950893869@qq.com";      // 发件人邮箱      
    $mail->FromName =  "步步坚客服";  // 发件人
    $mail->SMTPSecure = "ssl"; // Gmail QQ的SMTP主機需要使用SSL連線     
    $mail->Port = 465;  //Gamil的SMTP主機的SMTP埠位為465埠。    
    
    $mail->CharSet = "UTF-8";   // 这里指定字符集！    指定UTF-8后邮件的标题和发件人等等不会乱码，如果是标题会乱码
    $mail->Encoding = "base64";      
    $mail->AddAddress($sendto_email,$user_name);  // 收件人邮箱和姓名      
    //$mail->AddReplyTo("yourmail@yourdomain.com","yourdomain.com");      
    //$mail->WordWrap = 50; // set word wrap 换行字数      
    //$mail->AddAttachment("/var/tmp/file.tar.gz"); // attachment 附件      
    //$mail->AddAttachment("/tmp/image.jpg", "new.jpg");      
    $mail->IsHTML($extra_hdrs['is_html']);  // send as HTML
    // 邮件主题      
    $mail->Subject = $subject;      
    // 邮件内容      
    $mail->Body = $body;
    //$mail->AltBody ="text/html";      
    if(!$mail->Send())      
    {
        return false;//echo "error: " . $mail->ErrorInfo;
        //exit;
    }      
    else {
        return true;
        //echo "success!";
    }
} 
?>