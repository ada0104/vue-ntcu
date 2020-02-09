<?php
$accessLevel=$_SESSION['user_data']->access_level;
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        .table {
            border: 1px solid #6c757d;
            border-radius: .25rem;
            margin-top: 10px; 
        }
        .el-select{
            margin:0% 10px 10px 0%;
         
        }
    </style>
</head>

<body>
    <div id="mission_list" class="content2-Box">
        <div class="title01">核心素養診斷報告</div>
        <el-select v-model="current_school" placeholder="請選擇學校" v-if="isParent">
            <el-option v-for="schoolName in schoolName" :key="schoolName.value" :label="schoolName.text"
                :value="schoolName">
            </el-option>
        </el-select>
        <el-select v-model="current_classroom" placeholder="請選擇班級" v-if="isParent ">
            <el-option v-for="gradeClass in gradeClass" :key="gradeClass.value" :label="gradeClass.text"
                :value="gradeClass">
            </el-option>
        </el-select>
       <el-select v-model="current_student" placeholder="請選擇學生" v-if="isParent ">
            <el-option v-for="studentName in studentName" :key="studentName.user_id" :label="studentName.student_name"
                :value="studentName.user_id">
            </el-option>
        </el-select>
        <el-select v-model="current_classroom" placeholder="請選擇班級" v-if="teacherClass.length > 0">
            <el-option v-for="classroom in teacherClass" :key="classroom.value" :label="classroom.text"
                :value="classroom">
            </el-option>
        </el-select>
        <el-select v-model="current_student" placeholder="請選擇學生" v-if="student_data_of_classroom.length > 0">
            <el-option v-for="student in student_data_of_classroom" :key="student.user_id" :label="student.uname"
                :value="student.user_id">
            </el-option>
        </el-select>
        <el-button type="primary" icon="el-icon-search" @click="getMissionList" v-if="isParent || teacherClass.length > 0">搜尋</el-button>
        <el-table :data="mission_list_of_currentPage" class="table">
            <el-table-column type="index">
            </el-table-column>
            <el-table-column prop="date" label="建立日期" :min-width="25">
            </el-table-column>
            <el-table-column prop="name" label="任務名稱" :min-width="50">
            </el-table-column>
            <el-table-column prop="sn" label="診斷報告" :min-width="25">
                <template slot-scope="scope">
                    <el-link :href="scope.row.url" target="_blank"><i class="el-icon-link"></i>點擊觀看</el-link>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination :hide-on-single-page="true" :page-size="page_size" :total="mission_list.length"
            :current-page.sync="current_page" layout="prev, pager, next" style="text-align:center">
        </el-pagination>
    </div>
</body>
<script>
    
    var mission_list = new Vue({
        el: "#mission_list",
        data: { 
            mission_list: [], 
            page_size: 10, 
            current_page: 1, 
            teacherClass: [], 
            selectedObject: "", 
            current_classroom: null, 
            studentData: [], 
            current_student: "",
            student_data:[],
            isParent:false, 
            schoolName:[],
            studentName:[],
            current_school:null,
            gradeClass:[],
            all_data:[],
            AstudentName:[],
          
        
        },
        mounted() {
            //一進來先判斷登入身分
            this.identity(); 
        },
        watch: {
            current_school(){
                this.current_classroom = null;
                this.gradeClass = [];

                for(i=0; i< this.all_data.length ; i++){
                    if(this.all_data[i].name == this.current_school){
                        this.gradeClass.push(this.all_data[i].grade+"年"+this.all_data[i].class+"班");
                    }
                }
                var gradeClass = Array.from(new Set(this.gradeClass));
                this.gradeClass = gradeClass;
                
            },
            current_classroom(){
                this.current_student = null;
                this.studentData.shift({'uname':'全班','user_id':'all'});
                this.studentName = [];
                for(i=0; i< this.all_data.length ; i++){
                    if(this.AstudentName[i].sclassroom == this.current_classroom && this.current_school == this.all_data[i].name ){
                          this.studentName.push({'student_name':this.all_data[i].uname,'user_id':this.all_data[i].user_id});
                    }
                }

            }
        },
        computed: {
            
            mission_list_of_currentPage() {
                return this.mission_list.slice((this.current_page - 1) * this.page_size, this.current_page * this.page_size);
            },
            student_data_of_classroom() {
                
                if(!this.current_classroom){
                    return [];
                }
                this.studentData.unshift({'uname':'全班','user_id':'all','grade':this.current_classroom.grade,'class':this.current_classroom.class});
                return this.studentData.filter(v => ((v.grade == this.current_classroom.grade && v.class == this.current_classroom.class)||(v.uname == '全班')));

            }

        },
        methods: {
            identity(){
                <?php
	            $accessLevel=$_SESSION['user_data']->access_level;
	            ?>
                var id = <?php echo $accessLevel ?>;
	            var leavel =[];
	            leavel.push(<?php echo  json_encode(USER_PARENTS_GROUP) ?>) ;
                let accessLevel = leavel[0].indexOf(id);
                //如果是家長或是學伴身分
                if( accessLevel != -1){
                    this.isParent=true;
                    this.getSchoolName();
                }else{//老師及學生
                    this.getMissionList();
                    this.getTeacherClass();
                    this.getStudent();
                }
            },
            getMissionList() {
                $.LoadingOverlay("show");
                if(this.current_student == 'all'){
                    console.log(this.current_classroom);
                    var form = new FormData();
                    form.set("type", "1");
                    form.set("class_id", this.current_classroom.value);
                    form.set("grade", this.current_classroom.grade);
                    form.set("class", this.current_classroom.class);
                axios
                    .post("modules/assignMission/assignment/competencyListAll.php", form)
                    .then(response => {
                        //console.log(response);
                        this.mission_list = response.data;
                    }).finally(e => {
                        $.LoadingOverlay("hide");
                    });
                }else{
                   
                    axios
                    .get("modules/assignMission/assignment/competencyList.php?user_id=" + this.current_student)
                    .then(response => {
                        this.mission_list = response.data;
                        //console.log(response);
                    }).finally(e => {
                        $.LoadingOverlay("hide");
                    });
                }
                
            },
            getTeacherClass() {
                axios
                    .post("modules/assignMission/assignment/teacherClass.php")
                    .then(e => {
                        this.teacherClass = e.data;
                        for (let classroom of this.teacherClass) {
                            if (classroom.selected) {
                                this.current_classroom = classroom;
                            }
                        }
                        this.teacherClass.sort((a, b) => (a.grade - b.grade || a.class - b.class));
                    });
            },
            getStudent() {
                var form = new FormData();
                form.set("type", "4");
                axios
                    .post("modules/assignMission/prodb_assignment_data.php", form)
                    .then(e => {
                        this.studentData = e.data;
                        
                    });
            },
            getSchoolName(){
                var form = new FormData();
                form.set("type", "20");
                form.set("fuser_id", "<?php echo $_SESSION['user_data']->user_id; ?>");

                axios
                    .post("modules/assignMission/prodb_assignment_data.php", form)
                    .then(e => {
                        for(i=0;i<e.data.length;i++){
                            this.schoolName.push(e.data[i].name);
                            this.AstudentName.push({'sclassroom':e.data[i].grade+"年"+e.data[i].class+"班"});
                        }
                        var schoolName = Array.from(new Set(this.schoolName));
                        this.schoolName = schoolName;
                        this.all_data = e.data;
                    })

            }
        },
    });
</script>
</html>
