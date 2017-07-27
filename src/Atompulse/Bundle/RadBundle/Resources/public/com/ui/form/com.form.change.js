/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

/**
 * Directive to detect form changes.
 * NOTE: a model (object with properties) is required and their properties are watched
 * ATTENTION: the view is not observed for changes, its the model fields that are observed for changes;
 * this means that changes from both view and model are tracked
 */
angular.module('Web.Components')
    .directive('comFormChange', [
        function () {
            var comFormChange = {
                restrict: 'A',
                link: function (scope, element, attrs)
                {
                    var model = scope.$eval(attrs.comFormModel);
                    var callback = scope.$eval(attrs.comFormChange);

                    if (typeof(model) == 'undefined') {
                        throw 'comFormChange: model ['+attrs.comFormModel+'] is undefined';
                    }
                    if (typeof(callback) == 'undefined') {
                        throw 'comFormChange: callback ['+attrs.comFormChange+'] is undefined';
                    }
                    // add a watcher for each of the model fields
                    angular.forEach(model, function(value, modelField) {
                        var expr = attrs.comFormModel+'.'+modelField;
                        scope.$watch(expr, function(newVal, oldVal, scope) {
                            // if value is changed then launch the callback
                            if (newVal !== oldVal) {
                                callback.call(callback, modelField, newVal, oldVal);
                            }
                        });
                    });
                }
            };

            return comFormChange;
    }]);

