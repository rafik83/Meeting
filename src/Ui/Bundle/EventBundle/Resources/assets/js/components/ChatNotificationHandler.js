export const EVENT_NEW_CHAT_MESSAGE = 'new_chat_message';
export const EVENT_NEW_USER_CONNECTED = 'new_user_connected';

export const createNotificationHandler = (container) => {

    const notificationHandler =  {
        divCallVisioMessageTimeoutId: null,
        /**
         * @param {String} notification
         * @param {Chat} containerChat
         */
        handle: function (notification, containerChat) {
            const payload = JSON.parse(notification.data);
            if (payload.action === 'user_connection') {
                container.dispatchEvent(new CustomEvent(EVENT_NEW_USER_CONNECTED, {detail: payload}));

                return;
            }

            if (payload.action === 'add_chat_message') {
                if (payload.visioEnable){
                    this.chatVisio.visioEnable = '1';
                }
                containerChat.reload();

                container.dispatchEvent(new CustomEvent(EVENT_NEW_CHAT_MESSAGE));

                return;
            }

            if (payload.action === 'update_chat_message_votes') {
                containerChat.updateVotes(payload.messageId, payload.votes);
                return;
            }

            if (payload.action === 'request_visio') {
                const divCallVisioMessage = document.querySelector(`#private-chat-${payload.from.userId} .chat-message-call-visio`);
                if (divCallVisioMessage) {
                    const buttonVisio = divCallVisioMessage.querySelector('.state-normal');

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

    return notificationHandler;
}
