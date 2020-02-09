
<?php
require_once "../../../include/config.php";
?>
<?php
session_start();
$user_id = $_SESSION['self_id'];
if($_POST['type'] == '1'){
    $returnData = $dbh->prepare("SELECT
    mission_sn sn,
    mission_nm name,
    create_date date
FROM
    mission_info
WHERE
    teacher_id = :teacher_id AND mission_type = '7' AND target_id = :class_id 
ORDER BY `mission_info`.`create_date` DESC
");

    $returnData->bindValue(':teacher_id',$user_id , PDO::PARAM_STR);
    $returnData->bindValue(':class_id', $_POST['class_id'], PDO::PARAM_STR);
    $returnData->execute();
    $returnData =$returnData->fetchAll(\PDO::FETCH_ASSOC);
    $class_id = $_POST['class_id'];
    $grade = $_POST['grade'];
    $class = $_POST['class'];
    foreach($returnData as $key => &$value){
        $url = "modules.php?op=modload&name=assignMission&file=mission_competency_allclass&main=all&c_id=$class_id&grade=$grade&class=$class&mission_sn=";
        $returnData[$key]['url']='';
        $sn=$returnData[$key]['sn'];
        $returnData[$key]['url']=$url.$sn;
    }
    echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
	}
if($_POST['type'] == '2'){
   $class_id = $_POST['class_id'];
   $class = $_POST['class'];
   $grade = $_POST['grade'];
   $mission_sn =$_POST['mission_sn'];
   $organization_id = explode("-",$class_id);
   $organization_id = $organization_id[0];

   //全班人數
   $returnData = $dbh->prepare("SELECT 
   *  
   FROM `seme_student`
   WHERE `organization_id` = $organization_id AND `grade` = $grade AND `class` = $class 
   GROUP BY `stud_id`
   ");

    $returnData->execute();
    $returnData =$returnData->fetchAll(\PDO::FETCH_ASSOC);
    $returnData = count($returnData);
    echo  json_encode($returnData, JSON_UNESCAPED_UNICODE);
}
if ($_POST['type'] == '3') {
    $class_id = $_POST['class_id'];
    $class = $_POST['class'];
    $grade = $_POST['grade'];
    $mission_sn =$_POST['mission_sn'];
    $organization_id = explode("-",$class_id);
    $organization_id = $organization_id[0];
    //撈任務下的單元
    $where = $dbh->prepare("SELECT node  FROM mission_info WHERE `mission_sn` = $mission_sn");
    $where->execute();
    $where =$where->fetchAll(\PDO::FETCH_ASSOC);

    $a = $where['0']['node'];
    $a= explode("@XX@",$a);
    foreach($a as $value){
         $c= explode("-",$value);
         if($c['1']!=''){
             $x .= $c['1'].',';
         }
    }
    $x =substr($x,0,-1);
    $b = substr($a[0], 0, 3);
    if ($b=='gc-') {
        $b = substr($b, 0, 2);
    }
    //此任務的資料庫
    $b = $b.'as';
    //核心素養指標名稱
    $SQL = "SELECT name FROM $b.map_dsc WHERE used = 1 ORDER BY sn";
    $SQL = $dbh->prepare($SQL);
    $SQL->execute();
    $SQL = $SQL->fetchAll(PDO::FETCH_ASSOC);
    $sqlall['1st'] = $SQL;
  
    //取得全班學生成績
    $SQL = "SELECT a.mission_sn,a.main_data_num, a.unit_id, c.unit_nm, a.stuid, u.uname, a.power_dsc score 
    FROM $b.opt_record a 
    JOIN competency_unit c ON a.unit_id = c.unit_id
    JOIN user_info u ON a.stuid = u.user_id AND u.used = 1
    WHERE a.up_date = 
        (SELECT MAX(up_date) FROM $b.opt_record b 
         WHERE a.stuid = b.stuid AND a.unit_id=b.unit_id AND a.mission_sn=b.mission_sn)
         AND a.mission_sn = $mission_sn";
    $SQL = $dbh->prepare($SQL);
    $SQL->execute();
    $SQL = $SQL->fetchAll(PDO::FETCH_ASSOC); 
    $main_data_num_array = array();
    foreach($SQL as $key => $value){
        $score_array =  $SQL[$key]['score'] ;
        $score_array = explode(",",$score_array);
        $SQL[$key]['score'] = $score_array;
        $main_data_num_array[$key] = $SQL[$key]['main_data_num'];
    }
    $main_data_num_array = array_unique($main_data_num_array);
    //$sqlall['2st']= $main_data_num_array;
    
    //個別單元配分
    foreach($main_data_num_array as $key => $value){
        $main_num = $main_data_num_array[$key];
      
        $SQL = $dbh->prepare("SELECT c_power_dsc dsc, COUNT(DISTINCT questions_data_num)*2 score FROM cpsas.speak_data
        WHERE c_user_type = 0 AND c_power_dsc > '' AND questions_data_num IN
            (SELECT num FROM cpsas.questions_data 
             WHERE operation_data_num IN 
                 (SELECT num FROM cpsas.operation_data 
                 WHERE main_data_num = 107)
            )
        GROUP BY c_power_dsc*1 ASC");

    $SQL->execute();
    $SQL = $SQL->fetchAll(\PDO::FETCH_ASSOC);
        $i = 0;
        while($i<12){
            
            $i++;
        }
    $sqlall['4st'][$main_num] = $SQL;
    }

    //取得全部單元配分
    $SQL = $dbh->prepare("SELECT  
            COUNT(DISTINCT questions_data_num)*2 score 
            FROM $b.speak_data
            WHERE c_user_type = 0 AND c_power_dsc > '' AND questions_data_num IN
                    (SELECT num FROM $b.questions_data 
                    WHERE operation_data_num IN 
                        (SELECT num FROM $b.operation_data 
                        WHERE main_data_num IN($x))
                    )
            GROUP BY c_power_dsc*1 ASC");

    $SQL->execute();
    $SQL =$SQL->fetchAll(\PDO::FETCH_ASSOC);
    $sqlall['3st'] = $SQL;
    echo json_encode($sqlall);
}
?>