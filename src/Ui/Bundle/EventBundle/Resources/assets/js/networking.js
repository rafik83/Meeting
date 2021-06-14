import Chat from './components/_Chat';
import ChatVisio from './components/_ChatVisio';
import ParticipantList from './components/_ParticipantList';
import ParticipantListFilter from './components/_ParticipantListFilter';
import { NetworkingBadgeManager, BADGE_TYPE } from './components/_NetworkingBadgeManager';
import NotificationSubscriber from './components/_Subscriber';
import axios from 'axios';
import RefuseVisio from "./components/_RefuseVisio";

const { PRIVATECHAT, GENERALCHAT, NETWORKING_BUTTON, SINGLE_DISCUSSION_ITEM } = BADGE_TYPE;

const CHAT_TAB = {
    PRIVATE: "private",
    GENERAL: "general",
}

let activeChatTab = CHAT_TAB.GENERAL;

const doesChatItemExistsFromAuthor = (authorId) => {
    const elements = document.querySelectorAll('[chat-item-for-user-id]');
    return Array.from(elements).findIndex((item) => {
        return item.getAttribute('chat-item-for-user-id') == authorId;
    }) !== -1
}

export default function initNetworking(target, userConnection, notificationCallVisio, userMeetNodes) {

    const chatNetworkingElement = target.querySelector('[data-chat-networking]');
    const participantLists = [];

    const notificationHandler = {
        participantLists,
        target,
        divCallVisioMessageTimeoutId: null,
        /**
         * @param {String} notification
         * @param {Chat} targetChat
         */
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
                if (activeChatTab !== CHAT_TAB.GENERAL) {
                    networkingBadgeManager.updateBadgeCounterValue(GENERALCHAT, 1);
                    networkingBadgeManager.incrementMenuBadgesCounter();
                }

                if (payload.visioEnable){
                    this.chatVisio.visioEnable = '1';
                }
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
                    this.chatVisio.hideAllButtons();
                    this.chatVisio.setUrlAccept(payload.urlAccept);
                    this.refuseVisio.setUrlRefuse(payload.urlRefuse);
                    this.divCallVisioMessageTimeoutId = setTimeout(() => {
                        divCallVisioMessage.classList.add("hide");
                        this.chatVisio.showVisioButton();
                        buttonVisio.classList.remove("hide");
                    }, 30000);
                }
            }

            if (payload.action === 'refuse_visio') {
                this.chatVisio.onRefuseVisio();
            }

            if (payload.action === 'join_visio') {
                this.chatVisio.showJoinVisioButton(payload.urlAccept);
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


    if (userMeetNodes){
        const modalChat = {
            userConnection,
            notificationHandler,
            notificationCallVisio,
            close: function () {
                $(this.currentModal).modal('hide');
                this.currentModal = null;
            },
            open: function (userMeetNode) {

                const authorId = userMeetNode.getAttribute("data-participant-user-id");

                if (doesChatItemExistsFromAuthor(authorId)) {
                    const deltaToRemove = networkingBadgeManager.getCurrentCounterValueForChatItem(authorId);
                    networkingBadgeManager.updateBadgeCounterValue(
                        PRIVATECHAT, -deltaToRemove
                    );

                    networkingBadgeManager.decreaseChatItemMessageCounter(
                        authorId
                    );
                    networkingBadgeManager.decreaseMenuBadgesCounter(deltaToRemove);
                }

                const userId = userMeetNode.getAttribute('data-participant-user-id');

                const privateChatModalId = 'privateChat-' + userId;
                let modal = document.getElementById(privateChatModalId);

                if (modal == null) {
                    modal = document.getElementById('privateChat-modalTemplate').cloneNode(true);
                    modal.setAttribute('id', privateChatModalId);

                    axios.get(userMeetNode.getAttribute('data-private-chat-url')).then((response) => {
                        modal.querySelector('.modal-body').innerHTML = response.data;

                        const privateChatModalElement = modal.querySelector('[data-chat-networking]');

                        const chat = new Chat(privateChatModalElement);

                        chat.setToUserId(userId);

                        const chatVisio = new ChatVisio(modal.querySelector('.chat-header-tools'), modal.querySelector('.call-visio-accept'));

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
                            clearTimeout(this.notificationHandler.divCallVisioMessageTimeoutId);
                        })
                    });
                }

                this.currentModal = modal;
                $(modal).modal('show')
            }
        }
        modalChat.open.bind(modalChat);
        modalChat.close.bind(modalChat);
        userMeetNodes.forEach(userMeetNode => userMeetNode.addEventListener('click', () => modalChat.open(userMeetNode)));

        $('.networking_container').on('click', '[data-close-modal]', () => {
            modalChat.close();
        });
    }

    if (!chatNetworkingElement) {
        return;
    }

    const chatPrivateContainer = document.querySelector('[data-chat-private-container]');
    const chatGeneralContainer = document.querySelector('[data-chat-container]');

    const chatPrivateButton = document.querySelector('[data-chat-private-button]');
    const chatGeneralButton = document.querySelector('[data-chat-general-button]');

    chatPrivateButton.addEventListener('click', showChatPrivate);
    chatGeneralButton.addEventListener('click', showChatGeneral);

    const newMessageItems = document.querySelectorAll("[data-new-messages-count]");

    const headerSubmenuNetworkingBadgeNodes = document.querySelectorAll(`[${NETWORKING_BUTTON}]`);
    const unreadPrivateChatMessageCountNodes = document.querySelectorAll(`[${PRIVATECHAT}]`);
    const unreadGeneralChatMessageCountNodes = document.querySelectorAll(`[${GENERALCHAT}]`);
    const chatItems = document.querySelectorAll(`[${SINGLE_DISCUSSION_ITEM}]`);

    const networkingBadgeManager = new NetworkingBadgeManager();

    const privateChatStartingCount = networkingBadgeManager.getUnreadChatMessageStartingCount(
        unreadPrivateChatMessageCountNodes[0],
        PRIVATECHAT
    );

    const generalChatStartingCount = networkingBadgeManager.getUnreadChatMessageStartingCount(
        unreadGeneralChatMessageCountNodes[0],
        GENERALCHAT
    );

    networkingBadgeManager.createChatMessageCountBadge(unreadPrivateChatMessageCountNodes[0], PRIVATECHAT, privateChatStartingCount);
    networkingBadgeManager.createChatMessageCountBadge(unreadGeneralChatMessageCountNodes[0], GENERALCHAT, generalChatStartingCount);
    networkingBadgeManager.createDiscussionItemCounterBadge(chatItems);
    networkingBadgeManager.createMenuBadgeCounters(headerSubmenuNetworkingBadgeNodes, privateChatStartingCount + generalChatStartingCount)

    const callback = (notification) => {
        const payload = JSON.parse(notification.data);

        if (payload.action === "add_chat_message" && doesChatItemExistsFromAuthor(payload.authorId)) {
            networkingBadgeManager.updateBadgeCounterValue(PRIVATECHAT, 1);
            networkingBadgeManager.incrementMenuBadgesCounter()
            networkingBadgeManager.incrementChatItemBadgeCounter(payload.authorId)
        }
    };

    userConnection.addListener(callback);

    function showChatPrivate() {
        chatGeneralContainer.classList.add('hide');
        chatPrivateContainer.classList.remove('hide');
        chatPrivateButton.classList.add('btn-primary');
        chatPrivateButton.classList.remove('btn-gray');
        chatGeneralButton.classList.add('btn-gray');
        chatGeneralButton.classList.remove('btn-primary');

        activeChatTab = CHAT_TAB.PRIVATE
    }

    function showChatGeneral() {
        chatPrivateContainer.classList.add('hide');
        chatGeneralContainer.classList.remove('hide');
        chatPrivateButton.classList.remove('btn-primary');
        chatPrivateButton.classList.add('btn-gray');
        chatGeneralButton.classList.remove('btn-gray');
        chatGeneralButton.classList.add('btn-primary');

        activeChatTab = CHAT_TAB.GENERAL
    }

    const networkingTopic = chatNetworkingElement.getAttribute('data-networking-topic');

    const chatNetworking = new Chat(chatNetworkingElement);
    chatNetworking.initChat();

    const participantListElements = target.querySelectorAll('.participantList');

    participantListElements.forEach((element) => {
        const userCurrentIdElement = target.querySelector('[data-user-current]')
        if (!userCurrentIdElement) {
            throw new Error('Node with data-user-current not found');
        }
        participantLists.push(new ParticipantList(element, userCurrentIdElement.getAttribute('data-user-current')));
    });


    // private chat modale

    const participantNodes = target.querySelectorAll('.participantChat');

    const modalManager = {
        userConnection,
        notificationHandler,
        notificationCallVisio,
        close: function () {
            $(this.currentModal).modal('hide');
            this.currentModal = null;
        },
        open: function (participantNode) {

            const authorId = participantNode.getAttribute("data-participant-user-id");

            if (doesChatItemExistsFromAuthor(authorId)) {
                const deltaToRemove = networkingBadgeManager.getCurrentCounterValueForChatItem(authorId);
                networkingBadgeManager.updateBadgeCounterValue(
                    PRIVATECHAT, -deltaToRemove
                );

                networkingBadgeManager.decreaseChatItemMessageCounter(
                    authorId
                );
                networkingBadgeManager.decreaseMenuBadgesCounter(deltaToRemove);
            }

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
                    const chatVisio = new ChatVisio(modal.querySelector('.chat-header-tools'), modal.querySelector('.call-visio-accept'));
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
                        clearTimeout(this.notificationHandler.divCallVisioMessageTimeoutId);
                    })
                });
            }
            this.currentModal = modal;
            $(modal).modal('show')
        }
    }
    modalManager.open.bind(modalManager);
    modalManager.close.bind(modalManager);
    participantNodes.forEach(participantNode => participantNode.addEventListener('click', () => modalManager.open(participantNode)));

    $('.networking_container').on('click', '[data-close-modal]', () => {
        modalManager.close();
    });

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
