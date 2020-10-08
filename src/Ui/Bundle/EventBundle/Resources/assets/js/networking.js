import Chat from './components/_Chat';
import ParticipantList from './components/_ParticipantList';
import NotificationSubscriber from './components/_Subscriber';

function init(target) {

    const chatNetworkingElement = target.querySelector('[data-chat-networking]');

    const notificationProviderUrl = chatNetworkingElement.getAttribute('data-notifications-provider-url');
    const notificationSubscriberKey = chatNetworkingElement.getAttribute('data-notifications-subscriber-key');
    const topic = chatNetworkingElement.getAttribute('data-topic');

    const chat = new Chat(chatNetworkingElement);
    chat.initChat();

    const subscriber = new NotificationSubscriber(notificationProviderUrl);
    subscriber.addSubscriber(topic, notificationSubscriberKey, () => {
        chat.reload();
    });


    [].forEach.call(target.querySelectorAll('.participantList'), function (element) {
        const topic = target.querySelectorAll('[data-chat-networking]')[0].getAttribute('data-topic');
        const subscriberKey = target.querySelectorAll('[data-chat-networking]')[0].getAttribute('data-notifications-subscriber-key');
        const providerUrl = target.querySelectorAll('[data-chat-networking]')[0].getAttribute('data-notifications-provider-url');

        const participantList = new ParticipantList(element, providerUrl, topic, subscriberKey);
        participantList.init();
    });
}

init(document);
