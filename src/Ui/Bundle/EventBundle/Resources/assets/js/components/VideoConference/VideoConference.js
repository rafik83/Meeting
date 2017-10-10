'use strict';

var tokbox = require('@opentok/client');
var openTokLayout = require('opentok-layout-js');

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

  this.publisherContainer = element.querySelector('.publisher-container');
  this.layoutContainer = element.querySelector('.layout-container');
  this.helperContainer = element.querySelector('.video-helper');

  this.layout = openTokLayout.initLayoutContainer(this.layoutContainer).layout;

  this.publisher = new Publisher(this.publisherContainer);

  this.init();

  var resizeTimeout;
  window.onresize = function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function () {
      this.layout();
    }.bind(this), 20);
  }.bind(this);
}

/**
 * Initialize session and subscribe to new other stream
 */
VideoConference.prototype.init = function () {
  if (tokbox.checkSystemRequirements() !== 1) {
    alert('Votre navigateur n\'est pas compatible avec la video conférence en webRTC.');
  }

  this.session = tokbox.initSession(this.apiKey, this.sessionId);

  this.session.on('streamCreated', function (event) {
    var subscriberContainer = document.createElement('div');
    this.layoutContainer.appendChild(subscriberContainer);

    var subscriberManager = new Subscriber(this.session, subscriberContainer);
    subscriberManager.subscribe(event);

    var infoContainer = document.createElement('div');
    infoContainer.classList.add('subscriber-info');
    subscriberContainer.appendChild(infoContainer);

    this.layout();
  }.bind(this));

  this.session.on("streamDestroyed", function (event) {
    window.setTimeout(this.layout, 100);
  }.bind(this));

  this.session.on("sessionDisconnected", function (event) {
    this.layout();
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
      this.layout();
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
};

/**
 * Handle callback errors
 *
 * @param {Object} error
 */
VideoConference.prototype.handleError = function (error) {
  if (error) {
    alert('There was an error: ' + error.name + ', ' + error.message);
  }
};

module.exports = VideoConference;

