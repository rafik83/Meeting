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
    showControls: true,
    width: '100%',
    height: '100%'
  };

  this.publisher = tokbox.initPublisher(this.container, publisherOptions, this.handleError);

  this.publisher.on('streamCreated', function (event) {
    console.log('publisherSTREAM', event.stream.id);
  });

  return this.publisher;
};

Publisher.prototype.destroy = function () {
  this.publisher.destroy();
};

Publisher.prototype.handleError = function (error) {
  if (error) {
    console.log('Publisher error:', error);
  }
};

module.exports = Publisher;
