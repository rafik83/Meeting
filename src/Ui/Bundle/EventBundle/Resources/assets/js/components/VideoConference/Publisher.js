'use strict';

import tokbox from '@opentok/client';

export const  STREAM_TYPE_CAMERA = 'camera';
export const  STREAM_TYPE_SCREENSHARE = 'screen';
export const  STREAM_TYPE_CUSTOM = 'custom';

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
 * @see https://tokbox.com/developer/sdks/js/reference/OT.html#initPublisher
 *
 * @param {null|Object} options
 * @returns {null|Publisher}
 */
Publisher.prototype.create = function(options) {
  var publisherOptions = {
    insertMode: 'append',
    showControls: false,
    width: '100%',
    height: '100%',
  };

  publisherOptions = Object.assign(options, publisherOptions);

  this.publisher = tokbox.initPublisher(
    this.container,
    publisherOptions,
    this.handleError
  );

  return this.publisher;
};

/**
 * Disable video stream and hide publisher element
 */
Publisher.prototype.disableVideo = function (publisherStream) {
  publisherStream.publishVideo(false);
  publisherStream.element.style.display = 'none';
};

Publisher.prototype.destroy = function() {
  this.publisher.destroy();
};

/**
 * @returns {boolean}
 */
Publisher.prototype.isScreensharing = function() {
  if (this.publisher === null) {
    return false;
  }

  return this.publisher.stream.videoType === STREAM_TYPE_SCREENSHARE;
};

/**
 * return DOM element of user's webcam, if active, else null
 */
Publisher.prototype.getCameraVideo = function() {
    if (this.publisher && this.publisher.stream && this.publisher.stream.videoType === STREAM_TYPE_CAMERA) {
        return this.publisher.element;
    }

    return null;
};

Publisher.prototype.handleError = function(error) {
  if (error) {
    console.error('Publisher error:', error);
  }
};

export default Publisher;
