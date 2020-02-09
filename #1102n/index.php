<?php
include("./bcontroller/class/common_lite.php");
session_start();
if($_SESSION['loginType'] == ''){
	ri_jump("logout.php");
}
// var_dump($_SERVER['HTTP_REFERER']);
if(empty($_SERVER['HTTP_REFERER'])){
die('請勿開啟多分頁進行測驗!');
};


require_once "../assessment/index.html";
?>