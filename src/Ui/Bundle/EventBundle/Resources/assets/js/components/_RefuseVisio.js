'use strict';

import axios from "axios";

export default class RefuseVisio {
    constructor(buttonRefuse, callback) {
        this.buttonRefuse = buttonRefuse;
        this.buttonRefuse.addEventListener('click', this.refuseCall.bind(this));
        this.callback = callback;
    }

    refuseCall(event) {
        const url = this.buttonRefuse.getAttribute('data-url');
        axios.post(url).then((response) => {
            this.callback();
        });
    }

    setUrlRefuse(urlRefuse) {
        console.log(urlRefuse);
    }
}
