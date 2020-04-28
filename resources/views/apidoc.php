<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <title>Document</title>
    <script src="https://code.jquery.com/jquery-3.5.0.min.js" integrity="sha256-xNzN2a4ltkB44Mc/Jz3pT4iU1cmeR0FkXs4pru/JxaQ=" crossorigin="anonymous"></script>
    <style>
    .api {
        margin: 1em;
        border-bottom: 1px solid black;
    }

    .api input {
        margin: 1em;    
    }

    .api pre {
        height: 3em;
    }
    </style>
</head>
<body>
<div class="container">
<h1>api</h1>
    <div class="api"> 
        <p class="url">/get/id/</p>
        <p>닉네임으로 profile_id 조회</p>
        <div class="form-inline">
            <div class="form-group">
                <input class="form-control param" type="text" placeholder="name">
            </div>
            <button type="button" class="btn btn-success req">요청</button>
        </div>
        <p>response</p>
        <pre><div class="result"></div></pre>
    </div>
    <div class="api"> 
        <p class="url">/get/profile/</p>
        <p>기본 profile 정보 조회</p>
        <div class="form-inline">
            <div class="form-group">
                <input class="form-control param" type="text" placeholder="profile_id">
            </div>
            <button type="button" class="btn btn-success req">요청</button>
        </div>
        <p>response</p>
        <pre><div class="result"></div></pre>
    </div>
    <div class="api"> 
        <p class="url">/get/rank/</p>
        <p>기본 랭크 정보 조회</p>
        <div class="form-inline">
            <div class="form-group">
                <input class="form-control param" type="text" placeholder="profile_id">
            </div>
            <button type="button" class="btn btn-success req">요청</button>
        </div>
        <p>response</p>
        <pre><div class="result"></div></pre>
    </div>
    <div class="api"> 
        <p class="url">/get/operators/</p>
        <p>유저 오퍼레이터별 정보</p>
        <div class="form-inline">
            <div class="form-group">
                <input class="form-control param" type="text" placeholder="profile_id">
            </div>
            <button type="button" class="btn btn-success req">요청</button>
        </div>
        <p>response</p>
        <pre><div class="result"></div></pre>
    </div>
</div>

    <script>
    let BASE = 'http://ec2-13-209-98-115.ap-northeast-2.compute.amazonaws.com/api';

    $('.req').on('click', (event)=>{
        let dom = $(event.target).parent().parent();
        let url = dom.find('.url').text() + dom.find('.param').val();
        console.log(url);
        request(url, dom);
    })

    function request(url, dom) {
        $.ajax({url: BASE + url, success: function(result){
        let test = JSON.stringify(result);
        dom.find('.result').html(test);
    }});
    }
</script>
</body>
</html>