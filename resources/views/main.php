<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0,user-scalable=0"">
    <link href="https://fonts.googleapis.com/css?family=Francois+One&display=swap" rel="stylesheet">
    <script src="https://d3js.org/d3.v5.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
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
        background-color: rgba(41,44,51,.25);
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
        from { margin-bottom: 80%; opacity: 0.3; }
        to { margin-bottom: 0%; opacity: 1; }
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
      .search > input{
       width: 100%;
       height: 100%;
       background-color: #292c33;
       border: none;
       font-size: 1em;
       color: #f5f5f5;
      }
      .search > .progress {
        position: absolute;
        right: 45%;
        top: 40%;
        display: none;
      }
      .title{
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
    <div class='container search'>
        <input id="search" type="text" name="nickname" value="" placeholder="Search..."><span class="progress">Search...</span>
    </div>
    <div id='content'>
      <div class='container intro'>
        <div class='wrap'>
          <p class='title'>레식 전적 검색</p>
          <p>아시아 계정의 닉네임을 검색</p>
          <p>나의 랭킹과 오퍼레이터별 정보를 확인</p>
          <p>지속 관리로 오퍼레이터별 변화를 모니터링</p>
        </div>
        <div class="more">
            <hr>
            <p class="btn">더보기</p>
        </div>
      </div>
      <div class='container'>
        <div class='wrap'>
          <p class='title'>대회 정보</p>
          <p>레인보우식스 시즈 대회 정보</p>
          <p><a href="https://google.com">레식 대회 정보 가나다라마바사아차카타...</a></p>
          <p class="title">[ 06/19 19:00 XXCUP XX MATCH ]</p>
          <p>[img] A TEAM VS B TEAM [img]</p>
          <p>[img] A TEAM VS B TEAM [img]</p>
          <p class="title">[ 06/22 19:00 XXCUP XX MATCH ]</p>
          <p>[img] A TEAM VS B TEAM [img]</p>
        </div>
        <div class="more">
            <hr>
            <p class="btn">더보기</p>
        </div>
      </div>
      <div class='container'>
        <div class='wrap'>
          <p class='title'>메인 레식 정보</p>
          <p>레인보우식스 시즈 최신 정보</p>
          <p><a href="https://google.com">레식 정보 가나다라마바사아차카타...</a></p>
          <p><a href="https://google.com">레식 정보 가나다라마바사아차카타...</a></p>
          <p><a href="https://google.com">레식 정보 가나다라마바사아차카타...</a></p>
          <p><a href="https://google.com">레식 정보 가나다라마바사아차카타...</a></p>
        </div>
      </div>
    </div>
  </body>
</html>

<script>
let profileId = null;

$('#search').on('change', ()=>{
  search($('#search').val());
})

// 더보기 버튼
$('.more > .btn').on('click', ()=>{
  alert("more");
})

function search(nickname) {
  $('body #content').html('');
  $('.search > .progress').css('display', 'block');
    $.ajax({
            method: "POST",
            url: "api/get/id",
            data: {
                key: nickname,
                isCache: 1
            }
        })
        .done(function(res) {
          console.log(res);
          getProfile(res.profile_id);
          getRank(res.profile_id);
          getOperators(res.profile_id);
        });
}

function getProfile(id) {
    
    $.ajax({
            method: "POST",
            url: "api/get/profile",
            data: {
                key: id,
                isCache: 1
            }
        })
        .done(function(res) {
            console.log(res);
            addContainer(res, 0);
        });
}

function getRank(id) {
    $.ajax({
            method: "POST",
            url: "api/get/rank",
            data: {
                key: id,
                isCache: 1
            }
        })
        .done(function(res) {
            console.log(res);
            addContainer(res, 1);
        });
}

function getOperators(id) {
    $.ajax({
            method: "POST",
            url: "api/get/operators",
            data: {
                key: id,
                isCache: 1
            }
        })
        .done(function(res) {
            console.log(res);
            res.sort(function (a, b) {
                        return b.timeplayed - a.timeplayed;
                      });
            res.forEach((operator)=>{
              addContainer(operator, 2);
            })
        });
}

function addContainer(data, type) {
  let dom = '';
  switch(type) {
    case 0:
      dom = "<div class='container profile'> \
      <div class='wrap'> \
      <span class='title'>[ "+ data.nickname +" ]</span><br> \
      Level "+ data.level +"<br> \
      </div> \
    </div>";
    break;
    case 1:
      dom = "<div class='container rank'> \
      <div class='wrap'> \
      <span class='title'>[ Rank "+ data.rank +" ]</span><br> \
      MMR "+ data.mmr +"<br> \
      SEASON "+ data.season +"<br> \
      </div> \
      <div class='more'> \
            <hr> \
            <p class='btn'>더보기</p> \
        </div> \
    </div>";
    break;
    case 2:
      dom = "<div class='container operators'> \
      <div class='wrap'> \
      <span class='title'>[ "+ data.operator +" ]</span><br> \
      Win "+ data.roundwon +"<br> \
      Lost "+ data.roundlost +"<br> \
      Kill "+ data.kills +"<br> \
      Death "+ data.death +"<br> \
      TimePlayed "+ data.timeplayed +"<br> \
      </div> \
      <div class='more'> \
            <hr> \
            <p class='btn'>더보기</p> \
        </div> \
    </div>";
    break;
  }
  $('.search > .progress').css('display', 'none');
    $('body #content').append(dom);
}
</script>