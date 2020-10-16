'use strict';

import $ from "jquery";

export default class ChatVisio
{
    constructor (chat, buttonContainer) {
        const visioEnable = buttonContainer.getAttribute('data-visio-enable');
        const buttonVisio = buttonContainer.querySelector('button');
        if (visioEnable != 1){
            buttonVisio.classList.add("hide");
        }
    }

}
