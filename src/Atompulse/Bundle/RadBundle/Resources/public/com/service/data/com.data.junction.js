/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try {
    angular.module('Web.Components')
} catch (err) {
    angular.module('Web.Components', [])
}

angular.module('Web.Components')
    .factory('Web.Components.DataJunction', [
        function () {

            /**
             * A strategy to query multiple data sources in parallel,
             * then join the responses and return a unified response object
             */
            var DataJunction = {
                Instance: function () {

                    var $this = {};

                    var $private = {
                        sources: {},
                        state: null,
                        callback: null
                    };

                    /**
                     * Actual source registration
                     * @param alias
                     * @param callable
                     * @param parameters
                     * @param callback
                     */
                    $private.registerSource = function (alias, callable, parameters, callback) {
                        $private.sources[alias] = {
                            parameters: parameters,
                            callable: callable,
                            callback: callback,
                            state: false,
                            ds: {
                                status: null,
                                msg: null,
                                data: null
                            }
                        };
                    };

                    /**
                     * DataSource callback builder
                     * @param dataSourceAlias
                     * @returns {Function}
                     */
                    $private.buildCallback = function (dataSourceAlias) {
                        return function (resp) {

                            var dataSource = $private.sources[dataSourceAlias];

                            dataSource.state = true;
                            dataSource.ds = _.clone(resp);

                            if (dataSource.callback) {
                                dataSource.callback.apply(dataSource.callback, [resp]);
                            }

                            $private.onSourceUpdate(dataSourceAlias);
                        };
                    };

                    /**
                     * Initiate DataSource querying
                     * @param dataSourceAlias
                     */
                    $private.querySource = function (dataSourceAlias) {
                        var dataSource = $private.sources[dataSourceAlias],
                            wrappedCallback = $private.buildCallback(dataSourceAlias),
                            preparedParameters = dataSource.parameters;

                        preparedParameters.push(wrappedCallback);

                        // update state
                        dataSource.state = false;
                        // execute callable
                        dataSource.callable.apply($this, preparedParameters);
                    };

                    /**
                     * On DataSource state update
                     * @param dataSourceAlias
                     */
                    $private.onSourceUpdate = function (dataSourceAlias) {
                        var allSourcesQueried = true,
                            data = {};

                        // check if all sources have been queried
                        _.each($private.sources, function (dataSource, dataSourceAlias) {
                            if (dataSource.state) {
                                data[dataSourceAlias] = _.clone(dataSource.ds);
                            }
                            allSourcesQueried = allSourcesQueried && dataSource.state;
                        });

                        if (allSourcesQueried) {
                            $private.callback.apply($private.callback, [data]);
                        }
                    };

                    /**
                     * Data source registration
                     * @param dataSource
                     * @param parameters
                     * @param callback
                     * @returns {{}}
                     */
                    $this.addSource = function (dataSource, parameters, callback) {

                        callback = callback || null;

                        var alias = _.uniqueId('ds_'),
                            callable = dataSource;

                        if (_.isObject(dataSource)) {
                            alias = _.keys(dataSource)[0];
                            callable = dataSource[alias];
                        }

                        $private.registerSource(alias, callable, parameters, callback);

                        return $this;
                    };

                    /**
                     * Listener registration & query trigger
                     * @param callback
                     */
                    $this.onReady = function (callback) {
                        $private.callback = callback;

                        _.each($private.sources, function (dataSource, dataSourceAlias) {
                            $private.querySource(dataSourceAlias);
                        });
                    };

                    return $this;
                }
            };

            return DataJunction;
        }
    ]);