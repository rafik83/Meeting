'use strict';

var TokboxInstance = require('./TokboxInstance').TokboxInstance;
var CHROME_EXTENSION_URL = require('./TokboxInstance').CHROME_EXTENSION_URL;
var initLayoutContainer = require('opentok-layout-js');
var openTokTextChat = require('opentok-text-chat');
var Publisher = require('./Publisher');
var Subscriber = require('./Subscriber');
var Counter = require('./Counter');
var $ = require('jquery');
var Settings = require('./Settings');

require('bootstrap/js/modal');

/**
 * @constructor
 *
 * @param {Element} element
 */
function VideoConference(
    element,
    useSettings = true
) {
  this.element = element;
  this.typeScreenShare = 'screen';

  this.token = element.getAttribute('data-token');
  this.session = null;
  this.sessionId = element.getAttribute('data-session-id');
  this.apiKey = element.getAttribute('data-api-key');
  this.participantPresenceAction = element.getAttribute('data-participant-presence-action');

  this.timeRemaining = element.getAttribute('data-time-remaining');
  this.warningRemainingTime = element.getAttribute('data-warning-time-remaining');

  this.chatWaitingMessage = element.getAttribute('data-chat-waiting-message');
  this.userCompleteName = element.getAttribute('data-user-complete-name');

  this.notCompatibleBrowserMessage = element.getAttribute(
    'data-not-compatible-browser-message'
  );

  this.installScreenSharingExtensionMessage = element.getAttribute(
    'data-install-screensharing-extension-message'
  );

  this.accessDeniedErrorMessage = element.getAttribute(
    'data-user-denied-media-access'
  );

  this.layoutContainer = element.querySelector('.layout-container');
  this.chatContainer = element.querySelector('.chat-container');
  this.timerContainer = element.querySelector('.timer');
  this.countDownContainer = element.querySelector('.timer span.countdown');

  this.startScreenSharingButton = element.querySelector('#start-screensharing');
  this.endScreenSharingButton = element.querySelector('#end-screensharing');
  this.toggleAudioElement = element.querySelector('#toggle-audio');
  this.toggleVideoElement = element.querySelector('#toggle-video');
  this.toggleChatElement = element.querySelector('#toggle-chat');

  this.subscribers = [];
  this.subscribersNameMapping = element.getAttribute('data-subscriber-mapping');
  this.currentUserId = element.getAttribute('data-current-user-id');

  if (this.subscribersNameMapping) {
      this.subscribersNameMapping = JSON.parse(this.subscribersNameMapping);
  } else {
      this.subscribersNameMapping = {};
  }

  this.publisherStream = null;
  this.publisherScreen = null;

  this.chatInstance = null;
  this.hasScreenSharing = false;

  this.endMeetingButton = this.element.querySelector('.end-meeting');
  if (this.endMeetingButton) {
    this.endMeetingButton.addEventListener('click', this.disconnect.bind(this));
  }

  this.settingsContainer = this.element.querySelector('[data-meeting-settings-container]');
  this.meetingWaitingMessage = this.element.querySelector('[data-meeting-waiting-message]');
  this.meetingHelperWaitingContainer = this.element.querySelector('[data-meeting-waiting-helper]');
  this.endSound = this.element.getAttribute('data-visio-meeting-end-sound');
  this.hasEndMessageOrImage = this.element.getAttribute('data-visio-meeting-end-warning');
  this.endContainer = this.element.querySelector('[data-visio-meeting-end-container]');
  this.mediaShareScreenShareStatusMessage = this.element.getAttribute('data-media-screenShareStatus-message');

  this.useSettings = useSettings;

  if (this.useSettings) {
    this.settings = new Settings(
        this.settingsContainer.querySelector('#video-settings-section'),
        this.join.bind(this),
        false
    );
    this.settings.init();
  } else {
    this.join();
  }

  this.countDownBeforeEnd();
}

VideoConference.prototype.join = function () {
  this.layout = initLayoutContainer(this.layoutContainer).layout;
  this.publisher = new Publisher(this.layoutContainer);

  var resizeTimeout;
  window.onresize = function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        this.layout();
    }.bind(this), 20);
  }.bind(this);

  this.startScreenSharingButton.addEventListener('click', this.screenshare.bind(this));
  this.endScreenSharingButton.addEventListener('click', this.handleStopScreensharing.bind(this));

  this.toggleAudioElement.addEventListener('click', this.toggleAudio.bind(this));
  this.toggleVideoElement.addEventListener('click', this.toggleVideo.bind(this));

  if (this.toggleChatElement) {
      this.toggleChatElement.addEventListener('click', this.toggleChat.bind(this));
  }

  const fullscreenButton = this.createFullscreenButton();
  this.element.appendChild(fullscreenButton);

  fullscreenButton.addEventListener("click", () => {
    if (document.fullscreenElement) {
      document.exitFullscreen();
      return;
    }

    const element = this.element;

    const rfs = element.requestFullscreen
        || element.webkitRequestFullScreen
        || element.mozRequestFullScreen
        || element.msRequestFullscreen
    ;

    rfs.call(element);
  });

  document.addEventListener('webkitfullscreenchange', this.exitFullscreenHandler.bind(this), false);
  document.addEventListener('mozfullscreenchange', this.exitFullscreenHandler.bind(this), false);
  document.addEventListener('fullscreenchange', this.exitFullscreenHandler.bind(this), false);
  document.addEventListener('MSFullscreenChange', this.exitFullscreenHandler.bind(this), false);

  this.saveParticipantPresence();
  this.showElement(this.meetingHelperWaitingContainer);
  this.showElement(this.meetingWaitingMessage);

  if (this.settingsContainer) {
    this.hideElement(this.settingsContainer);
  }

  this.init();
};

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
  if (this.isNotIE() && TokboxInstance.checkSystemRequirements() !== 1) {
    alert(this.notCompatibleBrowserMessage);
    return;
  }

  this.session = TokboxInstance.initSession(this.apiKey, this.sessionId);

  this.session.on('streamCreated', function(event) {
    // Show chat and screen share buttons when there are another participant
    this.showElement(this.toggleChatElement);
    this.showElement(this.startScreenSharingButton);

    const subscriberManager = new Subscriber(
        this.session,
        this.layoutContainer,
        this.subscribersNameMapping
    );
    const subscriber = subscriberManager.subscribe(event);

    if (this.isScreenShareStream(event.stream)) {
      this.hasScreenSharing = true;
      this.minimizeAllSubscribers();
      this.maximize(subscriber.element);
    } else {
      this.autoMaximize(subscriber);
    }

    if (!this.hasScreenSharing && !this.subscribers.length) {
        this.maximize(subscriber.element);
    }

    this.subscribers.push(subscriber);

    this.hideElement(this.meetingHelperWaitingContainer);
    this.layout();

  }.bind(this));

  this.session.on('streamDestroyed', function (event) {
    event.preventDefault();

    this.session.getSubscribersForStream(event.stream).forEach((subscriber) => {
      this.subscribers = this.subscribers.filter(stream => stream !== subscriber);
      subscriber.element.classList.remove('ot-layout');

      if (this.isScreenShareStream(event.stream)) {
        this.hasScreenSharing = false;
        this.maximizeAllSubscribers();
      }

      setTimeout(() => {
        subscriber.destroy();
        this.layout();
      }, 200);
    });
  }.bind(this));

  this.session.on('sessionDisconnected', function() {
    this.layout();
  }.bind(this));

  this.connect();
};

VideoConference.prototype.maximize = function(element) {
  element.classList.add('OT_big');
};

VideoConference.prototype.minimize = function(element) {
  element.classList.remove('OT_big');
};

VideoConference.prototype.minimizeAllSubscribers = function() {
  this.subscribers.forEach((subscriber) => {
    this.minimize(subscriber.element);
  });
};

VideoConference.prototype.maximizeAllSubscribers = function() {
  this.subscribers.forEach((subscriber) => {
    this.maximize(subscriber.element);
  });
};

// todo: factorize with Webinar code after https://github.com/proximum/vimeet/pull/2719 is merged
VideoConference.prototype.autoMaximize = function(subscriber) {
  var activity = null;
  subscriber.on('audioLevelUpdated', function(event) {
    if (this.hasScreenSharing) {
        return;
    }
    if (this.subscribers.length < 2) {
        return;
    }

    const now = Date.now();
    if (event.audioLevel > 0.2) {
      if (!activity) {
        activity = {timestamp: now, talking: false};
      } else if (activity.talking) {
        activity.timestamp = now;
      } else if (now - activity.timestamp > 1000) {
        // detected audio activity for more than 1s for the first time.
        activity.talking = true;
        this.minimizeAllSubscribers();
        this.maximize(subscriber.element);
        this.layout();
      }
    } else if (activity && now - activity.timestamp > 2000) {
      // detected low audio activity for more than 2s
      if (activity.talking) {
        this.maximizeAllSubscribers();
        this.layout();
      }
      activity = null;
    }
  }.bind(this));
};


/**
 * Connect to the session, create and publish your stream
 */
VideoConference.prototype.connect = function() {
  this.session.connect(this.token, function(error) {
    this.showElement(this.toggleAudioElement);
    this.showElement(this.toggleVideoElement);

    if (!error) {
      this.publishStream();

      return;
    }

    console.error(error);
  }.bind(this));
};

/**
 * Open chat
 */
VideoConference.prototype.initChat = function () {
  if (this.chatInstance) {
    return;
  }

  this.chatInstance = new openTokTextChat({
    session: this.session,
    sender: {
      alias: this.userCompleteName,
    },
    textChatContainer: '.chat-container',
    waitingMessage: this.chatWaitingMessage,
    alwaysOpen: true
  });
};

/**
 *  Publish your camera and microphone stream
 */
VideoConference.prototype.publishStream = function() {
  const defaultOptions = {
    name: this.currentUserId ? this.currentUserId : null
  };

  if (this.useSettings) {
    // publish video to other participant
    var publisher = this.publisher.create({
      ...defaultOptions,
      audioSource: this.settings.getAudioSource(),
      videoSource: this.settings.getVideoSource(),
    });
  } else {
    var publisher = this.publisher.create(defaultOptions);
  }

  publisher.on('videoElementCreated', this.onVideoElementCreated.bind(this));
  this.session.publish(publisher, this.handlePublish.bind(this));
  this.publisherStream = publisher;

  this.layout();
};

VideoConference.prototype.onVideoElementCreated = function (event) {
    const publisherElement = event.target.element;

    // Show user name on video element.
    if (this.subscribersNameMapping.hasOwnProperty(this.currentUserId)) {
        let publisherName = document.createElement('span');
        publisherName.classList.add('visio-user-name');
        publisherName.textContent = this.subscribersNameMapping[this.currentUserId];

        publisherElement.appendChild(publisherName);
    }
};

/**
 * Disconnect from the session
 */
VideoConference.prototype.disconnect = function() {
  if (null !== this.session) {
    this.session.disconnect();
    this.session.off();
    this.session = null;
  }

  if (this.endMeetingButton) {
      const redirectLink = this.endMeetingButton.getAttribute('data-visio-end-redirect-link');

      if (redirectLink) {
          window.location.replace(redirectLink);

          return;
      }
  }

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
    console.error(error);
    this.showError(error);
    this.handleStopScreensharing();

    return;
  }

  this.startScreenSharingButton.classList.add('hide');
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
 * Start screensharing
 */
VideoConference.prototype.screenshare = function() {
  if (this.session === null) {
      alert('You cannot start screensharing outside of a session');
      return;
  }

  TokboxInstance.checkScreenSharingCapability(function(response) {
    if (!response.supported || response.extensionRegistered === false) {
      alert(this.notCompatibleBrowserMessage);
      return;
    }

    if (response.extensionRegistered && response.extensionInstalled === false) {
      this.installChromeExtension();
      return;
    }

    this.publisherScreen = new Publisher(null);
    const publisherScreen = this.publisherScreen.create({
      videoSource: 'screen',
      publishAudio: true,
      name: this.currentUserId ? this.currentUserId : null,
      insertDefaultUI: false,
    });

    const endSharingButton = document.createElement('button');
    endSharingButton.textContent = this.endScreenSharingButton.textContent;
    endSharingButton.classList.add('btn');
    endSharingButton.classList.add('btn-primary');
    endSharingButton.addEventListener('click', this.handleStopScreensharing.bind(this));

    this.screenElement = document.createElement('div');
    this.screenElement.classList.add('screen-share-in-progress');
    const screenCenteredElement = document.createElement('div');
    screenCenteredElement.textContent = this.mediaShareScreenShareStatusMessage;
    screenCenteredElement.appendChild(document.createElement('hr'));
    screenCenteredElement.appendChild(endSharingButton);

    this.screenElement.appendChild(screenCenteredElement);
    this.layoutContainer.appendChild(this.screenElement);

    this.hasScreenSharing = true;
    this.session.publish(publisherScreen, this.handlePublishScreensharing.bind(this));
    this.minimizeAllSubscribers();
    this.maximize(this.screenElement);
    this.layout();

    publisherScreen.on('mediaStopped', this.handleStopScreensharing.bind(this));
  }.bind(this));
};

/**
 * Handle stop screen sharing
 */
VideoConference.prototype.handleStopScreensharing = function() {
  if (!this.publisherScreen) {
    return;
  }

  this.hasScreenSharing = false;
  this.publisherScreen.destroy();
  this.screenElement.remove();
  this.maximizeAllSubscribers();
  this.layout();
  this.startScreenSharingButton.classList.remove('hide');
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
 * Toggle button
 *
 * @param element button
 * @param bool    isOn
 */
VideoConference.prototype.toggleButton = function (button, isOn) {
  if (isOn) {
    button.classList.remove('btn-off');
    return;
  }

  button.classList.add('btn-off');
};

/**
 * Toggle Chat
 */
VideoConference.prototype.toggleChat = function () {
  if (this.chatContainer.classList.contains('hide')) {
    this.toggleButton(this.toggleChatElement, true);
    this.chatContainer.classList.remove('hide');
    this.initChat();
    this.chatInstance.showTextChat();
    this.chatInstance.deliverUnsentMessages();
    this.element.classList.add('chat-opened');
    this.layout();

    return;
  }

  this.element.classList.remove('chat-opened');
  this.chatInstance.hideTextChat();
  this.chatContainer.classList.add('hide');
  this.toggleButton(this.toggleChatElement, false);
  this.layout();
};

/**
 * Toggle audio stream
 */
VideoConference.prototype.toggleAudio = function() {
  const publisher = this.publisherStream;
  if (publisher.stream) {
      const enableAudio = !publisher.stream.hasAudio;
      publisher.publishAudio(enableAudio);
      this.toggleButton(this.toggleAudioElement, enableAudio);
  } else {
    console.warn('publisher stream not available');
  }
};

/**
 * Toggle video stream
 */
VideoConference.prototype.toggleVideo = function() {
  const publisher = this.publisherStream;
  const enableVideo = !publisher.stream.hasVideo;
  publisher.publishVideo(enableVideo);
  this.toggleButton(this.toggleVideoElement, enableVideo);
};

VideoConference.prototype.installChromeExtension = function () {
    alert(this.installScreenSharingExtensionMessage);
    window.open(CHROME_EXTENSION_URL, '_blank');
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

VideoConference.prototype.countDownBeforeEnd = function() {
    if (!this.countDownContainer) {
      return;
    }

    const countDownEndCallback = () => {
        if (this.hasEndMessageOrImage) {
            $(this.endContainer).modal();
        }

        if (this.endSound) {
            const endSoundAudio = new Audio(this.endSound);
            endSoundAudio.play();
        }
    };

    new Counter(
        parseInt(this.timeRemaining, 10),
        parseInt(this.warningRemainingTime, 10),
        this.countDownContainer,
        this.timerContainer,
        countDownEndCallback.bind(this)
    );
};

VideoConference.prototype.isScreenShareStream = function (stream) {
  return this.typeScreenShare === stream.videoType;
};

VideoConference.prototype.hideElement = function (element) {
    if (!element) {
        return;
    }

    element.classList.add('hide');
};

VideoConference.prototype.showElement = function (element) {
    if (!element) {
        return;
    }

    element.classList.remove('hide');
};

module.exports = VideoConference;
