/**
 * @author Petru Cojocar <petru.cojocar@gmail.com>.
 */
(function ($) {
    var div;

    $.fn.outerHTML = function () {
        var elem = this[0],
            tmp;

        return !elem ? null
            : typeof ( tmp = elem.outerHTML ) === 'string' ? tmp
            : ( div = div || $('<div/>') ).html(this.eq(0).clone()).html();
    };

})(jQuery);