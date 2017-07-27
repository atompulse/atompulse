/**
 * Simple password generator
 * Adapted from here https://github.com/sebastianha/angular-bootstrap-pwgen
 * @author Ionut Tudorel <ionut.tudorel@gmail.com>.
 */

try {
    angular.module('Web.Components')
} catch (err) {
    angular.module('Web.Components', [])
}

angular.module('Web.Components')
    .directive('comPasswordGenerator', [
        function () {
            return {
                scope: {
                    model: "=ngModel",
                    disabled: "=ngDisabled",
                    length: "@",
                    placeholder: "@",
                    autostart: "@"
                },
                require: "ngModel",
                restrict: "E",
                replace: "true",
                template: "" +
                "<div class=\"angular-bootstrap-pwgen\">" +
                "<div class=\"input-group\">" +
                "<span class=\"input-group-addon\">" +
                "<i class=\"fa fa-random\"ng-disabled=\"disabled\" ng-click=\"generatePasswordStart()\"></i>" +
                "</span>" +
                "<input class=\"form-control\" ng-model=\"password\" ng-disabled=\"disabled\"  placeholder=\"{{placeholder}}\">" +
                "</div>" +
                "</div>",
                link: function (scope, elem, attrs, modelCtrl) {

                    scope.passwordNotNull = false;
                    scope.$watch("password", function () {
                        scope.model = scope.password;
                        if (scope.password !== undefined && scope.password !== null && scope.password !== "") {
                            scope.passwordNotNull = true;
                        } else {
                            scope.passwordNotNull = false;
                        }
                    });

                    scope.generatePasswordStart = function () {
                        scope.password = scope.generatePassword(scope.length, false);
                    };

                    /*
                     * password-generator
                     * Copyright(c) 2011-2013 Bermi Ferrer <bermi@bermilabs.com>
                     * From https://github.com/bermi/password-generator
                     * MIT Licensed
                     */
                    scope.vowel = /[aeiouAEIOU]$/;
                    scope.consonant = /[bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ]$/;
                    scope.generatePassword = function (length, memorable, pattern, prefix) {
                        var char, n;
                        if (length === undefined || length === null) {
                            length = 10;
                        }
                        if (memorable === undefined || memorable === null) {
                            memorable = true;
                        }
                        if (pattern === undefined || pattern === null) {
                            pattern = /[a-zA-Z0-9]/;
                        }
                        if (prefix === undefined || prefix === null) {
                            prefix = "";
                        }
                        if (prefix.length >= length) {
                            return prefix;
                        }
                        if (memorable) {
                            if (prefix.match(scope.consonant)) {
                                pattern = scope.vowel;
                            } else {
                                pattern = scope.consonant;
                            }
                        }
                        n = (Math.floor(Math.random() * 100) % 94) + 33;
                        char = String.fromCharCode(n);
                        if (memorable) {
                            char = char.toLowerCase();
                        }
                        if (!char.match(pattern)) {
                            return scope.generatePassword(length, memorable, pattern, prefix);
                        }
                        return scope.generatePassword(length, memorable, pattern, "" + prefix + char);
                    };


                    // Generate password from the begining if autostart
                    if (typeof (attrs.autostart) !== 'undefined' && attrs.autostart) {
                        scope.password = scope.generatePassword(scope.length, false);
                    }
                }
            };
        }
    ]
);