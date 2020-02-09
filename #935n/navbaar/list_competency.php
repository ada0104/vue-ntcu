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
    </style>
</head>

<body>
    <div id="mission_list" class="content2-Box">
        <div class="title01">核心素養診斷報告</div>
        <el-select v-model="current_classroom" placeholder="請選擇學校" v-if="isParent">
            <el-option v-for="classroom in teacherClass" :key="classroom.value" :label="classroom.text"
                :value="classroom">
            </el-option>
        </el-select>
        <el-select v-model="current_classroom" placeholder="請選擇班級" v-if="isParent ||teacherClass.length > 0">
            <el-option v-for="classroom in teacherClass" :key="classroom.value" :label="classroom.text"
                :value="classroom">
            </el-option>
        </el-select>
        <el-select v-model="current_student" placeholder="請選擇學生" v-if="isParent ||student_data_of_classroom.length > 0">
            <el-option v-for="student in student_data_of_classroom" :key="student.user_id" :label="student.uname"
                :value="student.user_id">
            </el-option>
        </el-select>
        <el-button type="primary" icon="el-icon-search" @click="getMissionList" v-if="isParent || teacherClass.length > 0">搜索</el-button>
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
            getSchoolName:[]
        },
        mounted() {
            //一進來先判斷登入身分
            this.identity(); 
        },
        watch: {
            current_classroom() {
                this.current_student = null;
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
                return this.studentData.filter(v => (v.grade == this.current_classroom.grade && v.class == this.current_classroom.class));
            }
        },
        methods: {
            identity(){
                var id = <?php echo $accessLevel ?>;
                
                //如果是家長或是學伴身分
                if( id == "11" || id == "12"){
                    console.log(id);
                    this.isParent=true;
                    this.getSchoolName();
                    this.getStudent();
                }else{//老師及學生
                    this.getMissionList();
                    this.getTeacherClass();
                    this.getStudent();
                }
            },
            getMissionList() {
                $.LoadingOverlay("show");
                axios.get("modules/assignMission/assignment/competencyList.php?user_id=" + this.current_student)
                .then(response => {
                    this.mission_list = response.data;
                    console.log('getMissonList',this.mission_list);
                }).finally(e => {
                    $.LoadingOverlay("hide");
                });
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
                        console.log('getTeacherClass',this.teacherClass);
                    });
            },
            getStudent() {
                var form = new FormData();
                form.set("type", "4");
                axios
                    .post("modules/assignMission/prodb_assignment_data.php", form)
                    .then(e => {
                        this.studentData = e.data;
                        console.log('getStudent',this.studentData);
                    });
            },
            getSchoolName(){
                var form = new FormData();
                form.set("type", "20");
                axios
                    .post("modules/assignMission/prodb_assignment_data.php", form)
                    .then(e => {
                        this.getSchoolName = e.data;
                        console.log('getSchoolName',this.getSchoolName);
                    });
            }
        },
    });
</script>
</html>
