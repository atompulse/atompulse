/**
 * @author Ionut Pisla <ionut.tudorel@gmail.com>.
 */

try {
    angular.module('Web.Components')
} catch (err) {
    angular.module('Web.Components', ['ui.bootstrap'])
}

angular.module('Web.Components')
    .directive('comModalConfirm', ['Web.Components.ModalConfirmService', function (modalConfirmService) {
        return {
            restrict: 'A',
            link: function (scope, elem, attrs) {

                var text = attrs.comModalTitle;
                var callable = attrs.comModalConfirm;
                var params = attrs.comModalParams;

                var callableDetails = callable.split('.');

                if (typeof(scope[callableDetails[0]]) === 'undefined') {
                    throw 'Controller instance [' + callableDetails[0] + '] not found on scope';
                }

                if (typeof(scope[callableDetails[0]][callableDetails[1]]) === 'undefined') {
                    throw 'Controller function [' + callableDetails[1] + '] not found on controller';
                }

                elem.on('click', function () {
                    modalConfirmService.confirm(text,
                        {
                            text: 'OK',
                            action: function () {
                                scope[callableDetails[0]][callableDetails[1]].call(scope[callableDetails[0]], params);
                            }
                        },
                        {
                            text: 'Cancel'
                        }
                    );
                });
            }
        };
    }])
    .factory('Web.Components.ModalConfirmService', ['$uibModal',
        function ($uibModal)
        {
            var $private = {
                /**
                 * Modal Service Default Options
                 */
                defaultOptions: {
                    onConfirm: {
                        text: 'Ok',
                        action: null,
                        params: {}
                    },
                    onCancel: {
                        text: 'Cancel',
                        action: null,
                        params: {}
                    }
                }
            };

            /**
             * On Show Confirm Modal Action
             * @param question
             * @param _onConfirm
             * @param _onCancel
             */
            $private.onShowConfirmModal = function (question, _onConfirm, _onCancel)
            {
                $uibModal.open({
                    template: '<div class="modal-body"><h3>'+question+'</h3></div>' +
                    '<div class="modal-footer">' +
                    '<button class="btn btn-default" ng-click="onCancelAction()">' +
                    _onCancel.text +
                    '</button> ' +
                    '<button class="btn btn-success" ng-click="onOkAction()">' +
                    _onConfirm.text +
                    '</button>' +
                    '</div>',
                    keyboard: false,
                    controller: ["$scope", "$uibModalInstance",
                        function ($scope, $uibModalInstance) {
                            $scope.onOkAction = function () {
                                $uibModalInstance.close();
                                // Call confirm function
                                _onConfirm.action.apply(_onConfirm.action, _onConfirm.params);
                            };
                            $scope.onCancelAction = function () {
                                $uibModalInstance.dismiss('cancel');
                                // If there is a cancel action then call it
                                if (_onCancel.action) {
                                    _onCancel.action.apply(_onCancel.action, _onCancel.params);
                                }
                            };
                        }]
                });
            };

            var ModalConfirmService = {};

            /**
             * Show confirm modal dialog
             * @param question
             * @param onConfirm {text: 'Ok', action: null, params: {}}
             * @param onCancel {text: 'Cancel', action: null, params: {}}
             */
            ModalConfirmService.confirm = function (question, onConfirm, onCancel)
            {
                var _onConfirm = $private.defaultOptions.onConfirm,
                    _onCancel = $private.defaultOptions.onCancel;

                if (typeof(onConfirm['text']) !== 'undefined') {
                    _onConfirm.text = onConfirm['text'];
                }
                if (typeof(onConfirm['action']) === 'undefined') {
                    throw "ModalConfirmService: Callback for 'action' must be defined and callable, got " + typeof(onConfirm['action']);
                } else {
                    _onConfirm.action = onConfirm['action'];
                }
                if (typeof(onConfirm['params']) !== 'undefined') {
                    _onConfirm.params = onConfirm['params'];
                }

                if (typeof(onCancel['text']) !== 'undefined') {
                    _onCancel.text = onCancel['text'];
                }
                if (typeof(onCancel['action']) !== 'undefined') {
                    _onCancel.action = onCancel['action'];
                }
                if (typeof(onCancel['params']) !== 'undefined') {
                    _onCancel.params = onCancel['params'];
                }

                $private.onShowConfirmModal(question, _onConfirm, _onCancel);
            };

            return ModalConfirmService;
        }
    ]);