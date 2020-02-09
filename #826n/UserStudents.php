<script src='scripts/adp_core_validation.js'></script> <!-- 2019-09-03 KP:input、textarea 字串輸入驗證通用JS-->
<div id="inline-content" class="personal-inline">
  <div class="title01">個人資料</div>
  <table class="class-list2 test-search table_scroll">
    <tr>
      <td style='text-align:right;'>姓名：</td>
      <td>
        <input type="text" class="forg_id" value="" style="display:none;">
        <input type="text" class="fuser_id" value="" style="display:none;">
        <input class='fname' type="text">
      </td>
      <td style='padding-left:5vh;text-align:right;'>性別：</td>
      <td>
        <select class='f_sex'>
          <option value='0'>請選擇</option>
          <option value='男'>男</option>
          <option value='女'>女</option>
        </select>
      </td>
      <td style="padding-left:5vh;display:none;" class="identity_switch_class">身份調整：</td>
      <td>
        <select class="identity_switch_class identity_switch" id="identity_switch" onchange="IdentitySwitch(this.value, <?php echo json_encode(USER_TEACHER_GROUP); ?>,'updateidentityswitch');">
        </select>
      </td>
    </tr>
    <tr>
      <td class="fpass" style='text-align:right;'>密碼：</td>
      <td class="fpass">
        <input name="fpass" type='password'>
      </td>
      <td class="passcheck" style='text-align:right;'>密碼確認：</td>
      <td class="passcheck">
        <input name="passcheck" type='password'>
      </td>
      <td class="fseme_num" style='padding-left:5vh;text-align:right;'>座號：</td>
      <td>
        <input type="number" class="fseme_num">
      </td>
    </tr>
    <tr>
      <td class="fidentity" style='text-align:right;'>身份證(居留碼)：</td>
      <td>
        <input type="text" class="fidentity">
      </td>
      <td style='padding-left:5vh; text-align:right;'>電子信箱：</td>
      <td>
        <input class='femail' type='email'>
      </td>
      <td style="padding-left:5vh;"></td>
      <td>
        <button class="btn06" id="cancel_openid_button" onclick="cancel_openid();" style="width: max-content;display: none;">取消OpenID綁定</button>
      </td>
    </tr>
  </table>
  <div class='after-20'></div>
  <button class='btn02 edit_ok_btn'>送出修改</button>
</div>

<?php
  session_start();
  $user_data = unserialize($_SESSION['USERDATA']);
  $org_type = getSchoolType();
  $grade_range = STU_IN_SCHOOL_YEAR[$org_type];

  $isTeacher = in_array($user_data->access_level, USER_TEACHER_GROUP);
  if ($isTeacher) {
    $sIsTeacher = '1';
  } else {
    $sIsTeacher = '0';
  }

  // 使用者身分
  if ($user_data->access_level >= 71) {
    $sCity = $dbh->prepare("SELECT DISTINCT city_name FROM city");
    $sCity->execute();
    $vCity = $sCity->fetchAll(PDO::FETCH_ASSOC);
  } else if ($user_data->access_level == USER_SCHOOL_ADMIN){
    $vCity[0] = array(
      "city_name" => $user_data->city_name
    );
  } else if ($isTeacher) {
    $vCity[0] = array(
      "city_name" => $user_data->city_name
    );
  } else {
    die('您的權限不足！');
  }

  // 學期選單
  $sqlGetSeme =
    "SELECT distinct seme_year_seme
    FROM seme_student
    WHERE organization_id = :org_id
    ORDER BY seme_year_seme DESC";

  $oGetSeme = $dbh->prepare($sqlGetSeme);
  $oGetSeme->bindValue(":org_id", $user_data->organization_id, PDO::PARAM_STR);
  $oGetSeme->execute();
  $vGetSeme = $oGetSeme->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content2-Box">
  <div main-box>
    <!-- 查詢框 -->
    <div class="class-list2 test-search table_scroll">
      <div id="SelClass">
        <div>
          <span id="user_id" title="<?php echo $user_data->user_id;?>" style="display:none"></span>
          <span id="user_teacher" title="<?php echo $sIsTeacher;?>" style="display:none"></span>
          <label for="sel_type">選擇查詢方式:</label>
          <select id="sel_type" class='showSel input-normal'>
            <option value="0">請選擇</option>
            <option value="selType">一般查詢</option>
            <option value="nameType">姓名查詢</option>
          </select>
          <span class='typeSel' style="display: none;">
            <?php
              if($user_data->access_level >= 71){
                echo "<select id='city' class='showSel input-normal'><option value='0'>縣市</option>";
                foreach ($vCity as $key => $value) {
                  echo "<option value='".$value['city_name']."'>".$value['city_name']."</option>";
                }
              }
            ?>
            </select>
            <select id="school" class="showSel input-normal">
              <option value="0">學校名稱</option>
              <?php
                if($user_data->access_level == USER_SCHOOL_ADMIN || $isTeacher){
                  echo "<option value='".$user_data->organization_id."' selected='selected' >".$user_data->organization_name."</option>";
                }
              ?>
            </select>
          </span>
        </div>
        <div class='typeSel' style="display: none;">
          <select id="identify" class="showSel input-normal">
            <option value="">身分查詢</option>
            <option value="<?php echo USER_STUDENT; ?>">學生</option>
            <option value="<?php echo USER_TEACHER; ?>">教師</option>
            <option value="<?php echo USER_SCHOOL_ADMIN; ?>">行政</option>
          </select>
            <select name="sel_Seme" id="sel_Seme" class='showSek input-normal' style='display: none;'>
                <?php
                    foreach($vGetSeme as $key => $value){
                        if($value['seme_year_seme'] == $user_data->semeYear){
                          echo "<option value='".$value['seme_year_seme']."' selected='selected' >".$value['seme_year_seme']."學年度</option>";
                        }else{
                          echo "<option value='".$value['seme_year_seme']."' >".$value['seme_year_seme']."學年度</option>";
                        }
                    }
                ?>
            </select>
          <select id="grade" class="showSel input-normal">
            <option value="">年級</option>
            <?php
                if($user_data->access_level == USER_SCHOOL_ADMIN){
                  $sGrade = $dbh->prepare("SELECT DISTINCT grade FROM user_info WHERE organization_id=? ORDER BY `grade` ASC");
                  $sGrade->execute(array($user_data->organization_id));
                  $vGrade = $sGrade->fetchAll(PDO::FETCH_ASSOC);
                  $sOpt = '';
                  foreach($vGrade as $value){
                    if(!in_array($value['grade'], $grade_range)){
                      continue;
                    }
                    $sOpt .= "<option value=".$value['grade'].">".$value['grade']."年</option>";
                  }
                  echo $sOpt;
                }
                ?>
          </select>
          <select id="class" class="showSel input-normal">
            <option value="">班級</option>
          </select>
          <select name="typeSt" id="typeSt" class='showSel input-normal' style='display:none;'>
            <option value="1" selected>在校學生(啟用)</option>
            <option value="2">在校學生(停用)</option>
            <option value="0">畢業學生</option>
          </select>
          <select name="typeAble" id="typeAble" class='showSel input-normal' style='display:none;'>
            <option value="1" selected>(啟用)帳號</option>
            <option value="2">(停用)帳號</option>
          </select>
          <input type="button" class="btn09" id="delClass" style="width:180px;display: none;" value='整班刪除'>
          </br>
          <span id="connection_1" style='cursor: help;'>
            <a target="_blank" href="modules.php?op=modload&name=UserManage&file=setStudentClass_xls" style="border:none;">💡
              批次調整身分證字號，請至編班作業下載檔案匯入</a>
          </span>
          <input type="button" class="btn02" id="search" style="width:100px;display: inline;" value='查詢'>
        </div>
        <div class='typeName' style='display: none;'>
          <label for="search_name">姓名：</label>
          <input type='text' id='search_name' class="showSel input-normal" style='width: 35%'>
          <button id='nameSearch_btn' class="btn02" style='display: inline; width: 150px;'>查詢</button>
        </div>
        <label id="displayRemedy" hidden><input type="checkbox">只顯示扶助學習須補身分證字號的同學</label>
      </div>
    </div>
    <div class="after-20"></div>

    <!-- 學生帳號列表 -->
    <table class="datatable" id="StuData" style='display: none'>
      <tr>
        <th>帳號</th>
        <th>年級</th>
        <th>班級</th>
        <th>座號</th>
        <th>姓名</th>
        <th class="password">密碼</th>
        <th>身份</th>
        <th>綁定OpenID</th>
        <th>綁定扶助學習</th>
        <th>編修功能</th>
      </tr>

      <tbody id="resData">
      </tbody>
    </table>

  </div>
</div>

<script>

  $(document).ready(function() {

    var user_id = document.getElementById("user_id").getAttribute("title");
    var is_teacher = document.getElementById("user_teacher").getAttribute("title");

    if (is_teacher == '1') {
      $('#connection_1').css('display', 'none');
    }

  })
  // sel_type 查詢方式
  $('#sel_type').change(function () {

    $('#displayRemedy').hide();
    $('#delClass').css('display', 'none');
    $('#StuData').css('display', 'none');

    if ($('#sel_type').val() == 'selType') {
      $('.typeSel').css('display', '');
      $('.typeName').css('display', 'none');
      // #709
      if($("#identify").val() == 21){
        $('.identity_switch_class').show();
      }else{
        $('.identity_switch_class').hide();
      }
    } else if ($('#sel_type').val() == 'nameType') {
      $('.typeSel').css('display', 'none');
      $('.typeName').css('display', '');

    } else {
      $('.typeSel').css('display', 'none');
      $('.typeName').css('display', 'none');
    }

    if ($('#user_teacher').attr('title') == '1') {
      // console.log('sel_type.change && user_teacher = 1');
      if ($('#sel_type').val() == 'selType') {
        $('#school').css('display', 'none');
        // $('#school').val('190040');
        $('#school').change();
        $('#identify').val('1');
        $('#identify').css('display', 'none');
        $('#sel_Seme').val('<?=$user_data->semeYear ?>');

      }
    }
  });

  $('#city').change(function () {

    $('#grade').html("<option value=''>年級</option>");
    $('#class').html("<option value=''>班級</option>");
    var city_code = $('#city').val();

    if ($('#city').find('option').length != 2) {
      $.ajax({
        type: "post",
        url: "modules/UserManage/UserMFunction.php",
        data: {
          act: 'schoolOption',
          city_code: city_code
        },
        dataType: "json",
        success: function (response) {
          $('#school').html("<option value=''>學校名稱</option>");
          var sOption = "";
          $.each(response, function (index, value) {
            sOption += "<option value='" + value['organization_id'] + "'>" + value['name'] +
              "</option>";
          });
          $('#school').append(sOption);
        }
      });
    }
  });

  $('#school').change(function () {

    var organization_id = $('#school').val();
    var user_id = $('#user_id').attr("title");
    var is_teacher = $('#user_teacher').attr("title");

    var act = 'GradeOption';

    if (is_teacher == '1') {
      $('#grade').html("");
      $('#class').html("");
    } else {
      $('#grade').val(0);
      $('#class').html("<option value=''>班級</option>");
    }

    $.ajax({
      type: "post",
      url: "modules/UserManage/UserMFunction.php",
      data: {
        act: act,
        organization_id: organization_id,
        user_id: user_id,
        is_teacher: is_teacher
      },
      dataType: "json",
      success: function (response) {
        if (is_teacher == '0') {
          $("#grade").html("<option value=''>年級</option>");
        } else {
          $("#grade").html("");
        }
        // $("#grade").html("<option value=''>年級</option>");
        var sOption = "";
        $.each(response, function (index, value) {
          sOption += "<option value='" + value['grade'] + "'>" + value['grade'] + "年</option>";
        });
        $('#grade').append(sOption);
        $('#grade').change();
      }
    });
  });
  $('#grade').change(function () {

    var organization_id = $('#school').val();
    var grade = $('#grade').val();
    var is_teacher = $('#user_teacher').attr("title");
    var user_id = $('#user_id').attr("title");

    $.ajax({
      type: "post",
      url: "modules/UserManage/UserMFunction.php",
      data: {
        act: 'ClassOption',
        organization_id: organization_id,
        grade: grade,
        user_id: user_id,
        is_teacher: is_teacher
      },
      dataType: "json",
      success: function (response) {
        if (is_teacher == '0') {
          $('#class').html("<option value=''>班級</option>");
        } else {
          $('#class').html("");
        }
        // $('#class').html("<option value=''>班級</option>");
        var sOption = "";
        $.each(response, function (index, value) {
          sOption += "<option value='" + value['class'] + "'>" + value['class'] + "班</option>";
        });
        $('#class').append(sOption);
      }
    });
  });

  //#543 CCW 增加判斷身分 整班刪除 學生或老師
  $('#delClass').click(function () {
    if($('#identify').val() != ''){
        if ($('#school').val() != '' && $('#grade').val() != '' && $('#class').val() != '') {
          var organization_id = $('#school').val();
          var organization_name = $('#school :selected').text();
          var grade = $('#grade').val();
          var classNum = $('#class').val();
          var identify = $('#identify').val();
          var identify_name = $('#identify :selected').text();
          swal({
            title: '確定刪除' + organization_name + grade + "年" + classNum + "班 整班 "+ identify_name +" 的資料？",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: '確定！',
            cancelButtonText: '取消！',
            closeOnConfirm: false,
          }).then(function (isConfirm) {
            if (isConfirm) {
              $.LoadingOverlay("show");
              $.ajax({
                type: "post",
                url: "modules/UserManage/UserMFunction.php",
                data: {
                  act: 'delClass',
                  organization_id: organization_id,
                  grade: grade,
                  class: classNum,
                  identify: identify
                },
                success: function (response) {
                  $.LoadingOverlay("hide");
                  if (response == 1) {
                    swal('班級資料已刪除', '', 'success');
                    $('#StuData').css("display", "none");
                  } else if (response == 0) {
                    swal("該班級無任何成員", '', 'info');
                  }
                }
              });
            }
          });
        } else {
          swal('請填寫每個欄位', '', 'error');
        }
    }else{
        swal('請選擇要刪除的身分', '', 'error');
    }
  });
  $('#identify').change(function () {
    if ($(this).val() == 1) {
      $('#typeSt').css('display', '');
      $('#typeAble').css('display', 'none');
      $('#sel_Seme').css('display','');
    } else {
      $('#typeAble').css('display', '');
      $('#typeSt').css('display', 'none');
      $('#sel_Seme').css('display', 'none');
    }
  });

  $('#search').click(function () {

    $.LoadingOverlay("show");

    if ($('#school').val() != 0) {
      if ($('#identify').val() != '') {
        if ($('#grade').val() != '') {
          var isAllidentify = 0;
        } else {
          var isAllidentify = 1;
        }
        $.ajax({
          type: "post",
          url: "modules/UserManage/UserMFunction.php",
          data: {
            act: 'StuData',
            organization_id: $('#school').val(),
            grade: $('#grade').val(),
            classNum: $('#class').val(),
            identify: $('#identify').val(),
            isAllidentify: isAllidentify,
            typeSt: $('#typeSt').val(),
            typeAble: $('#typeAble').val(),
            sel_seme: $('#sel_Seme').val()
          },
          dataType: "json",
          success: function (res) {
            $.LoadingOverlay("hide");
            if(res == 'empty_graducation_student'){
                $('#resData').html("<tr id='nodata'><td colspan=10>此區間無符合帳號</td></tr>");
                swal('此校不含畢業生', '', 'error');
            }else{
                addData(res, 1);
            }
          }
        });
      } else {
        if ($('#identify').val() == '') {
          $.LoadingOverlay("hide");
          swal('填寫不完整', '請選擇身分', 'error');
        } else {
          $.LoadingOverlay("hide");
          $.ajax({
            type: "post",
            url: "modules/UserManage/UserMFunction.php",
            data: {
              act: 'StuData',
              organization_id: $('#school').val(),
              grade: $('#grade').val(),
              classNum: $('#class').val(),
              identify: $('#identify').val(),
              sel_seme: $('#sel_Seme').val()
            },
            dataType: "json",
            success: function (res) {
              $.LoadingOverlay("hide");
              addData(res, 1);
            }
          });
        }
      }
    } else {
      $.LoadingOverlay("hide");
      swal('填寫不完整', '請選擇學校', 'error');
    }
  });

  $('#nameSearch_btn').click(function () {

    if ($('#search_name').val() != '') {
      $.LoadingOverlay("show");
      $.ajax({
        type: "post",
        url: "modules/UserManage/UserMFunction.php",
        data: {
          act: 'SearchByName',
          name: $('#search_name').val()
        },
        dataType: "json",
        success: function (res) {
          $.LoadingOverlay("hide");
          addData(res, 2);
        }
      });
    } else {
      swal("填寫不完整", "請輸入欲查詢姓名", "error");
    }
  });

  function addData(response, searchType) {
    $('#resData').html("");
    $('#StuData').css("display", "");
    if (!$.isEmptyObject(response.Stu)) {
      $('#resData').css('display', "");
      var resData = "";
      var need_num = 0;
      //console.log("test:"+response.level);
      if (response.level == 1) {
        $('#delClass').css("display", 'inline');

        $.each(response.Stu, function (index, value) {
          resData += "<tr class='resData'><td>" + value['remedy_error'] + value['view_id'] + "</td><td>" + value['grade'] + "</td><td>" +
            value['class'] + "</td><td>" + value['seme_num'] + "</td><td>" + value['uname'] +
            "</td><td>" + value['viewpass'] + "</td><td>" + value['access_title'] + "</td><td>" +
            value['OpenID_sub'] + "</td><td>" + value['priori_name'] + "</td>";
          resData += "<td><a href='#inline-content' class='venoboxinline' title='" + value['user_id'] +
            "' data-title='修改使用者資料' data-vbtype='inline'><img src='img/edit_icon.png' alt='修改使用者資料' class='edit_stu' title='修改使用者資料' height='42' width='42' style='cursor:pointer'></a>";
          if (value['used'] == 1) {
            resData += "<a title='" + value['user_id'] +
              "'><img src='img/unlock.png' alt='停用使用者帳號' class='lock' title='停用使用者帳號' height='42' width='42' style='cursor:pointer'></a>";
          } else {
            resData += "<a title='" + value['user_id'] +
              "'><img src='img/locked.png' alt='啟用使用者帳號' class='unlock' title='啟用使用者帳號' height='42' width='42' style='cursor:pointer'></a>";
          }
          resData += "<a title='" + value['user_id'] +
            "'><img src='img/IconDel.png' alt='刪除使用者帳號' class='stu_del' title='刪除使用者帳號' height='42' width='42' style='cursor:pointer'></a></td>";


        });
      } else if (response.level == 2) {
        $.each(response.Stu, function (index, value) {
          resData += "<tr class='resData'><td>" + value['remedy_error'] + value['view_id'] + "</td><td>" + value['grade'] + "</td><td>" +
            value['class'] + "</td><td>" + value['seme_num'] + "</td><td>" + value['uname'] +
            "</td><td>" + value['viewpass'] + "</td><td>" + value['access_title'] + "</td><td>" +
            value['OpenID_sub'] + "</td><td>" + value['priori_name'] + "</td>";
          resData += "<td><a href='#inline-content' class='venoboxinline' title='" + value['user_id'] +
            "' data-title='修改使用者資料' data-vbtype='inline'><img src='img/edit_icon.png' alt='修改使用者資料' class='edit_stu' title='修改使用者資料' height='42' width='42' style='cursor:pointer'></a>";
          if (value['used'] == 1) {
            resData += "<a title='" + value['user_id'] +
              "'><img src='img/unlock.png' alt='停用使用者帳號' class='lock' title='停用使用者帳號' height='42' width='42' style='cursor:pointer'></a>";
          } else {
            resData += "<a title='" + value['user_id'] +
              "'><img src='img/locked.png' alt='啟用使用者帳號' class='unlock' title='啟用使用者帳號' height='42' width='42' style='cursor:pointer'></a>";
          }
        });
      } else if (response.level == 3) { //校管
        $('#delClass').attr("disabled", true);
        $('.password').css('display', 'true'); // #308
        $.each(response.Stu, function (index, value) {
          if (value['remedy_error'] != "") {
            need_num++;
          }
          let isShow = value['remedy_error'] ? "show" : "";
          resData += `<tr class='resData ${isShow}'><td>` + value['remedy_error'] + value['view_id'] + "</td><td>" +
            value['grade'] + "</td><td>" +
            value['class'] + "</td><td>" + value['seme_num'] + "</td><td>" + value['uname'] + "</td><td>" + value['viewpass'] +
            "</td><td>" + value['access_title'] + "</td><td>" + value['OpenID_sub'] + "</td><td>" + value['priori_name'] + "</td>";
          resData += "<td><a href='#inline-content' class='venoboxinline' title='" + value['user_id'] +
            "' data-title='修改使用者資料' data-vbtype='inline'><img src='img/edit_icon.png' alt='修改使用者資料' class='edit_stu' title='修改使用者資料' height='42' width='42' style='cursor:pointer'></a>";
          if (value['used'] == 1) {
            resData += "<a title='" + value['user_id'] +
              "'><img src='img/unlock.png' alt='停用使用者帳號' class='lock' title='停用使用者帳號' height='42' width='42' style='cursor:pointer'></a>";
          } else {
            resData += "<a title='" + value['user_id'] +
              "'><img src='img/locked.png' alt='啟用使用者帳號' class='unlock' title='啟用使用者帳號' height='42' width='42' style='cursor:pointer'></a>";
          }
        });
      // 教師
      } else if (response.level == 4) {
        $('#delClass').attr("disabled", true);
        $('.password').css('display', 'true'); // #308
        $.each(response.Stu, function (index, value) {
          if (value['used'] == 0) {
            return;
            // i.e. continue
            // 停用時教師不顯示此筆帳號
          }

          if (value['remedy_error'] != "") {
            need_num++;
          }
          let isShow = value['remedy_error'] ? "show" : "";
          resData += `<tr class='resData ${isShow}'><td>` + value['remedy_error'] + value['view_id'] + "</td><td>" +
            value['grade'] + "</td><td>" +
            value['class'] + "</td><td>" + value['seme_num'] + "</td><td>" + value['uname'] + "</td><td>" + value['viewpass'] +
            "</td><td>" + value['access_title'] + "</td><td>" + value['OpenID_sub'] + "</td><td>" + value['priori_name'] + "</td>";

          resData += "<td>";
          resData += "<a href='#inline-content' class='venoboxinline' title='" + value['user_id'] +
            "' data-title='修改使用者資料' data-vbtype='inline'><img src='img/edit_icon.png' alt='修改使用者資料' class='edit_stu' title='修改使用者資料' height='42' width='42' style='cursor:pointer'></a>";
          resData += "</td>";

        });
      }
      $('#resData').append(resData);
      $('.venoboxinline').venobox({
        framewidth: 'auto',
        frameheight: 'auto',
        border: '20px',
        titleattr: 'data-title',
        framewidth: '100%',
      });
      if (need_num > 0) {
        $('#displayRemedy').show();
      }
      if ($('#displayRemedy input').prop('checked')) {
        displayRemedy();
      }
    } else {
      $('#resData').html("<tr id='nodata'><td colspan=10>此區間無符合帳號</td></tr>");
    }


    $('.edit_stu').click(function () {
      var user_id = $(this).parent().attr('title');
      $('.fuser_id').attr("value", response['Stu'][user_id]['user_id']);
      $('.forg_id').attr("value", response['Stu'][user_id]['org_id']);
      $('.fseme_num').attr("value", response['Stu'][user_id]['seme_num']);
      $('.fname').attr("value", response['Stu'][user_id]['uname']);
      $('.fidentity').attr("placeholder", response['Stu'][user_id]['identity'] ? response['Stu'][user_id]['identity'] : "扶助學習對應");
      $('.femail').attr("value", response['Stu'][user_id]['email']);
      $('.f_sex option').each(function () {
        if ($(this).val() == response['Stu'][user_id]['sex']) {
          $(this).attr('selected', true);
        } else {
          $(this).attr('selected', false);
        }
      });

      var user_level = <?php echo $user_data->access_level ?>;
      var user_school_admin = <?php echo USER_SCHOOL_ADMIN; ?>;
      var user_admin = <?php echo USER_ADMIN; ?>;
      var user_teacher_group = <?php echo json_encode(USER_TEACHER_GROUP); ?>;
      //#826 Ada 新增 - 管理者取消openid權限
      //判斷是否為校管或管理者身分
      if(user_level == user_school_admin || user_level == user_admin){
        //綁定 OPENID 有才顯示取消按鈕
        if(response['Stu'][user_id]['OpenID_sub'] == '已綁定'){
          $("#cancel_openid_button").show();
        }else{
          $("#cancel_openid_button").hide();
        }

        //判斷是否在 老師陣列 中找到 使用者的權限相同 有的話 顯示 切換角色的功能
        if(user_teacher_group.indexOf(response['Stu'][user_id]['access_level']) >= 0){
          IdentitySwitch(response['Stu'][user_id]['access_level'], user_teacher_group, 'optionsetting');

          //拿掉講師
          var show_identify_teacher_list = user_teacher_group;
          show_identify_teacher_list = show_identify_teacher_list.filter(v=>v!=25);
          if(show_identify_teacher_list.includes(response['Stu'][user_id]['access_level'])){
            $('.identity_switch_class').show();
          }else{
            $('.identity_switch_class').hide();
          }
        }else{
          $('.identity_switch_class').hide();
        }
      }

      if (response['Stu'][user_id]['seme_num'] == "無" || response['Stu'][user_id]['access_title'] != "學生") {
        $('.fseme_num').hide();
      } else {
        $('.fseme_num').show();
      }
      if (response.level != 3 && response.level != 4) {
        $('.passcheck').css("display", "none");
      }
      $(document).on('click', '.edit_ok_btn', function (event) {

        var editData = {
          "user_id": $('.fuser_id').last().val(),
          "uname": $('.fname').last().val(),
          "sex": $('.f_sex').last().val(),
          "email": $('.femail').last().val(),
          "pass": $("input[name=fpass]").last().val(),
          "org_id": $('.forg_id').last().val(),
          "seme_num": $('.fseme_num').last().val(),
          "identity": $('.fidentity').last().val().toUpperCase()
        };
        //2019-09-05 KP:逐一檢查value是否有XSS Payload
        var validation_result = 'false';
        $.each(editData,function(index,value){
          validation_result = XSSvalidataion(value);
          if(validation_result == 'false') return false;
        });
        if(validation_result == 'true'){
          // 檢查身分證字號格式
          // 英數字，十碼，不得用符號
          var identity = $('.fidentity').last().val().toUpperCase();
          identity = identity.replace(/\s/g, "");
          var identityLimit = new RegExp('[^A-Za-z0-9]', 'g');
          var illegalIdentity = identityLimit.test(identity);
          if (illegalIdentity) {
            swal({
              title: identity,
              text: '身份證格式有誤，請填寫英數字',
              type: 'error'
            });

          // 校管和教師
          } else if (response.level == 3 || response.level == 4) {
            var pass = $("input[name=fpass]").last().val();
            var passcheck = $('input[name=passcheck]').last().val();

            if (pass == passcheck) {
              $.ajax({
                type: "post",
                url: "modules/UserManage/UserMFunction.php",
                data: {
                  act: 'editStu',
                  editData: editData
                },
                success: function (response) {
                  swal({
                    title: '資料修改完成',
                    type: 'success'
                  }, function () {
                    $('.vbox-close, .vbox-overlay').trigger('click');
                    if (searchType == 1) {
                      $('#search').trigger('click');
                    } else {
                      $('#nameSearch_btn').trigger('click');
                    }
                  });
                }
              });
            } else {
              swal('兩次密碼輸入不同，請重新輸入', '', 'error');
            }
          } else {
            $.ajax({
              type: "post",
              url: "modules/UserManage/UserMFunction.php",
              data: {
                act: 'editStu',
                editData: editData
              },
              success: function (response) {
                swal({
                  title: '資料修改完成',
                  type: 'success'
                }, function () {
                  $('.vbox-close, .vbox-overlay').trigger('click');
                  if (searchType == 1) {
                    $('#search').trigger('click');
                  } else {
                    $('#nameSearch_btn').trigger('click');
                  }
                });
              }
            });
          }
        } //end of if(validation_result)
      });
    });
    $('.lock').click(function () {
      var user_id = $(this).parent().attr('title');
      var is_teacher = $("#user_teacher").attr('title');
      if (is_teacher == '1') {
        swal({
          title: '請由校管將學生帳號停用/啟用',
          type: 'warning',

        })
      }

      swal({
        title: '停用使用者帳號',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: '確定！',
        cancelButtonText: '取消！',
        closeOnConfirm: false,
      }).then(function (isConfirm) {
        if (isConfirm) {
            if($("#identify").val() == '1'){
                swal('帳號已停用', '已將該生移至 在校學生(停用) 清單', 'success');
            }else{
                swal('帳號已停用', '', 'success');
            }
          $.ajax({
            type: "post",
            url: "modules/UserManage/UserMFunction.php",
            data: {
              act: 'par_lock',
              par_id: user_id
            },
            success: function (response) {
              if (searchType == 1) {
                $('#search').trigger('click');
              } else {
                $('#nameSearch_btn').trigger('click');
              }
            }
          });
        }
      });
    });
    $('.unlock').click(function () {
      var user_id = $(this).parent().attr('title');
      swal({
        title: '啟用使用者帳號',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: '確定！',
        cancelButtonText: '取消！',
        closeOnConfirm: false,
      }).then(function (isConfirm) {
        if (isConfirm) {
            if($("#identify").val() == '1'){
                swal('帳號已啟用', '已將該生移至 在校學生(啟用) 清單', 'success');
            }else{
                swal('帳號已啟用', '', 'success');
            }
          $.ajax({
            type: "post",
            url: "modules/UserManage/UserMFunction.php",
            data: {
              act: 'par_unlock',
              par_id: user_id
            },
            success: function (response) {
              if (searchType == 1) {
                $('#search').trigger('click');
              } else {
                $('#nameSearch_btn').trigger('click');
              }
            }
          });
        }
      });
    });
    $('.stu_del').click(function () {
      var user_id = $(this).parent().attr('title');
      swal({
        title: '刪除使用者帳號',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: '確定！',
        cancelButtonText: '取消！',
        closeOnConfirm: false,
      }).then(function (isConfirm) {
        if (isConfirm) {
          $.ajax({
            type: "post",
            url: "modules/UserManage/UserMFunction.php",
            data: {
              act: 'stu_del',
              user_id: user_id
            },
            success: function (response) {
              if (response == 0) {
                swal('此帳號已有考試資料，如欲刪除此帳號請洽助理人員。', '', 'warning');
              } else if (response == 1) {
                swal('帳號已刪除', '', 'success');
                if (searchType == 1) {
                  $('#search').trigger('click');
                } else {
                  $('#nameSearch_btn').trigger('click');
                }
              }
            }
          });
        }
      });
    });
    $("#displayRemedy input").click(displayRemedy);
  }

  function displayRemedy() {
    if ($("#displayRemedy input").prop('checked')) {
      $("#resData tr").hide();
      $("#resData tr.show").show();
    } else {
      $("#resData tr").show();
    }
  }
  if ('true' == '<?=$_GET['
    direct ']?>') {
    $('#sel_type').val('selType');
    $('#identify').val('1');
    $('#search').trigger("click");
    $('#displayRemedy').trigger("click");
  }
  //#709 取消openid功能
  function cancel_openid(){
    var fuser_id = $('.fuser_id').val();
    var user_name = $('.fname').last().val();
    swal({
      title: "確定要取消 "+user_name+' 的OPENID 綁定嗎?',
      text: '取消OpenID綁定，只能透過教育雲端帳號重新登入才會再綁定',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#DD6B55',
      confirmButtonText: '確定！',
      cancelButtonText: '取消！',
      closeOnConfirm: false,
    }).then(function (isConfirm) {
      if (isConfirm) {
        $.LoadingOverlay("show");
        $.ajax({
          type: "post",
          url: "modules/UserManage/UserMFunction.php",
          data: {
            act: 'cleanOPENID',
            user_id: fuser_id,
          },
          success: function (response) {
            $.LoadingOverlay("hide");
            if (response == 1) {
              swal(user_name+' 的 OPENID綁定已解除', '', 'success');
            }else{
              swal(user_name+' 的 OPENID綁定解除失敗', '', 'info');
            }
            if($('#sel_type').val() == 'selType'){
              $('.vbox-close, #search').click();
            }else if($('#sel_type').val() == 'nameType'){
              $('.vbox-close, #nameSearch_btn').click();
            }
          }
        });
      }
    });
  }
  function IdentitySwitch(teacher_access, user_teacher_group, action){
    var user_teacher_group_CH = ['教師','主任','校長'];

    var delete_access = 25;

    var index = user_teacher_group.indexOf(delete_access);
    if (index !== -1) user_teacher_group.splice(index, 1);

    switch(action) {
      case 'optionsetting':
        $('#identity_switch').empty();
        var select = document.getElementById('identity_switch');
        select.value=31;
        for (i = 0; i < user_teacher_group.length; i++) {
          var opt = document.createElement('option');
          opt.value = user_teacher_group[i];
          opt.innerHTML = user_teacher_group_CH[i];

          if(teacher_access == user_teacher_group[i]){
            setTimeout(function(){
              for(let s of $(".identity_switch").get()){
                s.value = teacher_access;
              }
            }, 500);
          }else{

          }
          select.appendChild(opt);
        }
        break;
      case 'updateidentityswitch':
        var user_name = $('.fname').last().val();
        var fuser_id = $('.fuser_id').val();
        var oldidentity_name = $('#identity_switch option:selected').text();
        var newidentity = Number(teacher_access);
        var index2 = user_teacher_group.indexOf(newidentity);
        var newidentity_name = user_teacher_group_CH[index2];
        swal({
          title: "確定要將 "+user_name+' 的身份 從 '+oldidentity_name+' 調整成 '+newidentity_name+' 嗎',
          text: '',
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#DD6B55',
          confirmButtonText: '確定！',
          cancelButtonText: '取消！',
          closeOnConfirm: false,
        }).then(function (isConfirm) {
          if (isConfirm) {
            $.LoadingOverlay("show");
            $.ajax({
              type: "post",
              url: "modules/UserManage/UserMFunction.php",
              data: {
                act: 'UpdateIdentity',
                user_id: fuser_id,
                new_indentity: newidentity,
              },
              success: function (response) {
                $.LoadingOverlay("hide");
                if (response == 1) {
                  swal(user_name+' 的 身份已經調整為 '+ newidentity_name, '', 'success');
                }else{
                  swal(user_name+' 的 身份調整失敗，請聯繫系統人員協助處理', '', 'info');
                }
                if($('#sel_type').val() == 'selType'){
                  $('.vbox-close, #search').click();
                }else if($('#sel_type').val() == 'nameType'){
                  $('.vbox-close, #nameSearch_btn').click();
                }
              }
            });
          }
        });
        break;
      default:
        break;
    }
  }
</script>
