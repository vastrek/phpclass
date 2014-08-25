<?php
/*
    防止sql注入,防止xss攻击，放在php第一行
        @author:yaofuyuan
        @date:2012-11-15 14:23:11
*/
function safeInput(){
       safeArray($_GET);
       safeArray($_POST);
       safeArray($_COOKIE);
}
/*
    处理数组中的sql注入字符和js代码
        @author:yaofuyuan
        @date:2012-11-15 14:23:11
*/
function safeArray(&$arr){
    foreach($arr as $k => $v){
                if(is_array($v)){
                     $arr[$k]=safeArray($v);
                      continue;
                }
        $arr[$k]=str_replace(array('<','>'),array('&lt;','&gt;'),addslashes($v));
    }    
}

function safeInsert($table,$arr){
	safeArray($arr);
	$sql = 
	$col="";
	$val="";
	foreach ($arr as $k => $v){
		$col.=$k.",";
		$val.= "'".$v."',";
	}
	$col=rtrim($col,",");
	$val=rtrim($val,",");
	return query("insert into $table ($col) values($val)");
}

function safeUpdate($table,$arr,$where){
	safeArray($arr);	
	$sql = "";
	foreach ($aArray as $k => $v){
		$sql .= $k . "='".$v."',";
	}
	$sql="UPDATE $aTableName SET ".rtrim($sql,",");
	if ( $where ){
    	$sql .= " WHERE ".$where;
	}
	return query($sql);
}

/*
	后台记录信息
	$syslogFile在config.php定义
*/
function systemLog($msg){
	global $syslogFile;
	return error_log(date("[Y-m-d H:i:s]")."\t".$_SERVER['REMOTE_ADDR']."\t".$msg."\n",3,$syslogFile);
}
/*
	获取数据库连接
*/
function getConnection(){
  $conn=mysql_connect(DB_HOST,DB_USER,DB_PASSWORD) or die(mysql_error());
	mysql_select_db(DB_DATABASE) or die(mysql_error());
	mysql_query("SET NAMES 'utf8'");	
	return $conn;
}
/*
	执行sql语句，执行出错则退出程序
	默认使用$conn做数据库连接
*/
function query($sql){
	$res=mysql_query($sql) or systemLog($sql."\t".mysql_error()); 
	return $res;
}

function startTransaction(){
	query("START TRANSACTION");	
}

function commitTransaction(){
	query("COMMIT");	
}

function rollback(){
	query("ROLLBACK");	
}

/*
	查询某表的所有记录
*/
function getAllRows($table){
	$res=query("select * from $table"); 
	$result=array();
	while($row=mysql_fetch_assoc($res)){
		$result[]=$row;
	}
	return $result; 
}


/*
	分页查询车队
*/
function getQueueByPage($page){
	$startPos=($page-1)*PAGE_SIZE;
	$res=query("select id,weibo_screen_name,weibo_icon,car_id,prize_id from car_queue order by id desc limit $startPos,".PAGE_SIZE); 
	$result=array();
	while($row=mysql_fetch_assoc($res)){
		$row['short_name']=getShortName($row['weibo_screen_name']);
		$result[]=$row;
	}
	return $result; 
}

//是否已经赢得过奖品
function isWined($weiboUid){
	systemLog("select count(*) from car_queue where weibo_uid='$weiboUid' and prize_id>0");
	if($res=query("select count(*) from car_queue where weibo_uid='$weiboUid' and prize_id>0")){
		if($row=mysql_fetch_array($res)){
			if($row[0]==0){
				return false;
			}else{
				return true;
			}
		}
	}
	return true;
}


//是否需要填写用户信息
function isNeedWinnerInfo($weiboUid){
	if($res=query("select win_uuid from car_queue where weibo_uid='$weiboUid' and win_uuid!='' and win_uuid not in (select win_uuid from winner)")){
		if($row=mysql_fetch_array($res)){
			return $row[0];
		}else{
			return "false";
		}
	}
	return "false";
}

function isInCity($province_id,$city_id){
	$res=query("select count(*) from ticket_city where province_id='$province_id' or city_id='$city_id'"); 
	if($row=mysql_fetch_array($res)){
		if($row[0]==0){
			return false;
		}else{
			return true;
		}
	}
}

//是否需要填写用户信息
function getPrizes(){
	global $prizes;
	//缓存
	if(is_array($prizes)&& count($prizes) > 0){
		return 	$prizes;
	}
	if($res=query("select * from prize order by id asc")){
		while($row=mysql_fetch_assoc($res)){
			$prizes[]=$row;
		}
	}
	return $prizes;
}

function verifyMobile($mobile){  
	//11位手机号   
	return preg_match("/^1[3|5|8]\d{9}$/",$mobile);
} 

function jumpTo($url){
	echo "<script> top.location='$url';</script>";
}
function uuid($prefix = ''){
     $chars = md5(uniqid(mt_rand(), true));
     $uuid= substr($chars,0,8) . '-';
     $uuid.= substr($chars,8,4) . '-';
     $uuid.= substr($chars,12,4) . '-';
     $uuid.= substr($chars,16,4) . '-';
     $uuid.= substr($chars,20,12);
     return $prefix . $uuid;
} 

function isWin($weiboUid,$province_id,$city_id){
	global $prizes;
	//return "false";//不让中奖
	if(isWined($weiboUid)){
		systemLog("$weiboUid already wined.");
		return array("status"=> false,"winUuid"=>"","prizeId"=> 0);	
	}
	$prizes=getPrizes();
	$winUuid=0;
	foreach($prizes as $prize){
		if($prize['left_num']==0){
			continue;
		}
		$prizeId=$prize['id'];
		if($prizeId==1){
			//判断若门票时必须是上海周边城市
			if(!isInCity($province_id,$city_id)){
				continue;
			}
		}
		$percent=$prize['percent']*RAND_MAX;
		//产生一个1到100000的随机数
		$randKey = mt_rand(1,RAND_MAX);	
		//当随机数小于设定的几率时认为是中奖
		systemLog("$randKey <= $percent");
		if($randKey <= $percent){	
			$winUuid=uuid();
			//奖品数减一
			query("update prize set left_num=left_num-1 where id=$prizeId");
			if(mysql_affected_rows() ==1){
				return array("status"=> true,"winUuid"=> $winUuid,"prizeId"=> $prize['id'],"prize"=> $prize['name']);
			}else{
				return array("status"=> false,"winUuid"=>"","prizeId"=> 0);	
			}
		}
	}
	return array("status"=> false,"winUuid"=>"","prizeId"=> 0);	
}




function getShortName($longName){
	//英语
	if(mb_strlen($longName,"UTF-8")==strlen($longName)){
		//中文yifangyou
		if(mb_strlen($longName,"UTF-8")>9){
				return mb_substr($longName,0,8,"UTF-8")."...";
		}else{
				return $longName;
		}
	}else
	if(mb_strlen($longName,"UTF-8")>5){
			//中文
			return mb_substr($longName,0,4,"UTF-8")."...";
	}else{
			return $longName;
	}
}

function getGeo($ip){
	$ipstr=sprintf("%u", ip2long($ip));
	if($res=query("select province_id,city_id from ip_db where start_ip<=$ipstr and end_ip>=$ipstr")){
		if($row=mysql_fetch_assoc($res)){
			if($row["province_id"]==0){
				$row["province_id"]=-1;
			}
			if($row["city_id"]==0){
				$row["city_id"]=-1;
			}
			return array($row["province_id"],$row["city_id"]);
		}
	}
	return array(-1,-1);
}

function getIp(){
	$ip=false;
	if(!empty($_SERVER["HTTP_CLIENT_IP"])){
  	$ip = $_SERVER["HTTP_CLIENT_IP"];
	}
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
  	$ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
  	if($ip){
   		array_unshift($ips, $ip); $ip = FALSE;
  	}
  	for($i = 0; $i < count($ips); $i++){
   		if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])){
    		$ip = $ips[$i];
    		break;
   		}
  	}
	}
	return($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

/*****************************************************未来十年************************************************/

/**保存预约信息*/
function savebookingInfo($infoarray,$user,$isfollow){
	$sql = "INSERT INTO bookingInfor(name,phone,province,city,createdate,w_name,w_id,mobile_type,ip) VALUES ('".
			$infoarray['name']."','".$infoarray['phone']."','".$infoarray['province']."','".$infoarray['city']."','".date("Y-m-d H:i:s")."','".
			$user['screen_name']."','".$user['id']."','".$mobile_type."','".getIp()."');";
	return query($sql);
}

/*分享*/
function saveShareInfo($user,$content,$isfollow){
	$sql = "insert into shareInfor(w_name,w_id,content,isfollow,ip,createdate) values('".$user['screen_name']."','".$user['id']."','".$content."','".$isfollow."','".getIp()."','".date("Y-m-d H:i:s")."')";
	return query($sql);
}

/*微博用户信息*/
function saveWeiboUser($w_name,$content,$headimg,$reportlink,$commentlink){
	$sql = "insert into weiboUser(w_name,content,headimg,reportlink,commentlink,createdate) values('".$w_name."','".$content."','".$headimg."','".$reportlink."','".$commentlink."','".date("Y-m-d H:i:s")."')";
	return query($sql);
}

/*删除微博用户信息*/
function deleteWeiboUser($id){
	$sql = "delete from weiboUser where id=".$id;

	return query($sql);
}

/**获取全部信息*/
function getAllweiboUser(){
	$sql = "select * from weiboUser order by createdate desc";
	$result = query($sql);
	while($row = mysql_fetch_assoc($result)) {
        $data[] = $row;
    }
	return $data;
}
/********************************第二期 2012.12.17**************************************************************************/

/**保存预约信息*/
function savebooking($user,$sendmail){
	$sql = "INSERT INTO booking(w_name,w_id,sendmail,ip,senddate) VALUES ('".
			$user['screen_name']."','".$user['id']."','".$sendmail."','".getIp()."','".date("Y-m-d H:i:s")."');";
	return query($sql);
}

function sendMail($user){
	require("class.phpmailer.php");
	if(savebooking($user,SENDMAIL)){
		$mailObj = new PHPMailer();
		//$mailConfig = FLEA::getAppInf('EmailConfig');
		$mailObj->IsSMTP();
		$mailObj->IsHTML(true);
		$mailObj->Timeout=10;
		$mailObj->Host = 'mail.prdmail.phluency.com';//$mailConfig['Host'];
		$mailObj->SMTPAuth = true;
		$mailObj->Username = 'mbcl_as@prdmail.phluency.com';//$mailConfig['Username']; // SMTP username
		$mailObj->Password = 'mbcl_as#@!';//$mailConfig['Password']; // SMTP password
		$mailObj->FromName = "微博昵称：".$user['screen_name'];//$mailConfig['FromName'];
		$mailObj->From = 'mbcl_as@prdmail.phluency.com';//$mailConfig['From'];
		$mailObj->CharSet = 'utf-8';
		$mailObj->Body = header('Content-type:text/html; charset=utf-8');
		$mailObj->AltBody ="html";
		$mailObj->AddAddress(SENDMAIL);
		$mailObj->Subject = $user['id'];
		$mailObj->Body .= "微博ID:".$user['id']."<br />微博昵称：".$user['screen_name'];
		$sta = $mailObj->Send();
		if($mailObj->error_count<=0){
			echo "success";
		}else{
			echo "senderror";	
		}
		return $sta;	
	}else{
		echo "saveerror";	
	}
	
}

/*++++++++++++++++++++++++++++++++++++++++++++++++++=*/


/**
 * 图片加水印（适用于png/jpg/gif格式）
 *
 * @author flynetcn
 *
 * @param $srcImg 原图片
 * @param $waterImg 水印图片
 * @param $savepath 保存路径
 * @param $savename 保存名字
 * @param $positon 水印位置
 * 1:顶部居左, 2:顶部居右, 3:居中, 4:底部局左, 5:底部居右
 * @param $alpha 透明度 -- 0:完全透明, 100:完全不透明
 *
 * @return 成功 -- 加水印后的新图片地址
 *          失败 -- -1:原文件不存在, -2:水印图片不存在, -3:原文件图像对象建立失败
 *          -4:水印文件图像对象建立失败 -5:加水印后的新图片保存失败
 */
function img_water_mark($srcImg, $waterImg, $savepath=null, $savename=null, $positon=6, $alpha=100)
{
    $temp = pathinfo($srcImg);
    $name = $temp['basename'];
    $path = $temp['dirname'];
	
    $exte = $temp['extension'];
    $savename = $savename ? $savename : $name;
    $savepath = $savepath ? $savepath : $path;
    $savefile = $savepath .'/'. $savename;
    $srcinfo = @getimagesize($srcImg);
	
    if (!$srcinfo) {
        return -1; //原文件不存在
    }
    $waterinfo = @getimagesize($waterImg);
    if (!$waterinfo) {
        return -2; //水印图片不存在
    }
    $srcImgObj = image_create_from_ext($srcImg);
    if (!$srcImgObj) {
        return -3; //原文件图像对象建立失败
    }
    $waterImgObj = image_create_from_ext($waterImg);
    if (!$waterImgObj) {
        return -4; //水印文件图像对象建立失败
    }
    switch ($positon) {
    //1顶部居左
    case 1: $x=$y=0; break;
    //2顶部居右
    case 2: $x = $srcinfo[0]-$waterinfo[0]; $y = 0; break;
    //3居中
    case 3: $x = ($srcinfo[0]-$waterinfo[0])/2; $y = ($srcinfo[1]-$waterinfo[1])/2; break;
    //4底部居左
    case 4: $x = 0; $y = $srcinfo[1]-$waterinfo[1]; break;
    //5底部居右
    case 5: $x = $srcinfo[0]-$waterinfo[0]; $y = $srcinfo[1]-$waterinfo[1]; break;
	//自定义
	case 6: $x = 80; $y = $srcinfo[1]-$waterinfo[1]; break;
	case 7: $x = 230; $y = 201; break;
	case 8: $x = 30; $y = 49; break;
    default: $x=$y=0;
    }
    imagecopymerge($srcImgObj, $waterImgObj, $x, $y, 0, 0, $waterinfo[0], $waterinfo[1], $alpha);
    switch ($srcinfo[2]) {
    case 1: imagegif($srcImgObj, $savefile); break;
    case 2: imagejpeg($srcImgObj, $savefile); break;
    case 3: imagepng($srcImgObj, $savefile); break;
    default: return -5; //保存失败
    }
    imagedestroy($srcImgObj);
    imagedestroy($waterImgObj);
    return $savefile;
}

function img_water_mark2($srcImg, $waterImg, $savepath=null, $savename=null, $pos)
{
    $temp = pathinfo($srcImg);
    $name = $temp['basename'];
    $path = $temp['dirname'];
	
    $exte = $temp['extension'];
    $savename = $savename ? $savename : $name;
    $savepath = $savepath ? $savepath : $path;
    $savefile = $savepath .'/'. $savename;
    $srcinfo = @getimagesize($srcImg);
	
    if (!$srcinfo) {
        return -1; //原文件不存在
    }
    $waterinfo = @getimagesize($waterImg);
    if (!$waterinfo) {
        return -2; //水印图片不存在
    }
    
    $toWidth=$waterinfo[0];
    $toHeight=$waterinfo[1];
    //创建大图
    $srcImgObj= imagecreatetruecolor($toWidth, $toHeight);
    
    //设置背景色
    $white = imagecolorallocate($srcImgObj, 255, 255, 255); 
		imagefilledrectangle($srcImgObj, 0, 0, $toWidth, $toHeight, $white); 


    $inputImage = image_create_from_ext($srcImg);
    $waterImgObj = image_create_from_ext($waterImg);
    //$x = ($waterinfo[0]-$srcinfo[0])/2; 
    //$y = ($waterinfo[1]-$srcinfo[1])/2;
	imagecopyresampled($srcImgObj,$inputImage,$pos['x'], $pos['y'], 0, 0,  $srcinfo[0], $srcinfo[1], $srcinfo[0], $srcinfo[1]);
   	imagecopyresampled($srcImgObj,$waterImgObj,0, 0, 0, 0,  $toWidth, $toHeight, $toWidth, $toHeight);
    
    switch ($srcinfo[2]) {
    case 1: imagegif($srcImgObj, $savefile); break;
    case 2: imagejpeg($srcImgObj, $savefile); break;
    case 3: imagepng($srcImgObj, $savefile); break;
    default: return -5; //保存失败
    }
    imagedestroy($srcImgObj);
    imagedestroy($waterImgObj);
    imagedestroy($inputImage);
    return $savefile;
}
/*
 处理PNG24水印图片
 @param $target String 图片路径
 @param $watermark
*/
function watermark($target, $watermark,$pos) {
	$attachinfo = array();
	$attachinfo = getimagesize($target);
	$mark = imagecreatefrompng($watermark);
	$mark_w = imageSX($mark);
	$mark_h = imageSY($mark);
	switch ($attachinfo['mime']) {
	   case 'image/jpeg':
		$dest = imagecreatefromjpeg($target);
		break;
	   case 'image/gif':
		$dest = imagecreatefromgif($target);
		break;
	   case 'image/png':
		$dest = imagecreatefrompng($target);
		break;
	}
	$color = imagecolorAllocate($dest,255,255,255);   //分配一个灰色
	imagefill($dest,0,0,$color); 
	
	imagecopymerge_alpha($dest, $mark, $pos['x'], $pos['y'], 0, 0, $mark_w, $mark_h, 0);
	switch ($attachinfo['mime']) {
	   case 'image/jpeg':
		imagejpeg($dest, $target);
		break;
	   case 'image/gif':
		imagegif($dest, $target);
		break;
	   case 'image/png':
		imagepng($dest, $target);
		break;
	}
	imagedestroy($dest);
	imagedestroy($mark);
}

function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
	$opacity=$pct;
	 //getting the watermark width
	$w = imagesx($src_im);
	 //getting the watermark height
	$h = imagesy($src_im);
	 //creating a cut resource
	$cut = imagecreatetruecolor($src_w, $src_h);
	 //copying that section of the background to the cut
	//echo $cut."-".$dst_im."-".$dst_x."-".$dst_y."-".$src_w."-".$src_h ;
	imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
	 //inverting the opacity
	$opacity = 100 - $opacity;
	 //placing the watermark now
	imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
	imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
}

function image_create_from_ext($imgfile)
{
    $info = getimagesize($imgfile);
    $im = null;
    switch ($info[2]) {
    case 1: $im=imagecreatefromgif($imgfile); break;
    case 2: $im=imagecreatefromjpeg($imgfile); break;
    case 3: $im=imagecreatefrompng($imgfile); break;
    }
    return $im;
}
?>