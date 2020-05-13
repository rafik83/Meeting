'use strict';

var TokboxInstance = require('./TokboxInstance').TokboxInstance;
var CHROME_EXTENSION_URL = require('./TokboxInstance').CHROME_EXTENSION_URL;
var initLayoutContainer = require('opentok-layout-js');
var openTokTextChat = require('opentok-text-chat');
var Publisher = require('./Publisher');
var Subscriber = require('./Subscriber');
var Counter = require('./Counter');

function Webinar(element, isSpeaker) {
    this.element = element;
    this.isSpeaker = isSpeaker;
    this.typeScreenShare = 'screen';

    this.token = element.getAttribute('data-token');
    this.sessionId = element.getAttribute('data-session-id');
    this.apiKey = element.getAttribute('data-api-key');
    this.webinarEndTime = element.getAttribute('data-webinar-end-time');
    this.webinarStartTime = element.getAttribute('data-webinar-start-time');
    this.currentTime = element.getAttribute('data-current-time');
    this.chatWaitingMessage = element.getAttribute('data-chat-waiting-message');
    this.userCompleteName = element.getAttribute('data-user-complete-name');
    this.helperContainer = element.querySelector('.video-helper');

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
    this.toggleChatElement.addEventListener('click', this.toggleChat.bind(this));

    this.webinarWaitingMessage = element.querySelector('[data-webinar-waiting-message]');

    this.joinButton = element.querySelector('[data-webinar-join-button]');
    this.joinButton.addEventListener('click', this.join.bind(this));

    this.layoutContainer = element.querySelector('.layout-container');
    this.layout = initLayoutContainer(this.layoutContainer).layout;

    this.hasScreenSharing = false;

    const endWebinarButton = element.querySelector('.end-webinar');

    if (endWebinarButton) {
        endWebinarButton.addEventListener('click', this.disconnect.bind(this));
    }
    this.timerContainer = element.querySelector('.timer');
    this.countDownContainer = element.querySelector('.timer span.countdown');

    if (this.isSpeaker) {
        this.viewersCount = 0;
        this.viewersContainer = element.querySelector('.viewers');
        this.thereIsAlreadyAScreenShareInProgressMessage = element.getAttribute('data-screen-share-already-in-progress-message');

        this.endScreenSharingButton = element.querySelector('#end-screensharing');
        this.endScreenSharingButton.addEventListener('click', this.handleStopScreensharing.bind(this));

        this.toggleAudioElement = element.querySelector('#toggle-audio');
        this.toggleAudioElement.addEventListener('click', this.toggleAudio.bind(this));
        this.enableAudio = true;

        this.toggleVideoElement = element.querySelector('#toggle-video');
        this.toggleVideoElement.addEventListener('click', this.toggleVideo.bind(this));
        this.enableVideo = true;

        this.startScreenSharingButton = element.querySelector('#start-screensharing');
        this.startScreenSharingButton.addEventListener('click', this.screenshare.bind(this));

        this.publisher = new Publisher(this.layoutContainer);
    }

    this.subscribers = [];

    let resizeTimeout;
    window.onresize = function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function () {
            this.layout();
        }.bind(this), 20);
    }.bind(this);

    const fullscreenButton = this.createFullscreenButton();
    this.element.appendChild(fullscreenButton);
    fullscreenButton.addEventListener('click', () => {
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

    this.countDownBeforeEnd();
}

Webinar.prototype.join = function () {
    this.hideElement(this.joinButton);
    this.showElement(this.webinarWaitingMessage);
    this.init();
};

/**
 * Initialize session and subscribe to new other stream
 */
Webinar.prototype.init = function () {
    if (this.isNotIE() && TokboxInstance.checkSystemRequirements() !== 1) {
        alert(this.notCompatibleBrowserMessage);
        return;
    }

    this.session = TokboxInstance.initSession(this.apiKey, this.sessionId, {connectionEventsSuppressed: !this.isSpeaker});

    this.session.on('streamCreated', function (event) {
        this.hideElement(this.helperContainer);

        const subscriberManager = new Subscriber(this.session, this.layoutContainer);
        const subscriber = subscriberManager.subscribe(event);

        if (this.isScreenShareStream(event.stream)) {
            this.hasScreenSharing = true;
            this.hidePublisher();
            this.hideSubscribers();
        } else {
            this.subscribers.push(subscriber);
        }

        if (this.hasScreenSharing && !this.isScreenShareStream(subscriber.stream)) {
            this.hidePublisher();
            this.hideElement(subscriber.element);
        }

        this.layout();
    }.bind(this));

    this.session.on('connectionCreated', function (event) {
        ++this.viewersCount;
        this.updateViewers();
    }.bind(this));

    this.session.on('connectionDestroyed', function (event) {
        --this.viewersCount;
        this.updateViewers();
    }.bind(this));

    this.session.on('streamDestroyed', function (event) {
        event.preventDefault();

        this.session.getSubscribersForStream(event.stream).forEach((subscriber) => {
            this.subscribers = this.subscribers.filter(stream => stream !== subscriber);

            subscriber.element.classList.remove('ot-layout');

            setTimeout(() => {
                subscriber.destroy();
                this.layout();
            }, 200);
        });

        if (this.isScreenShareStream(event.stream)) {
            this.hasScreenSharing = false;
            this.showPublisher();
            this.showSubscribers();
        }
    }.bind(this));

    this.session.on('sessionDisconnected', function () {
        this.layout();
    });

    this.connect();
};

Webinar.prototype.updateViewers = function () {
    // remove speaker
    const viewersCount = this.viewersCount - 1;

    this.viewersContainer.textContent = '' + viewersCount;
};

/**
 * Connect to the session, create and publish your stream
 */
Webinar.prototype.connect = function () {
    this.session.connect(this.token, function (error) {
        this.showElement(this.toggleChatElement);
        this.showElement(this.toggleAudioElement);
        this.showElement(this.toggleVideoElement);
        this.showElement(this.startScreenSharingButton);

        if (!error) {
            if (this.isSpeaker) {
                this.publishStream();
            }

            return;
        }

        console.error(error);
    }.bind(this));
};

Webinar.prototype.hideElement = function (element) {
    if (!element) {
        return;
    }

    element.classList.add('hide');
};

Webinar.prototype.showElement = function (element) {
    if (!element) {
        return;
    }

    element.classList.remove('hide');
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
    this.hideElement(this.helperContainer);
    const publisher = this.publisher.create({});

    this.session.publish(publisher, this.handlePublish.bind(this));
    publisher.publishVideo(this.enableVideo);
    publisher.publishAudio(this.enableAudio);

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
    if (!this.isSpeaker) {
        return;
    }

    if (this.session === null) {
        alert('You cannot start screensharing outside of a session');
        return;
    }

    if (this.hasScreenSharing) {
        alert(this.thereIsAlreadyAScreenShareInProgressMessage);
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

        this.hidePublisher();
        this.hideSubscribers();

        this.publisherScreen = new Publisher(this.layoutContainer);
        const publisherScreen = this.publisherScreen.create({
            videoSource: this.typeScreenShare,
            publishAudio: true
        });

        this.session.publish(publisherScreen, this.handlePublishScreensharing.bind(this));
        this.layout();

        publisherScreen.on('mediaStopped', this.handleStopScreensharing.bind(this));
    }.bind(this));
};

/**
 * Callback after screensharing started
 *
 * @param {Object} error
 */
Webinar.prototype.handlePublishScreensharing = function (error) {
    if (error) {
        console.error(error);
        this.showError(error);
        this.handleStopScreensharing();

        return;
    }

    this.hasScreenSharing = true;
    this.layout();

    this.hideElement(this.startScreenSharingButton);
    this.showElement(this.endScreenSharingButton);
};

/**
 * Handle stop screen sharing
 */
Webinar.prototype.handleStopScreensharing = function () {
    if (this.publisherScreen) {
        this.publisherScreen.destroy();
    }

    this.hasScreenSharing = false;

    this.showPublisher();
    this.showSubscribers();
    this.layout();

    this.showElement(this.startScreenSharingButton);
    this.hideElement(this.endScreenSharingButton);
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

Webinar.prototype.exitFullscreenHandler = function () {
    if (document.webkitIsFullScreen
        || document.mozFullScreen
        || document.msFullscreenElement !== null
    ) {
        this.layout();
    }
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
        this.showElement(this.chatContainer);
        this.initChat();
        this.chatInstance.showTextChat();
        this.chatInstance.deliverUnsentMessages();
        this.element.classList.add('chat-opened');
        this.layout();

        return;
    }

    this.element.classList.remove('chat-opened');
    this.hideElement(this.chatContainer);
    this.chatInstance.hideTextChat();
    this.toggleButton(this.toggleChatElement, false);
    this.layout();
};

/**
 * Toggle audio stream
 */
Webinar.prototype.toggleAudio = function () {
    if (!this.publisher.publisher) {
        return;
    }

    const publisher = this.publisher.publisher;
    const enableAudio = !publisher.stream.hasAudio;

    publisher.publishAudio(enableAudio);

    this.enableAudio = enableAudio;
    this.toggleButton(this.toggleAudioElement, enableAudio);
};

/**
 * Toggle video stream
 */
Webinar.prototype.toggleVideo = function () {
    if (!this.publisher.publisher) {
        return;
    }

    const publisher = this.publisher.publisher;
    const enableVideo = !publisher.stream.hasVideo;

    publisher.publishVideo(enableVideo);

    this.enableVideo = enableVideo;
    this.toggleButton(this.toggleVideoElement, enableVideo);
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

Webinar.prototype.hidePublisher = function () {
    if (!this.publisher) {
        return;
    }

    const publisherStream = this.publisher.publisher;
    publisherStream.publishVideo(false);
    this.hideElement(publisherStream.element);
    this.hideElement(this.toggleVideoElement);
};

Webinar.prototype.showPublisher = function () {
    if (!this.publisher) {
        return;
    }

    const publisherStream = this.publisher.publisher;
    publisherStream.publishVideo(this.enableVideo);

    this.showElement(publisherStream.element);
    this.showElement(this.toggleVideoElement);
};

Webinar.prototype.hideSubscribers = function () {
    this.subscribers.forEach((subscriber) => {
        this.hideElement(subscriber.element);
    });
};

Webinar.prototype.showSubscribers = function () {
    this.subscribers.forEach((subscriber) => {
        this.showElement(subscriber.element);
    });
};

Webinar.prototype.isScreenShareStream = function (stream) {
    return this.typeScreenShare === stream.videoType;
};

module.exports = Webinar;

