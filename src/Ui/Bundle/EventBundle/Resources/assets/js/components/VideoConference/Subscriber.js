'use strict';

/**
 * @param {Session} session
 * @param {Node} container
 * @param {Object} subscribersNameMapping
 * @constructor
 */
function Subscriber(
    session,
    container,
    subscribersNameMapping = {}
) {
  this.session = session;
  this.container = container;
  this.subscriber = null;
  this.subscriberId = null;
  this.subscribersNameMapping = subscribersNameMapping;
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
  const subscriberElement = event.target.element;

  if (this.subscriber && this.subscriber.stream) {
    this.subscriberId = this.subscriber.stream.name;
  }

  if (this.subscriberId && this.subscribersNameMapping.hasOwnProperty(this.subscriberId)) {
      let subscriberName = document.createElement('span');
      subscriberName.classList.add('visio-user-name');
      subscriberName.textContent = this.subscribersNameMapping[this.subscriberId];

      subscriberElement.appendChild(subscriberName);
  }

  const fullscreenButton = subscriberElement.querySelector('.start-fullscreen-button');

  if (!fullscreenButton) {
    return;
  }

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

export default Subscriber;
