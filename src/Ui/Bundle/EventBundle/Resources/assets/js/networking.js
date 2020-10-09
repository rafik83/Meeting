import Chat from './components/_Chat';
import ParticipantList from './components/_ParticipantList';
import ParticipantListFilter from './components/_ParticipantListFilter';
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


    const participantList = target.querySelectorAll('.participantList');

    [].forEach.call(participantList, function (element) {
        const topic = target.querySelectorAll('[data-chat-networking]')[0].getAttribute('data-topic');
        const subscriberKey = target.querySelectorAll('[data-chat-networking]')[0].getAttribute('data-notifications-subscriber-key');
        const providerUrl = target.querySelectorAll('[data-chat-networking]')[0].getAttribute('data-notifications-provider-url');
        const userCurrentId = target.querySelectorAll('[data-user-current]')[0].getAttribute('data-user-current');
        const participantList = new ParticipantList(target, providerUrl, topic, subscriberKey, userCurrentId);

        participantList.init();
    });

    new ParticipantListFilter(document.getElementById('networking_list_search_input'), target.querySelectorAll('.networking_list_row'));

}

init(document);
