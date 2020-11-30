'use strict';

import Counter from "./Counter";

class WebinarStatus {
    /**
     * @param {Element} element
     * @param {boolean} isSpeaker
     */
    constructor(element, isSpeaker) {
        this.timerElement = element.querySelector('.timer-container');
        this.currentElement = element.querySelector('.current-container');
        this.endElement = element.querySelector('.end-container');
        this.countDownContainer = this.timerElement.querySelector('.timer span.countdown');

        this.timeRemainingBeforeStart = parseInt(element.getAttribute('data-time-remaining-before-start'), 10);
        this.timeRemaining = parseInt(element.getAttribute('data-time-remaining'), 10);
        this.warningRemainingTime = parseInt(element.getAttribute('data-warning-time-remaining'), 10);

        const startTime = new Date(new Date().getTime() + this.timeRemainingBeforeStart * 1000);
        const endTime = new Date(new Date().getTime() + this.timeRemaining * 1000);

        // before start counter
        new Counter(this.timeRemainingBeforeStart, this.warningRemainingTime, this.countDownContainer, this.timerElement);

        // show one container at a time (3 possible)
        const remainingTimeBeforeStart = Math.round((startTime.getTime() - new Date().getTime()) / 1000);
        const remainingTimeBeforeEnd = Math.round((endTime.getTime() - new Date().getTime()) / 1000);

        if (remainingTimeBeforeStart > 0) {
            this.setNotStarted();

            setTimeout(() => {
                this.setIsRunning();
            }, remainingTimeBeforeStart * 1000);

            setTimeout(() => {
                this.setEnded();
            }, remainingTimeBeforeEnd * 1000);
        } else {
            if (remainingTimeBeforeEnd > 0) {
                this.setIsRunning();

                setTimeout(() => {
                    this.setEnded();
                }, remainingTimeBeforeEnd * 1000);
            } else {
                this.setEnded();
            }
        }
    }

    setNotStarted() {
        this.timerElement.classList.remove('hide');
        this.currentElement.classList.add('hide');
        this.endElement.classList.add('hide');
    }

    setIsRunning() {
        this.timerElement.classList.add('hide');
        this.currentElement.classList.remove('hide');
        this.endElement.classList.add('hide');
    }

    setEnded() {
        this.timerElement.classList.add('hide');
        this.currentElement.classList.add('hide');
        this.endElement.classList.remove('hide');
    }
}

export default WebinarStatus;
