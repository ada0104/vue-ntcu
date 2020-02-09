<?php

/*
20190411 #543 CCW     增加判斷身分 整班刪除 學生或老師 ; 補上user_data變數;
20190417 #552 Ed      身分證字號清空，所有程式不再異動身分證字號欄位 user_info identity
                      匯入身分證不限制10碼，開放填入居留證
20190422 #558 Ariel   教師管理學生帳號時，加入 user_info 預設班級



*/


session_start();
require_once "../../include/adp_API.php";
require_once "../../include/security_function.php";
require_once "../../include/general/function_remedial.php";
$user_data = unserialize($_SESSION['USERDATA']);

$sAct = $_POST['act'];
/**刪除帳號對應的所有表 */
$vDelArr[] = $dbh->prepare("DELETE FROM exam_record WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM exam_record_dynamic WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM exam_record_indicate WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM exam_record_indicate_tmp WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM exam_record_indicator WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM exam_record_indicator_tmp WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM exam_record_its WHERE `user_id`=?");
// 不能將補救教學資料刪除，因為重新建帳號時需要再對應
// $vDelArr[] = $dbh->prepare("DELETE FROM exam_record_priori WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM exam_record_tmp WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM mad_exam_record WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM map_node_student_status WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM message_fileattached WHERE upload_userid=?");
$vDelArr[] = $dbh->prepare("DELETE FROM message_master WHERE create_user=?");
$vDelArr[] = $dbh->prepare("DELETE FROM message_response WHERE remsg_create_user=?");
$vDelArr[] = $dbh->prepare("DELETE FROM mission_result WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM mission_stud_record WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM prac_answer WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM prac_session_data WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM remedial_student WHERE student_id=?");
$vDelArr[] = $dbh->prepare("DELETE FROM remedial_teacher WHERE teacher_id=?");
$vDelArr[] = $dbh->prepare("DELETE FROM role_info WHERE stud_id=?");
$vDelArr[] = $dbh->prepare("UPDATE seme_group SET is_used = 0 WHERE teacher_id=?");
$vDelArr[] = $dbh->prepare("DELETE FROM seme_grad_student WHERE stud_id=?");
$vDelArr[] = $dbh->prepare("DELETE FROM seme_student WHERE stud_id=?");
$vDelArr[] = $dbh->prepare("DELETE FROM seme_teacher_subject WHERE teacher_id=?");
$vDelArr[] = $dbh->prepare("DELETE FROM user_group WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM user_history WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM user_status WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM user_info WHERE `user_id`=?");
//$vDelArr[] = $dbh->prepare("DELETE FROM user_info_tmp WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM video_exam_record WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM video_note WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM video_noteask WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM video_noteask_plus WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM video_review_record WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("DELETE FROM video_review_record_tmp WHERE `user_id`=?");
$vDelArr[] = $dbh->prepare("UPDATE mission_info SET end_mk='Y', unable='0' WHERE teacher_id=?");




switch ($sAct) {
  // 班級選單
  case 'TeacherClassOption':
    echo json_encode(actTeacherClassOption($user_data));
    break;

  /**
   * 家長帳號管理
   */
  case 'schoolOption':
    echo json_encode(actSchoolOption());
    break;
    // ------------------------------------------------------
  case 'GradeOption':
    echo json_encode(actGradeOption());
    break;
    // ------------------------------------------------------
  case 'ClassOption':
    echo json_encode(actClassOption($user_data));
    break;
    // ------------------------------------------------------
  case 'ParentData':
    echo json_encode(actParentData($user_data));
    break;
    // ------------------------------------------------------
  case 'findStu':
    echo json_encode(actFindStu());
    break;
    // ------------------------------------------------------
  case 'editParent':
    actEditParent($user_data);
    break;
    // ------------------------------------------------------
  case 'par_lock':
    $par_id = $_POST['par_id'];
    $sLockP = $dbh->prepare("UPDATE user_info SET used = 0 WHERE `user_id`=?");
    $sLockP->execute(array($par_id));
    break;
    // ------------------------------------------------------
  case 'par_unlock':
    $par_id = $_POST['par_id'];
    $sUnlockP = $dbh->prepare("UPDATE user_info SET used = 1 WHERE `user_id`=?");
    $sUnlockP->execute(array($par_id));
    break;
    // ------------------------------------------------------
  case 'par_del':
    $par_id = $_POST['par_id'];
    $vDelArr[] = $dbh->prepare("DELETE FROM user_family WHERE `fuser_id`=?");
    foreach ($vDelArr as $value) {
      $value->execute(array($par_id));
    }
    break;
  /**
   * 學生帳號管理
   */
  case 'StuData':
    echo json_encode(actStuData($user_data));
    break;
  case 'SearchByName':
    echo json_encode(actSearchByName($user_data));
    break;
  case 'editStu':
    actEditStu($user_data);
    break;
  case 'stu_del':
    $status = actStuDel($vDelArr);
    if ($status != '') {
      echo $status;
    }
    break;
  case 'delClass':
    $status = actDelClass($vDelArr);
    if ($status != '') {
      echo $status;
    }
    break;
  case 'cleanOPENID':
    $status = clean_openid($vDelArr);
    if ($status != '') {
      echo $status;
    }
    break;
  case 'UpdateIdentity':
    $status = update_identity($vDelArr);
    if ($status != '') {
      echo $status;
    }
    break;
}

function actSchoolOption() {
  global $dbh;
  $city_code = $_POST['city_code'];
  $sSchName = $dbh->prepare("SELECT DISTINCT `organization_id`, `name` FROM organization WHERE city_code LIKE ? AND used = '1'");
  $sSchName->execute(array("%" . $city_code . "%"));
  $vSchName = $sSchName->fetchAll(PDO::FETCH_ASSOC);
  return $vSchName;
}

function actGradeOption() {
  global $dbh;
  $vGrade = array();

  if (isset($_POST['is_teacher']) && $_POST['is_teacher'] == '1') {
    $vGrade = actTeacherGradeOption();
  } else {
    $organization_id = $_POST['organization_id'];
    $sGrade = $dbh->prepare("SELECT DISTINCT grade FROM user_info WHERE organization_id=? ORDER BY `grade` ASC");
    $sGrade->execute(array($organization_id));
    $vGrade = $sGrade->fetchAll(PDO::FETCH_ASSOC);
  }

  return $vGrade;
}

function actClassOption($user_data) {
  global $dbh;

  $vClass = array();
  if (isset($_POST['is_teacher']) && $_POST['is_teacher'] == '1') {
    $vClass = actTeacherClassOption($user_data);
  } else {
    $organization_id = $_POST['organization_id'];
    $grade = $_POST['grade'];
    $sClass = $dbh->prepare("SELECT DISTINCT class FROM user_info WHERE organization_id = ? AND grade = ? ORDER BY class ASC");
    $sClass->execute(array($organization_id, $grade));
    $vClass = $sClass->fetchAll(PDO::FETCH_ASSOC);
  }
  return $vClass;
}

function actParentData($user_data) {
  global $dbh;
  $organization_id = $_POST['organization_id'];
  $grade = $_POST['grade'];
  $classNum = $_POST['classNum'];
  $vReturnData = array();
  $typeAble = $_POST['typeAble'];
  $typelevel = $_POST['level'];
  if ($user_data->access_level >= 71) {
    $sParentData = $dbh->prepare("SELECT DISTINCT a.uname, a.user_id, a.grade, a.class, a.used, c.fuser_id FROM user_info a, user_status b, user_family c WHERE a.user_id = c.user_id AND c.organization_id = ? AND a.grade = ? AND a.class = ?");
    $sParentData->execute(array($organization_id, $grade, $classNum));
    $vReturnData['level'] = 1;
    $sFamily = $dbh->prepare("SELECT DISTINCT uname, used, email, sex FROM user_status LEFT JOIN user_info ON user_status.user_id = user_info.user_id WHERE user_status.user_id = ? AND user_info.used = ? AND user_status.access_level = ?");
  } elseif ($user_data->access_level == USER_SCHOOL_ADMIN) {
    $sParentData = $dbh->prepare("SELECT DISTINCT a.uname, a.user_id, a.grade, a.class, a.used, c.fuser_id FROM user_info a, user_status b, user_family c WHERE a.user_id = c.user_id AND c.organization_id = ? AND a.grade = ? AND a.class = ?");
    $sParentData->execute(array($user_data->organization_id, $grade, $classNum));
    $vReturnData['level'] = 2;
    $sFamily = $dbh->prepare("SELECT DISTINCT uname, used, email, sex FROM user_status LEFT JOIN user_info ON user_status.user_id = user_info.user_id WHERE user_status.user_id = ? AND user_info.used = ? AND user_status.access_level = ?");
  }
  $vParentData = $sParentData->fetchAll(PDO::FETCH_ASSOC);
  foreach ($vParentData as $key => $value) {
    $sFamily->execute(array($value['fuser_id'], $typeAble, $typelevel));
    $vFamily = $sFamily->fetchAll(PDO::FETCH_ASSOC);
    if($typeAble == '1' && $typeAble == $vFamily[0]['used']){
      if ($user_data->access_level >= 71) {//密碼轉置成正確,僅 admin 可查詢
        if ($vFamily[0]['pass'] != md5($vFamily[0]['viewpass'])) {
          $vData[$value['fuser_id']]['pass'] = pass2compiler($vFamily[0]['viewpass']);
        } else {
          $vData[$value['fuser_id']]['pass'] = $vFamily[0]['viewpass'];
        }
      }
      $vData[$value['fuser_id']]['fuser_id'] = $value['fuser_id'];
      $vData[$value['fuser_id']]['used'] = $vFamily[0]['used'];
      $vData[$value['fuser_id']]['fname'] = $vFamily[0]['uname'];
      $vData[$value['fuser_id']]['email'] = $vFamily[0]['email'];
      $vData[$value['fuser_id']]['sex'] = $vFamily[0]['sex'];
    }else if($typeAble == '0' && $typeAble == $vFamily[0]['used']){
      if ($user_data->access_level >= 71) {//密碼轉置成正確,僅 admin 可查詢
        if ($vFamily[0]['pass'] != md5($vFamily[0]['viewpass'])) {
          $vData[$value['fuser_id']]['pass'] = pass2compiler($vFamily[0]['viewpass']);
        } else {
          $vData[$value['fuser_id']]['pass'] = $vFamily[0]['viewpass'];
        }
      }
      $vData[$value['fuser_id']]['fuser_id'] = $value['fuser_id'];
      $vData[$value['fuser_id']]['used'] = $vFamily[0]['used'];
      $vData[$value['fuser_id']]['fname'] = $vFamily[0]['uname'];
      $vData[$value['fuser_id']]['email'] = $vFamily[0]['email'];
      $vData[$value['fuser_id']]['sex'] = $vFamily[0]['sex'];
    }
  }
  if (!empty($vData)) {
    foreach ($vData as $key => $value) {
      $sStuCombF = $dbh->prepare("SELECT a.user_id, a.uname, a.grade, a.class FROM user_info a, user_family b, user_status c WHERE b.fuser_id='$key' AND a.user_id = b.user_id AND b.user_id = c.user_id AND c.access_level=1 ORDER BY a.uid ");
      $sStuCombF->execute();
      $vStuCombF = $sStuCombF->fetchAll(PDO::FETCH_ASSOC);
      $vData[$key]['stu'] = $vStuCombF;
    }
    $vReturnData['parent'] = $vData;
  }
  return $vReturnData;
}

function actEditParent($user_data) {
  global $dbh;
  $editData = $_POST['editData'];
  $datetime = date("Y-m-d H:i:s");

  $oGetOriEmail = $dbh->prepare("SELECT email FROM user_info WHERE user_id = :user_id");
  $oGetOriEmail->bindValue(":user_id", $editData['par_id'], PDO::PARAM_STR);
  $oGetOriEmail->execute();
  $vGetOriEmail = $oGetOriEmail->fetchAll(PDO::FETCH_ASSOC);

  if($editData['femail'] != $vGetOriEmail['email']){
    include_once "../../classes/phpmailer_sendmail.php";
    //Email通知
    $mail = $editData['femail'];
    $subject = "因材網-家長信箱修改成功";
    $msg_mail = "恭喜您！您申請修改因材網信箱已成功<br>&nbsp;&nbsp;&nbsp;&nbsp;帳號：" . $editData['par_id'] . "<br>&nbsp;&nbsp;&nbsp;&nbsp;信箱：".$editData['femail']."<br>&nbsp;&nbsp;&nbsp;&nbsp;因材網入口：http://adaptive-learning.ntcu.edu.tw/<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;教育部教師適性教學素養與輔助平臺-因材網";
    phpmailer_sendmail("$mail", "$subject", "$msg_mail");
  }

    //家長資料→user_data
  if (!empty($editData['fpass'])) {
    $sParentU = $dbh->prepare("UPDATE user_info SET uname=?, email=?, sex=?, pass=?, viewpass=?, update_id=?, update_date=? WHERE `user_id` =?");
    $sParentU->execute(array($editData['fname'], $editData['femail'], $editData['fsex'], md5($editData['fpass']), pass2compiler($editData['fpass']), $user_data->user_id, $datetime, $editData['par_id']));
  } else {
    $sParentU = $dbh->prepare("UPDATE user_info SET uname=?, email=?, sex=?, update_id=?, update_date=? WHERE `user_id` =?");
    $sParentU->execute(array($editData['fname'], $editData['femail'], $editData['fsex'], $user_data->user_id, $datetime, $editData['par_id']));
  }
    //學生家長連結→user_family
  if (isset($editData['nowStu_dc'])) {
    $sDCStu = $dbh->prepare("DELETE FROM user_family WHERE `user_id`=? AND fuser_id=?");
    foreach ($editData['nowStu_dc'] as $key => $value) {
      $sDCStu->execute(array($value, $editData['par_id']));
    }
  }
  if (isset($editData['newStu'])) {
    $sQStu = $dbh->prepare("SELECT * FROM user_family WHERE `user_id`=? AND fuser_id=?");
    foreach ($editData['newStu'] as $key => $value) {
      $sQStu->execute(array($value, $editData['par_id']));
      $vQStu = $sQStu->fetchAll(PDO::FETCH_ASSOC);
      if (!empty($vQStu)) {
        $sCStu = $dbh->prepare("UPDATE user_family SET update_date=? WHERE `user_id`=? AND fuser_id=?");
        $sCStu->execute(array($datetime, $value, $editData['par_id']));
      } else {
        $sCStu = $dbh->prepare("INSERT INTO user_family(sn, organization_id, `user_id`, fuser_id, create_date, create_userid)VALUES(?,?,?,?,?,?)");
        $sCStu->execute(array(null, $editData['org_id'], $value, $editData['par_id'], $datetime, $user_data->user_id));
      }
    }
  }
}


// 20190422 不知使用用途，modules/UserManage/UserStudents.php 沒有使用到
function actFindStu() {
  global $dbh;
  $organization_id = $_POST['organization_id'];
  $grade = $_POST['grade'];
  $class = $_POST['class'];
  $nowStu = $_POST['nowStu'];
  $sStuData = $dbh->prepare("SELECT a.user_id, a.uname FROM user_info a, user_status b WHERE a.organization_id = ? AND a.grade = ? AND a.class = ? AND a.user_id = b.user_id AND b.access_level = 1");
  $sStuData->execute(array($organization_id, $grade, $class));
  $vStuData = $sStuData->fetchAll(PDO::FETCH_ASSOC);
  foreach ($vStuData as $key => $value) {
    if (in_array($value['user_id'], $nowStu)) {
      unset($vStuData[$key]);
    }
  }
  return array_values($vStuData);
}

function actStuData($user_data) {
  global $dbh;
  $oGetGradStu = $dbh->prepare("SELECT stud_id FROM seme_grad_student WHERE organization_id = :org_id");
  $oGetGradStu->bindValue(":org_id", $user_data->organization_id, PDO::PARAM_STR);
  $oGetGradStu->execute();
  $vGetGradStu = $oGetGradStu->fetchAll(PDO::FETCH_ASSOC);
  $sel_seme = $_POST['sel_seme'];
  $sStuid = "";
  foreach($vGetGradStu as $value){
    $sStuid.= "'".$value['stud_id']."',";
  }
  $sStuid = substr($sStuid, 0, -1);
  $organization_id = $_POST['organization_id'];
  $grade = $_POST['grade'];
  $classNum = $_POST['classNum'];
  $identify = $_POST['identify'];
  switch ($identify) {
    case USER_STUDENT:
      $sLevel = "'";
      $sLevel .= USER_STUDENT."','";
      $sLevel .= USER_STUDENT_DEMO1."','";
      $sLevel .= USER_STUDENT_DEMO2."'";
      $seme_rule = " AND s.seme_year_seme = '$sel_seme'";
      break;

    case USER_SCHOOL_ADMIN:
      $sLevel = "'";
      $sLevel .= USER_SCHOOL_ADMIN;
      break;
    case USER_TEACHER:
      $sLevel = "'";
      $sLevel .= USER_SCHOOL_PRINCIPAL."','";
      $sLevel .= USER_SCHOOL_DIRECTOR."','";
      $sLevel .= USER_TEACHER."','";
      $sLevel .= USER_LECTURER."'";
      break;
  }
  if ($user_data->access_level == 91 && $identify == USER_SCHOOL_ADMIN) {
      $sLevel .= "','";
      $sLevel .= USER_CITY_ADMIN."','";
      $sLevel .= USER_EDU_ADMIN."'";
  }else if(($user_data->access_level == USER_SCHOOL_ADMIN ||$user_data->access_level == USER_OPERATOR) && $identify == USER_SCHOOL_ADMIN ) {
      $sLevel .= "'";
  }
  $typeSt = $_POST['typeSt'];
  $typeAble = $_POST['typeAble'];
  $vReturnData = array();
  if($identify == USER_STUDENT){
    $sql = "SELECT a.user_id, a.uname, a.sex, a.email, s.grade, s.class, a.identity, a.hash_guid, a.priori_name, ";
  }else{
    $sql = "SELECT a.user_id, a.uname, a.sex, a.email, a.grade, a.class, a.identity, a.hash_guid, a.priori_name, ";
  }
  if ($user_data->access_level >= 71) {
    $sql .= " a.pass, a.viewpass,";
    $vReturnData['level'] = 1;
    if ($user_data->access_level < 91) {
      $vReturnData['level'] = 2;
    }
  } elseif ($user_data->access_level == USER_SCHOOL_ADMIN) {  // #308
    $sql .= " a.pass, a.viewpass,";
    $vReturnData['level'] = 3;
  } else if (in_array($user_data->access_level, USER_TEACHER_GROUP)) {
    $sql .= " a.pass, a.viewpass,";
    $vReturnData['level'] = 4;
  }
  $sql .= " a.used, a.OpenID_sub, c.access_title, c.access_level, s.seme_year_seme, s.seme_num, b.remedy_error FROM user_info a LEFT JOIN seme_student s ON a.user_id = s.stud_id, user_status b, user_access c WHERE a.organization_id =? $seme_rule";
  //left join seme_student 取得座號，依現在學期
  if ($_POST['isAllidentify'] == 0) {

    if($identify == USER_STUDENT){
      $sql .= " AND s.grade = " . $grade;
    }else{
      $sql .= " AND a.grade = " . $grade;
    }
    if ($classNum != '') {
      if($identify == USER_STUDENT){
        $sql .= " AND s.class = " . $classNum;
      }else{
        $sql .= " AND a.class = " . $classNum;
      }
    }
    if ($identify != '') {
      $sql .= " AND c.access_level IN ($sLevel)";
    }
  } else {
    if ($identify != '') {
      $sql .= " AND c.access_level IN ($sLevel)";
    }
  }

  //#543 CCW : 篩選在校 啟用或停用的帳號名單
  if($identify == USER_SCHOOL_ADMIN || $identify == USER_TEACHER){
    if($typeAble == "1"){
      $sql .= " AND a.used = 1";
    }else if($typeAble == "2"){
      $sql .= " AND a.used = 0";
    }
  }

  if($identify == USER_STUDENT){
    if($typeSt == "1"){
      $sql .= " AND a.used = 1";
    }else if($typeSt == "2"){
      $sql .= " AND a.used = 0";
    }
  }

  $sql .= " AND a.user_id = b.user_id AND b.access_level = c.access_level AND a.user_id";

  if($identify == 1  && $sStuid != ''){
    if($typeSt == "1" || $typeSt == "2"){
      $sql .= " NOT";
    }
    $sql .= " IN(".$sStuid.") ORDER BY a.grade, a.class, s.seme_num, c.access_level ASC";
  }else{
    if($typeSt == "0"){
      return 'empty_graducation_student';
    }else{
      $sql .= " ORDER BY a.grade, a.class, s.seme_num, c.access_level ASC";
    }
  }

  // $vReturnData['sql'] = $sql;
  $sStuData = $dbh->prepare($sql);

  $sStuData->execute(array($organization_id));
  $vStuData = $sStuData->fetchAll(PDO::FETCH_ASSOC);
  $Data = array();
  foreach ($vStuData as $key => $value) {
    $sID = explode('-', $value['user_id']);
    $Data[$value['user_id']]['org_id'] = $sID[0];
    $Data[$value['user_id']]['view_id'] = $sID[1];
    $Data[$value['user_id']]['user_id'] = $value['user_id'];
    $Data[$value['user_id']]['uname'] = $value['uname'];
    $Data[$value['user_id']]['sex'] = $value['sex'];
    $Data[$value['user_id']]['email'] = $value['email'];
    $Data[$value['user_id']]['grade'] = $value['grade'];
    $Data[$value['user_id']]['class'] = $value['class'];
    $Data[$value['user_id']]['seme_num'] = $value['seme_year_seme']?($value['seme_num']?:'未設定'):'無';//判斷這學期有沒有更新，有沒有設定

    if (trim($value['priori_name']) != '') {
      $identity = substr($value['priori_name'],0 ,strpos($value['priori_name'], $value['uname']));
    } else {
      $identity = '';
    }
    $Data[$value['user_id']]['identity']= $identity;

    // 2018-10-02 Edward: 確定有綁定到扶助學習的測驗資料才顯示"已綁定"，其餘顯示"尚未有測驗資料"
    $sShowPriori = '';
    if (trim($value['priori_name']) != '') {
      $sPrioriSQL = "SELECT cp_id FROM exam_record_priori WHERE exam_user = :exam_user";
      $oPriori = $dbh->prepare($sPrioriSQL);
      $oPriori->bindValue(':exam_user', $value['priori_name'], PDO::PARAM_STR);
      $oPriori->execute();
      $vPriori = $oPriori->fetchAll(PDO::FETCH_ASSOC);

      if (count($vPriori) > 0) {
        $vShow = array();
        foreach ($vPriori as $Pvalue) {
          if (substr($Pvalue['cp_id'],0,2) == '01') {
            $vShow[] = '國';
          }
          if (substr($Pvalue['cp_id'],0,2) == '02') {
            $vShow[] = '數';
          }
          $sShowPriori = implode(',',$vShow)." 已綁定";
        }

      }
      else {
        $sShowPriori = '無測驗';
      }
    }
    else {
      $sShowPriori = '無身分證字號';
    }

    $Data[$value['user_id']]['priori_name'] = $sShowPriori;
    $Data[$value['user_id']]['remedy_error'] = $value['remedy_error']?"<span title='因為扶助學習成績對照條件有限，有相同姓名者需要補充身分證字號' style='cursor: help;'>💡</span>":'';
    $Data[$value['user_id']]['remedy_error'] = (!empty($value['priori_name']) && !empty($value['hash_guid']))?'':$Data[$value['user_id']]['remedy_error'];
    // #308
    if ($user_data->access_level >= 71 || $user_data->access_level == USER_SCHOOL_ADMIN || in_array($user_data->access_level, USER_TEACHER_GROUP)) {
      if ($value['pass'] != md5($value['viewpass'])) {
        $Data[$value['user_id']]['viewpass'] = pass2compiler($value['viewpass']);
      } else {
        $Data[$value['user_id']]['viewpass'] = $value['viewpass'];
      }
    }
    $Data[$value['user_id']]['used'] = $value['used'];
    if (!empty($value['OpenID_sub'])) {
      $Data[$value['user_id']]['OpenID_sub'] = '已綁定';
    } else {
      $Data[$value['user_id']]['OpenID_sub'] = '';
    }
    $Data[$value['user_id']]['access_title'] = $value['access_title'];
    $Data[$value['user_id']]['access_level'] = $value['access_level'];
  }
  $vReturnData['Stu'] = $Data;
  return $vReturnData;
}

function actSearchByName($user_data) {
  global $dbh;
  $name = $_POST['name'];
  $vReturnData = array();
  $sql =
    "SELECT
      a.user_id,
      a.uname,
      a.organization_id,
      a.sex,
      a.email,
      a.grade,
      a.class,
      a.identity,
      a.hash_guid,
      a.priori_name, ";

  if ($user_data->access_level >= 71) {
    $sql .= " a.pass, a.viewpass,";
    $vReturnData['level'] = 1;
    if ($user_data->access_level < 91) {
      $vReturnData['level'] = 2;
    }
  } elseif ($user_data->access_level == USER_SCHOOL_ADMIN) {
    $sql .= " a.pass, a.viewpass,"; // #308
    $vReturnData['level'] = 3;
  } else if (in_array($user_data->access_level, USER_TEACHER_GROUP)) {
    $sql .= " a.pass, a.viewpass,";
    $vReturnData['level'] = 4;
  }
  $sql .=
    " a.used, a.OpenID_sub, c.access_title, c.access_level, s.seme_year_seme, s.seme_num, b.remedy_error
    FROM user_info a
    LEFT JOIN seme_student s
    ON a.user_id = s.stud_id AND s.seme_year_seme = '$user_data->semeYear', user_status b, user_access c
    WHERE a.uname like ? AND a.user_id = b.user_id AND b.access_level = c.access_level";

  if ($user_data->access_level == USER_SCHOOL_ADMIN || in_array($user_data->access_level, USER_TEACHER_GROUP)) {
    $sql .= " AND a.organization_id = " . $user_data->organization_id;
  }
  $sql .= " ORDER BY a.user_id, c.access_level ASC";

  if (in_array($user_data->access_level, USER_TEACHER_GROUP)) {

    // #558 Ariel: 教師管理學生帳號時，加入 user_info 預設班級
    $sql =
      "SELECT
        a.user_id
        ,a.uname
        ,a.organization_id
        ,a.sex
        ,a.email
        ,a.grade
        ,a.class
        ,a.identity
        ,a.hash_guid
        ,a.priori_name
        ,a.pass
        ,a.viewpass
        ,a.used
        ,a.OpenID_sub
        ,c.access_title
        ,s.seme_year_seme
        ,s.seme_num
        ,b.remedy_error
      FROM
          (SELECT
            user_info.user_id
            ,user_info.grade
            ,user_info.class
          FROM user_info
          WHERE user_info.user_id = :teacher_id) AS teacher_u
        LEFT JOIN
          (SELECT DISTINCT
            seme_teacher_subject.grade
            ,seme_teacher_subject.class
            ,seme_teacher_subject.teacher_id
          FROM seme_teacher_subject
          WHERE seme_teacher_subject.seme_year_seme = :seme_year) AS sts
        ON sts.teacher_id = teacher_u.user_id
        LEFT JOIN seme_student AS s
        ON (s.seme_year_seme = :seme_year_2
          AND ((s.grade = sts.grade AND s.class = sts.class)
            OR (s.grade = teacher_u.grade AND s.class = teacher_u.class)))
        LEFT JOIN user_info AS a
        ON a.user_id = s.stud_id
        LEFT JOIN user_status AS b
        ON b.user_id = a.user_id
        LEFT JOIN user_access AS c
        ON c.access_level = b.access_level
      WHERE a.organization_id = :user_organization_id
        AND a.uname like :name_like
      ORDER BY a.user_id, c.access_level ASC ";
  }

  $sStuData = $dbh->prepare($sql);
  if (in_array($user_data->access_level, USER_TEACHER_GROUP)) {
    $sStuData->bindValue(':name_like', '%'.$name.'%', PDO::PARAM_STR);
    $sStuData->bindValue(':user_organization_id', $user_data->organization_id, PDO::PARAM_STR);
    $sStuData->bindValue(':seme_year', $user_data->semeYear, PDO::PARAM_STR);
    $sStuData->bindValue(':seme_year_2', $user_data->semeYear, PDO::PARAM_STR);
    $sStuData->bindValue(':teacher_id', $user_data->user_id, PDO::PARAM_STR);
    $sStuData->execute();
  } else {
    $sStuData->execute(array("%" . $name . "%"));
  }

  $vStuData = $sStuData->fetchAll(PDO::FETCH_ASSOC);
  $Data = array();
  foreach ($vStuData as $key => $value) {
    if (strpos($value['user_id'],'-') === false) {
      $sID[0] = $value['organization_id'];
      $sID[1] = $value['user_id'];
    }
    else {
      $sID = explode('-', $value['user_id']);
    }
    $Data[$value['user_id']]['org_id'] = $sID[0];
    $Data[$value['user_id']]['view_id'] = $sID[1];
    $Data[$value['user_id']]['user_id'] = $value['user_id'];
    $Data[$value['user_id']]['uname'] = $value['uname'];
    $Data[$value['user_id']]['sex'] = $value['sex'];
    $Data[$value['user_id']]['email'] = $value['email'];
    $Data[$value['user_id']]['grade'] = $value['grade'];
    $Data[$value['user_id']]['class'] = $value['class'];
    $Data[$value['user_id']]['seme_num'] = $value['seme_year_seme']?($value['seme_num']?:'未設定'):'無';//判斷這學期有沒有更新，有沒有設定

    if (trim($value['priori_name']) != '') {
      $identity = substr($value['priori_name'],0 ,strpos($value['priori_name'], $value['uname']));
    } else {
      $identity = '';
    }

    $Data[$value['user_id']]['identity']= $identity;
    $Data[$value['user_id']]['remedy_error'] = $value['remedy_error']?"<span title='因為扶助學習成績對照條件有限，有相同姓名者需要補充身分證字號' style='cursor: help;'>💡</span>":'';
    $Data[$value['user_id']]['remedy_error'] = (!empty($value['priori_name']) && !empty($value['hash_guid']))?'':$Data[$value['user_id']]['remedy_error'];



    // 2018-10-02 Edward: 確定有綁定到扶助學習的測驗資料才顯示"已綁定"，其餘顯示"尚未有測驗資料"
    $sShowPriori = '';
    if ($value['priori_name'] != '') {
      $sPrioriSQL = "SELECT cp_id FROM exam_record_priori WHERE exam_user = :exam_user";
      $oPriori = $dbh->prepare($sPrioriSQL);
      $oPriori->bindValue(':exam_user', $value['priori_name'], PDO::PARAM_STR);
      $oPriori->execute();
      $vPriori = $oPriori->fetchAll(PDO::FETCH_ASSOC);

      if (count($vPriori) > 0) {
        $vShow = array();
        foreach ($vPriori as $Pvalue) {
          if (substr($Pvalue['cp_id'],0,2) == '01') {
            $vShow[] = '國';
          }
          if (substr($Pvalue['cp_id'],0,2) == '02') {
            $vShow[] = '數';
          }
          $sShowPriori = "(".implode(',',$vShow)." 已綁定)";
        }

      }
      else {
        $sShowPriori = '無測驗';
      }
    }
    else {
      $sShowPriori = '無身分證字號';
    }

    $Data[$value['user_id']]['priori_name'] = $sShowPriori;

    // #308
    if ($user_data->access_level >= 71 || $user_data->access_level == USER_SCHOOL_ADMIN || in_array($user_data->access_level, USER_TEACHER_GROUP)) {
      if ($value['pass'] != md5($value['viewpass'])) {
        $Data[$value['user_id']]['viewpass'] = pass2compiler($value['viewpass']);
      } else {
        $Data[$value['user_id']]['viewpass'] = $value['viewpass'];
      }
    }
    $Data[$value['user_id']]['used'] = $value['used'];
    if (!empty($value['OpenID_sub'])) {
      $Data[$value['user_id']]['OpenID_sub'] = '已綁定';
    } else {
      $Data[$value['user_id']]['OpenID_sub'] = '';
    }
    $Data[$value['user_id']]['access_title'] = $value['access_title'];
    $Data[$value['user_id']]['access_level'] = $value['access_level'];
  }
  $vReturnData['Stu'] = $Data;
  return $vReturnData;
}

function actEditStu($user_data) {
  global $dbh;

  $editData = $_POST['editData'];
  $datetime = date("Y-m-d H:i:s");

  $sStuName = $dbh->prepare("SELECT priori_name FROM user_info WHERE user_id= :user_id");
  $sStuName->bindValue(':user_id', $editData['user_id'], PDO::PARAM_STR);
  $sStuName->execute();
  $vStuName = $sStuName->fetch(PDO::FETCH_ASSOC);

  // 創帳號時、改帳號身分證/名字 時需比對補救教學身分，將測驗資料的user_id對應一遍
  $sPrioriName = $vStuName['priori_name'];
  if ($editData['originalname'] != $editData['uname']) {
    // 避免重複改值
    if (!preg_match("/($editData[uname])/", $vStuName['priori_name'])) {
      $sPrioriName = preg_replace("/($editData[originalname])/",$editData['uname'] ,$vStuName['priori_name']);
      mappingExamUserPriori('exam_user', $editData['user_id'], $editData['org_id'], $sPrioriName);
    }
  }
  if (!empty($editData['identity']) && strlen($editData['identity']) >= 10) {
    $sPrioriName = substr_replace($editData['identity'], '*****', 0, 5) . $editData['uname'];
    mappingExamUserPriori('exam_user', $editData['user_id'], $editData['org_id'], $sPrioriName);
  }

  $sUpdateStu = $dbh->prepare("UPDATE user_info SET
    uname=:uname,
    sex=:sex,
    email=:email,
    update_id=:update_id,
    update_date=:update_date,
    priori_name=:priori_name
    WHERE user_id=:user_id"
  );
  $sUpdateStu->bindValue(':uname', $editData['uname'], PDO::PARAM_STR);
  $sUpdateStu->bindValue(':sex', $editData['sex'], PDO::PARAM_STR);
  $sUpdateStu->bindValue(':email', $editData['email'], PDO::PARAM_STR);
  $sUpdateStu->bindValue(':update_id', $user_data->user_id, PDO::PARAM_STR);
  $sUpdateStu->bindValue(':update_date', $datetime, PDO::PARAM_STR);
  $sUpdateStu->bindValue(':user_id', $editData['user_id'], PDO::PARAM_STR);
  $sUpdateStu->bindValue(':priori_name', $sPrioriName, PDO::PARAM_STR);
  $sUpdateStu->execute();

  if (!empty($editData['pass'])) {
    $sUpdateStuPass = $dbh->prepare("UPDATE user_info SET
      pass=:pass,
      viewpass=:viewpass
      WHERE user_id=:user_id"
    );
    $sUpdateStuPass->bindValue(':pass', md5($editData['pass']), PDO::PARAM_STR);
    $sUpdateStuPass->bindValue(':viewpass', pass2compiler($editData['pass']), PDO::PARAM_STR);
    $sUpdateStuPass->bindValue(':user_id', $editData['user_id'], PDO::PARAM_STR);
    $sUpdateStuPass->execute();
  }
  if (!empty($editData['identity']) && strlen($editData['identity']) >= 10) {
    $update = updatePrioriData($editData['user_id'], $editData['uname'], preg_replace('/\s/', '', $editData['identity']));

    $sUpdateStuIdentity = $dbh->prepare("UPDATE user_info SET hash_guid=?, update_date=? WHERE `user_id`=?");
    $sUpdateStuIdentity->execute(array(
      hash("sha256", trim($editData['identity'])),
      date("Y-m-d H:i:s"),
      $editData['user_id']
    ));
  }

  // 座號
  if(!empty($editData['seme_num'])) {
    $sUpdateStuNum = $dbh->prepare("UPDATE seme_student SET seme_num = ? WHERE seme_year_seme = ? AND stud_id = ?");
    $sUpdateStuNum->execute(array(
      $editData['seme_num'],
      $user_data->semeYear,
      $editData['user_id']
    ));
    echo $sUpdateStuNum->rowCount();
  }

}

function actStuDel($vDelArr) {
  //#543 CCW 補上user_data變數
  global $dbh, $user_data;
  $status = '';
  $user_id = $_POST['user_id'];
  if ($user_data->access_level < 91) {
    $vCountsql[] = $dbh->prepare("SELECT count(*) sum FROM exam_record WHERE `user_id`=?");
    $vCountsql[] = $dbh->prepare("SELECT count(*) sum FROM exam_record_indicate WHERE `user_id`=?");
    $vCountsql[] = $dbh->prepare("SELECT count(*) sum FROM exam_record_dynamic WHERE `user_id`=?");
    $vCountsql[] = $dbh->prepare("SELECT count(*) sum FROM exam_record_indicate_tmp WHERE `user_id`=?");
    $vCountsql[] = $dbh->prepare("SELECT count(*) sum FROM exam_record_its WHERE `user_id`=?");
    $vCountsql[] = $dbh->prepare("SELECT count(*) sum FROM exam_record_tmp WHERE `user_id`=?");
    foreach ($vCountsql as $value) {
      $value->execute(array($user_id));
      $vValue = $value->fetchAll(PDO::FETCH_ASSOC);
      $iCountsql += $vValue['sum'];
      $echo[] = $vValue;
    }
    if ($iCountsql == 0) {
      $vDelArr[] = $dbh->prepare("DELETE FROM user_family WHERE `user_id`=?");
      foreach ($vDelArr as $value) {
        $value->execute(array($user_id));
      }
      $status = '1';
    } else {
      $status = '0';
    }
  } else {
    $vDelArr[] = $dbh->prepare("DELETE FROM user_family WHERE `user_id`=?");
    foreach ($vDelArr as $value) {
      $value->execute(array($user_id));
    }
    $status = '1';
  }
  return $status;
}

function actDelClass($vDelArr) {
  //#543 CCW 增加判斷身分 整班刪除 學生或老師 ; 補上user_data變數;
  global $dbh, $user_data;
  $status = '';
  $organization_id = $_POST['organization_id'];
  $grade = $_POST['grade'];
  $class = $_POST['class'];
  $identify = $_POST['identify'];
  $sSelectClass = $dbh->prepare("SELECT a.user_id FROM user_info a, user_status b WHERE a.organization_id=? AND a.grade=? AND a.class=? AND a.user_id = b.user_id AND b.access_level =? AND b.access_level !=" . USER_SCHOOL_ADMIN);
  $sSelectClass->execute(array($organization_id, $grade, $class, $identify));
  $vSelectClass = $sSelectClass->fetchAll(PDO::FETCH_ASSOC);
  if (!empty($vSelectClass)) {
    if ($user_data->access_level < 91) {
      $sql = $dbh->prepare("UPDATE user_info SET used =0 WHERE `user_id`=?");
      foreach ($vSelectClass as $val) {
        $sql->execute(array($val['user_id']));
      }
    } else {
      $vDelArr[] = $dbh->prepare("DELETE FROM user_family WHERE `user_id`=?");
      foreach ($vDelArr as $value) {
        foreach ($vSelectClass as $val) {
          $value->execute(array($val['user_id']));
        }
      }
    }
    $status = '1';
  } else {
    $status = '0';
  }
  return $status;
}

#558 Ariel   教師管理學生帳號時，加入 user_info 預設班級
#812 Amber   修正 - 學生帳號管理選單顯示所有任課班級
function actTeacherGradeOption() {
  global $dbh;

  $user_id = $_POST['user_id'];
  $now_sems = getYearSeme();
  $vGrade = array();

  $sql =
    "SELECT DISTINCT seme_teacher_subject.grade
    FROM seme_teacher_subject
      WHERE seme_teacher_subject.teacher_id = :user_id
      AND seme_teacher_subject.seme_year_seme = :year_seme
    ORDER BY seme_teacher_subject.grade ASC";

  $query = $dbh->prepare($sql);
  $query->bindValue(':user_id', $user_id, PDO::PARAM_STR);
  $query->bindValue(':year_seme', $now_sems, PDO::PARAM_STR);
  $query->execute();
  $i = 0;
  while ($row = $query->fetch()) {
    $vGrade[$i]['grade'] = $row['grade'];
    $i++;

  }

  $sql2 =
    "SELECT user_info.grade
    FROM user_info
    WHERE user_info.user_id = :user_id
      AND user_info.used = 1";
  $query2 = $dbh->prepare($sql2);
  $query2->bindValue(':user_id', $user_id, PDO::PARAM_STR);
  $query2->execute();
  $query2 = $query2->fetch();

  //判斷若重複導師班即跳出
  $bpushGrade = 0;
  foreach ($vGrade as $key => $value) {
    if($vGrade[$key]['grade'] == $query2['grade']){
      $bpushGrade = 1;
      break;
    }
  }

  if(!$bpushGrade){
    $vGrade[$i]['grade'] = $query2['grade'];
    $i++;
  }
  return $vGrade;
}

#558 Ariel   教師管理學生帳號時，加入 user_info 預設班級
#812 Amber   修正 - 學生帳號管理選單顯示所有任課班級
function actTeacherClassOption($user_data) {
  global $dbh;

  $user_id = $_POST['user_id'];
  $now_sems = getYearSeme();
  $vGrade = array();
  $vClass = array();

  $vBind = array(
    ':grade'      => $_POST['grade'],
    ':user_id'    => $_POST['user_id'],
    ':year_seme'  => getYearSeme(),
  );

  $sql =
    "SELECT DISTINCT seme_teacher_subject.class
    FROM seme_teacher_subject
    WHERE seme_teacher_subject.teacher_id = :user_id
      AND seme_teacher_subject.seme_year_seme = :year_seme
      AND seme_teacher_subject.grade = :grade
    ORDER BY seme_teacher_subject.class ASC";


   $query = $dbh->prepare($sql);
   $query->bindValue(':user_id', $user_id, PDO::PARAM_STR);
   $query->bindValue(':year_seme', $now_sems, PDO::PARAM_STR);
   $query->execute($vBind);

   $i = 0;
   while ($row = $query->fetch()) {
     $vClass[$i]['class'] = $row['class'];
     $i++;

   }

  $sql2 =
    "SELECT user_info.class, user_info.grade
    FROM user_info
    WHERE user_info.user_id = :user_id
      AND user_info.used = 1";
  $query2 = $dbh->prepare($sql2);
  $query2->bindValue(':user_id', $user_id, PDO::PARAM_STR);
  $query2->execute();
  $query2 = $query2->fetch();

  //判斷若重複導師班即跳出

  if($_POST['grade'] == $query2['grade'])
  {
    $bpushClass = 0;
    foreach ($vClass as $key => $value) {
      if($vClass[$key]['class'] == $query2['class']){
      $bpushClass = 1;
      break;
      }
    }

    if(!$bpushClass){
    $vClass[$i]['class'] = $query2['class'];
    $i++;
    }
  }
  return $vClass;
}




#709 CCW 取消OPENID功能
function clean_openid($user_data){
  global $dbh;

  try{

    $sql = "UPDATE user_info SET OpenID_sub = '' WHERE user_id=:user_id";
    $query = $dbh->prepare($sql);
    $query->bindValue(':user_id', $_POST['user_id'], PDO::PARAM_STR);
    return $query->execute();

  }catch(PDOException $e){

    return $e->getMessage();

  }
}

#709 校管 調整 老師、校長、主任 的權限
function update_identity($user_data){
  global $dbh;
  try{
    $sql = "UPDATE user_status SET access_level = :newaccess_level WHERE user_id=:update_userid";
    $query = $dbh->prepare($sql);
    $query->bindValue(':newaccess_level', $_POST['new_indentity'], PDO::PARAM_INT);
    $query->bindValue(':update_userid', $_POST['user_id'], PDO::PARAM_STR);
    return $query->execute();

  }catch(PDOException $e){

    return $e->getMessage();

  }
}
