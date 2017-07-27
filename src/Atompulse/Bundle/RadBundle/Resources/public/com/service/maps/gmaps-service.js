/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */
try {
    angular.module('Web.Components')
} catch (err) {
    angular.module('Web.Components', [])
}

angular.module('Web.Components')
    .factory('Web.Components.GoogleMap', [
        function () {

            var GMapService = {
                options: {
                    language: 'en',
                    latitude: '45.943161',
                    longitude: '24.96676',
                    zoom: 5
                }
            };

            var $private = {
                containerId: null,
                apiKey: false,
                gm: null,
                map: null,
                service: null,
                predService: null,
                infoWindow: null,
                placesCaches: {},
                markers: {},
                callbacks: {},
                geocoder: null,
                latlngbounds: null,
                latlng: null,
                animation: null,
                servicesReady: false,
                _stackedCallbacks: []
            };

            /**
             *
             * @param callback
             * @returns {string}
             */
            $private.buildApiUrl = function (callback)
            {
                var language = GMapService.options.language ? 'language='+GMapService.options.language : 'language=en';
                var apiKey = $private.apiKey ? '&key='+$private.apiKey+'&signed_in=true' : '&signed_in=false';

                var wsUrl = "https://maps.googleapis.com/maps/api/js?" + language + apiKey +
                          "&libraries=geometry,places&signed_in=false"+
                          "&callback="+callback;

                return wsUrl;
            };

            /**
             * Initialize Service/Map
             * @param callback
             */
            $private.init = function (callback)
            {
                var container = document.getElementById($private.containerId);

                if (!container || _.isUndefined(window.google)
                    || _.isUndefined(window.google.maps)
                    || _.isUndefined(window.google.maps.LatLng)
                    || _.isUndefined(window.google.maps.InfoWindow)
                    || _.isUndefined(window.google.maps.Geocoder)
                    || _.isUndefined(window.google.maps.LatLngBounds)
                    || _.isUndefined(window.google.maps.Animation)
                    || _.isUndefined(window.google.maps.places) ) {
                    //console.log ('Web.Components.GoogleMap: container with id ['+$private.containerId+'] not found, retrying in 1 sec');
                    setTimeout(function () {
                        $private.init(callback);
                    }, 1000);

                    return;
                }

                // get local copy
                $private.gm = $private.gm ? $private.gm : window.google.maps;

                var initialCenter = new $private.gm.LatLng(GMapService.options.latitude, GMapService.options.longitude);

                $private.map = new $private.gm.Map(container, {
                    center: initialCenter,
                    zoom: GMapService.options.zoom
                });

                // place service
                $private.service = $private.service ? $private.service : new $private.gm.places.PlacesService($private.map);

                // predictions service
                $private.predService = $private.predService ? $private.predService : new $private.gm.places.AutocompleteService();

                // Other usefully APIs
                $private.infoWindow = $private.infoWindow ? $private.infoWindow : new $private.gm.InfoWindow();
                $private.geocoder = $private.geocoder ? $private.geocoder : new $private.gm.Geocoder();
                $private.latlngbounds = $private.latlngbounds ? $private.latlngbounds : new $private.gm.LatLngBounds();
                $private.latlng = $private.latlng ? $private.latlng : new $private.gm.LatLng();

                $private.animation = $private.animation ? $private.animation : $private.gm.Animation;

                $private.servicesReady = true;

                if (typeof(callback) !== 'undefined' && callback !== null) {
                    callback.call(callback);
                }

                $private.runStackedCallbacks();
            };

            /**
             * Run callbacks when the map has been initialized & all the services are ready to be used
             */
            $private.runStackedCallbacks = function ()
            {
                _.each($private._stackedCallbacks, function (callback) {
                    callback.call(callback);
                });
            };

            /**
             * Add a callback to be executed when map is ready & services are ready
             * @param callback
             */
            $private.addStackedCallback = function (callback)
            {
                $private._stackedCallbacks.push(callback);
            };

            /**
             * Add marker on the map
             * @param location
             * @param markerOpts
             * @param callback
             */
            $private.addMarker = function (location, markerOpts, callback)
            {
                var fn = function () {
                    if (!_.isNull($private.gm)) {
                        var opts = {
                            position: location
                        };

                        _.extend(opts, markerOpts);

                        var marker = new $private.gm.Marker(opts);
                        marker.setMap($private.map);
                        marker.guid = _.uniqueId('marker_');

                        // store to cache
                        $private.markers[marker.guid] = marker;

                        if (!_.isUndefined(callback)) {
                            callback.call(callback, {marker: marker});
                        }
                    } else {
                        //TODO: should we return exception ?
                    }
                };

                if (!$private.servicesReady) {
                    $private.addStackedCallback(fn);
                } else {
                    return fn();
                }
            };

            /**
             * Clear a marker OR all markers
             * @param guid
             */
            $private.clearMarkers = function (guid)
            {
                guid = guid || false;
                if (guid) {
                    if (typeof($private.markers[guid]) != 'undefined') {
                        $private.markers[guid].setMap(null);
                        delete $private.markers[guid];
                    }
                } else {
                    _.each($private.markers, function (marker) {
                        marker.setMap(null);
                    });
                }
            };

            /**
             * Zoom on map
             * @param zoom
             */
            $private.setZoom = function (zoom)
            {
                $private.map.setZoom(zoom);
            };

            /**
             * Get place predictions
             * @param queryString String
             * @param types Array of values: 'geocode', '(regions)', '(cities)'
             * @param countryIsoCode String
             * @param callback
             */
            $private.getPlacePredictions = function (queryString, types, countryIsoCode, callback)
            {
                if ($private.predService) {
                    var params = {
                        input: queryString
                    };
                    if (countryIsoCode) {
                        params['componentRestrictions'] =  {country: countryIsoCode.toLowerCase()};
                    }
                    if (types) {
                        params['types'] = types;
                    }
                    //console.log("$private.getPlacePredictions params:", params);
                    $private.predService.getPlacePredictions(params, callback);
                } else {
                    throw 'Web.Components.GoogleMap::getPlacePredictions: Prediction Service Not Ready!';
                }
            };



            // Public Section ///////////////////////////////////////////

            /**
             *
             * @param containerId
             * @param options
             * @param callback
             * @param apiKey
             */
            GMapService.init = function (containerId, options, apiKey, callback)
            {
                $private.containerId = containerId;
                $private.apiKey = apiKey;
                $private._stackedCallbacks = [];
                $private.servicesReady = false;

                _.extend(GMapService.options, options);

                if (_.isNull($private.gm)) {
                    var wUrl = $private.buildApiUrl('initGMPCallback');

                    window.initGMPCallback = function () {
                        $private.init(callback);
                    };

                    head.load([wUrl], function () {});
                } else {
                    $private.init(callback);
                }
            };

            /**
             * Get details for a place id
             * @param placeId
             * @param callback
             */
            GMapService.getPlaceDetails = function (placeId, callback)
            {
                var fn = function () {
                    if (!_.isNull($private.service)) {

                        if (typeof($private.placesCaches[placeId]) === 'undefined') {

                            var params = {
                                placeId: placeId
                            };

                            $private.service.getDetails(params, function (place, status) {
                                if (status == $private.gm.places.PlacesServiceStatus.OK) {
                                    // store to cache
                                    $private.placesCaches[placeId] = place;
                                    if (typeof(callback) !== 'undefined') {
                                        callback.call(callback, place);
                                    }
                                } else {
                                    throw "Web.Components.GoogleMap:::getPlaceDetails: " + status;
                                }
                            });
                        } else {
                            if (typeof(callback) !== 'undefined') {
                                callback.call(callback, $private.placesCaches[placeId]);
                            }
                        }
                    } else {
                        //TODO: should we return exception ?
                    }
                };

                if (!$private.servicesReady) {
                    $private.addStackedCallback(fn);
                } else {
                    return fn();
                }
            };

            /**
             * Get an administrative geo location name format from a place
             * @param place
             * @returns {*}
             */
            GMapService.getPlaceGeoLocationName = function (place)
            {
                var geoLocationName = place.name;

                  if (!_.isUndefined(place.address_components)) {
                    var parts = [];
                    _.each (place.address_components, function (addressComp) {
                        // long_name is > 0
                        // was not already added
                        // is not one of postal_code,route,street_number types
                        if (addressComp.long_name.length > 0 &&
                            _.indexOf(parts, addressComp.long_name) == -1 &&
                            _.indexOf(addressComp.types, 'postal_code') == -1 &&
                            _.indexOf(addressComp.types, 'route') == -1 &&
                            _.indexOf(addressComp.types, 'street_number') == -1 &&
                            _.size(parts) < 4) {
                            parts.push(addressComp.long_name);
                        }
                    });
                    geoLocationName = parts.join(', ');
                } else {
                    geoLocationName = place.formatted_address;
                }

                return geoLocationName;
            };

            /**
             * Display markers for a list of place ids
             * @param placeIds
             * @param callback
             */
            GMapService.displayMarkersForPlaces = function (placeIds, callback)
            {
                var fn = function () {
                    if (!_.isNull($private.service)) {
                        // clear previous markers
                        GMapService.clearMarkers();
                        // show new markers
                        _.each(placeIds, function (placeId) {
                            GMapService.getPlaceDetails(placeId, function (place) {
                                GMapService.addPlaceMarker(place, {}, callback);
                            });
                        });
                    } else {
                        //TODO: should we return exception ?
                    }
                };

                if (!$private.servicesReady) {
                    $private.addStackedCallback(fn);
                } else {
                    return fn();
                }
            };

            /**
             * Adds a marker on a place id and centers the map around that place
             * @param placeId
             * @param zoom
             * @param callback
             * @param markerOpts
             */
            GMapService.gotoPlaceOnMap = function (placeId, zoom, callback, markerOpts)
            {
                GMapService.getPlaceDetails(placeId, function (place) {
                    if (place) {
                        markerOpts = markerOpts || {};
                        GMapService.clearMarkers();
                        GMapService.addPlaceMarker(place, markerOpts, callback);
                        GMapService.centerMap(place.geometry.location);
                        GMapService.setZoomLevel(typeof(zoom) !== 'undefined' ? zoom : GMapService.options.zoom);
                    } else {
                        throw 'Web.Components.GoogleMap::gotoPlaceOnMap: Place couldnt not be retrieved by placeId=['+placeId+']';
                    }
                });
            };

            /**
             * Adds a marker on a geo point and centers the map around that place
             * @param point {lat, lng}
             * @param zoom
             * @param callback
             * @param markerOpts
             */
            GMapService.gotoPointOnMap = function (point, zoom, callback, markerOpts)
            {
                if (_.isString(point.lat)) {
                    point.lat = parseFloat(point.lat);
                }
                if (_.isString(point.lng)) {
                    point.lng = parseFloat(point.lng);
                }

                markerOpts = markerOpts || {};
                GMapService.clearMarkers();
                GMapService.centerMap(point);
                GMapService.addMarker(point, markerOpts, callback);
                GMapService.setZoomLevel(typeof(zoom) !== 'undefined' ? zoom : GMapService.options.zoom);
            };

            /**
             * Search for places
             * @param queryString String
             * @param params {countryIsoCode: isoCode, types: ['geocode', '(regions)', '(cities)']}
             * @param callback Function
             */
            GMapService.searchLocation = function (queryString, params, callback)
            {
                var countryIsoCode = false,
                    types = false;

                if (_.isObject(params)) {
                    if (!_.isUndefined(params['types'])) {
                        types = params['types'];
                    }
                    if (!_.isUndefined(params['countryIsoCode'])) {
                        countryIsoCode = params['countryIsoCode'];
                    }
                } else {
                    if (_.isString(params)) {
                        countryIsoCode = params;
                    }
                }

                 var fn = function () {
                    if (!_.isNull($private.service)) {
                        $private.getPlacePredictions(queryString, types, countryIsoCode, callback);
                    } else {
                        //TODO: should we return exception ?
                    }
                };

                if (!$private.servicesReady) {
                    $private.addStackedCallback(fn);
                } else {
                    return fn();
                }
            };

            /**
             * Geocode an address
             * @param queryString -string
             * @param callback
             */
            GMapService.geocode = function (queryString, callback)
            {
                var fn = function () {
                    if (!_.isNull($private.service)) {
                        $private.geocoder.geocode({'address': queryString}, function (results, status) {
                            if (!_.isUndefined(callback)) {
                                callback.call(callback, {status: status, result: results});
                            }
                        });
                    }
                };

                if (!$private.servicesReady) {
                    $private.addStackedCallback(fn);
                } else {
                    return fn();
                }
            };

            /**
             * Reverse geocode an address
             * @param latLng - latlng gmaps object
             * @param callback
             */
            GMapService.reverseGeocode = function (latLng, callback)
            {
                var fn = function () {
                    $private.geocoder.geocode({'location': latLng}, function (results, status) {
                        if (!_.isUndefined(callback)) {
                            callback.call(callback, {status: status, result: results});
                        }
                    });
                };

                if (!$private.servicesReady) {
                    $private.addStackedCallback(fn);
                } else {
                    return fn();
                }
            };

            /**
             * Center map to a specific location
             * @param LatLng object
             */
            GMapService.centerMap = function (LatLng)
            {
                var fn = function () {
                    if (!_.isNull($private.map)) {
                        if (_.isString(LatLng.lat)) {
                            LatLng.lat = parseFloat(LatLng.lat);
                        }
                        if (_.isString(LatLng.lng)) {
                            LatLng.lng = parseFloat(LatLng.lng);
                        }
                        $private.map.setCenter(LatLng);
                    } else {
                        //TODO: should we return exception ?
                    }
                };

                if (!$private.servicesReady) {
                    $private.addStackedCallback(fn);
                } else {
                    return fn();
                }
            };

            /**
             * Set zoom level of the map
             * @param zoom - integer zoom level
             */
            GMapService.setZoomLevel = function (zoom)
            {
                var fn = function () {
                    if (!_.isNull($private.map)) {
                        $private.map.setZoom(zoom || 10);
                    } else {
                        //TODO: should we return exception ?
                    }
                };

                if (!$private.servicesReady) {
                    $private.addStackedCallback(fn);
                } else {
                    return fn();
                }
            };

            /**
             * Add marker at a specific location on map
             * @param {lat: null, lng: null} location
             * @param markerOpts - marker extra options
             * @param callback - callback on marker (return marker on callback (in order to add listeners on it if needed))
             */
            GMapService.addMarker = function (location, markerOpts, callback)
            {
                if (_.isString(location.lat)) {
                    location.lat = parseFloat(location.lat);
                }
                if (_.isString(location.lng)) {
                    location.lng = parseFloat(location.lng);
                }

                if (_.isNumber(location.lat) && _.isNumber(location.lng)) {
                    return $private.addMarker(location, markerOpts, callback);
                } else {
                    throw 'Web.Components.GoogleMap::addPlaceMarker: Invalid point given';
                }
            };

            /**
             *
             * @param place
             * @param markerOpts
             * @param callback
             */
            GMapService.addPlaceMarker = function (place, markerOpts, callback)
            {
                if (place && typeof(place.geometry.location) != 'undefined') {
                    return $private.addMarker(place.geometry.location, markerOpts, callback);
                } else {
                    throw 'Web.Components.GoogleMap::addPlaceMarker: Place is invalid or does not have a location';
                }
            };

            /**
             * Add an event listener on a marker
             * @param marker
             * @param event
             * @param callback
             */
            GMapService.addMarkerEventListener = function (marker, event, callback)
            {
                var fn = function () {
                    if (!_.isNull($private.gm)) {
                        $private.gm.event.addListener(marker, event, callback);
                    } else {
                        //TODO: should we return exception ?
                    }
                };

                if (!$private.servicesReady) {
                    $private.addStackedCallback(fn);
                } else {
                    return fn();
                }
            };

            /**
             * Getters for the private members
             * @returns {null}
             */
            GMapService.getGMap = function () {
                return $private.gm;
            };

            /**
             *
             * @returns {null}
             */
            GMapService.getMap = function () {
                return $private.map;
            };

            /**
             *
             * @returns {null}
             */
            GMapService.getInfoWindow = function () {
                return $private.infoWindow;
            };

            /**
             *
             * @returns {null}
             */
            GMapService.getLatLng = function ()
            {
                return $private.latlng;
            };

            /**
             *
             * @returns {null}
             */
            GMapService.getAnimation = function ()
            {
                return $private.animation;
            };

            /**
             * @markerId - if id given then delete that specific marker, otherwise delete all
             */
            GMapService.clearMarkers = function (markerId) {
                $private.clearMarkers(markerId);
            };

            return GMapService;
        }]);
