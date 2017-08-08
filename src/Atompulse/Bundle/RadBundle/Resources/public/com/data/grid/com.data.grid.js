/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */
try { angular.module('Web.Components') } catch(err) { angular.module('Web.Components', []) }

angular.module('Web.Components')
    .factory('Web.Components.DataGridManager', ['$state','$cookies','$sce','$compile','Web.Components.UriEngine',
        function ($state, $cookieStore, $sce, $compile, UriEngine)
        {
            var DataGridManager = {

                /**
                 * NON Singleton Object Instance
                 * @param $iniOptions
                 * @param $initCallback
                 * @returns {*}
                 * @constructor
                 */
                Instance : function ($iniOptions, $initCallback)
                {
                    $iniOptions = $iniOptions || {};

                    /*
                    Supported ini options
                    $iniOptions = {
                        uri_engine_pns => string : If a name is given then parameters will be also stored in a cookie
                    };
                    */

                    var $this = {

                        grid: {
                            scope: null,
                            title: null,
                            metaData: null
                        },

                        dataSource: null,

                        filters: {},

                        sorters: {},

                        pagination: {
                            page: 1,
                            size: 10,
                            total: 0,
                            available: 0,
                            pageRanges: [],
                            pageTotal: 0,
                            needsPagination: false
                        },

                        dsResponseHandler: null
                    };

                    var $private = {
                        guid: 'gi',
                        // external dependencies
                        ctrl: {
                            alias: '$ctrl',
                            scope: null,
                            instance: null
                        },
                        // data
                        rawData: null, // original data received from datasource
                        data: null, // data used for rendering
                        indexedData: null, // data indexed by row.uid
                        referenceMap: null, // a map from row.uid to row set key to allow updating of rows in all 3 sources (rawData, data, indexedData)
                        headerData: null,
                        params: {},
                        persistentParams: false,
                        rowActionRenderers: {},
                        rowActionSeparators: {
                            prefix: '<span class="com-grid-action-separator">',
                            suffix: '</span>'
                        },
                        rowCellRenderers: {},
                        rowCssClassInjector: false,
                        paginationListeners: [],
                        sorterListeners: [],
                        // settings
                        uriEngineStatus: true,
                        uriEnginePersistentNameSpace: false,
                        // caches
                        __renderViewCache: {row_cell: {}, row_css: {}, row_action: {}, column_row: {}, column_css: {}, column_header_css: {}},
                        __actionsWithRenderers: null,
                        __countActionsWithRenderers: null,
                        __countVisibleColumns: null,
                        __columns: null,
                        __paginationSizes: null,
                        __defaultPageSize: 10
                    };

                    /**
                     * Initializer method
                     * @param $iniOptions
                     * @param $initCallback
                     * @returns {*}
                     */
                    $this.init = function ($iniOptions, $initCallback)
                    {
//                        // handle persistent params restoration : currently only pagination details are stored & handled
//                        if ($state.params.dsp && $state.params.dsp == '!') {
//                            var dsParams = $cookieStore.get('dsp');
//                            if (!_.isUndefined(dsParams)) {
//                                dsParams = angular.fromJson(dsParams);
//                                _.each(dsParams, function (params) {
//                                    switch (params['name']) {
//                                        case 'data-filters' :
////                                            if (_.size(params['value']) > 0) {
////                                                $this.filters = params['value'];
////                                            }
//                                        break;
//                                        case 'data-sorters' :
//                                        break;
//                                        case 'iDisplayStart' :
//                                            $this.pagination.page = params['value'];
//                                        break;
//                                        case 'iDisplayLength' :
//                                            $this.pagination.size = params['value'];
//                                        break;
//                                    }
//                                });
//                                $private.persistentParams = true;
//                            }
//                        }

                        if (!_.isUndefined($initCallback) && _.isFunction($initCallback)) {
                            $initCallback.apply($this, [$iniOptions, $this, $private]);
                        } else {
                            return $private.init.apply($this, [$iniOptions]);
                        }

                        return $this;
                    };

                    /**
                     * Set the scope that will be used when rendering cells/actions
                     * @param ctrlScope
                     * @returns {*}
                     */
                    $this.setControllerScope = function (ctrlScope)
                    {
                        $private.ctrl['scope'] = ctrlScope;

                        return $this;
                    };

                    /**
                     * Return the current controller scope
                     * @returns {*}
                     */
                    $this.getControllerScope = function ()
                    {
                        return $private.ctrl['scope'];
                    };

                    /**
                     * Set the parent controller
                     * @param ctrl
                     * @param ctrlScope
                     * @param alias
                     * @returns {*}
                     */
                    $this.setController = function (ctrl, ctrlScope, alias)
                    {
                        alias = alias || $private.ctrl.alias;
                        ctrlScope = ctrlScope || $private.ctrl.scope;

                        $private.ctrl['alias'] = alias;
                        $private.ctrl['instance'] = ctrl;
                        $private.ctrl['scope'] = ctrlScope;

                        return $this;
                    };

                    /**
                     * Return the current controller scope
                     * @returns {*}
                     */
                    $this.getController = function (withAllData)
                    {
                        withAllData = withAllData || false;

                        return withAllData ? $private.ctrl : $private.ctrl['instance'];
                    };

                    /**
                     * Return the actions that have renderers registered
                     * @returns {{}}
                     */
                    $this.getActions = function ()
                    {
                        if ($private.__actionsWithRenderers === null) {
                            var actionOrderIdx = 0;
                            // check if there are action specified in meta data & register them
                            if (_.size($this.grid.metaData.rowActions) > 0) {
                                if ($private.ctrl.scope || $private.ctrl.instance) {
                                    _.each($this.grid.metaData.rowActions, function (actionSettings, actionName) {
                                        if (actionSettings['render']) {
                                            var hasCtrlInstanceRenderer = $private.ctrl.instance && !_.isUndefined($private.ctrl.instance[actionSettings['render']]),
                                                hasCtrlScopeRenderer = $private.ctrl.scope && !_.isUndefined($private.ctrl.scope[actionSettings['render']]);
                                            // supported renderers: on $ctrl instance OR $scope of the $ctrl
                                            if (hasCtrlInstanceRenderer || hasCtrlScopeRenderer) {
                                                // use the renderer that was found
                                                if (hasCtrlInstanceRenderer) {
                                                    $this.addRowActionRenderer(actionName, $private.ctrl.instance[actionSettings['render']], actionSettings['with']);
                                                } else {
                                                    $this.addRowActionRenderer(actionName, $private.ctrl.scope[actionSettings['render']], actionSettings['with']);
                                                }
                                            }
                                            else {
                                                throw "DataGridManager::Renderer ["+actionSettings['render']+"] for action ["+actionName+"] was not found on $ctrl OR $ctrl's $scope";
                                            }
                                        } else {
                                            throw "DataGridManager::Renderer for action ["+actionName+"] is not specified. Forgot to set 'render'?";
                                        }
                                    });
                                } else {
                                    throw "DataGridManager::Actions found in meta data but controller scope is null";
                                }
                            }

                            // order and prepare cached actions
                            if (_.size($private.rowActionRenderers) > 0) {
                                $private.__actionsWithRenderers = [];
                                _.each($private.rowActionRenderers, function (actionSettings, actionName) {
                                    $private.__actionsWithRenderers.push(actionSettings);
                                });
                            }
                        }

                        return $private.__actionsWithRenderers;
                    };

                    /**
                     * Get the number of actions
                     * @returns {*}
                     */
                    $this.getCountActions = function ()
                    {
                        if ($private.__countActionsWithRenderers == null) {
                            $private.__countActionsWithRenderers = _.size($this.getActions());
                        }

                        return $private.__countActionsWithRenderers;
                    };

                    /**
                     * Get the number of visible columns
                     * @returns {*}
                     */
                    $this.getCountColumns = function ()
                    {
                        if ($private.__countVisibleColumns === null) {
                            $private.__countVisibleColumns = 0;
                            _.each($this.grid.metaData.header, function (headerDefinition) {
                                // set visible column count
                                $private.__countVisibleColumns += (headerDefinition.visible ? 1 : 0);
                            });
                        }

                        return $private.__countVisibleColumns;
                    };

                    /**
                     * Get the number of non-system columns
                     * ! Ignores visibility !
                     * @returns {*}
                     */
                    $this.getCountManageableColumns = function ()
                    {
                        return _.size($this.getManageableColumns());
                    };

                    /**
                     * Get the paginations elements
                     * @returns {*}
                     */
                    $this.getPagination = function ()
                    {
                        return $this.pagination;
                    };

                    /**
                     * Get the pagination sizes
                     * @returns {*[]}
                     */
                    $this.getPaginationSizes = function ()
                    {
                        if ($private.__paginationSizes == null) {
                            $private.__paginationSizes = [
                                {label: '10',   value: 10},
                                {label: '25',   value: 25},
                                {label: '50',   value: 50},
                                {label: '75',   value: 75}
//                            {label: '100',   value: 100},
                            ];
                        }

                        return $private.__paginationSizes;
                    };

                    /**
                     * Add pagination change listener(callback)
                     * @param callback
                     */
                    $this.registerPaginationListener = function (callback)
                    {
                        $private.paginationListeners.push(callback);
                    };

                    /**
                     * Go to a specific page
                     * @param page
                     */
                    $this.goToPage = function (page)
                    {
                        var pageIn = _.clone($this.pagination.page);

                        if (typeof(page) === 'string') {
                            switch (page) {
                                case 'first' :
                                    $this.pagination.page = 0;
                                    break;
                                case 'previous' :
                                    if ($this.pagination.page >= 1) {
                                        $this.pagination.page--;
                                    }
                                    break;
                                case 'next' :
                                    if ($this.pagination.page < $this.pagination.pageTotal) {
                                        $this.pagination.page++;
                                    }
                                    break;
                                case 'last' :
                                    $this.pagination.page = $this.pagination.pageTotal;
                                    break;
                            }
                        } else {
                            $this.pagination.page = page;
                        }

                        if (pageIn !== $this.pagination.page) {
                            $this.reload();
                        }

                        // check for pagination listeners and execute them if any
                        if (_.size($private.paginationListeners) > 0) {
                            _.each ($private.paginationListeners, function (listener) {
                                if (_.isFunction(listener)) {
                                    listener.apply(listener, [$this.pagination]);
                                }
                            });
                        }
                    };

                    /**
                     * Set default page size
                     * @param size
                     */
                    $this.setDefaultPageSize = function (size)
                    {
                        size = parseInt(size);

                        $this.pagination.size = size;
                        // set this as default pagination size
                        $private.__defaultPageSize = size;

                        if (!_.findWhere($this.getPaginationSizes(), {value: size})) {
                            var pagination = {label: size, value: size};
                            // add the new set on first position
                            $private.__paginationSizes.unshift(pagination);
                        }
                    };

                    /**
                     * Set all grid config
                     * @param grid
                     */
                    $this.setGridConfig = function (grid)
                    {
                        _.deepExtend($this.grid, grid);

                        return $this;
                    };

                    /**
                     * Set grid data
                     * @param data
                     */
                    $this.setData = function (data)
                    {
                        //console.log('data received:', data, moment().format('h:mm:ss a'));
                        // check for datatables like structure specific for Atompulse\Component\Grid\DataGrid
                        if (!_.isUndefined(data.ds.data)) {
                            $private.prepareGridData(data.ds.data);
                            $private.setPaginationData(
                                {
                                    total:              parseInt(data.ds.meta.total),
                                    available:          parseInt(data.ds.meta.total_available),
                                    page:               parseInt(data.ds.meta.page),
                                    pageTotal:          parseInt(data.ds.meta.total_pages),
                                    pageRanges:         data.ds.meta.pages,
                                    needsPagination:   data.ds.meta.have_to_paginate
                                }
                            );
                        } else {
                            throw "DataGridManager : given data is not in the correct format";
                        }

                        // clear data related cache
                        $private.__renderViewCache['row_cell'] = {};
                        $private.__renderViewCache['row_css'] = {};
                        $private.__renderViewCache['row_action'] = {};

                        // trigger apply
                        //$this.grid.scope.$applyAsync();
                        var $scope = $this.getControllerScope() || $this.grid.scope;
                        $scope.$applyAsync();

                        return $this;
                    };

                    /**
                     * Return grid data
                     * @returns {*}
                     */
                    $this.getGridData = function ()
                    {
                        return $private.data;
                    };

                    /**
                     * Return the grid raw data
                     * @returns {*}
                     */
                    $this.getRawData = function ()
                    {
                        return $private.rawData;
                    };

                    /**
                     * Check if grid data
                     * @returns {boolean}
                     */
                    $this.hasData = function ()
                    {
                        return _.size($private.data) > 0;
                    };

                    /**
                     * Set grid title
                     * @param title
                     */
                    $this.setTitle = function (title)
                    {
                        $this.grid.title = title;

                        return $this;
                    };

                    /**
                     * Set the grid metadata
                     * @param metaData
                     * @returns {*}
                     */
                    $this.setMetaData = function (metaData)
                    {
                        $this.grid.metaData = angular.copy(metaData);

                        return $this;
                    };

                    /**
                     * Set the DataSource that will be used by the grid manager
                     * @param ds
                     * @returns {*}
                     */
                    $this.setDataSource = function (ds)
                    {
                        $this.dataSource = ds;
                        // set default response handler
                        $this.setDataSourceResponseHandler($private.onHandleDataSourceResponse);

                        return $this;
                    };

                    /**
                     * Get the data source state
                     * @returns {boolean|message.state|$this.state|$this.grid.state|Object|state|*}
                     */
                    $this.getDataSourceState = function ()
                    {
                        return $this.dataSource.state;
                    };

                    /**
                     * Get the raw parameters from the data source
                     * @returns {{}|*}
                     */
                    $this.getRawDsParams = function ()
                    {
                        return $this.dataSource.getParams();
                    };

                    /**
                     * Set the DataSource response handler
                     * @param dsResponseHandler
                     * @returns {*}
                     */
                    $this.setDataSourceResponseHandler = function (dsResponseHandler)
                    {
                        if (!_.isUndefined(dsResponseHandler) && _.isFunction(dsResponseHandler)) {
                            $this.dsResponseHandler = dsResponseHandler;
                        } else {
                            throw "DataGridManager:: Invalid DataSource Response Handler [" + dsResponseHandler + "]";
                        }

                        return $this;
                    };

                    /**
                     * Enable/Disable UriEngine
                     * @param status
                     */
                    $this.enableUriEngine = function (status)
                    {
                        status = !_.isUndefined(status) ? status : true;
                        $private.uriEngineStatus = status;
                    };

                    /**
                     * Add a filter to be observed and used to filter the data
                     * @param filterName
                     * @param filterObjContainer
                     * @param reloadOnChange
                     */
                    $this.trackFilter = function (filterName, filterObjContainer, reloadOnChange)
                    {
                        if (!_.isUndefined(filterObjContainer[filterName])) {
                            if (!_.isUndefined(filterObjContainer[filterName]) && filterObjContainer[filterName] != null) {
                                $this.filters[filterName] = filterObjContainer[filterName];

                                if (!_.isUndefined(reloadOnChange)) {
                                    reloadOnChange = true;
                                }
                            }

                            // watch filter for changes
                            watch(filterObjContainer, filterName, function (prop, act, newValue) {
                                if (!_.isUndefined($this.filters[filterName]) && newValue != $this.filters[filterName]) {
                                    $this.filters[filterName] = newValue;
                                    if (reloadOnChange) {
                                        $this.reload();
                                    }
                                } else {
                                    $this.filters[filterName] = newValue;
                                    if (reloadOnChange) {
                                        $this.reload();
                                    }
                                }
                            });
                        }
                    };

                    /**
                     * Set a filter to filter the data
                     * @param filterName
                     * @param filterValue
                     * @param reload
                     * @returns {*}
                     */
                    $this.setFilter = function (filterName, filterValue, reload)
                    {
                        $this.filters[filterName] = filterValue;

                        // reload on demand
                        if (!_.isUndefined(reload) && reload) {
                            $this.reload();
                        }

                        return $this;
                    };

                    /**
                     * Clear a filter by removing it
                     * @param filterName
                     * @param reload
                     * @returns {*}
                     */
                    $this.removeFilter = function (filterName, reload)
                    {
                        delete $this.filters[filterName];

                        // reload on demand
                        if (!_.isUndefined(reload) && reload) {
                            $this.reload();
                        }

                        return $this;
                    };

                    /**
                     * Check if a specific filter exists
                     * @param filterName
                     * @returns {boolean}
                     */
                    $this.hasFilter = function (filterName)
                    {
                        return !_.isUndefined($this.filters[filterName]);
                    };

                    /**
                     * Check if there are any filters set
                     * @returns {boolean}
                     */
                    $this.hasFilters = function ()
                    {
                        return _.size($this.filters) > 0;
                    };

                    /**
                     * Get the current filters
                     * @returns {*}
                     */
                    $this.getFilters = function ()
                    {
                        return $this.filters;
                    };

                    /**
                     *  Reset all the filters
                     * @param reload
                     */
                    $this.resetFilters = function (reload)
                    {
                        $this.filters = {};

                        if (!_.isUndefined(reload) && reload) {
                            $this.load();
                        }
                    };

                    /**
                     * Set sort on specific field
                     * @param column Supports sending [fieldName, 'ASC'] when column data is not available or for simplicity
                     * @param reload
                     * @returns {*}
                     */
                    $this.setSorter = function (column, reload)
                    {
                        var sortOrder = false;

                        // support for sending column parameter as [fieldName, 'ASC']
                        if (_.isArray(column) && _.size(column) == 2) {
                            sortOrder = column[1];
                            column = $this.getColumn(column[0]);
                        }

                        if (column.sortable) {
                            // sort order was sent as parameter
                            if (sortOrder) {
                                $this.sorters[column.field] = sortOrder;
                            } else {
                                // automatically determine the sort order based on current state
                                if ($this.hasSorter(column)) {
                                    // check previous direction
                                    if ($this.getSorter(column) == 'ASC') {
                                        // change direction
                                        $this.sorters[column.field] = 'DESC';
                                    } else {
                                        // remove sorter
                                        delete $this.sorters[column.field];
                                    }
                                } else {
                                    // default first direction
                                    $this.sorters[column.field] = 'ASC';
                                }
                            }

                            // reload on demand
                            if (!_.isUndefined(reload) && reload) {
                                $this.reload();
                            }

                            // clear view cache
                            if (!_.isUndefined($private.__renderViewCache['column_header_css'][column.uid])) {
                                delete $private.__renderViewCache['column_header_css'][column.uid]
                            }

                            // check for sorter listeners and execute them if any
                            if (_.size($private.sorterListeners) > 0) {
                                _.each ($private.sorterListeners, function (listener) {
                                    if (_.isFunction(listener)) {
                                        listener.apply(listener, [$this.sorters]);
                                    }
                                });
                            }
                        }

                        return $this;
                    };

                    /**
                     * Get sorting of a specific field
                     * @param column
                     * @returns {*}
                     */
                    $this.getSorter = function(column)
                    {
                        return $this.sorters[column.field];
                    };

                    /**
                     * Clear a sorter by removing it
                     * @param column
                     * @param reload
                     * @returns {*}
                     */
                    $this.removeSorter = function (column, reload)
                    {
                        delete $this.sorters[column.field];

                        // reload on demand
                        if (!_.isUndefined(reload) && reload) {
                            $this.reload();
                        }

                        // clear view cache
                        delete $private.__renderViewCache['column_header_css'][column.uid];

                        // check for sorter listeners and execute them if any
                        if (_.size($private.sorterListeners) > 0) {
                            _.each ($private.sorterListeners, function (listener) {
                                if (_.isFunction(listener)) {
                                    listener.apply(listener, [$this.sorters]);
                                }
                            });
                        }

                        return $this;
                    };

                    /**
                     * Check if a specific sorter exists
                     * @param column
                     * @returns {boolean}
                     */
                    $this.hasSorter = function (column)
                    {
                        return !_.isUndefined($this.sorters[column.field]);
                    };

                    /**
                     * Check if there are any sorters set
                     * @returns {boolean}
                     */
                    $this.hasSorters = function ()
                    {
                        return _.size($this.sorters) > 0;
                    };

                    /**
                     * Get the current sorters
                     * @returns {*}
                     */
                    $this.getSorters = function ()
                    {
                        return $this.sorters;
                    };

                    /**
                     *  Reset all the sorters
                     * @param reload
                     */
                    $this.resetSorters = function (reload)
                    {
                        $this.sorters = {};

                        // clear view cache
                        $private.__renderViewCache['column_header_css'] = {};

                        if (!_.isUndefined(reload) && reload) {
                            $this.load();
                        }

                        // check for sorter listeners and execute them if any
                        if (_.size($private.sorterListeners) > 0) {
                            _.each ($private.sorterListeners, function (listener) {
                                if (_.isFunction(listener)) {
                                    listener.apply(listener, [$this.sorters]);
                                }
                            });
                        }
                    };

                    /**
                     * Add sorters change listener(callback)
                     * @param callback
                     */
                    $this.registerSorterListener = function (callback)
                    {
                        $private.sorterListeners.push(callback);
                    };

                    /**
                     * Load data from Data Source
                     */
                    $this.load = function ()
                    {
                        $private.attachParams();

                        $this.dataSource.load($this.dsResponseHandler);

                    };

                    /**
                     * Proxy method for load
                     */
                    $this.reload = function ()
                    {
                        $this.load();
                    };

                    /**
                     * Get the header data used for grid rendering
                     * @returns {*}
                     */
                    $this.getHeaderData = function ()
                    {
                        if ($private.headerData === null) {
                            $private.prepareHeaderData();
                        }

                        return $private.headerData;
                    };

                    /**
                     * Configure columns state (visibility, order, etc)
                     * @param configuration
                     */
                    $this.configureColumns = function (configuration)
                    {
                        _.each($this.getHeaderData(), function (column) {
                            if (!column.internal && !_.isUndefined(configuration[column.field])) {
                                _.each(configuration[column.field], function (value, setting) {
                                    column[setting] = value;
                                });
                            }
                        });
                        // clear columns cache
                        $private.__columns = null;
                    };

                    /**
                     * Get visible && non-system columns
                     * This method must be used for rendering purposes
                     * @returns {{}}
                     */
                    $this.getColumns = function ()
                    {
                        if (!$private.__columns) {
                            var columns = {};
                            _.each($this.getHeaderData(), function (column) {
                                if (!column.internal && column.show) {
                                    columns[column.field] = column;
                                }
                            });

                            // sort by order
                            columns = _.object(_.pluck(columns, 'field'), _.sortBy(columns, 'order'));

                            $private.__columns = columns;
                            $private.__countVisibleColumns = _.size(columns);
                        }

                        return $private.__columns;
                    };

                    /**
                     * Get visible && non-system columns
                     * This method must be used for rendering purposes
                     * @returns {{}}
                     */
                    $this.getColumn = function (fieldName)
                    {
                        var columns = $this.getManageableColumns();

                        if (_.isUndefined(columns[fieldName])) {
                            throw "DataGridManager::getColumn column with field name ["+fieldName+"] does not exists!";
                        }

                        return columns[fieldName];
                    };

                    /**
                     * Get all [excluding system(internal)] columns
                     * @returns {{}}
                     */
                    $this.getManageableColumns = function ()
                    {
                        var columns = {};
                        _.each($this.getHeaderData(), function (column) {
                            if (!column.internal) {
                                columns[column.field] = column;
                            }
                        });

                        // sort by order
                        columns = _.indexBy(_.sortBy(columns, 'order'), 'field');

                        return columns;
                    };

                    /**
                     * Get columns state information
                     * @returns {{}}
                     */
                    $this.getColumnsState = function ()
                    {
                        var columnsData = {};

                        _.each($this.getManageableColumns(), function (column) {
                            var columnData = {
                                field: column.field,
                                show: column.show,
                                order: column.order
                            };
                            columnsData[column.field] = columnData;
                        });

                        return columnsData;
                    };

                    /**
                     * Change the visibility of a column
                     * @param column
                     */
                    $this.toggleColumnVisibility = function (column)
                    {
                        $private.headerData[column.field]['show'] = !$private.headerData[column.field]['show'];
                        // clear columns cache
                        $private.__columns = null;
                        // re-render column
                        $this.refreshColumn(column);
                    };

                    /**
                     * Change the order of a column
                     * @param column
                     * @param direction -> +1 or -1 (up/down)
                     * @param position -> exact position when direction is not provided
                     */
                    $this.reorderColumn = function (column, direction, position)
                    {
                        var maxPosition = $this.getCountManageableColumns(),
                            currentPosition = column.order;

                        if ((_.isUndefined(direction) || _.isNull(direction))  && (_.isUndefined(position) || _.isNull(position))) {
                            throw "DataGridManager::reorderColumn 'direction' or 'position' required!";
                        }

                        if (!_.isUndefined(direction) && !_.isNull(direction)) {
                            position = column.order + direction;
                        }

                        if (position >= 1 && position <= maxPosition) {
                            // reconcile other columns
                            _.each ($private.headerData, function (hColumn) {
                                if (hColumn.order < currentPosition && hColumn.order >= position) {
                                    hColumn.order++;
                                } else {
                                    if (hColumn.order > currentPosition && hColumn.order <= position) {
                                        hColumn.order--;
                                    }
                                }
                            });
                            // apply new position
                            $private.headerData[column.field]['order'] = position;
                            // clear cache
                            $private.__columns = null;
                            $private.__renderViewCache['row_cell'] = {};
                            $private.__renderViewCache['column_row'] = {};
                        }
                    };

                    /**
                     * Method used for rendering a cell
                     * @deprecated in favor of $this.renderCell
                     * @param cellValue
                     * @param row
                     * @param columnDefinition
                     * @returns {string}
                     */
                    $this.renderRowCell = function (cellValue, row, columnDefinition)
                    {
                        var output = '',
                            cacheId = row.uid+'_'+columnDefinition.field;

                        if (!_.isUndefined($private.__renderViewCache['row_cell'][cacheId])) {

                            return $private.__renderViewCache['row_cell'][cacheId];
                        } else {
                            output = cellValue;
                            if (!_.isUndefined($private.rowCellRenderers[columnDefinition.field])) {
                                // has renderer
                                var rowCellRenderer = $private.rowCellRenderers[columnDefinition.field];
                                output = rowCellRenderer.apply(rowCellRenderer, arguments);
                                //if (_.isObject(output) && output instanceof jQuery || typeof(output.nodeType) !== 'undefined') {
                                //    // transform JQuery object to plain string html
                                //    output = $(output).outerHTML();
                                //}
                            }

                            output = (_.isNull(output) || _.isUndefined(output)) ? '' : output;

                            $private.__renderViewCache['row_cell'][cacheId] = output;
                        }

                        return output;
                    };

                    /**
                     * Method used for rendering a cell
                     * @param row
                     * @param column
                     * @returns {*}
                     */
                    $this.renderCell = function (row, column)
                    {
                        var cacheId = row.uid + '_' + column.uid;

                        if (_.isUndefined($private.__renderViewCache['row_cell'][cacheId])) {
                            // initialize column cell view cache
                            $private.__renderViewCache['column_row'][column.field] = {};
                            var output = '';
                            // => 'actions' column
                            if (column.isAction) {
                                output = [];
                                _.each ($this.getActions(), function (action) {
                                    if (!_.isUndefined($private.rowActionRenderers[action.name])) {
                                        var actionRenderer = $private.rowActionRenderers[action.name]['renderer'];
                                        var actionStr = actionRenderer.apply(actionRenderer, [$private.getActionData(row, action), action, row.uid]);
                                        // output will replace directly the content of the cell using $
                                        if (actionStr) {
                                            // plain HTML string - RECOMMENDED !!!
                                            if (_.isHtml(actionStr)) {
                                                output.push(actionStr);
                                            } else {
                                                // support for jQuery objects - DEPRECATED !!!
                                                if (_.isObject(actionStr) && actionStr instanceof jQuery || !_.isUndefined(actionStr.nodeType)) {
                                                    $('#' + cacheId)
                                                        .append($compile(actionStr, false, 1001)($this.getControllerScope()))
                                                        .append($private.rowActionSeparators.prefix + $private.rowActionSeparators.suffix)
                                                } else {
                                                    throw "DataGridManager::Action renderer for [" + action.name + "] returned invalid HTML (" + actionStr + ")";
                                                }
                                            }
                                        }
                                    }
                                });
                                if (_.size(output) > 0) {
                                    $('#' + cacheId).html($compile(_.embrace(output, $private.rowActionSeparators.prefix, $private.rowActionSeparators.suffix), false, 1001)($this.getControllerScope()));
                                }
                            } else {
                                // => data column
                                // get raw value
                                output = $this.getFieldValueFromRow(column.field, row);
                                // check if there's a custom renderer
                                if (!_.isUndefined($private.rowCellRenderers[column.field])) {
                                    // has renderer
                                    var rowCellRenderer = $private.rowCellRenderers[column.field];
                                    // obtain custom output from renderer
                                    output = rowCellRenderer.apply(rowCellRenderer, [output, row, column]);
                                }
                                // output will replace directly the content of the cell using $
                                if (output) {
                                    // jquery object
                                    if (_.isObject(output) && output instanceof jQuery || !_.isUndefined(output.nodeType)) {
                                        $('#' + cacheId).html($compile(output, false, 1001)($this.getControllerScope()));
                                    } else {
                                        // plain string that might be HTML, so we need to test it
                                        if (!_.isHtml(output)) {
                                            $('#' + cacheId).html(output);
                                        } else {
                                            $('#' + cacheId).html($compile($.parseHTML(output), false, 1001)($this.getControllerScope()));
                                        }
                                    }
                                }
                            }
                        }
                        // mark cell as rendered
                        $private.__renderViewCache['row_cell'][cacheId] = true;
                        // store cacheId for cell using [column.field][row.uid]
                        $private.__renderViewCache['column_row'][column.field][row.uid] = cacheId;
                    };

                    /**
                     * Delete cache for a specific column
                     * Data for the column will be re-rendered
                     * @param column
                     */
                    $this.refreshColumn = function (column)
                    {
                        if (!_.isUndefined($private.__renderViewCache['column_row'][column.field])) {
                            _.each ($private.__renderViewCache['column_row'][column.field], function (cacheId, rowUid) {
                                if (!_.isUndefined($private.__renderViewCache['row_cell'][cacheId])) {
                                    delete $private.__renderViewCache['row_cell'][cacheId];
                                }
                            });
                        }
                    };

                    /**
                     * Delete cache for a specific row
                     * Data for the row will be re-rendered
                     * @param rowUid
                     */
                    $this.refreshRow = function (rowUid)
                    {
                        var columns = $this.getManageableColumns();
                        _.each (columns, function (column) {
                            if (!_.isUndefined($private.__renderViewCache['column_row'][column.field])) {
                                var cacheId = $private.__renderViewCache['column_row'][column.field][rowUid];
                                if (!_.isUndefined($private.__renderViewCache['row_cell'][cacheId])) {
                                    delete $private.__renderViewCache['row_cell'][cacheId];
                                }
                            }
                        });
                    };

                    /**
                     * Render a row action
                     * (using a com-html-provider directive for interpolation/angular compiling)
                     * @deprecated in favor of $this.renderCell
                     * @param actionName
                     * @param actionSettings
                     * @param row
                     * @param column
                     * @returns {*}
                     */
                    $this.renderRowAction = function (actionName, actionSettings, row, column)
                    {
                        var output = '',
                            cacheId = row.uid+'_'+column.uid;

                        if (!_.isUndefined($private.__renderViewCache['row_action'][cacheId])) {

                            return $private.__renderViewCache['row_action'][cacheId];
                        } else {
                            if (!_.isUndefined($private.rowActionRenderers[actionName])) {
                                var actionRenderer = $private.rowActionRenderers[actionName]['renderer'];
                                output = actionRenderer.apply(actionRenderer, [$private.getActionData(row, actionSettings), actionSettings, row.uid]);
                            }

                            $private.__renderViewCache['row_action'][cacheId] = output;
                        }

                        return output;
                    };

                    /**
                     * Register a row action renderer
                     * @param actionName
                     * @param renderer
                     * @param withParams
                     */
                    $this.addRowActionRenderer = function (actionName, renderer, withParams)
                    {
                        if (!_.isUndefined(renderer) && _.isFunction(renderer)) {
                            $private.rowActionRenderers[actionName] = {
                                name: actionName,
                                renderer: renderer,
                                'with': _.isUndefined(withParams) ? '*' : withParams
                            };
                        } else {
                            throw "DataGridManager::Action renderer ["+renderer+"] for ["+actionName+"] is invalid ";
                        }
                    };

                    /**
                     * Register a row cell renderer
                     * @param field
                     * @param renderer
                     */
                    $this.addRowCellRenderer = function (field, renderer)
                    {
                        if (!_.isUndefined(renderer) && _.isFunction(renderer)) {
                            $private.rowCellRenderers[field] = renderer;
                        } else {
                            throw "DataGridManager::addRowCellRenderer Row Cell renderer for ["+field+"] is invalid ["+renderer+"]";
                        }
                    };

                    /**
                     * Register a row css class injector callback
                     * @param callback
                     */
                    $this.addRowCssClassInjector = function (callback)
                    {
                        if (!_.isUndefined(callback) && _.isFunction(callback)) {
                            $private.rowCssClassInjector = callback;
                        } else {
                            throw "DataGridManager::addRowCssClassInjector Row Css Class injector callback is invalid ["+callback+"]";
                        }
                    };

                    /**
                     * Get raw value for a field from a row
                     * @param field
                     * @param row
                     */
                    $this.getFieldValueFromRow = function (field, row)
                    {
                        return row[$this.grid.metaData.columnsOrderMap.name2pos[field]];
                    };

                    /**
                     * Get an assoc object as field=value from a row
                     * @param row
                     */
                    $this.getAssocFieldsValuesFromRow = function (row)
                    {
                        var assoc = {};
                        _.each($this.getHeaderData(), function (fieldDefinition) {
                            assoc[fieldDefinition.field] = row[$this.grid.metaData.columnsOrderMap.name2pos[fieldDefinition.field]];
                        });

                        return assoc;
                    };

                    /**
                     * Get row data by row uid
                     * @param rowUid
                     * @param assoc true|false As key/value
                     * @returns {*}
                     */
                    $this.getRowData = function (rowUid, assoc)
                    {
                        assoc = _.isUndefined(assoc) ? false : true;
                        var data = $private.indexedData[rowUid];

                        if (assoc) {
                            data = $this.getAssocFieldsValuesFromRow(data);
                            data['uid'] = rowUid;
                        }

                        return data;
                    };

                    /**
                     * Update row data using row UID
                     * NOTE: purpose is only for VIEW update
                     * @param rowUid
                     * @param data assoc object (key/value)
                     */
                    $this.updateRowData = function (rowUid, data)
                    {
                        var rowDataSet = $private.indexedData[rowUid],
                            viewDataSet = $private.data[$private.referenceMap[rowUid]],
                            rawDataSet = $private.rawData[$private.referenceMap[rowUid]],

                            requiresRefresh = false;

                        _.each (rowDataSet, function (value, key) {
                            var property = $this.grid.metaData.columnsOrderMap.pos2name[key];
                            if (!_.isUndefined(data[property])) {
                                if (!_.isEqual(data[property], value)) {
                                    var newValue = angular.copy(data[property]);
                                    rowDataSet[key] = newValue;
                                    viewDataSet[key] = newValue;
                                    rawDataSet[key] = newValue;
                                    requiresRefresh = true;
                                }
                            }
                        });

                        if (requiresRefresh) {
                            $this.refreshRow(rowUid);
                        }
                    };

                    /**
                     * Retrieve and use a callback function (if registered with 'addRowCssClassInjector')
                     * to inject css class for the row(tr)
                     * @param row
                     * @returns {*}
                     */
                    $this.getRowCssClass = function (row)
                    {
                        var output = '',
                            cacheId = row.uid;

                        if (!_.isUndefined($private.__renderViewCache['row_css'][cacheId])) {

                            return $private.__renderViewCache['row_css'][cacheId];
                        } else {
                            if ($private.rowCssClassInjector && _.isFunction($private.rowCssClassInjector)) {
                                var callable = $private.rowCssClassInjector;
                                    output = callable.apply(callable, [$this.getAssocFieldsValuesFromRow(row), row.uid]);
                            }
                            $private.__renderViewCache['row_css'][cacheId] = output;
                        }

                        return output;
                    };

                    /**
                     * Clear row css class
                     * @param rowUid
                     * @returns {*}
                     */
                    $this.clearRowCssClass = function (rowUid)
                    {
                        rowUid = rowUid || false;

                        if (rowUid) {
                            delete $private.__renderViewCache['row_css'][rowUid];
                        } else {
                            $private.__renderViewCache['row_css'] = {};
                        }
                    };

                    /**
                     * Get column header css class
                     * @param column
                     * @returns {*}
                     */
                    $this.getColumnHeaderCssClass = function (column)
                    {
                        var output = {},
                            cacheId = column.uid;

                        if (!_.isUndefined($private.__renderViewCache['column_header_css'][cacheId])) {

                            return $private.__renderViewCache['column_header_css'][cacheId];
                        } else {
                            output = {
                               'sorting_asc': $this.sorters[column.field] == 'ASC',
                               'sorting_desc': $this.sorters[column.field] == 'DESC',
                               'sorting': column.sortable
                            };

                            if (column.headerClass) {
                                output[column.headerClass] = true;
                            }

                            $private.__renderViewCache['column_header_css'][cacheId] = output;
                        }

                        return output;
                    };

                    /**
                     * Get column css class
                     * @param column
                     * @returns {*}
                     */
                    $this.getColumnCssClass = function (column)
                    {
                        var output = {},
                            cacheId = column.uid;

                        if (!_.isUndefined($private.__renderViewCache['column_css'][cacheId])) {

                            return $private.__renderViewCache['column_css'][cacheId];
                        } else {
                            output = {};

                            if (column.cellClass) {
                                output[column.cellClass] = true;
                            }

                            $private.__renderViewCache['column_css'][cacheId] = output;
                        }

                        return output;
                    };

                    /**
                     * Set action separators
                     * @param prefix
                     * @param suffix
                     */
                    $this.setRowActionSeparator = function (prefix, suffix)
                    {
                        // single object with {'prefix', 'suffix'} properties
                        if (_.isObject(prefix) && _.isUndefined(prefix['prefix']) && _.isUndefined(prefix['suffix'])) {
                            $private.rowActionSeparators = prefix;
                        } else {
                            // each property given separately
                            $private.rowActionSeparators.prefix = prefix;
                            $private.rowActionSeparators.suffix = suffix;
                        }
                    };

                    /**
                     * ===============
                     * Private methods
                     * ===============
                     */

                    /**
                     * Get suffixed output name given an input name
                     * @param name
                     * @param glue Optional '@'
                     * @returns {*}
                     */
                    $private.getOwnName = function (name, glue)
                    {
                        glue = _.isUndefined(glue) ? '@' : glue;
                        return name+glue+$private.guid;
                    };

                    /**
                     * Default DataSource Response Handler
                     * if nothing provided
                     * @param responsePayload
                     * @param status
                     * @param headers
                     */
                    $private.onHandleDataSourceResponse = function (responsePayload, status, headers)
                    {
                        if (responsePayload && responsePayload.data && status == 200) {
                            $this.setData(responsePayload.data);
                        } else {
                            throw 'Web.Components.DataGridManager: onHandleDataSourceResponse error encountered';
                        }
                    };

                    /**
                     * Process meta data and prepare grid header sections
                     */
                    $private.prepareHeaderData = function ()
                    {
                        var headerData = {},
                            columnsOrderIdx = 1;
                            $private.__countVisibleColumns = 0;

                        // check if there are custom renderers for fields & require controller scope
                        if (_.size($this.grid.metaData.customRenders) > 0) {
                             if (!$private.ctrl.scope && !$private.ctrl.instance) {
                                 throw "DataGridManager::prepareHeaderData Custom renderers found in meta data but [$ctrl] OR [$ctrl's $scope] is not set";
                             }
                        }

                        _.each($this.grid.metaData.header, function (headerDefinition, headerIdx) {
                            // column mapping
                            var column = {
                                uid: 'col_uid_'+_.uniqueId(),
                                label: headerDefinition.label,
                                internal: !headerDefinition.visible,
                                order: headerDefinition.visible ? columnsOrderIdx : false,
                                show: headerDefinition.visible,
                                field: $this.grid.metaData.columnsOrderMap.pos2name[headerDefinition.targets[0]],
                                key: headerDefinition.targets[0],
                                isAction: headerDefinition.isAction,
                                width: headerDefinition.width,
                                headerClass: headerDefinition.headerClass ? headerDefinition.headerClass : '', // column header class
                                cellClass: headerDefinition.cellClass ? headerDefinition.cellClass : '', // cell class
                                sortable: headerDefinition.sortable
                            };

                            // set visible column count
                            $private.__countVisibleColumns += (headerDefinition.visible ? 1 : 0);

                            headerData[column.field] = column;

                            // check if field has custom renderer & register it
                            if (!_.isUndefined($this.grid.metaData.customRenders[headerIdx])) {
                                var rendererName = $this.grid.metaData.customRenders[headerIdx],
                                    // check for the renderer provider
                                    rendererProvider = $private.ctrl.instance && !_.isUndefined($private.ctrl.instance[rendererName]) ? $private.ctrl.instance
                                        : $private.ctrl.scope && !_.isUndefined($private.ctrl.scope[rendererName]) ? $private.ctrl.scope : null;

                                // check if render method is a valid callable
                                if (rendererProvider) {
                                    $this.addRowCellRenderer(column.field, rendererProvider[rendererName]);
                                } else {
                                    throw "DataGridManager::prepareHeaderData ["+column.field+"] renderer function ["+rendererName+"] was not found on $ctrl OR $ctrl's $scope";
                                }
                            }

                            if (!column.internal) {
                                columnsOrderIdx++;
                            }
                        });

                        $private.headerData = headerData;
                    };

                    /**
                     * Prepare grid data
                     * @param data
                     */
                    $private.prepareGridData = function (data)
                    {
                        // original data
                        $private.rawData = _.deepClone(data);
                        // row UID indexed
                        $private.indexedData = {};
                        // row UID to key reference map
                        $private.referenceMap = {};

                        var localData = _.deepClone(data);

                        _.each(localData, function (row, key) {
                            row['uid'] = 'row_uid_'+_.uniqueId();
                            localData[key] = row;
                            $private.indexedData[row['uid']] = row;
                            $private.referenceMap[row['uid']] = key;
                        });

                        $private.data = localData;
                    };

                    /**
                     * Extract the parameters from the row data to supply it to the action renderer
                     * @param row
                     * @param actionSettings
                     * @returns {*}
                     */
                    $private.getActionData = function (row, actionSettings)
                    {
                        var params = {};

                        if (!_.isUndefined(actionSettings)) {
                            if (actionSettings['with'] === '*') {
                                return $this.getAssocFieldsValuesFromRow(row);
                            } else {
                                _.each(actionSettings['with'], function (field, key) {
                                    params[field] = row[key];
                                });
                            }
                        } else {
                            params = row;
                        }

                        return params;
                    };

                    /**
                     * Create data tables filters
                     */
                    $private.addDtFilters = function ()
                    {
                        $private.params["filters"] = {
                            name: "data-filters",
                            value: $this.filters
                        };
                    };

                    /**
                     * Add pagination params
                     */
                    $private.addDtPagination = function ()
                    {
                        $private.params["page"] = {
                            name: "page",
                            value: $this.pagination.page
                        };

                        $private.params["size"] = {
                            name: "page-size",
                            value: $this.pagination.size
                        };

                    };

                    /**
                     * Create data tables sorters
                     */
                    $private.addDtSorters = function ()
                    {
                        $private.params["sorters"] = {
                            name: "data-sorters",
                            value: $this.sorters
                        };
                    };

                    /**
                     * Attach data tables type parameters
                     */
                    $private.attachParams = function ()
                    {
                        $private.addDtFilters();
                        $private.addDtPagination();
                        $private.addDtSorters();

                        var ord = 0;

                        _.each($private.params, function (param) {
                            $this.dataSource.addParam(ord, param);
                            ord++;
                        });

                        // store persistent params
                        //$cookieStore.put('dsp', angular.toJson($this.dataSource.getParams()));

                        $private.applyUriEngineParameters();
                    };

                    /**
                     * Default DataSource response handler
                     * @param payload
                     * @param status
                     * @param headers
                     * @param config
                     */
                    $private.onDataSourceLoadData = function (payload, status, headers, config)
                    {
                        if (payload.status) {
                            $this.setData(payload.data);
                        }
                    };


                    /**
                     *
                     * @param paginationData
                     *  {
                     *      total:      data.ds.total,
                     *      available:  data.ds.total_available
                     *  }
                     */
                    $private.setPaginationData = function (paginationData)
                    {
                        // reset page ranges
                        $this.pagination['pageRanges'] = [];
                        // merge values from paginationData and update data in $this.pagination
                        _.deepExtend($this.pagination, paginationData);
                    };

                    /**
                     * Push URI Engine params
                     */
                    $private.applyUriEngineParameters = function ()
                    {
                        //console.log('state:', $private.uriEngineStatus);

                        if ($private.uriEngineStatus) {

                            var $filters = $this.getFilters();
                            var $sorters = $this.getSorters();
                            var $pagination = $this.getPagination();

                            // handle filters
                            if (_.size($filters) > 0) {
                                UriEngine.addParam($private.getOwnName('dgf'), $filters);
                            } else {
                                // filters had been emptied
                                if (UriEngine.getParam($private.getOwnName('dgf'))) {
                                    UriEngine.removeParam($private.getOwnName('dgf'));
                                }
                            }
                            // handle sorters
                            if (_.size($sorters) > 0) {
                                UriEngine.addParam($private.getOwnName('dgs'), $sorters);
                            } else {
                                // sorters had been emptied
                                if (UriEngine.getParam($private.getOwnName('dgs'))) {
                                    UriEngine.removeParam($private.getOwnName('dgs'));
                                }
                            }
                            // handle page
                            if ($pagination.page > 1) {
                                UriEngine.addParam($private.getOwnName('dgp'), $pagination.page);
                            } else {
                                // pagination reset
                                if (UriEngine.getParam($private.getOwnName('dgp'))) {
                                    UriEngine.removeParam($private.getOwnName('dgp'));
                                }
                            }

                            // persist params in cookie if a persistentNameSpace was given
                            if ($private.uriEnginePersistentNameSpace) {
                                $cookieStore.put($private.uriEnginePersistentNameSpace, JSON.stringify(UriEngine.getParams()));
                            }
                        }
                    };

                    /**
                     * Apply URI engine params
                     */
                    $private.reApplyUriEngineParameters = function ()
                    {
                        if ($private.uriEngineStatus) {
                            // check persist params in cookie: if a persistentNameSpace was given
                            if ($private.uriEnginePersistentNameSpace) {
                                var persistentParams = $cookieStore.get($private.uriEnginePersistentNameSpace);
                                try {
                                    persistentParams = JSON.parse(persistentParams);
                                } catch (err) {
                                    if (!_.isUndefined(console)) {
                                        console.log('Web.Components.DataGridManager::Failed to decode persistent params');
                                    }
                                }
                            } else {
                                persistentParams = false;
                            }

                            // repopulate filters
                            if (!_.isUndefined(UriEngine.getParam($private.getOwnName('dgf')))) {
                                $this.filters = UriEngine.getParam($private.getOwnName('dgf'));
                            } else {
                                if (persistentParams && !_.isUndefined(persistentParams['dgf'])) {
                                    $this.filters = persistentParams['dgf'];
                                }
                            }
                            // repopulate sorters
                            if (!_.isUndefined(UriEngine.getParam($private.getOwnName('dgs')))) {
                                $this.sorters = UriEngine.getParam($private.getOwnName('dgs'));
                            } else {
                                if (persistentParams && !_.isUndefined(persistentParams['dgs'])) {
                                    $this.sorters = persistentParams['dgs'];
                                }
                            }
                            // repopulate pagination
                            if (!_.isUndefined(UriEngine.getParam($private.getOwnName('dgp')))) {
                                $this.pagination.page = UriEngine.getParam($private.getOwnName('dgp'));
                            } else {
                                if (persistentParams && !_.isUndefined(persistentParams['dgp'])) {
                                    $this.pagination.page = persistentParams['dgp'];
                                }
                            }
                        }
                    };

                    /**
                     * Setup initial data & methods
                     */
                    $private.init = function ($iniOptions)
                    {
                        // check for instance GUID
                        if (!_.isUndefined($iniOptions['guid']) && $iniOptions['guid'].length > 0) {
                            $private.guid = $iniOptions['guid'];
                        }

                        // check for URI Engine persistent namespace
                        if (!_.isUndefined($iniOptions['uri_engine_pns'])) {
                            $private.uriEnginePersistentNameSpace = $iniOptions['uri_engine_pns'];
                        }

                        // reapply parameters in the grid
                        $private.reApplyUriEngineParameters();

                        // set default data source response handler
                        $this.setDataSourceResponseHandler($private.onDataSourceLoadData);

                        return $this;
                    };

                    return $this.init($iniOptions, $initCallback);
                }
            };

            return DataGridManager;
        }
    ]);


angular.module('Web.Components')
    .directive('comDataGrid', ['$templateCache',
        function ($templateCache) {
            var WcDataGrid = {

                scope: {
                    manager: '=',
                    title: '@'
                },

                restrict: 'E',

                link: function (scope, elem, attrs)
                {
                    // set controller alias on manager's $scope
                    var ctrl = scope.manager.getController(true);
                    scope[ctrl.alias] = ctrl.instance;

                    // also cross check if the controller has a reference to itself
                    if (_.isUndefined(ctrl.scope['$ctrl'])) {
                        ctrl.scope['$ctrl'] = ctrl.instance;
                    }

                    scope.grid = scope.manager.grid;
                    scope.grid.scope = scope;

                    // setup custom vars
                    if (!_.isUndefined(scope.title)) {
                        scope.manager.setTitle(scope.title);
                    }

                },

                /**
                 *
                 * @param tElement
                 * @param tAttrs
                 * @returns {*|Object|HttpPromise}
                 */
                template: function (tElement, tAttrs)
                {
                    // use the attribute 'template' to retrive the directive template from cache
                    if (!_.isUndefined(tAttrs.template)) {
                        var templateName = tAttrs.template,
                            tpl = $templateCache.get(templateName);

                        if (!_.isUndefined(tpl)) {
                            return tpl;
                        } else {
                            throw "Web.Components:comDataGrid :: template [" + templateName + "] was not found in $templateCache, but should be included.";
                        }
                    } else {
                        throw "Web.Components:comDataGrid :: template attribute must be defined and have a valid value.";
                    }
                }
            };

            return WcDataGrid;
        }
    ]);
