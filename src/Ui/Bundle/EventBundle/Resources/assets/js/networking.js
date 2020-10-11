import Chat from './components/_Chat';
import ParticipantList from './components/_ParticipantList';
import ParticipantListFilter from './components/_ParticipantListFilter';
import NotificationSubscriber from './components/_Subscriber';

export default function initNetworking(target, userConnection) {

    const chatNetworkingElement = target.querySelector('[data-chat-networking]');

    if (!chatNetworkingElement) {
        return;
    }

    const networkingTopic = chatNetworkingElement.getAttribute('data-networking-topic');

    const chat = new Chat(chatNetworkingElement);
    chat.initChat();

    const participantListElements = target.querySelectorAll('.participantList');

    const participantLists = [];

    participantListElements.forEach((element) => {
        const userCurrentIdElement = target.querySelector('[data-user-current]')
        if (!userCurrentIdElement) {
            throw new Error('Node with data-user-current not found');
        }
        participantLists.push(new ParticipantList(element, userCurrentIdElement.getAttribute('data-user-current')));
    });

    const notificationHandler = {
        chat,
        participantLists,
        target,
        handle: function (notification) {
            const payload = JSON.parse(notification.data);
            if (payload.action === 'user_connection') {
                this.participantLists.forEach((participantList) => participantList.addNewuser(payload));
                this.target.querySelectorAll('.networking_list_count')
                    .forEach((element) => element.textContent = this.target.querySelectorAll('.participantList tr').length);
                return;
            }

            if (payload.action === 'add_chat_message') {
                this.chat.reload();
                return;
            }

            if (payload.action === 'update_chat_message_votes') {
                this.chat.updateVotes(payload.messageId, payload.votes);
                return;
            }
        }
    }
    notificationHandler.handle.bind(notificationHandler);

    if (networkingTopic) {
        // networking page
        const notificationProviderUrl = chatNetworkingElement.getAttribute('data-notifications-provider-url');
        const notificationSubscriberKey = chatNetworkingElement.getAttribute('data-notifications-subscriber-key');

        const subscriber = new NotificationSubscriber(notificationProviderUrl);
        subscriber.addSubscriber(networkingTopic, notificationSubscriberKey, (notification) => {
            notificationHandler.handle(notification);
        });
    } else {
        // private chat
        userConnection.addListener((notification) => {
            notificationHandler.handle(notification);
        });
    }

    new ParticipantListFilter(document.getElementById('networking_list_search_input'), target.querySelectorAll('.networking_list_row'));

}
