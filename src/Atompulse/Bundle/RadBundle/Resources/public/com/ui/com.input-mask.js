/**
 * @author Ionut Pisla <ionut.tudorel@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .directive('inputMask', [
        function ()
        {
            return {
                restrict: 'A',
                require: 'ngModel',
                link: function (scope, el, attrs, ngModel)
                {
                    // first handle angular model
                    ngModel.$parsers.push(function(value)
                    {
                        var parsedValue = parseFloat(value);

                        if (_.isNumber(parsedValue)) {
                            return parsedValue;
                        }

                        return value;
                    });

                    // next do the input-mask binding
                    if (el.prop('type') !== 'text') {
                        el.prop('type', 'text');
                    }
                    $(el).inputmask(scope.$eval(attrs.inputMask));
                }
            };
        }
    ]
);

