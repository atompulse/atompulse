/**
 * Global Web Components Module Definition
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */
try {
    angular.module('Web.Components')
} catch(err) {
    angular.module('Web.Components', ['ui.bootstrap'])
}