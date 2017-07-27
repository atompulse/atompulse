/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch (err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .value('options', {
        text: 'Loading...', // Display text
        css: '' // Custom class, added to directive
    })
    .directive('comOverlay', ['$compile', 'options',
        function ($compile, options) {
            var comOverlay = {
                restrict: 'A',
                scope: {
                    comOverlay: '=',
                    comOverlayText: '@',
                    comOverlayCss: '@'
                },
                link: function (scope, element, attrs) {
                    var text = scope.comOverlayText || '';
                    var css = scope.comOverlayCss || '';

                    if (text.length > 0) {
                        text = '<br/><span style="padding: 10px">'+text+'</span>';
                    }

                    var loaderHtml = '<div ng-if="comOverlay" class="'+css+'"' +
                        'style="z-index:999;position:absolute;top:0;left:0;width:100%;height:100%;text-align:center;background:rgba(58, 63, 81, 0.3) none repeat scroll 0 0;color: #000">' +
                            '<div style="display:table;width:100%;height:100%">' +
                                '<div style="height:100%;display:table-cell;vertical-align:middle;text-align:center;">' +
                                    '<i class="fa fa-spin fa-cog fa-2x"></i>' +
                                    text +
                                '</div>' +
                            '</div>' +
                        '</div>';

                    loaderHtml = $.parseHTML(String(loaderHtml));
                    element.append($compile(loaderHtml)(scope));
                }
        };

        return comOverlay;
    }]);
