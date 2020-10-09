import Chat from './components/_Chat';
import ParticipantList from './components/_ParticipantList';

function init(target) {

    [].forEach.call(target.querySelectorAll('[data-chat-networking]'), function (element) {
        const topicUrl =  element.getAttribute('data-topic');
        const chat = new Chat(element, topicUrl);
        chat.initChat();
    });


    [].forEach.call(target.querySelectorAll('.participantList'), function (element) {
        const topic = target.querySelectorAll('[data-chat-networking]')[0].getAttribute('data-topic');
        const subscriberKey = target.querySelectorAll('[data-chat-networking]')[0].getAttribute('data-notifications-subscriber-key');
        const providerUrl = target.querySelectorAll('[data-chat-networking]')[0].getAttribute('data-notifications-provider-url');
        const userCurrentId = target.querySelectorAll('[data-user-current]')[0].getAttribute('data-user-current');
        const participantList = new ParticipantList(target, providerUrl, topic, subscriberKey, userCurrentId);

        participantList.init();
    });
}

init(document);
