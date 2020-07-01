<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0,user-scalable=0"">
    <link href="https://fonts.googleapis.com/css?family=Francois+One&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.js"
        integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
    <title>r6s</title>
    <style media="screen">
        body {
            color: #f5f5f5;
            background-color: #1a1d22;
            font-family: 'Francois One';
        }

        .container {
            padding: 0.5em;
            margin: 0.5em;
            background-color: rgba(41, 44, 51, .25);
            border-radius: 15px;
            /* opacity: 0.85; */
            display: flex;
            flex-direction: column;
            justify-content: space-between;

        }

        /* container 애니메이션 */
        .container {
            animation-name: containerAppend;
            animation-duration: 1.5s;
            /* animation-iteration-count: infinite; */
        }

        @keyframes containerAppend {
            from {
                margin-bottom: 80%;
                opacity: 0.3;
            }

            to {
                margin-bottom: 0%;
                opacity: 1;
            }
        }

        .container .nickname {
            color: #fbc900;
        }

        .search {
            height: 2em;
            background-color: #292c33;
            border-radius: 15px;
            opacity: 0.65;
            border: none;
            color: #f5f5f5;
        }

        .search>input {
            width: 100%;
            height: 100%;
            background-color: #292c33;
            border: none;
            font-size: 1em;
            color: #f5f5f5;
        }

        .search>.progress {
            position: absolute;
            right: 45%;
            top: 40%;
            display: none;
        }

        .title {
            color: #fbc900;
        }

        hr {
            border-color: #f5f5f5;
        }

        .more {
            color: #f5f5f5;
        }

        p {
            margin-block-start: 0.3em;
            margin-block-end: 0.3em;
        }

        a {
            text-decoration: none;
            color: inherit;
        }
        .bb-tooltip-container {
            color: black;
        }
    </style>
</head>

<body>
    <div id="app">
        <div class='container search'>
            <input type="text" name="nickname" placeholder="Search..." v-on:keyup.13="search" v-model="nickname"><span class="progress">Search...</span>
        </div>
        <div id='content'>
            <div class='container' v-for="container in containers">
                <div class='wrap'>
                    <p class='title'>{{container.title}}</p>
                    <p v-for="article in container.articles">{{article}}</p>
                </div>
                <div class="more" v-if="container.more != false">
                    <hr>
                    <div v-bind:id="container.title" v-on:click="more">more</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<!-- Step 1) Load D3.js -->
<script src="https://d3js.org/d3.v5.min.js"></script>
<!-- Step 2) Load billboard.js with style -->
<script src="./js/billboard.js"></script>
<!-- Load with base style -->
<link rel="stylesheet" href="./js/billboard.css">
<!-- Or load different theme style -->
<link rel="stylesheet" href="./js/insight.min.css">
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script type="application/javascript">
const host = "http://ec2-13-209-98-115.ap-northeast-2.compute.amazonaws.com/";

var app = new Vue({
    el: '#app',
    data: {
        containers: [],
        nickname: 'LE16_',
        profileId: '',
        operatorsList: null,
        ranksList: null,
        seasonAllList: null,
    },
    mounted: function () {
        this.search();
    },
    methods: {
        search: function () {
            $('body #content').html('');
            $('.search > .progress').css('display', 'block');
            $.ajax({
                    method: "POST",
                    url: host + "api/get/id",
                    data: {
                        key: this.nickname,
                        isCache: 1
                    }
                })
                .done(function (data) {
                    app.containers = [];
                    app.profileId = data.profile_id;
                    app.getProfile();
                    app.getRank();
                    app.getOperators();
                    app.getOperatorsList();
                    app.getRanksList();
                    app.getSeasonAll();
                });
        },
        getProfile: function () {
            $.ajax({
                    method: "POST",
                    url: host + "api/get/profile",
                    data: {
                        key: app.profileId,
                        isCache: 1
                    }
                })
                .done(function (data) {
                    console.log(data);
                    app.containers.push({
                        title: "PROFILE",
                        articles: [data.nickname, "LEVEL " + data.level],
                        more: true
                    });
                    $('.search > .progress').css('display', 'none');
                });
        },
        getRank: function () {
            $.ajax({
                    method: "POST",
                    url: host + "api/get/rank",
                    data: {
                        key: app.profileId,
                        isCache: 1
                    }
                })
                .done(function (data) {
                    console.log(data);
                    app.containers.push({
                        title: 'RANK',
                        articles: [data.rank, "MMR " + data.mmr, "SEASON " + data.season],
                        more: true
                    });
                });
        },
        getOperators: function () {
            $.ajax({
                    method: "POST",
                    url: host + "api/get/operators",
                    data: {
                        key: app.profileId,
                        isCache: 1
                    }
                })
                .done(function (data) {
                    console.log(data);
                    data.sort(function (a, b) {
                        return b.timeplayed - a.timeplayed;
                    });
                    data.forEach((operator) => {
                        app.containers.push({
                            title: operator.operator,
                            articles: ["RoundWon " + operator.roundwon, "RoundLost " + operator.roundlost, "K/D " + (operator.kills / operator.death).toFixed(2), "PlayTime " + (operator.timeplayed / 3600).toFixed(0) + " hour"],
                            more: true
                        });
                    });
                });
        },
        getOperatorsList: function () {
            $.ajax({
                    method: "POST",
                    url: host + "api/get/operators/list",
                    data: {
                        key: app.profileId,
                        start_timestamp: 0,
                        end_timestamp: Date.now(),
                        isCache: 1
                    }
                })
                .done(function (data) {
                    app.operatorsList = data;
                });
        },
        getRanksList: function () {
            $.ajax({
                    method: "POST",
                    url: host + "api/get/rank/list",
                    data: {
                        key: app.profileId,
                        start_timestamp: 0,
                        end_timestamp: Date.now(),
                        isCache: 1
                    }
                })
                .done(function (data) {
                    app.ranksList = data;
                });
        },
        getSeasonAll: function () {
            $.ajax({
                    method: "POST",
                    url: host + "api/get/season/all",
                    data: {
                        key: app.profileId
                    }
                })
                .done(function (data) {
                    app.seasonAllList = JSON.parse(data);
                });
        },
        more: function (event) {
            let title = event.target.id;
            let column = [];
            let mmr = ['MMR'];
            let kd = ['K/D'];
            let wl = ['W/L'];
            let seasons = ['Black Ice', 'Dust Line', 'Skull Rain', 'Red Crow', 'Velvet Shell', 'Health', 'Blood Orchid', 
        'White Noise', 'Chimera', 'Para Bellum', 'Grim Sky', 'Wind Bastion', 'Burnt Horizon', 'Phantom Sight'
        ,'Ember Rise', 'Shifting Tides', 'Void Edge'];
            switch(title){
                case 'RANK':
                app.ranksList.forEach((ranks) => {
                    let data = JSON.parse(ranks.data)['players'][ranks.profile_id];
                    if (mmr.length < 10) {
                        mmr.push(data.mmr);
                    }
                })
                column.push(mmr);
                var chart = bb.generate({
                    bindto: "#" + title,
                    data: {
                        type: "area-spline",
                        columns: column
                    }
                });
                    break;
                case 'PROFILE':
                    seasons.forEach((season)=>{
                        mmr.push(app.seasonAllList[season].mmr);
                    })
                    column.push(mmr);
                    var chart = bb.generate({
                    bindto: "#" + title,
                    data: {
                        type: "area-spline",
                        columns: column
                    },
                    axis: {
                    x: {
                    type: "category",
                    categories: seasons
                    }
                }
            });
                    break;
                default:
                app.operatorsList.forEach((operators) => {
                    operators.forEach((operator) => {
                        if (operator.operator == title) {
                            kd.push((operator['kills'] / operator['death']).toFixed(2));
                            wl.push((operator['roundwon'] / operator['roundlost']).toFixed(2));
                        }
                    })  
                })
                column.push(kd);
                column.push(wl);
                var chart = bb.generate({
                    bindto: "#" + title,
                    data: {
                        type: "area-spline",
                        columns: column
                    }
            });
                    break;
            }
        }
    }
})
</script>