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
            this.buttonVisio.classList.remove("hide");
        }
    }

    onRequestVisio(event) {
        const url = this.buttonVisio.getAttribute('data-url');
        axios.post(url).then((response) => {
            this.buttonVisio.classList.add("hide");
            this.buttonRequestPending.classList.remove("hide");
            this.timerId = setTimeout(() => {
                this.buttonRequestPending.classList.add("hide");
                this.buttonRequestNoResponse.classList.remove("hide");
            }, 40000);
        });
    }

    onRefuseVisio() {
        this.buttonRequestPending.classList.add('hide');
        this.buttonRequestRefuse.classList.remove('hide');
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

    showJoinVisioButton(urlAccept) {
        this.buttonJoin.classList.remove('hide');
        this.buttonVisio.classList.add('hide');
        this.buttonRequestPending.classList.add('hide');
        clearTimeout(this.timerId);
        this.urlAccept = urlAccept;
    }

    busyVisio() {
        this.buttonVisio.classList.add("hide");
        this.buttonRequestPending.classList.remove('hide');
    }
}
