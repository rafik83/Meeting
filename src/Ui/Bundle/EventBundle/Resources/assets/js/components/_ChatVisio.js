'use strict';

import axios from "axios";

export default class ChatVisio
{
    constructor (buttonContainer, linkAccept) {
        this.visioEnable = buttonContainer.getAttribute('data-visio-enable');
        this.buttonVisio = buttonContainer.querySelector('.state-normal');
        this.buttonJoin = buttonContainer.querySelector('.state-join');
        this.buttonRequestPending = buttonContainer.querySelector('.state-pending');
        this.buttonRequestBusy = buttonContainer.querySelector('.state-busy');
        this.buttonRequestRefuse = buttonContainer.querySelector('.state-refuse');
        this.buttonRequestNoResponse = buttonContainer.querySelector('.state-no-response');
        this.linkAccept = linkAccept;
        this.buttonVisio.classList.add("hide");
        this.buttonVisio.addEventListener('click', this.onRequestVisio.bind(this));
        this.buttonJoin.addEventListener('click', this.onJoinVisio.bind(this));
    }

    onMessagesReceived(messages) {
        if (this.visioEnable === '1'
            && messages.some((message)=> !message.isAuthor)
            && this.buttonRequestBusy.classList.contains('hide')) {
            this.showVisioButton();
        }
    }

    onRequestVisio(event) {
        const url = this.buttonVisio.getAttribute('data-url');
        axios.post(url).then((response) => {
            this.showRequestPendingButton();
            this.timerId = setTimeout(() => {
                this.showRequestNoResponseButton();
            }, 40000);
        });
    }

    onRefuseVisio() {
        this.showRequestRefuseButton();
        clearTimeout(this.timerId);
    }

    abandonRequestVisio() {
        const url = this.buttonRequestPending.getAttribute('data-abandon-url');
        axios.post(url);
    }

    setUrlAccept(urlAccept) {
        this.linkAccept.setAttribute('href', urlAccept);
    }

    onJoinVisio() {
       window.open(this.urlAccept);
    }

    showVisioButton(){
        this.buttonVisio.classList.remove('hide');
        this.buttonJoin.classList.add('hide');
        this.buttonRequestPending.classList.add('hide');
        this.buttonRequestBusy.classList.add('hide');
        this.buttonRequestRefuse.classList.add('hide');
        this.buttonRequestNoResponse.classList.add('hide');
    }

    showJoinVisioButton(urlAccept) {
        // prepare 'join' button in case browser block new tab opening
        this.buttonVisio.classList.add('hide');
        this.buttonJoin.classList.remove('hide');
        this.buttonRequestPending.classList.add('hide');
        this.buttonRequestBusy.classList.add('hide');
        this.buttonRequestRefuse.classList.add('hide');
        this.buttonRequestNoResponse.classList.add('hide');
        clearTimeout(this.timerId);
        this.urlAccept = urlAccept;

        // open in new tab
        this.onJoinVisio();
    }

    showRequestPendingButton(){
        this.buttonVisio.classList.add('hide');
        this.buttonJoin.classList.add('hide');
        this.buttonRequestPending.classList.remove('hide');
        this.buttonRequestBusy.classList.add('hide');
        this.buttonRequestRefuse.classList.add('hide');
        this.buttonRequestNoResponse.classList.add('hide');
    }

    /**
     * Not yet used - busy button visibility is controlled by twig
     */
    showRequestBusyButton(){
        this.buttonVisio.classList.add('hide');
        this.buttonJoin.classList.add('hide');
        this.buttonRequestPending.classList.add('hide');
        this.buttonRequestBusy.classList.remove('hide');
        this.buttonRequestRefuse.classList.add('hide');
        this.buttonRequestNoResponse.classList.add('hide');
    }

    showRequestRefuseButton(){
        this.buttonVisio.classList.add('hide');
        this.buttonJoin.classList.add('hide');
        this.buttonRequestPending.classList.add('hide');
        this.buttonRequestBusy.classList.add('hide');
        this.buttonRequestRefuse.classList.remove('hide');
        this.buttonRequestNoResponse.classList.add('hide');
    }

    showRequestNoResponseButton(){
        this.buttonVisio.classList.add('hide');
        this.buttonJoin.classList.add('hide');
        this.buttonRequestPending.classList.add('hide');
        this.buttonRequestBusy.classList.add('hide');
        this.buttonRequestRefuse.classList.add('hide');
        this.buttonRequestNoResponse.classList.remove('hide');
    }

    hideAllButtons() {
        this.buttonVisio.classList.add('hide');
        this.buttonJoin.classList.add('hide');
        this.buttonRequestPending.classList.add('hide');
        this.buttonRequestBusy.classList.add('hide');
        this.buttonRequestRefuse.classList.add('hide');
        this.buttonRequestNoResponse.classList.add('hide');
    }
}
