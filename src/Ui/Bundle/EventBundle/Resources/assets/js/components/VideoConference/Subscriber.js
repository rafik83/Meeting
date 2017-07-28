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
    console.log('Subscribe to:', event.stream);

    var subscriberOptions = {
        insertMode: 'append',
        width: '500px',
        height: '500px',
        accessAllowed: true
    };
    
    var subscriber = this.session.subscribe(event.stream, this.container.id, subscriberOptions, this.handleError);

    subscriber.on('disconnected', function () {
        this.container.classList.toggle('hide');
        console.log('disconnected');
    }.bind(this));

    subscriber.on('destroyed', function () {
        this.container.classList.toggle('hide');
        console.log('destroyed')
    }.bind(this));

    subscriber.on('connected', function () {
        this.container.classList.toggle('hide');
        console.log('connected');
    }.bind(this));
};

Subscriber.prototype.handleError = function (error) {
    if (error) {
        console.log('Subscriber error:', error);
    }
};

module.exports = Subscriber;
