import axios from 'axios';

export default class Spool {
    constructor() {
        this.spool = JSON.parse(localStorage.getItem('spool')) || [];
    }

    consume() {
        let element = document.querySelector('#qrcode');

        this.spool
            .filter(spoolItem => {
                return !spoolItem.locked;
            })
            .map(spoolItem => {
                this.lock(spoolItem.identifier);

                axios
                    .post(element.dataset.scanEndpoint, spoolItem)
                    .then(() => { this.remove(spoolItem.identifier); })
                    .catch(() => { this.unlock(spoolItem.identifier); })
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

    remove(identifier) {
        this.spool = this.spool.filter(spoolItem => {
            return spoolItem.identifier !== identifier;
        });

        this.save();
    }

    setLock(identifier, locked) {
        this.spool = this.spool.map(spoolItem => {
            if (spoolItem.identifier === identifier) {
                spoolItem.locked = locked;
            }

            return spoolItem;
        });

        this.save();
    }

    lock(identifier) {
        this.setLock(identifier, true);
    }

    unlock(identifier) {
        this.setLock(identifier, false);
    }

    save() {
        localStorage.setItem('spool', JSON.stringify(this.spool));
    }
}

window.setInterval(() => {
    let spool = new Spool();
    spool.consume();
}, 200);
