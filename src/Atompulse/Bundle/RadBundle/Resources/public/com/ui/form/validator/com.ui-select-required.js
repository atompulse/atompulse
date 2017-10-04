/*
 Angular ui select validator (at least one value is required)
 Taken from here https://github.com/angular-ui/ui-select/issues/258
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .directive('comUiSelectRequired', function () {
        return {
            require: 'ngModel',
            link: function (scope, elm, attrs, ctrl)
            {
                var validator = function (modelValue, viewValue) {
                    var determineVal;
                    // Multiselect
                    if (angular.isArray(modelValue)) {
                        determineVal = modelValue;
                    } else if (angular.isArray(viewValue)) {
                        determineVal = viewValue;
                    } else {
                        // Regular single select
                        if (!angular.isArray(modelValue) && typeof(modelValue) !== 'undefined' && modelValue != null) {
                            return true;
                        } else {
                            return false;
                        }
                    }

                    return determineVal.length > 0;
                };

                ctrl.$validators.required = validator;
            }
        };
    });