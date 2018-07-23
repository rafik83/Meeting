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

    var otNetworkTest = new OTNetworkTest(tokbox, {
        apiKey: this.apiKey,
        sessionId: this.sessionId,
        token: this.token
    });

    otNetworkTest.testConnectivity().then(function (results) {
        if (results.success === false) {
            results.failedTests.forEach(function (failTest) {
                switch (failTest.type) {
                    case 'api':
                        alert(this.networkApiError);
                        break;
                    case 'media':
                        alert(this.networkMediaError);
                        break;
                    case 'messaging':
                        alert(this.networkMessagingError);
                        break;
                    case 'logging':
                        alert(this.networkLoggingError);
                        break;
                    default:
                        break;
                }
            }.bind(this));

            return;
        }

        otNetworkTest.testQuality(function updateCallback(stats) {
            console.log('intermediate testQuality stats', stats);
        }).then(function (results) {
            if (results.mos < 1) {
                alert(this.mosQualityLowError);
            }
            // This function is called when the quality test is completed.
            console.log('OpenTok quality results', results);
            var publisherSettings = {};

            if (results.video.reason) {
                console.log('Video not supported:', results.video.reason);
                publisherSettings.videoSource = null; // audio-only
                alert(this.videoNotSupportedError);
            } else {
                publisherSettings.frameRate = results.video.recommendedFrameRate;
                publisherSettings.resolution = results.video.recommendedResolution;
            }

            if (!results.audio.supported) {
                console.log('Audio not supported:', results.audio.reason);
                publisherSettings.audioSource = null;

                alert(this.audioNotSupportedError);
            }

            if (!publisherSettings.videoSource && !publisherSettings.audioSource) {
                alert(this.audioNotSupportedError + ' ' + this.videoNotSupportedError);
            }
        }.bind(this)).catch(function (error) {
            console.log('OpenTok quality test error', error);
            alert(this.qualityTestError);
        }.bind(this));
    }).catch(function(error) {
        console.log('OpenTok connectivity test error', error);
        alert(this.connectivityTestError);
    }.bind(this));
}

module.exports = VideoConferenceTest;
