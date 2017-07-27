/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .directive('comHtmlProvider', ['$compile',
        function ($compile)
        {
            var Html = {
                restrict: 'A',
                scope: {
                    comHtmlProvider: '=',
                    ctrlScope: '='
                },
                link: function (scope, element, attrs)
                {
//                    console.log(scope, element, attrs);
                    var comProvider = scope.comHtmlProvider;
//                    console.log(comProvider);
                    var content = comProvider.provider.apply(comProvider.provider, comProvider.with);
//                    console.log('=>>', content, '::', typeof(content));

                    if (!_.isUndefined(content)) {
                        if (_.isObject(content) && content instanceof jQuery || !_.isUndefined(content.nodeType)) {
                            // perfect
                        } else {
    //                        console.log(String(content));
                            content = $.parseHTML(String(content));
                        }
                        // will create a new DOM element compiled with ctrlScope
                        // and replace the directives element
                        element.replaceWith($compile(content)(scope.ctrlScope));
                    } else {
                        element.replaceWith('');
                    }
                }
            };

            return Html;
        }
    ]
);