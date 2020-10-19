'use strict';

import $ from "jquery";
import axios from "axios";

export default class ChatVisio
{
    constructor (chat, buttonContainer) {
        this.visioEnable = buttonContainer.getAttribute('data-visio-enable');
        this.buttonVisio = buttonContainer.querySelector('.state-normal');
        this.buttonRequestPending = buttonContainer.querySelector('.state-pending');
        this.buttonVisio.classList.add("hide");
        this.buttonVisio.addEventListener('click', this.onRequestVisio.bind(this));
    }

    onMessagesReceived(messages) {
        if (messages.some((message)=> !message.isAuthor)) {
            this.buttonVisio.classList.remove("hide");
        }
    }

    onRequestVisio(event) {
        const url = this.buttonVisio.getAttribute('data-url');
        axios.post(url).then((response) => {
            this.buttonVisio.classList.add("hide");
            this.buttonRequestPending.classList.remove("hide");
        });
    }
}
