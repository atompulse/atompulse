/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

/**
 * Add a NUMBER mask to an input to ONLY allow numbers (positive, negative, float, integer)
 * @deprecated Deprecated in favor of inputMask
 */
angular.module('Web.Components')
    .directive('comDataMaskInputNumber', [
        function() {
            var comDataMaskInputNumber = {
                require: 'ngModel',
                link : function(scope, element, attrs,  ngModel)
                {
                    ngModel.$parsers.push(function(inputValue) {
                        if (typeof(inputValue) == 'undefined' || inputValue == null) {

                            return ''; //If value is required
                        }

                        if (typeof(inputValue) != 'string') {
                            inputValue = inputValue.toString();
                        }

                        var transformedInput = inputValue.replace(/([^-\d.]+)?((-{0,1}\d*\.?\d*)(.*)?$)/, "$3");

                        if (transformedInput != inputValue) {
                            ngModel.$setViewValue(transformedInput);
                            ngModel.$render();
                        }

                        return transformedInput;
                    });
                }
            };

            return comDataMaskInputNumber;
        }
    ]);
