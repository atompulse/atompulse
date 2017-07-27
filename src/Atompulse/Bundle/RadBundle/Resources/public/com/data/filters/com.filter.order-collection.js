/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */
try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

/**
 * Filter for ordering a collection in ng-repeat by
 */
angular.module('Web.Components')
    .filter('comOrderCollection',
        function () {
            var orderCollection = function (collection, field, reverse) {
                var filtered = [];

                _.each(collection, function (item) {
                    filtered.push(item);
                });

                function index(obj, i) {
                    if (typeof(obj) !== 'undefined' && obj !== null) {
                        return obj[i];
                    }
                }

                filtered.sort(function (a, b) {
                    var comparator;
                    var reducedA = field.split('.').reduce(index, a);
                    var reducedB = field.split('.').reduce(index, b);
                    if (reducedA === reducedB) {
                        comparator = 0;
                    } else {
                        comparator = (reducedA > reducedB ? 1 : -1);
                    }

                    return comparator;
                });

                if (reverse) {
                    filtered.reverse();
                }

                return filtered;
        };

        return orderCollection;
    });