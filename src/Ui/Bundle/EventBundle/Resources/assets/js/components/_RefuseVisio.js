'use strict';

import axios from "axios";

export default class RefuseVisio {
    constructor(buttonRefuse, callback) {
        this.buttonRefuse = buttonRefuse;
        this.buttonRefuse.addEventListener('click', this.refuseCall.bind(this));
        this.callback = callback;
    }

    refuseCall(event) {
        axios.post(this.urlRefuse).then((response) => {
            this.callback();
        });
    }

    setUrlRefuse(urlRefuse) {
        this.urlRefuse = urlRefuse;
    }
}
