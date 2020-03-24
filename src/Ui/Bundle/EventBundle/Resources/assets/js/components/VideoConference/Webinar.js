'use strict';

var TokboxInstance = require('./TokboxInstance').TokboxInstance;
var CHROME_EXTENSION_URL = require('./TokboxInstance').CHROME_EXTENSION_URL;
var initLayoutContainer = require('opentok-layout-js');
var openTokTextChat = require('opentok-text-chat');
var Publisher = require('./Publisher');
var Subscriber = require('./Subscriber');
var Counter = require('./Counter');
var $ = require('jquery');

function Webinar(element, isSpeaker) {
    this.element = element;
    this.isSpeaker = isSpeaker;

    this.token = element.getAttribute('data-token');
    this.sessionId = element.getAttribute('data-session-id');
    this.apiKey = element.getAttribute('data-api-key');
    this.webinarEndTime = element.getAttribute('data-webinar-end-time');
    this.webinarStartTime = element.getAttribute('data-webinar-start-time');
    this.currentTime = element.getAttribute('data-current-time');
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

    this.chatContainer = element.querySelector('.chat-container');
    this.chatInstance = null;
    this.toggleChatElement = element.querySelector('#toggle-chat');
    if (this.toggleChatElement) {
        this.toggleChatElement.addEventListener('click', this.toggleChat.bind(this));
    }

    this.layoutContainer = element.querySelector('.layout-container');
    this.layout = initLayoutContainer(this.layoutContainer).layout;

    if (this.isSpeaker) {
        this.timerContainer = element.querySelector('.timer');
        this.countDownContainer = element.querySelector('.timer span.countdown');

        this.endScreenSharingButton = element.querySelector('#end-screensharing');
        this.endScreenSharingButton.addEventListener('click', this.endScreenshare.bind(this));

        this.toggleAudioElement = element.querySelector('#toggle-audio');
        this.toggleAudioElement.addEventListener('click', this.toggleAudio.bind(this));

        this.toggleVideoElement = element.querySelector('#toggle-video');
        this.toggleVideoElement.addEventListener('click', this.toggleVideo.bind(this));

        this.startScreenSharingButton = element.querySelector('#start-screensharing');
        this.startScreenSharingButton.addEventListener('click', this.screenshare.bind(this));

        const endWebinarButton = element.querySelector('.end-webinar');
        if (endWebinarButton) {
            endWebinarButton.addEventListener('click', this.disconnect.bind(this));
        }

        const publisherContainer = document.createElement('div');
        this.layoutContainer.appendChild(publisherContainer);

        this.publisher = new Publisher(publisherContainer);
        this.publisherStream = null;
    }

    var resizeTimeout;
    window.onresize = function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function () {
            this.layout();
        }.bind(this), 20);
    }.bind(this);

    document.addEventListener('webkitfullscreenchange', this.exitFullscreenHandler.bind(this), false);
    document.addEventListener('mozfullscreenchange', this.exitFullscreenHandler.bind(this), false);
    document.addEventListener('fullscreenchange', this.exitFullscreenHandler.bind(this), false);
    document.addEventListener('MSFullscreenChange', this.exitFullscreenHandler.bind(this), false);

    // Init
    this.init();
    this.countDownBeforeEnd();
}

/**
 * Handle exit fullscreen and rebuild Tokbox UI layout
 */
Webinar.prototype.exitFullscreenHandler = function () {
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
Webinar.prototype.init = function () {
    if (this.isNotIE() && TokboxInstance.checkSystemRequirements() !== 1) {
        alert(this.notCompatibleBrowserMessage);
        return;
    }

    this.session = TokboxInstance.initSession(this.apiKey, this.sessionId);

    this.session.on('streamCreated', function (event) {
        const subscriberContainer = document.createElement('div');
        this.layoutContainer.appendChild(subscriberContainer);

        const subscriberManager = new Subscriber(this.session, subscriberContainer);
        const subscriber = subscriberManager.subscribe(event);

        const fullscreenButton = this.createFullscreenButton();
        subscriber.element.appendChild(fullscreenButton);

        const infoContainer = document.createElement('div');
        infoContainer.classList.add('subscriber-info');
        subscriberContainer.appendChild(infoContainer);

        this.layout();
    }.bind(this));

    let connectionCount = 0;

    this.session.on("connectionCreated", function(event) {
        connectionCount++;
        console.log('connectionCreated, total ' + connectionCount)
    });
    this.session.on("connectionDestroyed", function(event) {
        connectionCount--;
        console.log('connectionDestroyed, total ' + connectionCount)
    });

    this.session.on('streamDestroyed', function () {
        window.setTimeout(this.layout, 100);
    }.bind(this));

    this.session.on('sessionDisconnected', function () {
        this.layout();
    });

    this.connect();
};

/**
 * Connect to the session, create and publish your stream
 */
Webinar.prototype.connect = function () {
    this.session.connect(this.token, function (error) {
        if (this.toggleChatElement) {
            this.toggleChatElement.classList.remove('hide');
        }

        if (!error) {
            if (this.isSpeaker) {
                this.publishStream();
            }

            return;
        }

        console.error(error);
    }.bind(this));
};

/**
 * Open chat
 */
Webinar.prototype.initChat = function () {
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
Webinar.prototype.publishStream = function () {
    const publisher = this.publisher.create({});

    // publish video to other participant
    this.session.publish(publisher, this.handlePublish.bind(this));
    this.publisherStream = publisher;

    this.layout();
};

/**
 * Disconnect from the session
 */
Webinar.prototype.disconnect = function () {
    this.session.disconnect();
    this.session.off();
    this.session = null;

    if (window.opener) {
        window.opener.location.reload(true);
    }

    window.close();
};

Webinar.prototype.handlePublish = function (error) {
    if (error) {
        this.showError(error);
    }
};

/**
 * Callback after screensharing started
 *
 * @param {Object} error
 */
Webinar.prototype.handlePublishScreensharing = function (error) {
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

Webinar.prototype.showError = function (error) {
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
Webinar.prototype.screenshare = function () {
    if (this.session === null) {
        alert('You cannot start screensharing outside of a session');
        return;
    }

    TokboxInstance.checkScreenSharingCapability(function (response) {
        if (!response.supported || response.extensionRegistered === false) {
            alert(this.notCompatibleBrowserMessage);
            return;
        }

        if (response.extensionRegistered && response.extensionInstalled === false) {
            this.installChromeExtension();
            return;
        }

        // start screensharing
        var publisher = this.publisher.create({
            videoSource: 'screen',
            publishAudio: true
        });

        this.session.publish(publisher, this.handlePublishScreensharing.bind(this));

        // stop screensharing
        publisher.on('mediaStopped', this.handleStopScreensharing.bind(this));
    }.bind(this));
};

/**
 * End screensharing requested by user using the UI
 */
Webinar.prototype.endScreenshare = function () {
    if (this.publisher.isScreensharing()) {
        this.publisher.destroy();
        this.handleStopScreensharing();
    }
};

/**
 * Handle stop screen sharing
 */
Webinar.prototype.handleStopScreensharing = function () {
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
Webinar.prototype.createFullscreenButton = function () {
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
Webinar.prototype.isNotIE = function () {
    var userAgent = window.navigator.userAgent.toLowerCase(),
        appName = window.navigator.appName;

    return !(appName === 'Microsoft Internet Explorer' || // IE <= 10
        (appName === 'Netscape' && userAgent.indexOf('trident') > -1)); // IE >= 11
};

/**
 * Toggle button
 *
 * @param element button
 * @param bool    isOn
 */
Webinar.prototype.toggleButton = function (button, isOn) {
    if (isOn) {
        button.classList.remove('btn-off');
        return;
    }

    button.classList.add('btn-off');
};

/**
 * Toggle Chat
 */
Webinar.prototype.toggleChat = function () {
    if (this.chatContainer.classList.contains('hide')) {
        this.toggleButton(this.toggleChatElement, true);
        this.chatContainer.classList.remove('hide');
        this.initChat();
        this.chatInstance.showTextChat();
        this.chatInstance.deliverUnsentMessages();

        return;
    }

    this.chatInstance.hideTextChat();
    this.chatContainer.classList.add('hide');
    this.toggleButton(this.toggleChatElement, false);
};

/**
 * Toggle audio stream
 */
Webinar.prototype.toggleAudio = function () {
    // if publisher stream is destroy because of stop screensharing, use previous stream
    let publisher = null;

    if (this.publisher.publisher.stream === null) {
        publisher = this.publisherStream;
    } else {
        publisher = this.publisher.isScreensharing() ?
            this.publisherStream : // get camera stream instead of screensharing stream
            this.publisher.publisher;
    }

    if (publisher.stream.hasAudio) {
        publisher.publishAudio(false);
        this.toggleButton(this.toggleAudioElement, false);
    } else {
        this.toggleButton(this.toggleAudioElement, true);
        publisher.publishAudio(true);
    }
};

/**
 * Toggle video stream
 */
Webinar.prototype.toggleVideo = function () {
    // if publisher stream is destroy because of stop screensharing, use previous stream
    const publisher = this.publisher.publisher.stream !== null ?
        this.publisher.publisher :
        this.publisherStream;

    if (publisher.stream.hasVideo) {
        publisher.publishVideo(false);
        this.toggleButton(this.toggleVideoElement, false);
    } else {
        publisher.publishVideo(true);
        this.toggleButton(this.toggleVideoElement, true);
    }
};

Webinar.prototype.installChromeExtension = function () {
    alert(this.installScreenSharingExtensionMessage);
    window.open(CHROME_EXTENSION_URL, '_blank');
};

Webinar.prototype.countDownBeforeEnd = function () {
    if (!this.countDownContainer) {
        return;
    }

    new Counter(this.webinarStartTime, this.webinarEndTime, this.currentTime, this.countDownContainer, this.timerContainer);
};

module.exports = Webinar;

