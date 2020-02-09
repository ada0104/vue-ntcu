<?php
//紀錄使用者所選擇的學年度，POST後，用SESSION紀錄，避免refresh到預設學年度頁面。
session_start();

if($_POST['seme'] != '') {
	$_SESSION['seme_year'] = $_POST['seme'];
}
$page = empty($_POST['page'])?1:$_POST['page'];
$size = empty($_POST['size'])?10:$_POST['size'];
require_once "HTML/QuickForm.php";
require_once "adp_API.php";
require_once "read_excel.inc.php";
require_once "adp_core_function.php";

$module_name= basename(dirname(__FILE__));
$col_num=1;
$bg = array('#FFFFCC', '#FFCCCC', '#CCFFCC', '#99FFCC', '#CCFF99');
//$bg = array('#f1f6d2', '#f1f6d2', '#f1f6d2', '#f1f6d2');

if(!$auth->checkAuth()){
	require_once "feet.php";
	die();
}

$NumArray=array('007020702','007020601');  //數感單元的版本號
$ChoMadArray=array('006021001','006021002','006021102','006080104');  //選擇題+建構題單元的版本號

if($user_data->access_level>=20 && $_GET['q_user_id']!=$user_data->user_id){
	//EXAM_RESULT_table_header();
	//-- 顯示主畫面

	if(!is_null($_REQUEST['organization']))
		$_SESSION['org']=$_REQUEST['organization'];

		chooseCLASS($_SESSION['org']);


}elseif($_GET['RedirectTo']==1){  //剛測驗完，直接轉址而來
	$pass=0;  //不給予查詢歷來測驗功能
	if($user_data->user_id!="test" || $user_data->user_id!="s001"){
		echo "接下來要印出診斷報告<br><br>但正在維護中，故本次不列出。請由使用上方的「成果查詢」<br><br>抱歉！！";
		die();
	}
} if (in_array($user_data->access_level, USER_PARENTS_GROUP))  { // 家長
	$oChild = $dbh->prepare("SELECT * FROM user_family
		LEFT JOIN user_info ON user_family.user_id = user_info.user_id
		WHERE fuser_id = :fuser_id
		GROUP BY user_family.user_id");
	$oChild->bindValue(':fuser_id', $user_data->user_id, PDO::PARAM_STR);
	$oChild->execute();
	$vChildData = $oChild->fetchAll(\PDO::FETCH_ASSOC);
	$iChildCount = $oChild->rowCount();

	$vStudSelect[] = '<select id ="stud_id" name="stud_id">';
	if (!empty($vChildData)) {
		foreach($vChildData as $vChild) {
			$sAttr = '';
			if ($_POST['stud_id'] === $vChild['user_id']) {
				$sAttr = 'selected';
				$sName = $vChild['uname'];
			}
			$vStudSelect[] = '<option value="'.$vChild['user_id'].'"'.$sAttr.'>'.$vChild['uname'].'</option>';
		}
	}
	else {
		echo '查無學生資料';
		exit();
	}
	$vStudSelect[] = '</select>';
	if (!empty($_POST['stud_id'])) {
		$user_data = new UserData($_POST['stud_id']);
	}
	else {
		$user_data = new UserData($vChildData[0]['user_id']);
	}

	$q_user_id=$user_data->user_id;
	$pass=1;


	// 調整寫法不使用此function Edward.
	// if(!is_null($_REQUEST['organization']))
	// 	$_SESSION['org']=$_REQUEST['organization'];
	// chooseChild($_SESSION['org'], $_SESSION['child_data']);

}else{  //學生使用

	$q_user_id=$user_data->user_id;
	$pass=1;
}

if($pass==1){  //具有查詢的權限，顯示歷來所有測驗單元
	listAllExams_col($q_user_id);
}

$MadArray=array('006');  //建構題的版本號
$PreCsID=substr($_GET['q_cs_id'], 0, 3);  //前三碼版本號
$chkMad=in_array($PreCsID, $MadArray);
$chkChoMad=in_array($_GET['q_cs_id'], $ChoMadArray);
//開放觀看診斷報告之ECD教材單元
$openMad=array('006021114');


if($_GET['report']==1){  //顯示個人測驗結果
	if($_GET['RedirectTo']==1){  //剛測驗完，直接轉址而來，提供測驗結果列印
		$url_p='<a href="modules.php?op=modload&name='.$module_name.'&file=classReports&report=2&q_user_id='.$_GET['q_user_id'].'&q_cs_id='.$_GET['q_cs_id'].'" target="new">';
		$img_p='<img src="'._ADP_URL.'images/print.gif" width="24" height="24" border="0" align="absmiddle" alt="友善列印">';
		//echo $url_p.$img_p."&nbsp; 列印本單元之學習診斷報告書</a><br>";
	}
	if($pass==1){
		echo '<hr>';
	}

	if($chkChoMad){
		personExamResults($_GET['q_user_id'], $_GET['q_cs_id'], $_GET['report']);
	}elseif($chkMad){
		//檢查在ECD教材的版本中，是否有特別要開放的單元
		if(in_array($_GET['q_cs_id'], $openMad)){
			personExamResults($_GET['q_user_id'], $_GET['q_cs_id'], $_GET['report']);
		}else{
			echo '<br><br>「ECD教材」之版本含建構題之判斷功能，診斷報告暫不開放觀看！若有疑問請洽系統管理者<br><br>';
		}
	}else{
		$_SESSION['search_exam_sn']=$_GET[exam_sn];
    $_SESSION['search_mission_sn']=$_GET['mission_sn'];
		personExamResults($_GET['q_user_id'], $_GET['q_cs_id'], $_GET['report'], $_GET['q_paper_sn']);
	}
}elseif($_GET['report']==2){
	PrintOutpersonExamResults($_GET['q_user_id'], $_GET['q_cs_id'], $_GET['report']);
}

function chooseChild($org, $child_data){
	global $dbh,$module_name,$user_data;

	foreach ($child_data as $key=>$value){
		//echo $value["user_id"].',';
		listAllExams_col($value["user_id"]);
	}

}


function chooseCLASS($org){
	global $dbh,$module_name,$user_data;
	$Nowseme=getYearSeme();
	$form = new HTML_QuickForm('frmTest','post','');
	//-- 尋找目前已建立之學校、單位，並初始化"關聯選單"
	//$select1[0]='縣市';
	//$select2[0][0]='學校名稱';
	//$select3[0][0][0]='年級';
	//$select4[0][0][0][0]='班級';
	//$select5[0][0][0][0][0]='學生';
	//echo('<pre>');print_r($user_data);die();
	$_SESSION[userData]=$user_data;
	if( in_array($_SESSION['user_data']->access_level, USER_TEACHER_GROUP) ){
		//看該教師是否有再其他班級授課
		$sql_sems='
			SELECT *
			FROM seme_teacher_subject
			WHERE teacher_id = "'.$user_data->user_id.'"
		';
		//debugBAI( __LINE__,__FILE__, $sql_sems, 'print_r' );
		$re_sems=$dbh->query($sql_sems);
		while( $data_sems=$re_sems->fetch() ){
			$sql_rule[] = $data_sems[seme_year_seme].$data_sems[organization_id].':'.$data_sems[grade].'-'.$data_sems["class"];
		}
		if( count($sql_rule)>0){
			$sql = '
    		select distinct b.seme_year_seme seme, a.city_code, a.organization_id, b.grade, b.class
    		from user_info a,seme_teacher_subject b
    		where a.user_id = "'.$user_data->user_id.'" AND a.user_id = b.teacher_id
    		ORDER BY b.seme_year_seme, a.city_code, a.organization_id, b.grade, b.class
  			';
			$sql_now='Select * From user_info where user_id ="'.$user_data->user_id.'"';
			$user_now =$dbh->query($sql_now);
			$user_nowa=$user_now->fetch();
// 			$user_now->execute();
			$user_class=$Nowseme.$user_nowa['organization_id'].':'.$user_nowa['grade'].'-'.$user_nowa['class'];
			if (! in_array($user_class, $sql_rule)){
				$se=$Nowseme;
				$cc=$user_nowa['city_code'];
				$oi=$user_nowa['organization_id'];
				$gr=$user_nowa['grade'];
				$cl=$user_nowa['class'];
				$select1[$se]=Semester_id2FullName($se);
				$select2[$se][$cc]=id2city($cc);
				$select3[$se][$cc][$oi]=id2org($oi);
				$select4[$se][$cc][$oi][$gr]="$gr 年";
				$select5[$se][$cc][$oi][$gr][$cl]="$cl 班";
				$sql_user = "select b.* from seme_student a,user_info b WHERE a.seme_year_seme='$se' AND a.organization_id='$oi' AND a.grade='$gr' AND a.class='$cl' AND a.stud_id=b.user_id AND b.used = 1 ORDER BY user_id";
				//debugBAI( __LINE__,__FILE__,$sql2,'print_r' );
				$result_user =$dbh->query($sql_user);
				while ($row_user=$result_user->fetch()){
					$uid=$row_user['user_id'];
					$un=$row_user['uname'];
					$select6[$se][$cc][$oi][$gr][$cl][$uid]=substr($uid,7).'-'.$un;
				}
			}

		}else{
			$sql = '
    		select distinct city_code, organization_id, grade, class
    		from user_info
			WHERE organization_id="'.$user_data->organization_id.'"
	    	AND grade="'.$user_data->grade.'"
	    	AND class="'.$user_data->class_name.'"
    		ORDER BY city_code
  			';
		}
	}else {
		$sql = '
    	select distinct city_code, organization_id, grade, class
    	from user_info
    	ORDER BY city_code
  		';
	}




	$result =$dbh->query($sql);

	while ($row=$result->fetch()){
		if($row['seme']==''){
			$se=$Nowseme;
		}else{
			$se=$row['seme'];
		}
		$cc=$row['city_code'];
		$oi=$row['organization_id'];
		$gr=$row['grade'];
		$cl=$row['class'];
		$select1[$se]=Semester_id2FullName($se);
		$select2[$se][$cc]=id2city($cc);
		$select3[$se][$cc][$oi]=id2org($oi);
		$select4[$se][$cc][$oi][$gr]="$gr 年";
		$select5[$se][$cc][$oi][$gr][$cl]="$cl 班";
		$sql2 = "select b.* from seme_student a,user_info b WHERE a.seme_year_seme='$se' AND a.organization_id='$oi' AND a.grade='$gr' AND a.class='$cl' AND a.stud_id=b.user_id AND b.used = 1 ORDER BY a.stud_id";
		//debugBAI( __LINE__,__FILE__,$sql2,'print_r' );
		//select b.* from seme_student a,user_info b WHERE a.seme_year_seme='$se' AND a.organization_id='$oi' AND a.grade='$gr' AND a.class=$cl' AND a.stud_id=b.user_id ORDER BY user_id
		$result2 =$dbh->query($sql2);
		while ($row2=$result2->fetch()){
			$uid=$row2['user_id'];
			$un=$row2['uname'];
			$select6[$se][$cc][$oi][$gr][$cl][$uid]=$un.' - '.substr($uid,7);
		}
	}


	//-- 顯示選單
	echo('
	 <div class="content2-Box">
       <div class="main-box">
            <div class="left-box discuss-select">
               <a href="modules.php?op=modload&name=ExamResult&file=classErrorStatistics&set_opt=classErrorStatistics" class="btn02">班級學習狀態</a>
			   <a href="modules.php?op=modload&name=ExamResult&file=classesInfo_teacher" class="btn02">班級節點狀態</a>
               <a href="modules.php?op=modload&name=ExamResult&file=classReports" class="btn02">學生診斷報告</a>
               <a href="modules.php?op=modload&name=learn_video&file=video_report_list" class="btn02">影片瀏覽報告</a>
            </div>
            <div class="right-box">
            	<div class="title01">學生診斷報告</div>
            	<div class="class-list2 test-search table_scroll">
  ');
	$optionEvent = ' class="input-normal" ';
	$sel =& $form->addElement('hierselect', 'organization', '', $optionEvent);  //關聯式選單
	$sel->setOptions(array($select1, $select2, $select3, $select4, $select5, $select6));
	$form->addElement('hidden','op','modload');
	$form->addElement('hidden','name',$module_name);
	$form->addElement('hidden','file','classReports');
	$btnEvent = 'style="display: block; margin: 0 auto;" class="btn04"';
	$form->addElement('submit','chooseCLASS','送出', $btnEvent);
	$form->addRule('organization', '「服務單位」不可空白！', 'nonzero', null, 'client', null, null);
	$form->setRequiredNote('前有<font color=red>*</font>的欄位不可空白');
	$selected = array("organization"=>$org);
	$form->setDefaults($selected);
	$form->display();

	echo('      </div>');
	if(!is_null($_SESSION['org']))
		listAllExams_col( $_SESSION['org']['5'] );
		//listAllExams_row($_SESSION['org']['4']);
		echo('
            </div>
       </div>
    </div>
  ');
	// debugBAI('', 'chooseCLASS', '');
}



function listUSER($org){
	global $dbh, $user_data, $module_name, $col_num, $bg;

	$_SESSION['org']=$org;
	$class_name=id2city($org[0])."&nbsp;".id2org($org[1])."&nbsp;".$org[2]."年&nbsp;".$org[3]."班";
	echo '<font class="title"><b>'.$class_name.'</b></font><br>';
	echo '<table width="95%" border="1" cellpadding="1" cellspacing="1" bordercolor="#0000FF" ';
	echo '<tr>';

	for($i=1;$i<=$col_num;$i++){
		echo '<td align="center"><font class="title"><b>帳號</b></font></td>';
		echo '<td align="center"><font class="title"><b>姓名</b></font></td>';
	}
	echo '</tr>';
	$class_data=new ClassData($org);   //產生班級基本資料物件
	//print_r($class_data);
	for($i=0;$i<sizeof($class_data->member);$i++){
		$bg_count=($i+1)%$col_num;
		if($bg_count==1){
			echo '<tr>';
		}
		$class_member = explode(_SPLIT_SYMBOL, $class_data->member[$i]);
		$url='<a href="modules.php?op=modload&name='.$module_name.'&file=classReports&q_user_id='.$class_member[1].'">';
		echo '<td align="left" bgcolor="'.$bg[$bg_count].'">'.$class_member[1].'</td>';  //帳號
		echo '<td align="left" bgcolor="'.$bg[$bg_count].'">'.$url.$class_member[2].'</a></td>';  //姓名
		if($bg_count==0){
			echo '</tr>';
		}
	}
	if($bg_count!=0){
		echo '</tr>';
	}
	echo "</table>";
	// debugBAI('', 'listUSER', '');
}

function listAllExams_row($user_id){
	global $dbh, $module_name, $bg, $user_data;

	$col_num=5;
	if($user_id!=$user_data->user_id && $user_data->access_level<20){
		echo "<br><br>非本人不可查詢。<br><br>";
		//include_once "feet.php";
		die();
	}

	$q_uname=id2uname($user_id);
	$bgcolor=array('white','ffeaff');
	//撈出學生的作答紀錄
	/*$sql="
		select *
		from exam_record
		WHERE user_id='$user_id'
		group by cs_id
		ORDER BY exam_sn
		";*/
	$sql="(select exam_sn, user_id,cs_id, paper_vol from exam_record a WHERE a.user_id='$user_id' group by a.cs_id ORDER BY a.exam_sn)
	UNION (select exam_sn, user_id,cs_id, '1' as paper_vol from exam_record_indicate b WHERE b.user_id='$user_id' group by b.cs_id ORDER BY b.exam_sn)";
	$result =$dbh->query($sql);
	$i=0;
	while ($row=$result->fetch()){

		//$chkCSID = $row['cs_id'];
		//$bg_count=($i+1)%$col_num;
		$my_csid=explode_cs_id($row['cs_id']);
		$my_p=id2publisher(intval($my_csid[0])).' ';
		$my_s=id2subject(intval($my_csid[1]));
		$my_v="第".intval($my_csid[2])."冊";
		$my_u="第".intval($my_csid[3])."單元";
		$my_csname=id2csname($row['cs_id']);
		//能力指標題庫(科目)
		$cs_title=$my_p.$my_s.$my_v.$my_u.'【'.$my_csname.'】';
		$paper_vol=$row['paper_vol'];
		//$url='<a href="modules.php?op=modload&name='.$module_name.'&file=classReports&report=1&q_user_id='.$user_id.'&q_cs_id='.$row['cs_id'].'&q_pvol='.$row['paper_vol'].'">';
		$url='<a href="modules.php?op=modload&name='.$module_name.'&file=classReports&report=1&q_user_id='.$user_id.'&q_cs_id='.$row['cs_id'].'&q_pvol='.$row['paper_vol'].'&exam_sn='.$row[exam_sn].'">';
		$tmpHtml[ $row['cs_id'] ]= '
      <tr>
        <td >'.$url.$cs_title.'</a>
    ';
		$i++;
	}
	if( is_array($tmpHtml) ) $csList = implode('',$tmpHtml);
	else $csList='<tr> <td>沒有作答紀錄';
	$reportHtml[]='
    <div class="right-box">
      <div class="after-20"></div>
      <div class="table_scroll">
        <table class="datatable datatable-l">
          <tr> <th>'.$user_id.'【'.$q_uname.'】歷來測驗單元'.$_SESSION['seme_year'].'
          '.$csList.'
        </table>
      </div>
    </div>
  ';

	echo implode( '', $reportHtml );
	// debugBAI('', 'listAllExams_row', $reportHtml);
}



function listAllExams_col($user_id){  //學生用
	global $dbh, $module_name, $col_num, $bg, $vStudSelect, $totalPage, $page, $size;

	$show_flag = false;
	$q_uname=id2uname($user_id);
	//(select count(*) from exam_record WHERE user_id='190041-s61605' GROUP by user_id) UNION (SELECT COUNT(*) FROM exam_record_indicate WHERE user_id = '190041-s61605')
	//fuyun@20180321 新增 exam_record_indicator 題庫單元
	/*
  $sql="(select count(*) from exam_record WHERE user_id='$user_id'  GROUP by user_id)
	UNION (SELECT count(*) FROM exam_record_indicate WHERE user_id = '$user_id')
	UNION (SELECT count(*) FROM exam_record_indicator WHERE user_id='$user_id')";
	$data = $dbh->query($sql);
	$row = $data->fetch();
  */
  //為了速度改變方式  fuyun@20180705
  $sql='(SELECT user_id FROM exam_record
        WHERE user_id=:user_id1
        LIMIT 1)
        UNION
        (SELECT user_id FROM exam_record_indicate
        WHERE user_id=:user_id2
        LIMIT 1)
        UNION
        (SELECT user_id FROM exam_record_indicator
        WHERE user_id=:user_id3
				LIMIT 1)
				UNION
        (SELECT user_id FROM exam_record_indicator_bat
        WHERE user_id=:user_id4
        LIMIT 1)';
  $result=$dbh->prepare($sql);
  $result->bindParam(':user_id1',$user_id, PDO::PARAM_STR);
  $result->bindParam(':user_id2',$user_id, PDO::PARAM_STR);
	$result->bindParam(':user_id3',$user_id, PDO::PARAM_STR);
	$result->bindParam(':user_id4',$user_id, PDO::PARAM_STR);
  $result->execute();
  $dataList=$result->fetchall();
  $dataCount=count($dataList);

	//$row['count(*)'] = 1;
	//if($row['count(*)']==0){
/*   if($dataCount==0){
		echo "<center><br><br><br>目前沒有任何測驗記錄可供查詢！<br><br><br><br></center>";
		require_once("die_feet.php");
		//die();
	}else  */$show_flag = true;

	if($show_flag) {
		$bgcolor=array('white','ffeaff');
		//170808，新增學年度查詢，此sql為撈學年度用。
		$search_year = $dbh->prepare("SELECT seme_student.seme_year_seme FROM seme_student GROUP BY seme_year_seme ORDER BY seme_year_seme DESC");
		$search_year->execute();
		$study_year_data = $search_year->fetchAll(\PDO::FETCH_ASSOC);
		$study_year = array();
		//將撈到的學念度的資料存進陣列
		// foreach ($study_year_data as $key=>$value){
		// 	//因抓出來的資料為1061或1052，要將字串做處理，只抓取前三位的學年度。
		// 	switch (substr($value["seme_year_seme"],3,1)) {
		// 		case '1':
		// 			$sSem = '(上)';
		// 			break;
    //
		// 		case '2':
		// 			$sSem = '(下)';
		// 			break;
		// 	}
    //
		// 	$study_year[$value["seme_year_seme"]]["seme_year"] = substr($value["seme_year_seme"],0,3).$sSem;
		// }

    foreach ($study_year_data as $key => $value) {
      $seme_year_seme = substr($value["seme_year_seme"], 0, 3);
      $study_year[$seme_year_seme]["seme_year"] = $seme_year_seme;
    }


		//170808，增加搜尋條件[學年度]。增加搜尋表[exam_record]、搜尋欄位[seme_year]、條件[record.seme_year=使用者所選學年度]
		///$select_seme_year用來存放user選擇的學年度的值。
		$select_seme_year;
		//$semeYear_to_CEyear;
		//$semeYear_to_NextCEyear;

		//170821，選擇診斷報告後，避免refresh到預設頁面，讓頁面停留在使用者所選擇的學年度。
		//新增判斷條件$_SESSION['seme_year'] == ''，如果session值為空，代表第一次進入，才會預設學年度。
		if(isset($_POST["seme"])=='' && $_SESSION['seme_year'] == '') {
			$select_seme_year=getNowSemeYear();
			//if抓到106，則時間區段為2017/9~2018/8
			//$semeYear_to_CEyear = $select_seme_year + 1911; //民國106-->西元2017
			//$semeYear_to_NextCEyear = $semeYear_to_CEyear + 1; //2017 +1 = 2018

		} elseif($_SESSION['seme_year'] != '') {
			//170821，重新load頁面時，不會有POST值，而如果session不為空，則將學年度設為session值，為使用者之前所選擇的年度。
			$select_seme_year = $_SESSION['seme_year'];

		}else {
			//因抓出來的資料為1061或1052，要將字串做處理，只抓取前三位的學年度。
			$select_seme_year = substr($_POST["seme"],0,3);

			//$semeYear_to_CEyear = $select_seme_year + 1911; //if民國106-->西元2017
			//$semeYear_to_NextCEyear = $semeYear_to_CEyear + 1; //2017 +1 = 2018
		}


		//撈出學生的作答紀錄
		//$sql="select * from exam_record WHERE user_id='$user_id' group by exam_title";
		//因為 group by，所以只會抓出一組exam_title的作答資料
		$sql="
		select *
		from exam_record
		WHERE user_id='$user_id' AND seme_year='$select_seme_year'
		ORDER BY exam_title, exam_sn
		";
		/*$sql="(select exam_title, exam_sn, user_id,cs_id, paper_vol, score from exam_record a WHERE a.user_id='$user_id' ORDER BY a.exam_title, a.exam_sn)
		 UNION (select cs_id as exam_title, exam_sn, user_id, cs_id, '1' as paper_vol, 'NAN' as score from exam_record_indicate b WHERE b.user_id='$user_id' ORDER BY b.cs_id, b.exam_sn)";*/
		//debug_msg("第".__LINE__."行 sql ", $sql);
		$result =$dbh->query($sql);

		/*$sql2="select DISTINCT result_sn as exam_title, indicate.exam_sn, indicate.user_id, indicate.cs_id, '1' as paper_vol, 'NAN' as score, indicate.date FROM exam_record_indicate indicate WHERE indicate.user_id='".$user_id."' AND indicate.date BETWEEN '".$semeYear_to_CEyear."-08%' AND '".$semeYear_to_NextCEyear."-07%' ORDER BY indicate.cs_id, indicate.exam_sn";*/
		//縱貫測驗的資料
    $sql2 =
      "SELECT DISTINCT
        result_sn as exam_title,
        indicate.exam_sn,
        indicate.user_id,
        indicate.cs_id,
        '1' as paper_vol,
        IFNULL(score, -1) AS score,
        indicate.date
      FROM exam_record_indicate indicate, mission_info info
      WHERE indicate.user_id = '$user_id'
        AND info.mission_sn = indicate.result_sn
        AND info.semester LIKE '".$select_seme_year."%'
      ORDER BY indicate.cs_id, indicate.exam_sn";

				$result2 =$dbh->query($sql2);

				while ($row=$result->fetch()){
				//$bg_count=($i+1)%$col_num;
					$i[ $row['exam_title'] ]+=1;
					$my_csid=explode_cs_id($row['cs_id']);
					$my_p=id2publisher(intval($my_csid[0])).' ';
					$my_s=id2subject(intval($my_csid[1]));
					$my_v="第".intval($my_csid[2])."冊";
					$my_u="第".intval($my_csid[3])."單元";
					$my_csname = id2csname($row['cs_id']);

          $cs_title = $my_p.'：'.$my_s.$my_v.$my_u.'【'.$my_csname.'】';
          $paper_vol = $row['paper_vol'];
					//$url='<a href="modules.php?op=modload&name='.$module_name.'&file=classReports&report=1&q_user_id='.$user_id.'&q_cs_id='.$row['cs_id'].'&q_pvol='.$row['paper_vol'].'&exam_sn='.$row[exam_sn].'">';
					$url='modules.php?op=modload&name='.$module_name.'&file=classReports&report=1&q_user_id='.$user_id.'&q_cs_id='.$row['cs_id'].'&q_pvol='.$row['paper_vol'].'&exam_sn='.$row[exam_sn].'&examTime='.$i[ $row['cs_id'] ];
					if( $_GET[exam_sn]==$row[exam_sn] ) $optionSel = 'selected';
					else $optionSel = '';
          if ($row[score] > -1) {
            $selOption[$row[cs_id]][]='<option value="'.$url.'" '.$optionSel.'> 卷'.$paper_vol.'第'.($i[ $row['exam_title'] ]).'次測驗('.$row['date'].')。答對比率：'.$row[score].'%</option>'; //num2English($paper_vol)
          } else {
            // check
            $selOption[$row[cs_id]][]='<option value="'.$url.'" '.$optionSel.'> 卷'.$paper_vol.'第'.($i[ $row['exam_title'] ]).'次測驗('.$row['date'].')。</option>';//num2English($paper_vol)
          }
          $cs_titleAry[$row[cs_id]] = [$cs_title, $row['date']];
        }

        while ($row2=$result2->fetch()) {
          $sql_mission = 'SELECT * FROM `mission_info` WHERE mission_sn="'.$row2['exam_title'].'"';
          $re_mission = $dbh->query($sql_mission);
          $data_mission = $re_mission->fetch();
          $i[$row2['exam_title']] += 1;
          $my_csid = explode_cs_id($row2['cs_id']);

          if (empty($data_mission['mission_nm'])) {
            $cs_title = '縱貫：【'.id2csname($row2['cs_id']).' (未命名)】';
          } else {
            $cs_title = '縱貫：【'.$data_mission['mission_nm'].'】';
          }

          $paper_vol=$row2['paper_vol'];
          $url='modules.php?op=modload&name='.$module_name.'&file=classReports&report=1&q_user_id='.$user_id.'&q_cs_id='.$row2['cs_id'].'&q_pvol='.$row2['paper_vol'].'&exam_sn='.$row2[exam_sn].'&examTime='.$i[ $row2['cs_id'] ].'&mission_sn='.$row2['exam_title'];

          if ($_GET[exam_sn] == $row2[exam_sn]) {
            $optionSel = 'selected';
          } else {
            $optionSel = '';
          }

          // check
          $selOption[$row2[exam_title]][]='<option value="'.$url.'" '.$optionSel.'>第'.($i[ $row2['exam_title'] ]).'次測驗('.$row2['date'].')。答對比率：'.$row2['score'].'% </option>';
          $cs_titleAry[$row2[exam_title]] = [$cs_title, $row2['date']];
        }

        //fuyun@20180321  讀取 exam_record_indicator 題庫單元的資料
        // check
        $sql3 =
          "SELECT
            exam_record_indicator.exam_sn
            ,exam_record_indicator.paper_sn
            ,mission_info.mission_sn
            ,exam_record_indicator.date
            ,mission_info.mission_nm
            ,IFNULL(exam_record_indicator.score, 'N') AS score
          FROM exam_record_indicator , mission_info
          WHERE exam_record_indicator.user_id=:user_id
            AND mission_info.semester LIKE :semester
            AND exam_record_indicator.mission_sn=mission_info.mission_sn
          ORDER BY exam_record_indicator.paper_sn, exam_record_indicator.exam_sn";

        $sql3_data=$dbh->prepare($sql3);
        $sql3_data->bindValue(":user_id",$user_id,PDO::PARAM_STR);
        $select_seme_year_str=$select_seme_year.'%';  //上下學期都取
        $sql3_data->bindValue(":semester",$select_seme_year_str,PDO::PARAM_STR);
				$sql3_data->execute();
        while ($row3=$sql3_data->fetch(PDO::FETCH_ASSOC)){

          if (empty($row3['mission_nm'])) {
            $title = '單元：【'.$row3['mission_sn'].' (未命名)】';
          } else {
            $title = '單元：【'.$row3['mission_nm'].'】';
          }
          $i[ $row3['mission_sn'] ] += 1;
          $cs_titleAry[$row3['mission_sn']] = [$title, $row3['date']];

          if ($_GET['exam_sn'] == $row3['exam_sn']) {
            $optionSel = 'selected';
          } else {
            $optionSel = '';
          }
          $url='modules.php?op=modload&name='.$module_name.'&file=classReports&report=1&q_user_id='.$user_id.'&q_paper_sn='.$row3['paper_sn'].'&exam_sn='.$row3['exam_sn'].'&examTime='.$i[ $row3['paper_sn'] ].'&mission_sn='.$row3['mission_sn'];
          //debug_msg("第".__LINE__."行 url ", $url);
          $selOption[$row3['mission_sn']][]='<option value="'.$url.'" '.$optionSel.'>第'.($i[ $row3['mission_sn'] ]).'次測驗('.$row3['date'].')。答對比率：'.$row3['score'].'% </option>';
          //debug_msg("第".__LINE__."行 selOption ", $selOption[$row3['mission_sn']]);
        }
				unset($i);
				//學力測驗的資料
				$sql4 =
				"SELECT
					exam_record_indicator_bat.exam_sn
					,exam_record_indicator_bat.paper_sn
					,mission_info.mission_sn
					,exam_record_indicator_bat.date
					,mission_info.mission_nm
					,IFNULL(exam_record_indicator_bat.score, 'N') AS score
				FROM exam_record_indicator_bat , mission_info
				WHERE exam_record_indicator_bat.user_id=:user_id
					AND mission_info.semester LIKE :semester
					AND exam_record_indicator_bat.mission_sn=mission_info.mission_sn
				ORDER BY exam_record_indicator_bat.paper_sn, exam_record_indicator_bat.exam_sn";

			$sql4_data=$dbh->prepare($sql4);
			$sql4_data->bindValue(":user_id",$user_id,PDO::PARAM_STR);
			$select_seme_year_str=$select_seme_year.'%';  //上下學期都取
			$sql4_data->bindValue(":semester",$select_seme_year_str,PDO::PARAM_STR);
			$sql4_data->execute();
			while ($row4=$sql4_data->fetch(PDO::FETCH_ASSOC)){

        if (empty($row4['mission_nm'])) {
          $title = '學力模擬：【'.$row4['mission_sn'].' (未命名)】';
        } else {
          $title = '學力模擬：【'.$row4['mission_nm'].'】';
        }

				$i[ $row4['mission_sn'] ] += 1;
				$cs_titleAry[$row4['mission_sn']] = [$title, $row4['date']];
				if( $_GET['exam_sn']==$row4['exam_sn'] ){
					$optionSel = 'selected';
				}else{
					$optionSel = '';
				}
				$url='modules.php?op=modload&name='.$module_name.'&file=classReports&report=1&q_user_id='.$user_id.'&q_paper_sn='.$row4['paper_sn'].'&exam_sn='.$row4['exam_sn'].'&examTime='.$i[ $row4['paper_sn'] ].'&mission_sn='.$row4['mission_sn'].'&bat=true';
				//debug_msg("第".__LINE__."行 url ", $url);
				$selOption[$row4['mission_sn']][]='<option value="'.$url.'" '.$optionSel.'>第'.($i[ $row4['mission_sn'] ]).'次測驗('.$row4['date'].')。答對比率：'.$row4['score'].'% </option>';
				//debug_msg("第".__LINE__."行 selOption ", $selOption[$row3['mission_sn']]);
			}
			unset($i);
				//頁面排序
					switch($_POST['sort_type']){
						case '':
						case 'time':
							$sort_type = 1;
							break;
						case 'name':
							$sort_type = 0;
							break;
					}
					switch($_POST['exam_sort']){
						case '':
						case 'desc':
							uasort($cs_titleAry, function($a, $b) use($sort_type){
								if($a[$sort_type] == $b[$sort_type]) return 0;
								return ($a[$sort_type]<$b[$sort_type])?1:-1;
							});
							break;
						case 'asc':
							uasort($cs_titleAry, function($a, $b) use($sort_type){
								if($a[$sort_type] == $b[$sort_type]) return 0;
								return ($a[$sort_type]<$b[$sort_type])?-1:1;
							});
							break;
					}
				//頁數計算
				$totalPage = count($cs_titleAry);
				foreach( array_slice($cs_titleAry, ($page-1) * $size, $size, true) as $key=>$val ){
					if($_POST['exam_sort'] != 'asc'){
						$selOption[$key] = array_reverse($selOption[$key]);
					}
					$i++;
					$tmpHtml[$key]= '
				      <tr>
				        <td >'.(($page-1) * $size + $i).'
				        <td >'.$val[0].'
				        <td ><select class="sel_vol"> <option value="stop">請選擇</option>'.implode('', $selOption[$key]).' </select>
				    ';

				}
				//#935 ADA 移除選單bar class=title01、left-box 加入style="margin-top:3%;"
				// $reportHtml[]='<div class="content2-Box">';
				// //$reportHtml[]='<div class="path">目前位置：診斷報告</div>';
				// if(!in_array($_SESSION['user_data']->access_level, USER_TEACHER_GROUP)){
				// 	$reportHtml[]='<div class="choice-box">';
				// 	$reportHtml[]='<div class="choice-title">診斷</div>';
				// 	$reportHtml[]='<ul class="choice work-cholic">';
				// 	$reportHtml[]='<li><a href="modules.php?op=modload&name=ExamResult&file=classReports" class="current"><i class="fa fa-caret-right"></i>診斷報告</a></li>';
				// 	$reportHtml[]='<li><a href="modules.php?op=modload&name=ExamResult&file=record_list"><i class="fa fa-caret-right"></i>診斷報告(CPS)</a></li>';
				// 	$reportHtml[]='<li><a href="modules.php?op=modload&name=ExamResult&file=record_list_gc"><i class="fa fa-caret-right"></i>診斷報告(GC)</a></li>';
				// 	$reportHtml[]='</ul>';
				// 	$reportHtml[]='</div>';
				// }
				// $reportHtml[]='<div class="main-box">';
				//170808，新增學年度下拉式選單。
				$nameSelected=($_POST['sort_type'] == 'name')?"selected":"";
				$examSelected=($_POST['exam_sort'] == 'asc')?"selected":"";
				if(!in_array($_SESSION['user_data']->access_level, USER_TEACHER_GROUP)){
					$reportHtml[]='<div class="left-box"style="margin-top:3%;">';
					$reportHtml[]='<form id="CRform" method="POST" action="modules.php?op=modload&name=ExamResult&file=classReports">';
					if (in_array($_SESSION['user_data']->access_level, USER_PARENTS_GROUP))  { // 家長
						$reportHtml[]= implode('', $vStudSelect);
					}
					$reportHtml[]='<select id=seme name="seme">';
					$reportHtml[]='<option value="">請選擇(學年度)</option>';



					foreach ($study_year as $key=>$value){

						if($_POST["seme"] == $value["seme_year"] && $_POST["btn02"]!=""){
							$att = "selected=\"selected\"";
						}else $att = "";
						//$reportHtml[]='<option value="'.$value["seme_year"].'"'.$att.">".$value["seme_year"]."學年度</option>";
						//調整 option value
						$reportHtml[]='<option value="'.$key.'"'.$att.">".$value["seme_year"]."學年度</option>";
					}
					$reportHtml[]='</select>';
					$reportHtml[]='<select name="sort_type"><option value="time">時間排序</option></select>';
          // $reportHtml[] = '<option value="name" '.$nameSelected.'>名稱排序</option><select>';
          $reportHtml[]='<select name="exam_sort"><option value="desc">新->舊</option><option value="asc" '.$examSelected.'>舊->新</option><select>';
					$reportHtml[]="<input name='page' type='hidden' value='$page'>";
					$reportHtml[]="<input name='size' type='hidden' value='$size'>";
					$reportHtml[]='<input  style="width: 160px; display: inline;" id="btn02" name="btn02" type="submit" class="btn02" value="提交">';
					$reportHtml[]='</form>';
					$reportHtml[]='</div>';
					$reportHtml[]='<div class="right-box">';
				}
				$reportHtml[]='<div class="title01" style="margin-top:3%;">'.$user_id.'【'.$q_uname.'】'.$_SESSION['seme_year'].'學年度測驗單元</div>';
				if(in_array($_SESSION['user_data']->access_level, USER_TEACHER_GROUP)){
					$reportHtml[]='<form id="CRform" method="POST" action="modules.php?op=modload&name=ExamResult&file=classReports">';
					$reportHtml[]='<select id=seme name="seme" style="width: 160px;>';
					$reportHtml[]='<option value="">請選擇(學期)</option>';

					foreach ($study_year as $key=>$value){

						if($_POST["seme"] == $value["seme_year"] && $_POST["btn02"]!=""){
							$att = "selected=\"selected\"";
						}else $att = "";
						$reportHtml[]='<option value="'.$key.'"'.$att.">".$value["seme_year"]."學年度</option>";
					}
					$reportHtml[]='</select>';
					$reportHtml[]=' <select name="sort_type" style="width: 160px;"><option value="time">時間排序</option></select>';
          // $reportHtml[] = '<option value="name" '.$nameSelected.'>名稱排序</option><select>';
					$reportHtml[]=' <select name="exam_sort" style="width: 160px;"><option value="desc">新->舊</option><option value="asc" '.$examSelected.'>舊->新</option><select>';
					$reportHtml[]="<input name='page' type='hidden' value='$page'>";
					$reportHtml[]="<input name='size' type='hidden' value='$size'>";
					$reportHtml[]='<input  style="width: 160px;display: inline;margin-left: 20px;" display: inline;" id="btn02" name="btn02" type="submit" class="btn02" value="提交">';
					$reportHtml[]='</form>';
				}


				$reportHtml[]='<div class="table_scroll">';
				$reportHtml[]='<table class="datatable datatable-l">';
				$reportHtml[]='<tr> <th>編號 <th>單元診斷 與 縱貫任務名稱 <th>診斷報告';
				$reportHtml[]=implode('',$tmpHtml)?:'<tr><td colspan=3>目前沒有任何測驗記錄可供查詢！</td></tr>';
				$reportHtml[]='</table>';
				if(!in_array($_SESSION['user_data']->access_level, USER_TEACHER_GROUP)){
					$reportHtml[]='</div></div></div></div>';
				}else{
					$reportHtml[]='</div></div></div>';
				}
				$reportHtml[]='</div>';
				$reportHtml[]='
			    <script>
					$(document).ready(function () {
						$.LoadingOverlay("hide");
					});
						$(".btn02").click(function() {
							$.LoadingOverlay("show");
						});
						$("ul.choice > li > a").click(function() {
							$.LoadingOverlay("show");
						});
			      $(".sel_vol").on("change",function(){
			        if( $(this).val()==="stop" ) return true;
							$.LoadingOverlay("show");
			       	document.location.href = $(this).val();
			    } );

			    </script>
			  ';
				echo implode( '', $reportHtml );
	}
// debugBAI('', 'listAllExams_col', $reportHtml);
}

function personExamResults($q_user_id, $q_cs_id, $report_for_pc, $q_paper_sn=0){
	global $dbh, $module_name, $NumArray, $ChoMadArray;
  //echo "<hr>"
  //-- 輸出PC版  report_for_pc==1
  $PreCsID=substr($q_cs_id, 0, 3);
  $c=in_array($q_cs_id, $NumArray);
  $cma=in_array($q_cs_id, $ChoMadArray);
  //var_dump($q_cs_id);

  if ($PreCsID == '017') {
    require_once "Diag_Report_DINA.php";
    $a = new Diag_Report_DINA($q_user_id, $q_cs_id, $report_for_pc);

  } elseif ($PreCsID == '018') {
    require_once "Diag_Report_LSA.php";
    $a = new Diag_Report_LSA($q_user_id, $q_cs_id, $report_for_pc, $_REQUEST['q_pvol']);

  } elseif ($PreCsID == '001') {
    if($q_cs_id == '001070103'){
      require_once "Diag_Report_Stage.php";
      $a = new Diag_Report_Stage($q_user_id, $q_cs_id, $report_for_pc,$_REQUEST['q_pvol']);
    }
    //require_once "Diag_Report_Stage.php";
    //$a=new Diag_Report_Stage($q_user_id, $q_cs_id, $report_for_pc, $_REQUEST['q_pvol']);

  } elseif ($c) {
    require_once "Diag_Report_NumSense.php";
    $a = new Diag_Report_NumSense($q_user_id, $q_cs_id, $report_for_pc);

  } elseif ($cma) {

    if ($q_cs_id == '006080104') {
      require_once "Diag_Report_ChoMad_Modes.php";
      $a = new Diag_Report_ChoMad_Modes($q_user_id, $q_cs_id, $report_for_pc);;

    } else {
      require_once "Diag_Report_ChoMad.php";
      $a = new Diag_Report_ChoMad($q_user_id, $q_cs_id, $report_for_pc);
    }
  } else {
    // 190508 Ariel.學生診斷報告增加說明文字，暫只增加於此分類
    $a = new Print_Student_Data($q_user_id, $q_cs_id, $report_for_pc);
  }

	$prt[0]=$a->print_header($report_for_pc)."<br>";  //標頭
	//echo $prt[0];
	$prt[1]=$a->print_student_basic_data($report_for_pc)."<br>";  //學生基本資料
	//echo $prt[1];
	$prt[2]=$a->print_unit_data($report_for_pc)."<br>";  //學習單元資訊
	//echo $prt[2];
	//$prt[2]=$a->print_least_data()."<br>";   //最近一次測驗結果
	//echo $prt[2];
	$prt[3]=$a->print_graphic_data($q_cs_id, $report_for_pc)."<br>";   //百分等級圖
	//echo $prt[3];
	//$prt[4]=$a->print_concept_history_data($report_for_pc)."<br>";   //本單元歷來學習記錄
	//echo $prt[4];
	//$prt[5]=$a->print_sturcture_gif();   //知識結構圖
	//echo $prt[5];

  if ($q_cs_id == '004020912') {
    $prt[6]='';
    $a->print_graphical_remedy_data($report_for_pc)."<br>";   //圖形化概念診斷報告;
    //$prt[6].=$a->print_remedy_data($report_for_pc)."<br>";
    // echo 'print_graphical_remedy_data ';

  } elseif ($q_cs_id=='004020905'){
    ;
  } else {
    $ep_id=$_REQUEST[q_cs_id].sprintf('%02d',$_REQUEST[q_pvol]);
    if($_SESSION['search_mission_sn']==0){
      $prt[6]=$a->print_remedy_data($report_for_pc,$_REQUEST[exam_sn],$ep_id)."<br>";   //概念診斷報告
      // echo 'print_remedy_data ';

    } else {
      //測驗結果
      if ($_GET[bat] == true && $q_paper_sn > 0) {
        //學力
        require_once './modules/indicatorTestBAT/indicatorTestBATResult.php';
        $prt[6] = indicatorTestBATResult($_SESSION['search_exam_sn'],$q_user_id,$_SESSION['search_mission_sn'])."<br>";
        // echo 'indicatorTestBATResult ';

      } elseif ($report_for_pc == 1 && $q_paper_sn > 0) {
        //單元
        require_once './modules/indicatorTest/indicatorTestResult.php';
        $prt[6] = indicatorTestResult($_SESSION['search_exam_sn'],$q_user_id,$_SESSION['search_mission_sn'])."<br>";
        // echo 'indicatorTestResult ';

      } else {
        //縱貫
        require_once "./modules/indicateTest/adaptiveExamResult.php";
        $prt[6] = adaptiveExamResult($q_user_id,$_SESSION['search_mission_sn'],$_SESSION['search_exam_sn'])."<br>";
        // echo 'adaptiveExamResult ';

      }
    }

  }
	//echo $prt[6];
	echo '
		<div id="inline-content" class="personal-inline">'.$prt[6].'</div>
		<a id="autoClick" class="venoboxinline"  data-title="學生診斷報告" data-gall="gall-frame2" data-vbtype="inline" href="#inline-content"> </a>
		<script>
			$(document).ready(function () {
				$("#autoClick").click();
			});
			$("#autoClick").on( "click", function(){
				;
			} );
		</script>
	';

	$prt[7]=$a->print_feet($report_for_pc);   //標尾
	echo $prt[7];
	if($PreCsID=='018'){
		$q_paper_vol=$_REQUEST['q_pvol'];
		$ac=explode_cs_id($q_cs_id);
		$RedirectTo="modules.php?op=modload&name=LSA&file=creatITEM&cs_id=".$q_cs_id."&paper_vol=".$q_paper_vol."&user_id=".$q_user_id."&type=LSA&unit_item[0]=".$ac[0]."&unit_item[1]=".$ac[1]."&unit_item[2]=".$ac[2]."&unit_item[3]=".$ac[3];
		echo '<center><br><br>【<a href="'.$RedirectTo.'" target="_blank">自我出題練習</a>】<br><br><br></center>';
	}
	// debugBAI('', 'personExamResults', $prt);
}



function PrintOutpersonExamResults($q_user_id, $q_cs_id, $report_for_pc){
	global $dbh, $module_name, $NumArray, $ChoMadArray;

	$c=in_array($q_cs_id, $NumArray);
	$cma=in_array($q_cs_id, $ChoMadArray);
	//-- 輸出報表   $report_for_pc==2
	$print_file=$q_user_id.$q_cs_id.'.htm';
	$print_file_loc=_ADP_TMP_UPLOAD_PATH.$print_file;
	$_SESSION['dfn']=$print_file;
	$prt[0]='<html><head>
		<meta http-equiv="Content-Type" content="text/html; charset=big5">
		<title>BNAT-學習診斷報告</title>
		<link href="'._THEME_CSS.'" rel="stylesheet" type="text/css" />
		</head>
		<body onload="self.print();">';
	if ($report_for_pc==2 && $fp = fopen($print_file_loc, "w+")) {  //可下載列印
		$PreCsID=substr($q_cs_id, 0, 3);
		if($PreCsID=='017'){
			require_once "Diag_Report_DINA.php";
			$a=new Diag_Report_DINA($q_user_id, $q_cs_id, $report_for_pc);
		}elseif($c){
			require_once "Diag_Report_NumSense.php";
			$a=new Diag_Report_NumSense($q_user_id, $q_cs_id, $report_for_pc);
		}elseif($cma){
			if($q_cs_id=='006080104'){
				require_once "Diag_Report_ChoMad_Modes.php";
				$a=new Diag_Report_ChoMad_Modes($q_user_id, $q_cs_id, $report_for_pc);;
			}else{
				require_once "Diag_Report_ChoMad.php";
				$a=new Diag_Report_ChoMad($q_user_id, $q_cs_id, $report_for_pc);
			}
		}else{
			$a=new Print_Student_Data($q_user_id, $q_cs_id, $report_for_pc);
		}

		$prt[0].=$a->print_header($report_for_pc)."<br>";  //標頭
		$prt[1]=$a->print_student_basic_data($report_for_pc)."<br>";  //學生基本資料
		//$prt[2]=$a->print_unit_data($report_for_pc)."<br>";  //學習單元資訊
		//$prt[2]=$a->print_least_data()."<br>";   //最近一次測驗結果
		//$prt[3]=$a->print_graphic_data($q_cs_id, $report_for_pc)."<br>";   //百分等級圖
		//$prt[4]=$a->print_concept_history_data($report_for_pc);   //本單元歷來學習記錄
		if($c){
			;
		}else{
			$prt[4].="<tr><td><P STYLE='page-break-before: always;'></td></tr>";
		}
		$ep_id=$_REQUEST[q_cs_id].sprintf('%02d',$_REQUEST[q_pvol]);
		$prt[5]=$a->print_remedy_data($report_for_pc,$_REQUEST[exam_sn],$ep_id);   //概念診斷報告
		$prt[6]=$a->print_feet($report_for_pc);   //標尾

		for($i=0;$i<=sizeof($prt);$i++){
			$astr=utf8_2_big5($prt[$i]);
			fwrite($fp, $astr);
		}
		$html_feet='</body></html>';
		fwrite($fp, $html_feet);
		fclose($fp);  //關閉檔案

		$RedirectTo="Location: modules.php?op=modload&name=".$module_name."&file=download2";
		Header($RedirectTo);
	}
	// debugBAI('', 'PrintOutpersonExamResults', $prt);
}



?>
<div id="app">
		<el-pagination
		@size-change="changeSize"
		@current-change="changePage"
		:current-page.sync="currentPage"
		:page-sizes="[10, 20, 30, 40, 50]"
		:page-size.sync="pageSize"
		layout="sizes, prev, pager, next"
		:total="<?=$totalPage?:0?>"
		v-if="<?=$totalPage>0?1:0?>">
		</el-pagination>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
<script src="https://unpkg.com/element-ui/lib/index.js"></script>
<script src="//unpkg.com/element-ui/lib/umd/locale/zh-TW.js"></script>
<script>
	ELEMENT.locale(ELEMENT.lang.zhTW);//繁體語言包
	var app = new Vue({
		el: "#app",
		data: {
			currentPage: <?=$page?>,
			pageSize: <?=$size?>
		},
		methods: {
			changePage(){
				$("#CRform > input[name=page]").val(this.currentPage);
				$("#CRform").submit();
			},
			changeSize(){
				$("#CRform > input[name=size]").val(this.pageSize);
				$("#CRform > input[name=page]").val(1);
				this.currentPage = 1;
				$("#CRform").submit();
			}
		}
	});
</script>
