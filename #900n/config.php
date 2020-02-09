<?php
require_once 'config_db.php';  //不可移除

//@ini_set('allow_call_time_pass_reference','On');
date_default_timezone_set('Asia/Taipei');//將時區設為台北標準時間

//定義系統目錄
define('_ADP_PATH' , dirname($_SERVER['SCRIPT_FILENAME'])."/");
define('_ADP_URL' , 'http://'.$_SERVER["SERVER_ADDR"].'/'.$MySite.'/');
define('_WEB_TITLE' , '因材網');
define('_SPLIT_SYMBOL' , '@XX@');
define('_SPLIT_SYMBOL2' , '*%%*');
define('_TEST_ACCOUNT' , 'bnattest');
define('_FRONT_PAGE' , 'index_AIAL2.php');
define('_MASTER_SITE' , 'adaptive-learning.ntcu.edu.tw');
//認證信
define('_MAIL_ACC', 'ntcujems506');
define('_MAIL_PW', 'ntcu!@#$%kbc');
//define("P_HOME", dirname($_SERVER['SCRIPT_FILENAME']).'/topiclibrary/');
define("P_HOME", dirname($_SERVER['SCRIPT_FILENAME']).'/topiclibrary/'); //007 調整

//班級人數
// define('_MAX_CLASS_S_NUM_E', 32);  //國小每班最大學生數
// define('_MAX_CLASS_S_NUM_J', 36);  //國中每班最大學生數
// define('_MAX_CLASS_S_NUM_S', 40);  //高中每班最大學生數
// define('_MAX_CLASS_S_NUM_U', 60);  //大學每班最大學生數
// define('_MAX_CLASS_S_NUM', 36);  //每班最大學生數
define('_MAX_CLASS_T_NUM', 12);   //每班最大教師數 6->12
define('_MAX_CLASS_S_NUM', 'e'._SPLIT_SYMBOL2.'40'._SPLIT_SYMBOL.'j'._SPLIT_SYMBOL2.'50'._SPLIT_SYMBOL.'s'._SPLIT_SYMBOL2.'50'._SPLIT_SYMBOL.'u'._SPLIT_SYMBOL2.'200');//每班最大學生數(e國小,j國中,s高中,u大學)

define('SCHOOL_TYPE_E', 'e');
define('SCHOOL_TYPE_J', 'j');
define('SCHOOL_TYPE_S', 's');
define('SCHOOL_TYPE_U', 'u');
define('SCHOOL_TYPE_EJ', 'ej');
define('SCHOOL_TYPE_EJS', 'ejs');
define('SCHOOL_TYPE_JS', 'js');
define('SCHOOL_TYPE_EJSU', 'ejsu');

//定義各學校類型的在學年級
const STU_IN_SCHOOL_YEAR = array(
	SCHOOL_TYPE_E=>array(0,1,2,3,4,5,6),
	SCHOOL_TYPE_J=>array(0,7,8,9),
	SCHOOL_TYPE_S=>array(0,10,11,12),
	SCHOOL_TYPE_U=>array(0,13,14,15,16),
	SCHOOL_TYPE_EJ=>array(0,1,2,3,4,5,6,7,8,9),
	SCHOOL_TYPE_EJS=>array(0,1,2,3,4,5,6,7,8,9,10,11,12),
	SCHOOL_TYPE_JS=>array(0,7,8,9,10,11,12),
	SCHOOL_TYPE_EJSU=>array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16)
);

// 學習問卷路徑
define('HK_GOOGLE_QUESTIONNAIRE', 'https://docs.google.com/forms/d/e/1FAIpQLScF9lj8tQXw8WkIwzM0O2P5UH0D4r54D43uq75iJ_Zhg2SztQ/formResponse');
define('TW_GOOGLE_QUESTIONNAIRE', 'https://docs.google.com/forms/d/e/1FAIpQLSd4UcYiRxxfDRllZSUlMdJGPFk9VPnffNHyTdvJLpr4S53jpg/formResponse');

//密碼長度
define('_MAX_PASSWORD_LENGTH', 15);  //最長密碼字元數
define('_MIN_PASSWORD_LENGTH', 6);   //最短密碼字元數

// 每個任務中最大的節點數
define('_MAX_NODE_IN_MISSION', 10);
// 縱貫
define('_MAX_NODE_IN_INDICATE_MISSION', 3);

//系統維護時間
define('_MAINTAIN_TIME_START', '23:59');  //開始時間
define('_MAINTAIN_TIME_STOP', '02:00');  //結束時間
$SiteConfig['setMaintainTime']=1;  //啟用系統維護時間

//學生作品
define('_WORK_PATH' , _ADP_PATH.'data/work/');
define('_WORK_URL' , _ADP_URL.'data/work/');

//上傳檔案目錄
define('_UPLOAD_PATH' , "data/");
define('_ADP_UPLOAD_PATH' , _ADP_PATH."data/");

//上傳影片目錄
define('_ADP_VIDEO_PATH' , "../../data/video_data");

//上傳截圖目錄
define('_ADP_fb_PATH' , _ADP_URL."data/topUPFILE/");

//預設上傳結構概念矩陣檔及試題之目錄
define('_CS_UPLOAD_PATH' , _UPLOAD_PATH."CS_db/");
define('_ADP_CS_UPLOAD_PATH' , _ADP_UPLOAD_PATH."CS_db/");

//預設上傳檔案暫存目錄
define('_ADP_TMP_UPLOAD_PATH' , _ADP_UPLOAD_PATH."tmp/");

//預設題庫網址
//define('_ADP_EXAM_DB_PATH' , _ADP_URL."data/CS_db/");
define('_ADP_EXAM_DB_PATH' , "data/CS_db/");

//模組預設 templates_dir
define("_TEMPLATE_DIR", dirname($_SERVER['SCRIPT_FILENAME'])."/templates/");

//布景主題 theme
define("_THEME", "themes/bnat/");
define("_THEME_CSS", _ADP_URL._THEME."css/front.css");
define("_THEME_IMG", _ADP_URL._THEME."img/");

//系統版本
define("_SYS_VER", "asia");

// 檔案大小
define('FILESIZE_KB', 1024);
define('FILESIZE_MB', 1048576);
define('FILESIZE_GB', 1073741824);
define('FILESIZE_TB', 1099511627776);

// 身分代號
define('USER_STUDENT', 1);
define('USER_STUDENT_DEMO1', 4);
define('USER_STUDENT_DEMO2', 9);
define('USER_PARENTS', 11);
define('USER_PARTNER', 12);
define('USER_TEACHER', 21);
define('USER_LECTURER', 25);
define('USER_SCHOOL_DIRECTOR', 31);
define('USER_SCHOOL_PRINCIPAL', 32);
define('USER_SCHOOL_ADMIN', 33);
define('USER_CITY_ADMIN', 41);
define('USER_EDU_ADMIN', 51);
define('USER_OPERATOR', 71);
define('USER_ADMIN', 91);

const USER_STUDENT_GROUP = array(USER_STUDENT,USER_STUDENT_DEMO1,USER_STUDENT_DEMO2);
const USER_TEACHER_GROUP = array(USER_TEACHER,USER_LECTURER,USER_SCHOOL_DIRECTOR,USER_SCHOOL_PRINCIPAL);
const USER_SCHOOL_ADMIN_GROUP = array(USER_SCHOOL_DIRECTOR,USER_SCHOOL_PRINCIPAL);
const USER_PARENTS_GROUP = array(USER_PARENTS,USER_PARTNER);

// 有參加縣市基本學力檢測的縣市
const BASICAL_ABILITY_CITY = array('桃園市','嘉義市','澎湖縣','雲林縣','彰化縣');

//整個網站使用之暫存檔
define('_SITECONFIG_PATH' , _UPLOAD_PATH."SiteConfig/");
$SiteFile['SchoolHierselect']=_SITECONFIG_PATH.'SchoolHierselect.txt';  //階層式"已啟用學校列表HTML"
//$SiteFile['AAA']='AAA.txt';

//負載平衡的主機，輸入"DNS"或"IP+目錄名稱"
// 判斷是否在測試區
if (strpos($_SERVER['PHP_SELF'], 'aialtest') !== false) {
	$rand_site=array('adaptive-learning.ntcu.edu.tw/aialtest');
}
else {
      //$rand_site=$site_chc=array('203.66.45.48');	
      $rand_site=$site_chc=array('203.66.45.48','203.66.45.48','210.71.198.38','210.65.47.92');
}

// 2018-11-09 Edward: 指定連線到該伺服器的參數
$site_dep=array(
	'36' => 'adaptive-learning.ntcu.edu.tw',
	'38' => '210.71.198.38',
	'48'=> '203.66.45.48',
	'92'=> '210.65.47.92'
);

// $rand_site=array('adl.edu.tw/aialtest');
//$rand_site=array('210.65.89.151');
//彰化縣分流主機
// $site_chc=array('210.71.198.38','210.65.89.151','210.65.89.151','210.71.198.38','adaptive-learning.chc.edu.tw');
// $site_chc=array('210.71.198.38','210.65.89.151','210.65.89.151','210.71.198.38');
// $site_chc=array('adaptive-learning.moe.edu.tw');

// 分流機data/tmp檔案的前綴詞定義，會由排程統一將抓回主機
define('_FILE_BACKUP_USER_FEEDBACK', 'q_'); // 問題回報
define('_FILE_BACKUP_MESSAGE', 'm_');       // 親師互動
define('_FILE_BACKUP_ASKQUESTION', 'a_');   // 班級討論


//身份認證的資料表
$auth_table='user_info';
//消息內容資料表
//$news_table='news';

// 登入後允許的閒置時間60分鐘 3600(秒), 測式區為最大值 PHP_INT_MAX
define('_IDLETIME', 3600);
$idletime = 3600;
// 登入後cookie的存活時間 60000(秒)
$expire = 60000;


//PDO 連接方式
$config['db']['dsn']="mysql:host=".$dbhost.";dbname=".$db_dbn.";charset=utf8mb4";
$config['db']['user'] = $dbuser;
$config['db']['password'] = $dbpass;

//$dbconnect = "mysql:host=".$dbhost.";dbname=".$db_dbn;

$dbh = new PDO($config['db']['dsn'],
				$config['db']['user'],
				$config['db']['password'],
				array(
					PDO::ATTR_EMULATE_PREPARES => false,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				)
		);

if (!$dbh){
	die('config 無法連資料庫');
}

