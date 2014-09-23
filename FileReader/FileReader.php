<?php 
$type = isset($_REQUEST['type'])?$_REQUEST['type']:4;
$state = isset($_REQUEST['state'])?$_REQUEST['state']:12;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui" />
<title>在线申请</title>
    <script src="http://bdgy.ftbj.net/9958/js/jquery-1.8.3.min.js" type="text/javascript"></script>


<link rel="stylesheet" type="text/css" href="css/page.css" />
<script type="text/javascript" src="js/zepto.min.js"></script>


</head>
<body>
<header>
  <h1>在线救助</h1>
  <span class="back"></span>
  <a class="menu" href="index.php"></a>
</header>
<nav><a href="open.php" class="l now"><span class="pc">在线申请</span></a><a href="query.php" class="r"><span class="zoom">申请进度</span></a></nav>
<div class="page1">
  <form>
    <table width="100%" class="table1">
      <tr>
        <td width="80">患儿姓名：</td>
        <td><input id="name" name="name" type="text" value="请输入患儿姓名" onFocus="if(this.value=='请输入患儿姓名')this.value=''" onBlur="if(this.value=='')this.value='请输入患儿姓名'" /></td>
      </tr>
      <tr>
        <td>出生日期：</td>
        <td>
          <p class="input"><input id="birthday" name="birthday" type="date" class="date dt-picker" id="datePicker" value="" /></p>
        </td>
      </tr>
      <tr>
        <td>患儿性别：</td>
        <td>
          <p class="input">
            <select id="sex" name="sex">
              <option value="-1">请选择患儿性别</option>
              <option value="0">男</option>
              <option value="1">女</option>
            </select>
          </p>
        </td>
      </tr>
      <tr>
        <td>联系电话：</td>
        <td><input id="tel" name="tel" type="text" value="请输入联系电话" onFocus="if(this.value=='请输入联系电话')this.value=''" onBlur="if(this.value=='')this.value='请输入联系电话'" /></td>
      </tr>
      <tr>
        <td>患儿籍贯：</td>
        <td><input id="city" name="city" type="text" value="请输入患儿籍贯" onFocus="if(this.value=='请输入患儿籍贯')this.value=''" onBlur="if(this.value=='')this.value='请输入患儿籍贯'" /></td>
      </tr>
      <tr>
        <td class="vtop">病情描述：</td>
        <td><textarea id="description" name="description" onFocus="if(this.value=='请简短描述病情')this.value=''" onBlur="if(this.value=='')this.value='请简短描述病情'">请简短描述病情</textarea></td>
      </tr>
      <tr>
        <td class="vtop">患儿资料：</td>
        <td class="pics"><div id="imgshow"></div><p class="add"><form><input id="file" name="file" type="file" class="file" /></form></p></td>
      </tr>
      <tr>
        <td colspan="2" class="text">
          <p>资料需要6个月以内！</p>
          <p>资料包括：孩子的照片以及身份证明或出生证明；诊断证明或检查报告；贫困证明三级公章；</p>
          <p>证明格式：jpg png ，每张大小不超过3M</p>
        </td>
      </tr>
      <tr>
        <td colspan="2"><input id="subBtn" class="btn1 right" type="button" style="margin-left: 10px; width: 115px;" value="提交申请"><span class="btn2 right" onClick="backto()">取消</span></td>
      </tr>
    </table>
  </form>
</div>
</body>
</html>
<script type="text/javascript">
$(function(){
	
	//$(".dt-picker").datepicker();
	function readFile(){
		var file = this.files[0];	
		if (!file){ 
			//alert(111);
			return false;	
		}
		if(!file.type){
			alert("获取不到文件类型");
		}
		window.console&&console.log(file) ;
		alert(file.type) ;
		if(file.type != 'image/jpeg' &&file.type != 'image/png' &&file.type != 'image/gif' &&file.type != 'image/jpg'){
			alert("图片类型错误！");
			return false;
		}
		var images = $('#imgshow span img');
		if (images.size() > 5) {
			alert("最多只能上传6张资料图片！");
			return false;
		}
		this.value = '';
		var reader = new FileReader();
		reader.readAsDataURL(file);
		reader.onload = function(e){
			$('#imgshow').append('<span><img width="58px" height="59px" src="'+this.result+'" /><em class="rm"></em></span>');
			$('#imgshow .rm').click(function(e) {
				var that = this;
				$(this).parent().hide('fast').remove();
			});
		}
	}
	var input = document.getElementById("file");
	if ( typeof(FileReader) === 'undefined' ){
		//result.innerHTML = "抱歉，你的浏览器不支持 FileReader，请使用现代浏览器操作！";
		input.setAttribute( 'disabled','disabled' );
	} else {
		input.addEventListener('change', readFile, false);
	}

	$('#subBtn').click(function(e) {
		var name = $('#name').val();
		var birthday = $('#birthday').val();
		var sex = $('#sex').val();
		var tel = $('#tel').val();
		var city = $('#city').val();
		var description = $('#description').val();
		var images = $('#imgshow span img');
		if (name=="请输入患儿姓名") {
			alert("请输入患儿姓名");
			return false;
		}
		if (birthday=="") {
			alert("请输入出生日期！");
			return false;
		}
		if (sex=="-1") {
			alert("请选择患儿性别");
			return false;
		}
		var pattern = /(^(([0\+]\d{2,3}-)?(0\d{2,3})[- ]?)(\d{7,8})(-(\d{3,}))?$)|(^0{0,1}1[3|4|5|6|7|8|9][0-9]{9}$)/;
		if(!pattern.test(tel)){
			alert('手机号码不正确，请重输！');
			return false;
		}
		if (city=="请输入患儿籍贯") {
			alert("请输入患儿籍贯");
			return false;
		}
		if (description=="请简短描述病情") {
			alert("请简短描述病情");
			return false;
		}
		var datas = []; 
		if (images.size() > 0) {
			images.each(function(index, element) {
				datas.push($(this).attr('src'));
			})
		}else{
			alert("请上传资料图片！");
			return false;
		}
		$.ajax({
			url:'include/do_ajax.php',// 跳转到 action    
			data:{   
				dopost:"insert",
				name:name,
				type:<?echo $type;?>,
				state:<?echo $state;?>,
				birthday:birthday,
				sex:sex,
				tel:tel,
				city:city,
				description:description,
				datas : datas
			},    
			type:'post',    
			cache:false,    
			dataType:'json',  
			onload:function(){
				alert("正在提交资料，请耐心等候！");
			},
			success:function(data) {    
				if(data.res>0){
					alert("提交成功");
					location.href="ok.php";
				}else{
					alert("提交失败");
				}
			 },    
			 error : function() {      
				  alert("系统异常，请稍后重试！");    
			 }    
		});
	})
});
function backto(){
	location.href="open.php";
}
</script>

