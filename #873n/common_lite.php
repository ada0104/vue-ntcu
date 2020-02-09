<?php
/*------------------------------------------------
 Object Name: run_odbc
    Function: 
          query() -->    執行 SQL 並傳回成功或失敗 
          fetch() -->    擷取紀錄成為Object 並傳回參數：$rows = 紀錄行號從 0 開始 
          num_rows() --> 傳回結果的紀錄數 
          error()  -->
------------------------------------------------*/
class run_db
{
  var $conn = "";
  var $dbkind = "mysql";
  var $dbip = "localhost";
  var $dbport = 3306;
  var $dbname = "gcas";
  var $dbuser = "cwc";
  var $dbpassword = "cwc";
  var $result;    
  
  /* 起始 run_db 物件 */
  /*function run_db()
  {
    $s_conn = sprintf("host=%s port=%s dbname=%s user=%s password=%s",$this->dbip, $this->dbport, $this->dbname, $this->dbuser, $this->dbpassword);
    $this->conn = @pg_connect ($s_conn);
    if ($this->conn) {
      @pg_exec($this->conn,"SET DATESTYLE TO 'ISO'");
      return true;
    }else
      return false;
  }*/
  
  function run_db($kind,$port="")
  {    
    $this->dbkind = $kind;
    if($port!="")
      $this->dbport = $port;
    switch($this->dbkind){
      case "mysql":
        if($port!=""){
          $this->dbip .= ":".$port;
        } else {
          $this->dbport = 3306;
          $this->dbip.=":3306";
        }
        $this->conn = mysql_connect($this->dbip, $this->dbuser, $this->dbpassword,true);
        mysql_select_db($this->dbname, $this->conn);
        if ($this->conn) {
          mysql_query("SET NAMES 'utf8'");
          mysql_query("SET CHARACTER_SET_CLIENT=utf8");
          mysql_query("SET CHARACTER_SET_RESULTS=utf8");
          putenv("TZ=Asia/Taipei"); 
          return true;
        }else
          return false;
      break;
      case "postgresql":
        $s_conn = sprintf("host=%s port=%s dbname=%s user=%s password=%s",$this->dbip, $this->dbport, $this->dbname, $this->dbuser, $this->dbpassword);
        $this->conn = @pg_connect ($s_conn);
        if ($this->conn) {
          pg_exec($this->conn,"SET DATESTYLE TO 'ISO'");
          return true;
        }else
          return false;
      break;
    }
  }  

  /* 執行 SQL 並傳回成功或失敗 */
  function query($sql)
  {
    switch($this->dbkind){
      case "mysql":
       // $this->result = mysql_real_escape_string($sql);
        $this->result = mysql_query($sql);
        
      break;
      case "postgresql":
        $this->result = pg_query($this->conn,$sql);
      break;
    }
    if (!$this->result) 
      echo $this->error ($sql);
    return $this->result;
  }
  

  
  /* 擷取紀錄成為Object 並傳回
     參數：$rows = 紀錄行號從 0 開始 */
  function fetch($rows,$result="")
  {
    switch($this->dbkind){
      case "mysql":       
        if ( $result == "" )
          return mysql_fetch_object($this->result);
        else
          return mysql_fetch_object($result);
      break;
      case "postgresql":        
        if ( $result == "" )
          return pg_fetch_object($this->result,$rows);
        else
          return pg_fetch_object($result,$rows);
      break;
    }
  }
  
  /* 傳回結果的紀錄數 */
  function num_rows($result="")
  {
    switch($this->dbkind){
      case "mysql": 
        if ($result == "")
          return mysql_num_rows($this->result);
        else
          return mysql_num_rows($result);
      break;
      case "postgresql": 
        if ($result == "")
          return pg_num_rows($this->result);
        else
          return pg_num_rows($result);
      break;
    }
  }  
  
  /* 傳回受影響的紀錄數 */
  function affected_rows($result="")
  {
    switch($this->dbkind){
      case "mysql": 
        if (trim($result) == "")
          return @mysql_affected_rows($this->result);
        else
          return @mysql_affected_rows($result);
      break;
      case "postgresql":
        if (trim($result) == "")
          return @pg_affected_rows($this->result);
        else
          return @pg_affected_rows($result);
      break;
    }
  }  
  
  function error($sql)
  {
    switch($this->dbkind){
      case "mysql": 
        return "["._("資料庫錯誤")."]-->".mysql_error()."<br>["._("&nbsp;\t&nbsp;")."]-->".$sql;
      break;
      case "postgresql":
        return "["._("資料庫錯誤")."]-->".pg_last_error()."<br>["._("&nbsp;\t&nbsp;")."]-->".$sql;
      break;
    }
  }
  
  function close(){
    switch($this->dbkind){
      case "mysql":        
        mysql_close($this->conn);
      break;
      case "postgresql":
        mysql_close($this->conn);
      break;
    }
  }
  
  function free($result=""){
    switch($this->dbkind){
      case "mysql": 
        if ($result == "")
          return @mysql_free_result($this->result);
        else
          return @mysql_free_result($result);
      break;
      case "postgresql":
        if ($result == "")
          return @pg_free_result($this->result);
        else
          return @pg_free_result($result);
      break;
    }    
  }
}

/*------------------------------------------------
 Session Object
 Object Name: run_session
    Function: run_session () --> 檢查session 是否逾期？程式是否有使用權限？
    
------------------------------------------------*/          
class run_session 
{
  function run_session ()
  {
    // 除錯用
    error_reporting(E_ALL ^ E_NOTICE);
    //error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
    //error_reporting(E_ALL);
    ini_set ("display_errors",1);
    
    session_name("zeroteamzero");
    session_start();
  }
} 
  


/*------------------------------------------------
Alert Funciton
 Object Name: ri_alert
    Parament: $string --> 欲顯示之訊息
------------------------------------------------*/          
function ri_alert($string)
{
  $output  = "<script>";
  $output .= "  alert('".$string."');";
  $output .= "</script>";
  return $output;
}

/*------------------------------------------------
Alert Funciton
 Object Name: ri_alert
    Parament: $type: 1.OK 2.Error 
              $string --> 欲顯示之訊息
------------------------------------------------*/          
function ri_msg($string)
{
  if (trim ($string) <> "") {
    $output  = "<div class='msg_ok'>";
    $output .= "  <font class='msg_ok_text'>";
    $output .= trim($string);
    $output .= "  </font>";
    $output .= "</div>";
    $string = "";
    return $output;
  }else
    return "";
}


/*------------------------------------------------
 Jump to Html Page Funciton
 Object Name: ri_jump
    Parament: $url --> 跳至網頁的網址，可含GET 的變數
              $second -->延遲秒數，default = 0
------------------------------------------------*/          
function ri_jump ($url, $second = 0)
{
  echo sprintf ("<meta http-equiv='Refresh' content='%d; url=%s'>", $second, $url);
  exit;
}

/*------------------------------------------------
Del Enter Funciton
 Object Name: ri_change_enter
    Parament: $string --> 轉換字串中的ENTER
------------------------------------------------*/          
function ri_change_enter($string)
{
  $string=str_replace(chr(13),"",nl2br($string));
  $string=str_replace("\n","",$string);
  return $string;
}

/*------------------------------------------------
Show file in open window Funciton
 Object Name: ri_getsysvar
    Parament: $sFile --> 欲開啟之檔案名稱
        $sKey  --> 欲顯示資料之主鍵值
------------------------------------------------*/          
function ri_wo_show_file($sFile,$sKey,$iWidth=600,$iHeight=440,$sMod="")
{
  if (trim($sMod) == "")
    $sMod = "toolbar=no,location=no,directories=no,resizable=yes,scrollbars=yes,width=".$iWidth.",height=".$iHeight;
  $data  ="\"";
  $data .= "win=window.open('".$sFile."?sKey=".trim($sKey)."','','".$sMod."');";
  $data .= "win.moveTo(screen.width/2-".floor($iWidth/2).",screen.availHeight/2-".floor($iHeight/2).");";
  //$data .= "return false;";
  $data .="\"";
  return $data;
}

/*-----------------------------------------------------------------------------------------------*/ 
/*日期函式*/
/* 
  分離年、月、日   日期格式: 2000-05-07
                               年-月-日
  註:月、日、時、分 必須補足兩位數
*/
function get_year($date)
{
  $ds=split("-",$date);
  return $ds[0];
}
  
function get_month($date)
{
  $ds=split("-",$date);
  return $ds[1];
}
  
function get_day($date)
{
  $ds=split("-",$date);
  return $ds[2];
}
  
/*時間轉時間戳記*/
function change_time($time)
{
  return mktime(substr($time,11,2),substr($time,14,2),substr($time,17,2),get_month($time),get_day($time),get_year($time));
} 
  
/*檢查日期*/
function check_date($date)
{
  if (trim ($date) == ""){
    return false;
  } else {  
    $c_year=strpos($date,"-",0); 
    $year=substr($date,0, $c_year);
    $c_month=strpos($date,"-",$c_year+1); 
    $month=substr($date,$c_year+1, $c_month-($c_year+1));
    $day=substr($date,$c_month+1,strlen($date)-($c_month+1));
    if ($c_year == "" or $c_month == "")
      return false;
    return checkdate($month,$day,$year);
  }  
}
     
function delay($n)
{   
  if ($n <= 0)   
  return 0;   
  else {   
  for ($i=0;$i<100;$i++);   
  delay(--$n);   
  }
}   
/*-------------------------------------------------------------------*/
  function operate_str($post,$str)
  {
    $str.= "Array (";
    while (list($key,$val)=each($post)) {
    $str.= " [".$key."] => ";
    if (is_array($val))
      $str=operate_str($val,$str);
    else
      $str.= str_replace("'","''",$val);
    } 
    $str.= " )";
    return $str;
  }
  
  
  //-------------- 查詢真實IP --------------
  function gethideIP(){
    if(isset($_SERVER[HTTP_X_FORWARDED_FOR]) && isset($_SERVER[HTTP_VIA])){
      $myip=$_SERVER[HTTP_X_FORWARDED_FOR];
    }else{
      $yourip=$_SERVER[REMOTE_ADDR];
    }
    return $yourip;
  }
  
  //-------------- 防SQL injection && XSS --------------
  if( !get_magic_quotes_gpc() ){
    if( is_array($_GET) ){
      while( list($k, $v) = each($_GET) ){
        if( is_array($_GET[$k]) ){
          while( list($k2, $v2) = each($_GET[$k]) ){
            $_GET[$k][$k2] = addslashes($v2);
            //$_GET[$k][$k2] = mysql_real_escape_string($v2);
          }
          @reset($_GET[$k]);
        } else {
          $_GET[$k] = addslashes($v);
          //$_GET[$k] = mysql_real_escape_string($v);
        }
      }
      @reset($_GET);
    }  
    if( is_array($_POST) ){
      while( list($k, $v) = each($_POST) ){
        if( is_array($_POST[$k]) ){
          while( list($k2, $v2) = each($_POST[$k]) ){
            $_POST[$k][$k2] = addslashes($v2);
            //$_POST[$k][$k2] = mysql_real_escape_string($v2);
          }
          @reset($_POST[$k]);
        }
        else
        {
          $_POST[$k] = addslashes($v);
          //$_POST[$k] = mysql_real_escape_string($v);
        }
      }
      @reset($_POST);
    }
  
    if( is_array($_COOKIE) ){
      while( list($k, $v) = each($_COOKIE) ){
        if( is_array($_COOKIE[$k]) ){
          while( list($k2, $v2) = each($_COOKIE[$k]) ){
            $_COOKIE[$k][$k2] = addslashes($v2);
            //$_COOKIE[$k][$k2] = mysql_real_escape_string($v2);
          }
          @reset($_COOKIE[$k]);
        } else {
          $_COOKIE[$k] = addslashes($v);
          //$_COOKIE[$k] = mysql_real_escape_string($v);
        }
      }
      @reset($_COOKIE);
    }
  }
  
  //--------------------- 調整時差設定成 系統預設值 ---------------------       
  /*$ODb = new run_db("mysql",3306);
  $sql = "select content from system_var where var='gmt_zone' ";
  $ODb->query($sql);
  if ($ODb->result) {
    $field=$ODb->fetch(0);
    $gmt_zone=trim($field->content);
    date_default_timezone_set($gmt_zone);
  } else {
    date_default_timezone_set("Asia/Taipei");
  }*/
  date_default_timezone_set("Asia/Taipei");
  
  //--------------------- 數字轉換成中文大寫 ---------------------
  function NumToBigStr($num){
    $numc ="零,壹,貳,參,肆,伍,陸,柒,捌,玖";
    $unic =",拾,佰,仟";
    $unic1  ="元整,萬,億,兆,京";
    
    $numc_arr =explode("," , $numc);
    $unic_arr =explode("," , $unic);
    $unic1_arr =explode("," , $unic1);
    
    $i = str_replace(',','',$num);#取代逗號
    $c0 = 0;
    $str=array();
    do{
      $aa = 0;
      $c1 = 0;
      $s = "";
      #取最右邊四位數跑迴圈,不足四位就全取
      $lan=(strlen($i)>=4)?4:strlen($i);
      $j = substr($i, -$lan);
      while($j>0){
        $k = $j % 10;#取餘數
        if($k > 0){
          $aa = 1;
          $s = $numc_arr[$k] . $unic_arr[$c1] . $s ;
        } else if ($k == 0){
          if($aa == 1)  $s = "0" . $s;
        }
        $j = intval($j / 10);#只取整數(商)
        $c1 += 1;
      }
      #轉成中文後丟入陣列,全部為零不加單位
      $str[$c0]=($s=='')?'':$s.$unic1_arr[$c0];
      #計算剩餘字串長度
      $count_len=strlen($i) - 4;
      $i=($count_len > 0 )?substr($i, 0, $count_len):'';
  
      $c0 += 1;
    }while($i!='');
    
    #組合陣列
    foreach($str as $v) $string .= array_pop($str);
  
    #取代重複0->零
    $string=preg_replace('/0+/','零',$string);
  
    return $string;
  }
  
  /**
   The MIT License
  
   Copyright (c) 2007 <Tsung-Hao>
  
   Permission is hereby granted, free of charge, to any person obtaining a copy
   of this software and associated documentation files (the "Software"), to deal
   in the Software without restriction, including without limitation the rights
   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   copies of the Software, and to permit persons to whom the Software is
   furnished to do so, subject to the following conditions:
  
   The above copyright notice and this permission notice shall be included in
   all copies or substantial portions of the Software.
  
   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   THE SOFTWARE.
   *
   * 抓取要縮圖的比例, 下述只處理 jpeg
   * $from_filename : 來源路徑, 檔名, ex: /tmp/xxx.jpg
   * $save_filename : 縮圖完要存的路徑, 檔名, ex: /tmp/ooo.jpg
   * $in_width : 縮圖預定寬度
   * $in_height: 縮圖預定高度
   * $quality  : 縮圖品質(1~100)
   *
   * Usage:
   *   ImageResize('ram/xxx.jpg', 'ram/ooo.jpg');
   */
  function ImageResize($from_filename, $save_filename, $in_width=400, $in_height=300, $quality=100)
  {
      $allow_format = array('jpeg','jpg', 'png', 'gif');
      $sub_name = $t = '';
  
      // Get new dimensions
      $img_info = getimagesize($from_filename);
      $width    = $img_info['0'];
      $height   = $img_info['1'];
      $imgtype  = $img_info['2'];
      $imgtag   = $img_info['3'];
      $bits     = $img_info['bits'];
      $channels = $img_info['channels'];
      $mime     = $img_info['mime'];
  
      list($t, $sub_name) = split('/', $mime);
      if ($sub_name == 'jpg') {
          $sub_name = 'jpeg';
      }
  
      if (!in_array($sub_name, $allow_format)) {
          return false;
      }
  
      // 取得縮在此範圍內的比例
      $percent = getResizePercent($width, $height, $in_width, $in_height);
      $new_width  = $width * $percent;
      $new_height = $height * $percent;
  
      // Resample
      $image_new = imagecreatetruecolor($new_width, $new_height);
  
      // $function_name: set function name
      //   => imagecreatefromjpeg, imagecreatefrompng, imagecreatefromgif
      /*
      // $sub_name = jpeg, png, gif
      $function_name = 'imagecreatefrom' . $sub_name;
  
      if ($sub_name=='png')
          return $function_name($image_new, $save_filename, intval($quality / 10 - 1));
  
      $image = $function_name($from_filename); //$image = imagecreatefromjpeg($from_filename);
      */
      $image = imagecreatefromjpeg($from_filename);
  
      imagecopyresampled($image_new, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
  
      return imagejpeg($image_new, $save_filename, $quality);
  }
  
  /**
   * 抓取要縮圖的比例
   * $source_w : 來源圖片寬度
   * $source_h : 來源圖片高度
   * $inside_w : 縮圖預定寬度
   * $inside_h : 縮圖預定高度
   *
   * Test:
   *   $v = (getResizePercent(1024, 768, 400, 300));
   *   echo 1024 * $v . "\n";
   *   echo  768 * $v . "\n";
   */
  function getResizePercent($source_w, $source_h, $inside_w, $inside_h)
  {
      if ($source_w < $inside_w && $source_h < $inside_h) {
          return 1; // Percent = 1, 如果都比預計縮圖的小就不用縮
      }
  
      $w_percent = $inside_w / $source_w;
      $h_percent = $inside_h / $source_h;
  
      return ($w_percent > $h_percent) ? $h_percent : $w_percent;
  }
  
  /*
  刪除指定目錄及其目錄下所有的檔案
  例如：
  $dir = "./tempImg/xxxxx/";
  deleteDirectory($dir);
  */
  function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir.DIRECTORY_SEPARATOR.$item)) return false;
    }
    return rmdir($dir);
}
	  /*
	  搬移指定目錄下所有的檔案，回傳搬移檔案名稱的陣列值
	  例如：
	  $srcDir = "./tempImg/xxxxx/";
	  $targetDir = "./upImg/xxxx/"
	  moveDirectory($srcDir,$targetDir);
	  */
	function moveDirectory($srcDir,$targetDir){
		$file_array='';
		 foreach (scandir($srcDir) as $item) {
			if ($item == '.' || $item == '..') continue;
			$file_array[] = $item;
			rename($srcDir.$item,$targetDir.$item);
		}
		deleteDirectory($srcDir);
		return $file_array;
	}

//回傳圖片寬、高度
function get_img_size($dir,$source_img) {
	$img_ex = strtolower(substr(strrchr($source_img,"."),1));
	switch($img_ex){
		case "jpg":
		case "JPG":
		case "JPEG":
		case "jpeg":
			$src_img=ImageCreateFromJpeg($dir.$source_img);
			break;
		case "gif":
			$src_img=ImageCreateFromGif($dir.$source_img);
			break;
	}
	$data['width']=imagesx($src_img);
	$data['height']=imagesy($src_img);
	return $data;
}


?>
