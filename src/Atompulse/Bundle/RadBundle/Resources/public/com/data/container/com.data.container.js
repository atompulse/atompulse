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
                        mappings = $private.initMappings(mappings);

                        var data = angular.copy(inputData),
                            withMappings = _.size(mappings) > 0;


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
                     * Validate input data against the described model
                     * @param inputData
                     * @param mappings
                     * @returns {boolean}
                     */
                    $this.validateDataModel = function (inputData, mappings)
                    {
                        if (_.size($private.model) == 0) {
                            throw "DataContainerService: data model not available, no properties defined!";
                        }

                        mappings = $private.initMappings(mappings);

                        var data = angular.copy(inputData),
                            withMappings = _.size(mappings) > 0,
                            isValid = true;

                        // fix reserved property name 'length'
                        if (!_.isUndefined(data['length'])) {
                            withMappings = true;
                            mappings['_length_'] = 'length';
                            data['_length_'] = data['length'];
                            delete data['length'];
                        }

                       _.each (data, function (value, property) {
                            if (!_.isUndefined($private.model[property])) {
                            } else {
                                var mappedProperty = withMappings && !_.isUndefined($private.model[mappings[property]]) ? mappings[property] : false;
                                // handle mappings (i.e. id => entity_id or vice-versa)
                                if (!mappedProperty) {
                                    isValid = false;
                                }
                            }
                        });

                        return isValid;
                    };

                    /**
                     * Get Data Model properties
                     * @returns {{}}
                     */
                    $this.getModelProperties = function ()
                    {
                        return $private.getModelProperties($private.model);
                    };

                    /**
                     * Get container data
                     * @param excludedProperties
                     * @returns {*}
                     */
                    $this.getData = function (excludedProperties)
                    {
                        excludedProperties = _.isObject(excludedProperties) && _.size(excludedProperties) > 0 ? excludedProperties : false;

                        return excludedProperties ? _.omit(angular.copy($private.model), excludedProperties) : angular.copy($private.model);
                    };

                    /********************
                        Private methods
                     ********************/

                    /**
                     * Handle mappings resolution
                     * @param mappings
                     * @returns {*}
                     */
                    $private.initMappings = function (mappings)
                    {
                        return (!_.isUndefined(mappings) && _.isObject(mappings) && _.size(mappings) > 0) ? mappings : {};
                    };

                    /**
                     * Get model properties
                     * @param $model
                     * @returns {{}}
                     */
                    $private.getModelProperties = function ($model)
                    {
                        var properties = {};

                        _.each ($model, function (value, property) {
                            if (_.isObject(value)) {
                                properties[property] = $private.getModelProperties(value);
                            } else {
                                properties[property] = typeof value;
                            }
                        });

                        return properties;
                    };

                    return $this.init($model);
                }
            };

            return DataContainerService;
        }
    ]);
