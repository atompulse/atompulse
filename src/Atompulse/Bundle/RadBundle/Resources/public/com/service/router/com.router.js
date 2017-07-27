/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try {
    angular.module('Web.Components')
} catch (err) {
    angular.module('Web.Components', [])
}

angular.module('Web.Components')
    .factory('Web.Components.Router', [
        function ()
        {

            var RouterService = {

                context: {},
                routes: {},

                init: function (context, routes)
                {
                    this.context = context || {base_url: '', prefix: '', host: '', scheme: ''};
                    this.setRoutes(routes || {});
                },

                /**
                 * @param {Object.<string, fos.Router.Route>} routes
                 */
                setRoutes: function(routes)
                {
                    this.routes = routes;
                },
                
                /**
                 * @return {Object.<string, fos.Router.Route>} routes
                 */
                getRoutes: function ()
                {
                    return this.routes;
                },
                
                /**
                 * @param {string} baseUrl
                 */
                setBaseUrl: function (baseUrl)
                {
                    this.context.base_url = baseUrl;
                },
                
                /**
                 * @return {string}
                 */
                getBaseUrl: function ()
                {
                    return this.context.base_url;
                },
                
                /**
                 * @param {string} prefix
                 */
                setPrefix: function (prefix)
                {
                    this.context.prefix = prefix;
                },
                
                /**
                 * @param {string} scheme
                 */
                setScheme: function (scheme)
                {
                    this.context.scheme = scheme;
                },
                
                /**
                 * @return {string}
                 */
                getScheme: function ()
                {
                    return this.context.scheme;
                },
                
                /**
                 * @param {string} host
                 */
                setHost: function(host) {
                    this.context.host = host;
                },
                
                /**
                 * @return {string}
                 */
                getHost: function() {
                    return this.context.host;
                },
                
                /**
                 * Builds query string params added to a URL.
                 * Port of jQuery's $.param() function, so credit is due there.
                 *
                 * @param {string} prefix
                 * @param {Array|Object|string} params
                 * @param {Function} add
                 */
                buildQueryParams: function(prefix, params, add) {
                    var self = this;
                    var name;
                    var rbracket = new RegExp(/\[\]$/);
                
                    if (params instanceof Array) {
                        _.each(params, function(val, i) {
                            if (rbracket.test(prefix)) {
                                add(prefix, val);
                            } else {
                                self.buildQueryParams(prefix + '[' + (typeof val === 'object' ? i : '') + ']', val, add);
                            }
                        });
                    } else if (typeof params === 'object') {
                        for (name in params) {
                            this.buildQueryParams(prefix + '[' + name + ']', params[name], add);
                        }
                    } else {
                        add(prefix, params);
                    }
                },
                
                /**
                 * Returns a raw route object.
                 *
                 * @param {string} name
                 * @return {fos.Router.Route}
                 */
                getRoute: function(name) {
                    var prefixedName = this.context.prefix + name;

                    if (!_.has(this.routes, prefixedName)) {
                        // Check first for default route before failing
                        if (!_.has(this.routes, name)) {
                            throw new Error('The route "' + name + '" does not exist.');
                        }
                    } else {
                        name = prefixedName;
                    }
                
                    return (this.routes[name]);
                },
                
                
                /**
                 * Generates the URL for a route.
                 *
                 * @param {string} name
                 * @param {Object.<string, string>} params
                 * @param {boolean} absolute
                 * @return {string}
                 */
                generate: function(name, params, absolute) {
                    var route = (this.getRoute(name)),
                        params = params || {},
                        unusedParams = _.clone(params),
                        url = '',
                        optional = true,
                        host = '';

                    _.each(route.tokens, function(token) {
                        if ('text' === token[0]) {
                            url = token[1] + url;
                            optional = false;
                
                            return;
                        }
                
                        if ('variable' === token[0]) {
                            var hasDefault = _.has(route.defaults, token[3]);
                            if (false === optional || !hasDefault || (_.has(params, token[3]) && params[token[3]] != route.defaults[token[3]])) {
                                    var value;
                
                                    if (_.has(params, token[3])) {
                                        value = params[token[3]];
                                        _.remove(unusedParams, token[3]);
                                    } else if (hasDefault) {
                                        value = route.defaults[token[3]];
                                    } else if (optional) {
                                        return;
                                    } else {
                                        throw new Error('The route "' + name + '" requires the parameter "' + token[3] + '".');
                                    }
                
                                    var empty = true === value || false === value || '' === value;
                
                                    if (!empty || !optional) {
                                        var encodedValue = encodeURIComponent(value).replace(/%2F/g, '/');
                
                                        if ('null' === encodedValue && null === value) {
                                            encodedValue = '';
                                        }
                
                                        url = token[1] + encodedValue + url;
                                    }
                
                                    optional = false;
                                } else if (hasDefault) {
                                    _.remove(unusedParams, token[3]);
                                }
                
                                return;
                        }
                
                        throw new Error('The token type "' + token[0] + '" is not supported.');
                    });
                
                    if (url === '') {
                        url = '/';
                    }
                
                   _.each(route.hosttokens, function (token) {
                        var value;
                
                        if ('text' === token[0]) {
                            host = token[1] + host;
                
                            return;
                        }
                
                        if ('variable' === token[0]) {
                            if (_.has(params, token[3])) {
                                value = params[token[3]];
                                _.remove(unusedParams, token[3]);
                            } else if (_.has(route.defaults, token[3])) {
                                value = route.defaults[token[3]];
                            }
                
                            host = token[1] + value + host;
                        }
                    });
                
                    url = this.context.base_url + url;
                    if (_.has(route.requirements, "_scheme") && this.getScheme() != route.requirements["_scheme"]) {
                        url = route.requirements["_scheme"] + "://" + (host || this.getHost()) + url;
                    } else if (host && this.getHost() !== host) {
                        url = this.getScheme() + "://" + host + url;
                    } else if (absolute === true) {
                        url = this.getScheme() + "://" + this.getHost() + url;
                    }
                
                    if (_.size(unusedParams) > 0) {
                        var prefix;
                        var queryParams = [];
                        var add = function(key, value) {
                            // if value is a function then call it and assign it's return value as value
                            value = (typeof value === 'function') ? value() : value;
                
                            // change null to empty string
                            value = (value === null) ? '' : value;
                
                            queryParams.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
                        };
                
                        for (prefix in unusedParams) {
                            this.buildQueryParams(prefix, unusedParams[prefix], add);
                        }
                
                        url = url + '?' + queryParams.join('&').replace(/%20/g, '+');
                    }
                
                    return url;
                }

            };

            return RouterService;
        }
    ]);





