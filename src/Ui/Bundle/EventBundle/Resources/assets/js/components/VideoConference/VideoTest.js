'use strict';

var OTNetworkTest = require('opentok-network-test-js').default;
var tokbox = require('@opentok/client');

/**
 * @constructor
 *
 * @param {Element} element
 */
function VideoConferenceTest(element) {
    this.element = element;

    this.token = element.getAttribute('data-token');
    this.sessionId = element.getAttribute('data-session-id');
    this.apiKey = element.getAttribute('data-api-key');

    this.spinner = element.querySelector('[data-spinner]');
    this.resultNetwork = element.querySelector('[data-result-network]');
    this.resultVideo = element.querySelector('[data-result-video]');
    this.resultAudio = element.querySelector('[data-result-audio]');
    this.resultScreensharing = element.querySelector('[data-result-screensharing]');

    // Error translations
    this.audioNotSupportedError = element.getAttribute('data-error-audio-not-supported');
    this.videoNotSupportedError = element.getAttribute('data-error-video-not-supported');
    this.networkApiError = element.getAttribute('data-error-network-api');
    this.networkMediaError = element.getAttribute('data-error-network-media');
    this.networkMessagingError = element.getAttribute('data-error-network-messaging');
    this.networkLoggingError = element.getAttribute('data-error-network-logging');
    this.mosQualityLowError = element.getAttribute('data-error-quality-low');
    this.qualityTestError = element.getAttribute('data-error-quality-test');
    this.connectivityTestError = element.getAttribute('data-error-connectivity-test');

    this.startButton = element.querySelector('[data-start-button]');
    this.startButton.addEventListener('click', this.start.bind(this));
    this.startButton.style.display = 'inline';

    this.loading = element.querySelector('[data-loading]');
    this.loading.style.display = 'none';
}

VideoConferenceTest.prototype.updateResult = function(element, comment, status) {
    var commentElement = element.querySelector('[data-comment]');
    commentElement.innerHTML = comment;

    if (status) {
        var labelIconElement = element.querySelector('[data-label-icon]');
        labelIconElement.classList.add('label');
        labelIconElement.classList.add('success' === status ? 'label-success' : 'label-danger');
    }
};

VideoConferenceTest.prototype.start = function() {
    this.spinner.style.display = 'block';
    this.startButton.style.display = 'none';

    var otNetworkTest = new OTNetworkTest(tokbox, {
        apiKey: this.apiKey,
        sessionId: this.sessionId,
        token: this.token
    });

    this.updateResult(this.resultNetwork, 'Test de la connexion en cours...');

    otNetworkTest.testConnectivity().then(function (results) {
        if (!results.success) {
            this.updateResult(this.resultNetwork, this.networkApiError, 'error');
            this.end();

            return;
        }

        this.updateResult(this.resultNetwork, 'Test de la connexion Ok !', 'success');
        this.updateResult(this.resultAudio, 'Test en cours');
        this.updateResult(this.resultVideo, 'Test en cours');

        var callbackCount = 0;

        otNetworkTest.testQuality(function updateCallback(stats) {
            callbackCount++;
            var dots = '';
            for (var i=0; i<=callbackCount; i++) {
                dots += '.';
            }
            this.updateResult(this.resultAudio, 'Test en cours' + dots);
            this.updateResult(this.resultVideo, 'Test en cours' + dots);
        }.bind(this)).then(function (results) {
            this.updateResult(this.resultAudio, results.audio.supported ? 'Qualité : ' + Math.round(results.audio.mos * 100 / 4.5) + '%' : results.audio.reason, results.audio.supported ? 'success' : 'error');
            this.updateResult(this.resultVideo, results.video.supported ? 'Qualité : ' + Math.round(results.audio.mos * 100 / 4.5) + '%' : results.video.reason, results.video.supported ? 'success' : 'error');
            this.end();
        }.bind(this)).catch(function (error) {
            alert('Quality test error');
            console.log('Quality test error', error);
        }.bind(this));
    }.bind(this)).catch(function(error) {
        alert('Connectivity test error');
        console.log('Connectivity test error', error);
    }.bind(this));
};

VideoConferenceTest.prototype.end = function() {
    this.spinner.style.display = 'none';
};

module.exports = VideoConferenceTest;
