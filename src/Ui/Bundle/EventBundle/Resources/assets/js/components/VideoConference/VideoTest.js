'use strict';

import $ from 'jquery';
import {TokboxInstance, CHROME_EXTENSION_URL} from './TokboxInstance';
import TokBoxNetworkTest from 'opentok-network-test-js';
import VideoConference from './VideoConference';
import isEdge from './../browser/isEdge';

function VideoConferenceTest(element) {
    this.element = element;

    this.token = element.getAttribute('data-token');
    this.sessionId = element.getAttribute('data-session-id');
    this.apiKey = element.getAttribute('data-api-key');

    this.results = element.querySelector('[data-results]');
    this.progress = element.querySelector('[data-progress]');
    this.progressBar = this.progress.querySelector('.progress-bar');
    this.resultNetwork = element.querySelector('[data-result-network]');
    this.resultVideo = element.querySelector('[data-result-video]');
    this.resultAudio = element.querySelector('[data-result-audio]');
    this.resultScreensharing = element.querySelector('[data-result-screensharing]');

    this.labelNotCompatibleBrowser = element.getAttribute('data-not-compatible-browser-message');
    this.labelTestInProgress = element.getAttribute('data-label-test-in-progress');
    this.labelTestSuccessful = element.getAttribute('data-label-test-successful');
    this.labelQuality = element.getAttribute('data-label-quality');
    this.labelNetworkApiError = element.getAttribute('data-error-network-api');
    this.labelInstallScreensharingExtension = element.getAttribute('data-install-screensharing-extension-message');

    this.visioTestedUrl = element.getAttribute('data-visio-tested-url');

    this.startButton = element.querySelector('[data-start-button]');
    this.startButton.addEventListener('click', this.start.bind(this));
    this.startButton.style.display = 'inline';

    this.videoConferencePreview = element.querySelector('.video-conference-preview');
    this.loading = element.querySelector('[data-loading]');
    this.loading.style.display = 'none';

    this.labels = { 'success': 'label-success', 'warning': 'label-warning', 'error': 'label-danger'};

    if (isEdge) {
        element.querySelector('[data-alert-edge]').style.display = 'block';
    }
}

VideoConferenceTest.prototype.updateResult = function(element, comment, status) {
    var commentElement = element.querySelector('[data-comment]');
    commentElement.innerHTML = comment;

    if (status) {
        var labelIconElement = element.querySelector('[data-label-icon]');
        labelIconElement.classList.add('label');
        labelIconElement.classList.add(this.labels[status]);
    }
};

VideoConferenceTest.prototype.updateProgress = function(value) {
    this.progressBar.style.width = value + '%';
};

VideoConferenceTest.prototype.start = function() {
    this.videoConferencePreview.style.display = 'block';
    this.results.style.display = 'block';
    this.progress.style.display = 'block';
    this.startButton.style.display = 'none';

    var tokBoxNetworkTestInstance = new TokBoxNetworkTest(TokboxInstance, {
        apiKey: this.apiKey,
        sessionId: this.sessionId,
        token: this.token
    });

    this.updateProgress(1);
    this.updateResult(this.resultNetwork, this.labelTestInProgress);

    tokBoxNetworkTestInstance.testConnectivity().then(function (results) {
        if (!results.success) {
            this.updateResult(this.resultNetwork, this.labelNetworkApiError, 'error');
            this.progress.style.display = 'none';

            return;
        }

        this.updateProgress(10);
        this.updateResult(this.resultNetwork, this.labelTestSuccessful, 'success');
        this.updateResult(this.resultAudio, this.labelTestInProgress);
        this.updateResult(this.resultVideo, this.labelTestInProgress);

        var callbackCount = 0;

        tokBoxNetworkTestInstance.testQuality(function updateCallback(stats) {
            callbackCount++;
            this.updateProgress(10 + Math.min(callbackCount*3, 80));
            this.updateResult(this.resultAudio, this.labelTestInProgress);
            this.updateResult(this.resultVideo, this.labelTestInProgress);

        }.bind(this)).then(function (results) {
            this.updateAudioVideoResult(this.resultAudio, results.audio);
            this.updateAudioVideoResult(this.resultVideo, results.video);
            tokBoxNetworkTestInstance.stop();
            this.checkScreenSharingCapability();

        }.bind(this)).catch(function (error) {
            var isUnsupportedBrowser = 'UnsupportedBrowser' === error.name;
            this.updateResult(this.resultAudio, error.description, isUnsupportedBrowser ? 'warning' : 'error');
            this.updateResult(this.resultVideo, '', isUnsupportedBrowser ? 'warning' : 'error');
            this.checkScreenSharingCapability();
        }.bind(this));
    }.bind(this)).catch(function(error) {
        this.updateResult(this.resultNetwork, this.labelNetworkApiError, 'error');
        this.progress.style.display = 'none';
    }.bind(this));
};

VideoConferenceTest.prototype.checkScreenSharingCapability = function() {
    this.updateProgress(90);

    TokboxInstance.checkScreenSharingCapability(function(response) {
        if (!response.supported || response.extensionRegistered === false) {
            this.updateResult(this.resultScreensharing, this.labelNotCompatibleBrowser, 'error');
        } else if (response.extensionRegistered && response.extensionInstalled === false) {
            this.updateResult(this.resultScreensharing, '<a href="' + CHROME_EXTENSION_URL + '" class="btn btn-link" target="_blank">' + this.labelInstallScreensharingExtension + '</a>', 'error');
        } else {
            this.updateResult(this.resultScreensharing, this.labelTestSuccessful, 'success');
        }

        this.end();
    }.bind(this));
};

VideoConferenceTest.prototype.updateAudioVideoResult = function(element, result) {
    var comment = result.supported ? this.labelTestSuccessful + ' ' + this.labelQuality + ' ' + Math.round(result.mos * 100 / 4.5) + '%' : result.reason;
    var status = result.supported ? 'success' : 'error';
    this.updateResult(element, comment, status);
};

VideoConferenceTest.prototype.end = function() {
    this.updateProgress(100);
    this.progressBar.classList.remove('active');
    this.progressBar.classList.remove('progress-bar-striped');
    this.videoConferencePreview.querySelector('.buttons-container').style.display = 'block';
    this.videoConferencePreview.style.display = 'block';
    $.post(this.visioTestedUrl);
    new VideoConference(this.videoConferencePreview, false);
};

export default VideoConferenceTest;
