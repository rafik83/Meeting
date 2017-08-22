'use strict';

var tokbox = require('@opentok/client');

/**
 * @param {Node} container
 * @constructor
 */
function Publisher(container) {
    this.container = container;
    this.publisher = null;
}

/**
 * The Publisher object represents the view of a video you publish
 */
Publisher.prototype.create = function () {
    var publisherOptions = {
        insertMode: 'append',
        width: '500px',
        height: '500px',
        accessAllowed: true
    };

    this.container.style.width = '500px';
    this.container.style.height = '500px';

    this.publisher = tokbox.initPublisher(this.container.id, publisherOptions, this.handleError);
    console.log('Publisher created', this.publisher);

    return this.publisher;
};

Publisher.prototype.destroy = function () {
    this.publisher.destroy();
    console.log('Publisher destroy');
};

Publisher.prototype.handleError = function (error) {
    if (error) {
        console.log('Publisher error:', error);
    }
};

module.exports = Publisher;
