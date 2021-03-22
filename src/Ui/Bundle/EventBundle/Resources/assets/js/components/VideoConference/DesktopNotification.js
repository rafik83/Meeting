export default class DesktopNotification {
    constructor(title, body) {
        this.title = title;
        this.body = body;
    }

    showPresent() {
        this.notification = new Notification(this.title, {
            body: this.body,
            requireInteraction: true,
        });
        this.notification.onclick = () => window.focus();
    }

    closePresent() {
        if (this.notification) {
            this.notification.close();
            this.notification = null;
        }
    }

    isOpened() {
        return !!this.notification;
    }
}
