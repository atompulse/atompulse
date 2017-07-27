/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */

try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .factory('Web.Components.UriEngine', [
        '$state', '$transitions','$location',
        function ($state, $transitions, $location)
        {
            var UriEngine = {};

            var $private = {
                // the query string variable that holds the params as json
                urigin: '$',
                // state stack
                stateStack: {},
                // flush state stack only
                flashHistoryStates: {}
            };

            /*
            state stack is composed of items with this structure:
                stateName: {
                    // state
                    state: stateName,
                    // query string params
                    searchParams: {},
                    // state URI
                    path: null,
                    // actual params stored in '$' (urigin) parameter
                    params: {}
                }
            */

            /**
             * Injected method to provide backwards state transition with applied URI params
             * @param state
             */
            $state.goBackTo = function (state)
            {
                $state.keepHistory(state);
                $state.go(state);
            };

            /**
             * Notify URI engine that params for state should be kept
             * @param state
             */
            $state.keepHistory = function (state)
            {
                $private.flashHistoryStates[state] = true;
            };

            /**
             * Factory initialization routine
             */
            $private.init = function ()
            {
                /**
                 * Before transition hook:
                 * Flush previous state stack item if exists unless state is not in flashHistoryStates;
                 * Every other existent state stack items are always flushed just before being transitioned to.
                 */
                $transitions.onBefore({}, function (transition) {
                    var toState = transition.$to();
                    //console.log('onBefore transition', transition.$from(),'->',transition.$to());
                    if (!_.isUndefined($private.stateStack[toState.name]) && _.isUndefined($private.flashHistoryStates[toState.name])) {
                        delete $private.stateStack[toState.name];
                    }
                });

                /**
                 * After transitioning to state trigger state resolution
                 */
                $transitions.onSuccess({}, function (transition) {
                    var toState = transition.$to();
                    //console.log('onSuccess transition', transition.$from(),'->',transition.$to());
                    $private.resolveState(toState.name);
                });
            };

            /**
             * Perform state resolution
             * @param state
             */
            $private.resolveState = function (state)
            {
                if (_.isUndefined($private.stateStack[state])) {
                    $private.stateStack[state] = $private.stateParse(state);
                }
            };

            /**
             * Parse state
             * @param stateName
             * @returns {{state: *, searchParams: {}, path: null, params: {}}}
             */
            $private.stateParse = function (stateName)
            {
                var stateStackItem = {
                        // state
                        state: stateName,
                        // query string params
                        searchParams: {},
                        // state URI
                        path: null,
                        // actual params stored in '$' (urigin) parameter
                        params: {}
                    };

                var searchParams = $location.search();

                stateStackItem.path = $location.path();
                stateStackItem.searchParams = searchParams;

                if (!_.isUndefined(searchParams[$private.urigin])) {
                    try {
                        stateStackItem.params = JSON.parse(searchParams[$private.urigin]);
                    } catch (err) {
                        console.log('Web.Components.UriEngine::Failed to decode '+$private.urigin+' content:', searchParams[$private.urigin]);
                    }
                }

                return stateStackItem;
            };

            /**
             * Get state stack item
             * @returns {*}
             */
            $private.getStateStackItem = function ()
            {
                var state = $state.current.name;

                $private.resolveState(state);

                return $private.stateStack[state];
            };

            /**
             * Push parameters to the URI
             */
            $private.syncParams = function ()
            {
                var stateStackItem = $private.getStateStackItem();

                stateStackItem['searchParams'][$private.urigin] = JSON.stringify(stateStackItem.params);

                // update URI query string
                $location.search(stateStackItem.searchParams);
            };

            /**
             * Add parameter
             * @param paramName
             * @param paramValue
             * @param apply Modify URI or NOT
             */
            UriEngine.addParam = function (paramName, paramValue, apply)
            {
                apply = apply || true;

                var stateStackItem = $private.getStateStackItem();

                stateStackItem.params[paramName] = paramValue;

                if (apply) {
                    $private.syncParams();
                }
            };

            /**
             * Retrieve a parameter
             * @param paramName
             * @returns {*}
             */
            UriEngine.getParam = function (paramName)
            {
                var stateStackItem = $private.getStateStackItem();

                if (!_.isUndefined(stateStackItem.params[paramName])) {
                    return stateStackItem.params[paramName];
                }
            };

            /**
             * If parameters were added with apply=false then apply must be called before
             * the parameters are pushed to the URI
             */
            UriEngine.apply = function ()
            {
                $private.syncParams();
            };

            /**
             * Reset a parameters value to null OR resetValue if given
             * @param paramName
             * @param resetValue Optional
             * @param apply Modify URI or NOT
             */
            UriEngine.resetParam = function (paramName, resetValue, apply)
            {
                apply = apply || true;

                var stateStackItem = $private.getStateStackItem();

                if (!_.isUndefined(stateStackItem.params[paramName])) {
                    stateStackItem.params[paramName] = resetValue || null;
                    if (apply) {
                        $private.syncParams();
                    }
                }
            };

            /**
             * Remove a parameter
             * @param paramName
             * @param apply Modify URI or NOT
             */
            UriEngine.removeParam = function (paramName, apply)
            {
                apply = apply || true;

                var stateStackItem = $private.getStateStackItem();

                if (!_.isUndefined(stateStackItem.params[paramName])) {
                    delete stateStackItem.params[paramName];
                    if (apply) {
                        $private.syncParams();
                    }
                }
            };

            /**
             * Get all parameters
             * @returns {{}}
             */
            UriEngine.getParams = function ()
            {
                var stateStackItem = $private.getStateStackItem();

                return stateStackItem.params;
            };

            /**
             * Reset parameters values to null
             * @param apply Modify URI or NOT
             */
            UriEngine.resetParams = function (apply)
            {
                apply = apply || true;

                var stateStackItem = $private.getStateStackItem();

                _.each(stateStackItem.params, function (paramValue, paramName) {
                    stateStackItem.params[paramName] = null;
                });

                if (apply) {
                    $private.syncParams();
                }
            };

            /**
             * Remove all parameters
             * @param apply Modify URI or NOT
             */
            UriEngine.removeParams = function (apply)
            {
                apply = apply || true;

                var stateStackItem = $private.getStateStackItem();

                stateStackItem.params = {};

                if (apply) {
                    $private.syncParams();
                }
            };

            /**
             * Obtain an uri engine compatible URI to be used on generating cross module filtering
             * @param params Object
             * @param withQM Add automatically ?
             * @param externalHash Append to an existing hash (ui router state url maybe?)
             * @returns {*|string}
             */
            UriEngine.getUriForParams = function (params, withQM, externalHash)
            {
                params = params || {};
                withQM = withQM || true;
                externalHash = externalHash || '';

                if (withQM) {
                    externalHash += '?'+$private.urigin+'='+JSON.stringify(params);
                } else {
                    externalHash += '&'+$private.urigin+'='+JSON.stringify(params);
                }

                return externalHash;
            };

            // on singleton creation make sure service is initialized
            $private.init();

            return UriEngine;
        }
    ]);