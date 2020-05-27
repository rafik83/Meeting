var VideoConference = require('./components/VideoConference/VideoConference'),
    VideoConferenceTest = require('./components/VideoConference/VideoTest'),
    Webinar = require('./components/VideoConference/Webinar')
;

function init(target) {
    [].forEach.call(target.querySelectorAll('.video-conference'), function (element) {
        new VideoConference(element);
    });

    [].forEach.call(target.querySelectorAll('.video-conference-test'), function (element) {
        new VideoConferenceTest(element);
    });

    [].forEach.call(target.querySelectorAll('.webinar-speaker'), function (element) {
        new Webinar(element, true);
    });

    [].forEach.call(target.querySelectorAll('.webinar-viewer'), function (element) {
        new Webinar(element, false);
    });
}

init(document);
