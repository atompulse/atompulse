/**!
 * AngularJS Plupload directive
 * @author Chungsub Kim <subicura@subicura.com>
 */

/* global plupload */
(function () {
    'use strict';

    angular.module('Web.Components')
        .provider('comUploadOption', function () {
            /* jshint camelcase: false */
            var opts = {
                url: 'upload.php',
                flash_swf_url: '../../../../js/vendor/plupload/plupload.flash.swf',
                silverlight_xap_url: '../../../../js/vendor/plupload/plupload.silverlight.xap',
                runtimes: 'html5, flash, silverlight, html4',
                max_file_size: '2mb',
                file_data_name: 'uploaded_file',
                filters: []
            };
            return {
                setOptions: function (newOpts) {
                    angular.extend(opts, newOpts);
                },
                $get: function () {
                    return opts;
                }
            };
        })
        .directive('comUpload', ['comUploadOption',
            function (comUploadOption) {
                function lowercaseFirstLetter(string) {
                    return string.charAt(0).toLowerCase() + string.slice(1);
                }
                function randomString(len, charSet) {
                    charSet = charSet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                    var randomString = '';
                    for (var i = 0; i < len; i++) {
                        var randomPoz = Math.floor(Math.random() * charSet.length);
                        randomString += charSet.substring(randomPoz, randomPoz + 1);
                    }

                    return randomString;
                }

                return {
                    scope: {
                        url: '=comUpload',
                        options: '=comUploadOptions',
                        callbacks: '=comUploadCallbacks'
                    },
                    /* jshint camelcase: false */
                    link: function postLink(scope, element, attrs) {

                        if (!attrs.id) {
                            var randomValue = randomString(5);
                            attrs.$set('id', randomValue);
                        }

                        var opts = comUploadOption;

                        opts.url = scope.url;
                        /* jshint unused: false */
                        opts.browse_button = attrs.id;
                        angular.extend(opts, scope.options);

                        var uploader = new plupload.Uploader(opts);

                        if (scope.callbacks) {
                            var callbackMethods = ['Init', 'PostInit', 'OptionChanged',
                                'Refresh', 'StateChanged', 'UploadFile', 'BeforeUpload', 'QueueChanged',
                                'UploadProgress', 'FilesRemoved', 'FileFiltered', 'FilesAdded',
                                'FileUploaded', 'ChunkUploaded', 'UploadComplete', 'Error', 'Destroy'];
                            angular.forEach(callbackMethods, function (method) {
                                var callback = (scope.callbacks[lowercaseFirstLetter(method)] || angular.noop);
                                uploader.bind(method, function () {
                                    callback.apply(null, arguments);
                                    if (!scope.$$phase && !scope.$root.$$phase) {
                                        scope.$apply();
                                    }
                                });
                            });
                        }
                        uploader.init();
                    }
                };
            }
        ]
    );
})();
