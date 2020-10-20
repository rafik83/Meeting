class NotificationToast {

    constructor(element, currentUserConnection) {
        if (element) {
            this.element = element;
            currentUserConnection.addListener(this.onNotificationReceived.bind(this));
            this.element.querySelector('.close').addEventListener('click', () => this.hide());
        }
    }

    onNotificationReceived(notification) {
        const payload = JSON.parse(notification.data);
        if (payload.action === 'add_chat_message') {
            this.timerId = setTimeout(this.hide.bind(this), 5000);
            this.show(payload);
        }
    }

    show(message) {
        const url = new URL(this.element.getAttribute('data-private-chat-url'), document.URL);
        url.searchParams.append('toUser', message.authorId);

        this.element.querySelector('.message-content').textContent = message.content;
        this.element.querySelector('.message-author').textContent = message.author;
        this.element.querySelector('.message-link').setAttribute('href', url);
        this.element.classList.remove('hide');
    }

    hide() {
        this.element.classList.add('hide');
    }
}
export default NotificationToast;
