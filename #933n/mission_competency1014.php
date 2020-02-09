<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>
    <script src="https://unpkg.com/vue-chartjs/dist/vue-chartjs.min.js"></script>
    <style>
        .table {
            border: 1px solid #6c757d;
            border-radius: .25rem;
        }
        .el-message-box{
            width:auto;
        }
    </style>
</head>

<body>
    <div id="competency" class="content2-Box">
        <template v-if="main=='unit'">
            <div class="title01">班級學習狀態</div>
            <h3>已完成全部任務人數:{{mission_user_finish.length}} 。全班人數:{{mission_user_all.length}}</h3>
            <el-tabs>
                <el-tab-pane :label="unit.unit_nm" v-for="unit of score">
                    <el-table :data="dscs" class="table">
                        <el-table-column :label="unit.unit_nm" align="center">
                            <el-table-column prop="name" label="素養" min-width="200">
                            </el-table-column>
                            <el-table-column label="平均表現" min-width="150">
                                <template v-slot="scope">
                                    <span v-if="unit.avg[scope.$index] >= 0"
                                        :style="{color: colorStandard(unit.avg[scope.$index])}">
                                        <span>{{unit.avg[scope.$index].toFixed(3)}}</span>
                                        ({{toStandard(unit.avg[scope.$index])}})
                                    </span>
                                    <span v-else>無</span>
                                </template>
                            </el-table-column>
                            <el-table-column v-for="(standard, key) of standards" :key="key" :label="standard"
                                min-width="150">
                                <template v-slot="scope">
                                    <template v-if="isNaN(unit.avg[scope.$index])">無</template>
                                    <el-popover title="學生名單：" trigger="hover" placement="top"
                                        v-else-if="unit[key][scope.$index].length >= 0">
                                        <ul><li v-for="uname of unit[key][scope.$index]">{{uname}}</li></ul>
                                        <div slot="reference" class="name-wrapper">
                                            {{unit[key][scope.$index].length}}
                                            ({{Math.round(unit[key][scope.$index].length*1000/mission_user_all.length)/10}}%)
                                        </div>
                                    </el-popover>
                                    <template v-else>0</template>
                                </template>
                            </el-table-column>
                        </el-table-column>
                    </el-table>
                </el-tab-pane>
            </el-tabs>
        </template>
        <template v-if="main=='user'">
            <div class="title01">學生素養報告</div>
            <h3>已完成全部任務人數:{{mission_user_finish.length}} 。全班人數:{{mission_user_all.length}}</h3>
            <el-tabs>
                <el-tab-pane :label="key" v-for="(unit, key) of score">
                    <el-table :data="dscs" class="table">
                        <el-table-column prop="name" label="素養" min-width="200">
                        </el-table-column>
                        <el-table-column v-for="user of unit" :label="user.uname" min-width="150">
                            <template v-slot="scope">
                                <el-tooltip class="item" effect="light" :content="scope.row.name" placement="top-start">
                                    <span v-if="user.avg[scope.$index] >= 0"
                                        :style="{color: colorStandard(user.avg[scope.$index])}">
                                        {{user.avg[scope.$index].toFixed(3)}}
                                        ({{toStandard(user.avg[scope.$index])}})
                                    </span>
                                    <span v-else>無</span>
                                </el-tooltip>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-tab-pane>
            </el-tabs>
        </template>
        <template v-if="main=='self'">
            <div class="title01" v-if="score.some(v=>v.unit_id=='total-0')">個人素養狀態</div>
            <h3>已完成全部任務人數:{{mission_user_finish.length}} 。全班人數:{{mission_user_all.length}}</h3>
            <el-card shadow="hover" class="box-card" > 
            <!-- <el-divider>素養雷達圖</el-divider> -->
            <template>
                <el-button type="warning" @click="open">點擊雷達圖說明</el-button>
            </template>

                <el-carousel indicator-position="outside" :interval="5000" height="500" :autoplay="false">
                <el-carousel-item v-for="(range, index) of chartRanges" :key="index"> 
                        <radar :labels="dscs" :datas="score" :range="range" :title-title="mission_data['title'][index]" :mission_avg="mission_data" v-if="score.filter(v=>v.unit_id=='total-0')"></radar>
                    </el-carousel-item>
                </el-carousel>
            </el-card>
            <el-divider>單元素養對照表</el-divider>
            <el-table :data="dscs" class="table" v-if="score.length != 0" show-summary :summary-method="getSummaries">
                <el-table-column prop="name" label="素養" min-width="200" fixed>
                </el-table-column>
                <el-table-column :label="unit.unit_nm" v-for="(unit, key) of score" min-width="150">
                    <template v-slot="scope">
                        <span v-if="unit.avg[scope.$index] >= 0"
                            :style="{color: colorStandard(unit.avg[scope.$index])}">
                            {{unit.avg[scope.$index].toFixed(3)}}
                            ({{toStandard(unit.avg[scope.$index])}})
                        </span>
                        <span v-else>
                            無
                        </span>
                    </template>
                </el-table-column>
            </el-table>
        </template>
    </div>
</body>
<script>
    function formatToForm(object) {
        let form = new FormData();
        for (let key in object) {
            form.set(key, object[key]);
        }
        return form;
    }
    var radar = Vue.extend({
        extends: VueChartJs.Radar,
        props: ["labels", "datas", "range", "mission_avg", "titleTitle"],
        data() {
            return {
                chartData: {
                    labels: this.labels.map(v => v.name).slice(...this.range),
                    datasets: [{
                        label:"班平均",
                        borderDash:[10,5],
                        borderWidth: 2.5,
                        borderColor:"#00a574",
                        backgroundColor: "transparent",
                        pointBorderColor:"transparent",
                        pointBackgroundColor:"transparent",
                        pointHoverBackgroundColor:"#00a574",
                        data: this.mission_avg[4].avg.slice(...this.range)
                    },{
                        label:"個人作答分佈",
                        borderWidth: 2,
                        borderColor:"#ffb600",
                        backgroundColor: "#ffb60050",
                        pointBorderColor:"transparent",
                        pointBackgroundColor:"transparent",
                        pointHoverBackgroundColor:"#ffb600",
                        data: this.datas.find(v=>v.unit_id=='total-0').avg.slice(...this.range)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, 
                    legend: { 
                        display: true,
                        position:'bottom' 
                    },
                    scale: {
                        pointLabels: { 
                            fontSize: 16 
                        },
                        ticks: {
                            beginAtZero: true,
                            min: 0,
                            max: 1,
                            stepSize: 0.2
                        }
                    },
                    title: {
                        display: true,
                        text: this.titleTitle,
                        position: 'bottom',
                        fontSize:20,
                        fontFamily:'Microsoft JhengHei'
                    }
                }
            };
        },
        watch: {
            labels: {
                handler(newName, oldName) {
                    this.chartData.labels = this.labels.map(v => v.name).slice(...this.range);
                    this.$data._chart.update();
                },
                deep: true
            },
            datas: {
                handler(newName, oldName) {
                    this.chartData.datasets[0].data = this.datas
                    .find(v=>v.unit_id=='total-0').avg
                    .slice(...this.range);
                    this.$data._chart.update()
                },
                deep: true
            }
        },
        mounted() {
            setTimeout(() => {
                this.renderChart(this.chartData, this.options);
                this.$data._chart.update()
            }, 0);
        },
      
    });
    var competency = new Vue({
        el: "#competency",
        components: {
            radar
        },
        data: {
            main: null,
            mission_sn: 0,
            mission_user_finish: 0,
            mission_user_all: [],
            standards: { top: "精熟 (人數 / 完成率)", mean: "基礎 (人數 / 完成率)", bottom: "待加強 (人數 / 完成率)" },
            genres: "",
            dscs: [],
            score: [],
            mission_data:null,
            where:''
        },
        computed: {
            chartRanges(){
                switch(this.genres){
                    case 'cps':
                        return [[0,3], [3,7], [7,19]];
                    case 'gc':
                    return [[0,4]];
                    case 'sc':
                        return [];
                }
            }
        },
        created() {
            const _this = this;                   
            let url = new URL(location.href);
            this.main = url.searchParams.get('main');
            this.mission_sn = url.searchParams.get('mission_sn'); 
            if (this.mission_sn) {
                $.LoadingOverlay("show");
                axios.post("modules/assignMission/assignment/competencyScore.php", formatToForm({ mission_sn: this.mission_sn }))
                    .then(response => {
                        _this.mission_data=response.data;                                          
                         // $.LoadingOverlay("hide");
                    });
                axios.post("modules/assignMission/assignment/competencyScore.php", formatToForm({ mission_sn: this.mission_sn, main: this.main }))
                    .then(response => {

                        this.mission_user_finish = response.data[0];
                        this.mission_user_all = response.data[1];
                        this.genres = response.data[2];
                        this.dscs = response.data[3];
                        this.score = response.data[4];
                        // console.log(this.genres);

                        if(this.genres == 'cps'){
                            this.mission_data.title = ['問題解決核心能力雷達圖', '團隊合作核心能力雷達圖', '合作問題解決能力雷達圖']; 
                        }else if(this.genres == 'gc'){
                            this.mission_data.title = ['全球素養雷達圖'];
                        }
                         
                        $.LoadingOverlay("hide");
    
                    });
            }
        },
        methods: {
            open() {
                this.$alert('<img src="./images/mission_competency.png">', '雷達圖解讀說明', {
                confirmButtonText: '确定',
                dangerouslyUseHTMLString: true
                });
            },
            avgFormatter(row, column, cellValue, index) {
                return cellValue >= 0 ? `${cellValue.toFixed(3)} (${this.toStandard(cellValue)})`: '無';
            },
            toStandard(value) {
                if (value < 0) {
                    return "無";
                } else if (value < 0.5) {
                    return "待加強";
                } else if (value < 0.8) {
                    return "基礎";
                } else {
                    return "精熟"
                }
            },
            colorStandard(value) {
                if (value < 0) {
                    return 'gray';
                } else if (value < 0.5) {
                    return '#c53737';
                } else if (value < 0.8) {
                    return '#ffa500';
                } else {
                    return '#2fa474';
                }
            },
            tableData() {
                let table = {};
                for (let unit of this.score) {
                    table[unit.unit_id] = this.dscs.map((dsc, key) => {
                        return {
                            'dsc': dsc.name,
                            'avg': unit.avg[key],
                            'top': unit.top[key],
                            'mean': unit.mean[key],
                            'bottom': unit.bottom[key]
                        }
                    });
                }
                return table;
            },
            getSummaries() {
                return ["單元平均表現", ...Object.values(this.score).map(unit => unit.grandMean>=0?`${unit.grandMean} (${this.toStandard(unit.grandMean)})`:"無")];
            }
        },
    })
</script>

</html>