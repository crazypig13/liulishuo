define([
    'app',
    '../../lib/services/api',
    '../../lib/services/dialog',
    '../../lib/services/alert',
    '../../lib/services/options',
    '../../lib/services/showFilter',
    '../main/filter/filter',
    '../main/filter/values'
], function (md) {
    md.controller('userListController', ['$rootScope','$scope', '$state','$location','$uibModal','alertService','api',
            function ($rootScope, $scope, $state, $location, $modal, alertService, api) {
                var vm = $scope.vm = {};
                var buddy_api = api.init('buddy');

                vm.buddy_req = {};
                vm.doQuery = function(){
                    vm.items = buddy_api.query(vm.query)
                };
                vm.doQuery();
                var listener = {
                    name: 'total',
                    func: vm.doQuery
                };
                $rootScope.listeners.push(listener)

                vm.addBuddy = function(){
                    buddy_api.save(vm.buddy_req, function(data){
                        alertService.add('添加成功');
                        vm.doQuery();
                    })
                };
                vm.deleteBuddy = function(item){
                    if(!confirm('确认删除？')){
                        return ;
                    }
                    buddy_api.delete({id: item.id}, function(data){
                        alertService.add('删除成功');
                        vm.doQuery();
                    })
                };
            }]
    );

    md.controller('userInfoController', ['$rootScope','$scope', '$state','$location','$uibModal','alertService','api','options',
        function ($rootScope, $scope, $state, $location, $modal, alertService, api, options) {
            var vm = $scope.vm = {};
            var msg_api = api.init('message');
            var user_api = api.init('user');
            vm.id = $state.params['id'];
            vm.req = {
                to_id: vm.id
            }

            vm.loadUser = function(){
                user_api.get({id:vm.id}, function(data){
                    vm.buddy = data;
                })
            }
            vm.loadUser();

            vm.load = function(){
                msg_api.query({to_id:vm.id}, function(data){
                    vm.messages = data;
                    setTimeout(function(){
                        $('#chat_field').scrollTop($("#chat_field").get(0).scrollHeight);
                    }, 600);
                });
            };

            vm.reload = function(detail){
                _.each(detail, function(d){
                    console.log(d);
                    if(d.user_id == vm.id){
                        vm.load();
                    }
                })
            }

            var listener = {
                name: 'detail',
                func: vm.reload
            };
            $rootScope.listeners.push(listener)
            vm.load();

            vm.saveMsg = function(){
                msg_api.save(vm.req, function(){
                    vm.load();
                    vm.req.message = '';
                })
            };
            vm.deleteMsg = function(msg){
                msg_api.delete({id:msg.id}, function(){
                    vm.load();
                })
            }
        }]
    );

    md.controller('userHistoryController', ['$rootScope','$scope', '$state','$location','$uibModal','alertService','api','options',
        function ($rootScope, $scope, $state, $location, $modal, alertService, api, options) {
            var vm = $scope.vm = {};
            var msg_api = api.init('message');
            var user_api = api.init('user');
            vm.id = $state.params['id'];
            vm.req = {
                to_id: vm.id
            }

            vm.loadUser = function(){
                user_api.get({id:vm.id}, function(data){
                    vm.buddy = data;
                })
            }
            vm.loadUser();

            vm.load = function(){
                msg_api.query({to_id:vm.id, history:1}, function(data){
                    vm.messages = data;
                });
            };

            vm.load();
        }]
    );

});