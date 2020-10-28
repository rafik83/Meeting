import Chat from './components/_Chat';
import ParticipantList from './components/_ParticipantList';
import ParticipantListFilter from './components/_ParticipantListFilter';
import NetworkingNewMessageCounter from './components/_NetworkingNewMessageCounter';
import NotificationSubscriber from './components/_Subscriber';
import axios from 'axios';

export default function initNetworking(target, userConnection) {

    const chatNetworkingElement = target.querySelector('[data-chat-networking]');

    if (!chatNetworkingElement) {
        return;
    }

    const chatPrivateContainer = document.querySelector('[data-chat-private-container]');
    const chatGeneralContainer = document.querySelector('[data-chat-container]');

    const chatPrivateButton = document.querySelector('[data-chat-private-button]');
    const chatGeneralButton = document.querySelector('[data-chat-general-button]');


    chatPrivateButton.addEventListener('click', showChatPrivate);
    chatGeneralButton.addEventListener('click', showChatGeneral);

    function showChatPrivate() {
        chatGeneralContainer.classList.add('hide');
        chatPrivateContainer.classList.remove('hide');
        chatPrivateButton.classList.add('btn-primary');
        chatPrivateButton.classList.remove('btn-gray');
        chatGeneralButton.classList.add('btn-gray');
        chatGeneralButton.classList.remove('btn-primary');
    }

    function showChatGeneral() {
        chatPrivateContainer.classList.add('hide');
        chatGeneralContainer.classList.remove('hide');
        chatPrivateButton.classList.remove('btn-primary');
        chatPrivateButton.classList.add('btn-gray');
        chatGeneralButton.classList.remove('btn-gray');
        chatGeneralButton.classList.add('btn-primary');
    }

    const networkingTopic = chatNetworkingElement.getAttribute('data-networking-topic');

    const chatNetworking = new Chat(chatNetworkingElement);
    chatNetworking.initChat();

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
        participantLists,
        target,
        handle: function (notification, targetChat) {
            const payload = JSON.parse(notification.data);
            if (payload.action === 'user_connection') {
                this.participantLists.forEach((participantList) => {
                    participantList.addNewuser(payload, participantNode => participantNode.addEventListener('click', () => modalManager.open(participantNode)));
                    participantListFilter.filter(this.target.querySelectorAll('.networking_list_row'));
                });
                this.target.querySelectorAll('.networking_list_count')
                    .forEach((element) => element.textContent = this.target.querySelectorAll('.participantList tr').length);
                return;
            }

            if (payload.action === 'add_chat_message') {
                targetChat.reload();
                return;
            }

            if (payload.action === 'update_chat_message_votes') {
                targetChat.updateVotes(payload.messageId, payload.votes);
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
            notificationHandler.handle(notification, chatNetworking);
        });
    }

    const participantListFilter = new ParticipantListFilter(
        document.getElementById('networking_list_search_input'),
        target.querySelectorAll('.networking_list_row')
    );

    // private chat modale

    const participantNodes = target.querySelectorAll('.participantChat');

    const modalManager = {
        userConnection,
        notificationHandler,
        open: function (participantNode) {
            const privateChatModalId = 'private-chat-' + participantNode.getAttribute('data-participant-user-id');
            let modal = document.getElementById(privateChatModalId);
            if (modal == null) {
                modal = document.getElementById('privateChat-modalTemplate').cloneNode(true);
                modal.setAttribute('id', privateChatModalId);

                target.querySelector('.networking_container').appendChild(modal);
                axios.get(participantNode.getAttribute('data-private-chat-url')).then((response) => {
                    modal.querySelector('.modal-body').innerHTML = response.data;

                    const privateChatModalElement = modal.querySelector('[data-chat-networking]');

                    const chat = new Chat(privateChatModalElement);
                    chat.initChat();

                    this.userConnection.addListener((notification) => {
                        this.notificationHandler.handle(notification, chat);
                    });
                });
            }

            $(modal).modal('show')
        }
    }
    modalManager.open.bind(modalManager);
    participantNodes.forEach(participantNode => participantNode.addEventListener('click', () => modalManager.open(participantNode)));

    // open private chat if "toUser" known is queryString
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const toUser = urlParams.get('toUser');
    if (toUser !== null) {
        const toUserParticipantNode = target.querySelector('[data-participant-user-id="' + toUser + '"]')
        if (toUserParticipantNode) {
            modalManager.open(toUserParticipantNode);
        }
    }
    const newMessageItems = document.querySelectorAll("[data-new-messages-count");
    const networkingPageDataNodeElements = document.querySelectorAll("[data-is-networking-page-active]");

    const networkingMessageCounter = new NetworkingNewMessageCounter(newMessageItems);

    networkingMessageCounter.appendNewMessageBadge(networkingPageDataNodeElements)
}
