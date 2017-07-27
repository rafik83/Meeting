'use strict';

var tokbox = require('@opentok/client');
var axios = require('axios');

/**
 * @param {Node} element
 * @constructor
 */
function VideoConference(element) {
    this.element = element;

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
        this.session.on('streamCreated', this.subscribe.bind(this));
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
            var publisher = this.initPublisher();

            // publish video to other participant
            this.session.publish(publisher, this.handleError);
        } else {
            console.log(error);
        }
    }.bind(this));
};

VideoConference.prototype.disconnect = function () {
    this.session.disconnect();
    this.element.querySelector('.start-visio').classList.toggle('hide');
    this.element.querySelector('.end-visio').classList.toggle('hide');
};

VideoConference.prototype.subscribe = function (event) {
    var selector = 'subscriber-' + this.element.dataset.meetingId;

    var subscriberOptions = {
        insertMode: 'replace',
        width: '500px',
        height: '500px'
    };

    this.session.subscribe(event.stream, selector, subscriberOptions, this.handleError);
};

/**
 * The Publisher object represents the view of a video you publish
 */
VideoConference.prototype.initPublisher = function () {
    var publisherOptions = {
        insertMode: 'replace',
        width: '500px',
        height: '500px',
        accessAllowed: true
    };

    var selector = 'publisher-' + this.element.dataset.meetingId;

    this.element.querySelector('#' + selector).style.width = '500px';
    this.element.querySelector('#' + selector).style.height = '500px';

    return tokbox.initPublisher(selector, publisherOptions, this.handleError);
};

VideoConference.prototype.handleError = function (error) {
    if (error) {
        console.log('There was an error: ', error.name, error.message);
    }
};

module.exports = VideoConference;

