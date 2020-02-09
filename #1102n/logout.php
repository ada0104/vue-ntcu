<?php
	session_start();
	
  //包含需求檔案 ------------------------------------------------------------------------
	include("./bcontroller/class/common_lite.php");
	$ODb = new run_db("mysql",3306); 
	$num_login = $_SESSION['swStudentNum']; 
	$sql_dsc1 = "UPDATE studentdata  SET login_ing = 0 WHERE num = '$num_login';";
	$res=$ODb->query($sql_dsc1);
	
	
	$gowhere = "login.php";
	session_destroy();
	ri_jump($gowhere);	
?>

