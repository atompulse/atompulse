/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try {
    angular.module('Web.Components')
} catch (err) {
    angular.module('Web.Components', [])
}

angular.module('Web.Components')
    .directive('comOnEnter', [
        function () {
            var comOnEnter = {
                restrict: 'A',
                link: function (scope, element, attrs)
                {
                    element.bind("keydown keypress", function ($event) {
                        if ($event.which === 13) {
                            scope.$apply(function () {
                                scope.$eval(attrs.comOnEnter);
                            });

                            $event.preventDefault();
                        }
                    });
                }
            };

            return comOnEnter;
        }
    ]
);