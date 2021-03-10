import VideoConference from './components/VideoConference/VideoConference';
import VideoConferenceTest from './components/VideoConference/VideoTest';
import Webinar from './components/VideoConference/Webinar';

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

// force file hash refresh
const version=1;
