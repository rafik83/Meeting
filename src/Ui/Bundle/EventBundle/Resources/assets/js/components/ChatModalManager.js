import axios from 'axios';
import Chat from './_Chat';
import ChatVisio from './_ChatVisio';
import NotificationCallVisio from './_NotificationCallVisio';
import RefuseVisio from './_RefuseVisio';

export const EVENT_CHAT_MODAL_OPENED = 'chat_modal_opened';

export const createModalManager = (container, userConnection, notificationHandler) => {
    const notificationCallVisio = new NotificationCallVisio(container.querySelector('[data-notification-call-visio]'), userConnection);

    const modalManager = {
        userConnection,
        notificationHandler,
        notificationCallVisio,
        close: function () {
            $(this.currentModal).modal('hide');
            this.currentModal = null;
        },
        open: function (participantNode) {

            const toUserId = parseInt(participantNode.getAttribute('data-participant-user-id'), 10);

            container.dispatchEvent(new CustomEvent(EVENT_CHAT_MODAL_OPENED, { detail: toUserId }));

            const privateChatModalId = 'private-chat-' + toUserId;
            let modal = document.getElementById(privateChatModalId);
            if (modal == null) {
                modal = document.getElementById('privateChat-modalTemplate').cloneNode(true);
                modal.setAttribute('id', privateChatModalId);

                document.getElementById('modal-container').appendChild(modal);
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

    return modalManager;
}
