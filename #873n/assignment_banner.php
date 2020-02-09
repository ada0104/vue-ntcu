<?php
    $files = [
        'assignment2' => '知識結構學習',
        'assignment5' => '縱貫診斷測驗',
        'assignment4' => '單元診斷測驗',
    ];

    // #545 Edward:學力測驗全部開放，展示學校不開放除了桃園展示學校
    // 20190520 #602 學力判斷修改
    $oBascial = $dbh->prepare("SELECT organization_id FROM organization_exception WHERE basical_ability = 0");
    $oBascial->execute();
    $vBascial = $oBascial->fetchAll(\PDO::FETCH_ASSOC);
    $vData = array();
    foreach ($vBascial as $key => $value) {
      $vData[] = $value['organization_id'];
    }
    if (!in_array($_SESSION["user_data"]->organization_id, $vData)) {
        $files['assignment_bat'] = '基本學力模擬測驗';
    }
    
    $files['competencyMission'] = '核心素養評量';
    // if (in_array($_SESSION["user_data"]->organization_id,  ["196666"])) {
        
    // }
    foreach ($files as $file_name => $assignment_name) {
        if($file_name == $from){
            echo '<li><a href="modules.php?op=modload&name=assignMission&file='.$file_name.'" class="current"><i class="fa fa-caret-right"></i>'.$assignment_name.'</a></li>';
        }else{
            echo '<li><a href="modules.php?op=modload&name=assignMission&file='.$file_name.'"><i class="fa fa-caret-right"></i>'.$assignment_name.'</a></li>';
        }
    }
    echo '<br /><span style="color:red;">新課綱公告：配合108課綱教材，1年級及7年級的相關教材請選擇國語108、數學108</span>';
?>
