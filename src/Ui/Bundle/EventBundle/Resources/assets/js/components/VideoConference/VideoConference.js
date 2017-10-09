'use strict';

var tokbox = require('@opentok/client');

var Publisher = require('./Publisher');
var Subscriber = require('./Subscriber');

/**
 * @constructor
 *
 * @param {Node} element
 */
function VideoConference(element) {
  this.element = element;

  this.token = element.getAttribute('data-token');
  this.sessionId = element.getAttribute('data-session-id');
  this.apiKey = element.getAttribute('data-api-key');

  this.publisherContainer = element.querySelector('.publisher');
  this.subscriberContainer = element.querySelector('.subscriber');
  this.helperContainer = element.querySelector('.video-helper');

  this.publisher = new Publisher(this.publisherContainer);

  this.init();

  // Event Listener
  this.element.querySelector('.end-visio').addEventListener('click', this.disconnect.bind(this));
}

/**
 * Initialize session and subscribe to new other stream
 */
VideoConference.prototype.init = function () {
  if (tokbox.checkSystemRequirements() !== 1) {
    alert('Votre navigateur n\'est pas compatible avec la video conférence en webRTC.');
  }

  this.session = tokbox.initSession(this.apiKey, this.sessionId);

  // The Session object dispatches a streamCreated event
  // when a new stream (other than your own) is created in a session
  this.session.on('streamCreated', function (event) {
    var subscriberManager = new Subscriber(this.session, this.subscriberContainer);
    subscriberManager.subscribe(event);

    this.subscriberContainer.classList.remove('hide');
    this.helperContainer.classList.add('hide');
  }.bind(this));

  // When a stream, other than your own, leaves a session
  // the Session object dispatches a streamDestroyed event
  this.session.on("streamDestroyed", function (event) {
    console.log('Stream destroyed', event);
  }.bind(this));

  this.session.on("sessionDisconnected", function (event) {
    console.log("Session disconnected", event);
  });

  this.connect();
};

/**
 * Connect to the session, create and publish your stream
 */
VideoConference.prototype.connect = function () {
  this.session.connect(this.token, function (error) {
    if (!error) {
      // create video view
      var publisher = this.publisher.create();

      // publish video to other participant
      this.session.publish(publisher, this.handleError);

      // this.publisherContainer.classList.remove('hide');
      // this.helperContainer.classList.remove('hide');
    } else {
      console.log(error);
    }
  }.bind(this));
};

/**
 * Disconnect from the session
 */
VideoConference.prototype.disconnect = function () {
  this.session.disconnect();
  this.session.off();
  this.session = null;

  this.element.querySelector('.end-visio').classList.toggle('hide');

  this.publisherContainer.classList.add('hide');
  this.subscriberContainer.classList.add('hide');
  this.helperContainer.classList.add('hide');
};

/**
 * Handle callback errors
 *
 * @param {Object} error
 */
VideoConference.prototype.handleError = function (error) {
  if (error) {
    console.log('There was an error: ', error.name, error.message);
  }
};

module.exports = VideoConference;

