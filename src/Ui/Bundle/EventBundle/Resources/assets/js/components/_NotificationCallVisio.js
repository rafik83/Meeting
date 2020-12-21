import RefuseVisio from "./_RefuseVisio";

class NotificationCallVisio {

    constructor(element, currentUserConnection) {
        if (element) {
            this.element = element;
            currentUserConnection.addListener(this.onNotificationReceived.bind(this));
            this.acceptVisio = this.element.querySelector('.call-visio-accept');
            this.element.querySelector('.close').addEventListener('click', () => this.hide());
            this.disabled = false;
            this.refuseVisio = new RefuseVisio(this.element.querySelector('.call-visio-refuse'), ()=> this.hide());

            $(element).on('click', '[data-close-modal]', ()=> {
                this.hide();
            });
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
        clearTimeout(this.timerId);
    }

    disable() {
        this.disabled = true;
    }

    enable() {
        this.disabled = false;
    }
}
export default NotificationCallVisio;
