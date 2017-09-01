/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */
try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .factory('Web.Components.DataContainerService', [
        function ()
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
                            $this.addProperty(property, defaultValue);
                        });

                        return $this;
                    };

                    /**
                     * Add property to model
                     * @param property
                     * @param defaultValue
                     */
                    $this.addProperty = function (property, defaultValue)
                    {
                        defaultValue = !_.isUndefined(defaultValue) ? defaultValue : null;

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
                    };

                    /**
                     * Populate data container
                     * @param inputData
                     * @param mappings
                     */
                    $this.populateFromData = function (inputData, mappings)
                    {
                        var data = angular.copy(inputData),
                            withMappings = !_.isUndefined(mappings) && _.isObject(mappings) && _.size(mappings) > 0,
                            mappings = mappings || {};

                        // fix reserved property name 'length'
                        if (!_.isUndefined(data['length'])) {
                            withMappings = true;
                            mappings['_length_'] = 'length';
                            data['_length_'] = data['length'];
                            delete data['length'];
                        }

                        _.each (data, function (value, property) {
                            if (!_.isUndefined($private.model[property])) {
                                if (!_.isEqual($private.model[property], value)) {
                                    $private.model[property] = value;
                                }
                            } else {
                                // handle mappings (i.e. id => entity_id or vice-versa)
                                var mappedProperty = withMappings && !_.isUndefined($private.model[mappings[property]]) ? mappings[property] : false;
                                if (mappedProperty && !_.isEqual($private.model[mappedProperty], value)) {
                                    $private.model[mappedProperty] = value;
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
