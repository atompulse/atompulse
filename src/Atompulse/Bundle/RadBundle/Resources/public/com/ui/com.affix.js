/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try {
    angular.module('Web.Components')
} catch (err) {
    angular.module('Web.Components', [])
}
/**
 * USAGE: <div class="iphone" com-affix data-offset-top="200" data-offset-bottom="300">
            <div class="iphone-content">
            </div>
          </div>
 */
angular.module('Web.Components')
    .factory('Web.Components.StickyManager', [
        'dimensions',
        function (dimensions) {
            var $private = {
                containers: {},
                checkPosition : function (containerElement, stickyContainerElement, stickThisElement, options)
                {
                    options = options || {};

                    // container
                    var containerScrollTop = containerElement[0].scrollTop;
                    var containerPosition = dimensions.offset.call(containerElement[0]);
                    // target
                    var stickyContainerPosition = dimensions.offset.call(stickyContainerElement[0]);
                    var stickyContainerHeight = dimensions.height.call(stickyContainerElement[0]);

                    // settings
                    var offsetTop = typeof options.offsetTop == 'undefined' ? 15 : options.offsetTop;
                    var offsetBottom = typeof options.offsetBottom == 'undefined' ? 15 : options.offsetBottom;

                    //console.log($($(stickThisElement[0]).children()[0]).text());
                    //console.log('h:',stickyContainerHeight,' t:',stickyContainerPosition.top,'|',containerPosition.top,'|',containerScrollTop,'|');
                    //console.log('->', stickyContainerHeight + (stickyContainerPosition.top-containerPosition.top));

                    var start = stickyContainerPosition.top < containerPosition.top;
                    var stop = stickyContainerHeight + (stickyContainerPosition.top-containerPosition.top) < 0;

                    if (start && !stop) {
                        stickThisElement.addClass('affix');
                    } else {
                        stickThisElement.removeClass('affix');
                    }
                }
            };

            var StickyManager = {};

            StickyManager.registerContainer = function (containerName, containerElement)
            {
                $private.containers[containerName] = {
                    element: containerElement,
                    bindingDone: false,
                    stickies: []
                };
            };

            StickyManager.registerSticky = function (containerName, stickyContainerElement, stickThisElement)
            {
                if (typeof $private.containers[containerName] == 'undefined') {
                    throw 'Web.Components.StickyManager::registerSticky container ['+containerName+'] was not registered! Use com-sticky-container="container-name" to register a sticky container.';
                }

                // add listener
                $private.containers[containerName]['stickies'].push({
                    stickyContainerElement: stickyContainerElement,
                    stickThisElement: stickThisElement
                });

                // check if binding has been added for this container
                if (!$private.containers[containerName]['bindingDone']) {
                    angular.element($private.containers[containerName]['element']).bind('scroll', function () {
                       _.each ($private.containers[containerName]['stickies'], function (sticky) {
                            $private.checkPosition($private.containers[containerName]['element'], sticky.stickyContainerElement, sticky.stickThisElement);
                            //$private.checkCallbacks(listener.scope, listener.pin, listener.attrs);
                       });
                    });

                    $private.containers[containerName]['bindingDone'] = true;
                }
            };

            return StickyManager;
        }
    ])
    .directive('comStickyContainer', ['Web.Components.StickyManager',
        function (StickyManager) {
            var comStickyContainer = {
                restrict: 'A',
                link: function (scope, element, attrs) {
                    // register an sticky container - a box where scroll event is fired
                    StickyManager.registerContainer(attrs.comStickyContainer, element);
                }
            };

            return comStickyContainer;
        }
    ])
    .directive('comSticky', ['Web.Components.StickyManager',
        function (StickyManager) {
            var comSticky = {
                restrict: 'A',
                link: function (scope, stickyContainerElement, attrs) {
                    var stickThisElement = $(stickyContainerElement).find('[stick-this]')[0];
                    if (typeof stickThisElement == 'undefined') {
                        throw 'Web.Components.StickyManager: you need to mark an element inside the sticky with attribute [stick-this]';
                    }
                    StickyManager.registerSticky(attrs.comSticky, stickyContainerElement, angular.element(stickThisElement));
                }
            };
            
            return comSticky;
        }
    ])
    .provider('dimensions', function () {
        this.$get = function () {
            return this;
        };
        this.offset = function () {
            if (!this)
                return;
            var box = this.getBoundingClientRect();
            var docElem = this.ownerDocument.documentElement;
            return {
                top: box.top + window.pageYOffset - docElem.clientTop,
                left: box.left + window.pageXOffset - docElem.clientLeft
            };
        };
        this.height = function (outer) {
            var computedStyle = window.getComputedStyle(this);
            var value = this.offsetHeight;
            if (outer) {
                value += parseFloat(computedStyle.marginTop) + parseFloat(computedStyle.marginBottom);
            } else {
                value -= parseFloat(computedStyle.paddingTop) + parseFloat(computedStyle.paddingBottom) + parseFloat(computedStyle.borderTopWidth) + parseFloat(computedStyle.borderBottomWidth);
            }
            return value;
        };
        this.width = function (outer) {
            var computedStyle = window.getComputedStyle(this);
            var value = this.offsetWidth;
            if (outer) {
                value += parseFloat(computedStyle.marginLeft) + parseFloat(computedStyle.marginRight);
            } else {
                value -= parseFloat(computedStyle.paddingLeft) + parseFloat(computedStyle.paddingRight) + parseFloat(computedStyle.borderLeftWidth) + parseFloat(computedStyle.borderRightWidth);
            }
            return value;
        };
    })

;
