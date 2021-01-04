class NotificationToast {
    /**
     *
     * @param {Element} templateElement
     */
    constructor(templateElement) {
        if (templateElement) {
            this.element = templateElement.cloneNode(true);
            this.element.classList.remove('notification-toast-template');
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

    destroy() {
        this.element.remove();
        this.element = null;
    }

}
export default NotificationToast;
