<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>
    <script src="https://unpkg.com/vue-chartjs/dist/vue-chartjs.min.js"></script>
    <style>
        @media (max-width: 768px) {
            img{
                width:90%;

            }
         }

        .table {
            border: 1px solid #6c757d;
            border-radius: .25rem;
        }
        .el-message-box{
            width:auto;
        }
        .el-carousel__arrow:hover {
            background-color: #FDCD5390;
        }
        .el-carousel__arrow {
            border: none;
            outline: 0;
            padding: 0;
            margin: 0;
            height: 40px;
            width: 40px;
            cursor: pointer;
            -webkit-transition: .3s;
             transition: .3s;
            border-radius: 50%;
            background-color: rgba(31,45,61,0);
            color: #0b344b;
            font-size: 30px;
            position: absolute;
            top: 13%;
            z-index: 10;
            -webkit-transform: translateY(-50%);
            transform: translateY(-50%);
            text-align: center;
            font-size:1rem;
            }
    </style>
</head>

<body>

    <div id="competency_allclass" class="content2-Box">
        <template>
        <p v-if='bshowSuggest' class='suggestNote'>建議橫放螢幕以獲得最佳使用體驗！</p>
            <div class="title01">班級素養狀態</div>
            <h3>已完成全部任務人數: 。全班人數:{{class_size}}人</h3>
            <el-card shadow="hover" class="box-card" >
            <!-- <el-divider>素養雷達圖</el-divider> -->
            <template>
                <el-button type="warning" @click="open">點擊雷達圖說明</el-button>
            </template>

            </el-card>

        </template>
    </div>
    
</body>
<script>

    var competency = new Vue({
        el: "#competency_allclass",
        
        data: {
            bshowSuggest: false,//螢幕橫放建議文字
            iPadClientWidth: 768,
            class_size:0,
            mission_all_dsc:[]
           
        },
        computed: {
           
        },
        created() {

            document.addEventListener("resize", this.showSuggestion);
            this.showSuggestion();

            const _this = this;
            let url = new URL(location.href);
            _this.main = url.searchParams.get('main');
            _this.class_id = url.searchParams.get('c_id');
            _this.class = url.searchParams.get('class');
            _this.grade = url.searchParams.get('grade');
            _this.mission_sn = url.searchParams.get('mission_sn');
          $.LoadingOverlay("show");
            if (_this.mission_sn) {//全班人數
                    var form = new FormData();
                    form.set("type", "2");
                    form.set("class_id", _this.class_id);
                    form.set("mission_sn", _this.mission_sn);
                    form.set("class", _this.class);
                    form.set("grade", _this.grade);
                axios
                    .post("modules/assignMission/assignment/competencyListAll.php", form)
                    .then(response => {
                        this.class_size = response.data;
                    }).finally(e => {
                        $.LoadingOverlay("hide");
                    });

            };
            if (_this.mission_sn) {
               
                    var form = new FormData();
                    form.set("type", "3");
                    form.set("class_id", _this.class_id);
                    form.set("mission_sn", _this.mission_sn);
                    form.set("class", _this.class);
                    form.set("grade", _this.grade);
                axios
                    .post("modules/assignMission/assignment/competencyListAll.php", form)
                    .then(response => {
                        this.mission_all_dsc = response.data;
                        console.log(response);
                    }).finally(e => {
                        
                    });

            };
            
            $.LoadingOverlay("hide");

        },destroyed(){
			document.removeEventListener("resize", this.showSuggestion);
		},
        methods: {
            //判斷螢幕大小 顯示提示字樣
            showSuggestion(){

				if(document.body.clientWidth <= this.iPadClientWidth && document.body.clientWidth < document.body.clientHeight){
                    this.bshowSuggest = true;

				}else{
					this.bshowSuggest = false;
                }

			},
            open() {
                //雷達圖說明彈出按鈕
                this.$alert('<img  src="./images/mission_competency.png" >', '雷達圖解讀說明', {
                confirmButtonText: '確定',
                dangerouslyUseHTMLString: true,
                });
            },
        },
    })
</script>

</html>
