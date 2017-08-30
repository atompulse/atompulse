/**
 * Convert uib datepicker to ISO format
 * Solve problem from here https://stackoverflow.com/questions/27172394/javascript-doest-convert-angular-ui-datepicker-date-to-utc-correctly
 * @author Ionut Pisla <ionut.tudorel@gmail.com>.
 */

try {
    angular.module('Web.Components')
} catch (err) {
    angular.module('Web.Components', [])
}

angular.module('Web.Components')
    .directive('comDateToIso', function () {

        var linkFunction = function (scope, element, attrs, ngModelCtrl) {

            ngModelCtrl.$parsers.push(function (datepickerValue) {
                if (typeof(moment) != 'undefined') {
                    return moment(datepickerValue).format("YYYY-MM-DD");
                }
            });
        };

        return {
            restrict: "A",
            require: "ngModel",
            link: linkFunction
        };
    });