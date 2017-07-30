<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登录</title>
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="/admin/bower_components/bootstrap/dist/css/bootstrap.css">
    <!-- Custom styles for this template -->
    <link href="/admin/style/login.css" rel="stylesheet">
</head>
<body class="login-background">
<img src="/admin/images/caicvlogo.jpg" class="caicvlogo" alt="">

<div class="panel-default panel-signin  panel  box-shadow">
    <div class="panel-heading"><strong>登录</strong></div>
    <div class="panel-body">
        <form class="form-signin" id="login" method="post">
            <div class="form-group">
                <label class="sr-only" for="name">用户名</label>

                <div class="input-group">
                    <div class="input-group-addon"><span class="glyphicon glyphicon-user"></span></div>
                    <input type="text" class="form-control " id="name" name="name" placeholder="用户名" required>
                </div>
            </div>
            <div class="form-group">
                <label class="sr-only" for="inputPassword">密码</label>

                <div class="input-group">
                    <div class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></div>
                    <input type="password" class="form-control" id="inputPassword" name="password" placeholder="密码"
                           required autofocus>
                </div>
            </div>

            <div class="form-forgetpwd"><input type="checkbox">记住密码
                <a target="_blank" href="javascript:void();" onclick="forgetPassword();" class="pwd-Forget">忘记登录密码</a>
                <img src="/admin/images/arrow.jpg" class="arrow" alt=""></div>
            <div class="form-group">
                <button class="btn btn-lg btn-primary btn-block" type="submit">登录</button>
            </div>
        </form>
    </div>
</div>
<!-- /container -->

<!-- Modal -->
<div class="modal fade bs-example-modal-sm" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">错误信息</h4>
            </div>
            <div class="modal-body">
                密码长度不能小于6位！
            </div>
        </div>
    </div>
</div>


<script type="application/javascript" src="/admin/bower_components/jquery/dist/jquery.min.js"></script>
<script type="application/javascript" src="/admin/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>


<script>
    var host = 'http://10.21.100.237/';


    function getHost() {
        var url = location.href;
        return (/localhost:/.test(url)) ? host : '/';
    }

    function getCorporation() {
        var url = location.href;

        if (/localhost:/.test(url)) {
            return 'demo';
        } else {
            var reg = /http:\/\/.*?\/(\w+)/;
            return (reg.exec(url) && reg.exec(url)[1]) || 'demo';
        }
    }

    /**
     * 忘记密码
     */
    function forgetPassword() {
        var url = getHost() + getCorporation() + '/site/findpwd';
        window.open(url);
    }

    /**
     * 用户登陆
     */
    $("#login").on('submit', function () {
        var name = $('#name').val();
        var password = $('#inputPassword').val();
        if ($('#inputPassword').val().length < 6) {
            $('#myModal').modal();
            return false;
        }
        $.ajax({
            url: getHost() + getCorporation() + "/site/login",
            type: 'post',
            dataType: 'json',
            data: {'username': name, 'password': password},
            success: function () {
                location.href = getHost() + getCorporation() + "/admin/index.html" + location.hash;
            },
            error: function (data) {
                var regx = /:\s+(.*?)$/;
                var rs = regx.exec(data.responseText);
                if (rs instanceof Array) {
                    alert(rs[1]);
                }
            }
        });
        return false;
    })
</script>

</body>
</html>