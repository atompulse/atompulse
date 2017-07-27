/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */
/*
    Angular ui select validator (at least one value is required)
    Taken from here https://github.com/angular-ui/ui-select/issues/258
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .directive('comArrayRequired', function () {
        return {
            require: 'ngModel',
            link: function (scope, elm, attrs, ctrl) {
                ctrl.$validators.required = function (modelValue, viewValue) {

                    ctrl.$dirty = true;
                    ctrl.$pristine = false;
                    ctrl.$touched = true;
                    ctrl.$untouched = false;

                    var determineVal;
                    if (angular.isArray(modelValue)) {
                        determineVal = modelValue;
                    } else if (angular.isArray(viewValue)) {
                        determineVal = viewValue;
                    } else {
                        return false;
                    }

                    return determineVal.length > 0;
                };
            }
        };
    });