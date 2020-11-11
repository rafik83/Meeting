const WEBAUDIO_ANALYZER_FFT_SIZE = 2048;
const WEBAUDIO_ANALYZER_SMOOTHING_TIME = 0.8;

/**
 * @constructor
 */
function Settings(
    videoSettingsContainer,
    settingsValidateCallback,
    enableInvisibleMode
) {
    this.videoSettingsContainer = videoSettingsContainer;
    this.settingsValidateCallback = settingsValidateCallback;
    this.settingsFocus = false;
    this.requestPermissionModalFocus = false;
    this.settingsModalFocus = false;
    this.requestPermissionModal = this.videoSettingsContainer.querySelector('#visio-request-permission');
    this.settingsModal = this.videoSettingsContainer.querySelector('#visio-settings');
    this.requestPermissionErrorContainer = this.videoSettingsContainer.querySelector('#visio-request-permission-error');
    this.chromeRequired = this.videoSettingsContainer.querySelector('#visio-chrome-required');
    this.invisibleModeOption = this.videoSettingsContainer.querySelector('#invisible-mode-option');
    this.enableInvisibleMode = enableInvisibleMode;

    if (enableInvisibleMode) {
        showElement(this.invisibleModeOption);
    }

    this.audioDeviceId = null;
    this.videoDeviceId = null;

    this.videoDetected = true;
    this.audioDetected = true;

    this.validateSettingsButton = this.settingsModal.querySelector('#visio-settings-validate');
    this.requestPermissionButton = this.requestPermissionModal.querySelector('#visio-request-permission-ask');
    this.audioSourceSelect = this.settingsModal.querySelector('#visio-audio-source-select');
    this.videoSourceSelectContainer = this.settingsModal.querySelector('.visio-settings-video-select');
    this.videoSourceSelect = this.settingsModal.querySelector('#visio-video-source-select');
    this.videoSourceNoVideoContainer = this.settingsModal.querySelector('.visio-settings-video-select-error');
    this.videoBox = this.settingsModal.querySelector('#visio-video-box');
    this.audioVolumeProgressBar = this.settingsModal.querySelector('#visio-audio-volume');
    this.currentStream = null;
    this.audioLevelCheckIntervalId = null;
    this.isRetryingDeviceSearch = false;

    this.constraints = {
        video: this.videoDetected,
        audio: this.audioDetected
    };

    this.detectDevices();
    this.prepareEventListener();

    // Permissions message container
    this.permissionDenialMessage = this.videoSettingsContainer.querySelector('[data-visio-request-permission-denial-message]');
    this.deviceNotFoundMessage = this.videoSettingsContainer.querySelector('[data-visio-request-permission-device-not-found]');
    this.micNotFoundMessage = this.videoSettingsContainer.querySelector('[data-visio-request-permission-mic-not-found]');
}

Settings.prototype.detectDevices = function() {
    navigator.mediaDevices.enumerateDevices().then((devices) => {
        let audioDevices = [];
        let videoDevices = [];

        devices.forEach((mediaDevice) => {
            const kind = mediaDevice.kind;

            if (kind === 'videoinput') {
                videoDevices.push(mediaDevice);
                this.videoDetected = true;
            }

            if (kind === 'audioinput') {
                audioDevices.push(mediaDevice);
                this.audioDetected = true;
            }
        });

        if (audioDevices.length === 0) {
            showElement(this.requestPermissionErrorContainer);
            showElement(this.micNotFoundMessage);

            if (this.requestPermissionModalFocus) {
                this.requestPermissionButton.disabled = true;
            } else {
                this.validateSettingsButton.disabled = true;
            }
        }

        if (videoDevices.length === 0) {
            this.noCameraDetected();
        }
    });
}

Settings.prototype.noCameraDetected = function () {
    this.videoDetected = false;
    this.videoDeviceId = null;
    this.prepareConstraints();
    hideElement(this.videoSourceSelectContainer);
    hideElement(this.videoBox);
    showElement(this.videoSourceNoVideoContainer);
};

Settings.prototype.getUserMedia = function () {
    navigator.mediaDevices
        .getUserMedia(this.constraints)
        .then(
            (stream) => {
                const audioStream = stream.getAudioTracks();

                if (audioStream.length > 0) {
                    this.audioDeviceId = audioStream[0].getSettings().deviceId;

                    this.audioLevelCheck(stream);
                }

                if (this.videoDetected) {
                    const videoStream = stream.getVideoTracks();

                    if (videoStream.length > 0) {
                        if (this.videoDeviceId === null) {
                            this.videoDeviceId = videoStream[0].getSettings().deviceId;
                        }
                        this.videoBox.srcObject = stream;
                        this.currentStream = stream;
                    }
                }

                showElement(this.settingsModal);
                hideElement(this.requestPermissionModal);
                hideElement(this.requestPermissionErrorContainer);
                this.requestPermissionModalFocus = false;
                this.settingsModalFocus = true;

                this.handleDevices();
            }
        ).catch((error) => {
            showElement(this.requestPermissionModal);
            hideElement(this.settingsModal);

            if (error.name === "NotAllowedError" || error.name === "PermissionDeniedError") {
                //permission denied in browser
                showElement(this.requestPermissionErrorContainer);
                showElement(this.permissionDenialMessage);
            } else if (error.name === "NotFoundError" || error.name === "DevicesNotFoundError") {
                this.detectDevices();

                //required track is missing. Can be caused by a missing webcam.
                this.noCameraDetected();

                // Retry without camera.
                if (false === this.isRetryingDeviceSearch) {
                    this.isRetryingDeviceSearch = true;
                    this.getUserMedia();

                    this.requestPermissionModalFocus = true;
                    this.settingsModalFocus = false;

                    console.error(error);
                } else {
                    showElement(this.requestPermissionErrorContainer);
                    showElement(this.deviceNotFoundMessage);
                }
            }

            this.requestPermissionModalFocus = true;
            this.settingsModalFocus = false;

            console.error(error);
            // Check error as it can be caused by a denial of the user permission
        });
};

Settings.prototype.audioLevelCheck = function (currentStream) {
    if (null !== this.audioLevelCheckIntervalId) {
        clearInterval(this.audioLevelCheckIntervalId);
    }

    window.AudioContext = window.AudioContext || window.webkitAudioContext;
    this.context = null;

    if (window.AudioContext) {
        this.context = new AudioContext();
        // https://github.com/jitsi/lib-jitsi-meet/blob/master/modules/statistics/LocalStatsCollector.js#L32
        this.context.suspend && this.context.suspend();
    }

    if (!this.context) {
        return;
    }

    this.context.resume();
    this.analyser = this.context.createAnalyser() || this.context.webkitCreateAnalyser();

    this.analyser.smoothingTimeConstant = WEBAUDIO_ANALYZER_SMOOTHING_TIME;
    this.analyser.fftSize = WEBAUDIO_ANALYZER_FFT_SIZE;

    const source = this.context.createMediaStreamSource(currentStream) || this.context.webkitCreateMediaStreamSource(currentStream);

    source.connect(this.analyser);

    let audioLevelGlobal = 0;

    this.audioLevelCheckIntervalId = setInterval(
        () => {
            const array = new Uint8Array(this.analyser.frequencyBinCount);

            this.analyser.getByteTimeDomainData(array);
            const audioLevel = timeDomainDataToAudioLevel(array);

            if (audioLevel !== audioLevelGlobal) {
                audioLevelGlobal = animateLevel(audioLevel, audioLevelGlobal);
            }

            const percentageAudioLevel = Math.floor(audioLevelGlobal * 100);
            const percentage = percentageAudioLevel.toString() + '%';

            this.audioVolumeProgressBar.setAttribute('style', `width: ${percentage}`)
            this.audioVolumeProgressBar.setAttribute('aria-valuenow', percentageAudioLevel.toString());
            this.audioVolumeProgressBar.innerHtml = `<span class="sr-only">${percentage}</span>`;
        },
        200
    );
};

Settings.prototype.insertDevicesInSelect = function (mediaDevices) {
    this.videoSourceSelect.innerHTML = "";
    this.audioSourceSelect.innerHTML = "";

    let countVideo = 1;
    let countAudio = 1;

    mediaDevices.forEach((mediaDevice) => {
        if (mediaDevice.kind === 'audiooutput'){
            return;
        }
        const option = document.createElement("option");
        option.value = mediaDevice.deviceId;

        // Handle device with no name
        const kind = mediaDevice.kind === "videoinput" ? "camera" : "audio";
        const countKind = kind === "camera" ? countVideo++ : countAudio++;
        const label = mediaDevice.label || `${kind} ${countKind}`;

        const textNode = document.createTextNode(label);

        option.appendChild(textNode);

        if ((mediaDevice.deviceId === this.audioDeviceId && kind === "audio")
            || (mediaDevice.deviceId === this.videoDeviceId && kind === "camera")
        ) {
            option.selected = true;
        }

        if (kind === "camera") {
            this.videoSourceSelect.appendChild(option);
        } else if (kind === "audio") {
            this.audioSourceSelect.appendChild(option);
        }
    });
};

/**
 * @returns {null|string}
 */
Settings.prototype.getAudioSource = function () {
    return this.audioDeviceId;
};

/**
 * @returns {null|string}
 */
Settings.prototype.getVideoSource = function () {
    return this.videoDeviceId;
};

Settings.prototype.handleDevices = function () {
    navigator.mediaDevices
        .enumerateDevices()
        .then((devices) => {
            this.insertDevicesInSelect(devices);
        });
};

Settings.prototype.prepareEventListener = function () {
    const onDeviceSelectChange = () => {
        this.currentStream.getTracks().forEach(function (track) {
            track.stop();
        });
        this.prepareConstraints();
        this.getUserMedia();
    };

    this.audioSourceSelect.addEventListener('change', (event) => {
        this.audioDeviceId = this.audioSourceSelect.value;
        onDeviceSelectChange();
    });

    this.videoSourceSelect.addEventListener('change', (event) => {
        this.videoDeviceId = this.videoSourceSelect.value;
        onDeviceSelectChange();
    });

    // No event to attach if there is no mic.
    if (this.audioDetected) {
        this.requestPermissionButton.addEventListener('click', (event) => {
            this.getUserMedia();
        });
    }

    this.validateSettingsButton.addEventListener('click', (event) => {
        this.closeSettings();
        const invisibleMode = this.enableInvisibleMode && this.invisibleModeOption.querySelector('input[type=checkbox]').checked;
        this.settingsValidateCallback(invisibleMode);
    });

    // Event dispatched when a device is plugged or unplugged from the computer/device of the user.
    // Not compatible with safari and IE.
    navigator.mediaDevices.ondevicechange = () => {
        // Useless to handle the devices if the settings modal is not focused.
        // As the permission modal can be focused.
        if (true === this.settingsModalFocus) {
            this.handleDevices();
        }
    };
};

Settings.prototype.prepareConstraints = function () {
    let audio = this.audioDetected;
    let video = this.videoDetected;

    if (this.audioDeviceId) {
        audio = {
            deviceId: this.audioDeviceId
        };
    }

    if (this.videoDeviceId) {
        video = {
            deviceId: this.videoDeviceId
        };
    }
    this.constraints = {
        audio: audio,
        video: video,
    }
};

Settings.prototype.closeSettings = function () {
    // Hide all modals and container.
    hideElement(this.requestPermissionModal);
    hideElement(this.settingsModal);
    hideElement(this.videoSettingsContainer);

    this.settingsFocus = false;
    this.requestPermissionModalFocus = false;
    this.settingsModalFocus = false;

    // Disable all the tracks used by the settings,
    // To avoid video consumption if the user disable the video or the mic during the meeting.
    if (this.currentStream) {
        this.currentStream.getTracks().forEach(function (track) {
            track.enabled = false;
        });
    }
    this.currentStream = null;
    this.videoBox.srcObject = null;
    this.context.suspend && this.context.suspend();

    const htmlEvent = document.createEvent('HTMLEvents');
    htmlEvent.initEvent('visio-settings-validate', true, true);
    dispatchEvent(htmlEvent);
}

Settings.prototype.init = function (chromeRequired) {
    this.settingsFocus = true;
    this.requestPermissionModalFocus = true;
    this.settingsModalFocus = false;
    showElement(this.videoSettingsContainer);
    showElement(this.requestPermissionModal);
    hideElement(this.settingsModal);

    if (chromeRequired && !(/Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor))) {
        showElement(this.chromeRequired);
        hideElement(this.requestPermissionModal);
        this.chromeRequired.querySelector('#visio-chrome-required-bypass').addEventListener('change', () => {
            this.init(false);
        });
    }
}

// Theses methods do not need to be link to the Settings component.

/**
 * From https://github.com/jitsi/lib-jitsi-meet/blob/master/modules/statistics/LocalStatsCollector.js#L40
 * @param samples
 * @returns {number}
 */
function timeDomainDataToAudioLevel(samples) {
    let maxVolume = 0;

    const length = samples.length;

    for (let i = 0; i < length; i++) {
        if (maxVolume < samples[i]) {
            maxVolume = samples[i];
        }
    }

    return parseFloat(((maxVolume - 127) / 128).toFixed(3));
}

/**
 * From https://github.com/jitsi/lib-jitsi-meet/blob/master/modules/statistics/LocalStatsCollector.js#L61
 * @param {number} newLevel
 * @param {number} lastLevel
 *
 * @returns {number}
 */
function animateLevel(newLevel, lastLevel) {
    let value = 0;
    const diff = lastLevel - newLevel;

    if (diff > 0.2) {
        value = lastLevel - 0.2;
    } else if (diff < -0.4) {
        value = lastLevel + 0.4;
    } else {
        value = newLevel;
    }

    return parseFloat(value.toFixed(3));
}

function showElement(element) {
    element.classList.remove('hide');
}

function hideElement(element) {
    element.classList.add('hide');
}

export default Settings;
