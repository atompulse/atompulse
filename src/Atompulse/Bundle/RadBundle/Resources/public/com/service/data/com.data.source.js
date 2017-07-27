/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .factory('Web.Components.DataSource', [
        '$rootScope',
        function ($rootScope)
        {
            var DataSource = {
                /**
                 * NON Singleton Object Instance
                 * @returns {*}
                 * @constructor
                 */
                Instance : function ()
                {
                    var $private = {
                        // current request
                        request: null,
                        // headers parsers
                        parseResponseHeaders: function (headerStr)
                        {
                            var headers = {};
                            if (!headerStr) {
                                return headers;
                            }
                            var headerPairs = headerStr.split('\u000d\u000a');
                            for (var i = 0; i < headerPairs.length; i++) {
                                var headerPair = headerPairs[i];
                                // Can't use split() here because it does the wrong thing
                                // if the header value has the string ": " in it.
                                var index = headerPair.indexOf('\u003a\u0020');
                                if (index > 0) {
                                    var key = headerPair.substring(0, index);
                                    var val = headerPair.substring(index + 2);
                                    headers[key] = val;
                                }
                            }

                            return headers;
                        }
                    };

                    var $this = {
                        /**
                         * Request Config for the AJAX request
                         */
                        requestConfig: {
                            method: 'GET',
                            url: null,
                            contentType: 'application/json', // data format sent TO server
                            dataType: 'json', // data format received FROM server
                            data: null,
                            headers: {},
                            cache: false,
                            timeout: 1000 * 60 * 5 // 5 minutes timeout
                        },

                        /**
                         * Loading state: true:loading / false:not loading
                         */
                        state: false,

                        /**
                         * Parameters that will be added to the request
                         * @type {*}
                         */
                        params: {}
                    };

                    /**
                     * Default response handler (does nothing)
                     * @param data
                     * @param status
                     * @param headers
                     */
                    $private.responseHandler = function (data, status, headers)
                    {
                        $this.state = false;
                    };

                    /**
                     * Make a new request
                     * @returns {{promise: null, timeoutExpired: boolean, abortTimer: Number, abortPromise: Deferred, aborted: boolean, abort: abort}}
                     */
                    $private.makeNewRequest = function ()
                    {
                        var requestConfig = _.deepClone($this.requestConfig);

                        var request = $.ajax(requestConfig.url,
                            {
                                type: requestConfig.method,
                                contentType: requestConfig.contentType,
                                dataType: requestConfig.dataType,
                                timeout: requestConfig.timeout,
                                headers: requestConfig.headers,
                                data: requestConfig.data ? JSON.stringify(requestConfig.data) : null,
                                success: function (responsePayload, status, jqXHR) {
                                    // update state
                                    $this.state = false;

                                    $private.responseHandler.apply($private.responseHandler, [responsePayload, status, $private.parseResponseHeaders(jqXHR.getAllResponseHeaders())]);

                                    $rootScope.$applyAsync();

                                    // clear the current request
                                    $private.request = null;
                                },
                                error: function (jqXHR, status, error) {
                                    // update state
                                    $this.state = false;

                                    if (status === 500) {
                                        $private.responseHandler.apply($private.responseHandler, [null, status, $private.parseResponseHeaders(jqXHR.getAllResponseHeaders())]);
                                    } else {
                                        $private.responseHandler.apply($private.responseHandler, [false, status, $private.parseResponseHeaders(jqXHR.getAllResponseHeaders())]);
                                    }

                                    $rootScope.$applyAsync();

                                    // clear the current request
                                    $private.request = null;
                                }
                            });


                        return request;
                    };

                    /**
                     * Initiate the loading data
                     * @param callback
                     */
                    $this.load = function (callback)
                    {
                        // overwrite existing callback ?
                        if (typeof(callback) !== 'undefined' && _.isFunction(callback)) {
                            $this.setResponseHandler(callback);
                        }

                        // handle parameters
                        _.deepExtend($this.params, $this.requestConfig['data']);
                        if (_.size($this.params) > 0) {
                            $this.requestConfig['data'] = $this.params;
                        }

                        // if there is a request in progress then we will abort it
                        if (!_.isNull($private.request)) {
                            $private.request.abort();
                        }
                        // create new request
                        $private.request = $private.makeNewRequest();

                        // update state
                        $this.state = true;
                    };

                    /**
                     * Set response handler
                     * @param callback
                     */
                    $this.setResponseHandler = function (callback)
                    {
                        if (typeof(callback) !== 'undefined' && _.isFunction(callback)) {
                            $private.responseHandler = callback;
                        } else {
                            throw 'DataSource::Response Handler is not valid ['+callback+']';
                        }
                    };

                    /**
                     * Get current response handler
                     * @returns {responseHandler|*|$private.responseHandler}
                     */
                    $this.getResponseHandler = function ()
                    {
                        return $private.responseHandler;
                    };

                    /**
                     * Set the config params for AJAX request
                     * @param params
                     */
                    $this.setRequestConfig = function (params)
                    {
                        _.deepExtend($this.requestConfig, params);
                    };

                    /**
                     * Add parameter to datasource
                     * @param paramName
                     * @param paramExpression
                     * @param reload
                     * @returns {*}
                     */
                    $this.addParam = function (paramName, paramExpression, reload)
                    {
                        $this.params[paramName] = paramExpression;

                        if (typeof(reload) !== 'undefined' && reload) {
                            return $this.load();
                        }
                    };

                    /**
                     * Remove a parameter
                     * @param paramName
                     * @param reload
                     * @returns {*}
                     */
                    $this.clearParam = function (paramName, reload)
                    {
                        $this.params[paramName] = null;

                        if (typeof(reload) !== 'undefined' && reload) {
                            return $this.load();
                        }
                    };

                    /**
                     * Get all parameters
                     * @returns {{}|*}
                     */
                    $this.getParams = function ()
                    {
                        return $this.params;
                    };

                    /**
                     * Clear all parameters
                     * @param reload
                     * @returns {*}
                     */
                    $this.resetParams = function (reload)
                    {
                        $this.params = {};

                        if (typeof(reload) !== 'undefined' && reload) {
                            return $this.load();
                        }
                    };

                    return $this;
                }
            };

            return DataSource;
        }
    ]);