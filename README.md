# liulishuo

## demo
[demo地址](http://121.42.45.187/site/admin/mt/#/login)
测试帐户：
test1@ff.com ~ test5@ff.com
密码全部为 123456

## 项目说明
代码为前后端分离，前端为angularjs，后端为php

## 后端
+ 工程路径: code/api
+ 路由动态配置，如 /api/app/user/info，将调用api下app/controllers/UserController.php中的actionInfo()作为控制器
+ 主要分为三个模块，
>* /api/app/user/* 维护用户登录/退出、注册和基本信息
>* /api/app/buddy/* 维护好友关系，和检查变更
>* /api/app/message/* 维护聊天记录
+ 事件处理，用于逻辑功能解耦
>* 在控制器中发送事件，如MessageController.php中创建聊天消息完成后，发送 EVENT_MESSAGE_SENDED 事件
>* 在 common/custom/DefaultAppEvent.php 中监听事件，并调用相应逻辑处理，如接收 EVENT_MESSAGE_SENDED 事件后，处理自动加好友、刷新未读消息数等。

## 前端
+ 工程路径：code/web/site/project
+ 可在router.js中看到前端代码地图，分为 /login, /register, /app
其中 /app 中根据hash路由动态加载子模块，如登录后的好友列表 #/app/user/list，将加载project下的user/controller.js（控制器）和user/views/list.html（视图）
+ 事件处理，前端/app模块将轮询调用后端接口/api/app/buddy/check-new检查是否有变更，子模块 /app/user/info、 /app/user/list、/app/user/history将在载入时添加listener，监听这些变更中的自己需要的部分并刷新数据。这些子模块的listener将在子模块切换时得到清除。
