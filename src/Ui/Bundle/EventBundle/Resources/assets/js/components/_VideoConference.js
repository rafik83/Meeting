'use strict';

var tokbox = require('@opentok/client');

function VideoConference(element) {
    this.element = element;

    if (tokbox.checkSystemRequirements() === 1) {
        this.session = tokbox.initSession(this.element.dataset.apikey, this.element.dataset.sessionid);
        this.publisher = this.initPublisher.bind(this);

        // Event Listener
        this.element.querySelector('.start-visio').addEventListener('click', this.connect.bind(this));
        this.element.querySelector('.end-visio').addEventListener('click', this.disconnect.bind(this));
        this.session.on('streamCreated', this.subscribe.bind(this));
    }
}

VideoConference.prototype.connect = function () {
    this.element.querySelector('.start-visio').classList.toggle('hide');
    this.element.querySelector('.end-visio').classList.toggle('hide');

    this.session.connect(this.element.dataset.token, function (error) {
        if (!error) {
            this.session.publish(this.publisher, function (error) {
                if (error) {
                    console.log('There was an error publishing: ', error.name, error.message);
                }
            });
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
    this.session.subscribe(event.stream, '.subscriber', {
        insertMode: 'append',
        width: '400',
        height: '300'
    }, function (error) {
        console.log('There was an error on subscribe:', error.name, error.message);
    });
};

VideoConference.prototype.initPublisher = function () {
    var publisherOptions = {
        insertMode: 'append',
        width: '400',
        height: '300',
        accessAllowed: true
    };

    var publisher = this.opentox.initPublisher('.publisher', publisherOptions, function (error) {
        if (error) {
            console.log('There was an error initializing the publisher: ', error.name, error.message);
            return;
        }
    });

    return publisher;
};

module.exports = VideoConference;

