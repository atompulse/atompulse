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
                link: function (scope, el, attrs, ngModel) {

                    $(el).inputmask(scope.$eval(attrs.inputMask));

                    ngModel.$parsers.push(function(value) {
                        var parsedValue = parseFloat(value);
                        if (_.isNumber(parsedValue)) {
                            return parsedValue;
                        }
                        return value;
                    });

                }
            };
        }
    ]
);

