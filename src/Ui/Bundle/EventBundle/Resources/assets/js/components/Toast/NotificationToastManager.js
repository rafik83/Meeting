import NotificationToast from '../Toast/NotificationToast';

class NotificationToastManager {

    constructor(element, container, currentUserConnection) {
        this.listeners = null;

        if (element && container) {
            this.element = element;
            this.container = container;
            currentUserConnection.addListener(this.onNotificationReceived.bind(this));
            this.toasts = [];
            this.listeners = [];
        }
    }

    onNotificationReceived(notification) {
        const payload = JSON.parse(notification.data);

        if (payload.action === 'add_chat_message') {

            const toast = new NotificationToast(this.element);

            this.container.append(toast.element);
            toast.show(payload);
            this.toasts.push(toast);
            this.container.classList.remove('hide');

            if (this.toasts.length > 3) {
                const toastToRemove = this.toasts.shift();
                toastToRemove.destroy();
            }

            const close = () => {
                const index = this.toasts.indexOf(toast);
                this.toasts.splice(index,1);
                toast.destroy();
            };

            toast.element.querySelector('.close').addEventListener('click', close);

            toast.element.querySelector('.message-link').addEventListener('click', () => {
                this.listeners.forEach((callback) => callback(toast));
                close();
            });
        }
    }

    addListener(callback) {
        if (this.listeners === null) {
            return;
        }

        this.listeners.push(callback);
    }

    removeListener(callback) {
        if (this.listeners === null) {
            return;
        }

        this.listeners = this.listeners.filter((item) => {
            return item !== callback
        })
    }
}
export default NotificationToastManager;
