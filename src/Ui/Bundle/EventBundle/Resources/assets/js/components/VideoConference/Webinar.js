'use strict';

var TokboxInstance = require('./TokboxInstance').TokboxInstance;
var CHROME_EXTENSION_URL = require('./TokboxInstance').CHROME_EXTENSION_URL;
var initLayoutContainer = require('opentok-layout-js');
var openTokTextChat = require('opentok-text-chat');
var Publisher = require('./Publisher');
var Subscriber = require('./Subscriber');
var Counter = require('./Counter');
var Settings = require('./Settings');
var $ = require('jquery');
require('bootstrap/js/tooltip');
require('bootstrap/js/popover'); // popover require tooltip

function Webinar(element, isSpeaker) {
    this.element = element;
    this.isSpeaker = isSpeaker;
    this.typeScreenShare = 'screen';
    this.typeCustomShare = 'custom';

    this.token = element.getAttribute('data-token');
    this.sessionId = element.getAttribute('data-session-id');
    this.apiKey = element.getAttribute('data-api-key');

    this.timeRemaining = element.getAttribute('data-time-remaining');
    this.warningRemainingTime = element.getAttribute('data-warning-time-remaining');

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

    this.layoutContainer = element.querySelector('.layout-container');
    this.layout = initLayoutContainer(this.layoutContainer).layout;

    this.shareVideoElement = null;
    this.hasMediaSharing = false;

    const endWebinarButton = element.querySelector('.end-webinar');

    if (endWebinarButton) {
        endWebinarButton.addEventListener('click', this.disconnect.bind(this));
    }
    this.timerContainer = element.querySelector('.timer');
    this.countDownContainer = element.querySelector('.timer span.countdown');
    this.viewersCount = 0;
    this.viewersContainer = element.querySelector('.viewers-container');
    this.viewersTextContainer = element.querySelector('.viewers');

    this.subscribers = [];
    this.subscribersNameMapping = element.getAttribute('data-subscriber-mapping');
    this.currentUserId = element.getAttribute('data-current-user-id');

    if (this.subscribersNameMapping) {
        this.subscribersNameMapping = JSON.parse(this.subscribersNameMapping);
    }

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

    if (!this.isSpeaker) {
        this.joinButton.addEventListener('click', this.join.bind(this));

        return;
    }

    this.thereIsAlreadyAScreenShareInProgressMessage = element.getAttribute('data-screen-share-already-in-progress-message');

    this.mediaStartSharingButtonSelector = '#media-start-sharing';
    this.sharePopover = $(this.mediaStartSharingButtonSelector, this.element);
    this.mediaStartSharingButton = this.element.querySelector(this.mediaStartSharingButtonSelector, this.element);

    this.mediaShareUrlVideoMessage = element.getAttribute('data-media-share-url-video-message');
    this.mediaShareUrlVideoSecurityErrorMessage = element.getAttribute('data-media-share-url-video-security-error-message');
    this.mediaShareUrlVideoLoadingErrorMessage = element.getAttribute('data-media-share-url-video-loading-error-message');
    this.mediaShareButtonScreenShareMessage = element.getAttribute('data-media-share-button-screenshare-message');
    this.mediaShareButtonVideoShareMessage = element.getAttribute('data-media-share-button-videoshare-message');

    this.endSharingButton = element.querySelector('#media-stop-sharing');
    this.endSharingButton.addEventListener('click', this.handleStopSharing.bind(this));

    this.toggleAudioElement = element.querySelector('#toggle-audio');
    this.toggleAudioElement.addEventListener('click', this.toggleAudio.bind(this));
    this.enableAudio = true;

    this.toggleVideoElement = element.querySelector('#toggle-video');
    this.toggleVideoElement.addEventListener('click', this.toggleVideo.bind(this));
    this.enableVideo = true;

    this.settingsContainer = this.element.querySelector('[data-settings-container]');

    this.publisher = new Publisher(this.layoutContainer);

    this.settings = new Settings(
      this.settingsContainer.querySelector('#video-settings-section'),
      this.join.bind(this)
    );
    this.settings.init();
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

    this.session = TokboxInstance.initSession(this.apiKey, this.sessionId);

    this.session.on('streamCreated', function (event) {
        this.hideElement(this.helperContainer);

        const subscriberManager = new Subscriber(
            this.session,
            this.layoutContainer,
            this.subscribersNameMapping
        );
        const subscriber = subscriberManager.subscribe(event);

        if (this.isScreenShareStream(event.stream)) {
            this.hasMediaSharing = true;
            this.minimizeAllSubscribers();
            this.maximize(subscriber.element);
        } else {
            this.subscribers.push(subscriber);
        }

        if (this.hasMediaSharing && !this.isScreenShareStream(subscriber.stream)) {
            this.minimize(subscriber.element)
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
            this.hasMediaSharing = false;
            this.maximizeAllSubscribers();
        }
    }.bind(this));

    this.session.on('sessionDisconnected', function () {
        this.layout();
    });

    this.connect();
};

Webinar.prototype.updateViewers = function () {
    this.viewersTextContainer.textContent = this.viewersCount;
};

/**
 * Connect to the session, create and publish your stream
 */
Webinar.prototype.connect = function () {
    this.session.connect(this.token, function (error) {
        this.showElement(this.toggleChatElement);
        this.showElement(this.toggleAudioElement);
        this.showElement(this.toggleVideoElement);
        this.showElement(this.mediaStartSharingButton);
        this.showElement(this.timerContainer);
        this.showElement(this.viewersContainer);
        this.initShareMedia();

        if (!error) {
            if (this.isSpeaker) {
                this.publishStream();
            }

            return;
        }

        console.error(error);
    }.bind(this));
};

Webinar.prototype.initShareMedia = function () {
    if (!this.isSpeaker) {
        return;
    }

    this.mediaStartSharingButton.addEventListener('click', () => this.sharePopover.popover('toggle'));
    this.showElement(this.mediaStartSharingButton);

    this.sharePopover.popover({
        animation: false,
        html : true,
        placement: 'top',
        trigger: 'manual',
        content: () => {
            return `<div class="text-center">
                <button data-share-screen class="btn">${this.mediaShareButtonScreenShareMessage}</button><br />
                <button data-share-video class="btn">${this.mediaShareButtonVideoShareMessage}</button>
              </div>`;
        }
    });

    this.sharePopover.on('shown.bs.popover', () => {
        const shareScreenButton = this.element.querySelector('[data-share-screen]');
        shareScreenButton.addEventListener('click', this.screenshare.bind(this));

        const shareVideoButton = this.element.querySelector('[data-share-video]');
        shareVideoButton.addEventListener('click', this.shareVideo.bind(this));
    });
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
    const publisher = this.publisher.create({
        audioSource: this.settings.getAudioSource(),
        videoSource: this.settings.getVideoSource(),
        name: this.currentUserId
    });

    publisher.on('videoElementCreated', this.onVideoElementCreated.bind(this));

    this.session.publish(publisher, this.handlePublish.bind(this));
    publisher.publishVideo(this.enableVideo);
    publisher.publishAudio(this.enableAudio);

    this.layout();
};

Webinar.prototype.onVideoElementCreated = function (event) {
    const publisherElement = event.target.element;

    // Show user name on video element.
    if (this.subscribersNameMapping.hasOwnProperty(this.currentUserId)) {
        let publisherName = document.createElement('span');
        publisherName.classList.add('visio-user-name');
        publisherName.textContent = this.subscribersNameMapping[this.currentUserId].name;

        publisherElement.appendChild(publisherName);
    }
};

/**
 * Disconnect from the session
 */
Webinar.prototype.disconnect = function () {
    if (this.session) {
        this.session.disconnect();
        this.session.off();
    }

    this.session = null;

    if (window.opener) {
        window.opener.location.reload(true);
        window.close();

        return;
    }

    window.history.go(-1);
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

Webinar.prototype.askUrlVideo = function (previousUrl) {
    const url = window.prompt(this.mediaShareUrlVideoMessage, previousUrl);

    if (!url) {
        return;
    }

    if ('https://' !== url.substr(0,8)) {
        alert(this.mediaShareUrlVideoSecurityErrorMessage);

        return this.askUrlVideo(url);
    }

    return url;
};

Webinar.prototype.shareVideo = function () {
    this.sharePopover.popover('hide');

    if (this.hasMediaSharing) {
        alert(this.thereIsAlreadyAScreenShareInProgressMessage);
        return;
    }

    const videoElement = document.createElement('video');
    videoElement.setAttribute('crossOrigin', 'anonymous');
    videoElement.setAttribute('controls', '');
    videoElement.setAttribute('preload', 'auto');
    videoElement.setAttribute('controlslist', 'disablePictureInPicture nodownload nofullscreen noremoteplayback');
    videoElement.setAttribute('disablePictureInPicture', '');
    this.layoutContainer.appendChild(videoElement);
    this.shareVideoElement = videoElement;

    if (!videoElement.captureStream) {
        alert(this.notCompatibleBrowserMessage);
        this.handleStopSharing();
        return;
    }

    const url = this.askUrlVideo();

    if (!url) {
        return;
    }

    this.hideElement(this.mediaStartSharingButton);

    videoElement.addEventListener('error', () => {
        this.handleStopSharing();
        alert(this.mediaShareUrlVideoLoadingErrorMessage);
    }, true);

    videoElement.src = url;
    videoElement.play();

    const stream = videoElement.mozCaptureStream ? videoElement.mozCaptureStream() : videoElement.captureStream();

    let publisher;

    const publishVideo = () => {
        const videoTracks = stream.getVideoTracks();
        const audioTracks = stream.getAudioTracks();

        if (!publisher && videoTracks.length > 0 && audioTracks.length > 0) {
            stream.removeEventListener('addtrack', publishVideo);

            this.publisherScreen = new Publisher(null);
            publisher = this.publisherScreen.create({
                videoSource: videoTracks[0],
                audioSource: audioTracks[0],
                fitMode: 'contain',
                insertDefaultUI: false,
            });

            publisher.on('destroyed', () => {
                this.handleStopSharing();
            });

            this.session.publish(publisher, this.handlePublishMediaSharing.bind(this));
            this.layout();
        }
    };

    stream.addEventListener('addtrack', publishVideo);
    publishVideo();

    this.minimizeAllSubscribers();
    this.maximize(videoElement);
    this.layout();
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

    if (this.hasMediaSharing) {
        alert(this.thereIsAlreadyAScreenShareInProgressMessage);
        return;
    }

    this.hideElement(this.mediaStartSharingButton);
    this.sharePopover.popover('hide');

    TokboxInstance.checkScreenSharingCapability(function (response) {
        if (!response.supported || response.extensionRegistered === false) {
            alert(this.notCompatibleBrowserMessage);
            return;
        }

        if (response.extensionRegistered && response.extensionInstalled === false) {
            this.installChromeExtension();
            return;
        }

        this.publisherScreen = new Publisher(this.layoutContainer);
        const publisherScreen = this.publisherScreen.create({
            videoSource: this.typeScreenShare,
            publishAudio: true,
            name: this.currentUserId
        });

        publisherScreen.on('videoElementCreated', this.onVideoElementCreated.bind(this));
        this.session.publish(publisherScreen, this.handlePublishMediaSharing.bind(this));

        this.minimizeAllSubscribers();
        this.maximize(publisherScreen.element);
        this.layout();

        publisherScreen.on('mediaStopped', this.handleStopSharing.bind(this));
    }.bind(this));
};

/**
 * Callback after screensharing started
 *
 * @param {Object} error
 */
Webinar.prototype.handlePublishMediaSharing = function (error) {
    if (error) {
        console.error(error);
        this.showError(error);
        this.handleStopSharing();

        return;
    }

    this.hasMediaSharing = true;
    this.layout();

    this.showElement(this.endSharingButton);
};

/**
 * Handle stop screen sharing
 */
Webinar.prototype.handleStopSharing = function () {
    if (this.publisherScreen) {
        this.publisherScreen.destroy();
    }

    if (this.shareVideoElement) {
        this.shareVideoElement.remove();
        this.shareVideoElement = null;
    }

    this.hasMediaSharing = false;

    this.maximizeAllSubscribers();
    this.layout();

    this.showElement(this.mediaStartSharingButton);
    this.hideElement(this.endSharingButton);
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
    const publisher = this.publisher.publisher;

    if (!publisher || !publisher.stream) {
        return;
    }

    const enableAudio = !publisher.stream.hasAudio;
    publisher.publishAudio(enableAudio);
    this.enableAudio = enableAudio;
    this.toggleButton(this.toggleAudioElement, enableAudio);
};

/**
 * Toggle video stream
 */
Webinar.prototype.toggleVideo = function () {
    const publisher = this.publisher.publisher;

    if (!publisher || !publisher.stream) {
        return;
    }

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

    new Counter(parseInt(this.timeRemaining, 10), parseInt(this.warningRemainingTime, 10), this.countDownContainer, this.timerContainer);
};

Webinar.prototype.isScreenShareStream = function (stream) {
    return [this.typeScreenShare, this.typeCustomShare].includes(stream.videoType);
};

Webinar.prototype.maximize = function(element) {
    element.classList.add('OT_big');
};

Webinar.prototype.minimize = function(element) {
    element.classList.remove('OT_big');
};

Webinar.prototype.minimizeAllSubscribers = function() {
    this.subscribers.forEach((subscriber) => {
        this.minimize(subscriber.element);
    });
};

Webinar.prototype.maximizeAllSubscribers = function() {
    this.subscribers.forEach((subscriber) => {
        this.maximize(subscriber.element);
    });
};

module.exports = Webinar;

