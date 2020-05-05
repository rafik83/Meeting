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
  var subscriberOptions = {
    insertMode: 'append',
    showControls: false,
    width: '100%',
    height: '100%'
  };

  this.subscriber = this.session.subscribe(event.stream, this.container, subscriberOptions, this.handleError);

  // Subscriber events

  this.subscriber.on('videoElementCreated', this.onVideoElementCreated.bind(this));

  return this.subscriber;
};

/**
 * Dispatched to indicate the video element was created
 */
Subscriber.prototype.onVideoElementCreated = function (event) {
  var subscriberElement = event.target.element;

  var fullscreenButton = subscriberElement.querySelector('.start-fullscreen-button');

  // start fullscreen
  fullscreenButton.addEventListener("click", function() {

    if (document.fullscreenElement) {
      document.exitFullscreen();

      return;
    }

    var el = subscriberElement,
      rfs = el.requestFullscreen
        || el.webkitRequestFullScreen
        || el.mozRequestFullScreen
        || el.msRequestFullscreen
    ;

    el.style.width = '100%';
    el.style.height = '100%';
    el.style.display = 'block';

    rfs.call(el);
  });
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
