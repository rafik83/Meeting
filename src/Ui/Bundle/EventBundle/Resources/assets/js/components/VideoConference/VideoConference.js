'use strict';

var tokbox = require('@opentok/client');
var openTokLayout = require('opentok-layout-js');

var Publisher = require('./Publisher');
var Subscriber = require('./Subscriber');
var CHROME_EXTENSION_ID = 'alpphdcgnkkpafmlhllecaganiekhjcp';
var CHROME_EXTENSION_IS_INSTALLED = 'CHROME_EXTENSION_IS_INSTALLED';
var $ = require('jquery');
/**
 * @constructor
 *
 * @param {Element} element
 */
function VideoConference(element) {
  this.element = element;

  this.token = element.getAttribute('data-token');
  this.sessionId = element.getAttribute('data-session-id');
  this.apiKey = element.getAttribute('data-api-key');
  this.participantPresenceAction = element.getAttribute('data-participant-presence-action');

  this.notCompatibleBrowserMessage = element.getAttribute(
    'data-not-compatible-browser-message'
  );

  this.installScreenSharingExtensionMessage = element.getAttribute(
    'data-install-screensharing-extension-message'
  );

  this.accessDeniedErrorMessage = element.getAttribute(
    'data-user-denied-media-access'
  );

  this.publisherContainer = element.querySelector('.publisher-container');
  this.layoutContainer = element.querySelector('.layout-container');
  this.helperContainer = element.querySelector('.video-helper');

  var endMeetingButton = element.querySelector('.end-meeting');
  this.startScreenSharingButton = element.querySelector('#start-screensharing');
  this.endScreenSharingButton = element.querySelector('#end-screensharing');
  this.toggleAudioElement = element.querySelector('#toggle-audio');
  this.toggleVideoElement = element.querySelector('#toggle-video');

  // if (!window.opener) {
  //   endMeetingButton.classList.add('hide');
  // } else {
  endMeetingButton.addEventListener('click', this.disconnect.bind(this));
  // }

  this.layout = openTokLayout.initLayoutContainer(this.layoutContainer).layout;

  this.publisher = new Publisher(this.publisherContainer);
  this.publisherStream = null;

  var resizeTimeout;
  window.onresize = function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
      this.layout();
    }.bind(this), 20);
  }.bind(this);

  // Custom Events
  tokbox.registerScreenSharingExtension('chrome', CHROME_EXTENSION_ID, 2);

  this.startScreenSharingButton.addEventListener('click', this.preScreenshare.bind(this));
  this.endScreenSharingButton.addEventListener('click', this.endScreenshare.bind(this));

  this.toggleAudioElement.addEventListener('click', this.toggleAudio.bind(this));
  this.toggleVideoElement.addEventListener('click', this.toggleVideo.bind(this));

  document.addEventListener('webkitfullscreenchange', this.exitFullscreenHandler.bind(this), false);
  document.addEventListener('mozfullscreenchange', this.exitFullscreenHandler.bind(this), false);
  document.addEventListener('fullscreenchange', this.exitFullscreenHandler.bind(this), false);
  document.addEventListener('MSFullscreenChange', this.exitFullscreenHandler.bind(this), false);

  // check if chrome extension already installed
  if (this.isChrome()) {
    this.isChromeExtensionInstall(function(response) {
      localStorage.setItem(CHROME_EXTENSION_IS_INSTALLED, response === true ? '1' : '0');
    });
  }

  // Init
  this.init();
  this.saveParticipantPresence();
}

/**
 * Handle exit fullscreen and rebuild Tokbox UI layout
 */
VideoConference.prototype.exitFullscreenHandler = function() {
  if (document.webkitIsFullScreen
    || document.mozFullScreen
    || document.msFullscreenElement !== null
  ) {
    this.layout();
  }
};

/**
 * Initialize session and subscribe to new other stream
 */
VideoConference.prototype.init = function() {
  if (this.isNotIE() && tokbox.checkSystemRequirements() !== 1) {
    alert(this.notCompatibleBrowserMessage);
    return;
  }

  // Create Tokbox Session

  this.session = tokbox.initSession(this.apiKey, this.sessionId);

  // Session Event Listener

  this.session.on('streamCreated', function(event) {
    var subscriberContainer = document.createElement('div');
    this.layoutContainer.appendChild(subscriberContainer);

    var subscriberManager = new Subscriber(this.session, subscriberContainer);
    var subscriber = subscriberManager.subscribe(event);

    var fullscreenButton = this.createFullscreenButton();

    subscriber.element.appendChild(fullscreenButton);

    console.log('subscriberSTREAM', subscriber.stream.id);

    this.helperContainer.classList.add('hide');

    var infoContainer = document.createElement('div');
    infoContainer.classList.add('subscriber-info');
    subscriberContainer.appendChild(infoContainer);

    this.layout();
  }.bind(this));

  this.session.on('streamDestroyed', function() {
    window.setTimeout(this.layout, 100);
  }.bind(this));

  this.session.on('sessionDisconnected', function() {
    this.layout();
  });

  this.connect();
};

/**
 * Connect to the session, create and publish your stream
 */
VideoConference.prototype.connect = function() {
  this.session.connect(this.token, function(error) {
    if (!error) {
      this.publishStream();
    } else {
      console.log(error);
    }
  }.bind(this));
};

/**
 *  Publish your camera and microphone stream
 */
VideoConference.prototype.publishStream = function() {
  // create video view
  var publisher = this.publisher.create({});

  // publish video to other participant
  this.session.publish(publisher, this.handlePublish.bind(this));
  this.publisherStream = publisher;

  this.layout();
};

/**
 * Disconnect from the session
 */
VideoConference.prototype.disconnect = function() {
  this.session.disconnect();
  this.session.off();
  this.session = null;

  if (window.opener) {
    window.opener.location.reload(true);
  }

  window.close();
};

VideoConference.prototype.handlePublish = function(error) {
  if (error) {
    this.showError(error);
  }
};

/**
 * Callback after screensharing started
 *
 * @param {Object} error
 */
VideoConference.prototype.handlePublishScreensharing = function(error) {
  if (error) {
    this.showError(error);
  } else {
    if (this.publisherStream !== null) {
      this.publisher.disableVideo(this.publisherStream);
    }

    this.startScreenSharingButton.classList.add('hide');
    this.endScreenSharingButton.classList.remove('hide');
  }
};

VideoConference.prototype.showError = function(error) {
  switch (error.name) {
    case 'OT_USER_MEDIA_ACCESS_DENIED':
      alert(this.accessDeniedErrorMessage);
      break;
    default:
      alert('There was an error: ' + error.name + ', ' + error.message);
      break;
  }
};

/**
 * Handle installation of screensharing extension if needed
 */
VideoConference.prototype.preScreenshare = function () {
  if (this.session === null) {
    alert('You cannot start screensharing outside of a session');
    return;
  }

  if (this.isChrome()) {
    var isInstalled = parseInt(localStorage.getItem(CHROME_EXTENSION_IS_INSTALLED));

    if (isInstalled === '1') {
      this.screenshare();
      return;
    }

    this.installChromeExtension(function () {
      console.log('chrome extension installation succeeded, start screensharing');
      localStorage.setItem(CHROME_EXTENSION_IS_INSTALLED, '1');
      this.screenshare();
    }.bind(this), function(error) {
      console.log('Installation fail : ' + error);
      localStorage.setItem(CHROME_EXTENSION_IS_INSTALLED, '0');
    });

    return;
  }

  // otherwise start screensharing directly
  this.screenshare();
};

/**
 * Start screensharing
 */
VideoConference.prototype.screenshare = function() {
  tokbox.checkScreenSharingCapability(function(response) {
    if (!response.supported || response.extensionRegistered === false) {
      alert(this.notCompatibleBrowserMessage);
    } else if (response.extensionInstalled === false && (response.extensionRequired)) {
      alert(this.installScreenSharingExtensionMessage);
    } else {
      // start screensharing
      var publisher = this.publisher.create({
        videoSource: 'screen',
        publishAudio: true
      });

      this.session.publish(publisher, this.handlePublishScreensharing.bind(this));

      // stop screensharing
      publisher.on('mediaStopped', this.handleStopScreensharing.bind(this));
    }
  }.bind(this));
};

/**
 * End screensharing requested by user using the UI
 */
VideoConference.prototype.endScreenshare = function() {
  if (this.publisher.isScreensharing()) {
    this.publisher.destroy();
    this.handleStopScreensharing();
  }
};

/**
 * Handle stop screen sharing
 */
VideoConference.prototype.handleStopScreensharing = function() {
  this.publisherStream.publishVideo(true);
  this.publisherStream.element.style.display = 'block';
  this.startScreenSharingButton.classList.remove('hide');
  this.endScreenSharingButton.classList.add('hide');
};

/**
 * Create fullscreen button node element
 *
 * @returns {Element}
 */
VideoConference.prototype.createFullscreenButton = function() {
  var fullscreenButton = document.createElement('button');
  var icon = document.createElement('i');
  icon.classList.add('glyphicon');
  icon.classList.add('glyphicon-fullscreen');

  fullscreenButton.classList.add('btn');
  fullscreenButton.classList.add('btn-default');
  fullscreenButton.classList.add('start-fullscreen-button');
  fullscreenButton.appendChild(icon);

  return fullscreenButton;
};

/**
 * Check if user agent is not internet explorer
 *
 * @returns {boolean}
 */
VideoConference.prototype.isNotIE = function() {
  var userAgent = window.navigator.userAgent.toLowerCase(),
    appName = window.navigator.appName;

  return !( appName === 'Microsoft Internet Explorer' || // IE <= 10
    (appName === 'Netscape' && userAgent.indexOf('trident') > -1) ); // IE >= 11
};

/**
 * Toggle audio stream
 */
VideoConference.prototype.toggleAudio = function() {
  // if publisher stream is destroy because of stop screensharing, use previous stream
  var publisher = null;

  if (this.publisher.publisher.stream === null) {
    publisher = this.publisherStream;
  } else {
    publisher = this.publisher.isScreensharing() ?
      this.publisherStream : // get camera stream instead of screensharing stream
      this.publisher.publisher;
  }

  if (publisher.stream.hasAudio) {
    publisher.publishAudio(false);
    this.toggleAudioElement.classList.add('btn-off');
  } else {
    publisher.publishAudio(true);
    this.toggleAudioElement.classList.remove('btn-off');
  }
};

/**
 * Toggle video stream
 */
VideoConference.prototype.toggleVideo = function() {
  // if publisher stream is destroy because of stop screensharing, use previous stream
  var publisher = this.publisher.publisher.stream !== null ?
      this.publisher.publisher :
      this.publisherStream;

  if (publisher.stream.hasVideo) {
    publisher.publishVideo(false);
    this.toggleVideoElement.classList.add('btn-off');
  } else {
    publisher.publishVideo(true);
    this.toggleVideoElement.classList.remove('btn-off');
  }
};

/**
 * @returns {boolean}
 */
VideoConference.prototype.isChrome = function () {
  return /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
};

/**
 * Check if chrome extension is installed by send it a message
 *
 * @param callback
 */
VideoConference.prototype.isChromeExtensionInstall = function (callback) {
  chrome.runtime.sendMessage(
    CHROME_EXTENSION_ID,
    { type: 'isInstalled' },
    function(response) {
      callback(response);
    }
  );
};

/**
 * Show prompt install chrome extension
 *
 * @param successCallback
 * @param errorCallback
 */
VideoConference.prototype.installChromeExtension = function (successCallback, errorCallback) {
  chrome.webstore.install(
    null, // pick up using link rel="chrome-webstore-item"
    function() {
      successCallback();
    },
    function(error) {
      errorCallback(error);
    }
  );
};

VideoConference.prototype.saveParticipantPresence = function() {
    var _this = this;

    if (!_this.participantPresenceAction) {
        return;
    }

    // Save the first time the page is loaded
    $.post(_this.participantPresenceAction);

    // Save each 1 minute
    setInterval(function(){
        $.post(_this.participantPresenceAction);
    }, 60000);
};

module.exports = VideoConference;

