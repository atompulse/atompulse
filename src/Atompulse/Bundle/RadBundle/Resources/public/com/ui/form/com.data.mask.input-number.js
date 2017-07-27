/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

/**
 * Add a NUMBER mask to an input to ONLY allow numbers (positive, negative, float, integer)
 */
angular.module('Web.Components')
    .directive('comDataMaskInputNumber', [
        function() {
            var comDataMaskInputNumber = {
                require: 'ngModel',
                link : function(scope, element, attrs,  ngModel)
                {
                    // TODO: this is deprecated => use inputMask

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

//angular.module('Web.Components')
//    .directive('forceNumber', [
//        function() {
//            var comDataMaskInputNumber = {
//                require: 'ngModel',
//                link : function(scope, element, attrs,  ngModel)
//                {
//                    ngModel.$formatters.push(function(value) {
//                            return parseFloat(value);
//                          });
//                    ngModel.$parsers.push(function(value) {
//                        //console.log(typeof value, value, 'then :', typeof parseFloat(value), parseFloat(value));
//                        return parseFloat(value);
//                    });
//                }
//            };
//
//            return comDataMaskInputNumber;
//        }
//    ]);