import Chat from './components/_Chat';
import ChatVisio from './components/_ChatVisio';
import ParticipantList from './components/_ParticipantList';
import ParticipantListFilter from './components/_ParticipantListFilter';
import NotificationSubscriber from './components/_Subscriber';
import axios from 'axios';
import RefuseVisio from "./components/_RefuseVisio";

export default function initNetworking(target, userConnection, notificationCallVisio) {
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
        requestUrlAccept: null,
        requestUrlRefuse: null,
        /**
         * @param {String} notification
         * @param {Chat} targetChat
         */
        handle: function (notification, targetChat) {
            const payload = JSON.parse(notification.data);
            if (payload.action === 'user_connection') {
                this.participantLists.forEach((participantList) => participantList.addNewuser(payload, participantNode => participantNode.addEventListener('click', () => modalManager.open(participantNode))));
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

            if (payload.action === 'request_visio') {
                const divCallVisioMessage = document.querySelector('.chat-message-call-visio');
                const buttonVisio = document.querySelector('.state-normal');

                if (divCallVisioMessage != null) {
                    divCallVisioMessage.classList.remove("hide");
                    buttonVisio.classList.add("hide");
                    this.refuseVisio.setUrlRefuse(payload.urlRefuse);
                    setTimeout(() => {
                        divCallVisioMessage.classList.add("hide");
                        buttonVisio.classList.remove("hide");
                    }, 30000);
                }
            }

            if (payload.action === 'refuse_visio') {
                this.chatVisio.onRefuseVisio();
            }

            if (payload.action === 'accept_visio' && payload.from.userId === targetChat.getToUserId()) {
                document.location.href = payload.urlAccept;
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

    new ParticipantListFilter(document.getElementById('networking_list_search_input'), target.querySelectorAll('.networking_list_row'));

    // private chat modale

    const participantNodes = target.querySelectorAll('.participantChat');

    const modalManager = {
        userConnection,
        notificationHandler,
        notificationCallVisio,
        open: function (participantNode) {
            const toUserId = parseInt(participantNode.getAttribute('data-participant-user-id'), 10);
            const privateChatModalId = 'private-chat-' + toUserId;
            let modal = document.getElementById(privateChatModalId);
            if (modal == null) {
                modal = document.getElementById('privateChat-modalTemplate').cloneNode(true);
                modal.setAttribute('id', privateChatModalId);

                target.querySelector('.networking_container').appendChild(modal);
                axios.get(participantNode.getAttribute('data-private-chat-url')).then((response) => {
                    modal.querySelector('.modal-body').innerHTML = response.data;

                    const privateChatModalElement = modal.querySelector('[data-chat-networking]');

                    const chat = new Chat(privateChatModalElement);
                    chat.setToUserId(toUserId);
                    const chatVisio = new ChatVisio(chat, modal.querySelector('.chat-header-tools'));
                    const refuseVisio = new RefuseVisio(modal.querySelector('.call-visio-refuse'),
                        () => {
                            modal.querySelector('.chat-message-call-visio').classList.add('hide');
                            modal.querySelector('.state-normal').classList.remove('hide');
                        });
                    this.notificationHandler.refuseVisio = refuseVisio;
                    this.notificationHandler.chatVisio = chatVisio;
                    chat.initChat();
                    this.notificationCallVisio.disable();
                    chat.addListener((messages) => {
                        chatVisio.onMessagesReceived(messages);
                    });

                    const callback = (notification) => {
                        this.notificationHandler.handle(notification, chat);
                    };
                    this.userConnection.addListener(callback);

                    $(modal).on('hidden.bs.modal', () => {
                        modal.remove();
                        this.notificationCallVisio.enable();
                        this.userConnection.removeListener(callback);
                        chatVisio.abandonRequestVisio();
                    })
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
}
