/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .directive('comLoadingButton', [
        function ()
        {
            var comLoadingButton = {
                restrict: 'E',
                scope: {
                    options: '=',
                    loadWhen: '=',
                    size: '=',
                    isDisabled: '='
                },
                transclude: true,
                replace: true,
                template: '<button ng-disabled="loadWhen || isDisabled">' +
                                '<span ng-class="{\'comlb-sm-loader\': loadWhen}"><com-spinner spin="loadWhen" size="size" options="options"></com-spinner></span>' +
                                '<span ng-transclude></span>' +
                          '</button>',
                link: function (scope, element, attrs)
                {
                }
            };

            return comLoadingButton;
        }
    ]
);
