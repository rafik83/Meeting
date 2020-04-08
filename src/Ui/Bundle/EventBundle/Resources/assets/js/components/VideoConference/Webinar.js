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
    if (this.toggleChatElement) {
        this.toggleChatElement.addEventListener('click', this.toggleChat.bind(this));
    }

    this.layoutContainer = element.querySelector('.layout-container');
    this.layout = initLayoutContainer(this.layoutContainer).layout;

    this.hasScreenSharing = false;

    const endWebinarButton = element.querySelector('.end-webinar');

    if (endWebinarButton) {
        endWebinarButton.addEventListener('click', this.disconnect.bind(this));
    }

    if (this.isSpeaker) {
        this.viewersCount = 0;
        this.viewersContainer = element.querySelector('.viewers');

        this.timerContainer = element.querySelector('.timer');
        this.countDownContainer = element.querySelector('.timer span.countdown');

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

    this.streams = [];

    let resizeTimeout;
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

    this.session = TokboxInstance.initSession(this.apiKey, this.sessionId, {connectionEventsSuppressed: !this.isSpeaker});

    this.session.on('streamCreated', function (event) {
        if (this.helperContainer) {
            this.helperContainer.classList.add('hide');
        }

        const subscriberManager = new Subscriber(this.session, this.layoutContainer);
        const subscriber = subscriberManager.subscribe(event);

        const fullscreenButton = this.createFullscreenButton();
        subscriber.element.appendChild(fullscreenButton);

        if ('screen' === event.stream.videoType) {
            this.hasScreenSharing = true;
            this.streams.forEach((stream) => {
                stream.element.style.display = 'none';
            });
        } else {
            this.streams.push(subscriber);
        }

        if (this.hasScreenSharing && 'screen' !== subscriber.stream.videoType) {
            subscriber.element.style.display = 'none';
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
            this.streams = this.streams.filter(stream => stream !== subscriber);

            subscriber.element.classList.remove('ot-layout');

            setTimeout(() => {
                subscriber.destroy();
                this.layout();
            }, 200);
        });

        if ('screen' === event.stream.videoType) {
            this.hasScreenSharing = false;

            this.streams.forEach((stream) => {
                stream.element.style.display = 'block';
            });
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
        alert('Un screenshare est en cours.');
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

        const publisherStream = this.publisher.publisher;
        publisherStream.publishVideo(false);
        publisherStream.element.style.display = 'none';
        this.toggleVideoElement.style.display = 'none';

        this.publisherScreen = new Publisher(this.layoutContainer);
        const publisherScreen = this.publisherScreen.create({
            videoSource: 'screen',
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

    this.startScreenSharingButton.classList.add('hide');
    this.endScreenSharingButton.classList.remove('hide');
};

/**
 * Handle stop screen sharing
 */
Webinar.prototype.handleStopScreensharing = function () {
    if (this.publisherScreen) {
        this.publisherScreen.destroy();
    }

    const publisherStream = this.publisher.publisher;
    publisherStream.publishVideo(this.enableVideo);
    publisherStream.element.style.display = 'block';

    this.startScreenSharingButton.classList.remove('hide');
    this.endScreenSharingButton.classList.add('hide');
    this.toggleVideoElement.style.display = 'inline-block';
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
        this.element.classList.add('chat-opened');
        this.layout();

        return;
    }

    this.element.classList.remove('chat-opened');
    this.chatContainer.classList.add('hide');
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

module.exports = Webinar;

