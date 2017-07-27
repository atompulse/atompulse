/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */
try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

/**
 * Search a collection by using 1 or more criterias given in as an object
 * {field1: value1, field2: value2, etc}
 */
angular.module('Web.Components')
    .filter('comSearchCollection',
        function () {
            var searchCollection = function (collection, fields) {
                var filtered = [];
                _.each(collection, function (item) {
                    var match = true;
                    // search for every criteria
                    _.each(fields, function (filterValue, filter) {
                        var itemValue = item[filter];
                        // check for complex structure field.path.path
                        if (filter.indexOf('.') > 0) {
                            itemValue = item;
                            var pathKeys = filter.split('.');
                            _.each(pathKeys, function (key) {
                                itemValue = itemValue[key];
                            });
                        }
                        switch (typeof filterValue) {
                            // number
                            case 'number' :
                                match = match && (itemValue == filterValue);
                            break;
                            // boolean
                            case 'boolean' :
                                match = match && (itemValue == filterValue);
                            break;
                            // string
                            case 'string' :
                            default:
                                if (_.isString(filterValue) && filterValue.length > 0) {
                                    var textMatch = new RegExp(filterValue, 'ig');
                                    match = match && textMatch.test('\b'+itemValue);
                                }
                            break;
                        }
                    });
                    if (match) {
                        filtered.push(item);
                    }
                });

                return filtered;
        };

        return searchCollection;
    });