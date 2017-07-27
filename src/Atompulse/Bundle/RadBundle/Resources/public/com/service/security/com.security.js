/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try {
    angular.module('Web.Components')
} catch (err) {
    angular.module('Web.Components', [])
}
/**
 * Security component
 */
angular.module('Web.Components')
    .factory('Web.Components.Security', [
        function ()
        {
            var Base64 = {_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9+/=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/rn/g,"n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}};

            var SecurityAdviser = {
                encoded: true,
                permissions: {}
            };

            /**
             * Initialize permissions
             * @param permissions List of permissions
             * @param encoded Should the system encode permissions
             */
            SecurityAdviser.init = function (permissions, encoded)
            {
                this.encoded = _.isUndefined(encoded) ? true : encoded;
                this.permissions = permissions;
            };

            /**
             *
             * @returns {boolean|Function|*}
             */
            SecurityAdviser.isUsingEncode = function ()
            {
                return this.encoded;
            };

            /**
             * Check if the permissions appears in the initialized permissions list
             * @param permissions Array
             * @param each boolean EACH permission is mandatory by default|false for ANY of
             * @param isNormalized If 'permissions' are normalized
             *
             * @returns {boolean}
             */
            SecurityAdviser.isGranted = function (permissions, each, isNormalized)
            {
                each = _.isUndefined(each) ? true : each;
                isNormalized = _.isUndefined(isNormalized) ? true : isNormalized;

                if (!isNormalized) {
                    permissions = this.normalizeValue(permissions);
                }

                if (each) {
                    return _.size(_.intersection(this.permissions, permissions)) == _.size(permissions);
                } else {
                    return _.size(_.intersection(this.permissions, permissions)) >= 1;
                }
            };

            /**
             * Encode a string
             * @param value
             * @returns {*}
             */
            SecurityAdviser.encode = function (value)
            {
                return Base64.encode(value);
            };

            /**
             * Decode a string
             * @param value
             * @returns {*}
             */
            SecurityAdviser.decode = function (value)
            {
                return  Base64.decode(value);
            };

            /**
             * Normalize value => ensure array structure and apply encoding if necessary
             * @param rawPermissions
             * @returns {Array}
             */
            SecurityAdviser.normalizeValue = function (rawPermissions)
            {
                var permissions = [];
                // string given ?
                if (!_.isArray(rawPermissions)) {
                    rawPermissions = [rawPermissions];
                }
                if (SecurityAdviser.isUsingEncode()) {
                    _.each(rawPermissions, function (permission) {
                        permissions.push(SecurityAdviser.encode(permission));
                    });
                } else {
                    permissions = rawPermissions;
                }

                return permissions;
            };

            return SecurityAdviser;
        }
    ]);

/**
 * Enable/Disable UI based on user permissions
 * Every permission is mandatory
 */
angular.module('Web.Components')
    .directive('isGranted', ['Web.Components.Security',
        function (SecurityAdviser)
        {

            var extractPermission;

            var isGranted = {
                transclude: 'element',
                priority: 600,
                terminal: true,
                restrict: 'A',
                link: function ($scope, $element, $attr, ctrl, $transclude)
                {
                    var childScope, permissions = [];

                    //// extract initial permissions
                    //var viewValue = $scope.$eval($attr.isGranted);
                    //var initialPermissions = SecurityAdviser.normalizeViewValue(viewValue ? viewValue : $attr.isGranted);
                    //// overwrite with encoded permissions
                    //if (SecurityAdviser.isUsingEncode()) {
                    //    $attr.$set('isGranted', JSON.stringify(initialPermissions));
                    //}

                    $attr.$observe('isGranted', function (value) {
                        var viewValue = $scope.$eval(value);
                        permissions = SecurityAdviser.normalizeValue(viewValue ? viewValue : value);

                        // check for EACH of the given permissions
                        if (SecurityAdviser.isGranted(permissions)) {
                            if (!childScope) {
                                childScope = $scope.$new();
                                $transclude(childScope, function (clone) {
                                    $element.replaceWith(clone);
                                });
                            }
                        } else {
                            if (childScope) {
                                childScope.$destroy();
                                childScope = null;
                            }
                        }
                    });
                }
            };

            return isGranted;
        }
    ]);

/**
 * Enable/Disable UI based on user permissions
 * ANY permission is sufficient
 */
angular.module('Web.Components')
    .directive('isGrantedAny', ['Web.Components.Security',
        function (SecurityAdviser)
        {
            var isGrantedAny = {
                transclude: 'element',
                priority: 600,
                terminal: true,
                restrict: 'A',
                link: function ($scope, $element, $attr, ctrl, $transclude)
                {
                    var childScope, permissions;

                    $attr.$observe('isGrantedAny', function (value) {
                        var viewValue = $scope.$eval(value);
                        permissions = SecurityAdviser.normalizeValue(viewValue ? viewValue : value);

                        // check for ANY of the given permissions
                        if (SecurityAdviser.isGranted(permissions, false)) {
                            if (!childScope) {
                                childScope = $scope.$new();
                                $transclude(childScope, function (clone) {
                                    $element.replaceWith(clone);
                                });
                            }
                        } else {
                            if (childScope) {
                                childScope.$destroy();
                                childScope = null;
                            }
                        }
                    });
                }
            };

            return isGrantedAny;
        }
    ]);