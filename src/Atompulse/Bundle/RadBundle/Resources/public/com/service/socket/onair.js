

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) };

/**
 * Web Socket Session Service
 */
angular.module('Web.Components')

    // SocketSession Service
    .factory('SocketSession', function() {

        var SocketSession = {

            settings: null,
            conn: null,

            publishersQueue: [],
            subscribers: [],

            active: false,
            calling: false,

            onConnect: function (conn) {

                SocketSession.conn = conn;
                SocketSession.active = true;
                SocketSession.calling = false;

                //console.log('Established SocketSession:', conn);

                if (_.size(SocketSession.subscribers)) {
                    //console.log('Launching Subscribe Promises');
                    _.each(SocketSession.subscribers, function (promise) {
                        promise();
                    });
                }

                if (_.size(SocketSession.publishersQueue)) {
                    //console.log('Launching Publish Promises ', SocketSession.publishersQueue);
                    _.each(SocketSession.publishersQueue, function (promise, idx) {
                        promise.fulfill();
                    });

                    SocketSession.publishersQueue = [];
                }
            },

            onClose: function (code, reason, detail) {
                SocketSession.active = false;
                SocketSession.calling = false;
                // WAMP SocketSession closed here ..
                //console.log('Closed SocketSession:', code, reason, detail);
            }
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
                    //console.log("On Channel:", channel, " Received event:", event);
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
                                exclude = [SocketSession.conn.SocketSessionid()];
                            } else {
                                eligible.push(SocketSession.conn.SocketSessionid());
                            }
                        }
                    }

                    //console.log("Publishing on Channel:", channel, " Event:", event, 'Params: excldude', exclude, ' eligible:', eligible);
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

            if (SocketSession.settings) {
                if (!SocketSession.active) {
                    if (!SocketSession.calling) {
                        // This should be handled in the config phase of ajs
                        ab.connect(SocketSession.settings.wsurl,
                            SocketSession.onConnect,
                            SocketSession.onClose,
                            // The SocketSession options
                            {
                                maxRetries: 1000,
                                retryDelay: 2000
                            }
                        );

                        SocketSession.calling = true;
                    }
                    // recheck later
                    setTimeout(SocketSession.initiate, 1000);
                }
            } else {
                // settings unavailable
            }
        };

        return SocketSession;
    });