<?php
session_start();

include("./bcontroller/class/common_lite.php");

    //建立資料庫物件
    $ODb = new run_db("mysql",3306);      
    
    //老師資料
    $teachernum =$_SESSION['swTeacherNum'];
    $whereDsc = " where `teacherdataNum`='".$_SESSION['swTeacherNum']."' ";

    //取得此老師帳號下的題目清單編號
    $sql_dsc = "SELECT * FROM `test_time_teacher` ".$whereDsc." GROUP BY `f_num`";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	$f_num_dsc = '';
	while($row = mysql_fetch_array($res)){
		$f_num_new .= ",".$row['f_num'];
	}
	if($f_num_new== ''){
		$f_num_new = 0;
    };
    
    $f_num_new = substr($f_num_new,1,strlen($f_num_new));
    //取得所有資料
    $sql_dsc ="SELECT
    studentdata.city_name,
    studentdata.num student_num,
    studentdata.school_name,
    studentdata.grade_dsc,
    studentdata.class_dsc,
    studentdata.student_id,
    studentdata.c_name,
    studentdata.sex_dsc,
    test_time_list.c_title list,
    main_data.c_title c_title,
    opt_record.test_begin_time,
    opt_record.power_dsc,
    opt_record.main_data_num,
    main_data.count_c_power_dsc,
    test_time_list.num listnum
    FROM
    opt_record
    LEFT JOIN test_time_list ON opt_record.timelist_num = test_time_list.num
    LEFT JOIN main_data ON opt_record.main_data_num = main_data.num
    LEFT JOIN studentdata ON studentdata.num = opt_record.student_user
    WHERE
    opt_record.teacher_user = $teachernum AND opt_record.student_user > 0 AND opt_record.timelist_num IN ($f_num_new);";
    
    $result=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
   
    while ($row = mysql_fetch_assoc($result)) {
        $all_data[]= $row; 
       
    };


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>合作問題解決線上評量</title>
<script src="./js/jquery-1.10.1.min.js"> </script>
<script src="./js/javascript.js"></script><!-- 頁面收和 -->
<script src="./js/jquery-ui.js"></script>
<!-- <link rel="stylesheet" href="css/admin.css" /> -->
<link rel="stylesheet" href="css/jquery-ui.css" />
<link rel="Stylesheet" href="css/jquery-ui-1.7.1.custom.css" type="text/css" />
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.0"></script>
<!-- 引入样式 -->
<link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
<!-- 引入组件库 -->
<script src="https://unpkg.com/element-ui/lib/index.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script src="https://cdn.bootcss.com/qs/6.5.1/qs.min.js"></script>
</head>

    <style>
        #show_record{
            /* margin:2% 5% 4% 5%; */
            font-family:Microsoft JhengHei;
            background-color:#F0F0F0;
        }
       
        .el-select{
            margin:10px 10px 10px 0px;
        }
        .el-table{
            margin:auto;
        }
        .el-button{
            margin-left:2px;
        }
        .txt_shadow{
            text-shadow: 2px 2px 6px #6b6b6b;
            font-size: 30px;
            color:#444444;
            font-family:Microsoft JhengHei;
        }

</style>

<body>
    <div id="show_record">
        <div class="txt_shadow">{{school_name}}{{teacher_name}}老師，您好!
        <!-- <el-button type="primary"  onclick="location.href='logout.php'" >登出</el-button> -->
        </div>
        
        <el-select v-model="current_classroom" placeholder="請選擇班級" v-if="isTeacher">
            <el-option v-for="gradeClass in gradeClass" :key="gradeClass.value" :label="gradeClass.text"
                :value="gradeClass">
            </el-option>
        </el-select>
        <el-select v-model="current_list" placeholder="請選擇題目清單" v-if="isTeacher">
            <el-option v-for="list in list" :key="list.value" :label="list.text"
                :value="list">
            </el-option>
        </el-select>
        <el-select v-model="current_title" placeholder="請選擇題目名稱" v-if="isTeacher">
            <el-option v-for="main_data in main_data" :key="main_data.value" :label="main_data.text"
                :value="main_data">
            </el-option>
        </el-select>
        <el-button type="primary" icon="el-icon-search" @click="show_data" v-if="isTeacher">查詢</el-button>

        <el-table :data="all_data_new1" height="800" border style="width: 100%" v-if="check_data">
            <el-table-column prop="name" label="姓名" width="90" alt="123"></el-table-column>
            <el-table-column prop="student_id" label="學號" width="90"></el-table-column>
            <!-- <el-table-column prop="c_title" label="題目名稱" width="130"></el-table-column> -->
            <el-table-column prop="test_begin_time" label="測驗時間" width="150"></el-table-column>
            <el-table-column prop="test[0]" label="1"  ></el-table-column>
            <el-table-column prop="test[1]" label="2"  ></el-table-column>
            <el-table-column prop="test[2]" label="3"  ></el-table-column>
            <el-table-column prop="test[3]" label="A"  ></el-table-column>
            <el-table-column prop="test[4]" label="B"  ></el-table-column>
            <el-table-column prop="test[5]" label="C"  ></el-table-column>
            <el-table-column prop="test[6]" label="D"  ></el-table-column>
            <el-table-column prop="test[7]" label="(A1)" ></el-table-column>
            <el-table-column prop="test[8]" label="(B1)" ></el-table-column>
            <el-table-column prop="test[9]" label="(C1)" ></el-table-column>
            <el-table-column prop="test[10]" label="(D1)" ></el-table-column>
            <el-table-column prop="test[11]" label="(A2)" ></el-table-column>
            <el-table-column prop="test[12]" label="(B2)" ></el-table-column>
            <el-table-column prop="test[13]" label="(C2)" ></el-table-column>
            <el-table-column prop="test[14]" label="(D2)" ></el-table-column>
            <el-table-column prop="test[15]" label="(A3)" ></el-table-column>
            <el-table-column prop="test[16]" label="(B3)" ></el-table-column>
            <el-table-column prop="test[17]" label="(C3)"></el-table-column>
            <el-table-column prop="test[18]" label="(D3)"></el-table-column>
        </el-table>
</body>
         

<script> 
   var all_data_php = <?php echo json_encode($all_data); ?>;
   
    var show_record= new Vue({
        el: "#show_record",
        data: { 
           teacher_name:"<?php echo $_SESSION['loginUserName']?>",
           isTeacher:true,
           gradeClass:[],
           current_classroom:'',
           current_title:'',
           all_data:all_data_php,
           main_data:[],
           school_name:all_data_php[0].school_name,
           all_data_new:[],
           all_data_new1:[],
           check_data:false,
           record:[],
           list:[],
           current_list:'',

           
    
        },
        mounted() {
            this.resetdata();
        },
        watch: {
            current_classroom(){
                this.check_data = false;
                this.current_list=[];
                this.current_title = [];
                for(i=0; i< this.all_data_new.length ; i++){
                    if(this.current_classroom == all_data_php[i].grade_dsc+"年"+all_data_php[i].class_dsc+"班"){
                        this.list.push(this.all_data_new[i].list);
                    }
                }
                this.list = Array.from(new Set(this.list));
            },
            current_list(){
                this.check_data = false;
                this.current_title = [];
                this.main_data = [];
                for(i=0; i< this.all_data_new.length ; i++){
                    if(this.current_classroom == all_data_php[i].grade_dsc+"年"+all_data_php[i].class_dsc+"班" && this.current_list == all_data_php[i].list){
                        this.main_data.push(this.all_data_new[i].c_title);
                    }
                }
                this.main_data.push("全部");
                this.main_data = Array.from(new Set(this.main_data));
            },
            current_title(){
                this.all_data_new1 = [];

                for(i=0; i< this.all_data_new.length ; i++){
                    if(this.current_classroom == all_data_php[i].grade_dsc+"年"+all_data_php[i].class_dsc+"班" && this.current_title == all_data_php[i].c_title  && this.current_list == all_data_php[i].list){
                        this.all_data_new1.push(
                            {'name':this.all_data_new[i].c_name,
                            'student_id':this.all_data_new[i].student_id,
                            'c_title':this.all_data_new[i].c_title,
                            'test_begin_time':this.all_data_new[i].test_begin_time,
                            'power_dsc':this.all_data_new[i].power_dsc,
                            'test':this.all_data_new[i].test,
                            });
                    }
                }
                
            }
        },
        methods: {
        
            show_data(){
                this.check_data=true;
             
            },
            resetdata(){

            this.all_data_new =JSON.parse(JSON.stringify(all_data_php));
                console.log(this.all_data_new);
                
            for(i=0;i<this.all_data_new.length;i++){
                this.gradeClass.push(this.all_data_new[i].grade_dsc+"年"+this.all_data_new[i].class_dsc+"班");
                var power_dsc_a = this.all_data_new[i].power_dsc;
                var NewArray = power_dsc_a.split(",");
                var NewArray =NewArray.map(Number);
                var count_c_power_dsc_a = this.all_data_new[i].count_c_power_dsc;
                var NewArray1 = count_c_power_dsc_a.split(",");
                var NewArray1 =NewArray1.map(Number);
                var a = '';
                if(this.all_data_new[i].listnum = 33 ){
                    a += this.all_data_new[i].listnum;
                    console.log(a);
                }
                this.record=[];

                this.record[0] = ((NewArray[0]+NewArray[1]+NewArray[2]+NewArray[3])/((NewArray1[0]+NewArray1[1]+NewArray1[2]+NewArray1[3])*2)).toString().substr(0,5);
                this.record[1] = ((NewArray[4]+NewArray[5]+NewArray[6]+NewArray[7])/((NewArray1[4]+NewArray1[5]+NewArray1[6]+NewArray1[7])*2)).toString().substr(0,5);
                this.record[2] = ((NewArray[8]+NewArray[9]+NewArray[10]+NewArray[11])/((NewArray1[8]+NewArray1[9]+NewArray1[10]+NewArray1[11])*2)).toString().substr(0,5);
                this.record[3] = ((NewArray[0]+NewArray[4]+NewArray[8])/((NewArray1[0]+NewArray1[4]+NewArray1[8])*2)).toString().substr(0,5);
                this.record[4] = ((NewArray[1]+NewArray[5]+NewArray[9])/((NewArray1[1]+NewArray1[5]+NewArray1[9])*2)).toString().substr(0,5);
                this.record[5] = ((NewArray[2]+NewArray[6]+NewArray[10])/((NewArray1[2]+NewArray1[6]+NewArray1[10])*2)).toString().substr(0,5);
                this.record[6] = ((NewArray[3]+NewArray[7]+NewArray[11])/((NewArray1[3]+NewArray1[7]+NewArray1[11])*2)).toString().substr(0,5);
                this.record[7] = (NewArray[0]/(NewArray1[0]*2)).toString().substr(0,5);
                this.record[8] = (NewArray[1]/(NewArray1[1]*2)).toString().substr(0,5);
                this.record[9] = (NewArray[2]/(NewArray1[2]*2)).toString().substr(0,5);
                this.record[10] = (NewArray[3]/(NewArray1[3]*2)).toString().substr(0,5);
                this.record[11] = (NewArray[4]/(NewArray1[4]*2)).toString().substr(0,5);
                this.record[12] = (NewArray[5]/(NewArray1[5]*2)).toString().substr(0,5);
                this.record[13] = (NewArray[6]/(NewArray1[6]*2)).toString().substr(0,5);
                this.record[14] = (NewArray[7]/(NewArray1[7]*2)).toString().substr(0,5);
                this.record[15] = (NewArray[8]/(NewArray1[8]*2)).toString().substr(0,5);
                this.record[16] = (NewArray[9]/(NewArray1[9]*2)).toString().substr(0,5);
                this.record[17] = (NewArray[10]/(NewArray1[10]*2)).toString().substr(0,5);
                this.record[18] = (NewArray[11]/(NewArray1[11]*2)).toString().substr(0,5);
                
                this.record = this.record.map(function(e){
                    if (isNaN(e)) {
                    return '無';
                    }
                    return e;
                })

                this.all_data_new[i].test = this.record;
                
            }
            this.gradeClass = Array.from(new Set(this.gradeClass));
        }
    }});
</script>
</html>