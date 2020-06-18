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

    this.isMobile = 768 > (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth);

    this.notCompatibleBrowserMessage = element.getAttribute(
        'data-not-compatible-browser-message'
    );

    this.installScreenSharingExtensionMessage = element.getAttribute(
        'data-install-screensharing-extension-message'
    );

    this.accessDeniedErrorMessage = element.getAttribute(
        'data-user-denied-media-access'
    );

    this.sideContainer = element.querySelector('.side-container');

    this.chatContainer = element.querySelector('[data-chat-container]');
    this.questionsContainer = element.querySelector('[data-questions-container]');
    this.questionsList = this.questionsContainer.querySelector('.questions-list');
    this.questionsForm = element.querySelector('[data-questions-form]');
    this.questionsFormContent = this.questionsForm.querySelector('input[name="content"]');
    this.questionsFormAction = this.questionsForm.getAttribute('action');
    this.questionsFormSubmit = this.questionsForm.querySelector('button[type="submit"]');

    this.chatInstance = null;
    this.chatButton = element.querySelector('[data-chat-button]');
    if (this.chatButton) {
        this.chatButton.addEventListener('click', this.showChat.bind(this));
    }
    this.questionsButton = element.querySelector('[data-questions-button]');
    this.questionsButton.addEventListener('click', this.showQuestions.bind(this));
    this.questionsForm.addEventListener('submit', this.submitQuestion.bind(this));

    this.toggleSideBarElement = element.querySelector('#toggle-sidebar');
    this.toggleSideBarElement.addEventListener('click', this.toggleSideBar.bind(this));

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
    } else {
        this.subscribersNameMapping = {};
    }

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
    this.mediaShareScreenShareStatusMessage = element.getAttribute('data-media-screenShareStatus-message');

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
    }.bind(this));

    this.session.on('signal:QuestionsUpdate', function (event) {
        this.initQuestions();
    }.bind(this));

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
        this.showElement(this.toggleSideBarElement);
        this.showElement(this.toggleAudioElement);
        this.showElement(this.toggleVideoElement);
        this.showElement(this.mediaStartSharingButton);
        this.showElement(this.timerContainer);
        this.showElement(this.viewersContainer);
        this.createFullscreenButton();
        this.initShareMedia();

        if (!this.isMobile) {
            this.toggleSideBar();
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
        publisherName.textContent = this.subscribersNameMapping[this.currentUserId];

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
        this.handleStopSharing();
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

            this.showElement(this.endSharingButton);
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

        this.publisherScreen = new Publisher(null);
        const publisherScreen = this.publisherScreen.create({
            videoSource: this.typeScreenShare,
            publishAudio: true,
            name: this.currentUserId,
            insertDefaultUI: false,
        });

        const endSharingButton = document.createElement('button');
        endSharingButton.textContent = this.endSharingButton.textContent;
        endSharingButton.classList.add('btn');
        endSharingButton.classList.add('btn-primary');
        endSharingButton.addEventListener('click', this.handleStopSharing.bind(this));

        this.screenElement = document.createElement('div');
        this.screenElement.classList.add('screen-share-in-progress');
        const screenCenteredElement = document.createElement('div');
        screenCenteredElement.textContent = this.mediaShareScreenShareStatusMessage;
        screenCenteredElement.appendChild(document.createElement('hr'));
        screenCenteredElement.appendChild(endSharingButton);

        this.screenElement.appendChild(screenCenteredElement);
        this.layoutContainer.appendChild(this.screenElement);

        this.session.publish(publisherScreen, this.handlePublishMediaSharing.bind(this));

        this.minimizeAllSubscribers();
        this.maximize(this.screenElement);
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
};

/**
 * Handle stop screen sharing
 */
Webinar.prototype.handleStopSharing = function () {
    if (this.publisherScreen) {
        this.publisherScreen.destroy();
        this.screenElement.remove();
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

Webinar.prototype.createFullscreenButton = function () {
    if (this.isMobile) {
        return;
    }

    const fullscreenButton = document.createElement('button');
    const icon = document.createElement('i');
    const startFullScreenClass = 'glyphicon-fullscreen';
    const endFullScreenClass = 'icon-Reduire_3';
    icon.classList.add('glyphicon');
    icon.classList.add(startFullScreenClass);

    fullscreenButton.classList.add('btn');
    fullscreenButton.classList.add('btn-gray');
    fullscreenButton.classList.add('start-fullscreen-button');
    fullscreenButton.classList.add('OT_ignore');
    fullscreenButton.appendChild(icon);

    this.layoutContainer.appendChild(fullscreenButton);

    fullscreenButton.addEventListener('click', () => {
        if (document.fullscreenElement) {
            icon.classList.remove(endFullScreenClass);
            icon.classList.add('glyphicon');
            icon.classList.add(startFullScreenClass);
            document.exitFullscreen();
            return;
        }

        icon.classList.remove('glyphicon');
        icon.classList.remove(startFullScreenClass);
        icon.classList.add(endFullScreenClass);

        const element = this.layoutContainer;
        const rfs = element.requestFullscreen
          || element.webkitRequestFullScreen
          || element.mozRequestFullScreen
          || element.msRequestFullscreen
        ;
        rfs.call(element);
        this.layout();
    });
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

Webinar.prototype.showChat = function (event) {
    event.preventDefault();

    this.questionsButton.classList.remove('btn-primary');
    this.questionsButton.classList.add('btn-gray');
    this.chatButton.classList.remove('btn-gray');
    this.chatButton.classList.add('btn-primary');
    this.hideElement(this.questionsContainer);
    this.showElement(this.chatContainer);
};

Webinar.prototype.showQuestions = function (event) {
    event.preventDefault();

    this.chatButton.classList.remove('btn-primary');
    this.chatButton.classList.add('btn-gray');
    this.questionsButton.classList.remove('btn-gray');
    this.questionsButton.classList.add('btn-primary');

    this.hideElement(this.chatContainer);
    this.showElement(this.questionsContainer);

    this.initQuestions();
};

Webinar.prototype.initQuestions = function () {
    const href = this.questionsContainer.getAttribute('data-href');

    const $questionsList = $(this.questionsList);

    $.get(href, function (response) {
        $questionsList.empty();
        response.forEach((item) => {
            const rowEl = document.createElement('div');
            rowEl.classList.add('question-row');

            const contentEl = rowEl.appendChild(document.createElement('div'));
            contentEl.classList.add('question-content');
            const questionCreatedAt = document.createElement('small');
            questionCreatedAt.classList.add('pull-right');
            questionCreatedAt.textContent = item.createdAt;
            contentEl.appendChild(questionCreatedAt);
            contentEl.appendChild(document.createTextNode(item.questionContent));

            const authorEl = rowEl.appendChild(document.createElement('div'));
            authorEl.classList.add('question-author');
            const authorNameEl = authorEl.appendChild(document.createElement('span'));
            authorNameEl.classList.add('question-author-name');
            const authorNameTextEl = authorNameEl.appendChild(document.createElement('span'));
            authorNameTextEl.textContent = item.firstName + ' ' + item.lastName;

            if (item.sheetTitle) {
                const authorTitleEl = authorNameEl.appendChild(document.createElement('small'));
                authorTitleEl.textContent = [item.position, item.sheetTitle].filter((item) => !!item).join(', ');
                authorTitleEl.classList.add('question-author-title');
            }

            const avatarEl = authorEl.appendChild(document.createElement('span'));
            if (item.avatar) {
                avatarEl.classList.add('question-author-avatar');
                const imgEl = avatarEl.appendChild(document.createElement('img'));
                imgEl.setAttribute('src', item.avatar);
            }

            $questionsList[0].appendChild(rowEl);
        });
    }.bind(this))
    .fail(function () {
        console.error('Failed to load webinar questions');
    }.bind(this));
}

Webinar.prototype.submitQuestion = function (event) {
    event.preventDefault();
    const questionContent = this.questionsFormContent.value;

    if ('' === questionContent) {
        window.setTimeout(() => this.questionsFormSubmit.disabled = false, 100);
        return;
    }

    this.questionsFormContent.value = '';

    $.post(this.questionsFormAction, JSON.stringify({questionContent: questionContent}), (response) => {
        this.questionsFormSubmit.disabled = false;

        if (response.status === 'ok') {
            this.session.signal({
                    type: 'QuestionsUpdate'
                },
                (error) => {
                    if (error) {
                        console.error('QuestionsUpdate signal error', error);
                    }
                }
            );

            this.questionsList.scrollTop = 0;

            return;
        }

        this.questionsFormContent.value = questionContent;
        this.showError('Question creation failed');
    })
    .fail(() => {
        this.questionsFormSubmit.disabled = false;
        this.questionsFormContent.value = questionContent;
        this.showError('Question creation failed');
    });
}

Webinar.prototype.toggleSideBar = function () {
    if (this.sideContainer.classList.contains('hide')) {
        this.toggleButton(this.toggleSideBarElement, true);
        this.showElement(this.sideContainer);
        this.initChat();
        this.chatInstance.showTextChat();
        this.chatInstance.deliverUnsentMessages();
        this.element.classList.add('chat-opened');
        this.layout();

        return;
    }

    this.element.classList.remove('chat-opened');
    this.hideElement(this.sideContainer);
    this.chatInstance.hideTextChat();
    this.toggleButton(this.toggleSideBarElement, false);
    this.initQuestions();
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

