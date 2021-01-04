'use strict';

import {CHROME_EXTENSION_URL, TokboxInstance} from './TokboxInstance';
import initLayoutContainer from 'opentok-layout-js';
import Publisher, {STREAM_TYPE_CUSTOM, STREAM_TYPE_SCREENSHARE} from './Publisher';
import VideoSubscriber from './Subscriber';
import $ from 'jquery';
import Settings from './Settings';
import HlsPlayer from './HlsPlayer';

import Chat from '../_Chat.js';
import Question from '../_Question.js';
import NotificationSubscriber from '../_Subscriber';
import Modal from '../Modal';
import WebinarStatus from './WebinarStatus';
import SharingManager from './Sharing/Manager';

/**
 * @param {Element} element
 * @param {boolean} isSpeaker
 * @constructor
 */
function Webinar(element, isSpeaker) {
    this.element = element;
    this.isSpeaker = isSpeaker;
    this.invisibleMode = false;
    this.typeScreenShare = 'screen';
    this.typeCustomShare = 'custom';
    this.openTab = 'chat';

    this.startFullScreenClass = 'glyphicon-fullscreen';
    this.endFullScreenClass = 'icon-Reduire_3';

    this.sidebarAllowed = element.getAttribute('data-sidebar-allowed') == 1;

    this.newMessageChatCountNotification = element.querySelector('[data-chat-button] span');
    this.newMessageQuestionCountNotification = element.querySelector('[data-questions-button] span');

    this.canDelete = element.hasAttribute('data-chat-can-delete');
    this.questionCanDelete = element.hasAttribute('data-question-can-delete');

    if (this.sidebarAllowed) {
        this.shiftWithSidebar = 'shift-with-sidebar';
    } else {
        this.shiftWithSidebar = '';
    }

    this.eventId = element.getAttribute('data-event-id');
    this.happeningId = element.getAttribute('data-happening-id');

    this.token = element.getAttribute('data-token');
    this.sessionId = element.getAttribute('data-session-id');
    this.apiKey = element.getAttribute('data-api-key');
    const notificationProviderUrl = element.getAttribute('data-notifications-provider-url');
    this.notificationSubscriber = new NotificationSubscriber(notificationProviderUrl);
    this.topicChat = `https://vimeet.events/happening/${this.happeningId}/webinar/chat`;
    this.topicQuestions = `https://vimeet.events/happening/${this.happeningId}/webinar/questions`;
    this.topicStream = `https://vimeet.events/happening/${this.happeningId}/webinar/stream`;
    this.notificationSubscriberKey = element.getAttribute('data-notifications-subscriber-key');

    this.timeRemainingBeforeStart = element.getAttribute('data-time-remaining-before-start');
    const modalBeforeStartElement = element.querySelector('[data-modal-warning-before-start]');
    if (modalBeforeStartElement) {
        this.modalWarningBeforeStart = new Modal();
        this.modalWarningBeforeStart.init(modalBeforeStartElement);
    }

    if (this.isSpeaker && this.timeRemainingBeforeStart > 0) {
        const startTime = new Date(new Date().getTime() + this.timeRemainingBeforeStart * 1000);

        const remainingTime = startTime.getTime() - new Date().getTime();
        setTimeout(() => {
            this.modalWarningBeforeStart.show();
            this.modalWarningBeforeStart.hideAfter(30000);
        }, remainingTime);
    }

    this.chatWaitingMessage = element.getAttribute('data-chat-waiting-message');
    this.userCompleteName = element.getAttribute('data-user-complete-name');
    this.waitingContainer = element.querySelector('.video-waiting-container');

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
        this.chat = new Chat(element);
        this.question = new Question(element);


        this.chatButton = element.querySelector('[data-chat-button]');
        this.chatButton.addEventListener('click', this.showChat.bind(this));

        this.questionVoteMessage = element.getAttribute('data-question-vote-message');
        this.questionUnvoteMessage = element.getAttribute('data-question-unvote-message');
        this.questionVoteDisabledMessage = element.getAttribute('data-question-vote-disabled-message');

        this.questionsButton = element.querySelector('[data-questions-button]');
        this.questionsButton.addEventListener('click', this.showQuestions.bind(this));


    }

    this.webinarWaitingMessage = element.querySelector('[data-webinar-waiting-message]');
    this.webinarWaitingMedia = element.querySelector('[data-webinar-waiting-media]');
    this.webinarWaitingPlayer = element.querySelector('[data-webinar-waiting-player]');

    this.joinButton = element.querySelector('[data-webinar-join-button]');

    this.layoutContainer = element.querySelector('.layout-container');
    this.layout = initLayoutContainer(this.layoutContainer).layout;

    this.shareVideoElement = null;
    // Incoming screen or video sharing
    this.hasMediaSharing = false;

    this.layoutContainer.addEventListener('refresh', () => this.layout());
    this.layoutContainer.addEventListener('maximize', (event) => this.layoutFocus(event.detail.target));
    this.layoutContainer.addEventListener('maximizeAll', () => {
        this.maximizeAllSubscribers();
        this.layout();
    });

    if (this.isSpeaker) {
        this.sharingManager = new SharingManager(this.element, () => this.session, this.layoutContainer, () => this.publisher.getCameraVideo());
        this.sharingManager.onSharingStarted(this.handleStartStream.bind(this));
        this.sharingManager.onSharingStopped(this.handleStopStream.bind(this));
        this.sharingManager.onSharingError((error) => this.showError(error));
    }

    this.latestMediaSharingStreamId = null;
    this.latestMediaSharingType = null;

    this.liveUrl = element.getAttribute('data-live-url');

    this.endWebinarButton = element.querySelector('.end-webinar');

    if (this.endWebinarButton) {
        this.endWebinarButton.addEventListener('click', this.disconnect.bind(this));
    }

    this.viewersCount = element.getAttribute('data-webinar-initial-viewers-count');
    this.viewersContainer = element.querySelector('.viewers-container');
    this.viewersTextContainer = element.querySelector('.viewers');

    this.streamEndpoint = element.getAttribute('data-webinar-stream-endpoint');

    this.isWebinarRecorded = element.getAttribute('data-webinar-recorded');
    this.canRecordWebinar = element.getAttribute('data-webinar-can-record');
    this.recordEndpoint = element.getAttribute('data-webinar-record-endpoint');
    this.stopRecordEndpoint = element.getAttribute('data-webinar-stop-record-endpoint');
    this.toggleRecordingButton = element.querySelector('#toggle-recording');
    this.webinarAutoStart = element.getAttribute('data-webinar-auto-start');
    this.webinarStopTimestamp = element.getAttribute('data-webinar-stop-timestamp');
    this.isRecording = false;
    this.isHls = element.getAttribute('data-webinar-hls') === 'true';
    if (this.isHls) {
        this.hlsVideoElement = element.querySelector('#webinar-video');
    }

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

    this.lastSeenquestionMessageCount = parseInt(element.getAttribute('data-questions-count'), 10);

    this.webRTCStackInitialized = false;

    this.subscribeToStreamNotifications();

    if (!this.isSpeaker) {
        this.joinButton.addEventListener('click', this.join.bind(this));

        return;
    }

    this.invisibleModeQuitConfirmationMessage = element.getAttribute('data-invisibleMode-quitConfirmation-message');
    this.invisibleModeEnableConfirmationMessage = element.getAttribute('data-invisibleMode-enableConfirmation-message');

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

    this.settings.init(true);

    // open to public management
    this.openToPublicButton = element.querySelector('.open-webinar');
    this.openToPublicButton.addEventListener('click', this.onOpeningToPublic.bind(this));

    this.openStreamToPublicEndpoint = element.getAttribute('data-webinar-open-stream-to-public-endpoint');

    this.isStreamOpenToPublic = element.getAttribute('data-is-stream-open-to-public') === 'true';

    if (!this.isStreamOpenToPublic) {
        // "preparation" modal
        this.prepareModal = this.element.querySelector('#visio-prepare');
        this.closePrepareMessageButton = this.element.querySelector('#visio-prepare-validate');
        if (this.closePrepareMessageButton) {
            this.closePrepareMessageButton.addEventListener('click', this.onPrepareMessageClose.bind(this));
        }

        // "reminder to open to public" modal
        this.openToPublicReminderModal = this.element.querySelector('#visio-openPublicReminder');
        this.openToPublicReminderButton = this.element.querySelector('#visio-openPublicReminder-open');
        this.openToPublicReminderButton.addEventListener('click', () => {
            this.hideElement(this.waitingContainer);
            this.hideElement(this.openToPublicReminderModal);
            this.onOpeningToPublic();
        });
        this.notOpenToPublicReminderButton = this.element.querySelector('#visio-openPublicReminder-notOpen');
        this.notOpenToPublicReminderButton.addEventListener('click', () => {
            this.hideElement(this.waitingContainer);
            this.hideElement(this.openToPublicReminderModal);
        });

        this.hasOpenToPublicButtonBtnPrimaryClass = false;

        this.blinkOpenToPublicBtnIntervalId = null;
    }
}

Webinar.prototype.onOpeningToPublic = function () {
    let params = {};
    if (this.hasMediaSharing) {
        params = {mediaSharingStream: this.latestMediaSharingStreamId, mediaSharingType: this.latestMediaSharingType};
    }

    $.post(this.openStreamToPublicEndpoint, params)
        .fail((error) => {
            this.showError({name: `${error.status}: ${error.statusText}`, message: 'Could not open to public'});
            console.error(error.status, error.statusText, this.recordEndpoint);
        });
}

Webinar.prototype.onSettingsValidate = function (invisibleMode) {
    this.invisibleMode = invisibleMode;
    if (Notification.permission === 'default') {
        Notification.requestPermission().then(permission => this.showPrepareModalOrJoin());
    } else {
        this.showPrepareModalOrJoin();
    }
};

Webinar.prototype.showPrepareModalOrJoin = function()
{
    if (this.isStreamOpenToPublic) {
        this.join();
    } else {
        this.showElement(this.prepareModal);
    }
}

Webinar.prototype.onPrepareMessageClose = function () {
    this.hideElement(this.prepareModal);
    this.join();
};

Webinar.prototype.join = function () {
    this.hideElement(this.joinButton);

    if (this.liveUrl) {
        this.hideElement(this.waitingContainer);
        this.liveVideo();
    } else {
        this.showElement(this.webinarWaitingMessage);
        this.showElement(this.webinarWaitingMedia);
        if (this.webinarWaitingPlayer) {
            this.webinarWaitingPlayer.play();
        }
    }

    this.init();

    if (this.invisibleMode) {
        this.hideElement(this.toggleVideoElement);
        this.hideElement(this.toggleAudioElement);
        this.toggleButton(this.invisibleModeButton, true);
    }
};

Webinar.prototype.initHLSPlayer = function () {
    this.hlsPlayer = new HlsPlayer(this.hlsVideoElement, () => {
        if (this.webinarWaitingPlayer) {
            this.webinarWaitingPlayer.remove();
        }
        this.hideElement(this.waitingContainer);
        this.layout();
    });
    this.addShownChatSubscriber();
    if (this.hlsVideoElement.dataset.hlsUrl) {
        this.hlsPlayer.initHlsStreamPlayer(this.hlsVideoElement.dataset.hlsUrl);
    }
    this.hlsConnect(this.hlsVideoElement.dataset.startViewUrl);
}

Webinar.prototype.initWebRTCStack = function() {
    if (this.sessionId.length === 0 || this.webRTCStackInitialized) {
        return;
    }

    this.webRTCStackInitialized = true;

    this.session = TokboxInstance.initSession(this.apiKey, this.sessionId);

    // incoming streams
    this.session.on('streamCreated', function (event) {
        if (this.webinarWaitingPlayer) {
            this.webinarWaitingPlayer.remove();
        }
        this.hideElement(this.waitingContainer);

        const subscriberManager = new VideoSubscriber(
            this.session,
            this.layoutContainer,
            this.subscribersNameMapping
        );
        const subscriber = subscriberManager.subscribe(event);

        if (this.isScreenShareStream(event.stream)) {
            this.hasMediaSharing = true;
            this.latestMediaSharingStreamId = event.stream.streamId;
            this.latestMediaSharingType = subscriber.stream.videoType;
            this.layoutFocus(subscriber.element);
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

    if (!this.mobile) {
        this.addShownChatSubscriber();
        this.addHiddenQuestionSubscriber();
    }

    this.prepareRecordButtons();
}

/**
 * Initialize session and subscribe to new other stream
 */
Webinar.prototype.init = function () {
    if (this.isNotIE() && TokboxInstance.checkSystemRequirements() !== 1) {
        alert(this.notCompatibleBrowserMessage);
        return;
    }

    this.updateViewers();
    this.showViewerControls();

    new WebinarStatus(this.element, this.isSpeaker);

    if (this.isHls && !this.isSpeaker) {
        this.initHLSPlayer();
        return;
    }

    this.initWebRTCStack();
};

Webinar.prototype.updateViewers = function () {
    this.viewersTextContainer.textContent = this.viewersCount;
};

Webinar.prototype.subscribeToStreamNotifications = function () {
    this.notificationSubscriber.addSubscriber(this.topicStream, this.notificationSubscriberKey, (event) => {
        const payload = JSON.parse(event.data);

        if (payload.action === 'stream_started') {
            if (this.isHls && !this.isSpeaker) {
                const hlsUrl = payload.sessionReference;
                this.hlsPlayer.updateHlsSource(hlsUrl);
                console.info('Received stream_started notification, hlsUrl: ' + hlsUrl);
            }

            if (!this.isHls) {
                this.sessionId = payload.sessionReference;
                this.initWebRTCStack();
            }

            if (!this.isStreamOpenToPublic && this.isSpeaker) {
                this.hideElement(this.openToPublicButton);
                this.showElement(this.endWebinarButton);
                this.isStreamOpenToPublic = true;
                clearTimeout(this.openPublicReminderId);
                clearTimeout(this.blinkOpenToPublicBtnIntervalId);
            }
        }

        if (payload.action === 'viewer_connected') {
            this.viewersCount = payload.connectedUsersCount;
            this.updateViewers();
        }
    });
};

/**
 * Connect to the session, create and publish your stream
 */
Webinar.prototype.connect = function () {
    this.session.connect(this.token, function (error) {

        if(this.isStreamOpenToPublic) {
            this.showElement(this.endWebinarButton);
        } else {
            this.showElement(this.openToPublicButton);
        }

        this.showElement(this.invisibleModeButton);

        if (!this.invisibleMode) {
            this.showElement(this.toggleAudioElement);
            this.showElement(this.toggleVideoElement);
        }

        this.showElement(this.mediaStartSharingButton);
        this.initShareMedia();

        if (!error) {
            if (this.isSpeaker) {
                this.publishStream();
                this.subscribeToStreamNotifications();
            }

            return;
        }

        console.error(error);
    }.bind(this));
};

// declare viewer on server and get hlsUrl if not available when page loads
Webinar.prototype.hlsConnect = function (hlsConnectUrl) {
    $.get(hlsConnectUrl, (response) => {
        if (response.hlsUrl && !this.hlsPlayer.isInitialized()) {
            this.hlsPlayer.initHlsStreamPlayer(response.hlsUrl)
        }
    })
    .fail((error) => {
        console.error(error.status, error.statusText, this.recordEndpoint);
    });
}

Webinar.prototype.showViewerControls = function () {
    this.showElement(this.viewersContainer);

    if (!this.isMobile && !this.isSidebarOpened()) {
        this.toggleSideBar();
    }

    this.createToggleSidebarButton();
    this.createFullscreenButton();
}

Webinar.prototype.initShareMedia = function () {
    if (!this.isSpeaker) {
        return;
    }

    this.sharingManager.init();
};

Webinar.prototype.prepareRecordButtons = function () {
    if (!this.isWebinarRecorded || !this.canRecordWebinar) {
        return;
    }

    const recordAutoStart = this.webinarAutoStart == 1;
    if (recordAutoStart) {
        const nowTimestamp = Math.round((new Date()).getTime() / 1000);
        if (!this.isRecording && nowTimestamp < this.webinarStopTimestamp) {
            if (this.timeRemainingBeforeStart > 5 * 60) {
                // schedule record start 5 minutes before start
                setTimeout(this.requestRecordStart.bind(this), (this.timeRemainingBeforeStart - 5 * 60) * 1000);
            } else {
                // force record start
                setTimeout(this.requestRecordStart.bind(this), 1000);
            }
        }

        if (nowTimestamp < this.webinarStopTimestamp) {
            setTimeout(this.requestRecordStop.bind(this), (this.webinarStopTimestamp - nowTimestamp) * 1000);
        }

        return;
    }

    this.toggleRecordingButton.classList.remove('hide');
    this.toggleRecordingButton.addEventListener('click', () => {
        if (!this.isRecording) {
            // call endpoint record
            this.toggleRecording(true);
            this.requestRecordStart();
        } else {
            // call endpoint stop record
            this.toggleRecording(false);
            this.requestRecordStop();
        }
    });

    this.session.on('signal:startRecording', (event) => {
        this.toggleRecording(true);
    });

    this.session.on('signal:stopRecording', (event) => {
        this.toggleRecording(false);
    });
};

Webinar.prototype.requestRecordStart = function () {
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
            this.showError({name: `${error.status}: ${error.statusText}`, message: 'Could not start recording'});
            console.error(error.status, error.statusText, this.recordEndpoint);
        });
}

Webinar.prototype.requestRecordStop = function () {
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

Webinar.prototype.toggleRecording = function (recording) {
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

/**
 * @param {Element} element
 */
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

Webinar.prototype.blinkOpenToPublicBtnHandler = function() {
    if (!this.hasOpenToPublicButtonBtnPrimaryClass) {
        this.openToPublicButton.classList.add('btn-primary');
    } else {
        this.openToPublicButton.classList.remove('btn-primary');
    }

    this.hasOpenToPublicButtonBtnPrimaryClass = !this.hasOpenToPublicButtonBtnPrimaryClass;
}

/**
 *  Publish your camera and microphone stream
 */
Webinar.prototype.publishStream = function () {
    this.hideElement(this.waitingContainer);
    this.hideElement(this.webinarWaitingMessage);

    if (!this.isStreamOpenToPublic) {
        this.openPublicReminderId = setTimeout(() => {
            this.showElement(this.waitingContainer);
            this.showElement(this.openToPublicReminderModal);

            this.blinkOpenToPublicBtnIntervalId = setInterval(this.blinkOpenToPublicBtnHandler.bind(this), 1000);
        }, this.timeRemainingBeforeStart * 1000);
    }

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
        this.handleStartStream(event.stream, 'video');
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

Webinar.prototype.handleStartStream = function (
    stream,
    type
) {
    const streamId = stream.streamId;

    $.post(this.streamEndpoint, {
        streamId: streamId,
        type: type,
        action: 'start'
    })
    .fail((error) => {
        this.showError('Stream creation failed');
        console.error(error);
    });
};

Webinar.prototype.handleStopStream = function (
    stream,
    type
) {
    if (!stream) {
        return;
    }
    const streamId = stream.streamId;

    $.post(this.streamEndpoint, {
        streamId,
        type,
        action: 'stop',
    })
    .fail((error) => {
        this.showError('Stream stop failed');
        console.error(error);
    });
};

Webinar.prototype.liveVideo = function () {
    const liveElement = document.createElement('iframe');
    liveElement.setAttribute('src', this.liveUrl);
    liveElement.setAttribute('frameborder', '0');
    liveElement.setAttribute('allow', 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen');
    liveElement.setAttribute('allowfullscreen', '1');
    this.layoutContainer.appendChild(liveElement);

    this.layoutFocus(liveElement);
};

Webinar.prototype.createToggleSidebarButton = function () {
    if (!this.sidebarAllowed) {
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

Webinar.prototype.addShownChatSubscriber = function () {
    if (this.mobile) {
        return;
    }

    this.notificationSubscriber.addSubscriber(
        this.topicChat,
        this.notificationSubscriberKey,
        (event) => {
            const payload = JSON.parse(event.data);

            if (payload.action === 'add_chat_message' || payload.action === 'delete_chat_message') {
                this.chat.reload();
            }

            if (payload.action === 'update_chat_message_votes') {
                this.chat.updateVotes(payload.messageId, payload.votes);
            }
        }
    );
}

Webinar.prototype.addHiddenChatSubscriber = function () {
    this.notificationSubscriber.addSubscriber(
        this.topicChat,
        this.notificationSubscriberKey,
        function (event) {
            const payload = JSON.parse(event.data);

            if (payload.action === 'delete_chat_message') {
                this.lastSeenChatMessagesCount = Math.max(0, this.lastSeenChatMessagesCount - 1);
            }

            if (payload.action === 'add_chat_message') {
                const newMessageCount = payload.msg_count - this.lastSeenChatMessagesCount;
                this.newMessageChatCountNotification.textContent = newMessageCount > 99 ? '99+' : newMessageCount;
                this.newMessageChatCountNotification.classList.add('alert-notification');
            }

        }.bind(this)
    );
}

Webinar.prototype.addHiddenQuestionSubscriber = function () {
    this.notificationSubscriber.addSubscriber(
        this.topicQuestions,
        this.notificationSubscriberKey,
        function (event) {
            const payload = JSON.parse(event.data);

            if (payload.action === 'update') {
                const newQuestionCount = payload.msg_count - this.lastSeenquestionMessageCount;
                this.newMessageQuestionCountNotification.textContent = newQuestionCount > 99 ? '99+' : newQuestionCount;
                this.newMessageQuestionCountNotification.classList.add('alert-notification');
            }

            if (payload.action === 'delete') {
                this.lastSeenquestionMessageCount = Math.max(0, this.lastSeenquestionMessageCount -1);
            }

        }.bind(this)
    );
}

Webinar.prototype.showChat = function (event) {
    event.preventDefault();
    this.openTab = 'chat';
    this.lastSeenquestionMessageCount = this.question.questionMessageCount;
    this.questionsButton.classList.remove('btn-primary');
    this.questionsButton.classList.add('btn-gray');
    this.chatButton.classList.remove('btn-gray');
    this.chatButton.classList.add('btn-primary');
    this.hideElement(this.question.questionsContainer);
    this.showElement(this.chat.chatContainer);
    this.chat.reload();
    this.notificationSubscriber.removeSubscriber(this.topicChat);
    this.addShownChatSubscriber();
    this.notificationSubscriber.removeSubscriber(this.topicQuestions);
    this.newMessageChatCountNotification.textContent = '';
    this.newMessageChatCountNotification.classList.remove('alert-notification');
    this.addHiddenQuestionSubscriber();

};

Webinar.prototype.showQuestions = function (event) {
    event.preventDefault();
    this.openTab = 'questions';
    this.lastSeenChatMessagesCount = this.chat.getChatMessagesCount();
    this.chatButton.classList.remove('btn-primary');
    this.chatButton.classList.add('btn-gray');
    this.questionsButton.classList.remove('btn-gray');
    this.questionsButton.classList.add('btn-primary');

    this.hideElement(this.chat.chatContainer);
    this.notificationSubscriber.removeSubscriber(this.topicChat);
    this.notificationSubscriber.removeSubscriber(this.topicQuestions);
    this.showElement(this.question.questionsContainer);

    this.question.initQuestions();

    this.newMessageQuestionCountNotification.textContent = '';
    this.newMessageQuestionCountNotification.classList.remove('alert-notification');

    this.notificationSubscriber.addSubscriber(
        this.topicQuestions,
        this.notificationSubscriberKey,
        (event) => {
            const payload = JSON.parse(event.data);
            if (payload.action === 'update' || payload.action === 'delete') {
                this.question.initQuestions();
            }
        }
    );

    if (this.newMessageQuestionCountNotification.textContent === '') {
        this.newMessageQuestionCountNotification.classList.remove('alert-notification');
    } else {
        this.newMessageQuestionCountNotification.classList.add('alert-notification');
    }

    this.addHiddenChatSubscriber();
};


Webinar.prototype.isSidebarOpened = function () {
    return !this.sideContainer.classList.contains('hide');
}

Webinar.prototype.toggleSideBar = function () {
    if (!this.sidebarAllowed) {
        return
    }
    if (!this.isSidebarOpened()) {
        this.showElement(this.sideContainer);
        this.chat.initChat();
        this.element.classList.add('chat-opened');
        this.layout();

        return;
    }

    this.element.classList.remove('chat-opened');
    this.hideElement(this.sideContainer);
    this.question.initQuestions();
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

Webinar.prototype.isScreenShareStream = function (stream) {
    return [STREAM_TYPE_SCREENSHARE, STREAM_TYPE_CUSTOM].includes(stream.videoType);
};

/** Focus one element in layout */
Webinar.prototype.layoutFocus = function (element) {
    this.minimizeAllSubscribers();
    this.maximize(element);
    this.layout();
};

Webinar.prototype.maximize = function (element) {
    element.classList.add('OT_big');
};

Webinar.prototype.minimize = function (element) {
    element.classList.remove('OT_big');
};

Webinar.prototype.minimizeAllSubscribers = function () {
    this.subscribers.forEach((subscriber) => {
        this.minimize(subscriber.element);
    });
};

Webinar.prototype.maximizeAllSubscribers = function () {
    this.subscribers.forEach((subscriber) => {
        this.maximize(subscriber.element);
    });
};

Webinar.prototype.autoMaximize = function (subscriber) {
    var activity = null;
    subscriber.on('audioLevelUpdated', function (event) {
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
                this.layoutFocus(subscriber.element);
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

export default Webinar;
