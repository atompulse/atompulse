/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */
try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .factory('Web.Components.DataContainerService', ['$sce',
        function ($sce)
        {
            var DataContainerService = {

                /**
                 * NON Singleton Object Instance
                 * @param $model
                 * @returns {*}
                 * @constructor
                 */
                Instance : function ($model)
                {
                    /**
                     * Public instance
                     * @type {{}}
                     */
                    var $this = {
                    };

                    /**
                     * Private instance
                     * @type {{}}
                     */
                    var $private = {
                        model: {}
                    };

                    /********************
                        Public methods
                     ********************/

                    /**
                     * Initializer method
                     * @param $model
                     * @returns {{}}
                     */
                    $this.init = function ($model)
                    {
                        _.each ($model, function (defaultValue, property) {
                            Object.defineProperty($this, property, {
                                get: function () {
                                    return $private.model[property];
                                },
                                set: function (value) {
                                    $private.model[property] = value;
                                },
                                enumerable: true
                            });
                            $private.model[property] = defaultValue;
                        });

                        return $this;
                    };

                    /**
                     * Populate data container
                     * @param data
                     * @param mappings
                     */
                    $this.populateFromData = function (data, mappings)
                    {
                        var withMappings = !_.isUndefined(mappings) && _.isObject(mappings) && _.size(mappings) > 0;

                        _.each (data, function (value, property) {
                            if (!_.isUndefined($private.model[property])) {
                                if (!_.isEqual($private.model[property], value)) {
                                    $private.model[property] = angular.copy(value);
                                }
                            } else {
                                // handle mappings (i.e. id => entity_id or vice-versa)
                                var mappedProperty = withMappings && !_.isUndefined($private.model[mappings[property]]) ? mappings[property] : false;
                                if (mappedProperty && !_.isEqual($private.model[mappedProperty], value)) {
                                    $private.model[mappedProperty] = angular.copy(value);
                                }
                            }
                        });
                    };

                    /**
                     * Get container data
                     * @returns {*}
                     */
                    $this.getData = function ()
                    {
                        return angular.copy($private.model);
                    };

                    /********************
                        Private methods
                     ********************/


                    return $this.init($model);
                }
            };

            return DataContainerService;
        }
    ]);
