<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0,user-scalable=0"">
    <link href=" https://fonts.googleapis.com/css?family=Francois+One&display=swap" rel="stylesheet">
    <script src="https://d3js.org/d3.v5.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js"
        integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./css/billboard.css">
    <script src="./js/billboard.js"></script>
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
            opacity: 0.85;
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
                    <p class="btn">더보기</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script type="application/javascript">
var app = new Vue({
            el: '#app',
            data: {
                containers: null,
                nickname: '',
                profileId: '',
            },
            created: function () {
                // `this` 는 vm 인스턴스를 가리킵니다.
                this.containers = [{
                        title: '레식 전적 검색',
                        articles: [
                            '아시아 계정의 닉네임을 검색',
                            '나의 랭킹과 오퍼레이터별 정보를 확인',
                            '지속 관리로 오퍼레이터별 변화를 모니터링'
                        ],
                        more: true
                    },
                    {
                        title: '대회 정보',
                        articles: [
                            '레인보우식스 시즈 대회 정보',
                            '[ 06/19 19:00 XXCUP XX MATCH ]',
                            '[img] A TEAM VS B TEAM [img]',
                            '[img] A TEAM VS B TEAM [img]'
                        ],
                        more: true
                    },
                    {
                        title: '레식 뉴스',
                        articles: [
                            '레식 정보 가나다라마바사아차카타...',
                            '레식 정보 가나다라마바사아차카타...',
                            '레식 정보 가나다라마바사아차카타...',
                        ],
                        more: false
                    }
                ]
            },
            methods: {
                search: function () {
                    $('body #content').html('');
                    $('.search > .progress').css('display', 'block');
                    $.ajax({
                            method: "POST",
                            url: "api/get/id",
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
                        });
                },
                getProfile: function () {
                    $.ajax({
                            method: "POST",
                            url: "api/get/profile",
                            data: {
                                key: app.profileId,
                                isCache: 1
                            }
                        })
                        .done(function (data) {
                            console.log(data);
                            app.containers.push({
                                title: data.nickname,
                                articles: [data.level],
                                more: false
                            });
                            $('.search > .progress').css('display', 'none');
                        });
                },
                getRank: function () {
                    $.ajax({
                            method: "POST",
                            url: "api/get/rank",
                            data: {
                                key: app.profileId,
                                isCache: 1
                            }
                        })
                        .done(function (data) {
                            console.log(data);
                            app.containers.push({
                                title: "Rank" + data.rank,
                                articles: ["MMR " + data.mmr, "SEASON " + data.season],
                                more: true
                            });
                        });
                },
                getOperators: function () {
                    $.ajax({
                            method: "POST",
                            url: "api/get/operators",
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
                                    articles: [operator.roundwon, operator.roundlost, operator.kills, operator.death, operator.timeplayed],
                                    more: true
                                });
                            });
                        });
                      }
                  }
                })
</script>