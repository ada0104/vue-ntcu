<script>
    function download(url){
        swal({title: '請問是否下載?', text: `<a download href="${url}">點擊下載</a>`, html: true, showConfirmButton: false, showCancelButton: true,});
    }
</script>
<?php
  include_once('include/config.php');
  include_once('include/ref_cityarea.php');
  include_once('getReportUsage.php');
  ini_set('memory_limit', '4096M');
  //ini_set('display_errors','1');
  //error_reporting(E_ALL);
  //include_once('account.php');
  require 'vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Writer\Ods;

  if (!isset($_SESSION)) {
      session_start();
  }

  $vUertACL = array('31', '32' ,'33', '41', '51');

  // 取得 user 資料
  $vUserData = get_object_vars($_SESSION['user_data']);


  if (false === array_search($vUserData['access_level'], $vUertACL)) {
      echo '無權限瀏覽';
      return;
  }
  $extra = 0;
  // 統一接變數
  $vCond = array(   'search_start' => '',
                    'hiCity' => '',
                    'hiArea' => '',
                    'hiSchool' => '',
                    'hiGrade' => '',
                    'hiClass' => '',
                    'group' => '',
                    'system' => ''
                );
  if (!empty($_POST['search_start'])) {
      $vCond['search_start'] = $_POST['search_start'];
  }
  if (!empty($_POST['search_end'])) {
    $vCond['search_end'] = $_POST['search_end'];
  }
  if (!empty($_POST['hiCity'])) {
      $vCond['hiCity'] = $_POST['hiCity'];
  }
  if (!empty($_POST['hiArea'])) {
      $vCond['hiArea'] = $_POST['hiArea'];
  }
  if (!empty($_POST['hiSchool'])) {
      $vCond['hiSchool'] = $_POST['hiSchool'];
      $extra = 200;
  }
  if (!empty($_POST['hiGrade'])) {
      $vCond['hiGrade'] = $_POST['hiGrade'];
  }
  if (!empty($_POST['hiClass'])) {
      $vCond['hiClass'] = $_POST['hiClass'];
  }
  if (!empty($_POST['group'])) {
      $vCond['group'] = $_POST['group'];
  }
  if (!empty($_POST['system'])) {
      $vCond['system'] = $_POST['system'];
  }
  if (!empty($_POST['range'])) {
      $vCond['range'] = $_POST['range'];
  }
  if(!empty($_POST['search_start']) && !empty($_POST['search_end'])){
    $vMax = max($_POST['search_start'], $_POST['search_end']);
  }
  $column = array("teacher_count", "student_count", "teacher_activity", "student_activity");
  $loginlog = array("teacher_loginlog", "student_loginlog");


  // 權限控制開放搜尋內容
  getUserACL();

  // 下拉選單條件設定
  $vSelect = getSelector();

  // 時間範圍
  $sUserSearch = getCondetionRange($vCond);

  if (!empty($_POST['first']) && $_POST['first'] == 'notfirst') {
      // 取得報表資料
      $vReportData = getReprotData($vCond);
      // 整理報表資料
      $vReportData = handleData($vReportData);
      // 取得圖表資料
      $vChart = getChartData($vReportData, $vCond);
      
      getReportData2();
      
      //下載EXCEL
    switch($_POST['graph']){
        case '.bar':
            $url = make_excel();
            echo "<script>download('$url')</script>";
            break;
        case '.line':
            $url = make_excel2();
            echo "<script>download('$url')</script>";
            break;
    }
  }else{
    $_POST['search_start'] = date("Y-m-d", strtotime($lastest)-6*24*60*60);
    $_POST['search_end'] = $lastest;
    $_POST['range']=7;
    $vCond['search_start'] = date("Y-m-d", strtotime($lastest)-6*24*60*60);
    $vCond['search_end'] = $lastest;
    $vCond['range']=7;
  }

  // 整理資料, 統一變數傳至HTML
  $sJSOject = arraytoJS(array('UserCode' => $vUserData['uid'],
                              'City' => $vCityData,
                              'CityArea' => $vCiryArea,
                              'School' => $vSchool,
                              'Grade' => $vGrade,
                              'Class' => $vClass,
                              'Date' => $vDate,
                              'Max' => empty($vMax)?"":$vMax,
                              'Range' => $vCond['range'],
                              'Group' => $vCond['group'],
                              'UserCond' => $sUserSearch,
                              'CondCity' => $vCond['hiCity'],
                              'CondArea' => $vCond['hiArea'],
                              'CondSchool' => $vCond['hiSchool'],
                              'CondGrade' => $vCond['hiGrade'],
                              'Conclass' => $vCond['hiClass'],
                              'Chart' => empty($vChart)?[]:$vChart));
function getReprotData($vData)
{
    global $dbh, $vUserData, $excelSQL;
    $sUserLevel = $vUserData['access_level'];
    $sManageCity = $vUserData['city_name'];
    $sManageSchool = $vUserData['organization_name'];
    $sOrganization_id = $vUserData['organization_id'];
    $sSemeYear = $vUserData['semeYear'];
    $sCityCode = $vUserData['city_code'];

    $sReprotSQL = ", GROUP_CONCAT(teacher_active_name) teacher_active, GROUP_CONCAT(teacher_count_name) teacher_count, GROUP_CONCAT(student_active_name) student_active, GROUP_CONCAT(student_count_name) student_count FROM report_dailyusage ";
    $sGroupSQL = "";

    switch ($sUserLevel) {
        case '31'://主任
        case '32'://校長
        case '33'://校管
            if (isset($vData['search_end']) || isset($vData['hiSchool'])) {
                $vData['hiSchool'] = $sManageSchool;
                $sReprotSQL .= " WHERE grade > 0 AND class > 0 AND organization_id = '$sOrganization_id'";
            }
            break;
        case '41': // 縣市政府
            if (isset($vData['search_end']) || isset($vData['hiCity'])) {
                $vData['hiCity'] = $sManageCity;
                $sReprotSQL .= " WHERE grade > 0 AND class > 0";
            }
            break;

        case '51': // 教育部
            if (isset($vData['search_end']) || isset($vData['hiCity'])) {
                $sReprotSQL .= " WHERE  grade > 0 AND class > 0 ";
            }
            break;
    }
    $sReprotSQL .= " AND postcode NOT IN('984', '985', '986')";
    $sGroupSQL .= ' city_name ';
    //MYSQL條件
    if (isset($vData['search_end']) && !empty($vData['search_end'])) {
        $sReprotSQL .= ' AND DATEDIFF("'.$vData['search_end'].'", datetime_log)>=0 ';
    }
    if (isset($vData['hiCity']) && !empty($vData['hiCity'])) {
        $sReprotSQL .= ' AND city_name = "'.$vData['hiCity'].'" ';
        $sGroupSQL .= ' , city_area ';
    }

    if (isset($vData['hiArea']) && !empty($vData['hiArea'])) {
        $sReprotSQL .= ' AND city_area = "'.$vData['hiArea'].'" ';
        $sGroupSQL .= ' , name ';
    }

    if (isset($vData['hiSchool']) && !empty($vData['hiSchool'])) {
        $sReprotSQL .= ' AND name = "'.$vData['hiSchool'].'" ';
        if($sUserLevel == '31' || $sUserLevel == '32' || $sUserLevel == '33'){
            $sGroupSQL .= ' , grade ';
        }
    }

    if (isset($vData['system']) && !empty($vData['system'])) {
        $sReprotSQL .= ' AND type Like "%'.$vData['system'].'%" ';
    }

    if (isset($vData['hiGrade']) && !empty($vData['hiGrade'])) {
        $sReprotSQL .= ' AND grade = "'.$vData['hiGrade'].'" ';
        if($sUserLevel == '31' || $sUserLevel == '32' || $sUserLevel == '33'){
            $sGroupSQL .= ' , class ';
        }
    }

    if (isset($vData['hiClass']) && !empty($vData['hiClass'])) {
        $sReprotSQL .= ' AND class = "'.$vData['hiClass'].'" ';
    }
    $excelSQL = $sReprotSQL;
    $sReprotSQL = "SELECT ".$sGroupSQL.$sReprotSQL;
    $sReprotSQL .= ' GROUP BY '.$sGroupSQL;
    //echo $sReprotSQL;
    $dbh->query("SET group_concat_max_len = 1024000000000");
    $oReprot = $dbh->prepare($sReprotSQL);
    $oReprot->execute();
    $vReportData = $oReprot->fetchAll(\PDO::FETCH_ASSOC);
    return $vReportData;
}

function handleData($vReportData)
{
    return (empty($vReportData))?array():nameToNumber($vReportData);
}

function getChartData($vReportData, $vCond)
{
    if (empty($vReportData)) {
        return;
    }

    global $vUserData;
    //預設看縣市
    $sField = 'city_name';

    // 選擇城市要看區
    if (!empty($vCond['hiCity'])) {
        $sField = 'city_area';
    }

        // 選擇區要看學校
    if (!empty($vCond['hiArea'])) {
        $sField = 'name';
    }

    if($vUserData['access_level'] == '31' || $vUserData['access_level'] == '32' || $vUserData['access_level'] == '33'){
        if (!empty($vCond['hiSchool'])) {
            $sField = 'grade';
            $vChart['category'] = array_map(function($value){
                return $value."年級";
            }, array_column($vReportData, $sField));
        }
        if (!empty($vCond['hiGrade'])) {
            $sField = 'class';
            $vChart['category'] = array_map(function($value){
                return $value."班";
            }, array_column($vReportData, $sField));
        }

    }else{
        $vChart['category'] = array_column($vReportData, $sField);
    }
    switch ($vCond['group']) {
        case 'student':
            $vChart['count'] = array_column($vReportData, "student_count");
            $vChart['active'] = array_column($vReportData, "student_active");
            break;
        case 'teacher':
            $vChart['count'] = array_column($vReportData, "teacher_count");
            $vChart['active'] = array_column($vReportData, "teacher_active");
            break;
        default:
            $vChart['count'] = array_map(function($s,$t){
                return $s+$t;
            }, array_column($vReportData, "student_count")
            , array_column($vReportData, "teacher_count"));
            $vChart['active'] = array_map(function($s,$t){
                return $s+$t;
            }, array_column($vReportData, "student_active")
            , array_column($vReportData, "teacher_active"));
            break;
    }


    /*echo '<pre>';
    print_r($vChart);
    echo '</pre>';*/

    return $vChart;
}
  function getUserACL()
  {
      global $dbh, $vUserData, $vCityData, $vCiryArea, $vSchool, $vGrade, $vClass;

      $vSchool = array();
      $vCityData = array();
      $vCiryArea = array();
      $sUserLevel = $vUserData['access_level'];
      $sManageCity = $vUserData['city_name'];
      $sManageId = $vUserData['organization_id'];
      $sSQLCond = "SELECT organization_id, city_name, postcode, city_area, name, type, GROUP_CONCAT(DISTINCT grade ORDER BY grade) grade, GROUP_CONCAT(DISTINCT class ORDER BY class) class FROM report_dailyusage ";
      switch ($sUserLevel) {
        case '31'://主任
        case '32'://校長
        case '33'://校管
            $sSQLCond .= " WHERE organization_id IN('$sManageId') ";
            break;
        case '41'://教育局
            $_POST['hiCity'] = $sManageCity;
            $sSQLCond .= " WHERE city_name IN('$sManageCity') AND organization_id NOT IN('074799', '190039') AND postcode NOT IN('984', '985', '986')";
            break;
        case '51'://教育部
            $sSQLCond .= " WHERE organization_id NOT IN('074799', '190039') AND postcode NOT IN('984', '985', '986') AND type != 'a'";
            break;
      }
      $sSQLCond .= ' GROUP BY city_name, city_area, name ';
      $oCond = $dbh->prepare($sSQLCond);
      $oCond->execute();
      $oCond = $oCond->fetchAll(\PDO::FETCH_ASSOC);
      $vGrade = array();
      $vClass = array();
      foreach ($oCond as $tmpData) {
          if ('074799' != $tmpData['organization_id'] && '190039' != $tmpData['organization_id']) {
              $grade = explode(',',$tmpData['grade']);
              $class = explode(',',$tmpData['class']);
              $vSchool[$tmpData['city_name']][$tmpData['name']] = array($tmpData['postcode'], $tmpData['city_area'], $tmpData['name'], $tmpData['type'], array_combine($grade, $grade));
              $vCityData[$tmpData['city_name']] = $tmpData['city_name'];
              $vCiryArea[$tmpData['city_name']][$tmpData['city_area']] = array($tmpData['postcode'], $tmpData['city_area'], $tmpData['city_name']);
              $vGrade = array_merge($vGrade, $grade);
              $vGrade = array_unique($vGrade);
              $vClass = array_merge($vClass, $class);
              $vClass = array_unique($vClass);
          }
      }
      if (!empty($vClass) && !empty($vGrade)) {
          sort($vClass);
          sort($vGrade);
      }

  }

function getSelector()
{
    global $vUserData, $vCityData, $vCiryArea, $vGrade, $vClass, $vGrdCls;
    $sUserLevel = empty($vUserData['access_level'])?"":$vUserData['access_level'];
    $sManageCity = empty($vUserData['city_name'])?"":$vUserData['city_name'];
    $sManageArea = empty($vUserData['city_area'])?"":$vUserData['city_area'];
    $sManageSchool = empty($vUserData['organization_name'])?"":$vUserData['organization_name'];
    $sSystem = empty($_POST['system'])?"":$_POST['system'];
    $vSelect = array();

    switch ($sUserLevel) {
        case '31'://主任
        case '32'://校長
        case '33'://校管
            $vSelect['CitySelect'][] = '<select id="select_school" style="display:none;">';
            $vSelect['CitySelect'][] =   '<option value="'.$sManageSchool.'">'.$sManageSchool.'</option>';
            $vSelect['CitySelect'][] = '</select>';
        break;
        case '41':
            $vSelect['CitySelect'][]="依學等搜尋
            <select name='system' id='system' style='width:150px;' value='$sSystem'>
                <option value=''>全部學等</option>
                <option value='e'>國小</option>
                <option value='j'>國中</option>
                <option value='s'>高中</option>
                <option value='u'>大學</option>
            </select>";
            $vSelect['CitySelect'][] = "依地區搜尋 ";
            $vSelect['CitySelect'][] = '<select id="select_city" style="display:none;">';
            //$vSelect['CitySelect'][] =   '<option value="">全部縣市</option>';
            $vSelect['CitySelect'][] = '<option value="'.$sManageCity.'" selected>'.$sManageCity.'</option>';
            $vSelect['CitySelect'][] = '</select>';

            $vSelect['CitySelect'][] = '<select id="select_area">';
            $vSelect['CitySelect'][] =   '<option value="">全部鄉鎮市區</option>';
            if (!empty($vCiryArea)) {
                foreach ($vCiryArea[$sManageCity] as $tmpData) {
                    $vSelect['CitySelect'][] = '<option value="'.$tmpData[1].'">'.$tmpData[1].'</option>';
                }
            }
            $vSelect['CitySelect'][] = '</select>';
            $vSelect['CitySelect'][] = '<select id="select_school">';
            $vSelect['CitySelect'][] =   '<option value="全部學校">全部學校</option>';
            $vSelect['CitySelect'][] = '</select>';
            break;
        default:
            $vSelect['CitySelect'][]="依學等搜尋
            <select name='system' id='system' style='width:150px;' value='$sSystem'>
                <option value=''>全部學等</option>
                <option value='e'>國小</option>
                <option value='j'>國中</option>
                <option value='s'>高中</option>
                <option value='u'>大學</option>
            </select>";
            $vSelect['CitySelect'][] = "依地區搜尋 ";
            $vSelect['CitySelect'][] = '<select id="select_city">';
            $vSelect['CitySelect'][] =   '<option value="">全部縣市</option>';
            if (!empty($vCityData)) {
                foreach ($vCityData as $tmpData) {
                    $vSelect['CitySelect'][] = '<option value="'.$tmpData.'">'.$tmpData.'</option>';
                }
            }
            $vSelect['CitySelect'][] = '</select>';
            $vSelect['CitySelect'][] = '<select id="select_area">';
            $vSelect['CitySelect'][] =   '<option value="全部鄉鎮市區">全部鄉鎮市區</option>';
            $vSelect['CitySelect'][] = '</select>';
            $vSelect['CitySelect'][] = '<select id="select_school">';
            $vSelect['CitySelect'][] =   '<option value="全部學校">全部學校</option>';
            $vSelect['CitySelect'][] = '</select>';
            break;
      }
    $vSelect['CitySelect'][] = '<b id="option">依班級搜尋</b> <select id="select_grade">';
    $vSelect['CitySelect'][] =   '<option value="">全部年級</option>';
    if (!empty($vGrade)) {
        foreach ($vGrade as $tmpData) {
            if ($tmpData != 0) {
                $vSelect['CitySelect'][] = '<option value="'.$tmpData.'">'.$tmpData.'年級</option>';
            }
        }
    }
    $vSelect['CitySelect'][] = '</select>';
    $vSelect['CitySelect'][] = '<select id="select_class">';
    $vSelect['CitySelect'][] =   '<option value="">全部班級</option>';
    if (!empty($vClass)) {
        foreach ($vClass as $tmpData) {
            if ($tmpData != 0) {
                $vSelect['CitySelect'][] = '<option value="'.$tmpData.'">'.$tmpData.'班</option>';
            }
        }
    }
    $vSelect['CitySelect'][] = '</select>';
    return $vSelect;
}

function getCondetionRange($vData)
{
    global $dbh, $lastest, $vDate;
    $lastest = date("Y-m-d", strtotime("-1 day"));

    $sUserSearch="";
    if (!empty($vData['hiCity'])) {
        $sUserSearch = $vData['hiCity'].' / ';
    }
    if (!empty($vData['hiArea'])) {
        $sUserSearch .= $vData['hiArea'].' / ';
    }
    if (!empty($vData['system'])) {
        switch ($vData['system']) {
            case 'e':
                $sUserSearch .= '小學 / ';
                break;
            case 'j':
                $sUserSearch .= '國中 / ';
                break;
            case 's':
                $sUserSearch .= '高中 / ';
                break;
            case 'u':
                $sUserSearch .= '大學 / ';
                break;
        }
    }
    if (!empty($vData['hiSchool'])) {
        $sUserSearch .= $vData['hiSchool'].' / ';
    }
    if (!empty($vData['hiSubject'])) {
        $sUserSearch .= $vData['hiSubject'].' / ';
    }
    if (!empty($vData['hiGrade'])) {
        $sUserSearch .= $vData['hiGrade'].'年級 / ';
    }
    if (!empty($vData['hiClass'])) {
        $sUserSearch .= $vData['hiClass'].'班 / ';
    }
    if (!empty($vData['group'])) {
        switch ($vData['group']) {
            case 'teacher':
                $sUserSearch .= '老師 / ';
                break;
            case 'student':
                $sUserSearch .= '學生 / ';
                break;
        }
    }
    if (isset($vData['search_end'])) {
        $sUserSearch .= $vData['search_end'];
        $vDate = $vData['search_end'];
    }

    return $sUserSearch;
}


function make_excel()
{
    global $dbh, $vUserData, $vGrade, $vClass, $excelSQL;
    $date = date('Ymd_His');
    $excel_content[0] = array('學校代號', '縣市', '區', '學校', '年級', '班級', '教師使用人數', '教師註冊人數', '學生使用人數', '學生註冊人數', '教師使用率', '學生使用率');
    $excelSQL = "SELECT organization_id, city_name , city_area , name, grade, class ".$excelSQL." GROUP BY city_name ,city_area ,name, grade, class";
    $dbh->query("SET group_concat_max_len = 10240000000000");
    $oReprot = $dbh->prepare($excelSQL);
    $oReprot->execute();
    $oReprot = $oReprot->fetchAll(\PDO::FETCH_ASSOC);
    $sum = [0,0,0,0];
    foreach (['teacher_active', 'teacher_count', 'student_active', 'student_count'] as $key => $value) {
        $sum[$key] = array_column($oReprot, $value);
        $sum[$key] = nameToNumber([[$value=>implode(",", $sum[$key])]], 1)?:0;
        $sum[$key] = $sum[$key][0][$value];
    }

    foreach($oReprot as $key => $value){
        foreach($value as $sColumn => $sValue){
            if(preg_match("/(student_|teacher_)/", $sColumn)){
                $people = str_replace(",", "", $sValue);
                $people = explode('"', $people);
                $people = array_unique($people);
                $people = array_filter($people, function($v){return !empty($v) && !preg_match('/,/', $v);});
                $oReprot[$key][$sColumn] = count($people);
                $value[$sColumn] = count($people);
            }
        }
        array_push($oReprot[$key], ($value['teacher_count'])?($value['teacher_active']/$value['teacher_count']):0.0, ($value['student_count'])?($value['student_active']/$value['student_count']):0.0);
    }
    $excel_content = array_merge($excel_content,$oReprot);
    $excel_content[(count($excel_content)+1)] = array('','','','','','總計'
    ,$sum[0]
    ,$sum[1]
    ,$sum[2]
    ,$sum[3]
    ,($sum[1])?($sum[0]/$sum[1]):0
    ,($sum[3])?($sum[2]/$sum[3]):0
    );
    $objPHPExcel = new Spreadsheet();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle('K:L')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE);
    $objPHPExcel->getActiveSheet()->fromArray($excel_content, null, 'A1');
    switch ($_POST['download']) {
        case 'ods':
            $objWriter = new Ods($objPHPExcel);
            $objWriter->setPreCalculateFormulas(false);
            $filename= '使用狀況'.date("Ymd");
            $date=microtime(true);
            $DataAddr = "tmp/$filename.$date.ods";
            $objWriter->save($DataAddr);
            return $DataAddr;
            exit;
        case 'xlsx':
            $objWriter = new Xlsx($objPHPExcel);
            $objWriter->setPreCalculateFormulas(false);
            $filename= '使用狀況'.date("Ymd");
            $date=microtime(true);
            $DataAddr = "tmp/$filename.$date.xlsx";
            $objWriter->save($DataAddr);
            return $DataAddr;
            exit;
        default:
            # code...
            break;
    }
}
?>
    <!DOCTYPE HTML>
    <html>
    <link rel="stylesheet" href="/js/report/buttons.css">
    <script type="text/javascript" src="/js/report/echarts-all-3.js"></script>
    <script type="text/javascript" src="/js/report/ecStat.min.js"></script>
    <script type="text/javascript" src="/js/report/dataTool.min.js"></script>
    <script type="text/javascript" src="/js/report/loadingoverlay.min.js"></script>
    <script type="text/javascript" src="/js/report/loadingoverlay_progress.min.js"></script>
    <script>
        var color = "linear-gradient(to top, #cfd9df 0%, #e2ebf0 100%)";
        var oItem = $.parseJSON('<?php echo $sJSOject; ?>');
        var oUserSelect = {
            cond: oItem.UserCond,
            date: oItem.Date,
            range: oItem.Range,
            city: oItem.CondCity,
            area: oItem.CondArea,
            grade: oItem.CondGrade,
            class: oItem.Conclass,
            school: oItem.CondSchool,
            group: oItem.Group,
            max: oItem.Max
        };

        var sTitle = '全國 ';
        if ('' !== oUserSelect.city && null !== oUserSelect.city) {
            sTitle = oUserSelect.city + ' ';
        }
        if ('' !== oUserSelect.area && null !== oUserSelect.area) {
            sTitle += oUserSelect.area + ' ';
        }
        if ('' !== oUserSelect.grade && null !== oUserSelect.grade) {
            sTitle += oUserSelect.grade + '年級';
        }
        if ('' !== oUserSelect.class && null !== oUserSelect.class) {
            sTitle += oUserSelect.class + '班';
        }

        oUserSelect.title = (oItem.CondSchool) ? oItem.CondSchool : sTitle;

        $(function () {
            $.LoadingOverlay("show");

            $('#stats_period').hide();
            $("#search_start").hide();

            $(document).ready(function () {
                // $('#select_city').val(oUserSelect.city);
                // $('#select_area').val(oUserSelect.area);
                // $('#select_school').val(oUserSelect.school);
                // $('#select_subject').val(oUserSelect.subject);
                // $('#select_grade').val(oUserSelect.grade);
                // $('#hiSubject').val(oUserSelect.subject);
                // $('#hiGrade').val(oUserSelect.grade);
                $.LoadingOverlay("hide");
                $(".chart").hide();
                $("#select_class").hide();
                if (sessionStorage.getItem('clas1') && sessionStorage.getItem('clas1')) {
                    $(sessionStorage.getItem('clas1') + sessionStorage.getItem('clas2')).show();
                    $(sessionStorage.getItem('clas1').replace(".", "#radio_")).attr("checked", true);
                    $(sessionStorage.getItem('clas2').replace(".", "#radio_")).attr("checked", true);
                    $(`:radio[value='${sessionStorage.getItem('clas1')}']`).parent().css("background", color);
                    $(`:radio[value='${sessionStorage.getItem('clas2')}']`).parent().css("background", color);
                } else {
                    $(".chart.bar.avg").show();
                    $(`:radio[value='.bar']`).parent().css("background", color);
                    $(`:radio[value='.avg']`).parent().css("background", color);
                }
                if ($("#select_city").length == 0) {
                    $("#option").html(
                        "<b>依</b><select id='opt'><option value selected>全部</option><option value='grade'>年級</option><option value='class'>班級</option></select><b>搜尋</b>"
                    );
                    $("#select_grade, #select_class").hide();
                    $("#select_" + sessionStorage.getItem('grdcls')).show();
                    $("#opt").change(() => {
                        sessionStorage.setItem('grdcls', $("#opt").val());
                        $("#select_grade, #select_class").hide();
                        $("#select_" + sessionStorage.getItem('grdcls')).show();
                    }).val(sessionStorage.getItem('grdcls'));
                    $("#main_cond").css("display", "inline-block").css("justify-content", "center");
                }
                if (null !== oItem.Chart && 0 !== oItem.Chart.length) {

                    if (sessionStorage.getItem('clas1') == ".bar") {
                        $("#search_start").hide();
                        $('#stats_period').hide();
                        $('#stats_date').show();
                        $('#sort').show();
                    }
                    if (sessionStorage.getItem('clas1') == ".line") {
                        $("#search_start").show();
                        $('#stats_period').show();
                        $('#stats_date').hide();
                        $('#cross').show();
                    }
                }
            });
            if (null !== oItem.Chart && 0 !== oItem.Chart.length) {

                //當日圖
                //active chart
                var adom = document.getElementById("active_chart");
                var aChart = echarts.init(adom);
                var active = oItem.Chart.count.map((value, key) => (!value) ? 0 : Math.round(oItem.Chart.active[
                    key] / value * 100));
                //排序陣列
                var cmbn_active = combine(oItem.Chart.category, active);
                var sort_active = cmbn_active.sort((a, b) => a.value - b.value);
                var sprt_active = separate(sort_active);

                var cmbn_active_all = combine(oItem.Chart.category, oItem.Chart.count);
                var sort_active_all = cmbn_active_all.sort((a, b) => a.value - b.value);
                var sprt_active_all = separate(sort_active_all);

                var swap_active = swap(oItem.Chart.category);
                var aoption = {
                    textStyle: {
                        fontWeight: 'bold',
                        fontSize: '14'
                    },
                    title: {
                        text: oUserSelect.title,
                        subtext: oUserSelect.cond
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        }
                    },
                    legend: {
                        data: ['帳號使用率', '使用成長率']
                    },
                    toolbox: {
                        show: true,
                        feature: {
                            saveAsImage: {
                                show: true,
                                title: '圖片',
                                name: '使用狀況-長條圖'
                            }
                        }
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'value',
                        boundaryGap: [0, 0]
                    },
                    yAxis: {
                        type: 'category',
                        data: sprt_active[0],
                        axisLabel: {
                            interval: 0,
                            textStyle: {
                                fontSize: 16
                            }
                        }
                    },
                    series: [{
                        name: '帳號使用率',
                        type: 'bar',
                        data: sprt_active[1],
                        itemStyle: {
                            normal: {
                                color: "#35768c",
                                areaStyle: {
                                    type: 'default'
                                }
                            }
                        },
                        label: {
                            normal: {
                                show: true,
                                textStyle: {
                                    fontSize: 14,
                                    align: 'left'
                                },
                                formatter: (params) => {
                                    if ('' == oItem.CondSchool && params.seriesName.indexOf(
                                            '總共') == -1) {
                                        var str = "(" + oItem.Chart.active[swap_active[params.name]] +
                                            "人/" + oItem.Chart.count[swap_active[params.name]] +
                                            "人)";
                                    } else {
                                        var str = "";
                                    }
                                    if (params.value == 0) {
                                        return "";
                                    } else {
                                        return params.value + "% " + str;
                                    }
                                }
                            }
                        }
                    }]
                };
                console.log(aoption);
                aChart.setOption(aoption, true);

                var adom_all = document.getElementById("active_chart_all");
                var aChart_all = echarts.init(adom_all);
                var aoption_all = $.extend(true, {}, aoption);
                aoption_all.legend.data = ['帳號註冊人數', '註冊成長人數'];
                aoption_all.series[0].name = '帳號註冊人數';
                aoption_all.yAxis.data = sprt_active_all[0];
                aoption_all.series[0].data = sprt_active_all[1];
                aoption_all.series[0].label.normal.formatter = (params) => {
                    if (params.value == 0) {
                        return "";
                    } else {
                        return params.value + "人";
                    }
                };

                aChart_all.setOption(aoption_all, true);

                aChart.on("click", chartCLK);
                aChart_all.on("click", chartCLK);

                //走勢圖
                var adoms = document.getElementById("actives_chart");
                var aCharts = echarts.init(adoms);
                var adoms_all = document.getElementById("actives_chart_all");
                var aCharts_all = echarts.init(adoms_all);
                var aoptions = $.extend(true, {}, aoption);
                var aoptions_all = $.extend(true, {}, aoption_all);

                var category = [];
                var arate = [];
                var atrend = [];
                var counts = []
                var count = [];
                var shnk = [];
                var shvl = [];


                        response.category = response.category.map((value) => value.toChinese());
                        var rate = response.counts.map((value, key) => (value) ? Math.round(
                            response.actives[key] / value * 100) : 0);
                        var trend = rate.map((value, key, array) => (key) ? value - array[key - 1] :
                            0);

                        [aoptions.xAxis, aoptions.yAxis] = [aoptions.yAxis, aoptions.xAxis];
                        aoptions.xAxis.data = response.category;
                        aoptions.yAxis.max = 100;
                        aoptions.xAxis.axisLabel.interval = "auto";
                        aoptions.tooltip.formatter =
                            "<h1>20{b}</h1><br><h2>{a0}: {c0}%<br>{a1}: {c1}%</h2>";
                        aoptions.tooltip.axisPointer.type = "line";
                        aoptions.series[0].type = 'line';
                        aoptions.series[0].data = rate;
                        aoptions.series[0].itemStyle.normal.areaStyle = null;
                        aoptions.series[0].label.normal.show = false;
                        aoptions.series[1] = $.extend(true, {}, aoptions.series[0]);
                        aoptions.series[1].name = "使用成長率";
                        aoptions.series[1].data = trend;
                        aoptions.series[1].itemStyle.normal.color = "#ff1493";
                        aoptions.series[1].label.normal.show = false;
                        aCharts.setOption(aoptions, true);

                        [aoptions_all.xAxis, aoptions_all.yAxis] = [aoptions_all.yAxis,
                            aoptions_all.xAxis
                        ];
                        aoptions_all.xAxis.data = response.category;
                        aoptions_all.yAxis.max = 'dataMax';
                        aoptions_all.xAxis.axisLabel.interval = "auto";
                        aoptions_all.tooltip.formatter =
                            "<h1>20{b}</h1><br><h2>{a0}: {c0}人<br>{a1}: {c1}人</h2>";
                        aoptions_all.tooltip.axisPointer.type = "line";
                        aoptions_all.series[0].type = 'line';
                        aoptions_all.series[0].data = response.counts;
                        aoptions_all.series[0].itemStyle.normal.areaStyle = null;
                        aoptions_all.series[0].label.normal.show = false;
                        aoptions_all.series[1] = $.extend(true, {}, aoptions_all.series[0]);
                        aoptions_all.series[1].name = "註冊成長人數";
                        aoptions_all.series[1].data = response.count;
                        aoptions_all.series[1].itemStyle.normal.color = "#ff1493";
                        aoptions_all.series[1].label.normal.show = false;
                        aCharts_all.setOption(aoptions_all, true);

                        //變全域變數
                        category = response.category;
                        arate = rate;
                        atrend = trend;
                        counts = response.counts;
                        count = response.count;

                        shnk = shrink(category, atrend, arate, count, counts);
                        shvl = shrivel(category, atrend, arate, count, counts);


                $(".button").show();
                $("#search_text").show();
            } else {
                $(".chart").text('☝ 請設定上方篩選條件進行搜尋！').height("100%");
                $(".button").hide();
                $("#search_text").hide();
            }
            //群體預設值
            $('#group').val($('#group').attr('value'));
            $('#system').val($('#system').attr('value'));

            // 縣市預設值
            if ('' !== oUserSelect.city) {
                $("#select_city").val(oUserSelect.city);

                // 區
                $("#select_area").empty();
                $('#select_area').append($('<option>', {
                    value: ''
                }).text('全部鄉鎮市區'));
                if ($('#select_city').val()) {
                    $.each(oItem.CityArea[$('#select_city').val()], function (iInx, vData) {
                        $('#select_area').append($('<option>', {
                            value: vData[1]
                        }).text(vData[1]));
                        if (vData[1] === oUserSelect.city) {
                            $('#select_school').val(oUserSelect.school);
                        }
                    });
                }

                // 學校預設值
                $("#select_school").empty();
                $('#select_school').append($('<option>', {
                    value: ''
                }).text('全部學校'));
                if ("" != $('#select_city').val()) {
                    $.each(oItem.School[$('#select_city').val()], function (iInx, vData) {
                        if ($('#select_area').val() === vData[1] && vData[3].indexOf($('#system').val()) !=
                            -1) {
                            $('#select_school').append($('<option>', {
                                value: vData[2]
                            }).text(vData[2]));
                        }
                        if (vData[2] === oUserSelect.school) {
                            $('#select_school').val(oUserSelect.school);
                        }
                    });
                }

                // 年級
                $("#select_grade").empty();
                $("#select_grade").append($('<option>', {
                    value: ''
                }).text('全部年級'));
                if ('' !== oItem.Grade) {
                    $.each(oItem.Grade, function (iInx, vData) {
                        $('#select_grade').append($('<option>', {
                            value: vData
                        }).text(vData + '年級'));
                    });
                }
                $('#hiGrade').val($('#select_grade').val());
                $('#hiCity').val($('#select_city').val());
                $('#hiArea').val($('#select_area').val());
                $('#hiSchool').val($('#select_school').val());
            }

            // 區預設值
            if ('' !== oUserSelect.area) {
                $("#select_area").val(oUserSelect.area);

                // 學校
                $("#select_school").empty();
                $('#select_school').append($('<option>', {
                    value: ''
                }).text('全部學校'));
                if ("" != $('#select_city').val()) {
                    $.each(oItem.School[$('#select_city').val()], function (iInx, vData) {
                        if ($('#select_area').val() === vData[1] && vData[3].indexOf($('#system').val()) !=
                            -1) {
                            $('#select_school').append($('<option>', {
                                value: vData[2]
                            }).text(vData[2]));
                        }
                        if (vData[2] === oUserSelect.school) {
                            $('#select_school').val(oUserSelect.school);
                        }
                    });
                }
                // 年級
                $("#select_grade").empty();
                $("#select_grade").append($('<option>', {
                    value: ''
                }).text('全部年級'));
                if ('' !== oItem.Grade) {
                    $.each(oItem.Grade, function (iInx, vData) {
                        $('#select_grade').append($('<option>', {
                            value: vData
                        }).text(vData + '年級'));
                    });
                }
                $('#hiGrade').val($('#select_grade').val());
                $('#hiSchool').val($('#select_school').val());
                $('#hiArea').val($('#select_area').val());
            }
            //學校預設值
            if ('' !== oUserSelect.school) {
                $("#select_school").val(oUserSelect.school);
                // 年級
                $("#select_grade").empty();
                $("#select_grade").append($('<option>', {
                    value: ''
                }).text('全部年級'));
                if ("" != $('#select_school').val() && "" != $('#select_city').val() && $('#select_city').val()) {
                    $.each(oItem.School[$('#select_city').val()][$('#select_school').val()][4], function (iInx,
                        vData) {
                        $('#select_grade').append($('<option>', {
                            value: iInx
                        }).text(iInx + '年級'));
                    });
                }
                $('#hiGrade').val($('#select_grade').val());
                $('#hiSchool').val($('#select_school').val());
            }

            //學等預設值
            if ('' !== $('#system').attr('value')) {
                // 年級
                $("#select_grade").empty();
                $("#select_grade").append($('<option>', {
                    value: ''
                }).text('全部年級'));
                var min = 0,
                    max = 0;
                switch ($('#system').attr('value')) {
                    case 'e':
                        min = 1;
                        max = 6;
                        break;
                    case 'j':
                        min = 7;
                        max = 9;
                        break;
                    case 's':
                        min = 10;
                        max = 12;
                        break;
                    case 'u':
                        min = 13;
                        max = 16;
                        break;

                    default:
                        min = oItem.Grade[0];
                        max = oItem.Grade[oItem.Grade.length - 1];
                        break;
                }
                for (let i = min; i <= max; i++) {
                    $('#select_grade').append($('<option>', {
                        value: i
                    }).text(i + '年級'));
                }
                $('#hiGrade').val($('#select_grade').val());
                $('#hiSchool').val($('#select_school').val());
            }

            //年級預設值
            if ('' !== oUserSelect.grade) {
                $("#select_grade").val(oUserSelect.grade);
                $('#hiGrade').val($('#select_grade').val());
            }
            //班級預設值
            if ('' !== oUserSelect.class) {
                $("#select_class").val(oUserSelect.class);
                $('#hiClass').val($('#select_class').val());
            }
            //範圍預設值
            if ('' !== oUserSelect.range) {
                $("input[range]").val(oUserSelect.range);
            }

            // 選擇縣市, 區及學校需變動
            $('#select_city').change(function () {

                // 區
                $("#select_area").empty();
                $('#select_area').append($('<option>', {
                    value: ''
                }).text('全部鄉鎮市區'));
                if ('' !== $('#select_city').val()) {
                    $.each(oItem.CityArea[$('#select_city').val()], function (iInx, vData) {
                        $('#select_area').append($('<option>', {
                            value: vData[1]
                        }).text(vData[1]));
                    });
                }

                // 學校
                $("#select_school").empty();
                $('#select_school').append($('<option>', {
                    value: ''
                }).text('全部學校'));
                if ('' !== $('#select_city').val()) {
                    $.each(oItem.School[$('#select_city').val()], function (iInx, vData) {
                        if ($('#select_area').val() === vData[0] && vData[3].indexOf($(
                                '#system').val()) != -1) {
                            $('#select_school').append($('<option>', {
                                value: vData[1]
                            }).text(vData[1]));
                        }
                    });
                }
                // 年級
                $("#select_grade").empty();
                $("#select_grade").append($('<option>', {
                    value: ''
                }).text('全部年級'));
                if ('' !== oItem.Grade) {
                    $.each(oItem.Grade, function (iInx, vData) {
                        $('#select_grade').append($('<option>', {
                            value: vData
                        }).text(vData + '年級'));
                    });
                }

                $('#hiCity').val($('#select_city').val());
                $('#hiArea').val($('#select_area').val());
                $('#hiSchool').val($('#select_school').val());
                $('#hiGrade').val($('#select_grade').val());
            });

            // 選擇區 學校需變動
            $('#select_area').change(function () {
                // 學校
                $("#select_school").empty();
                $('#select_school').append($('<option>', {
                    value: ''
                }).text('全部學校'));
                if ("" != $('#select_city').val()) {
                    $.each(oItem.School[$('#select_city').val()], function (iInx, vData) {
                        if ($('#select_area').val() === vData[1] && vData[3].indexOf($(
                                '#system').val()) != -1) {
                            $('#select_school').append($('<option>', {
                                value: vData[2]
                            }).text(vData[2]));
                        }
                    });
                }
                // 年級
                $("#select_grade").empty();
                $("#select_grade").append($('<option>', {
                    value: ''
                }).text('全部年級'));
                if ('' !== oItem.Grade) {
                    $.each(oItem.Grade, function (iInx, vData) {
                        $('#select_grade').append($('<option>', {
                            value: vData
                        }).text(vData + '年級'));
                    });
                }

                $('#hiSchool').val($('#select_school').val());
                $('#hiArea').val($('#select_area').val());
                $('#hiGrade').val($('#select_grade').val());
            });

            // 學校
            $('#select_school').change(function () {
                // 年級
                $("#select_grade").empty();
                $("#select_grade").append($('<option>', {
                    value: ''
                }).text('全部年級'));
                if ("" != $('#select_school').val() && "" != $('#select_city').val()) {
                    $.each(oItem.School[$('#select_city').val()][$('#select_school').val()][4],
                        function (iInx, vData) {
                            $('#select_grade').append($('<option>', {
                                value: iInx
                            }).text(iInx + '年級'));
                        });
                }
                $('#hiGrade').val($('#select_grade').val());
                $('#hiSchool').val($('#select_school').val());
            });

            // 年級
            $('#select_grade').change(function () {
                $('#hiGrade').val($('#select_grade').val());
                if ($('#select_city').length == 0) {
                    $('#select_class').val("");
                    $('#hiClass').val("");
                }
            });

            // 班級
            $('#select_class').change(function () {
                $('#hiClass').val($('#select_class').val());
                if ($('#select_city').length == 0) {
                    $('#select_grade').val("");
                    $('#hiGrade').val("");
                }
            });

            // 學等
            $('#system').change(function () {
                // 學校
                $("#select_school").empty();
                $('#select_school').append($('<option>', {
                    value: ''
                }).text('全部學校'));
                if ($('#select_city').val()) {
                    $.each(oItem.School[$('#select_city').val()], function (iInx, vData) {
                        if ($('#select_area').val() === vData[1] && vData[3].indexOf($(
                                '#system').val()) != -1) {
                            $('#select_school').append($('<option>', {
                                value: vData[2]
                            }).text(vData[2]));
                        }
                    });
                }
                // 年級
                $("#select_grade").empty();
                $("#select_grade").append($('<option>', {
                    value: ''
                }).text('全部年級'));
                var min = 0,
                    max = 0;
                switch ($(this).val()) {
                    case 'e':
                        min = 1;
                        max = 6;
                        break;
                    case 'j':
                        min = 7;
                        max = 9;
                        break;
                    case 's':
                        min = 10;
                        max = 12;
                        break;
                    case 'u':
                        min = 13;
                        max = 16;
                        break;

                    default:
                        min = oItem.Grade[0];
                        max = oItem.Grade[oItem.Grade.length - 1];
                        break;
                }
                for (let i = min; i <= max; i++) {
                    $('#select_grade').append($('<option>', {
                        value: i
                    }).text(i + '年級'));
                }

                $('#hiGrade').val($('#select_grade').val());
                $('#hiSchool').val($('#select_school').val());
            });
            if ('' !== oUserSelect.grade) {
                $('#select_grade').val(oUserSelect.grade);
            }
            //時間
            $(".time").change(function (e) {
                var start = new Date($("#search_start").val());
                var end = new Date($("#search_end").val());
                var min = new Date(Math.min(start, end));
                var max = new Date(Math.max(start, end));
                min.year = min.getFullYear();
                min.month = (min.getMonth() + 1 < 10) ? "0" + (min.getMonth() + 1) : (min.getMonth() +
                    1);
                min.date = (min.getDate() < 10) ? "0" + min.getDate() : min.getDate();
                max.year = max.getFullYear();
                max.month = (max.getMonth() + 1 < 10) ? "0" + (max.getMonth() + 1) : (max.getMonth() +
                    1);
                max.date = (max.getDate() < 10) ? "0" + max.getDate() : max.getDate();

                $("#search_start").val(min.year + "-" + min.month + "-" + min.date);
                $("#search_end").val(max.year + "-" + max.month + "-" + max.date);
                $("#range").val(Math.abs((start - end) / 86400000) + 1);
            });
            //切換圖表
            $("#sort").hide();
            $("#cross").hide();
            $('input[name=group1]').change(() => {
                $('.chart').hide();
                $(".due").toggle();
                $("#sort").toggle();
                $("#search_start").toggle();
                $("#cross").toggle();
                var class1 = $('input[name="group1"]:checked').val();
                var class2 = $('input[name="group2"]:checked').val();
                console.log(class1, class2);
                $(".chart" + class1 + class2).show();
                sessionStorage.setItem('clas1', class1);
                sessionStorage.setItem('clas2', class2);
                $(`:radio`).parent().css("background", "white");
                $(`:radio[value='${sessionStorage.getItem('clas1')}']`).parent().css("background", color);
                $(`:radio[value='${sessionStorage.getItem('clas2')}']`).parent().css("background", color);
            });
            $('input[name=group2]').change(() => {
                $('.chart').hide();
                var class1 = $('input[name="group1"]:checked').val();
                var class2 = $('input[name="group2"]:checked').val();
                console.log(class1, class2);
                $(".chart" + class1 + class2).show();
                sessionStorage.setItem('clas1', class1);
                sessionStorage.setItem('clas2', class2);
                $(`:radio`).parent().css("background", "white");
                $(`:radio[value='${sessionStorage.getItem('clas1')}']`).parent().css("background", color);
                $(`:radio[value='${sessionStorage.getItem('clas2')}']`).parent().css("background", color);
            });
            //sort
            var symbol = [{
                sign: "◈",
                text: "原始排序"
            }, {
                sign: "▲",
                text: "升冪排序"
            }, {
                sign: "▼",
                text: "降冪排序"
            }];
            $("#sort").click(function (e) {
                $("#sort_sign").text(symbol[0].sign);
                $("#sort_text").text(symbol[0].text);
                symbol.push(symbol.shift());
                if ($("#sort_sign").text() == "◈") {
                    aoption.yAxis.data = oItem.Chart.category;
                    aoption.series[0].data = active;
                    aoption_all.yAxis.data = oItem.Chart.category;
                    aoption_all.series[0].data = oItem.Chart.count;
                } else {
                    aoption.yAxis.data = sprt_active[0].reverse();
                    aoption.series[0].data = sprt_active[1].reverse();
                    aoption_all.yAxis.data = sprt_active_all[0].reverse();
                    aoption_all.series[0].data = sprt_active_all[1].reverse();
                }
                aChart.clear();
                aChart.setOption(aoption);
                aChart_all.clear();
                aChart_all.setOption(aoption_all);
            });
            //
            var cymbol = [{
                sign: "週",
            }, {
                sign: "月",
            }, {
                sign: "日",
            }];
            $("#cross").click(function (e) {
                $("#cross_sign").text(cymbol[0].sign);
                cymbol.push(cymbol.shift());
                if ($("#cross_sign").text() == "日") {
                    aoptions.xAxis.data = category;
                    aoptions.series[0].data = arate;
                    aoptions.series[1].data = atrend;
                    aoptions_all.xAxis.data = category;
                    aoptions_all.series[0].data = counts;
                    aoptions_all.series[1].data = count;
                } else if ($("#cross_sign").text() == "周") {
                    aoptions.xAxis.data = shnk[0];
                    aoptions.series[0].data = shnk[2];
                    aoptions.series[1].data = shnk[1];
                    aoptions_all.xAxis.data = shnk[0];
                    aoptions_all.series[0].data = shnk[4];
                    aoptions_all.series[1].data = shnk[3];
                } else if ($("#cross_sign").text() == "月") {
                    aoptions.xAxis.data = shvl[0];
                    aoptions.series[0].data = shvl[2];
                    aoptions.series[1].data = shvl[1];
                    aoptions_all.xAxis.data = shvl[0];
                    aoptions_all.series[0].data = shvl[4];
                    aoptions_all.series[1].data = shvl[3];
                }
                aCharts.clear();
                aCharts.setOption(aoptions);
                aCharts_all.clear();
                aCharts_all.setOption(aoptions_all);
            });
            $("#day" + oUserSelect.range).prop("checked", true);
            //下載EXCEL
            $('.download').click(function () {
                var type = $(this).val();
                $("#download").val(type);
                var graph = $('input[name=group1]:checked').val();
                $("#graph").val(graph);
                $("#main_cond").submit();
            });

            $("#main_cond").submit(function (e) {
                $.LoadingOverlay("show");
                setTimeout(() => {
                    $.LoadingOverlay("hide");
                }, 3000);
            });

        });

        function combine(keys, values) {
            if (keys.length != values.length) {
                return [];
            } else {
                cmbn = [];
                for (let key in keys) {
                    cmbn.push({
                        key: keys[key],
                        value: values[key]
                    });
                }
                return cmbn;
            }
        }

        function separate(array) {
            sprt = [
                [],
                []
            ];
            for (let obj of array) {
                sprt[0].push(obj.key);
                sprt[1].push(obj.value);
            }
            return sprt;
        }
        //周線資料
        function shrink(category, trend, rate, variety, accum) {
            var shrink = [
                [],
                [],
                [],
                [],
                []
            ];
            var ctgy = Object.values(category);
            var trnd = Object.values(trend);
            var rt = Object.values(rate);
            var vrty = Object.values(variety);
            var accm = Object.values(accum);

            shrink[0].push(ctgy.splice(0, (ctgy.length - 1) % 7 + 1).pop());
            shrink[1].push(trnd.splice(0, (trnd.length - 1) % 7 + 1).reduce((a, b) => a + b, 0));
            shrink[2].push(rt.splice(0, (rt.length - 1) % 7 + 1).pop());
            shrink[3].push(vrty.splice(0, (vrty.length - 1) % 7 + 1).reduce((a, b) => a + b, 0));
            shrink[4].push(accm.splice(0, (accm.length - 1) % 7 + 1).pop());
            while (ctgy.length > 0) {
                shrink[0].push(ctgy.splice(0, 7).pop());
                shrink[1].push(trnd.splice(0, 7).reduce((a, b) => a + b, 0));
                shrink[2].push(rt.splice(0, 7).pop());
                shrink[3].push(vrty.splice(0, 7).reduce((a, b) => a + b, 0));
                shrink[4].push(accm.splice(0, 7).pop());
            }
            return shrink;
        }
        //月線資料
        function shrivel(category, trend, rate, variety, accum) {
            var shrivel = [
                [],
                [],
                [],
                [],
                []
            ];
            var ctgy = Object.values(category);
            var trnd = Object.values(trend);
            var rt = Object.values(rate);
            var vrty = Object.values(variety);
            var accm = Object.values(accum);

            ctgy.push("");
            ctgy.forEach((e, k, a) => {
                if (k == a.length - 1) {
                    return;
                }
                if (e.substr(0, 10) != a[k + 1].substr(0, 10)) {
                    shrivel[0].push(e);
                    shrivel[1].push(trnd[k]);
                    shrivel[2].push(rt[k]);
                    shrivel[3].push(vrty[k]);
                    shrivel[4].push(accum[k]);
                } else {
                    trnd[k + 1] = trnd[k + 1] + trnd[k];
                    vrty[k + 1] = vrty[k + 1] + vrty[k];
                }
            });

            return shrivel;
        }

        function chartCLK(params) {
            if ($("#select_city").length > 0) {
                if (!oItem.CondCity) {
                    [oItem.CondCity, params.name] = [params.name, oItem.CondCity];
                } else if (!oItem.CondArea && oItem.CondCity != params.name) {
                    [oItem.CondArea, params.name] = [params.name, oItem.CondArea];
                } else if (!oItem.CondSchool && oItem.CondArea != params.name) {
                    [oItem.CondSchool, params.name] = [params.name, oItem.CondSchool];
                }
                $("#hiCity").val(oItem.CondCity);
                $("#hiArea").val(oItem.CondArea);
                $("#hiSchool").val(oItem.CondSchool);

                $("#main_cond").submit();
            }

        }

        function swap(json) {
            var ret = {};
            for (var key in json) {
                ret[json[key]] = key;
            }
            return ret;
        }

        String.prototype.toChinese = function (){
            var array = ["零", "一", "二", "三", "四", "五", "六", "七", "八", "九"];
            return this.substring(2).replace(/\W/g, "$&\n").replace("-", "\n.").replace("-", "\n.");
        }
    </script>

    <style>
        @import url(https://fonts.googleapis.com/earlyaccess/cwtexfangsong.css);
        #main_cond label {
            cursor: pointer;
        }

        .button {
            font-family: 'cwTeXFangSong', serif;
            font-size: 18px;
        }

        .row1 {
            display: flex;
        }

        #select_city,
        #select_area,
        #select_school {
            flex: 5;
        }

        #system,
        #select_grade,
        #select_class {
            width:200px;
        }

        #option {
            display: inline-flex;
            white-space: nowrap;
        }

        .ui-datepicker-calendar {
            display: none;
        }
        .button:focus{
            outline: 1px solid red;
        }
        
        @media screen and (max-width: 798px) {
            #stats_date,
            #stats_period{
                clear: left;              
            }
        }
        @media screen and (max-width: 565px) {
            #search_start{
                clear: left;   
            }
        }
    </style>
    <div class="content2-Box">

        <div class="choice-box">
            <div class="choice-title">報表</div>
            <ul class="choice work-cholic">
                <li>
                    <a href="modules.php?op=modload&name=schoolReport&file=report_dailyusage" class="current">
                        <i class="fa fa-caret-right"></i>使用狀況</a>
                </li>
                <li>
                    <a href="modules.php?op=modload&name=schoolReport&file=report_learningeffect">
                        <i class="fa fa-caret-right"></i>學習狀況</a>
                </li>
                <li <?=($vUserData['access_level']==31 || $vUserData['access_level']==32 || $vUserData['access_level']==33)?"":"style='display:none'"?>><a href="modules.php?op=modload&name=schoolReport&file=report_stulearn">
                        <i class="fa fa-caret-right"></i>各班使用狀況</a>
                </li>
                <li <?=($vUserData['access_level']==41)?"":"style='display:none'"?>><a href="modules.php?op=modload&name=schoolReport&file=report_school_chc">
                        <i class="fa fa-caret-right"></i>學校報表</a>
                </li>
            </ul>

        </div>
        <div class="left-box" style="width:100%;">
            <form id="main_cond" method="post" action="modules.php?op=modload&name=schoolReport&file=report_dailyusage"style="width:100%;">
                <h3 class='row1'>
                    <?php echo implode('', $vSelect['CitySelect']); ?>
                </h3>
                <h3 style="float:left;">統計群體 </h3>
                <select name="group" id="group" style="width:200px; float:left;" value="<?=empty($_POST['group'])?"":$_POST['group']?>">
                    <option value="">全部使用者</option>
                    <option value="teacher">老師</option>
                    <option value="student">學生</option>
                </select>

                <h3 class="due" id="stats_date" style="float:left;">統計日期 </h3>
                <h3 class="due" id="stats_period" style="float:left;">統計期間 </h3>
                <input type="date" class="time" placeholder="yyyy/mm/dd" id="search_start" name="search_start" min="2016-05-01" max="<?=date('Y-m-d',strtotime('-1 day'))?>"
                    value="<?=$vCond['search_start']?>" style="float:left;width:200px;">
                <input type="date" class="time" placeholder="yyyy/mm/dd" id="search_end" name="search_end" min="2016-05-01" max="<?=date('Y-m-d',strtotime('-1 day'))?>"
                    value="<?=$vCond['search_end']?>" style="float:left;width:200px;">

                <input type="hidden" id="hiCity" name="hiCity" value="<?=empty($_POST['hiCity'])?"":$_POST['hiCity']?>">
                <input type="hidden" id="hiArea" name="hiArea" value="<?=empty($_POST['hiArea'])?"":$_POST['hiArea']?>">
                <input type="hidden" id="hiSchool" name="hiSchool" value="<?=empty($_POST['hiSchool'])?"":$_POST['hiSchool']?>">
                <input type="hidden" id="hiGrade" name="hiGrade" value="<?=empty($_POST['hiGrade'])?"":$_POST['hiGrade']?>">
                <input type="hidden" id="hiClass" name="hiClass" value="<?=empty($_POST['hiClass'])?"":$_POST['hiClass']?>">
                <input type="hidden" id="range" name="range" value="<?=empty($_POST['range'])?"":$_POST['range']?>">
                <input type="hidden" id="first" name="first" value="notfirst">
                <input type="hidden" id="download" name="download">
                <input type="hidden" id="graph" name="graph">
                <input type="submit" name="sreach" class="btn02" style="width:150px;margin: 0 0 1% 1%;float:left" value="查詢">
            </form>
        </div>
        <div class="right-box" style="width:100%; padding:0px;">
            <div class="button-group" style="float:left;margin:10px 10px;">
                <label for="radio_bar" class="button button-glow button-border button-rounded button-primary" style="padding:0 20px;">
                    <input type="radio" id="radio_bar" name="group1" value=".bar" hidden checked/>當日
                </label>
                <label for="radio_line" class="button button-glow button-border button-rounded button-primary" style="padding:0 20px;">
                    <input type="radio" id="radio_line" name="group1" value=".line" hidden />走勢
                </label>
            </div>
            <div class="button-group" style="float:left;margin:10px 10px;">
                <label for='radio_avg' class="button button-glow button-border button-rounded  button-royal" style="padding:0 20px;">
                    <input type="radio" id="radio_avg" name="group2" value=".avg" hidden checked />平均
                </label>
                <label for='radio_sum' class="button button-glow button-border button-rounded button-royal" style="padding:0 20px;">
                    <input type="radio" id="radio_sum" name="group2" value=".sum" hidden />總數
                </label>
            </div>           
            <button class="download button button-glow button-border button-rounded button-action" style="float:left;padding:0 20px;margin:10px; 10px;" value="xlsx">
                XLSX檔案下載
            </button>
            <button class="download button button-glow button-border button-rounded button-action" style="float:left;padding:0 20px;margin:10px; 10px;"  value="ods">
                ODS檔案下載
            </button>
            <label id="sort" class="button button-glow button-border button-rounded button-highlight" style="float:left;padding:0 20px;margin:10px 10px;">
                <span id="sort_sign">▼</span>
                <span id="sort_text">降冪排序</span>
            </label>
            <label id="cross" class="button button-glow button-border button-rounded button-highlight" style="float:left;padding:0 20px;margin:10px 10px;">
                <span id="cross_sign">日</span>
                <span id="cross_text">線</span>
            </label>
            <div style="width:100%;height:2%;"></div>           
            <font id="search_text" class="color-blue" style="display:inline-block;font-size:1rem;">搜尋範圍：
                <?php echo $sUserSearch; ?>
            </font>
            <div id="active_chart" class="chart bar avg" name="當日活躍" style="height:<?=count($vChart['category'])*64+90+$extra?>px;"></div>
            <div id="active_chart_all" class="chart bar sum" name="當日活躍全" style="height:<?=count($vChart['category'])*64+90+$extra?>px;"></div>
            <div id="actives_chart" class="chart line avg" name="走勢活躍" style="height:700px;"></div>
            <div id="actives_chart_all" class="chart line sum" name="走勢活躍全" style="height:700px;"></div>

        </div>
    </div>


    </html>