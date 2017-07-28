'use strict';

var tokbox = require('@opentok/client');

/**
 * @param {Node} container
 * @constructor
 */
function Publisher(container) {
    this.container = container;
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

    return tokbox.initPublisher(this.container.id, publisherOptions, this.handleError);
};

Publisher.prototype.handleError = function (error) {
    if (error) {
        console.log('Publisher error:', error);
    }
};

module.exports = Publisher;
