'use strict';

/**
 * @param {Session} session
 * @param {Node} container
 * @constructor
 */
function Subscriber(session, container) {
    this.session = session;
    this.container = container;
    this.subscriber = null;
}

/**
 * @param {Object} event
 * @returns {null|Subscriber}
 */
Subscriber.prototype.subscribe = function (event) {
    console.log('Subscribe to:', event.stream);

    var subscriberOptions = {
        insertMode: 'append',
        width: '500px',
        height: '500px',
        accessAllowed: true
    };
    
    this.subscriber = this.session.subscribe(event.stream, this.container.id, subscriberOptions, this.handleError);

    return this.subscriber;
};

/**
 * Handle subscriber callback errors
 *
 * @param {Object} error
 */
Subscriber.prototype.handleError = function (error) {
    if (error) {
        console.log('Subscriber error:', error);
    }
};

module.exports = Subscriber;
