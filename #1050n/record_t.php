<?php
	session_start();
	/*
		此頁面只顯示老師及其學生資料
	*/
	//包含需求檔案 ------------------------------------------------------------------------
	include("./bcontroller/class/common_lite.php");
	$getKey = $_GET['getKey'];
	//if($_SESSION['loginType'] == '' || !is_numeric($getKey)){
	if($_SESSION['loginType'] == ''){
		ri_jump("logout.php");
	}else{
		switch($_SESSION['loginType']){
			case "ADMIN":
				$whereDsc = " where `teacherdataNum`='".$getKey."' ";
				$userType = "ad";
			break;
			case "TEACHER":
				$whereDsc = " where `teacherdataNum`='".$_SESSION['swTeacherNum']."' ";
				$whereDsc_pratcice = " where `user_num`='".$_SESSION['swTeacherNum']."' ";
				$getKey = $_SESSION['swTeacherNum'];
				$userType = "tr";
			break;
			default:
			ri_jump("logout.php");
			break;		
		}	
	}	
	//宣告變數 ----------------------------------------------------------------------------
	if($_GET['pg']=='' || !is_numeric($_GET['pg'])){
		if($_SESSION['teacher_list_pg']==''){
			$_SESSION['teacher_list_pg']=1;
			ri_jump("?pg=1");
		}else{
			ri_jump("?pg=".$_SESSION['teacher_list_pg']);
		}
	}

	$show_data_num = 50;//一頁顯示多少筆資料
	if($_GET['pg']== '1' ){
		$_SESSION['teacher_list_pg']=1;
		$now_pg = 1;
		$pg_dsc = ' limit '.$show_data_num;
	}else{
		$_SESSION['teacher_list_pg']=$_GET['pg'];
		$now_pg = $_GET['pg'];
		$pg_dsc = ' limit '.(($_GET['pg']-1) * $show_data_num) .','.$show_data_num ;
	}
	
	$ODb = new run_db("mysql",3306);      //建立資料庫物件
	
	//取出學生資料
	$sql_dsc = "select * from `studentdata` ".$whereDsc." order by `num` desc ".$pg_dsc;//管理員資料
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$test_data['num'] = $row['num'];
		$test_data['grade_dsc'] = $row['grade_dsc'];		
		$test_data['class_dsc'] = $row['class_dsc'];
		$test_data['student_id'] = $row['student_id'];
		$test_data['c_name'] = $row['c_name'];
		$test_data_array[] = $test_data;
	}

	//計算總頁數
	$total_pg = 0;
	$sql_dsc = "SELECT count(*) as `get_num` FROM `studentdata` ".$whereDsc;
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		if($row['get_num'] > 0 ){
			$num1 = $row['get_num']/$show_data_num;
			$num1_array = explode('.',$num1);
			$total_pg = $num1_array[0];
			if(count($num1_array)>1){
			$total_pg++;
			}
		}
	}
	
	//根據現有清單，計算正式題數與取得f_num
	$sql_dsc = "SELECT * FROM `test_time_teacher` ".$whereDsc." GROUP BY  `main_data_num` ";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	$total_test_num =0;
	$f_num_dsc = '';
	while($row = mysql_fetch_array($res)){
		$total_test_num++;

		$f_num_dsc = ",'".$row['f_num']."'";
		$f_num_new = $row['f_num'];

	}	
		
	//根據現有清單取得此老師下的題目清單
	$sql_dsc = "SELECT * FROM `test_time_teacher` ".$whereDsc." GROUP BY `f_num`";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	$total_test_num_practice =0;
	while($row = mysql_fetch_array($res)){
		
		$f_num_new .= ",".$row['f_num'];
		
	}
	if($f_num_new== ''){
		$f_num_new = 0;
	} ;
	//調查一下現有清單內，同學做過得題目數量(包含練習題目)
	// switch($_SESSION['loginType']){
	// 	case "ADMIN":
	// 		$whereDsc = " WHERE `teacher_user`='".$getKey."' AND `student_user`>0 AND `timelist_num` IN ('0'".$f_num_dsc.") ";
	// 	break;
	// 	case "TEACHER":
	// 		$whereDsc = " WHERE `teacher_user`='".$_SESSION['swTeacherNum']."' AND `student_user`>0  AND `timelist_num` IN ('0'".$f_num_dsc.") ";
	// 	break;
	// }	
	// $sql_dsc = "SELECT * FROM `opt_record` ".$whereDsc;
	// $res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	// while($row = mysql_fetch_array($res)){
	// 	if(!isset($test_count[$row['student_user']])){
	// 		$test_count[$row['student_user']] = 1;
	// 	}else{
	// 		$test_count[$row['student_user']]++;
	// 	}
	// }
	
	//調查一下現有清單內，同學做過得題目數量(不包含練習題目)
	switch($_SESSION['loginType']){
		case "ADMIN":
			$whereDsc = " WHERE `teacher_user`='".$getKey."' AND `student_user`>0 AND `timelist_num` IN ('0'".$f_num_dsc.") ";
		break;
		case "TEACHER":
			$whereDsc = " WHERE `teacher_user`='".$_SESSION['swTeacherNum']."' AND `student_user`>0  AND `timelist_num` IN ($f_num_new)";
		break;
	}	
	$sql_dsc = "SELECT * FROM `opt_record` ".$whereDsc;
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){

		if(!isset($test_count[$row['student_user']])){
			$test_count[$row['student_user']] = 1;
			
		}else{
			$test_count[$row['student_user']]++;
		
		}
	}
	
	$ODb->close();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>合作問題解決線上評量</title>
<script src="./js/jquery-1.10.1.min.js"> </script>
<script src="./js/javascript.js"></script><!-- 頁面收和 -->
<script src="./js/jquery-ui.js"></script>
<link rel="stylesheet" href="css/admin.css" />
<link rel="stylesheet" href="css/jquery-ui.css" />
<link rel="Stylesheet" href="css/jquery-ui-1.7.1.custom.css" type="text/css" />

</head>

<body>
<div id="wrapper">
	<h1>操作記錄列表</h1>
	<ul>
    	<li><?php echo $_SESSION['loginUserName'];?>您好!!</li>		
		<li><a class="button" title="下載學生成績總表" onclick="$('#excelForm1').submit();" >下載學生成績總表</a></li>
    	<li><a class="button" title="試題列表" href="index.php">試題列表</a></li>
    	<li><a class="button" title="登出" href="logout.php">登出</a></li>
    </ul>    

    <table class="title">
        <tr>
            <td width="5%">編號</td>
            <td width="10%">授課年級</td>
            <td width="10%">授課班級</td>
            <td width="10%">學號</td>									
            <td width="10%">正式題目作答進度</td>						
            <td width="15%">姓名</td>
            <td width="20%">動作</td>
        </tr>
    </table>
<!-- 單元 -->
	<?php if($_GET['pg']==1){?>
    <div class="accordionButton">
        <table class="list_item">
            <tr>
                <td width="5%">1</td>
                <td width="10%"><p class="name"></p></td>
                <td width="10%"><p class="name"></p></td>
                <td width="10%"></td>								
                <td width="10%"></td>				
                <td width="15%"><?php echo $_SESSION['loginUserName'];?>教師</td>
                <td width="20%">                                   
                    <a class="button" title="觀看成績" href="record_s.php?t=<?php echo $getKey;?>&s=<?php echo $valueArray['num'];?>">觀看成績</a>                
                </td>
            </tr>
        </table>             
    </div>
	<?php }?>
<!-- 單元 End -->
<?php 
if($_GET['pg']==1){$y=2;}else{$y=1;}
for ($x=0;$x<count($test_data_array);$x++) {
    $valueArray = $test_data_array[$x];
	?>
	
<!-- 單元 -->

    <div class="accordionButton">
        <table class="list_item">
            <tr>
                <td width="5%"><?php echo ($y);?></td>
                <td width="10%" align="center"><?php echo $valueArray['grade_dsc'];?></td>
                <td width="10%" align="center"><?php echo $valueArray['class_dsc'];?></td>
                <td width="10%"><?php echo $valueArray['student_id'];?></td>						
                <td width="10%"><?php 
				
				if(isset($test_count[$valueArray['num']])){
					if($total_test_num>0){
						echo $test_count[$valueArray['num']]."/".($total_test_num);
					}else{
						echo $test_count[$valueArray['num']]."/".($total_test_num);
					}
				}else{
					echo '無';
				}	
				 ?></td>				
                <td width="15%"><?php echo $valueArray['c_name'];?></td>
                <td width="20%">
                    <a class="button" title="學生表現" href="record_s.php?t=<?php echo $getKey;?>&s=<?php echo $valueArray['num'];?>">學生表現</a>                
                </td>
            </tr>
        </table>             
    </div>
<!-- 單元 End -->
<?php	
	$y++;
}
?>	
</div>
<div class="page" align="center">
	 <?php 
		if($total_pg >0){
				if(($now_pg-1) > 0){
				echo '
				<a href="?pg='.($now_pg-1).'" > 上一頁 </a>&nbsp;&nbsp;
				';
				}else{
				echo '
				<a href="?pg='.("1").'" > 上一頁 </a>&nbsp;&nbsp;';
				}
			 
			  if(($now_pg-5)>0){
				echo '<u><a href="?pg='.($now_pg-5).'">'.($now_pg-5).'</a></u>&nbsp;&nbsp;';
			  }
			   if(($now_pg-4)>0){
				echo '<u><a href="?pg='.($now_pg-4).'">'.($now_pg-4).'</a></u>&nbsp;&nbsp;';
			  }
			   if(($now_pg-3)>0){
				echo '<u><a href="?pg='.($now_pg-3).'">'.($now_pg-3).'</a></u>&nbsp;&nbsp;';
			  }
			   if(($now_pg-2)>0){
				echo '<u><a href="?pg='.($now_pg-2).'">'.($now_pg-2).'</a></u>&nbsp;&nbsp;';
			  }
			   if(($now_pg-1)>0){
				echo '<u><a href="?pg='.($now_pg-1).'">'.($now_pg-1).'</a></u>&nbsp;&nbsp;';
			  }
			  
				echo $now_pg."&nbsp;&nbsp;";//目前頁面
				
			   if(($now_pg+1)<=$total_pg){
				echo '<u><a href="?pg='.($now_pg+1).'">'.($now_pg+1).'</a></u>&nbsp;&nbsp;';
			  }			  
			   if(($now_pg+2)<=$total_pg){
				echo '<u><a href="?pg='.($now_pg+2).'">'.($now_pg+2).'</a></u>&nbsp;&nbsp;';
			  }			  
			   if(($now_pg+3)<=$total_pg){
				echo '<u><a href="?pg='.($now_pg+3).'">'.($now_pg+3).'</a></u>&nbsp;&nbsp;';
			  }			  
			   if(($now_pg+4)<=$total_pg){
				echo '<u><a href="?pg='.($now_pg+4).'">'.($now_pg+4).'</a></u>&nbsp;&nbsp;';
			  }			  
			   if(($now_pg+5)<=$total_pg){
				echo '<u><a href="?pg='.($now_pg+5).'">'.($now_pg+5).'</a></u>&nbsp;&nbsp;';
			  }			  
			  
				if(($total_pg-$now_pg) > 0){
				echo '&nbsp;&nbsp;
				<a href="?pg='.($now_pg+1).'" > 下一頁 </a>';
				}else{
				echo '&nbsp;&nbsp;
				<a href="?pg='.($now_pg).'" > 下一頁 </a>';
				}
		}	  
  ?>
</div>
<form method="POST" action="./testdig/index.php" id="excelForm1" target="_black">
	<input type="hidden" name="userID" value="<?php echo $_SESSION['xx_user_loginId'];?>">	
	<input type="hidden" name="userPW" value="<?php echo $_SESSION['xx_user_pw'];?>">	
	<input type="hidden" name="userType" value="<?php echo $userType;?>">	
	<input type="hidden" name="teacherNum" value="<?php echo $getKey;?>">
</form>
</body>
</html>
