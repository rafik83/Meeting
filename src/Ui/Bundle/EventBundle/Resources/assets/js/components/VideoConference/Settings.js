const WEBAUDIO_ANALYZER_FFT_SIZE = 2048;
const WEBAUDIO_ANALYZER_SMOOTHING_TIME = 0.8;

/**
 * @constructor
 */
function Settings(
    videoSettingsContainer,
    settingsValidateCallback
) {
    this.videoSettingsContainer = videoSettingsContainer;
    this.settingsValidateCallback = settingsValidateCallback;
    this.settingsFocus = false;
    this.requestPermissionModalFocus = false;
    this.settingsModalFocus = false;
    this.requestPermissionModal = this.videoSettingsContainer.querySelector('#visio-request-permission');
    this.settingsModal = this.videoSettingsContainer.querySelector('#visio-settings');
    this.requestPermissionErrorContainer = this.videoSettingsContainer.querySelector('#visio-request-permission-error');
    this.audioDeviceId = null;
    this.videoDeviceId = null;

    this.validateSettingsButton = this.settingsModal.querySelector('#visio-settings-validate');
    this.requestPermissionButton = this.requestPermissionModal.querySelector('#visio-request-permission-ask');
    this.audioSourceSelect = this.settingsModal.querySelector('#visio-audio-source-select');
    this.videoSourceSelect = this.settingsModal.querySelector('#visio-video-source-select');
    this.videoBox = this.settingsModal.querySelector('#visio-video-box');
    this.audioVolumeProgressBar = this.settingsModal.querySelector('#visio-audio-volume');

    this.constraints = {
        video: true,
        audio: true
    }
    this.currentStream = null;
    this.audioLevelCheckIntervalId = null;
    this.prepareEventListener();
}

Settings.prototype.getUserMedia = function () {
    navigator.mediaDevices
        .getUserMedia(this.constraints)
        .then(
            (stream) => {
                const videoStream = stream.getVideoTracks();
                const audioStream = stream.getAudioTracks();

                if (audioStream.length > 0) {
                    this.audioDeviceId = audioStream[0].getSettings().deviceId;

                    this.audioLevelCheck(stream);
                }

                if (videoStream.length > 0) {
                    this.videoDeviceId = videoStream[0].getSettings().deviceId;
                    this.videoBox.srcObject = stream;
                    this.currentStream = stream;
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

            //permission denied in browser
            if (error.name === "NotAllowedError" || error.name === "PermissionDeniedError") {
                showElement(this.requestPermissionErrorContainer);
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
        const option = document.createElement("option");
        option.value = mediaDevice.deviceId;

        // Handle device with no name
        const kind = mediaDevice.kind === "videoinput" ? "camera" : "audio";
        const countKind = kind === "camera" ? countVideo++ : countAudio++;
        const label = mediaDevice.label || `${kind} ${countKind}`;

        const textNode = document.createTextNode(label);

        option.appendChild(textNode);

        if (mediaDevice.deviceId === this.audioDeviceId
            || mediaDevice.deviceId === this.videoDeviceId
        ) {
            option.selected = true;
        }

        if (mediaDevice.kind === "videoinput") {
            this.videoSourceSelect.appendChild(option);
        } else {
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

    this.requestPermissionButton.addEventListener('click', (event) => {
        this.getUserMedia();
    });

    this.validateSettingsButton.addEventListener('click', (event) => {
        this.closeSettings();
        this.settingsValidateCallback();
    });

    // Event dispatched when a device is plugged or unplugged from the computer/device of the user.
    // Not compatible with safari and IE.
    navigator.mediaDevices.ondevicechange = () => {
        // Useless to handle the devices of the settings modal is not focused.
        // As the permission modal can be focused.
        if (true === this.settingsModalFocus) {
            this.handleDevices();
        }
    };
};

Settings.prototype.prepareConstraints = function () {
    this.constraints = {
        audio: {
            deviceId: {
                exact: this.audioDeviceId
            }
        },
        video: {
            deviceId: {
                exact: this.videoDeviceId
            }
        }
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
    this.currentStream.getTracks().forEach(function (track) {
        track.enabled = false;
    });
    this.currentStream = null;
    this.videoBox.srcObject = null;
    this.context.suspend && this.context.suspend();

    const htmlEvent = document.createEvent('HTMLEvents');
    htmlEvent.initEvent('visio-settings-validate', true, true);
    dispatchEvent(htmlEvent);
}

Settings.prototype.init = function () {
    this.settingsFocus = true;
    this.requestPermissionModalFocus = true;
    this.settingsModalFocus = false;
    showElement(this.videoSettingsContainer);
    showElement(this.requestPermissionModal);
    hideElement(this.settingsModal);
}

// Theses methods do not need to be link to the Settings component.

/**
 * From https://github.com/jitsi/lib-jitsi-meet/blob/master/modules/statistics/LocalStatsCollector.js#L40
 * @param samples
 * @returns {number}
 */
timeDomainDataToAudioLevel = function (samples) {
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
animateLevel = function (newLevel, lastLevel) {
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

showElement = function(element) {
    element.classList.remove('hide');
};

hideElement = function (element) {
    element.classList.add('hide');
};

module.exports = Settings;
