/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

/**
 * Web Socket Session Service
 */
angular.module('Web.Components')
    // SocketSession Service
    .factory('Web.Components.SocketSessionService', function()
    {
        /**
         * Socket Session Object
         * @type {{options: {maxRetries: number, retryDelay: number}, settings: {wsurl: null, onConnectCallback: null, onCloseCallback: null}, conn: null, publishersQueue: Array, subscribers: Array, active: boolean, calling: boolean}}
         */
        var SocketSession = {
            /**
             * socket session options
             */
            options: {
                maxRetries: 1000,
                retryDelay: 2000
            },
            settings: {
                wsurl: null,
                onConnectedCallback: null,
                onClosedCallback: null
            },

            /**
             * the active connection
             */
            conn: null,

            publishersQueue: [],
            subscribers: [],

            /**
             * session state
             */
            active: false,
            /**
             * trying to establish connection ?
             */
            calling: false
        };

        /**
         * Executed when connection was established
         * @param conn
         */
        SocketSession.onConnect = function (conn)
        {

            SocketSession.conn = conn;
            SocketSession.active = true;
            SocketSession.calling = false;


            if (_.size(SocketSession.subscribers)) {

                _.each(SocketSession.subscribers, function (promise) {
                    promise();
                });
            }

            if (_.size(SocketSession.publishersQueue)) {

                _.each(SocketSession.publishersQueue, function (promise, idx) {
                    promise.fulfill();
                });

                SocketSession.publishersQueue = [];
            }

            if (typeof(SocketSession.settings.onConnectedCallback) !== 'undefined') {
                SocketSession.settings.onConnectedCallback.call(SocketSession.settings.onConnectedCallback);
            }
        };

        /**
         * Executed when connection was closed
         * @param code
         * @param reason
         * @param details
         */
        SocketSession.onClose = function (code, reason, details)
        {
            SocketSession.active = false;
            SocketSession.calling = false;

            if (typeof(SocketSession.settings.onClosedCallback) !== 'undefined') {
                SocketSession.settings.onClosedCallback.call(SocketSession.settings.onClosedCallback, code, reason, details);
            }
            // WAMP SocketSession closed here ..
        };

        /**
         * Subscribe to a given channel, subsequently receive events published under the channel.
         * @param {string} channel An URI or CURIE of the channel to subscribe to.
         * @param {function} eventHandler The event handler to fire when receiving an event under the subscribed channel.
         * @returns {void}
         */
        SocketSession.subscribe = function (channel, eventHandler)
        {
            // Create a subscription promise to be used now and in the future
            // depending on the availability of the connection.
            var promise = function () {
                SocketSession.conn.subscribe(channel, function(channel, event) {
                    eventHandler.apply(eventHandler, [channel, event]);
                });
            };

            // register subsription promise
            SocketSession.subscribers.push(promise);

            // fulfill promise if there is an active connection
            if (SocketSession.active) {
                promise();
            }
        };

        /**
         * Unsubscribe any callback(s) currently subscribed to the the given channel.
         * @param {string} channel The URI or CURIE of the topic to unsubscribe from.
         * @returns {void}
         */
        SocketSession.unsubscribe = function (channel)
        {
            if (SocketSession.active) {
                SocketSession.conn.unsubscribe(channel);
            } else {
                throw "Session not active";
            }
        };

        /**
         * Publish the given event (which may be of simple type, or any JSON serializable object) to the given channel.
         * @param {string} channel The URI or CURIE of the topic to publish to.
         * @param {Object} event The event to be published.
         * @param {Array|boolean} exclude Explicit list of clients to exclude from this publication, given as array of SocketSession IDs
         *                                OR boolean to exclude/include itself
         * @param {Array} eligible Explicit list of clients that are eligible for this publication, given as array of SocketSession IDs.
         * @returns {void}
         */
        SocketSession.publish = function(channel, event, exclude, eligible)
        {
            // Create a publish promise to be used when an active connection is available
            // depending on the availability of the connection.
            var promise = {
                fulfill: function () {
                    if (_.isUndefined(eligible)) {
                        eligible = [];
                    }
                    if (_.isUndefined(exclude)) {
                        exclude = [];
                    } else {
                        if (_.isBoolean(exclude)) {
                            // explicit self exclusion
                            if (exclude) {
                                exclude = [SocketSession.conn.sessionid()];
                            } else {
                                eligible.push(SocketSession.conn.sessionid());
                            }
                        }
                    }

                    SocketSession.conn.publish(channel, event, exclude, eligible);

                    // promise is fulfilled
                    this.fulfilled = true;
                }
            };

            // fulfill promise if there is an active connection
            if (SocketSession.active) {
                promise.fulfill();
            } else {
                // keep promise
                SocketSession.publishersQueue.push(promise);
            }
        };

        /**
         * Runtime Settings
         * @param settings
         */
        SocketSession.initiate = function (settings)
        {
            if (!_.isUndefined(settings)) {
                SocketSession.settings = settings;
            }

            if (SocketSession.settings && SocketSession.settings.wsurl) {
                if (!SocketSession.active) {
                    if (!SocketSession.calling) {
                        // This should be handled in the config phase of ajs
                        ab.connect(SocketSession.settings.wsurl,
                                   SocketSession.onConnect,
                                   SocketSession.onClose,
                                   {
                                       maxRetries: SocketSession.options.maxRetries,
                                       retryDelay: SocketSession.options.retryDelay
                                   }
                        );

                        SocketSession.calling = true;
                    }
                    // recheck later
                    setTimeout(SocketSession.initiate, 1000);
                }
            } else {
                throw "SocketSession::initiate 'settings' are not valid: " + typeof(settings);
                // settings unavailable
            }
        };

        return SocketSession;
    });