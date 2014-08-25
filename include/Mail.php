<?php
require(LIB."/Mail/class.phpmailer.php");
class Model_Mail extends PHPMailer 
{
    function __construct()
    {
    	parent::IsSMTP();
    	//$mailConfig = FLEA::getAppInf('EmailConfig');
    	$this->IsHTML(true);
    	$this->Timeout=10;
    	$this->Host = 'mail.prdmail.phluency.com';//$mailConfig['Host'];
    	$this->SMTPAuth = true;
    	$this->Username = 'mbcl_as@prdmail.phluency.com';//$mailConfig['Username']; // SMTP username
		$this->Password = 'mbcl_as#@!';//$mailConfig['Password']; // SMTP password
		$this->FromName = '测试';//$mailConfig['FromName'];
		$this->From = 'mbcl_as@prdmail.phluency.com';//$mailConfig['From'];
		$this->CharSet = 'utf-8';
		$this->Body = header('Content-type:text/html; charset=utf-8');
		$this->AltBody ="html";
    }
    function sendMail($toMail,$toName,$subject,$mailBody)
    {
    	$this->AddAddress($toMail,$toName);
    	$this->Subject = $subject;
    	$this->Body .= $mailBody;
    	return $this->Send();
    }
    function dealerActive($toMail,$toName)
    {
    	$subject = 'Active your account';
    }
    
    function sendMail2($toMail,$ccMail,$subject,$mailBody)
    {
    	if(is_array($toMail))
    	{
    		for($i=0;$i<count($toMail);$i++)
    		{
    			$this->AddAddress($toMail[$i][0],$toMail[$i][1]);	
    		}
    	}
    	
    	if(is_array($ccMail))
    	{
    		for($j=0;$j<count($ccMail);$j++)
    		{
    			$this->AddCC($ccMail[$j][0],$ccMail[$j][1]);	
    		}
    	}
    	
    	$this->Subject = $subject;
    	$this->Body .= $mailBody;
    	return $this->Send();
    }

	function sendRssMail($toMail,$toName,$mailBody=''){
		$this->AddAddress($toMail,$toName);
    	$this->Subject = 'test';
    	$this->Body .= $mailBody;
    	return $this->Send();
	}
}
?>