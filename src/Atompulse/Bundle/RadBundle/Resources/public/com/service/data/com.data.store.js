/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .factory('Web.Components.DataStore', [
        function () {

            // requires https://github.com/nbubna/store
            if (typeof store == 'undefined') {
                throw "Web.Components.DataStore requires store.js to be loaded";
            }

            /**
             * Browser local storage service (com wrapper for store.js)
             */
            var DataStore = {

                /**
                 * Set a key/value in the browser's local storage
                 * @param key
                 * @param value
                 * @param lifetime
                 */
                set: function (key, value, lifetime) {
                    var record = {data: value, lifetime: parseInt(lifetime) * 1000 || false, date_added: new Date().getTime()};
                    store.set(key, record)
                },

                /**
                 * Check if key exists
                 * @param key
                 * @returns {*}
                 */
                has: function (key) {
                    return store.has(key);
                },

                /**
                 * Get value from key if exists
                 * @param key
                 * @returns {*}
                 */
                get: function (key) {
                    var record = store.get(key);
                    if (!record) {
                        return null;
                    }

                    var expired = !record.lifetime ? false : new Date().getTime() - record.date_added > record.lifetime;

                    if (expired) {
                        store.remove(key);
                        return null;
                    }

                    return record.data;
                }
            };

            return DataStore;
        }
]);