<?php
	session_start();

	date_default_timezone_set("Asia/Taipei");

	$iMaxLogin = 5;
    $vTokenEveryMin = array();
    for ($i=0 ; $i < 10 ; $i++) {
      if ($i < $iMaxLogin) {
        $vTokenEveryMin[$i] = "past";
      }
      else {
        $vTokenEveryMin[$i] = "future";
      }
    }

	$sMd5Keyen = md5(date('Y-m-d H:').$vTokenEveryMin[substr( date('Y-m-d H:i'), -1)]);
    $sMd5Token = md5("cps".$sMd5Keyen);

  //包含需求檔案 ------------------------------------------------------------------------
	include("./class/common_lite.php");
	if($_SESSION['loginType'] > ''){
		ri_jump("index.php");
	}
	
	//宣告變數 ----------------------------------------------------------------------------
	$ODb = new run_db("mysql",3306);      //建立資料庫物件

	if(isset($_POST['loginName']) && $_POST['loginName']>'' && isset($_POST['loginPw']) && $_POST['loginPw'] && $_POST['token'] >''){
		
		$Token = $_POST['token'];

		$sMd5Keyen1 = md5(date('Y-m-d H:').'future');
		$sMd5Keyen2 = md5(date('Y-m-d H:').'past');
	
		
        if ((md5("cps".$sMd5Keyen1) != $Token) && (md5("cps".$sMd5Keyen2) != $Token)) {
			ri_jump("logout.php");
		}
		
		
		
		$LOGINNAME = $_POST['loginName'];
		$LOGINPW = base64_encode($_POST['loginPw']);
			
		$sql_dsc = "SELECT * FROM `admindata` WHERE `loginId`='".$LOGINNAME."' and `pw`='".$LOGINPW."'";
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
		if(mysql_num_rows($res)==1){
			$_SESSION['loginType'] = 'ADMIN';
			$_SESSION['loginUserName'] = '管理員';
			$_SESSION['xx_user_loginId'] = $LOGINNAME;
			$_SESSION['xx_user_pw'] = $LOGINPW;
			
			
			//刪除不必要的縮圖
			$shortImgArray = array();
			$sql_dsc = "SELECT `c_short_img` FROM `science_module_list` ";
			$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
			while($row = mysql_fetch_array($res)){
				$shortImgArray[] = $row['c_short_img'];
			}
			$sql_dsc = "SELECT `c_short_img` FROM `mathematics_module_list` ";
			$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
			while($row = mysql_fetch_array($res)){
				$shortImgArray[] = $row['c_short_img'];
			}
			$sql_dsc = "SELECT `c_short_img` FROM `read_module_list` ";
			$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
			while($row = mysql_fetch_array($res)){
				$shortImgArray[] = $row['c_short_img'];
			}
						
			if(count($shortImgArray) > 0){
				$delFile = array();
				//$dir = dirname(__FILE__).DIRECTORY_SEPARATOR."shortImg".DIRECTORY_SEPARATOR;
				$dir = "./shortImg";
				// 判斷是否為目錄
				if(is_dir($dir)){
					if ($dh = opendir($dir)) {
						//die('in');
						while (($file = readdir($dh)) !== false) {
							
							 //只過讀取出png 的檔案
							if (strpos( $file, '.png')){
								if(!in_array($file,$shortImgArray)){
									unlink("shortImg/$file");	
								}
							} 
						}
						closedir($dh);
					}
				}
			}
			
			ri_jump("index.php");
		}
		$sql_dsc = "SELECT * FROM `teacherdata` WHERE `loginId`='".$LOGINNAME."' and `pw`='".$LOGINPW."'";
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
		if(mysql_num_rows($res)==1){
			while($row = mysql_fetch_array($res)){
				$_SESSION['loginType'] = 'TEACHER';
				$_SESSION['swTeacherNum'] = $row['num'];
				$_SESSION['loginUserName'] = $row['c_name'];
				$_SESSION['xx_user_loginId'] = $LOGINNAME;
				$_SESSION['xx_user_pw'] = $LOGINPW;

				ri_jump("memberListS.php");
			}			
		}
		
		$error_msg="帳號或密碼錯誤！！";
	}
	$ODb->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>合作問題解決數位學習系統</title>
<link rel="stylesheet" href="css/admin.css" />
<script src="./js/jquery-1.10.1.min.js"></script>
<script language="javascript">
function ck_value(){
	var isGo = true;
	var file_check = true;
	var err_dsc = '';
	var ck_array =  ["loginName","loginPw"];
	var err_array =  ["請輸入登入帳號!!","請輸入登入密碼!!"];
	var type_array =  ["text","text"];

	for(var x=0;x< ck_array.length;x++){
		switch(type_array[x]){
			case "text":
			case "file":
				if($('#'+ck_array[x]).val() ==''){
				err_dsc = err_dsc + err_array[x] +'\r\n';
				isGo = false;
				}
			break;
			case "number":
				if(!$.isNumeric($('#'+ck_array[x]).val()) ){
					err_dsc = err_dsc + err_array[x] +'\r\n';
					isGo = false;				
				}		
			break;
		}
	}
	
	
	if(isGo){
		$('#form').submit();
	}
	
	if(err_dsc !=''){
		alert(err_dsc);
	}
}

$( document ).ready(function() {
    <?php if($error_msg>''){echo 'alert("'.$error_msg.'")';}?>
});


</script>
</head>
<body id="login">
<h1><img src="../images/login_title.png" alt="合作問題解決數位學系系統" /></h1>
<form method="POST" action="login.php" id="form">
	<table>
	<tr>
		<td>帳　號</td>
		<td><input type="text" name="loginName" id="loginName" placeholder="請輸入帳號"></td>
	</tr>
	<tr>
		<td>密　碼</td>
		<td><input type="password" name="loginPw" id="loginPw" placeholder="請輸入密碼"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="right"><input type="reset" value="清除"><input type="button" value="送出" onclick="ck_value()"></td>
	</tr>	
	</table>
	<input type='hidden' name='token'  id="token" value="<?php echo $sMd5Token ?>">
</form>
</body>
</html>
