import RefuseVisio from "./_RefuseVisio";

class NotificationCallVisio {

    constructor(element, currentUserConnection) {
        if (element) {
            this.modalElement = element;
            currentUserConnection.addListener(this.onNotificationReceived.bind(this));
            this.acceptVisio = this.modalElement.querySelector('.call-visio-accept');
            this.modalElement.querySelector('.close').addEventListener('click', () => this.hide());
            this.disabled = false;
            this.timerId = null;
            this.refuseVisio = new RefuseVisio(this.modalElement.querySelector('.call-visio-refuse'), ()=> this.hide());
        }
    }

    onNotificationReceived(notification) {
        const payload = JSON.parse(notification.data);
        if (payload.action === 'request_visio') {
            this.timerId = setTimeout(this.hide.bind(this), 30000);
            this.show(payload);
            this.refuseVisio.setUrlRefuse(payload.urlRefuse);
            this.acceptVisio.setAttribute('href', payload.urlAccept);
        }

        if (payload.action === 'abandon_visio') {
            this.hide();
        }
    }

    show(message) {
        if (this.disabled) {
            return;
        }

        const author = this.modalElement.querySelector('.author');
        const user = document.createElement('p');
        const position = document.createElement('em');
        user.textContent = message.from.userFirstName+' '+ message.from.userLastName+' - '+ message.from.userCompany + ' - ';
        position.textContent = message.from.userPosition;

        author.innerHTML = '';
        author.appendChild(user);
        author.appendChild(position);

        this.modalElement.classList.remove('hide');
        $(this.modalElement).modal('show');
    }

    hide() {
        $(this.modalElement).modal('hide');
        if (this.timerId) {
            clearTimeout(this.timerId);
            this.timerId = null;
        }
    }

    disable() {
        this.disabled = true;
    }

    enable() {
        this.disabled = false;
    }
}
export default NotificationCallVisio;
