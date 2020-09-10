'use strict';

var TokboxInstance = require('./TokboxInstance').TokboxInstance;
var CHROME_EXTENSION_URL = require('./TokboxInstance').CHROME_EXTENSION_URL;
var initLayoutContainer = require('opentok-layout-js');
var Publisher = require('./Publisher');
var Subscriber = require('./Subscriber');
var Counter = require('./Counter');
var Settings = require('./Settings');
var $ = require('jquery');
require('bootstrap/js/tooltip');
require('bootstrap/js/popover'); // popover require tooltip
const esPolyfill = require('event-source-polyfill');

function Webinar(element, isSpeaker) {
    this.element = element;
    this.isSpeaker = isSpeaker;
    this.invisibleMode = false;
    this.typeScreenShare = 'screen';
    this.typeCustomShare = 'custom';

    this.startFullScreenClass = 'glyphicon-fullscreen';
    this.endFullScreenClass = 'icon-Reduire_3';

    this.sidebarAllowed = element.getAttribute('data-sidebar-allowed') == 1;

    if(this.sidebarAllowed) {
        this.shiftWithSidebar = 'shift-with-sidebar';
    } else {
        this.shiftWithSidebar = '';
    }

    this.eventId = element.getAttribute('data-event-id');
    this.happeningId = element.getAttribute('data-happening-id');

    this.token = element.getAttribute('data-token');
    this.sessionId = element.getAttribute('data-session-id');
    this.apiKey = element.getAttribute('data-api-key');
    this.notificationProviderUrl = element.getAttribute('data-notifications-provider-url');
    this.notificationSubscriberKey = element.getAttribute('data-notifications-subscriber-key');

    this.timeRemainingBeforeStart = element.getAttribute('data-time-remaining-before-start');
    this.timeRemainingBeforeStartMessage = element.getAttribute('data-time-remaining-before-start-message');
    this.timeRemaining = element.getAttribute('data-time-remaining');
    this.warningRemainingTime = element.getAttribute('data-warning-time-remaining');

    if (this.isSpeaker && this.timeRemainingBeforeStart > 0) {
        const startTime = new Date(new Date().getTime() + this.timeRemainingBeforeStart * 1000);

        const timerInterval = setInterval(() => {
            const remainingTime = Math.round((startTime.getTime() - new Date().getTime()) / 1000);

            if (remainingTime <= 0) {
                clearInterval(timerInterval);
                alert(this.timeRemainingBeforeStartMessage);
            }
        }, 500);
    }

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

    if (this.sidebarAllowed) {
        this.chatContainer = element.querySelector('[data-chat-container]');
        this.addChatForm = element.querySelector('[data-chat-form]');
        this.addChatFormContent = this.addChatForm.querySelector('input[name="content"]');
        this.addChatFormAction = this.addChatForm.getAttribute('action');
        this.addChatFormSubmit = this.addChatForm.querySelector('button[type="submit"]');
        this.addChatFormList = this.chatContainer.querySelector('.chat-list');
        this.questionsContainer = element.querySelector('[data-questions-container]');
        this.questionsList = this.questionsContainer.querySelector('.questions-list');
        this.questionsForm = element.querySelector('[data-questions-form]');
        this.questionsFormContent = this.questionsForm.querySelector('input[name="content"]');
        this.questionsFormAction = this.questionsForm.getAttribute('action');
        this.questionsFormSubmit = this.questionsForm.querySelector('button[type="submit"]');

        this.chatInstance = null;
        this.chatLoaded = false;
        this.chatButton = element.querySelector('[data-chat-button]');

        this.chatButton.addEventListener('click', this.showChat.bind(this));
        this.addChatForm.addEventListener('submit', this.submitChat.bind(this));
        this.questionVoteMessage = element.getAttribute('data-question-vote-message');
        this.questionUnvoteMessage = element.getAttribute('data-question-unvote-message');
        this.questionVoteDisabledMessage = element.getAttribute('data-question-vote-disabled-message');

        this.chatVoteMessage = {
            'like' : element.getAttribute('data-chat-vote-like'),
            'acclaim' : element.getAttribute('data-chat-vote-acclaim'),
            'heart' : element.getAttribute('data-chat-vote-heart'),
            'instructive' : element.getAttribute('data-chat-vote-instructive'),
            'happy' : element.getAttribute('data-chat-vote-happy')
        };

        this.chatUnVoteMessage = element.getAttribute('data-chat-unvote-message');
        this.chatVoteDisabledMessage = element.getAttribute('data-chat-vote-disabled-message');

        this.questionsButton = element.querySelector('[data-questions-button]');
        this.questionsButton.addEventListener('click', this.showQuestions.bind(this));
        this.questionsForm.addEventListener('submit', this.submitQuestion.bind(this));
        this.questionListeners = [];
        this.chatListeners = [];
    }

    this.webinarWaitingMessage = element.querySelector('[data-webinar-waiting-message]');

    this.joinButton = element.querySelector('[data-webinar-join-button]');

    this.layoutContainer = element.querySelector('.layout-container');
    this.layout = initLayoutContainer(this.layoutContainer).layout;

    this.shareVideoElement = null;
    this.hasMediaSharing = false;

    this.liveUrl = element.getAttribute('data-live-url');

    const endWebinarButton = element.querySelector('.end-webinar');

    if (endWebinarButton) {
        endWebinarButton.addEventListener('click', this.disconnect.bind(this));
    }

    this.timerContainer = element.querySelector('.timer-container');
    this.timerElement = this.timerContainer.querySelector('.timer');
    this.countDownContainer = this.timerContainer.querySelector('.timer span.countdown');
    this.viewersCount = 0;
    this.viewersContainer = element.querySelector('.viewers-container');
    this.viewersTextContainer = element.querySelector('.viewers');

    this.streamEndpoint = element.getAttribute('data-webinar-stream-endpoint');

    this.isWebinarRecorded = element.getAttribute('data-webinar-recorded');
    this.canRecordWebinar = element.getAttribute('data-webinar-can-record');
    this.recordEndpoint = element.getAttribute('data-webinar-record-endpoint');
    this.stopRecordEndpoint = element.getAttribute('data-webinar-stop-record-endpoint');
    this.toggleRecordingButton = element.querySelector('#toggle-recording');
    this.isRecording = false;

    const recordStatus = element.getAttribute('data-webinar-is-recording');
    if (this.isWebinarRecorded && this.canRecordWebinar && recordStatus) {
        this.isRecording = recordStatus === 'true';
        this.toggleRecording(this.isRecording);
    }

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

    document.addEventListener('webkitfullscreenchange', this.changeFullscreenHandler.bind(this), false);
    document.addEventListener('mozfullscreenchange', this.changeFullscreenHandler.bind(this), false);
    document.addEventListener('fullscreenchange', this.changeFullscreenHandler.bind(this), false);
    document.addEventListener('MSFullscreenChange', this.changeFullscreenHandler.bind(this), false);

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
    this.invisibleModeQuitConfirmationMessage = element.getAttribute('data-invisibleMode-quitConfirmation-message');
    this.invisibleModeEnableConfirmationMessage = element.getAttribute('data-invisibleMode-enableConfirmation-message');

    this.endSharingButton = element.querySelector('#media-stop-sharing');
    this.endSharingButton.addEventListener('click', this.handleStopSharing.bind(this));

    this.toggleAudioElement = element.querySelector('#toggle-audio');
    this.toggleAudioElement.addEventListener('click', this.toggleAudio.bind(this));
    this.enableAudio = true;

    this.toggleVideoElement = element.querySelector('#toggle-video');
    this.toggleVideoElement.addEventListener('click', this.toggleVideo.bind(this));
    this.enableVideo = true;

    this.invisibleModeButton = element.querySelector('#invisible-mode-button');
    this.invisibleModeButton.addEventListener('click', this.handleInvisibleMode.bind(this));

    this.settingsContainer = this.element.querySelector('[data-settings-container]');

    this.publisher = new Publisher(this.layoutContainer);

    this.settings = new Settings(
      this.settingsContainer.querySelector('#video-settings-section'),
      this.onSettingsValidate.bind(this),
      true
    );
    this.settings.init();
}

Webinar.prototype.onSettingsValidate = function (invisibleMode) {
    this.invisibleMode = invisibleMode;
    this.join();
};

Webinar.prototype.join = function () {
    this.hideElement(this.joinButton);

    if (this.liveUrl) {
        this.hideElement(this.helperContainer);
        this.liveVideo();
    } else {
        this.showElement(this.webinarWaitingMessage);
    }

    this.init();

    if (this.invisibleMode) {
        this.hideElement(this.toggleVideoElement);
        this.hideElement(this.toggleAudioElement);
        this.toggleButton(this.invisibleModeButton, true);
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

        this.autoMaximize(subscriber);

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
        this.layout();
    }.bind(this));

    this.session.on('sessionDisconnected', function () {
        this.layout();
    }.bind(this));

    this.connect();

    this.prepareRecordButtons();
};

Webinar.prototype.updateViewers = function () {
    this.viewersTextContainer.textContent = this.viewersCount;
};

/**
 * Connect to the session, create and publish your stream
 */
Webinar.prototype.connect = function () {
    this.session.connect(this.token, function (error) {
        this.showElement(this.invisibleModeButton);

        if (!this.invisibleMode) {
            this.showElement(this.toggleAudioElement);
            this.showElement(this.toggleVideoElement);
        }

        this.showElement(this.mediaStartSharingButton);
        this.showElement(this.timerContainer);
        this.showElement(this.viewersContainer);
        this.initShareMedia();

        if (!this.isMobile) {
            this.toggleSideBar();
        }

        this.createToggleSidebarButton();
        this.createFullscreenButton();

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

Webinar.prototype.prepareRecordButtons = function() {
    if (!this.isWebinarRecorded || !this.canRecordWebinar) {
        return;
    }

    this.toggleRecordingButton.classList.remove('hide');
    this.toggleRecordingButton.addEventListener('click', () => {
        if (!this.isRecording) {
            // call endpoint record
            this.toggleRecording(true);

            $.post(this.recordEndpoint, JSON.stringify({}), (response) => {
                this.session.signal({
                        type: 'startRecording'
                    },
                    (error) => {
                        if (error) {
                            console.error('startRecording signal error', error);
                        }
                    }
                );
            })
            .fail((error) => {
                this.toggleRecording(false);
                this.showError({name: `${error.status}: ${error.statusText}`, message:'Could not start recording'});
                console.error(error.status, error.statusText, this.recordEndpoint);
            });
        } else {
            // call endpoint stop record
            this.toggleRecording(false);

            $.post(this.stopRecordEndpoint, JSON.stringify({}), (response) => {
                this.session.signal({
                        type: 'stopRecording'
                    },
                    (error) => {
                        if (error) {
                            console.error('stopRecording signal error', error);
                        }
                    }
                );
            })
            .fail(() => {
                this.toggleRecording(true);
                this.showError('Could not stop recording');
            });
        }
    });

    this.session.on('signal:startRecording', (event) => {
        this.toggleRecording(true);
    });

    this.session.on('signal:stopRecording', (event) => {
        this.toggleRecording(false);
    });
};

Webinar.prototype.toggleRecording = function(recording) {
    this.isRecording = recording;

    if (recording) {
        this.toggleRecordingButton.classList.add('recording');
        this.toggleRecordingButton.setAttribute(
            'title',
            this.toggleRecordingButton.getAttribute('data-button-recording-title')
        );
    } else {
        this.toggleRecordingButton.classList.remove('recording');
        this.toggleRecordingButton.setAttribute(
            'title',
            this.toggleRecordingButton.getAttribute('data-button-record-title')
        );
    }
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
    if (this.chatLoaded) {
        return;
    }

    const href = this.chatContainer.getAttribute('data-href');
    const voteChatHref = this.chatContainer.getAttribute('data-vote-chat-href');

    const $addChatFormList = $(this.addChatFormList);

    $.get(href, function (response) {
        this.removeChatListeners();
        $addChatFormList.empty();
        response.forEach((item) => {
            const rowEl = document.createElement('div');
            rowEl.id = `chat-message-${item.id}`;
            rowEl.classList.add('chat-row');

            const contentEl = rowEl.appendChild(document.createElement('div'));
            contentEl.classList.add('chat-content');

            const chatAside = document.createElement('small');
            chatAside.classList.add('pull-right', 'chat-aside');

            const emoticonBlock = document.createElement('div');

            const element = {
                '&#x1F44D;': 'like',
                '&#128079;': 'acclaim',
                '&#x2764;&#xFE0F': 'heart',
                '&#128161;': 'instructive',
                '&#128522;': 'happy'
            };

            const onLikedClicked = function (event) {
                const payload = {
                    'messageId': event.currentTarget.getAttribute('data-message-id'),
                    'messageType': event.currentTarget.getAttribute('data-message-type')
                };
                $.post(voteChatHref, JSON.stringify(payload), (response) => {
                    if (response.status !== 'ok') {
                        this.showError('Message vote failed');
                    }
                }, 'json');

                const chatMessageRow = document.getElementById(`chat-message-${payload.messageId}`);
                const voteCounts = chatMessageRow.querySelectorAll(`[data-message-type]`);

                if (event.currentTarget.classList.contains('btn-primary')) {
                    event.currentTarget.classList.remove('btn-primary', 'disabled');
                    event.currentTarget.classList.add('btn-gray');
                    const voteType = event.currentTarget.getAttribute('data-message-type');
                    event.currentTarget.title = this.chatVoteMessage[voteType];
                }  else {
                    voteCounts.forEach((voteCount) => {
                        voteCount.classList.add('btn-gray');
                        voteCount.classList.remove('btn-primary', 'disabled');
                        const voteType = voteCount.getAttribute('data-message-type');
                        voteCount.title = this.chatVoteMessage[voteType];
                    });
                    event.currentTarget.classList.add('btn-primary', 'disabled');
                    event.currentTarget.classList.remove('btn-gray');
                    event.currentTarget.title = this.chatUnVoteMessage;
                }

            }.bind(this);

            const chatCreatedAt = document.createElement('div');
            chatCreatedAt.textContent = item.formattedCreatedAt;
            chatAside.appendChild(chatCreatedAt);

            contentEl.appendChild(chatAside);
            contentEl.appendChild(document.createTextNode(item.content));

            const emoticonEl = rowEl.appendChild(document.createElement('div'));
            emoticonEl.classList.add('chat-emoticon');

            const authorEl = rowEl.appendChild(document.createElement('div'));
            authorEl.classList.add('chat-author');
            const authorNameEl = authorEl.appendChild(document.createElement('span'));
            authorNameEl.classList.add('chat-author-name');
            const authorNameTextEl = authorNameEl.appendChild(document.createElement('span'));
            authorNameTextEl.textContent = item.authorName;

            const avatarEl = authorEl.appendChild(document.createElement('span'));
            avatarEl.classList.add('chat-author-avatar');
            const imgEl = avatarEl.appendChild(document.createElement('img'));
            imgEl.setAttribute('src', item.avatar);

            for (let smileyCode in element) {
                const emoticonBtn = document.createElement('i');
                emoticonBtn.classList.add('glyphicon', 'btn', 'btn-xs');
                emoticonBtn.innerHTML = smileyCode;
                emoticonBtn.setAttribute('data-message-id', item.id);
                emoticonBtn.setAttribute('data-message-type', element[smileyCode]);

                const voteChat = document.createElement('span');
                voteChat.classList.add('chat-vote-count');

                if (item.votes[element[smileyCode]]) {
                    voteChat.textContent = item.votes[element[smileyCode]];
                }
                emoticonBtn.append(voteChat);

                if (!item.isAuthor) {
                    emoticonBtn.addEventListener('click', onLikedClicked);
                    this.chatListeners.push([emoticonBtn, onLikedClicked]);

                    if (item.selfVote === element[smileyCode]) {
                        emoticonBtn.classList.add('btn-primary', 'disabled');
                        emoticonBtn.title = this.chatUnVoteMessage;
                    } else {
                        emoticonBtn.classList.add('btn-gray');
                        emoticonBtn.title = this.chatVoteMessage[element[smileyCode]];
                    }

                } else {
                    emoticonBtn.classList.add('btn-gray', 'disabled');
                    emoticonBtn.title = this.chatVoteDisabledMessage;

                    rowEl.classList.add('chat-row-on');
                    contentEl.classList.add('chat-content-on');
                }
                emoticonBlock.appendChild(emoticonBtn);
                emoticonEl.appendChild(emoticonBlock);
            }

            if (item.sheetTitle) {
                const authorTitleEl = authorNameEl.appendChild(document.createElement('small'));
                authorTitleEl.textContent = [item.sheetTitle].filter((item) => !!item).join(', ');
                authorTitleEl.classList.add('chat-author-title');

                if (item.isAuthor) {
                    authorTitleEl.classList.add('chat-author-title-on');
                }
            }

            $addChatFormList[0].appendChild(rowEl);
        });

        this.addChatFormList.scrollTop = this.addChatFormList.scrollHeight;
        this.chatLoaded = true;

        const url = new URL(this.notificationProviderUrl);
        url.searchParams.append('topic', `https://vimeet.events/event/${this.eventId}/notifications/happening/${this.happeningId}`);

        var eventSource = new esPolyfill.EventSourcePolyfill(url, {
            headers: {
                'Authorization': `Bearer ${this.notificationSubscriberKey}`
            }
        });
        eventSource.onmessage = (event) => {
            const payload = JSON.parse(event.data);

            if (payload.action === 'add_chat_message') {
                this.chatLoaded = false;
                this.initChat();
            }

            if (payload.action === 'update_chat_message_votes') {
                const chatMessageRow = document.getElementById(`chat-message-${payload.messageId}`);
                const voteCounts = chatMessageRow.querySelectorAll(`[data-message-type]`);
                voteCounts.forEach((voteCount) => {
                    const voteType = voteCount.getAttribute('data-message-type');
                    voteCount.querySelector('.chat-vote-count').textContent = payload.votes[voteType] ? payload.votes[voteType] : '';
                });
            }
        }

    }.bind(this))
        .fail(function () {
            console.error('Failed to load webinar chat');
        }.bind(this));
};

/**
 *  Publish your camera and microphone stream
 */
Webinar.prototype.publishStream = function () {
    this.hideElement(this.helperContainer);

    if (this.invisibleMode) {
        return;
    }

    const publisher = this.publisher.create({
        audioSource: this.settings.getAudioSource(),
        videoSource: this.settings.getVideoSource(),
        name: this.currentUserId
    });

    publisher.on('videoElementCreated', this.onVideoElementCreated.bind(this));
    publisher.on('streamCreated', (event) => {
        this.handleStream(event.stream, 'video');
    });
    publisher.on('streamDestroyed', (event) => {
        this.handleStopStream(event.stream, 'video');
    });


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
            alert('There was an error: ' + (error.name ? error.name : error) + (error.message ? (', ' + error.message) : ''));
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

            publisher.on('streamCreated', (event) => {
                this.handleStream(event.stream, this.typeCustomShare);
            });

            publisher.on('streamDestroyed', (event) => {
                this.handleStopStream(event.stream, this.typeCustomShare);
            });
        }
    };

    stream.addEventListener('addtrack', publishVideo);
    publishVideo();

    this.minimizeAllSubscribers();
    this.maximize(videoElement);
    this.layout();
};

Webinar.prototype.handleStream = function(
    stream,
    type
) {
    const streamId = stream.streamId;

    $.post(this.streamEndpoint, {
        streamId: streamId,
        type: type,
        action: 'start'
    }, (response) => {})
    .fail((error) => {
        console.error(error);
    });
};

Webinar.prototype.handleStopStream = function(
    stream,
    type
) {
    const streamId = stream.streamId;

    $.post(this.streamEndpoint, {
        streamId: streamId,
        type: type,
        action: 'stop'
    }, (response) => {})
    .fail((error) => {
        console.error(error);
    });
};

Webinar.prototype.liveVideo = function () {
    const liveElement = document.createElement('iframe');
    liveElement.setAttribute('src', this.liveUrl);
    liveElement.setAttribute('frameborder', '0');
    liveElement.setAttribute('allow', 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture');
    this.layoutContainer.appendChild(liveElement);

    this.minimizeAllSubscribers();
    this.maximize(liveElement);
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

        publisherScreen.on('streamCreated', (event) => {
            this.handleStream(event.stream, this.typeScreenShare);
        });
        publisherScreen.on('streamDestroyed', (event) => {
            this.handleStopStream(event.stream, this.typeScreenShare);
        });

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
    }

    if (this.screenElement) {
        this.screenElement.remove();
        this.screenElement = null;
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

Webinar.prototype.createToggleSidebarButton = function () {
    if(!this.sidebarAllowed){
        return
    }
    const toggleSidebarButton = document.createElement('button');
    const icon = document.createElement('i');
    const showSidebarClass = 'icon-Precedent_3';
    const hideSidebarClass = 'icon-Suivant_3';
    icon.classList.add(this.isSidebarOpened() ? hideSidebarClass : showSidebarClass);

    toggleSidebarButton.classList.add('btn');
    toggleSidebarButton.classList.add('btn-gray');
    toggleSidebarButton.classList.add('toggle-sidebar-button');
    toggleSidebarButton.classList.add('OT_ignore');
    toggleSidebarButton.appendChild(icon);

    this.layoutContainer.appendChild(toggleSidebarButton);
    this.toggleSidebarButton = toggleSidebarButton;

    toggleSidebarButton.addEventListener('click', () => {
        if (this.isSidebarOpened()) {
            icon.classList.remove(hideSidebarClass);
            icon.classList.add(showSidebarClass);
        } else {
            icon.classList.remove(showSidebarClass);
            icon.classList.add(hideSidebarClass);
        }

        this.exitFullscreenHandler();
        this.toggleSideBar();
    });

    const mobileToggleSidebar = this.element.querySelector('[data-mobile-toggle-sidebar]');
    mobileToggleSidebar.addEventListener('click', this.toggleSideBar.bind(this));
};

Webinar.prototype.createFullscreenButton = function () {
    if (this.isMobile) {
        return;
    }

    const fullscreenButton = document.createElement('button');
    this.iconFullscreenButton = document.createElement('i');

    this.iconFullscreenButton.classList.add('glyphicon');
    this.iconFullscreenButton.classList.add(this.startFullScreenClass);

    fullscreenButton.classList.add('btn');
    fullscreenButton.classList.add('btn-gray');
    fullscreenButton.classList.add('start-fullscreen-button');
    fullscreenButton.classList.add('OT_ignore');

    if (this.shiftWithSidebar) {
        fullscreenButton.classList.add(this.shiftWithSidebar);
    }

    fullscreenButton.appendChild(this.iconFullscreenButton);

    this.layoutContainer.appendChild(fullscreenButton);
    this.fullscreenButton = fullscreenButton;

    fullscreenButton.addEventListener('click', () => {
        if (this.isFullScreen()) {
            this.exitFullscreenHandler();

            return;
        }

        this.iconFullscreenButton.classList.remove('glyphicon');
        this.iconFullscreenButton.classList.remove(this.startFullScreenClass);
        this.iconFullscreenButton.classList.add(this.endFullScreenClass);

        const element = this.layoutContainer;
        const rfs = element.requestFullscreen
          || element.webkitRequestFullScreen
          || element.mozRequestFullScreen
          || element.msRequestFullscreen
        ;
        rfs.call(element);

        this.toggleFullscreenAndSidebarButtons(true);
        this.layout();
    });
};

Webinar.prototype.isFullScreen = function () {
    return document.fullscreenElement;
};

Webinar.prototype.changeFullscreenHandler = function () {
    if (!this.isFullScreen()) {
        this.exitFullscreenHandler();
    }

    this.layout();
};

Webinar.prototype.toggleFullscreenAndSidebarButtons = function (isFullScreen) {
    if (isFullScreen) {
        this.hideElement(this.toggleSidebarButton);

        if (this.fullscreenButton) {
            this.fullscreenButton.classList.remove(this.shiftWithSidebar);
        }

        return;
    }

    if (this.fullscreenButton) {
        this.fullscreenButton.classList.add(this.shiftWithSidebar);
    }

    if (this.iconFullscreenButton) {
        this.iconFullscreenButton.classList.remove(this.endFullScreenClass);
        this.iconFullscreenButton.classList.add('glyphicon');
        this.iconFullscreenButton.classList.add(this.startFullScreenClass);
    }

    this.showElement(this.toggleSidebarButton);
};

Webinar.prototype.exitFullscreenHandler = function () {
    if (this.isFullScreen()) {
        document.exitFullscreen();
    }

    this.toggleFullscreenAndSidebarButtons(false);
    this.layout();
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
    this.initChat();
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
    const voteHref = this.questionsContainer.getAttribute('data-vote-href');

    const $questionsList = $(this.questionsList);

    $.get(href, function (response) {
        // make shure there no listeners leak
        this.removeQuestionListeners();
        $questionsList.empty();

        response.forEach((item) => {
            const rowEl = document.createElement('div');
            rowEl.classList.add('question-row');

            const contentEl = rowEl.appendChild(document.createElement('div'));
            contentEl.classList.add('question-content');

            const questionAside = document.createElement('small');
            questionAside.classList.add('pull-right', 'question-aside');

            const likeBlock = document.createElement('div');
            const voteCount = document.createElement('span');

            if (+item.voteCount) {
                voteCount.textContent = item.voteCount;
                voteCount.classList.add('question-vote-count')
            }

            likeBlock.append(voteCount);

            const likeBtn = document.createElement('i');
            likeBtn.classList.add('glyphicon', 'glyphicon-thumbs-up', 'btn', 'btn-xs');
            likeBtn.setAttribute('data-question-id', item.questionId);

            const onLikedClicked = function (event) {
                const payload = {'questionId': event.currentTarget.getAttribute('data-question-id')};
                $.post(voteHref, JSON.stringify(payload), (response) => {
                    if (response.status !== 'ok') {
                        this.showError('Question vote failed');
                    }
                }, 'json');
                event.currentTarget.classList.add('disabled');

                // remove all listeners, they'll be added again on questions update
                this.removeQuestionListeners();
            }.bind(this);

            if (item.canVote) {
                likeBtn.addEventListener('click', onLikedClicked);
                this.questionListeners.push([likeBtn, onLikedClicked]);

                if (item.isLiked ) {
                    likeBtn.classList.add('btn-primary', 'disabled');
                } else {
                    likeBtn.classList.add('btn-gray');
                }
                likeBtn.title = item.isLiked ? this.questionUnvoteMessage : this.questionVoteMessage;
            } else {
                likeBtn.classList.add('btn-gray', 'disabled');
                likeBtn.title = this.questionVoteDisabledMessage;
            }
            likeBlock.appendChild(likeBtn);

            questionAside.append(likeBlock);

            const questionCreatedAt = document.createElement('div');
            questionCreatedAt.textContent = item.createdAt;
            questionAside.appendChild(questionCreatedAt);
            contentEl.appendChild(questionAside);
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

        const url = new URL(this.notificationProviderUrl);
        url.searchParams.append('topic', `https://vimeet.events/happening/${this.happeningId}/webinar/questions`);

        var eventSource = new esPolyfill.EventSourcePolyfill(url, {
            headers: {
                'Authorization': `Bearer ${this.notificationSubscriberKey}`
            }
        });
        eventSource.onmessage = (event) => {
            const payload = JSON.parse(event.data);
            if (payload.action === 'update') {
                this.initQuestions();
            }
        }

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


Webinar.prototype.submitChat = function (event) {
    event.preventDefault();
    const chatContent = this.addChatFormContent.value;

    if ('' === chatContent) {
        window.setTimeout(() => this.addChatFormSubmit.disabled = false, 100);
        return;
    }

    this.addChatFormContent.value = '';

    $.post(this.addChatFormAction, JSON.stringify({content: chatContent}), (response) => {
        this.addChatFormSubmit.disabled = false;

        if (response.status === 'ok') {
            return;
        }

        this.addChatFormContent.value = content;
        this.showError('Message creation failed');
    })

        .fail(() => {
            this.addChatFormSubmit.disabled = false;
            this.addChatFormContent.value = content;
            this.showError('Message creation failed');
        });
}

Webinar.prototype.sendUpdateQuestionsSignal = function () {
    this.session.signal({
        type: 'QuestionsUpdate'
    },
    (error) => {
        if (error) {
            console.error('QuestionsUpdate signal error', error);
        }
    });
}

Webinar.prototype.removeQuestionListeners = function () {
    this.questionListeners.forEach((item) => item[0].removeEventListener('click', item[1]));
    this.questionListeners = [];
}

Webinar.prototype.removeChatListeners = function () {
    this.chatListeners.forEach((item) => item[0].removeEventListener('click', item[1]));
    this.chatListeners = [];
}

Webinar.prototype.isSidebarOpened = function () {
    return !this.sideContainer.classList.contains('hide');
}

Webinar.prototype.toggleSideBar = function () {
    if (!this.sidebarAllowed) {
        return
    }
    if (!this.isSidebarOpened()) {
        this.showElement(this.sideContainer);
        this.initChat();
        this.element.classList.add('chat-opened');
        this.layout();

        return;
    }

    this.element.classList.remove('chat-opened');
    this.hideElement(this.sideContainer);
    this.chatInstance.hideTextChat();
    this.initQuestions();
    this.layout();
};

Webinar.prototype.handleInvisibleMode = function () {
    if (this.invisibleMode) {
        if (!window.confirm(this.invisibleModeQuitConfirmationMessage)) {
            return;
        }

        this.invisibleMode = false;
        this.publishStream();

        this.toggleButton(this.invisibleModeButton, false);
        this.showElement(this.toggleVideoElement);
        this.showElement(this.toggleAudioElement);

        return;
    }

    if (!window.confirm(this.invisibleModeEnableConfirmationMessage)) {
        return;
    }

    this.invisibleMode = true;

    if (this.publisher) {
        this.publisher.destroy();
        this.layout();
    }

    this.toggleButton(this.invisibleModeButton, true);
    this.hideElement(this.toggleVideoElement);
    this.hideElement(this.toggleAudioElement);
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

    new Counter(parseInt(this.timeRemaining, 10), parseInt(this.warningRemainingTime, 10), this.countDownContainer, this.timerElement);
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

Webinar.prototype.autoMaximize = function(subscriber) {
    var activity = null;
    subscriber.on('audioLevelUpdated', function(event) {
        if (this.hasMediaSharing) {
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

module.exports = Webinar;
