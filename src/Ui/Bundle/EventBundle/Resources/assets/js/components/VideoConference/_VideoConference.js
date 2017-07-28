'use strict';

var tokbox = require('@opentok/client');
var axios = require('axios');

var Publisher = require('./Publisher');
var Subscriber = require('./Subscriber');

/**
 * @param {Node} element
 * @constructor
 */
function VideoConference(element) {
    this.element = element;

    this.publisherContainer = element.querySelector('#publisher-' + element.dataset.meetingId);
    this.subscriberContainer = element.querySelector('#subscriber-' + element.dataset.meetingId);

    this.publisher = new Publisher(this.publisherContainer);

    // Event Listener
    this.element.querySelector('.start-visio').addEventListener('click', this.requestAccess.bind(this));
    this.element.querySelector('.end-visio').addEventListener('click', this.disconnect.bind(this));
}

VideoConference.prototype.requestAccess = function () {
    var url = this.element.dataset.visioCallback;

    axios.get(url).then(function (response) {
        this.init(response.data.apiKey, response.data.sessionId, response.data.token);
    }.bind(this));
};

/**
 * @param {string} apiKey
 * @param {string} sessionId
 * @param {string} token
 */
VideoConference.prototype.init = function (apiKey, sessionId, token) {
    if (tokbox.checkSystemRequirements() === 1) {
        this.session = tokbox.initSession(apiKey, sessionId);

        var subscriber = new Subscriber(this.session, this.subscriberContainer);

        // The Session object dispatches a streamCreated event when a new stream (other than your own) is created in a session
        this.session.on('streamCreated', subscriber.subscribe.bind(subscriber));
        this.connect(token);
    }
};

/**
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
            this.publisherContainer.classList.toggle('hide');
        } else {
            console.log(error);
        }
    }.bind(this));
};

VideoConference.prototype.disconnect = function () {
    this.session.disconnect();

    this.element.querySelector('.start-visio').classList.toggle('hide');
    this.element.querySelector('.end-visio').classList.toggle('hide');

    this.publisherContainer.classList.toggle('hide');
    this.subscriberContainer.classList.toggle('hide');
};

VideoConference.prototype.handleError = function (error) {
    if (error) {
        console.log('There was an error: ', error.name, error.message);
    }
};

module.exports = VideoConference;

