/**
 * alertService.add(data.msg, 3000, 'danger')
 *  danger
 *  success
 *  info
 *  warning
 */
define(['app'], function (md) {
    md.service('alertService', ['$rootScope', '$timeout', function ($rootScope, $timeout) {
        var alerts = [];
        var timer = null;

        var danger = function(msg, time){
            return add(msg, time, 'danger');
        };
        var info = function(msg, time){
            return add(msg, time, 'info');
        };
        var warning = function(msg, time){
            return add(msg, time, 'warning');
        };

        function add(msg, time, type) {
            time= time||3000;
            if (_.findWhere(alerts, {msg:msg})) {
                return false;
            }
            var item = {
                type: type || 'success ',
                msg: msg,
                close: function () {
                    return closeAlert(this);
                }

            };

            if (time) {
                timer = $timeout(function () {
                    closeAlert(item);
                }, time)
            }
            return alerts.push(item);
        }

        function closeAlert(alert) {
            return closeAlertIdx(alerts.indexOf(alert));
        }

        function closeAlertIdx(index) {
            return alerts.splice(index, 1);
        }

        function clear() {
            alerts = [];
        }

        function get() {
            return alerts;
        }

        return {
            add: add,
            danger: danger,
            error: danger,
            info: info,
            warning: warning,
            closeAlert: closeAlert,
            closeAlertIdx: closeAlertIdx,
            clear: clear,
            get: get
        };
    }]
    )

});