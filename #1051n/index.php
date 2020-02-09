<!-- Global site tag (gtag.js) - Google Analytics -->
 <script async src="https://www.googletagmanager.com/gtag/js?id=UA-108376301-1"></script>
 <script>
  window.dataLayer = window.dataLayer || [];

  function gtag() {
   dataLayer.push(arguments);
  }
  gtag('js', new Date());
  gtag('config', 'UA-108376301-1');
 </script>
<?php
	session_start();
  echo $_SESSION['stuid'];
  //包含需求檔案 ------------------------------------------------------------------------
	include("./bcontroller/class/common_lite.php");
	//宣告變數 ----------------------------------------------------------------------------
	$ODb = new run_db("mysql",3306);      //建立資料庫物件
	$isStudent=false;
	if($_SESSION['loginType'] == ''){
		$ODb->close();
		ri_jump("logout.php");
	}else{
		switch($_SESSION['loginType']){
			case "ADMIN":
				$whereDsc = "";
				$recordURL = "record_list";
			break;
			case "TEACHER":
			$_SESSION['teacher_list_pg']='';
			$whereDsc = " where `create_user`='".$_SESSION['swTeacherNum']."' and `create_user_type`='TEACHER' and `is_open`='1' ";
			$recordURL = "record_t";
			break;
			case "STUDENT":
			$recordURL = "record_s";
			//1.找出練習題
			$sql_dsc = "select `num` from `main_data` where `is_practice`='1' ";
			$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
			while($row = mysql_fetch_array($res)){
			$is_practice_array[] = $row['num'];
			}
			
			//2.找出老師設定的開放時間內可以做的題目
			$nowdate =  date("Y-m-d H:i",time());
			$sql_dsc = "select * from `test_time_teacher` where `teacherdataNum`='".$_SESSION['teacherdataNum']."' and `grade_dsc`='".$_SESSION['grade_dsc']."' and `class_dsc`='".$_SESSION['class_dsc']."' and `begin_time`<='".$nowdate."' and `end_time`>='".$nowdate."' order by f_num desc";
			$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
			while($row = mysql_fetch_array($res)){
				if(!isset($fIDArray[$row['f_num']])){
					$fIDArray[$row['f_num']] = array();
				}
				$tempData = array(
					'main_data_num'=>$row['main_data_num'],
					'begin_time'=>$row['begin_time'],
					'end_time'=>$row['end_time']
				);
				$fIDArray[$row['f_num']][]=$tempData;
			}
 //     print_r($tempData);
			//找出開放時間內，學生還沒有做過得題目
			if(is_array($fIDArray)){
				$fnum='';
				$mnum='';
				foreach($fIDArray as $key=> $value0){
					foreach($value0 as $value){
						$sql_dsc = "
						select * from `opt_record` 
						where 
						`student_user`='".$_SESSION['swStudentNum']."' 
						and `teacher_user`='".$_SESSION['teacherdataNum']."' 
						and `main_data_num`='".$value['main_data_num']."' 
						and `test_begin_time` between '".$value['begin_time']."' and '".$value['end_time']."' ";
						$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
						if(mysql_num_rows($res)==0){
							//if(!isset($canDoArray[$key])){$canDoArray[$key]='';}
							$fnum = $key;
							$mnum .= "'".$value['main_data_num']."',";
						}
					}
				}
				
				if($fnum > '' && $mnum>''){
					$sql_dsc = "
					select * from `test_time_topic` where 
					`f_num`='".$fnum."' 
					and `main_data_num` in (".substr($mnum,0,-1).") 
					order by `c_order`
					";
					
					$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
					while($row = mysql_fetch_array($res)){
						$orderData[] = $row['main_data_num'];
					}					
				}	
			}
			$whereDsc = "";
			$isStudent = true;
			break;
			default:
			ri_jump("logout.php");
			break;		
		}	
	}
	
	$module_dsc = array(
	'science'=>'科學模組','read'=>'閱讀模組','mathematics'=>'數學模組'
	);

	$speech_dsc = array(
	'switch'=>'選項式對話','free'=>'開放式對話'
	);
	
	
	//取出單元資料
	$sql_dsc = "select * from `main_data` ".$whereDsc;//管理員資料
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$sql_data['c_title'] = $row['c_title'];
		$sql_data['up_date'] = $row['up_date'];
		$sql_data['c_module_type'] = $module_dsc[$row['c_module_type']];
		$sql_data['c_speech_type'] = $speech_dsc[$row['c_speech_type']];
		
		$sql_data['num'] = $row['num'];
		switch($row['c_module_type']){
		case "science":
		$sql_data['c_html_dsc'] = "test.php";
		break;
		case "mathematics":
		$sql_data['c_html_dsc'] = "test_m.php";
		break;
		case "read":
		$sql_data['c_html_dsc'] = "test_r.php";
		break;
		}
		

		//取出作業資料
		$sql_dsc_1 = "select * from `operation_data` where `main_data_num`='".$row['num']."' order by `num`";
		$res_1=$ODb->query($sql_dsc_1) or die("載入資料出錯，請聯繫管理員。");
		while($row_1 = mysql_fetch_array($res_1)){
			$work_data['num'] = $row_1['num'];				
			$work_data['c_title'] = $row_1['c_title'];				
			$work_data['up_date'] = $row_1['up_date'];				

			//計算試題數量
			$sql_dsc_2 = "select count(*) as `g_num` from `questions_data` where `operation_data_num`='".$row_1['num']."'";
			$res_2=$ODb->query($sql_dsc_2) or die("載入資料出錯，請聯繫管理員。");
			while($row_2 = mysql_fetch_array($res_2)){
				$work_data['g_num'] = $row_2['g_num'];
			}		
			$work_data_array[$row['num']][] = $work_data;
		}
		
		$sql_data_array[$row['num']] = $sql_data;
	}
	
		//如果是教師就調出已經選擇別人創造的題目
	if($_SESSION['loginType'] == "TEACHER" || $_SESSION['loginType'] == "STUDENT"){
		if($_SESSION['loginType'] == "TEACHER"){$userNum=$_SESSION['swTeacherNum'];}
		if($_SESSION['loginType'] == "STUDENT"){$userNum=$_SESSION['teacherdataNum'];}
	
		$sql_dsc = "
		select `a`.*,`b`.`mainData_num`,`b`.`user_type`,`b`.`user_num`
		from `main_data` as `a`
		left join `share_data` as `b` on `b`.`mainData_num`=`a`.`num` 
		where `b`.`user_type`='TEACHER' and `b`.`user_num`='".$userNum."' and `a`.`is_share`=1 order by `a`.`num` ";//管理員資料
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
		while($row = mysql_fetch_array($res)){
			if(!isset($sql_data_array[$row['num']])){

			$sql_data['c_title'] = $row['c_title'];
			$sql_data['up_date'] = $row['up_date'];
			$sql_data['c_module_type'] = $module_dsc[$row['c_module_type']];
			$sql_data['c_speech_type'] = $speech_dsc[$row['c_speech_type']];
			
			$sql_data['num'] = $row['num'];
			switch($row['c_module_type']){
			case "science":
			$sql_data['c_html_dsc'] = "test.php";
			break;
			case "mathematics":
			$sql_data['c_html_dsc'] = "test_m.php";
			break;
			case "read":
			$sql_data['c_html_dsc'] = "test_r.php";
			break;
			}
			

			//取出作業資料
			$sql_dsc_1 = "select * from `operation_data` where `main_data_num`='".$row['num']."' order by `num`";
			$res_1=$ODb->query($sql_dsc_1) or die("載入資料出錯，請聯繫管理員。");
			while($row_1 = mysql_fetch_array($res_1)){
				$work_data['num'] = $row_1['num'];				
				$work_data['c_title'] = $row_1['c_title'];				
				$work_data['up_date'] = $row_1['up_date'];				

				//計算試題數量
				$sql_dsc_2 = "select count(*) as `g_num` from `questions_data` where `operation_data_num`='".$row_1['num']."'";
				$res_2=$ODb->query($sql_dsc_2) or die("載入資料出錯，請聯繫管理員。");
				while($row_2 = mysql_fetch_array($res_2)){
					$work_data['g_num'] = $row_2['g_num'];
				}		
				$work_data_array[$row['num']][] = $work_data;
			}
		
			$sql_data_array[$row['num']] = $sql_data;
			}
		}	
	}
$ODb->close();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>全球素養線上評量</title>
<link rel="stylesheet" href="css/admin.css" />
<link rel="stylesheet" href="css/jquery-ui.css" />
<link rel="stylesheet" href="css/colorbox.css" />

<script src="./js/jquery-1.10.1.min.js"> </script>
<script src="./js/javascript.js"></script><!-- 頁面收和 -->
<script src="./js/jquery-ui.js"></script>
<script src="./js/jquery.colorbox.js"></script>
<script language="javascript">
var tr_index = 0;//新增作業的index

//顯示校區下拉選單
function add_unit(){
$('#inline').show();
$.colorbox({inline:true,href:"#inline", width:"30%",open:true,onClosed:function(){
$('#inline').hide();
}});
}

function add_operation(keynum){
$('#inline_operation').show();
$('#inline_operation_input_2').val(keynum) ;
$.colorbox({inline:true,href:"#inline_operation", width:"30%",open:true,onClosed:function(){
$('#inline_operation').hide();
}});
}

//新增一筆單元
function up_unit(){
	var is_Go = true;
	var error_dsc ="";
	if($('#inline_input').val() ==''){
		is_Go = false;
		error_dsc +="請輸入單元名稱!!\r\n";
	}
	if(is_Go){
		$.ajax({
	    url: './js_function/add_unit.php',
		data: {keyNum:$('#inline_input').val()},		
	    error: function(xhr) {
			alert('Ajax request 發生錯誤');
	    },
	    success: function(response) {
			alert('新增單元成功!!');
			location.replace('?tr=');
	    }
	  });
	}
	
	if(error_dsc !=''){
		alert(error_dsc);
	}	
}

//新增一筆作業
function up_operation(){
	var is_Go = true;
	var error_dsc ="";
	if($('#inline_operation_input').val() ==''){
		is_Go = false;
		error_dsc +="請輸入作業名稱!!\r\n";
	}
	if(is_Go){
		$.ajax({
	    url: './js_function/add_operation.php',
		data: {keyNum:$('#inline_operation_input_2').val(),namedsc:$('#inline_operation_input').val(),powerdsc:$('#inline_operation_dsc').val()},		
	    error: function(xhr) {
			alert('Ajax request 發生錯誤');
	    },
	    success: function(response) {			
			alert('新增作業成功!!');
			location.replace('?tr='+$('#inline_operation_input_2').val());
	    }
	  });
	}
	
	if(error_dsc !=''){
		alert(error_dsc);
	}	
}

function del_unit(get_num,unit_dsc){
	if(confirm("確定是否刪除下列單元名稱的資料及其底下所有的作業資料內容嗎?\r\n"+unit_dsc)){
		$.ajax({
			url: './js_function/delfunction.php',
			data: {keyNum:get_num,tables:"<?php echo base64_encode("main_data");?>"},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {			
				alert('資料刪除成功!!');
				location.replace('?tr=');
			}
		});
	}
}

function del_operation(get_num,unit_dsc){
	if(confirm("確定是否刪除下列作業名稱的資料內容嗎?\r\n"+unit_dsc)){
		$.ajax({
			url: './js_function/delfunction.php',
			data: {keyNum:get_num,tables:"<?php echo base64_encode("operation_data");?>"},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {			
				alert('資料刪除成功!!');
				location.replace('?tr=');
			}
		});
	}
}

function edit_operation(keynum){
	location.href="operation_edit.php?o_num="+keynum+"&o_t_num=<?php echo base64_encode(0);?>";
}

$(document).ready(function() {
<?php 
	if($_GET['tr']!=''){
	echo "$('#div_area_".$_GET['tr']."').slideDown('fase');";
	}
?>
});
</script>
</head>

<body>
<div id="wrapper">
	<h1>試題列表</h1>
	<ul>
    	<li><?php echo $_SESSION['loginUserName'];?>您好!!</li>	
    	<li><a class="button" title="學生表現查詢" href="<?php echo $recordURL;?>.php">學生表現查詢</a></li>	
    	<li><a class="button" title="登出" href="logout.php">登出</a></li>
    </ul>    
    <table class="title">
        <tr>
            <td width="10%">編號</td>
            <td width="42%">單元名稱</td>
            <!--<td width="10%">對話類型</td>-->
            <!--<td width="10%">模組類別</td>-->			
            <td width="10%">作業數量</td>
            <td width="38%">功能</td>
        </tr>
    </table>
<?php 
if($isStudent){
$x=0;
//練習題目
if(is_array($is_practice_array)){
foreach($is_practice_array as $mynum){
			$value =$sql_data_array[$mynum]; 
?>
<!-- 單元 -->
	<div class="accordionButton">
		<table class="list_item">
			<tr>
				<td width="10%"><?php echo ($x+1);?></td>
				<td width="42%" ><p class="name" align="center"><?php echo $value['c_title'];?>(練習題目)</p></td>
				<!-- <td width="10%"><?php //echo $value['c_speech_type'];?></td> -->
				<!-- <td width="10%"><?php //echo $value['c_module_type'];?></td> -->				
				<td width="10%"><?php echo count($work_data_array[$value['num']]);?></td>
				<td width="38%">
					<a class="button" title="練習" href="<?php echo $value['c_html_dsc'];?>?m_num=<?php echo $value['num'];?>">練習</a>                
				</td>
			</tr>
		</table>             
	</div>
<!-- 單元 End -->
<?php	
	$x++;
	}
}

//老師設定的題目
$showBtn=true;
if(is_array($orderData)){
foreach($orderData as $mynum){

		$value =$sql_data_array[$mynum]; 
?>
<!-- 單元 -->
    <div class="accordionButton">
        <table class="list_item">
            <tr>
                <td width="10%"><?php echo ($x+1);?></td>
                <td width="42%" ><p class="name" align="center"><?php echo $value['c_title'];?></p></td>
                <!-- <td width="10%"><?php //echo $value['c_speech_type'];?></td> -->
                <!-- <td width="10%"><?php //echo $value['c_module_type'];?></td> -->				
                <td width="10%"><?php echo count($work_data_array[$value['num']]);?></td>
                <td width="38%">
                    <?php if($showBtn){?>
					<a class="button" title="練習" href="<?php echo $value['c_html_dsc'];?>?m_num=<?php echo $value['num'];?>">練習</a>
					<?php
					$showBtn = false;
					}
					?>
                </td>
            </tr>
        </table>             
    </div>
<!-- 單元 End -->
<?php	
	$x++;

}
}

}else{
if(is_array($sql_data_array)){
$x=0;
foreach($sql_data_array as $value){	?>
<!-- 單元 -->
    <div class="accordionButton">
        <table class="list_item">
            <tr>
                <td width="10%"><?php echo ($x+1);?></td>
                <td width="42%" <p class="name" align="center"><?php echo $value['c_title'];?></p></td>
                <!--<td width="10%"><?php //echo $value['c_speech_type'];?></td>-->
                <!--<td width="10%"><?php //echo $value['c_module_type'];?></td>-->				
                <td width="10%"><?php echo count($work_data_array[$value['num']]);?></td>
                <td width="38%">
                    <a class="button" title="練習" href="<?php echo $value['c_html_dsc'];?>?m_num=<?php echo $value['num'];?>">練習</a>                
                </td>
            </tr>
        </table>             
    </div>
<!-- 單元 End -->
<?php	
	$x++;
		}	
	}
}
?>
</div>

</body>
</html>
