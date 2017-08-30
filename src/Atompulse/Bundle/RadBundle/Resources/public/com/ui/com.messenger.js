/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .directive('comMessenger', ['Web.Components.MessengerService',
        function (MessengerService) {
            var appMessenger = {
                restrict: 'E',
                template: '<div class="com-messenger [[options.layout]]">' +
                            '<div class="com-message [[msg.class]] [[options.msgCss]] [[animate(msg.state)]]" ' +
                                'ng-click="msg.state=!msg.state" ' +
                                'ng-show="msg.state" ' +
                                'ng-repeat="msg in messages" ' +
                                'ng-bind-html="msg.content">' +
                            '</div>' +
                          '</div>',
                replace: true,
                scope: {
                    channel: '@',
                    settings: '='
                },
                link: function (scope, element, attrs)
                {
                    if (typeof(scope.channel) === 'undefined') {
                        throw ('appMessenger requires a channel! Please add one using the attribute [channel]');
                    }

                    scope.messages = [];
                    scope.options = {};

                    // register the channel
                    MessengerService.registerChannel(scope);
                }
            };

            return appMessenger;
        }
    ])
    .factory('Web.Components.MessengerService', [
        function ()
        {
            var $private = {
                /**
                 * Accepted message types
                 */
                types: {
                    info:   'info',
                    warn:   'warning',
                    ok:     'success',
                    err:    'error'
                },
                /**
                 * Accepted layout placements
                 */
                layouts: {
                    'top-center': {
                        animate: {
                            in: 'fadeInDown animated',
                            out: 'fadeOutDown animated'
                        }
                    },
                    'top-full-width': {
                        animate: {
                            in: 'fadeInDown animated',
                            out: 'fadeOutDown animated'
                        }
                    },
                    'top-left': {
                        animate: {
                            in: 'fadeInDown animated',
                            out: 'fadeOutLeft animated'
                        }
                    },
                    'top-right': {
                        animate: {
                            in: 'fadeInDown animated',
                            out: 'fadeOutRight animated'
                        }
                    },
                    'host-top': {
                        animate: {
                            in: 'fadeInDown animated',
                            out: 'fadeOutDown animated'
                        }
                    },
                    'bottom-center': {
                        animate: {
                            in: 'fadeInUp animated',
                            out: 'fadeOutUp animated'
                        }
                    },
                    'bottom-full-width': {
                        animate: {
                            in: 'fadeInUp animated',
                            out: 'fadeOutUp animated'
                        }
                    },
                    'bottom-left': {
                        animate: {
                            in: 'fadeInUp animated',
                            out: 'fadeOutLeft animated'
                        }
                    },
                    'bottom-right': {
                        animate: {
                            in: 'fadeInUp animated',
                            out: 'fadeOutRight animated'
                        }
                    }
                }
            };

            var MessengerService = {
                channels: {}
            };

            /**
             * Register a channel
             * @param scope
             */
            MessengerService.registerChannel = function (scope)
            {
                if (typeof(this.channels[scope.channel]) === 'undefined') {

                    /**
                     * Default Options(Settings)
                     * @type {{layout: string, animate: {in: string, out: string}}}
                     */
                    var defaultOptions = {
                        layout: 'top-full-width',
                        animate: {
                            in: 'fadeInDown animated',
                            out: 'fadeOutDown animated'
                        },
                        msgCss: '',     // extra class to add for the message container
                        dt: 5           // default message display time 5 seconds
                    };

                    // create default settings for channel
                    if (typeof(scope.settings) === 'undefined') {
                        scope.options = defaultOptions;
                    } else {
                        scope.options = _.deepExtend(scope.settings, scope.options);
                        scope.options = _.deepExtend(defaultOptions, scope.options);
                    }

                    this.channels[scope.channel] = {
                        scope: scope
                    };

                } else {
                    throw 'Messenger Channel [' + channel + '] is already registered';
                }
            };

            /**
             * Send a message to a channel
             * @param channelName
             * @param msg
             * @param msgType
             * @param displayTime
             */
            MessengerService.send = function (channelName, msg, msgType, displayTime)
            {
                if (typeof(this.channels[channelName]) !== 'undefined') {

                    var $scope = this.channels[channelName].scope;

                    if (typeof(msgType) !== 'undefined') {
                        if (typeof($private.types[msgType]) !== 'undefined') {
                            msgType = $private.types[msgType];
                        } else {
                            throw 'Unrecognized message type: ['+msgType+']';
                        }
                    } else {
                        msgType = 'default';
                    }

                    var _cssClass = 'com-message-' + msgType;
                    var isSticky = false;

                    if (typeof(displayTime) !== 'undefined') {
                        if (_.isBoolean(displayTime)) {
                            if (displayTime) {
                                isSticky = true;
                            }
                        } else {
                            displayTime = displayTime * 1000;
                        }
                    } else {
                        displayTime = $scope.options.dt * 1000;
                    }

                    var message = {
                        content: msg,
                        class: _cssClass,
                        state: true
                    };

                    // hider
                    if (!isSticky) {
                        setTimeout(function () {
                            message.state = false;
                            $scope.$apply();
                        }, displayTime);
                    }

                    // push message
                    $scope.messages.push(message);

                    setTimeout(function () {
                        $scope.$apply();
                    }, 100);

                } else {
                    throw 'Messenger Channel [' + channelName + '] is not registered';
                }
            };

            /**
             * Clear all messages from a channel
             * @param channelName
             */
            MessengerService.clear = function (channelName)
            {
                if (typeof(this.channels[channelName]) !== 'undefined') {
                    var $scope = this.channels[channelName].scope;

                    $scope.messages = [];

                    setTimeout(function () {
                        $scope.$apply();
                    }, 100);

                } else {
                    throw 'Messenger Channel [' + channelName + '] is not registered';
                }
            };

            return MessengerService;
        }
    ]);