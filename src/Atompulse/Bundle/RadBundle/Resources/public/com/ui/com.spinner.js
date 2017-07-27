/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .directive('comSpinner', [
        function ()
        {
            var spinner = {
                restrict: 'E',
                scope: {
                    options: '=',
                    spin: '=',
                    size: '='
                },
                link: function (scope, element, attrs)
                {
                    var opts = {
                        lines: 13, // The number of lines to draw
                        length: 20, // The length of each line
                        width: 10, // The line thickness
                        radius: 30, // The radius of the inner circle
                        corners: 1, // Corner roundness (0..1)
                        rotate: 0, // The rotation offset
                        direction: 1, // 1: clockwise, -1: counterclockwise
                        color: '#000', // #rgb or #rrggbb or array of colors
                        speed: 1, // Rounds per second
                        trail: 60, // Afterglow percentage
                        shadow: false, // Whether to render a shadow
                        hwaccel: false, // Whether to use hardware acceleration
                        className: 'spinner', // The CSS class to assign to the spinner
                        zIndex: 2e9, // The z-index (defaults to 2000000000)
                        top: '50%', // Top position relative to parent
                        left: '50%' // Left position relative to parent
                    };

                    // map options
                    var addOptions = function (from, to)
                    {
                        _.each(from, function (optionValue, optionName) {
                            if (optionName === 'lg') {
                                to['length'] = optionValue;
                            } else {
                                to[optionName] = optionValue;
                            }
                        });
                    };

                    // predefined sizes
                    var sizes = {
                        small: {
                            lines: 11,
                            lg: 4,
                            width: 2,
                            radius: 2
                        },
                        medium: {
                            lines: 12,
                            lg: 8,
                            width: 1,
                            radius: 7
                        },
                        large: {
                            lines: 17,
                            lg: 15,
                            width: 4,
                            radius: 12
                        }
                    };

                    // check for predefined sizes
                    if (typeof(scope.size) !== 'undefined' && typeof(sizes[scope.size]) !== 'undefined') {
                        addOptions(sizes[scope.size], opts);
                    }
                    // check for options
                    if (typeof(scope.options) !== 'undefined') {
                        addOptions(scope.options, opts);
                    }

                    var spinnerObj = new Spinner(opts);

                    scope.$watch('spin', function (doSpin) {
                        if (doSpin) {
                            spinnerObj.spin();
                            element.html(spinnerObj.el);
                        } else {
                            spinnerObj.stop();
                        }
                    });

                    $(element).css('position', 'relative');
                }
            };

            return spinner;
        }
    ]
);
