/**
 * Event Emitter
 *
 * @param {Element} element
 */
function EventEmitter(element) {
    this.element = element;
}

/**
 * Listen for an event
 *
 * @param {String} name Event name
 * @param {Function} callback Callback
 */
EventEmitter.prototype.on = function(name, callback) {
    this.element.addEventListener(name, callback);
};

/**
 * Listen for an event
 *
 * @param {String} event Event name
 */
EventEmitter.prototype.emit = function(name) {
    this.element.dispatchEvent(new Event(name));
};

module.exports = EventEmitter;
