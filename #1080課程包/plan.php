<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>因材網</title>
	<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=yes,maximum-scale=5.0,minimum-scale=1.0">
	<link href='favicon.ico' rel='icon' type='image/x-icon' />
	<link rel="stylesheet" href="css/cms-index_aial.css?ver=180521" type="text/css">
	<link rel="stylesheet" href="css/cms-header_aial.css" type="text/css">
	<link rel="stylesheet" href="css/cms-footer_aial.css" type="text/css">
	<link rel="stylesheet" href="css/jquery.tipsy.css" type="text/css">
	<link rel="stylesheet" href="css/sticky.css" type="text/css" />
	<link rel="stylesheet" href="css/sortable-theme-minimal.css" />

	<link rel="stylesheet" type="text/css" href="scripts/tab/easy-responsive-tabs.css?ver=180521" />

	<link id="main-css" href="scripts/menu/menuzord.css?ver=0218" type="text/css" rel="stylesheet">

	<script type="text/javascript" src="./include/jquery-1.7.2.min.js"></script>
	
	<link rel="stylesheet" href="scripts/venobox/venobox.css?ver=180514" type="text/css" media="screen" />
	<script type="text/javascript" src="scripts/venobox/venobox.min.js?ver=180514"></script>
  <script src="./js/jquery.blockUI.js"></script>
  <script>
  function showBlockUI(){
	 	  $.blockUI({
      message: '<div align="center"><img src="./image/ajax-loader.gif"/> 單元讀取中,請稍候...</div>',
      css: {
           width: '500px',
           height: '50px',
      }
    });
    return true;
  }
</script>
</head>


	<body <?php echo $oncontextadd;?>>

	<header>
		<div class="content-Box"> 
			<div class="logo">
				<a href="http://210.65.89.151/index_AIAL2.php">
					<img src="images/logo.png">
				</a>
			</div>
		</div>
	</header>
		<?php
			require_once("plan.html");
		?>

<!--
===============================================================
檔名：	feet_new.php
功能：	頁尾
作者：
異動者	日期			異動說明
----------------------------------------------------
coway	20161003	增加教育部LOGO
coway	20170119	修改關於我們：目標特色
teresa	20170203	增加 btn2canvas for 問題回報 function
teresa	20170302	修改 操作流程
teresa	20170417	修改 操作流程 為 iframe
===============================================================
-->

<link rel="stylesheet" href="scripts/sweetalert2/sweetalert2.min.css">
<script type="text/javascript">
 $(document).ready(function(){
		$('.venobox_custom').venobox({
			framewidth: '480px',
			frameheight: 'auto',
			border: '10px',
			bgcolor: '#EEE',
			titleattr: 'data-title',
		});
	})
</script>

<footer class="after-0">
	<div class="content-Box">
		<div class="footer-left">
			<span>關於我們</span>
			<div>
				<a class="venobox_custom" data-title="聯絡我們" data-gall="gall-frame" data-vbtype="inline" href="#inline-content-footer" title="連絡資訊">連絡資訊</a>
				<a target="_blank" href="systemIntor.php">系統特色</a>
				<a target="_blank" href="teamMember.php">研發團隊</a>
				<a target="_blank" href="Privacy_Page.php">因材網同意書</a>
			</div>
			<span>操作流程</span>
			<div>
				<a target="_blank" href="https://bit.ly/2OUtgqu">系統操作</a>
				<a target="_blank" href="https://goo.gl/forms/lBcDaAyPSjS3ZG9R2">訪客帳號申請</a>
			</div>
		</div>

		<div class="footer-right">
			<div class="teacher-logo">
				<img src="images/teacher-logo.png">
			</div>
			<span>© 2016 國立臺中教育大學</span>
			<span>♦ 測驗統計與適性學習研究中心</span>
			<br>
			<span>最佳瀏覽建議： Chrome 瀏覽器</span>
			<span>♦ 最佳解析度：1280x768</span>
	</div>
</div>
	
</footer>
	<div>
		<div id="inline-content-footer" style="display:none;">
			<div style="background:#fff; width:450px;margin:0 auto; height:550px; padding:10px;">
				若有系統問題請洽：
				<div class="contact-title">
					<i class="fa fa-map-marker" aria-hidden="true"></i> 【服務時間】 週一至週五 08：00-12：00，13：30-17：30</div>
				<div class="contact-title">
					<i class="fa fa-envelope" aria-hidden="true"></i> 【連絡信箱】
					<a href="mailto:ai.ntcu.edu@gmail.com">ai.ntcu.edu@gmail.com</a> ♦ TEL:04-2218-1033</div>
				<br> 若有教材(影片、試題)問題請洽：
				<br>
				<div class="contact-title">
					<i class="fa fa-chevron-circle-right"></i> 【數學領域】04-2218-1033 ♦ ai.ntcu.edu@gmail.com ♦ 40306臺中市西區民生路140號</div>
				<br>
				<div class="contact-title">
					<i class="fa fa-chevron-circle-right"></i> 【語文領域】04-2218-3477 ♦ lan@gm.ntcu.edu.tw ♦ 40306臺中市西區民生路140號</div>
				<br>
				<div class="contact-title">
					<i class="fa fa-chevron-circle-right"></i> 【自然科學領域】03-4227151#57861 ♦ wuret.moe@gmail.com ♦ 32001桃園市中壢區中大路300號</div>
			</div>
		</div>
	</div>
	<div id="gotop">
		<a class="fa fa-chevron-up"></a>
	</div>

	<!-- <script type="text/javascript" src="scripts/customer.js"></script> -->
	<!-- <script type="text/javascript" src="scripts/html2canvas.js"></script> -->
	<script>
		$(document).ready(function () {
			$(".idx-main").hover(function () {
				$(".idx-main").toggleClass("open-close");
			});

		});
	</script>

	<script type="text/javascript">
		$(document).ready(function () {
			/**調整html2canvas至新版 2018-08-10 YXY */
			$('#btn2canvas').click(function(e){
				$.LoadingOverlay("show");
				html2canvas(document.body).then(function(canvas) {
						var imagedata = canvas.toDataURL('image/png');
						var imgdata = imagedata.replace(/^data:image\/(png|jpg);base64,/, "");
						//ajax call to save image inside folder
						$.ajax({
							url: 'modules/assignMission/prodb_feedback_data.php',
							data: {
								imgdata: imgdata
							},
							type: 'post',
							success: function (response) {
								// console.log(response);
								// console.log(now_url);
								var url = "QAfeedback.php?imgpath=" + response;
								openIframeModal("fas fa-flag", "問題回報", url);
								//console.log(url);
								/*$("#a2canvas").attr("href", url);
								$("#a2canvas").venobox().trigger('click');*/
								$.LoadingOverlay("hide");
								//把結果回傳至 iframe...
							}
						});
				});
			});
		});
	</script>

	<!-- <script type="text/javascript" src="scripts/jsManage.js"></script> -->
		<div id="dialogModal">

		</div>
	</body>

	</html>
	</html>
</html>
<!-- <script src="scripts/netspeed.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/mobile-detect@1.4.3/mobile-detect.min.js"></script>
<script>
	var orgId = '<?=$user_data->organization_id?>';
	var secs = 0;
	if(!window.chrome.csi()){
		var countTime = setInterval(e => {
			secs++;
		}, 1000);
	}
	var md = new MobileDetect(window.navigator.userAgent);
	if(md.mobile()){
		window.addEventListener('pagehide', feedDog, false);//mobile
	}else{
		window.addEventListener('unload', feedDog, false);//desktop
	}
	function feedDog(e) {
		if(window.chrome){
			secs = window.chrome.csi().pageT / 1000;
		}
		localStorage.setItem("idleTime", secs);
		let formData = new FormData();
		formData.set('org_id', orgId);
		formData.set('seconds', secs);
        if(navigator.sendBeacon("modules/UserManage/FeedDog.php", formData)){
        }else{
            var client = new XMLHttpRequest();
        	client.open("post", "modules/UserManage/FeedDog.php", false);
            client.send(formData);
		}
	}
	function openIframeModal(icon, title, url){
		$("#dialogModal").iziModal({
				title: title,
				icon: icon,
				bodyOverflow: true,
				openFullscreen: true,//內容超過一頁建議用全螢幕，要不然過長會無法滾動
				padding: 5,//一定要加，不然會跑版
				iframe: true,
				iframeURL: url,
				onOpened: function () {
				},
				onOpening: function(){
					$("#qreport").hide();
				},
				onClosing: function(){
					$("#qreport").show();
				},
				onClosed: function(){
					$("#dialogModal").iziModal('destroy');
				},
		}).iziModal('open');
		$(".iziModal-iframe").on("load", function(){
			$('.iziModal-wrap').scrollTop(0);//讀取後滾到最上面
		});
	}
</script>
<style>

</style>
		</body>
