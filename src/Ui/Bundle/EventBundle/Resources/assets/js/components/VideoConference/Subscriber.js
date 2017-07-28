'use strict';

/**
 * @param session
 * @param {Node} container
 * @constructor
 */
function Subscriber(session, container) {
    this.session = session;
    this.container = container;
}

Subscriber.prototype.subscribe = function (event) {
    console.log('Subscribe');

    var subscriberOptions = {
        insertMode: 'append',
        width: '500px',
        height: '500px',
        accessAllowed: true
    };
    
    var subscriber = this.session.subscribe(event.stream, this.container.id, subscriberOptions, this.handleError);

    subscriber.on('disconnected', function () {
        console.log('disconnected');
    });

    subscriber.on('destroyed', function () {
        console.log('destroyed')
    });

    subscriber.on('connected', function () {
        console.log('connected');
    });
};

Subscriber.prototype.handleError = function (error) {
    if (error) {
        console.log('Subscriber error:', error);
    }
};

module.exports = Subscriber;
