<?php

// require_once 'config.php'; 
include_once "../../include/config.php";
include_once '../../include/adp_core_function.php';
// session_start();

// $user_id=$_SESSION[_authsession][username];



echo $_SESSION['user_id'];
$user_id = $_SESSION['user_id'];
$indicate_id=$_POST[indicate_id];
$date=date("Y-m-d H:i:s");
$start_time=$_POST[startTime];
$stop_time=$_POST[endTime];
$during_time=$stop_time-$start_time;
$item_num=$_POST[item_num];
$org_res=$_POST[org_res];

$insertData = $dbh->prepare('INSERT INTO exam_record_its(user_id, indicate_id, date, start_time, stop_time, during_time, item_num, org_res) VALUES (:user_id,:indicate_id,:date,:start_time,:stop_time,:during_time,:item_num,:org_res)');

$insertData->bindValue(':user_id',$user_id, PDO::PARAM_STR);
$insertData->bindValue(':indicate_id', $indicate_id, PDO::PARAM_STR);
$insertData->bindValue(':date', $date, PDO::PARAM_STR);
$insertData->bindValue(':start_time', $start_time, PDO::PARAM_STR);
$insertData->bindValue(':stop_time', $stop_time, PDO::PARAM_STR);
$insertData->bindValue(':during_time', $during_time, PDO::PARAM_STR);
$insertData->bindValue(':item_num', $item_num, PDO::PARAM_STR);
$insertData->bindValue(':org_res', $org_res, PDO::PARAM_STR);
$insertData->execute();



?>
