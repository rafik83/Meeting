import ChatVisio from "./_ChatVisio";
import RefuseVisio from "./_RefuseVisio";

class NotificationCallVisio {

    constructor(element, currentUserConnection) {
        if (element) {
            this.element = element;
            currentUserConnection.addListener(this.onNotificationReceived.bind(this));
            this.element.querySelector('.close').addEventListener('click', () => this.hide());
            this.disabled = false;
            this.refuseVisio = new RefuseVisio(this.element.querySelector('.glyphicon-remove-sign'), ()=> this.hide());
        }
    }

    onNotificationReceived(notification) {
        const payload = JSON.parse(notification.data);
        if (payload.action === 'request_visio') {
            this.timerId = setTimeout(this.hide.bind(this), 30000);
            this.show(payload);
            this.requestUrlRefuse = payload.urlRefuse;
            this.requestUrlAccept = payload.urlAccept;
            this.refuseVisio.setUrlRefuse(payload.urlRefuse);
            // réinitialiser le timeout
        }
    }

    show(message) {
        if (this.disabled) {
            return;
        }
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

    disable() {
        this.disabled = true;
    }

    enable() {
        this.disabled = false;
    }
}
export default NotificationCallVisio;
