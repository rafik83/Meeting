class NotificationCallVisio {

    constructor(element, currentUserConnection) {
        if (element) {
            this.element = element;
            currentUserConnection.addListener(this.onNotificationReceived.bind(this));
            this.element.querySelector('.close').addEventListener('click', () => this.hide());
        }
    }

    onNotificationReceived(notification) {
        const payload = JSON.parse(notification.data);
        if (payload.action === 'request_visio') {
            this.timerId = setTimeout(this.hide.bind(this), 1000000);
            this.show(payload);
            // réinitialiser le timeout
        }
    }

    show(message) {
        const author = document.querySelector('.author');
        const user = document.createElement('p');
        const position = document.createElement('em');
        user.textContent = message.from.userFirstName+' '+ message.from.userLastName+' - '+ message.from.userCompany + ' - ';
        position.textContent = message.from.userPosition;
        author.appendChild(user);
        author.appendChild(position);
        this.element.classList.remove('hide');
        $("#notificationCallVisio").modal('show');
    }

    hide() {
        const author = document.querySelector('.author');
        author.innerHTML = "";
        $("#notificationCallVisio").modal('hide');
    }
}
export default NotificationCallVisio;
