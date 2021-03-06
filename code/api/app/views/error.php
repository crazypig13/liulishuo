<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title ng-bind="title">TDS</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <link rel="stylesheet" href="//cdn.bootcss.com/weui/0.4.3/style/weui.min.css">
    <link rel="stylesheet" href="//cdn.bootcss.com/jquery-weui/0.8.0/css/jquery-weui.min.css">
</head>
<body>

<div class="weui_msg">
    <div class="weui_icon_area"><i class="weui_icon_warn weui_icon_msg"></i></div>
    <div class="weui_text_area">
        <h2 class="weui_msg_title">登录失败</h2>
        <p class="weui_msg_desc"><?=$error?></p>
    </div>
    <div class="weui_opr_area">
        <p class="weui_btn_area">
            <a href="#" id="close" class="weui_btn weui_btn_default">关闭页面</a>
        </p>
    </div>
</div>

<script src="//cdn.bootcss.com/jquery/1.11.0/jquery.min.js"></script>
<script src="//cdn.bootcss.com/jquery-weui/0.8.0/js/jquery-weui.min.js"></script>
<script src="//res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>

<script>
    $(function(){
        $('#close').click(function(){
            wx.closeWindow();
        })
    });
</script>

</body>
</html>