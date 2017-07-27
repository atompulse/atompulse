/**
 * This is a temporary fix for initial date display bug in angular 1.3
 * More details here https://github.com/angular-ui/bootstrap/issues/2659
 * Ui Bootstrap  0.13  have date validation problems (will be refactored https://github.com/angular-ui/bootstrap/issues/3736) so for now will stick with 0.12
 * @author Ionut Pisla <ionut.tudorel@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .directive('comDatePicker', function () {
        var comDatePicker = {
            priority: 1,
            terminal: true,
            restrict: 'EAC',
            require: 'ngModel',
            replace: true,
              scope: {
                  opened: "=",
                  options: "=",
                  format: "="
              }
        };

        comDatePicker.templateUrl = 'dapicker';

        comDatePicker.link = function(scope, element, attr) {

        //console.log(scope.model);
          //scope.popupOpen = false;
          //scope.openPopup = function($event) {
          //    $event.preventDefault();
          //    $event.stopPropagation();
          //    scope.popupOpen = true;
          //};
          //
          scope.open = function($event) {
              console.log('hopa');
            $event.preventDefault();
            $event.stopPropagation();
            scope.opened = true;
          };

      };

        return comDatePicker;
});