import axios from 'axios';

export default class Spool {
    constructor() {
        this.spool = JSON.parse(localStorage.getItem('spool')) || [];
    }

    consume() {
        let element = document.querySelector('#qrcode');

        this.spool
            .filter(spool => {
                return !spool.locked;
            })
            .map((spool, key) => {
                this.lock(key);

                axios
                    .post(element.dataset.scanEndpoint, spool)
                    .then(() => { this.remove(key); })
                    .catch(() => { this.unlock(key); })
                ;
            })
        ;
    }

    add(identifier) {
        this.spool.push({
            identifier: identifier,
            scannedAt: new Date(),
            locked: false
        });
        this.save();
    }

    remove(key) {
        this.spool.splice(key, 1);
        this.save();
    }

    lock(key) {
        this.spool[key].locked = true;
        this.save();
    }

    unlock(key) {
        this.spool[key].locked = false;
        this.save();
    }

    save() {
        localStorage.setItem('spool', JSON.stringify(this.spool));
    }
}

window.setInterval(() => {
    let spool = new Spool();
    spool.consume();
}, 200);
