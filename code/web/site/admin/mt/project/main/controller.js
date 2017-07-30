'use strict';

define([
    'app',
    '../../lib/services/api',
    '../../lib/services/alert',
    '../../lib/services/showFilter',
    '../../lib/services/utils'
], function (md) {

    md.controller('AppController', ['$rootScope', '$scope','$stateParams','$location','$state','alertService','api','utils',
        function ($rootScope, $scope, $stateParams, $location, $state, alertService, api, utils) {
            //console.log('app controller');
            //console.log($stateParams);
            //console.log($location.path());
            //console.log($location.search());
            var vm = $scope.vm = {};
            var root = $rootScope.root = {}
            vm.time = (new Date()).getHours();
            var user_api = api.init('user');
            vm.logout = function () {
                user_api.logout(function (res) {
                    window.location = "#/login";
                    $rootScope.user = null;
                });
            };
            vm.alerts = alertService.get();

            var checkNew = function(){
                var check_api = api.raw('/api/app/buddy/check-new');
                check_api.get(function(data){
                    if(data['unread_all']){
                        root.new_message = 1;
                    } else {
                        root.new_message = 0;
                    }
                    _.each($rootScope.listeners, function(listener){
                        if(data[listener['name']]){
                            listener['func'](data[listener['name']]);
                        }
                    })
                    setTimeout(checkNew, 3000);
                }, function(){
                    setTimeout(checkNew, 3000);
                })
            };
            checkNew();

            user_api.info(function(data){
                vm.user = data;
                root.user = data;
                //root.admin_conf = data['conf']['admin'] || {};
            });

        }
    ]);

    md.controller('LocalLoginController', ['$rootScope', '$scope','$stateParams','$location','$state','alertService','api', 'utils',
        function ($rootScope, $scope, $stateParams, $location, $state, alertService, api, utils) {
            var vm = $scope.vm = {};
            var user_api = api.init('user');
            vm.req = {};

            vm.alerts = alertService.get();
            vm.login = function(){
                console.log(vm.req);
                user_api.local_login(vm.req, function(data){
                    console.log(data);
                    window.location = '#/app/user/list'
                })
            }
        }
    ]);

    md.controller('RegController', ['$rootScope', '$scope','$stateParams','$location','$state','alertService','api', 'utils',
        function ($rootScope, $scope, $stateParams, $location, $state, alertService, api, utils) {
            var vm = $scope.vm = {};
            var user_api = api.raw('/api/app/user/register');
            vm.req = {};

            vm.alerts = alertService.get();
            vm.reg = function(){
                if(vm.req.password.length < 6){
                    alertService.error("密码长度不能小于6位");
                    return;
                }
                if(vm.req.password != vm.req.password2){
                    alertService.error("两次密码输入不一致");
                    return;
                }
                user_api.save(vm.req, function(data){
                    alertService.add('注册成功');
//                    windows.location = '#/app/user/list'
                })
            }
        }
    ]);
});
