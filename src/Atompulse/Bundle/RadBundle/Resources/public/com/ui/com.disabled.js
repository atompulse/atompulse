/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .directive('comDisabled', [
        function ()
        {
            var comDisabled = {
                restrict: 'A',
                scope: {
                    comDisabled: '='
                },
                link: function (scope, element, attrs)
                {
                    var $overlay = $('<div>', {id: _.uniqueId('comd-ov-'), class: "com-disabled"});

                    var addOverlay = function () {
                        element.append($overlay);
                        element.find(':input').prop('disabled', true);
                        element.find(':button').prop('disabled', true);
                    };

                    var removeOverlay = function () {
                        $overlay.remove();
                        element.find(':input').prop('disabled', false);
                        element.find(':button').prop('disabled', false);
                    };

                    scope.$watch('comDisabled', function (value) {
                        if (value) {
                            addOverlay();
                        } else {
                            removeOverlay();
                        }
                    });
                }
            };

            return comDisabled;
        }
    ]
);