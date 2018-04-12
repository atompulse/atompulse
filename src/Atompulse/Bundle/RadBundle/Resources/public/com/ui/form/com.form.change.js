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
                scope: {
                    comFormModel: '=',
                    comFormChange: '='
                },
                link: function (scope, element, attrs)
                {
                    var model = scope.comFormModel,
                        listener = scope.comFormChange;

                    if (typeof(model) === 'undefined') {
                        throw 'comFormChange: model ['+attrs.comFormModel+'] is undefined';
                    }
                    if (typeof(listener) === 'undefined') {
                        throw 'comFormChange: listener ['+attrs.comFormChange+'] is undefined';
                    }

                    // add a watcher for each of the model fields
                    angular.forEach(model, function(value, modelField)
                    {
                        if (_.isFunction(value) || modelField === '$$hashKey') {
                            return;
                        }

                        var expr = 'comFormModel.'+modelField;

                        scope.$watch(expr, function(newVal, oldVal, scope) {
                            // if value is changed then notify the listener
                            if (newVal !== oldVal) {
                                listener.call(listener, modelField, newVal, oldVal);
                            }
                        });
                    });
                }
            };

            return comFormChange;
    }]);
