'use strict';

import $ from 'jquery';
import {TokboxInstance, CHROME_EXTENSION_URL} from './TokboxInstance';
import TokBoxNetworkTest from 'opentok-network-test-js';
import VideoConference from './VideoConference';
import isEdge from './../browser/isEdge';
import Settings from './Settings';

function VideoConferenceTest(element) {
    this.element = element;

    this.token = element.getAttribute('data-token');
    this.sessionId = element.getAttribute('data-session-id');
    this.apiKey = element.getAttribute('data-api-key');

    this.results = element.querySelector('[data-results]');
    this.progress = element.querySelector('[data-progress]');
    this.progressBar = this.progress.querySelector('.progress-bar');
    this.resultNetwork = element.querySelector('[data-result-network]');
    this.resultQuality = element.querySelector('[data-result-quality]');
    this.resultWarning = element.querySelector('[data-result-warning]');

    this.settingsContainer = this.element.querySelector('[data-settings-container]');

    this.labelNotCompatibleBrowser = element.getAttribute('data-not-compatible-browser-message');
    this.labelTestInProgress = element.getAttribute('data-label-test-in-progress');
    this.labelTestSuccessful = element.getAttribute('data-label-test-successful');
    this.labelQuality = element.getAttribute('data-label-quality');
    this.labelNetworkApiError = element.getAttribute('data-error-network-api');
    this.labelInstallScreensharingExtension = element.getAttribute('data-install-screensharing-extension-message');

    this.visioTestedUrl = element.getAttribute('data-visio-tested-url');

    this.videoConferencePreview = element.querySelector('.video-conference-preview');
    this.loading = element.querySelector('[data-loading]');
    this.loading.classList.add('hide');

    this.labels = { 'success': 'label-success', 'warning': 'label-warning', 'error': 'label-danger'};

    if (isEdge) {
        element.querySelector('[data-alert-edge]').classList.remove('hide');
    }

    this.settings = new Settings(
        this.settingsContainer.querySelector('#video-settings-section'),
        this.start.bind(this),
        false
    );
    this.settingsContainer.classList.remove('hide');
}

VideoConferenceTest.prototype.updateResult = function(element, comment, status) {
    var commentElement = element.querySelector('[data-comment]');
    commentElement.innerHTML = comment;

    if (status) {
        const labelIconElement = element.querySelector('[data-label-icon]');
        labelIconElement.classList.add('label');
        labelIconElement.classList.add(this.labels[status]);
    }
};

VideoConferenceTest.prototype.updateWarning = function(comment) {
    this.resultWarning.classList.remove('hide')
    this.resultWarning.innerHTML += comment + '<br>';
};

VideoConferenceTest.prototype.updateProgress = function(value) {
    this.progressBar.style.width = value + '%';
};

VideoConferenceTest.prototype.start = function() {
    this.videoConferencePreview.classList.remove('hide');
    this.results.classList.remove('hide');
    this.progress.classList.remove('hide');
    this.settingsContainer.classList.add('hide');

    var tokBoxNetworkTestInstance = new TokBoxNetworkTest(TokboxInstance, {
        apiKey: this.apiKey,
        sessionId: this.sessionId,
        token: this.token
    }, {timeout: 10000});

    this.updateProgress(1);
    this.updateResult(this.resultNetwork, this.labelTestInProgress);

    tokBoxNetworkTestInstance.testConnectivity().then(function (results) {
        if (!results.success) {
            this.updateResult(this.resultNetwork, this.labelNetworkApiError, 'error');
            this.progress.classList.add('hide');

            return;
        }

        this.updateProgress(10);
        this.updateResult(this.resultNetwork, this.labelTestSuccessful, 'success');
        this.updateResult(this.resultQuality, this.labelTestInProgress);

        var callbackCount = 0;

        tokBoxNetworkTestInstance.testQuality(function updateCallback(stats) {
            callbackCount++;
            this.updateProgress(10 + Math.min(callbackCount*3, 80));
            this.updateResult(this.resultQuality, this.labelTestInProgress);

        }.bind(this)).then(function (results) {
            this.updateAudioVideoResult(results);
            tokBoxNetworkTestInstance.stop();
            this.checkScreenSharingCapability();

        }.bind(this)).catch(function (error) {
            console.error('testQuality error', error);
            var isUnsupportedBrowser = 'UnsupportedBrowser' === error.name;
            this.updateResult(this.resultQuality, error.toString(), isUnsupportedBrowser ? 'warning' : 'error');
            this.checkScreenSharingCapability();
        }.bind(this));
    }.bind(this)).catch(function(error) {
        console.error('testConnectivity error', error);
        this.updateResult(this.resultNetwork, this.labelNetworkApiError, 'error');
        this.progress.classList.add('hide');
    }.bind(this));
};

VideoConferenceTest.prototype.checkScreenSharingCapability = function() {
    this.updateProgress(90);

    TokboxInstance.checkScreenSharingCapability(function(response) {
        if (!response.supported || response.extensionRegistered === false) {
            this.updateWarning(this.labelNotCompatibleBrowser);
        } else if (response.extensionRegistered && response.extensionInstalled === false) {
            this.updateWarning('<a href="' + CHROME_EXTENSION_URL + '" class="btn btn-link" target="_blank">' + this.labelInstallScreensharingExtension + '</a>', 'error');
        }

        this.end();
    }.bind(this));
};

VideoConferenceTest.prototype.updateAudioVideoResult = function(result) {
    const isFullySupported = result.audio.supported && result.video.supported;
    const grade = Math.round((result.audio.mos + result.video.mos) / 9 * 50) / 10;
    let comment;
    if (isFullySupported) {
        comment = `${this.labelTestSuccessful} <br>${this.labelQuality}: ${grade}/5`;
    } else {
        comment = `Audio: ${result.audio.reason} <br>Video: ${result.video.reason}`;
    }
    const status = isFullySupported ? 'success' : 'error';
    this.updateResult(this.resultQuality, comment, status);
};

VideoConferenceTest.prototype.end = function() {
    this.updateProgress(100);
    this.progressBar.classList.remove('active');
    this.progressBar.classList.remove('progress-bar-striped');
    this.videoConferencePreview.querySelector('.buttons-container').classList.remove('hide');
    this.videoConferencePreview.classList.remove('hide');
    $.post(this.visioTestedUrl);
    new VideoConference(this.videoConferencePreview, false);
};

export default VideoConferenceTest;
