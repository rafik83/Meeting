'use strict';

var tokbox = require('@opentok/client');
var axios = require('axios');

var Publisher = require('./Publisher');
var Subscriber = require('./Subscriber');

/**
 * @constructor
 *
 * @param {Node} element
 */
function VideoConference(element) {
    this.element = element;

    this.publisherContainer = element.querySelector('#publisher-' + element.dataset.meetingId);
    this.subscriberContainer = element.querySelector('#subscriber-' + element.dataset.meetingId);
    this.helperContainer = element.querySelector('#video-' + element.dataset.meetingId + ' > .video-helper');

    this.publisher = new Publisher(this.publisherContainer);

    // Event Listener
    this.element.querySelector('.start-visio').addEventListener('click', this.requestAccess.bind(this));
    this.element.querySelector('.end-visio').addEventListener('click', this.disconnect.bind(this));
}

/**
 * Request session ID and token in order to access to the session
 */
VideoConference.prototype.requestAccess = function () {
    var url = this.element.dataset.visioCallback;
    this.element.querySelector('.start-visio').disabled = true;

    var request = axios.get(url);

    request.then(function (response) {
        // init and connect to session
        this.init(response.data.apiKey, response.data.sessionId, response.data.token);
    }.bind(this));

    request.catch(function (error) {
        alert('Impossible de démarrer le RDV en visio-conférence.');
        this.element.querySelector('.start-visio').disabled = false;
    }.bind(this));
};

/**
 * Initialize session and subscribe to new other stream
 *
 * @param {string} apiKey
 * @param {string} sessionId
 * @param {string} token
 */
VideoConference.prototype.init = function (apiKey, sessionId, token) {
    if (tokbox.checkSystemRequirements() !== 1) {
        alert('Votre navigateur n\'est pas compatible avec la video conférence en webRTC.');
    }

    this.session = tokbox.initSession(apiKey, sessionId);

    // The Session object dispatches a streamCreated event
    // when a new stream (other than your own) is created in a session
    this.session.on('streamCreated', function (event) {
        console.log('other stream created > subscribe');

        var subscriberManager = new Subscriber(this.session, this.subscriberContainer);
        subscriberManager.subscribe(event);

        this.subscriberContainer.classList.remove('hide');
        this.helperContainer.classList.add('hide');
    }.bind(this));

    // When a stream, other than your own, leaves a session
    // the Session object dispatches a streamDestroyed event
    this.session.on("streamDestroyed", function (event) {
        console.log('Stream destroyed', event);
    }.bind(this));

    this.session.on("sessionDisconnected", function (event) {
        console.log("Session disconnected", event);
    });

    this.connect(token);
};

/**
 * Connect to the session, create and publish your stream
 *
 * @param {string} token
 */
VideoConference.prototype.connect = function (token) {
    this.element.querySelector('.start-visio').classList.toggle('hide');
    this.element.querySelector('.end-visio').classList.toggle('hide');

    this.session.connect(token, function (error) {
        if (!error) {
            // create video view
            var publisher = this.publisher.create();

            // publish video to other participant
            this.session.publish(publisher, this.handleError);

            this.publisherContainer.classList.remove('hide');
            this.helperContainer.classList.remove('hide');
        } else {
            console.log(error);
        }
    }.bind(this));
};

/**
 * Disconnect from the session
 */
VideoConference.prototype.disconnect = function () {
    this.session.disconnect();
    this.session.off();
    this.session = null;

    this.element.querySelector('.start-visio').classList.toggle('hide');
    this.element.querySelector('.start-visio').disabled = false;
    this.element.querySelector('.end-visio').classList.toggle('hide');

    this.publisherContainer.classList.add('hide');
    this.subscriberContainer.classList.add('hide');
    this.helperContainer.classList.add('hide');
};

/**
 * Handle callback errors
 *
 * @param {Object} error
 */
VideoConference.prototype.handleError = function (error) {
    if (error) {
        console.log('There was an error: ', error.name, error.message);
    }
};

module.exports = VideoConference;

