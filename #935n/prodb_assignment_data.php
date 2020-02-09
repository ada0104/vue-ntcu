<?php
include_once "../../include/config.php";
include_once "../../include/adp_core_function.php";
include_once "../../include/adp_core_security.php";
?>
<?php
session_start();
// echo"<per>";
// print_r($_POST["ms_node_arr"]);
// echo"</per>";
// echo 'modules\assignMission\prodb_assignment_data.php';
$ind_node='';
$target_data ='';
$insert_mark=false;
$user_id = $_SESSION['user_id'];
$iCheckType =0;
// 判斷學習任務所選type BlueS 20180517
if (isset($_POST["ms_node_arr"])) {
    $ms_node_arr=$_POST["ms_node_arr"];
    $i=count($ms_node_arr);
    for ($j=0 ; $j<$i ; $j=$j+3) {
        // echo $ms_node_arr[$j];
        $ms_type_arr_check[$ms_node_arr[$j]][$ms_node_arr[$j+1]]=$ms_node_arr[$j+2];
    if($ms_node_arr[$j+1]!='ques' && $ms_node_arr[$j+2]==1){
      $iCheckType++;
    }
    }
// print_r($ms_type_arr_check);
// echo '$iCheckType=='.$iCheckType;
$ms_type_arr=serialize($ms_type_arr_check);
}

//180427，KP，把user_info 的 session data加上來
$vUserData = get_object_vars($_SESSION['user_data']);
$organization_id = $vUserData['organization_id'];

if (isset($_SESSION['user_id'])) {
    $user_data = $dbh->prepare("SELECT info.user_id, info.uname,info.organization_id, org.name org_name , info.city_code, city.city_name, info.grade, info.class, s.access_level, acc.access_title
        FROM user_info info, user_status s, user_access acc, city , organization org
        where info.city_code = city.city_code and info.organization_id = org.organization_id and info.user_id = s.user_id and s.access_level = acc.access_level and info.user_id = :user_id");
    $user_data->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_STR);
    $user_data->execute();
    $userinfo = $user_data->fetch();
    $level = $userinfo["access_level"];
    $org = $userinfo["organization_id"];
    $grade = $userinfo["grade"];
    $class = $userinfo["class"];
}

if ($_POST["mission_type"] == '3') {
    $concept_query = $dbh->prepare("SELECT cs_id FROM `concept_info` WHERE unit = :unit AND subject_id = :subject_id");
    $concept_query->bindValue(':unit', '99', PDO::PARAM_STR);
    $concept_query->bindValue(':subject_id', $_POST["subject_id"], PDO::PARAM_STR);
    $concept_query->execute();
    $concept_data = $concept_query->fetch();
    $full_cs = $concept_data["cs_id"];
}

if ($_POST["type"] == '2' && isset($_POST["yearnm"])) {
    $resultdata2 = array();
    $mission_str2 = "SELECT DISTINCT organization_id, grade, class FROM seme_teacher_subject WHERE teacher_id = :teacher_id AND seme_year_seme = :seme ORDER BY grade,class ASC";
    $mission_data2 = $dbh->prepare($mission_str2);
    $mission_data2->bindValue(':seme', $_POST["yearnm"], PDO::PARAM_STR);
    $mission_data2->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
    $mission_data2->execute();
    $mission_count =$mission_data2->rowCount();

    $class_nm="SELECT organization_id, grade,class FROM user_info WHERE user_id = :teacher_id";
    $class_data = $dbh->prepare($class_nm);
    $class_data->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
    $class_data->execute();

    $i=0;
    if ($mission_count==0) {
        while ($row3 =$class_data->fetch(\PDO::FETCH_ASSOC)) {
            if ($row3["class"]<10) {
                $row3["class"]="0".$row3["class"];
            }
            //    $resultdata2[$i] = $row2["organization_id"]."-".$row2["grade"].$row2["class"];
            $resultdata2[$i] = $row3;
        }
    } else {
        while ($row2 = $mission_data2->fetch(\PDO::FETCH_ASSOC)) {
            if ($row2["class"]<10) {
                $row2["class"]="0".$row2["class"];
            }
            //    $resultdata2[$i] = $row2["organization_id"]."-".$row2["grade"].$row2["class"];
            $resultdata2[$i] = $row2;
            $i++;
        }
    }
    echo  json_encode($resultdata2);
}

if ($_POST["type"] == '1' && isset($_POST["classnm"])) {
    $resultData = array();
    if ($_POST["classnm"] !='all') {
        //班級任務
        $mission_str = "SELECT a.mission_sn, a.mission_nm, a.target_type, a.node, a.target_id, a.mission_type
      FROM mission_info as a
      WHERE unable=1 AND end_mk='N' AND target_id = :target_id AND teacher_id =:teacher_id AND semester = :yearnm
      ORDER BY a.mission_sn DESC";
        $mission_data = $dbh->prepare($mission_str);
        $mission_data->bindValue(':target_id', $_POST["classnm"], PDO::PARAM_STR);
        $mission_data->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
        $mission_data->bindValue(':yearnm', $_POST["yearnm"], PDO::PARAM_INT);
        $mission_data->execute();
    } else {
        $mission_str = "SELECT  a.mission_sn, a.mission_nm, a.target_type, a.node, a.target_id, a.mission_type
      FROM mission_info as a
      WHERE unable=1 AND end_mk='N' AND teacher_id =:teacher_id AND target_type ='C' AND semester = :yearnm
      ORDER BY a.mission_sn DESC";
        $mission_data = $dbh->prepare($mission_str);
        $mission_data->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
        $mission_data->bindValue(':yearnm', $_POST["yearnm"], PDO::PARAM_INT);
        $mission_data->execute();
    }
    //$sub_mission = $mission_data->fetchAll(\PDO::FETCH_ASSOC);
    //個人任務
    $mission_str2 = "SELECT  a.mission_sn, a.mission_nm, a.target_type, a.node, a.target_id, a.mission_type
      FROM mission_info as a
      WHERE unable=1 AND end_mk='N' AND teacher_id =:teacher_id AND target_type ='I' AND semester = :yearnm
      ORDER BY a.mission_sn DESC";
    $mission_data2 = $dbh->prepare($mission_str2);
    $mission_data2->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
    $mission_data2->bindValue(':yearnm', $_POST["yearnm"], PDO::PARAM_INT);
    $mission_data2->execute();
    //$sub_mission2 = $mission_data2->fetchAll(\PDO::FETCH_ASSOC);
    $i=0;
    while ($row = $mission_data->fetch(\PDO::FETCH_ASSOC)) {
        $resultData[$i] = $row;
        $i++;
    }

    while ($row = $mission_data2->fetch()) {
        $resultData[$i] = $row;
        $i++;
    }

    echo  json_encode($resultData);
}

$topic_count=0;
if (isset($_POST["mission_nm"])&&isset($_POST["target_id"])) {
    $mission_nm =  xss_filter($_POST['mission_nm']); //2019-10-22 #981 KP:過濾XSS Payload
    $user_id = $_SESSION['user_id'];
    $time = date("YmdHis");
    $co = count($_POST["node"]);
    if (count($_POST["node"]) >1) {
        foreach ($_POST["node"] as $key=> $value) {
            $co2 = _SPLIT_SYMBOL;
            $ind_node = $ind_node.$value._SPLIT_SYMBOL;
        }
    } else {
        $ind_node = $_POST["node"][0];
    }

    $count3 = count($_POST["endYear"]);
    if ($count3 >0) {
        if (count($_POST["endYear"]) >1) {
            foreach ($_POST["endYear"] as $key=> $value) {
                $co2 = _SPLIT_SYMBOL;
                $endYear_node = $endYear_node.$value._SPLIT_SYMBOL;
            }
        } else {
            $endYear_node = $_POST["endYear"][0];
        }
    } else {
        $endYear_node='';
    }


    if (count($_POST["data_type"]) >0) {
        foreach ($_POST["data_type"] as $key=> $value) {
            if ($value =='ques') {
                $insert_mark = true;
            }
        }
    }


    //  if($_POST["target_id"][0] =="patch"){
//    $_POST["type"]=2;//patch
//    array_splice($_POST["target_id"],0, 1);
//  }else{
        if ($_POST["mission_class"] =='I') { //多個學生
            $stu_co = count($_POST["target_id"]);
            if (count($_POST["target_id"]) >0) {
                foreach ($_POST["target_id"] as $key2=> $value2) {
                    $stu_co2 = _SPLIT_SYMBOL;
                    $target_student = $target_student.$value2._SPLIT_SYMBOL;
                }
            } else {
                $target_student = $_POST["target_id"][0];
            }
            $target_data = $target_student;

            if(isset($_POST['group_id'])){
                $group_data = implode(_SPLIT_SYMBOL, $_POST["group_id"]);
            }

        } elseif ($_POST["mission_class"] =='B') { //多個班級
            $_POST["type"]=2;//patch
            $_POST["mission_class"]="C";
        }elseif ($_POST["mission_class"] =='VirC') { //虛擬班級
            $_POST["type"]=2;//patch
            //$_POST["mission_class"]="C";
            $target_data=$_POST["target_id"][0];
        }  else {
            $target_data = $_POST["target_id"];
        } //一個班級
//  }


    $topic_num=0;
    //單元題數計算 BlueS 20170623
    if ($_POST["mission_type"]=="0") {
        $topic=$dbh->prepare("SELECT count(*) num FROM `concept_item` where exam_paper_id =:exam_paper_id");
        $topic->bindValue(':exam_paper_id', $ind_node, PDO::PARAM_STR);
        $topic->execute();
        $topic_count=$topic->fetch();
        $topic_num=$topic_count["num"];
    } elseif ($_POST["mission_type"]=="3" || $_POST["mission_type"]=="5") {//縱貫題數計算 BlueS 20170628 and 加上題庫縱貫 fuyun 20180620
        $bNodeAry = explode(_SPLIT_SYMBOL, $ind_node);
        if (empty($bNodeAry[count($bNodeAry)]) && count($bNodeAry)>1) {
            unset($bNodeAry[count($bNodeAry)-1]);
        }
        for ($i=0;$i<=100;$i++) {
            if ($bNodeAry[$i]==null) {
                unset($bNodeAry[$i]);
                break;
            }

            if ($_POST["endYear"][$i]!=0) {
                $tmp_nodeYear = sNode2bNode($bNodeAry[$i], $_POST["subject_id"]);
                $nodeYear = $tmp_nodeYear[0];
                if ($_POST["endYear"][$i] > $nodeYear) {
                    unset($bNodeAry[$i]);
                    continue;
                }
            }

            $sql='
        SELECT indicate_low
        FROM indicateTest
        WHERE indicate="'.$bNodeAry[$i].'"
      ';
            $re=$dbh->query($sql);
            $data=$re->fetch();
            $tmp = explode(_SPLIT_SYMBOL, $data[indicate_low]);

            foreach ($tmp as $val) {
                if (in_array($val, $bNodeAry)) {
                    continue;
                }
                if ($val==null) {
                    continue;
                }
                $bNodeAry[] = $val;
            }
        }
        foreach ($bNodeAry as $key=>$value) {
          //消除 paper_vol=1 and 加上限制兩題 fuyun 20180620
          if($_POST["mission_type"]=="3"){
            $sql3='SELECT count(*) sum FROM concept_item
                   WHERE indicator like "'.$value.'%" AND double_item = 0
                   AND (cs_id LIKE "023%" OR cs_id LIKE "021%" OR cs_id LIKE "020%") LIMIT 2';
            $re3=$dbh->query($sql3);
            $sum=$re3->fetch();
            $topic_num=$topic_num+$sum["sum"];
          }elseif($_POST["mission_type"]=="5"){
            $sql3='SELECT count(*) sum FROM concept_itemBank
                   WHERE indicator like "'.$value.'%" LIMIT 2';
            $re3=$dbh->query($sql3);
            $sum=$re3->fetch();
            $topic_num=$topic_num+$sum["sum"];
          }
          /*
            $sql3='SELECT count(*) sum FROM concept_item
      WHERE indicator like "'.$value.'%" AND double_item = 0 AND paper_vol=1
        AND (cs_id LIKE "023%" OR cs_id LIKE "021%" OR cs_id LIKE "020%")';
            $re3=$dbh->query($sql3);
            $sum=$re3->fetch();
            $topic_num=$topic_num+$sum["sum"];
          */
        }
    } elseif ($_POST["mission_type"] == '4') {
      $sPaperDataSQL = "SELECT * FROM concept_paper WHERE paper_sn = :paper_sn";
      $oPaperData = $dbh->prepare($sPaperDataSQL);
      $oPaperData->bindValue(':paper_sn', $_POST["node"][0], PDO::PARAM_STR);
      $oPaperData->execute();
      $vPaperData = $oPaperData->fetch(\PDO::FETCH_ASSOC);

      $vTopicCount = explode(_SPLIT_SYMBOL, $vPaperData['item_sn']);
      $topic_num = count($vTopicCount);
    }

    if (empty($_POST["run_examtype"])) {
        $exam_type = '0';
    } else {
        $exam_type = $_POST["run_examtype"];
    }
//debugBAI(__LINE__,__FILE__,[$student_list,'123'],'print_r');
if(($iCheckType !='0' && $_POST["mission_type"]=='1') || $_POST["mission_type"] != '1' || ($_POST["mission_type"]=='1' && $_POST['ques_only'] === 'Y')) {
    if ($_POST["type"] == '1') {
      //debugBAI(__LINE__,__FILE__,[$student_list,'123'],'print_r');
      // 2018-11-07 Edward: 新增學習問卷
      if ($insert_mark && $_POST["mission_type"] == '1' || $_POST['ques_only'] === 'Y') {
        $result_ques = $dbh->prepare("INSERT INTO mission_info (mission_nm, target_id, group_id, subject_id, node, date, semester, teacher_id, target_type, mission_type, create_date, start_time)
        VALUES (:mission_nm, :target_id, :group_id, :subject_id, :node, :date, :semester, :teacher_id, :target_type, :mission_type, :create_date, :Sdate)");

        $mis_nm = $mission_nm.'--自主學習問卷';
        $result_ques->bindValue(':mission_nm', $mis_nm, PDO::PARAM_STR);
        $result_ques->bindValue(':group_id', $group_data, PDO::PARAM_STR);
        $result_ques->bindValue(':target_id', $target_data, PDO::PARAM_STR);
        $result_ques->bindValue(':subject_id', $_POST["subject_id"], PDO::PARAM_INT);
        $result_ques->bindValue(':node', 'viewform', PDO::PARAM_STR);
        $result_ques->bindValue(':date', $_POST["enddate"], PDO::PARAM_STR);
        $result_ques->bindValue(':Sdate', $_POST["strdate"], PDO::PARAM_STR);
        $result_ques->bindValue(':semester', $_POST["semester"], PDO::PARAM_STR);
        $result_ques->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
        $result_ques->bindValue(':target_type', $_POST["mission_class"], PDO::PARAM_STR);
        $result_ques->bindValue(':mission_type', '2', PDO::PARAM_STR);
        $result_ques->bindValue(':create_date', $time, PDO::PARAM_STR);
        $result_ques->execute();

        //初始化每個學生的任務狀態，先找出mission_sn
        $mission_info = $dbh->prepare("SELECT * FROM mission_info WHERE teacher_id = :teacher_id AND mission_nm = :mission_nm ORDER BY mission_sn DESC LIMIT 1");
        $mission_info->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
        $mission_info->bindValue(':mission_nm', $mis_nm, PDO::PARAM_STR);
        $mission_info->execute();
        $mission_info_data = $mission_info->fetch(\PDO::FETCH_ASSOC);
        $mission_sn = $mission_info_data["mission_sn"];

        //找出學生的年級與班級
        //20191010 bai 虛擬班級
        if ($_POST["mission_class"] !='I' AND $_POST["mission_class"] !=='VirC') { //如果對象不是多個學生，就是一個班級，就要找出整班的學生
            $class_id = explode("-", $target_data);
            $str_length = strlen($class_id[1]);
            if ($str_length == 3) { //格式為601，6年一班
                $stud_grade = substr($class_id[1], 0, 1);
                $stud_class = substr($class_id[1], -2);
            } elseif ($str_length == 4) { //格是為1101，11年一班
                $stud_grade = substr($class_id[1], 0, 2);
                $stud_class = substr($class_id[1], -2);
            }
            //找出全班的學生
            $search_student = $dbh->prepare("SELECT user_info.* FROM user_info,user_access,user_status WHERE organization_id = :org_id AND grade = :grade AND class = :class AND user_access.access_level = 1 AND user_status.access_level = user_access.access_level AND user_info.user_id = user_status.user_id");
            $search_student->bindValue(':org_id', $org, PDO::PARAM_STR);
            $search_student->bindValue(':grade', $stud_grade, PDO::PARAM_INT);
            $search_student->bindValue(':class', $stud_class, PDO::PARAM_INT);
            $search_student->execute();
            $student_list = $search_student->fetchAll(\PDO::FETCH_ASSOC);
        } elseif( $_POST["mission_class"] =='VirC' ){
          //$remedial_class_sn=explode('_', $target_data);
          $tmp=explode('_',$_POST["target_id"][0]);
          $class_sn=$tmp[1];
          //找出全班學生
          $ssSQL='
            SELECT student_id
            FROM remedial_student
            WHERE class_sn="'.$class_sn.'"
          ';
          $search_student = $dbh->prepare($ssSQL);
          $search_student->execute();
          $student_list = $search_student->fetchAll(\PDO::FETCH_ASSOC);
          //debugBAI(__LINE__,__FILE__,array($_POST["mission_class"],$remedial_class_sn),'print_r');
        }   else {
            $student_list = $_POST["target_id"];
        }

        //寫入初始化學生任務狀態資料表
        if ($_POST["mission_class"] !='I') {
          //debugBAI(__LINE__,__FILE__,$student_list,'print_r');
            foreach ($student_list as $key=>$value) {
                $insert_mission_info_status = $dbh->prepare("INSERT into mission_stud_record (mission_sn,update_time,semester,user_id,finish_node) SELECT mission_sn,create_date,semester,:target_id,:finish_node FROM mission_info WHERE mission_sn = :mission_sn");
                $insert_mission_info_status->bindValue(':mission_sn', $mission_sn, PDO::PARAM_INT);
                $insert_mission_info_status->bindValue(':target_id', $value["user_id"], PDO::PARAM_STR);
                $insert_mission_info_status->bindValue(':finish_node', serialize(""), PDO::PARAM_STR);
                $insert_mission_info_status->execute();
            }
        } else { //單純的學生帳號陣列
          //debugBAI(__LINE__,__FILE__,$student_list,'print_r');
            $acount = 0;
            foreach ($student_list as $key=>$value) {
                $insert_mission_info_status = $dbh->prepare("INSERT into mission_stud_record (mission_sn,update_time,semester,user_id,finish_node) SELECT mission_sn,create_date,semester,:target_id,:finish_node FROM mission_info WHERE mission_sn = :mission_sn");
                $insert_mission_info_status->bindValue(':mission_sn', $mission_sn, PDO::PARAM_INT);
                $insert_mission_info_status->bindValue(':target_id', $student_list[$key], PDO::PARAM_STR);
                $insert_mission_info_status->bindValue(':finish_node', serialize(""), PDO::PARAM_STR);
                $insert_mission_info_status->execute();

                if($insert_mission_info_status -> errorCode() == '00000'){
                    $acount++;
                }
            }
                if($acount != count($student_list)){
                    foreach ($student_list as $key=>$value) {
                        $insert_mission_info_status = $dbh->prepare("INSERT into mission_stud_record (mission_sn,update_time,semester,user_id,finish_node) SELECT mission_sn,create_date,semester,:target_id,:finish_node FROM mission_info WHERE mission_sn = :mission_sn");
                        $insert_mission_info_status->bindValue(':mission_sn', $mission_sn, PDO::PARAM_INT);
                        $insert_mission_info_status->bindValue(':target_id', $student_list[$key], PDO::PARAM_STR);
                        $insert_mission_info_status->bindValue(':finish_node', serialize(""), PDO::PARAM_STR);
                        $insert_mission_info_status->execute();
                    }
                }
            }
        if ($_POST['ques_only'] === 'Y') {
          $vReturn['share']['type'] = '0';
          $vReturn['share']['sharecode'] = '0';

          echo json_encode($vReturn);
          exit;
        }
      }


      // 一般任務新增

      // 家長/大學伴指派任務將self
      if($_POST["self_practice"] == '2'){
        $result = $dbh->prepare("INSERT INTO mission_info (mission_nm, target_id, group_id, subject_id, node, date, semester, teacher_id, target_type, mission_type, assign_type, create_date, start_time, endYear, topic_num, exam_type, self_practice)
      VALUES (:mission_nm, :target_id, :group_id, :subject_id, :node, :date, :semester, :teacher_id, :target_type, :mission_type, :assign_type, :create_date, :Sdate, :endYear, :topic_num, :exam_type, :self_practice)");//Sdate開始時間 BlueS 2017.07.06
        $result->bindValue(':self_practice', $_POST["self_practice"], PDO::PARAM_STR);
      }else{
        $result = $dbh->prepare("INSERT INTO mission_info (mission_nm, target_id, group_id, subject_id, node, date, semester, teacher_id, target_type, mission_type, assign_type, create_date, start_time, endYear, topic_num, exam_type)
      VALUES (:mission_nm, :target_id, :group_id, :subject_id, :node, :date, :semester, :teacher_id, :target_type, :mission_type, :assign_type, :create_date, :Sdate, :endYear, :topic_num, :exam_type)");//Sdate開始時間 BlueS 2017.07.06

      }

      $result->bindValue(':mission_nm', $mission_nm, PDO::PARAM_STR);
      $result->bindValue(':target_id', $target_data, PDO::PARAM_STR);
      $result->bindValue(':group_id', $group_data, PDO::PARAM_STR);
      $result->bindValue(':subject_id', $_POST["subject_id"], PDO::PARAM_INT);
      $result->bindValue(':node', $ind_node, PDO::PARAM_STR);
      $result->bindValue(':date', $_POST["enddate"], PDO::PARAM_STR);
      $result->bindValue(':Sdate', $_POST["strdate"], PDO::PARAM_STR);
      $result->bindValue(':semester', $_POST["semester"], PDO::PARAM_STR);
      $result->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
      $result->bindValue(':target_type', $_POST["mission_class"], PDO::PARAM_STR);
      $result->bindValue(':mission_type', $_POST["mission_type"], PDO::PARAM_STR);
      $result->bindValue(':assign_type', $ms_type_arr, PDO::PARAM_STR);
      $result->bindValue(':create_date', $time, PDO::PARAM_STR);
      $result->bindValue(':endYear', $endYear_node, PDO::PARAM_STR);
      $result->bindValue(':topic_num', $topic_num, PDO::PARAM_INT);
      $result->bindValue(':exam_type', $exam_type, PDO::PARAM_INT);
      $result->execute();



        //初始化每個學生的任務狀態，先找出mission_sn
        $mission_info = $dbh->prepare("SELECT * FROM mission_info WHERE teacher_id = :teacher_id AND mission_nm = :mission_nm ORDER BY mission_sn DESC LIMIT 1");
        $mission_info->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
        $mission_info->bindValue(':mission_nm', $mission_nm, PDO::PARAM_STR);
        $mission_info->execute();
        $mission_info_data = $mission_info->fetch(\PDO::FETCH_ASSOC);
        $mission_sn = $mission_info_data["mission_sn"];

        //選擇小組長檢核任務進度
        if(isset($_POST["groupLeader"])){
            $groupLeaderArray = $_POST["groupLeader"];
            $groupLeaderArray_in = implode("','", $groupLeaderArray);
            $groupLeaderArray_in = "('$groupLeaderArray_in')";

            $userLeader_data = $dbh->prepare("SELECT i.user_id, g.group_id FROM user_info i
                                                LEFT JOIN user_group g ON i.user_id = g.user_id
                                                WHERE g.group_id IN $groupLeaderArray_in AND g.group_leader = 1");
            $userLeader_data->execute();
            foreach($userLeader_data as $key=>$value){
                $groupLeader_data = $dbh->prepare("INSERT into mission_group_leader (user_id, group_id, mission_sn, is_used) VALUES (:leader_id, :group_id, :mission_sn, 1)");
                $groupLeader_data->bindValue(':leader_id', $value['user_id'], PDO::PARAM_STR);
                $groupLeader_data->bindValue(':group_id', $value['group_id'], PDO::PARAM_STR);
                $groupLeader_data->bindValue(':mission_sn', $mission_sn, PDO::PARAM_STR);
                $groupLeader_data->execute();
            }
        }

        //找出學生的年級與班級
        //20191010 bai 虛擬班級 echo($_POST["mission_class"]);
        if ($_POST["mission_class"] !='I' AND $_POST["mission_class"] !=='VirC') { //如果對象不是多個學生，就是一個班級，就要找出整班的學生
            $class_id = explode("-", $target_data);
            $str_length = strlen($class_id[1]);
            if ($str_length == 3) { //格式為601，6年一班
                $stud_grade = substr($class_id[1], 0, 1);
                $stud_class = substr($class_id[1], -2);
            } elseif ($str_length == 4) { //格是為1101，11年一班
                $stud_grade = substr($class_id[1], 0, 2);
                $stud_class = substr($class_id[1], -2);
            }
            //找出全班的學生
            $search_student = $dbh->prepare("SELECT user_info.* FROM user_info,user_access,user_status WHERE organization_id = :org_id AND grade = :grade AND class = :class AND user_access.access_level = 1 AND user_status.access_level = user_access.access_level AND user_info.user_id = user_status.user_id");
            $search_student->bindValue(':org_id', $org, PDO::PARAM_STR);
            $search_student->bindValue(':grade', $stud_grade, PDO::PARAM_INT);
            $search_student->bindValue(':class', $stud_class, PDO::PARAM_INT);
            $search_student->execute();
            $student_list = $search_student->fetchAll(\PDO::FETCH_ASSOC);
        } elseif( $_POST["mission_class"] =='VirC' ){
          //$remedial_class_sn=explode('_', $target_data);
          $tmp=explode('_',$_POST["target_id"][0]);
          $class_sn=$tmp[1];
          //找出全班學生
          $ssSQL='
            SELECT student_id
            FROM remedial_student
            WHERE class_sn="'.$class_sn.'"
          ';
          $search_student = $dbh->prepare($ssSQL);
          $search_student->execute();
          $student_list = $search_student->fetchAll(\PDO::FETCH_ASSOC);
          //debugBAI(__LINE__,__FILE__,array($_POST["mission_class"],$remedial_class_sn),'print_r');
        } else {
            $student_list = $_POST["target_id"];
        }

        //寫入初始化學生任務狀態資料表
        if ($_POST["mission_class"] !='I') {
            foreach ($student_list as $key=>$value) {
                $insert_mission_info_status = $dbh->prepare("INSERT into mission_stud_record (mission_sn,update_time,semester,user_id,finish_node) SELECT mission_sn,create_date,semester,:target_id,:finish_node FROM mission_info WHERE mission_sn = :mission_sn");
                $insert_mission_info_status->bindValue(':mission_sn', $mission_sn, PDO::PARAM_INT);
                $insert_mission_info_status->bindValue(':target_id', $value["user_id"], PDO::PARAM_STR);
                $insert_mission_info_status->bindValue(':finish_node', serialize(""), PDO::PARAM_STR);
                $insert_mission_info_status->execute();
            }
        } else { //單純的學生帳號陣列
            foreach ($student_list as $key=>$value) {
                $acount = 0;
                $insert_mission_info_status = $dbh->prepare("INSERT into mission_stud_record (mission_sn,update_time,semester,user_id,finish_node) SELECT mission_sn,create_date,semester,:target_id,:finish_node FROM mission_info WHERE mission_sn = :mission_sn");
                $insert_mission_info_status->bindValue(':mission_sn', $mission_sn, PDO::PARAM_INT);
                $insert_mission_info_status->bindValue(':target_id', $student_list[$key], PDO::PARAM_STR);
                $insert_mission_info_status->bindValue(':finish_node', serialize(""), PDO::PARAM_STR);
                $insert_mission_info_status->execute();
            }
        }
        //$insert_mark = true;
    }
    elseif ($_POST["type"] =='2') {//patch BlueS 20170712
      //debugBAI(__LINE__,__FILE__,[$student_list,'123'],'print_r');
      // 2018-11-07 Edward: 新增學習問卷
      if ($_POST['ques_only'] === 'Y') {

        foreach ($_POST["target_id"] as $key=>$value) {
          $target_id=$value;
            $result = $dbh->prepare("INSERT INTO mission_info (mission_nm, target_id, group_id, subject_id, node, date, semester, teacher_id, target_type, mission_type, create_date, start_time)
                VALUES (:mission_nm, :target_id, :group_id, :subject_id, :node, :date, :semester, :teacher_id, :target_type, :mission_type, :create_date, :Sdate)");

            $mis_nm = $mission_nm.'--自主學習問卷';
            $result->bindValue(':mission_nm', $mis_nm, PDO::PARAM_STR);
            $result->bindValue(':group_id', $group_data, PDO::PARAM_STR);
            $result->bindValue(':target_id', $target_id, PDO::PARAM_STR);
            $result->bindValue(':subject_id', $_POST["subject_id"], PDO::PARAM_INT);
            $result->bindValue(':node', 'viewform', PDO::PARAM_STR);
            $result->bindValue(':date', $_POST["enddate"], PDO::PARAM_STR);
            $result->bindValue(':Sdate', $_POST["strdate"], PDO::PARAM_STR);
            $result->bindValue(':semester', $_POST["semester"], PDO::PARAM_STR);
            $result->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
            $result->bindValue(':target_type', $_POST["mission_class"], PDO::PARAM_STR);
            $result->bindValue(':mission_type', '2', PDO::PARAM_STR);
            $result->bindValue(':create_date', $time, PDO::PARAM_STR);
            $result->execute();
        }
      }
      else {
        foreach ($_POST["target_id"] as $key=>$value) {

          $target_id=$value;

          if ($insert_mark) {
            $result = $dbh->prepare("INSERT INTO mission_info (mission_nm, target_id, group_id, subject_id, node, date, semester, teacher_id, target_type, mission_type, create_date, start_time)
                VALUES (:mission_nm, :target_id, :group_id, :subject_id, :node, :date, :semester, :teacher_id, :target_type, :mission_type, :create_date, :Sdate)");

            $mis_nm = $mission_nm.'--自主學習問卷';
            $result->bindValue(':mission_nm', $mis_nm, PDO::PARAM_STR);
            $result->bindValue(':group_id', $group_data, PDO::PARAM_STR);
            $result->bindValue(':target_id', $target_id, PDO::PARAM_STR);
            $result->bindValue(':subject_id', $_POST["subject_id"], PDO::PARAM_INT);
            $result->bindValue(':node', 'viewform', PDO::PARAM_STR);
            $result->bindValue(':date', $_POST["enddate"], PDO::PARAM_STR);
            $result->bindValue(':Sdate', $_POST["strdate"], PDO::PARAM_STR);
            $result->bindValue(':semester', $_POST["semester"], PDO::PARAM_STR);
            $result->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
            $result->bindValue(':target_type', $_POST["mission_class"], PDO::PARAM_STR);
            $result->bindValue(':mission_type', '2', PDO::PARAM_STR);
            $result->bindValue(':create_date', $time, PDO::PARAM_STR);
            $result->execute();
        }
            $result = $dbh->prepare("INSERT INTO mission_info (mission_nm, target_id, subject_id, node, date, semester, teacher_id, target_type, mission_type, create_date, assign_type, start_time, endYear, topic_num, exam_type)
      VALUES (:mission_nm, :target_id, :subject_id, :node, :date, :semester, :teacher_id, :target_type, :mission_type, :create_date, :assign_type, :Sdate, :endYear, :topic_num, :exam_type)");//Sdate開始時間 BlueS 2017.07.06
            $result->bindValue(':mission_nm', $mission_nm, PDO::PARAM_STR);
            $result->bindValue(':target_id', $value, PDO::PARAM_STR);
            $result->bindValue(':subject_id', $_POST["subject_id"], PDO::PARAM_INT);
            $result->bindValue(':node', $ind_node, PDO::PARAM_STR);
            $result->bindValue(':date', $_POST["enddate"], PDO::PARAM_STR);
            $result->bindValue(':Sdate', $_POST["strdate"], PDO::PARAM_STR);
            $result->bindValue(':semester', $_POST["semester"], PDO::PARAM_STR);
            $result->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
            $result->bindValue(':assign_type', $ms_type_arr, PDO::PARAM_STR);
            $result->bindValue(':target_type', "C", PDO::PARAM_STR);
            $result->bindValue(':mission_type', $_POST["mission_type"], PDO::PARAM_STR);
            $result->bindValue(':create_date', $time, PDO::PARAM_STR);
            $result->bindValue(':endYear', $endYear_node, PDO::PARAM_STR);
            $result->bindValue(':topic_num', $topic_num, PDO::PARAM_INT);
            $result->bindValue(':exam_type', $exam_type, PDO::PARAM_INT);
            $result->execute();

            //初始化每個學生的任務狀態，先找出mission_sn
            $mission_info = $dbh->prepare("SELECT * FROM mission_info WHERE teacher_id = :teacher_id AND mission_nm = :mission_nm ORDER BY mission_sn DESC LIMIT 1");
            $mission_info->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
            $mission_info->bindValue(':mission_nm', $mission_nm, PDO::PARAM_STR);
            $mission_info->execute();
            $mission_info_data = $mission_info->fetch(\PDO::FETCH_ASSOC);
            $mission_sn = $mission_info_data["mission_sn"];

            //分割出年級和班級
            $class_id = explode("-", $value);
            $str_length = strlen($class_id[1]);
            if ($str_length == 3) { //格式為601，6年一班
                $stud_grade = substr($class_id[1], 0, 1);
                $stud_class = substr($class_id[1], -2);
            } elseif ($str_length == 4) { //格是為1101，11年一班
                $stud_grade = substr($class_id[1], 0, 2);
                $stud_class = substr($class_id[1], -2);
            }

            //20191010 bai 虛擬班級
            if( $_POST["mission_class"]==='VirC' ){
              $tmp=explode('_',$_POST["target_id"][0]);
              $class_sn=$tmp[1];
              //找出全班學生
              $ssSQL='
                SELECT student_id
                FROM remedial_student
                WHERE class_sn="'.$class_sn.'"
              ';
              $search_student = $dbh->prepare($ssSQL);
              $search_student->execute();
              $student_list = $search_student->fetchAll(\PDO::FETCH_ASSOC);
            }else{
              //找出全班學生
              $search_student = $dbh->prepare("SELECT * FROM user_info WHERE organization_id = :org_id AND grade = :grade AND class = :class");
              $search_student->bindValue(':org_id', $org, PDO::PARAM_STR);
              $search_student->bindValue(':grade', $stud_grade, PDO::PARAM_INT);
              $search_student->bindValue(':class', $stud_class, PDO::PARAM_INT);
              $search_student->execute();
              $student_list = $search_student->fetchAll(\PDO::FETCH_ASSOC);
            }

            foreach ($student_list as $key=>$value) {
                $insert_mission_info_status = $dbh->prepare("INSERT into mission_stud_record (mission_sn,update_time,semester,user_id,finish_node) SELECT mission_sn,create_date,semester,:target_id,:finish_node FROM mission_info WHERE mission_sn = :mission_sn");
                $insert_mission_info_status->bindValue(':mission_sn', $mission_sn, PDO::PARAM_INT);
                //20191015 bai 虛擬班級
                if( $_POST["mission_class"]==='VirC' ) $insert_mission_info_status->bindValue(':target_id', $value["student_id"], PDO::PARAM_STR);
                else $insert_mission_info_status->bindValue(':target_id', $value["user_id"], PDO::PARAM_STR);
                $insert_mission_info_status->bindValue(':finish_node', serialize(""), PDO::PARAM_STR);
                $insert_mission_info_status->execute();
            }
        }//end oof foreach($_POST["target_id"])
      }
    }

    $oGetMissionSn = $dbh->prepare("SELECT mission_sn FROM mission_info WHERE create_date = :create_date AND teacher_id = :teacher_id");
    $oGetMissionSn->bindValue(':create_date', $time, PDO::PARAM_STR);
    $oGetMissionSn->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
    $oGetMissionSn->execute();
    $vGetMissionSn = $oGetMissionSn->fetchAll(PDO::FETCH_ASSOC);


    $mission_sn = $vGetMissionSn[0]['mission_sn'];
    switch($_POST["sharetype"]){
        case '1':
            $sShareCode = user_id2org($user_id);
        break;
        case '2':
            $sShareCode = base64_encode($mission_sn."_".time());
        break;
        default:
            $sShareCode = '0';
        break;
    }

    $share=$_POST["sharetype"]._SPLIT_SYMBOL.$sShareCode;
    $oUpdateShare = $dbh->prepare("UPDATE mission_info SET share = :share WHERE mission_sn = :mission_sn");
    $oUpdateShare->bindValue(":share", $share, PDO::PARAM_STR);
    $oUpdateShare->bindValue(":mission_sn", $mission_sn, PDO::PARAM_STR);
    $oUpdateShare->execute();

}

    $html = <<<EOF
  insert success......
EOF;

    $vReturn['html'] = $html;
    $vReturn['share']['type'] = $_POST['sharetype'];
    $vReturn['share']['sharecode'] = $sShareCode;

    echo json_encode($vReturn);
    // echo $class_id;
    // echo "<pre>";
    // echo print_r($student_list);


    $dbh = null;
}
$nowseme=getYearSeme();
if ($_POST["type"] == '4') {
    $seme_teacher_data = $dbh->prepare("SELECT DISTINCT organization_id,grade,class FROM seme_teacher_subject where teacher_id=:user_id and seme_teacher_subject.seme_year_seme=:nowseme ORDER BY grade,class ASC");
    $seme_teacher_data->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_STR);
    $seme_teacher_data->bindValue(':nowseme', $nowseme, PDO::PARAM_STR);
    $seme_teacher_data->execute();

    $user_info_class = $dbh->prepare("SELECT organization_id,grade,class FROM user_info where user_id=:user_id");
    $user_info_class->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_STR);
    $user_info_class->execute();
    $teacher_class = $user_info_class->fetch(\PDO::FETCH_ASSOC);
    $classData[0]= $teacher_class;
    $i=1;
    while ($seme_teacher = $seme_teacher_data->fetch(\PDO::FETCH_ASSOC)) {
        if ($seme_teacher["grade"]==$teacher_class["grade"] && $seme_teacher["class"]==$teacher_class["class"]) {
            continue;
        } else {
            $classData[$i] = $seme_teacher;
            $i++;
        }
    }
    $i=0;

    $groupData = getGroupInfo();
    foreach ($classData as $key => $value) {
        $seme_student_data = $dbh->prepare("SELECT b.seme_num,a.user_id,a.uname,b.grade,b.class FROM user_info a,seme_student b where b.organization_id=:organization_id and b.seme_year_seme=:nowseme and b.grade=:grade and b.class=:class and a.user_id=b.stud_id and a.used = 1 ORDER BY `a`.`user_id` ASC");
        $seme_student_data->bindValue(':organization_id', $value["organization_id"], PDO::PARAM_STR);
        $seme_student_data->bindValue(':nowseme', $nowseme, PDO::PARAM_STR);
        $seme_student_data->bindValue(':grade', $value["grade"], PDO::PARAM_STR);
        $seme_student_data->bindValue(':class', $value["class"], PDO::PARAM_STR);
        $seme_student_data->execute();
        //    $student_class = $seme_student_data->fetchAll(\PDO::FETCH_ASSOC);
        while ($student_class = $seme_student_data->fetch(\PDO::FETCH_ASSOC)) {
            $groups = array_column(array_filter($groupData, function ($group) use ($student_class) {
                return $group["user_id"] == $student_class["user_id"];
            }), "group_nm", "group_id");
            $student_class["groups"] = $groups;
            $returnData[$i] = $student_class;
            $i++;
        }
    }
    //  $seme_student_data = $dbh->prepare("SELECT a.user_id,a.uname,b.grade,b.class FROM user_info a,seme_student b where b.organization_id=:organization_id and b.seme_year_seme=:nowseme and b.grade=:grade and b.class=:class and a.user_id=b.stud_id");
    //  $seme_student_data->bindValue(':organization_id', "190041", PDO::PARAM_STR);
    //  $seme_student_data->bindValue(':nowseme', "1061", PDO::PARAM_STR);
    //  $seme_student_data->bindValue(':grade', "6", PDO::PARAM_STR);
    //  $seme_student_data->bindValue(':class', "9", PDO::PARAM_STR);
    //  $seme_student_data->execute();
    //  $student_class = $seme_student_data->fetchAll(\PDO::FETCH_ASSOC);
    //  $i=0;
    //  while ($student_class = $seme_student_data->fetch(\PDO::FETCH_ASSOC)){
    //    $returnData[$i] = $student_class;
    //    $i++;
    //  }
    echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
}

// 171026，KP，新增學生小組功能
if ($_POST["type"] == '5') {
    $all_stud_id = $_POST["user_id"];
    $organization_id = $_POST["organization_id"];
    $group_name = xss_filter($_POST["group_name"]); //2019-10-22 #981 KP:過濾XSS Payload
    $time = date('YmdHis');
    $teacher_id = $_SESSION['user_id'];

    // 171101，搜尋資料庫中總共有幾組小組，取出最大group_id。
    // **171102，修改group_id欄位為Primary Key和A.I，因此不需要在做計算。
    // $find_group = $dbh->prepare("SELECT DISTINCT group_id FROM `seme_group` ORDER BY group_id DESC LIMIT 1");
    // $find_group->execute();
    // $group_data = $find_group->fetch();
    // $group_id_now = $group_data["group_id"];
    // //後面要新增小組的新的group_id
    // $group_id_next = $group_id_now + 1;

    //寫入seme_group資料表
    $add_seme_group_sql = "INSERT INTO seme_group(group_nm, teacher_id, create_date) VALUES (:group_name,:teacher_id,:create_date)";
    $add_seme_group = $dbh->prepare($add_seme_group_sql);
    $add_seme_group->bindValue(':group_name', $group_name, PDO::PARAM_STR);
    $add_seme_group->bindValue(':teacher_id', $teacher_id, PDO::PARAM_STR);
    $add_seme_group->bindValue(':create_date', $time, PDO::PARAM_STR);
    $add_seme_group->execute();

    //寫入seme_group資料表後，要馬上撈出group_id，寫入user_group的group_id欄位。
    $find_group_id_sql = "SELECT * FROM seme_group ORDER BY group_id DESC LIMIT 1";
    $find_group_id = $dbh->prepare($find_group_id_sql);
    $find_group_id->execute();
    $group_data = $find_group_id->fetch();
    $group_id_now = $group_data["group_id"];

    //寫入user_group資料表
    foreach ($all_stud_id as $key => $value) {
        $stud_id = $value;

        //尋找該名學生是否已經有加入小組?
        // $findkey = $dbh->prepare("SELECT * FROM user_group WHERE user_id = :stud_id AND organization_id = :org_id");
        // $findkey->bindValue(':stud_id', $stud_id, PDO::PARAM_STR);
        // $findkey->bindValue(':org_id', $organization_id, PDO::PARAM_STR);
        // $key = $findkey->fetch();
        // $count = $findkey->rowCount();


        // if($count==0){ //如果小組資料，則新增小組。
        $add_user_group_sql = "INSERT INTO user_group(organization_id, user_id, group_id,create_date, update_date) VALUES (:org_id,:stud_id,:group_id,:create_date,:update_date)";

        $add_user_group = $dbh->prepare($add_user_group_sql);
        $add_user_group->bindValue(':org_id', $organization_id, PDO::PARAM_STR);
        $add_user_group->bindValue(':stud_id', $stud_id, PDO::PARAM_STR);
        $add_user_group->bindValue(':group_id', $group_id_now, PDO::PARAM_INT);
        $add_user_group->bindValue(':create_date', $time, PDO::PARAM_STR);
        $add_user_group->bindValue(':update_date', $time, PDO::PARAM_STR);
        $add_user_group->execute();
        // }else { //如果之前已編過小組，則更新該學生的小組資料
        //    $update_user_group_sql = "UPDATE user_group SET group_id=:group_id,update_date=:update_date WHERE user_id=:stud_id";

        //    $update_user_group = $dbh->prepare($update_user_group_sql);
        //    $update_user_group->bindValue(':stud_id',$stud_id,PDO::PARAM_STR);
        //    $update_user_group->bindValue(':group_id',$group_id_next,PDO::PARAM_INT);
        //    $update_user_group->bindValue(':update_date',$time,PDO::PARAM_STR);
        //    $update_user_group->execute();
        // }
    }

    foreach ($all_stud_id as $key => $value) {
        $stud_id = $value;
        //check 學生小組資料是否成功寫入資料庫。
        $check_group_sql = "SELECT * FROM user_group,seme_group WHERE user_group.group_id = seme_group.group_id AND user_id = :stud_id AND user_group.group_id = :group_id";
        $check_group = $dbh->prepare($check_group_sql);
        $check_group->bindValue(':stud_id', $stud_id);
        $check_group->bindValue(':group_id', $group_id_now);
        $check_group->execute();
        $check_group_data = $check_group->fetch();
        $check_group_count = $check_group->rowCount();
        if ($check_group_count!=0) {
            $status = '新增小組成功！';
        } else {
            $status = '新增失敗！請稍後在嘗試一次。';
        }
    }

    echo $status;
}

// 171026，新增老師給個別學生獎勵的功能
if ($_POST["type"] == '6') {
    if ($_POST["user_id"]!='') {
        $failExchangeCount = 0;
        $v_failExchangeStu = array();
        $good_user_id = $_POST["user_id"];
        $coin_type = substr($_POST["coin_value"], 0, 1); //+ or -
        if($coin_type == '-'){
            $aciton_type = '扣除';
            $rule_type = 'subtract';
        }else if($coin_type == '+'){
            $aciton_type = '獲得';
            $rule_type = 'add';
        }else if($coin_type == '$'){
            $aciton_type = '兌換';
            $rule_type = 'exchange';
        }
        $coin_value = substr($_POST["coin_value"], 1);
        $rule_name = xss_filter($_POST["rule_name"]);//獎勵的項目名稱 2019-10-22 #981 KP:過濾XSS Payload
        $memo = '因【'.$rule_name.'】而'.$aciton_type.'代幣'.$coin_value.'個';
        $pro_no = $_POST["pro_no"];
        // tab的id當作參數
        $pro_no_id = '#parentHorizontalTab1';
        $time = date('YmdHis');
        //2018-08-15 KP:判斷所選擇的獎勵選項是預設還是自訂
        $b_choose_preset = $_POST['is_preset'];

        foreach ($good_user_id as $key => $value) {
            $stud_id = $value;
            $user_id_temp = split('-', $stud_id);
            $organization_id = $user_id_temp[0];

            //171011，先抓取現在角色的代幣數(role_info.coin)
            //和可以得到的代幣數(role_reward_way.reward_coin_num)
            $reward_coin = "SELECT coin,accumulation_coin,uname FROM role_info,user_info WHERE stud_id = :user_id AND stud_id = user_id";
            $coin_num = $dbh->prepare($reward_coin);
            $coin_num->bindValue(':user_id', $stud_id, PDO::PARAM_STR);
            $coin_num->execute();
            $role_cu = $coin_num->rowCount();

            //存取回答者現在的代幣數
            while ($row=$coin_num->fetch(PDO::FETCH_ASSOC)) {
                $role_coin = $row['coin'];
                $accumulation_coin = $row['accumulation_coin'];
                $sn = $row['sn'];
                $uname = $row['uname'];
            }

            //2019-07-18 KP:如果兌換獎勵的話，需要先判斷學生的代幣是否夠兌換
            if($aciton_type == '兌換'){
                if($coin_value > $role_coin){
                    $failExchangeCount++;
                    array_push($v_failExchangeStu,$uname);
                    continue;
                }
            }

            if ($role_cu == 0) {
                $result = $dbh->prepare("INSERT INTO role_info (organization_id, stud_id, coin,accumulation_coin,
        body_weight, body_height, health_points, create_time)
        VALUES (:org_id, :stud_id, :coin, :accumulation_coin, :body_weight, :body_height, :health_points, :create_time)");
                $result->bindValue(':org_id', $organization_id, PDO::PARAM_STR);
                $result->bindValue(':stud_id', $stud_id, PDO::PARAM_STR);
                $result->bindValue(':coin', $coin_value, PDO::PARAM_STR);
                $result->bindValue(':accumulation_coin', $coin_value, PDO::PARAM_STR);
                $result->bindValue(':body_weight', 0, PDO::PARAM_STR);
                $result->bindValue(':body_height', 0, PDO::PARAM_STR);
                $result->bindValue(':health_points', 0, PDO::PARAM_STR);
                $result->bindValue(':create_time', $time, PDO::PARAM_STR);

                $result->execute();
            } else {
                //更新回答者的coin數量
                $update_coin = "UPDATE role_info SET coin = :coin_num, accumulation_coin = :accumulation_coin WHERE stud_id = :user_id";
                $give_coin = $dbh->prepare($update_coin);
                //2018-08-14 KP:新增扣除代幣機制，判斷是增加代幣還是減少代幣
                if($coin_type == '+'){
                    $coin_num = $role_coin + $coin_value;
                    $accumulation_coin += $coin_value;
                }else{
                    $coin_num = $role_coin - $coin_value;
                }
                if($coin_num < 0) $coin_num = 0; //過濾掉負數情況
                $give_coin->bindValue(':coin_num', $coin_num, PDO::PARAM_INT);
                $give_coin->bindValue(':accumulation_coin', $accumulation_coin, PDO::PARAM_INT);
                $give_coin->bindValue(':user_id', $stud_id, PDO::PARAM_STR);
                $give_coin->execute();
            }

            //date、org_id、user_id、pro_no、type、action、memo
            $add_user_history_sql = "INSERT INTO user_history(date, organization_id, user_id,pro_no, type,action,memo) VALUES (:gain_date, :org_id, :user_id, :pro_no, :type, :action, :memo)";
            $add_user_history = $dbh->prepare($add_user_history_sql);
            $add_user_history->bindValue(':gain_date', $time, PDO::PARAM_STR);
            $add_user_history->bindValue(':org_id', $organization_id, PDO::PARAM_STR);
            $add_user_history->bindValue(':user_id', $stud_id, PDO::PARAM_STR);
            $add_user_history->bindValue(':pro_no', $pro_no, PDO::PARAM_STR);
            $add_user_history->bindValue(':type', 'gain_t', PDO::PARAM_STR);
            $add_user_history->bindValue(':action', $pro_no_id, PDO::PARAM_STR);
            $add_user_history->bindValue(':memo', $memo, PDO::PARAM_STR);
            $add_user_history->execute();


            //2018-08-29 KP:紀錄老師的操作代幣紀錄
            $add_coin_record_sql = "INSERT INTO role_coin_record(teacher_id, stud_id, rule_name, rule_type, rule_point, time) VALUES (:teacher_id, :stud_id, :rule_name, :rule_type, :rule_point, :time)";
            $add_coin_record = $dbh->prepare($add_coin_record_sql);
            $add_coin_record->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
            $add_coin_record->bindValue(':stud_id', $stud_id, PDO::PARAM_STR);
            $add_coin_record->bindValue(':rule_name', $rule_name, PDO::PARAM_STR);
            $add_coin_record->bindValue(':rule_type', $rule_type, PDO::PARAM_STR);
            $add_coin_record->bindValue(':rule_point', $coin_value, PDO::PARAM_INT);
            $add_coin_record->bindValue(':time', $time, PDO::PARAM_STR);
            $add_coin_record->execute();
        } //end of foreach

        //2018-08-14 KP:紀錄老師曾經使用過的獎勵 / 懲罰項目，並計算使用次數。
        if($b_choose_preset == 'false'){ //js 的 boolean 傳過來後型態變成字串
            $s_coin_rule = "SELECT * FROM role_coin_rule WHERE user_id = :user_id AND rule_name = :rule_name";
            $o_coin_rule = $dbh->prepare($s_coin_rule);
            $o_coin_rule->bindValue(':user_id', $user_id, PDO::PARAM_STR); //此處的user_id為老師的id
            $o_coin_rule->bindValue(':rule_name', $rule_name, PDO::PARAM_STR);
            $o_coin_rule->execute();
            $i_rule_count = $o_coin_rule->rowCount();
            $v_coin_rule_data = $o_coin_rule->fetch(PDO::FETCH_ASSOC);

            if($i_rule_count == 0){
                $o_insert_new_coin_rule = $dbh->prepare("INSERT INTO role_coin_rule (user_id,rule_name,rule_type,rule_point,use_count)
                    VALUES (:user_id,:rule_name,:rule_type,:rule_point,:use_count)");
                $o_insert_new_coin_rule->bindValue(':user_id', $user_id, PDO::PARAM_STR);
                $o_insert_new_coin_rule->bindValue(':rule_name', $rule_name, PDO::PARAM_STR);
                $o_insert_new_coin_rule->bindValue(':rule_type', $rule_type, PDO::PARAM_STR);
                $o_insert_new_coin_rule->bindValue(':rule_point', $coin_value, PDO::PARAM_INT);
                $o_insert_new_coin_rule->bindValue(':use_count', 1, PDO::PARAM_INT);
                $o_insert_new_coin_rule->execute();
            }else {
                $i_rule_use_count = $v_coin_rule_data['use_count'];
                $i_rule_use_count += 1;
                $s_update_rule_count = "UPDATE role_coin_rule SET use_count = :use_count WHERE user_id = :user_id AND rule_name = :rule_name";
                $o_update_rule_count = $dbh->prepare($s_update_rule_count);
                $o_update_rule_count->bindValue(':user_id', $user_id, PDO::PARAM_STR);
                $o_update_rule_count->bindValue(':rule_name', $rule_name, PDO::PARAM_STR);
                $o_update_rule_count->bindValue(':use_count', $i_rule_use_count, PDO::PARAM_INT);
                $o_update_rule_count->execute();
            }
        }//end of if(is_preset)
        // echo $b_choose_preset;
        echo json_encode($v_failExchangeStu);
        // echo $failExchangeCount;
    }
    // echo $memo;
} //end of $_POST["type"] == '6'

if ($_POST["type"] == '7') {
    if ($_POST["user_id"]!='') {
        $user_id = $_POST["user_id"];
        $user_id_temp = split('-', $user_id);
        $stud_id = $user_id_temp[1];
        $new_group_id = $_POST["new_group_id"];
        $old_group_id = $_POST["old_group_id"];
        $time = date('YmdHis');
        $oChkStuInGroup = $dbh->prepare("SELECT user_id FROM user_group WHERE user_id = :user_id AND group_id = :new_group_id");
        $oChkStuInGroup->bindValue(":user_id", $user_id, PDO::PARAM_STR);
        $oChkStuInGroup->bindValue(":new_group_id", $new_group_id, PDO::PARAM_STR);
        $oChkStuInGroup->execute();
        $vResult = $oChkStuInGroup->fetchAll(PDO::FETCH_ASSOC);
        if(empty($vResult)){
            $update_group_id_sql = "UPDATE user_group SET  group_id = :new_group_id,update_date = :update_date WHERE user_id = :stud_id AND group_id = :old_group_id";
            $update_group_id = $dbh->prepare($update_group_id_sql);
            $update_group_id->bindValue(':new_group_id', $new_group_id, PDO::PARAM_STR);
            $update_group_id->bindValue(':old_group_id', $old_group_id, PDO::PARAM_STR);
            $update_group_id->bindValue(':stud_id', $user_id);
            $update_group_id->bindValue(':update_date', $time, PDO::PARAM_STR);
            $update_group_id->execute();
        }else{
            $stud_id = "error";
        }
    }

    echo $stud_id;
}

if ($_POST["type"] == '8') {
    if ($_POST["user_id"]!='') {
        $user_id = $_POST["user_id"];
        $user_id_temp = split('-', $user_id);
        $stud_id = $user_id_temp[1];
        $group_id = $_POST['group_id'];

        $update_group_id_sql = "DELETE FROM user_group WHERE user_id = :user_id AND group_id = :group_id";
        $update_group_id = $dbh->prepare($update_group_id_sql);
        $update_group_id->bindValue(':group_id', $group_id);
        $update_group_id->bindValue(':user_id', $user_id);
        $update_group_id->execute();
    }

    echo $stud_id;
}


if ($_POST["type"] == '9') {
    if ($_POST["user_id"]!='') {
        $all_stud_id = $_POST["user_id"];
        $user_id_temp = split('-', $user_id);
        $organization_id = $user_id_temp[0];
        $group_id = $_POST['group_id'];
        $time = date('YmdHis');
        $sErrNms = '';
        $Ids = array();
        //寫入user_group資料表
        foreach ($all_stud_id as $key => $value) {
            $stud_id = $value;
            //尋找該名學生是否已經有加入小組?
             $findkey = $dbh->prepare("SELECT * FROM user_group WHERE user_id = :stud_id AND group_id = :group_id");
             $findkey->bindValue(':stud_id', $stud_id, PDO::PARAM_STR);
             $findkey->bindValue(':group_id', $group_id, PDO::PARAM_STR);
             $key = $findkey->execute();
             $count = $findkey->rowCount();
             if($count==0){ //如果小組資料，則新增小組。
                array_push($Ids, $stud_id);
            }else{
                $oGetStu = $dbh->prepare("SELECT uname FROM user_info WHERE user_id = :user_id");
                $oGetStu->bindValue(":user_id", $stud_id, PDO::PARAM_STR);
                $oGetStu->execute();
                $vGetStu = $oGetStu->fetchAll(PDO::FETCH_ASSOC);
                $sErrNms .= " ".$vGetStu[0]['uname'].",";
             }
        }
        if($sErrNms != ''){
            $vReturn['status'] = '0';
            $sErrNms = substr($sErrNms, 0, -1);
            $vReturn['ErrNms'] = $sErrNms;
        }else{
            $vReturn['status'] = '1';
            foreach($Ids as $Id){
                $add_user_group_sql = "INSERT INTO user_group(organization_id, user_id, group_id,create_date, update_date) VALUES (:org_id,:stud_id,:group_id,:create_date,:update_date)";
                $add_user_group = $dbh->prepare($add_user_group_sql);
                $add_user_group->bindValue(':org_id', $organization_id, PDO::PARAM_STR);
                $add_user_group->bindValue(':stud_id', $Id, PDO::PARAM_STR);
                $add_user_group->bindValue(':group_id', $group_id, PDO::PARAM_INT);
                $add_user_group->bindValue(':create_date', $time, PDO::PARAM_STR);
                $add_user_group->bindValue(':update_date', $time, PDO::PARAM_STR);
                $add_user_group->execute();
            }
        }
        echo json_encode($vReturn);
        //echo $stud_id;
    }
}//end of type = 9


//180427，KP，寫後端更新組別名稱的程式`。
//在最上方加上user_info的session data，記得把跟user相關的資料，改為從那邊取。
if ($_POST["type"] == '10') {
    //user_id 和 org_id，已經有給值了

    $group_name = xss_filter($_POST['group_name']); //2019-10-22 #981 KP:過濾XSS Payload

    $sModfiyGroupNameSQL = "UPDATE seme_group
    SET group_nm = :group_nm WHERE group_id = :group_id";

    $vReturn = array();
    $vReturn['status'] = 'OK';

    foreach ($_POST as $sIndx => $sPara) {
        if (empty($sPara) || '' == $sPara) {
            $vReturn['status'] = 'ERR';
            $vReturn['msg'] .= 'parameter error: '.$sIndx.'</br>';
        }
    }

    try {
        $oModfiyGroupName = $dbh->prepare($sModfiyGroupNameSQL);
        $oModfiyGroupName->bindValue(':group_nm', $group_name, PDO::PARAM_STR);
        $oModfiyGroupName->bindValue(':group_id', $_POST['group_id'], PDO::PARAM_INT);
        $oModfiyGroupName->execute();
    } catch (PDOException $e) {
        $vReturn['status'] = 'ERR';
        $vReturn['msg'] .= 'sql error: '.$e;
    }

    echo json_encode($vReturn);
}

//180508，KP，刪除小組的後端程式
if ($_POST["type"] == '11') {
    //user_id 和 org_id，已經有給值了

    $sModfiyGroupStatusSQL = "UPDATE seme_group
    SET is_used = 0 WHERE group_id = :group_id";

    $vReturn = array();
    $vReturn['status'] = 'OK';

    foreach ($_POST as $sIndx => $sPara) {
        if (empty($sPara) || '' == $sPara) {
            $vReturn['status'] = 'ERR';
            $vReturn['msg'] .= 'parameter error: '.$sIndx.'</br>';
        }
    }

    try {
        $oModfiyGroupStatus = $dbh->prepare($sModfiyGroupStatusSQL);
        $oModfiyGroupStatus->bindValue(':group_id', $_POST['group_id'], PDO::PARAM_INT);
        $oModfiyGroupStatus->execute();
    } catch (PDOException $e) {
        $vReturn['status'] = 'ERR';
        $vReturn['msg'] .= 'sql error: '.$e;
    }

    echo json_encode($vReturn);
}

//2018-08-14 KP:撈出老師曾經使用過的獎勵項目，根據使用次數做倒序排序，讓老師可以選擇。
if ($_POST["type"] == '12') {
    //user_id 和 org_id，已經有給值了

    $s_reward_rule = "SELECT * FROM role_coin_rule WHERE user_id = :user_id ORDER BY use_count DESC";

    try {
        $o_reward_rule = $dbh->prepare($s_reward_rule);
        $o_reward_rule->bindValue(':user_id', $user_id, PDO::PARAM_STR); //此處的user_id為老師的id
        $o_reward_rule->execute();
        $i_rule_count = $o_reward_rule->rowCount();
    } catch (PDOException $e) {
        $vReturn['status'] = 'ERR';
        $vReturn['msg'] .= 'sql error: '.$e;
    }

    $v_reward_rule_data = $o_reward_rule->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($v_reward_rule_data);
}

//2018-08-22 KP:編輯自定義的代幣選項資料
if ($_POST["type"] == '13') {
    global $dbh;
    $vReturn = array();
    $v_coin_info = array();
    $v_coin_info = $_POST['coin_info'];
    $rule_name = xss_filter($_POST['rule_name']); //2019-10-20 #981 KP:過濾XSS Payload
    // $rule_type = $_POST['rule_type']; //先不讓老師編輯類別
    $rule_point = xss_filter($_POST['rule_point']); //2019-10-20 #981 KP:過濾XSS Payload
    $rule_sn = $_POST['rule_sn'];

    $s_update_coin_rule = "UPDATE role_coin_rule SET rule_name = :rule_name, rule_point = :rule_point WHERE rule_sn = :rule_sn";
    try {
        $o_update_coin_rule = $dbh->prepare($s_update_coin_rule);
        $o_update_coin_rule->bindValue(':rule_name',$rule_name,PDO::PARAM_STR);
        // $o_update_coin_rule->bindValue(':rule_type',$rule_type,PDO::PARAM_STR);
        $o_update_coin_rule->bindValue(':rule_point',$rule_point,PDO::PARAM_INT);
        $o_update_coin_rule->bindValue(':rule_sn',$rule_sn,PDO::PARAM_INT);
        $o_update_coin_rule->execute();
        $vReturn['status'] = 'OK';
        $vReturn['msg'] = '修改成功!';
    } catch (PDOException $e) {
        $vReturn['status'] = 'ERR';
        $vReturn['msg'] = 'sql error: '.$e;
    }
    // print_r($v_coin_info);
    echo json_encode($vReturn);
}

//2018-08-27 KP:編輯自定義的代幣選項資料
if ($_POST["type"] == '14') {
    global $dbh;
    $vReturn = array();
    $rule_sn = $_POST['rule_sn'];

    $s_delete_coin_rule = "UPDATE role_coin_rule SET display = 0 WHERE rule_sn = :rule_sn";
    try {
        $o_delete_coin_rule = $dbh->prepare($s_delete_coin_rule);
        $o_delete_coin_rule->bindValue(':rule_sn',$rule_sn,PDO::PARAM_INT);
        $o_delete_coin_rule->execute();
        $vReturn['status'] = 'OK';
        $vReturn['msg'] = '刪除成功!';
    } catch (PDOException $e) {
        $vReturn['status'] = 'ERR';
        $vReturn['msg'] = 'sql error: '.$e;
    }
    // print_r($v_coin_info);
    echo json_encode($vReturn);
}
if ($_POST["type"] == '15') {
    $vReturn = array();
    $rule_name = xss_filter($_POST["item_name"]); //2019-10-20 #981 KP:過濾XSS Payload
    $rule_type = $_POST['item_type'];
    $rule_points = $_POST['item_value'];

    $s_inert_new_coin_rule = 'INSERT INTO role_coin_rule (user_id,rule_name,rule_type,rule_point,use_count)
                              VALUES (:user_id,:rule_name,:rule_type,:rule_point,:use_count)';
    try {
        $o_insert_new_coin_rule = $dbh->prepare($s_inert_new_coin_rule);
        $o_insert_new_coin_rule->bindValue(':user_id', $user_id, PDO::PARAM_STR);
        $o_insert_new_coin_rule->bindValue(':rule_name', $rule_name, PDO::PARAM_STR);
        $o_insert_new_coin_rule->bindValue(':rule_type', $rule_type, PDO::PARAM_STR);
        $o_insert_new_coin_rule->bindValue(':rule_point', $rule_points, PDO::PARAM_INT);
        $o_insert_new_coin_rule->bindValue(':use_count', 0, PDO::PARAM_INT);
        $o_insert_new_coin_rule->execute();
        $vReturn['status'] = 'OK';
        $vReturn['msg'] = '新增成功!';
    } catch (PDOException $e) {
        $vReturn['status'] = 'ERR';
        $vReturn['msg'] = 'sql error: '.$e;
    }

    echo json_encode($vReturn);
}

// #627 Edward: 跨校指派任務 - For 東山國小 棒球隊
if ($_POST["type"] == '16') {
    $vReturn = array();
    $sGetCrossSQL = "SELECT * FROM cross_school_student LEFT JOIN user_info ON cross_school_student.student_id = user_info.user_id LEFT JOIN organization ON organization.organization_id = user_info.organization_id WHERE cross_school_student.teacher_id = :teacher_id AND user_info.user_id IS NOT NULL ORDER by organization.organization_id";
    $oGetCrossSQL = $dbh->prepare($sGetCrossSQL);
    $oGetCrossSQL->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
    $oGetCrossSQL->execute();
    $vStud = $oGetCrossSQL->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($vStud as $key => $value) {
      $vReturn[$key]['org_name'] = $value['name'];
      $vReturn[$key]['user_id'] = $value['user_id'];
      $vReturn[$key]['uname'] = $value['uname'];
      $vReturn[$key]['grade'] = $value['grade'];
      $vReturn[$key]['class'] = $value['class'];
      $vReturn[$key]['groups'] = array();
    }

    echo json_encode($vReturn);
}

if(isset($_POST['act'])){
    switch($_POST['act']){
        case 'updateShare':
            switch($_POST['sharetype']){
                case '0':
                $share = '0';
                break;
                case '1':
                $share = user_id2org($user_id);
                break;
                case '2':
                $share = base64_encode($_POST['mission_sn']."_".time());
                break;
            }
            $share = $_POST['sharetype']._SPLIT_SYMBOL.$share;
            $oUpdate = $dbh->prepare("UPDATE mission_info SET share = :share WHERE mission_sn = :mission_sn");
            $oUpdate->bindValue(":share", $share, PDO::PARAM_STR);
            $oUpdate->bindValue(":mission_sn", $_POST['mission_sn'], PDO::PARAM_STR);
            $oUpdate->execute();
            $code = explode(_SPLIT_SYMBOL, $share);

            $vReturn['sharetype'] = $_POST['sharetype'];
            $vReturn['sharecode'] = $code[1];
             echo json_encode($vReturn);
        break;
    }
}
//設定小組長
if($_POST["type"] == '17'){
    $groupId = $_POST["group_id"];
    $leaderId = $_POST["leader_id"];
    $vReturn = array();

    //先設定所有組員為非組長(組長僅有一位)
    $group_leader_not = $dbh->prepare("UPDATE user_group SET group_leader = 0 WHERE group_id = :group_id");
    $group_leader_not->bindValue(":group_id", $groupId, PDO::PARAM_INT);
    $group_leader_not->execute();

    try {
        if($leaderId != 'noneLeader'){
            //設定選取組員為組長
            $group_leader_set = $dbh->prepare("UPDATE user_group SET group_leader = 1 WHERE group_id = :group_id AND user_id = :leader_id");
            $group_leader_set->bindValue(":group_id", $groupId, PDO::PARAM_INT);
            $group_leader_set->bindValue(":leader_id", $leaderId, PDO::PARAM_STR);
            $group_leader_set->execute();
            $vReturn['status'] = 'OK';
            $vReturn['msg'] = '指派小組長成功!';
        }else{
            $vReturn['status'] = 'OKK';
            $vReturn['msg'] = '不指派小組長成功!';
        }
    } catch (PDOException $e) {
        $vReturn['status'] = 'ERROR';
        $vReturn['msg'] = '指派小組長失敗!';
    }

    echo json_encode($vReturn);
}

//指派小組長時,顯示該組組員名單
if($_POST['type'] == '18'){
  $group_mamber_list_data = $dbh->prepare("SELECT g.user_id, i.uname, g.group_leader FROM user_group g
                                            LEFT JOIN user_info i ON g.user_id = i.user_id
                                                WHERE g.group_id = :group_id ORDER BY g.user_id");
  $group_mamber_list_data->bindValue(":group_id", $_POST['group_id'],PDO::PARAM_STR);
  $group_mamber_list_data->execute();
  $group_mamber_list = $group_mamber_list_data->fetchAll(\PDO::FETCH_ASSOC);
  echo json_encode($group_mamber_list);
}

// 校管找出全校班級的學生
if($_POST['type'] == '19') {

  $seme_teacher_data = $dbh->prepare("SELECT DISTINCT organization_id,grade,class FROM seme_student where organization_id=:organization_id and seme_year_seme=:nowseme ORDER BY grade,class ASC");
  $seme_teacher_data->bindValue(':organization_id', $organization_id, PDO::PARAM_STR);
  $seme_teacher_data->bindValue(':nowseme', $nowseme, PDO::PARAM_STR);
  $seme_teacher_data->execute();

  $i=0;
  while ($seme_teacher = $seme_teacher_data->fetch(\PDO::FETCH_ASSOC)) {
    $classData[$i] = $seme_teacher;
    $i++;
  }

  $i=0;
  foreach ($classData as $key => $value) {
      $seme_student_data = $dbh->prepare("SELECT a.user_id,a.uname,b.grade,b.class,a.organization_id FROM user_info a,seme_student b where b.organization_id=:organization_id and b.seme_year_seme=:nowseme and b.grade=:grade and b.class=:class and a.user_id=b.stud_id and a.used = 1 ORDER BY `a`.`user_id` ASC");
      $seme_student_data->bindValue(':organization_id', $value["organization_id"], PDO::PARAM_STR);
      $seme_student_data->bindValue(':nowseme', $nowseme, PDO::PARAM_STR);
      $seme_student_data->bindValue(':grade', $value["grade"], PDO::PARAM_STR);
      $seme_student_data->bindValue(':class', $value["class"], PDO::PARAM_STR);
      $seme_student_data->execute();

      while ($student_class = $seme_student_data->fetch(\PDO::FETCH_ASSOC)) {
        $student_family_data = $dbh->prepare("SELECT * FROM user_family LEFT JOIN user_status ON user_family.fuser_id = user_status.user_id where organization_id=:organization_id and user_family.user_id= :user_id");
        $student_family_data->bindValue(':organization_id', $student_class["organization_id"], PDO::PARAM_STR);
        $student_family_data->bindValue(':user_id', $student_class["user_id"], PDO::PARAM_STR);
        $student_family_data->execute();

        if ($student_family_data->rowCount() > 0) {
          while ($vfamilydata = $student_family_data->fetch(\PDO::FETCH_ASSOC)) {
            $vBinding = array();
            if ($vfamilydata['access_level'] == USER_PARENTS) {
              $vBinding[] = '家長';
            }
            if ($vfamilydata['access_level'] == USER_PARTNER) {
              $vBinding[] = '大學伴';
            }
          }
          $vBinding = array_unique($vBinding);
          $sBinding = implode($vBinding, ',');
          $student_class['uname'] = $student_class['uname'].'('.$sBinding.'已綁定)';
          $returnData[$i] = $student_class;
        }
        else {
          $returnData[$i] = $student_class;
        }

        $i++;
      }
  }

  echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
}

if($_POST['type'] == '20'){
    $returnData = $dbh->prepare("SELECT  
        user_info.user_id,
        user_info.uname,
        organization.name,
        user_info.grade,
        user_info.class
    FROM
        user_family
    LEFT JOIN
        user_info ON user_family.user_id = user_info.user_id
    LEFT JOIN
        organization ON user_info.organization_id = organization.organization_id
    WHERE
        fuser_id = :fuser_id AND user_info.user_id IS NOT NULL
    ");


    $returnData->bindValue(':fuser_id', $_POST['fuser_id'], PDO::PARAM_STR);
    $returnData->execute();
    $returnData =$returnData->fetchAll(\PDO::FETCH_ASSOC);

    // while ($student_class = $seme_student_data->fetch(\PDO::FETCH_ASSOC)) {
    //     $student_family_data = $dbh->prepare("SELECT * FROM user_family LEFT JOIN user_info ON user_family.user_id = user_info.user_id where organization_id=:organization_id and user_family.user_id= :user_id");
    //     $student_family_data->bindValue(':organization_id', $student_class["organization_id"], PDO::PARAM_STR);
    //     $student_family_data->bindValue(':user_id', $student_class["user_id"], PDO::PARAM_STR);
    //     $student_family_data->execute();

   
    echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
}


?>
