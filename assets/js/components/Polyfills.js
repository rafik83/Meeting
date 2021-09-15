/**
 * Internet Explorer doesn't support forEach method on NodeList
 *
 * @see https://caniuse.com/mdn-api_nodelist_foreach
 * @see https://github.com/imagitama/nodelist-foreach-polyfill
 */
if (typeof window !== 'undefined' &&  window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = function (callback, thisArg) {
        thisArg = thisArg || window;
        for (var i = 0; i < this.length; i++) {
            callback.call(thisArg, this[i], i, this);
        }
    };
}
