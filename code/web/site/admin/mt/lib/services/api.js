'use strict';

define([
    'app',
    '../../lib/services/utils'
], function (md) {
    md.service('api', ['$resource', '$filter', 'utils', function ($resource, $filter, utils) {
        var api_prefix = '';
        var api_map = {
            user:['/api/app/user/:id', {id:"@id"}, {
                logout:{
                    url:'/api/app/user/logout'
                },
                info:{
                    url:'/api/app/user/info'
                },
                local_login:{
                    method:'post',
                    url:'/api/app/user/login'
                }
            }],
            buddy:['/api/app/buddy/:id', {id:"@id"}],
            message:['/api/app/message/:id', {id:"@id"}]
        };

        this.init = function(type){
            if(!api_map[type]){
                return null;
            }
            var option = api_map[type];
            if(_.isString(option)){
                return $resource(api_prefix + option);
            }

            option[0] = api_prefix + option[0]; //add url prefix
            var resource = $resource.apply(null, option);

            return resource;
        };

        this.raw = function(url){
            return $resource(url);
        };

        this.meta = function(header_func){
            return JSON.parse(header_func()['x-meta-list'])
        };

        //2010-08-09 13:22:13
        this.strToDate = function(str){
            return new Date(str);
        };
        this.dateToDayStr = function(day){
            return $filter('date')(day, 'yyyy-MM-dd');
        };
        this.dateToTimeStr = function(day){
            return $filter('date')(day, 'yyyy-MM-dd HH:mm:ss');
        };

        this.getCorpId = function(){
            return utils.getCorpId()
        }

    }]);
});