/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try {
    angular.module('Web.Components')
} catch (err) {
    angular.module('Web.Components', [])
}

angular.module('Web.Components')
    .directive('comComputed', [
        function () {
            var comComputedValue = {
                restrict: 'A',
                scope: {
                    ngModel: '='
                },
                link: function (scope, element, attrs) {
                    // observe changes to interpolated attribute
                    attrs.$observe('comComputed', function (value) {
                        scope.ngModel = value;
                    });
                }
            };

            return comComputedValue;
        }
    ]
);