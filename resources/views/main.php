<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.73">
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
        flex-direction: row;
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
       height: 3em;
       width: 100%;
       background-color: #292c33;
       border-radius: 15px;
       opacity: 0.65;
       border: none;
       color: #f5f5f5;
      }
      .search input {
       background-color: #292c33;
       width: 90%;
       height: 80%;
       margin : 0.3em;
       border-radius: 15px;
       opacity: 0.65;
       border: none;
       color: #f5f5f5;
       font-size: 1em;
      }
      .title{
        color: #fbc900;
      }
    </style>
  </head>
  <body>
    <div class='container'>
        <input id="search" class="search" type="text" name="nickname" value="" placeholder="Search...">
    </div>
    <div id="content"></div>
  </body>
</html>

<script>
let profileId = null;

$('#search').on('change', ()=>{
  search($('#search').val());
})

function search(nickname) {
  $('body #content').html('');
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
      dom = "<div class='container'> \
      <div class='overview'> \
      <span class='title'>[ "+ data.nickname +" ]</span><br> \
      Level "+ data.level +"<br> \
      </div> \
    </div>";
    break;
    case 1:
      dom = "<div class='container'> \
      <div class='overview'> \
      <span class='title'>[ Rank "+ data.rank +" ]</span><br> \
      MMR "+ data.mmr +"<br> \
      SEASON "+ data.season +"<br> \
      </div> \
    </div>";
    break;
    case 2:
      dom = "<div class='container'> \
      <div class='overview'> \
      <span class='title'>[ "+ data.operator +" ]</span><br> \
      Win "+ data.roundwon +"<br> \
      Lost "+ data.roundlost +"<br> \
      Kill "+ data.kills +"<br> \
      Death "+ data.death +"<br> \
      TimePlayed "+ data.timeplayed +"<br> \
      </div> \
    </div>";
    break;
  }
    $('body #content').append(dom);
}
</script>