import Chat from './components/_Chat';
import ParticipantList from './components/_ParticipantList';
import { NetworkingBadgeManager, BADGE_TYPE } from './components/_NetworkingBadgeManager';
import NotificationSubscriber from './components/_Subscriber';
import { EVENT_NEW_CHAT_MESSAGE, EVENT_NEW_USER_CONNECTED } from './components/ChatNotificationHandler';
import { EVENT_OPEN_CHAT_MODAL } from './components/ChatModalManager';
import ParticipantListFilter from './components/_ParticipantListFilter';

const { PRIVATECHAT, GENERALCHAT, NETWORKING_BUTTON, SINGLE_DISCUSSION_ITEM } = BADGE_TYPE;

const CHAT_TAB = {
    PRIVATE: "private",
    GENERAL: "general",
}

let activeChatTab = CHAT_TAB.GENERAL;

export default function initNetworking(target, userConnection, notificationHandler, modalManager) {

    const chatNetworkingElement = target.querySelector('[data-chat-networking]');
    const participantLists = [];

    if (!chatNetworkingElement) {
        return;
    }

    const chatPrivateContainer = document.querySelector('[data-chat-private-container]');
    const chatGeneralContainer = document.querySelector('[data-chat-container]');

    const chatPrivateButton = document.querySelector('[data-chat-private-button]');
    const chatGeneralButton = document.querySelector('[data-chat-general-button]');

    chatPrivateButton.addEventListener('click', showChatPrivate);
    chatGeneralButton.addEventListener('click', showChatGeneral);

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

    if (networkingTopic) {
        // networking page
        const notificationProviderUrl = chatNetworkingElement.getAttribute('data-notifications-provider-url');
        const notificationSubscriberKey = chatNetworkingElement.getAttribute('data-notifications-subscriber-key');

        const subscriber = new NotificationSubscriber(notificationProviderUrl);
        subscriber.addSubscriber(networkingTopic, notificationSubscriberKey, (notification) => {
            notificationHandler.handle(notification, chatNetworking);
        });
    }

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

    target.addEventListener(EVENT_NEW_CHAT_MESSAGE, () => {
        if (activeChatTab !== CHAT_TAB.GENERAL) {
            networkingBadgeManager.updateBadgeCounterValue(GENERALCHAT, 1);
            networkingBadgeManager.incrementMenuBadgesCounter();
        }
    });

    const participantListFilter = new ParticipantListFilter(
        target.getElementById('networking_list_search_input'),
        target.querySelectorAll('.networking_list_row')
    );

    target.addEventListener(EVENT_NEW_USER_CONNECTED, (event) => {
        const payload = event.detail;
        participantLists.forEach((participantList) => {
            participantList.addNewuser(
                payload,
                participantNode => participantNode.addEventListener('click', () => modalManager.open(participantNode))
            );
            participantListFilter.filter(target.querySelectorAll('.networking_list_row'));
        });
        target.querySelectorAll('.networking_list_count')
            .forEach((element) => element.textContent = target.querySelectorAll('.participantList tr').length);
    });

    // private chat modale

    // participants under private chat tab
    const participantNodes = target.querySelectorAll('.participantChat');

    const doesChatItemExistsFromAuthor = (authorId) => {
        const elements = document.querySelectorAll('[chat-item-for-user-id]');
        return Array.from(elements).findIndex((item) => {
            return item.getAttribute('chat-item-for-user-id') == authorId;
        }) !== -1
    }

    target.addEventListener(EVENT_OPEN_CHAT_MODAL, (event) => {
        const authorId = event.detail;
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
    });

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
