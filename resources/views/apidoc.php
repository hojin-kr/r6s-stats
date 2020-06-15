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
<div>
<h5>알림</h5>
<p>cache : 1 캐시에서 가져옴 (없으면 갱신)</p>
<p>cache : 0 갱신</p>
<hr>
</div>
    <div class="api"> 
        <p class="url">/get/id/</p>
        <p>닉네임으로 profile_id 조회</p>
        <div class="form-inline">
            <div class="form-group">
                <input class="form-control param" type="text" name="name" placeholder="name">
                <input class="form-control param-2" type="text" name="cache" placeholder="cache">
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
                <input class="form-control param-2" type="text" name="cache" placeholder="cache">
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
    <div class="api"> 
        <p class="url">/get/rank/list/</p>
        <p>유저 랭크 리스트 조회</p>
        <small>DB에 저장된 유저의 랭크 리스트</small><br>
        <small>Timestamp를 기준으로 원하는 단위로 (주, 월) 조회가능</small><br>
        <small>시즌별로 날짜를 스태틱하게 정의하고 날짜를 기준으로 시즌을 구분하여 가져올 수 있을 것</small><br>
        <small>Tip. 현재 Timestamp : <?php echo time();?> </small><br>
        <small>Tip. 단순하게 start는 0 or 음수 end는 현재로 파라미터 전달하면 전체 데이터 조회 </small>
        <div class="form-inline">
            <div class="form-group">
                <input class="form-control param" type="text" placeholder="profile_id">
                <input class="form-control param-2" type="text" placeholder="start timestamp">
                <input class="form-control param-3" type="text" placeholder="end timestamp">
            </div>
            <button type="button" class="btn btn-success req">요청</button>
        </div>
        <p>response</p>
        <pre><div class="result"></div></pre>
    </div>
    <div class="api"> 
        <p class="url">/get/operators/list/</p>
        <p>유저 오퍼레이터 리스트 조회</p>
        <small>DB에 저장된 유저의 오퍼레이터 리스트</small><br>
        <small>Timestamp를 기준으로 원하는 단위로 (주, 월) 조회가능</small><br>
        <small>시즌별로 날짜를 스태틱하게 정의하고 날짜를 기준으로 시즌을 구분하여 가져올 수 있을 것</small><br>
        <small>Tip. 현재 Timestamp : <?php echo time();?> </small><br>
        <small>Tip. 단순하게 start는 0 or 음수 end는 현재로 파라미터 전달하면 전체 데이터 조회 </small>
        <div class="form-inline">
            <div class="form-group">
                <input class="form-control param" type="text" placeholder="profile_id">
                <input class="form-control param-2" type="text" placeholder="start timestamp">
                <input class="form-control param-3" type="text" placeholder="end timestamp">
            </div>
            <button type="button" class="btn btn-success req">요청</button>
        </div>
        <p>response</p>
        <pre><div class="result"></div></pre>
    </div>
    <div class="api"> 
        <p class="url">/get/season/all/</p>
        <p>유저 전체시즌 정보</p>
        <small>/get/id/를 호출하면 백그라운드에서 전체 시즌 정보를 갱신</small><br>
        <small>정보가 없으면 0 반환</small><br>
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
    let BASE = '/api';        

    $('.req').on('click', (event)=>{ 
        let dom = $(event.target).parent().parent();
        let param = new Array();
        param[0] = dom.find('.param').val() + '/';
        param[1] = ((dom.find('.param-2').val() !== undefined) ? dom.find('.param-2').val() + '/' : '');
        param[2] = ((dom.find('.param-3').val() !== undefined) ? dom.find('.param-3').val() + '/': '');
        let url = dom.find('.url').text() + param[0] + param[1] + param[2];
        console.log(url);
        request(url, dom);
    })

    $('.req-2').on('click', (event)=>{ 
        let dom = $(event.target).parent().parent();
        $.ajax({
                method: "POST",
                url: BASE + dom.find('.url').text(),
                data: { key: dom.find('.param').val(), isCache: dom.find('.param-2').val() }
                })
                .done(function( result ) {
                    let test = JSON.stringify(result);
                    dom.find('.result').html(test);
                });
    })

    function request(url, dom) {
        dom.find('.result').html('');
        $.ajax({url: BASE + url, success: function(result){
        let test = JSON.stringify(result);
        dom.find('.result').html(test);
    }});
    }
</script>
</body>
</html>