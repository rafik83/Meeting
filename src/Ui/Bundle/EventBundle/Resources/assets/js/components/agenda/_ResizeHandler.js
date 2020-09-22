import EventEmitter from "./_EventEmitter";

/**
 * Risze handler
 *
 * @param {Element} element
 */
function ResizeHandler(element) {
    EventEmitter.call(this, element);

    this.timeout = null;

    this.onResize = this.onResize.bind(this);
    this.onResized = this.onResized.bind(this);

    this.element.addEventListener('resize', this.onResize);
}

ResizeHandler.prototype = Object.create(EventEmitter.prototype);
ResizeHandler.prototype.constructor = ResizeHandler;

/**
 * Time before handling the resize
 *
 * @type {Number}
 */
ResizeHandler.prototype.tolerance = 100;

/**
 * Clear timeout
 */
ResizeHandler.prototype.clearTimeout = function() {
    if (this.timeout) {
        clearTimeout(this.timeout);
        this.timeout = null;
    }
};

/**
 * On window resize event
 *
 * @param {Event} event
 */
ResizeHandler.prototype.onResize = function(event) {
    this.clearTimeout();
    this.timeout = setTimeout(this.onResized, this.tolerance);
};

/**
 * On window has resized (finish)
 */
ResizeHandler.prototype.onResized = function() {
    this.clearTimeout();
    this.emit('resized');
};

export default ResizeHandler;
