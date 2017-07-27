/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .factory('Web.Components.DataCache', [
        function ()
        {
            /**
             * Data Cache Service
             * @private
             */
            var DataCache = {
                /**
                 * Check if cache contains namespace.key cached value
                 * @param namespace
                 * @param key
                 * @returns {boolean}
                 */
                has: function (namespace, key) {
                    return !_.isUndefined(this.data[namespace]) && !_.isUndefined(this.data[namespace][key]);
                },
                /**
                 * Get a cached value from namespace.key
                 * @param namespace
                 * @param key
                 * @returns {*}
                 */
                get: function (namespace, key) {
                    return this.data[namespace][key];
                },
                /**
                 * Add a cached value under namespace.key
                 * @param namespace
                 * @param key
                 * @param value
                 */
                add: function (namespace, key, value) {
                    if (_.isUndefined(this.data[namespace])) {
                        this.data[namespace] = {};
                    }
                    this.data[namespace][key] = value;
                },
                /**
                 * Remove cache for a key
                 * @param namespace
                 * @param key
                 */
                remove: function (namespace, key) {
                    if (this.has(namespace, key)) {
                        delete this.data[namespace][key];
                    }
                },
                /**
                 * Clear namespace cache
                 * @param namespace
                 */
                clearNSCache: function (namespace) {
                    this.data[namespace] = {};
                },
                /**
                 * Clear all cache
                 */
                clearAll: function ()
                {
                    var $this = this;
                    _.each ($this.data, function (cacheData, nsKey) {
                        $this.data[nsKey] = {};
                    });
                },
                /**
                 * Data container
                 */
                data: {}
            };

            return DataCache;
        }
    ]);