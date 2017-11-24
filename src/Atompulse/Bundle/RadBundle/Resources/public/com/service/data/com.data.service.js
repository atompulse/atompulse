/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */
try {
    angular.module('Web.Components')
} catch (err) {
    angular.module('Web.Components', [])
}

angular.module('Web.Components')
    .factory('Web.Components.DataService', ['$window',
        function ($window) {
            var ApplicationGlobalData = $window.Application || {};

            var DataService = {};

            /**
             * Get an Name Spaced Object from $window.Application
             * @param namespace
             * @returns {*}
             */
            DataService.get = function (namespace) {

                if (this.has(namespace)) {
                    return Fusion.retrieveNsObject(namespace, ApplicationGlobalData);
                }

                throw new Error("DataService::get [window.Application."+namespace+"] does not exist");
            };

            /**
             * Set an Name Spaced Object into $window.Application
             * @param namespace
             * @param value
             */
            DataService.set = function (namespace, value) {
                Fusion.registerNsObject(namespace, value, ApplicationGlobalData);
            };

            /**
             * Check if an Name Spaced Object exists in $window.Application
             * @param namespace
             * @returns {boolean}
             */
            DataService.has = function (namespace) {
                var exists = false;

                if (typeof(namespace) !== 'undefined') {
                    try {
                        exists = typeof(Fusion.retrieveNsObject(namespace, ApplicationGlobalData)) !== 'undefined';
                    } catch (exception) {
                    }
                }

                return exists;
            };

            return DataService;
        }
    ]);