'use strict';

function Counter(startTime, endTime, currentTime, countDownContainer, timerContainer) {
    if (!countDownContainer) {
        return;
    }

    this.countDownContainer = countDownContainer;
    this.timerContainer = timerContainer;

    const _this = this;

    const endTimeValue = parseInt(endTime);
    const startTimeValue = parseInt(startTime);
    let currentTimeValue = parseInt(currentTime);

    const totalTime = endTimeValue - startTimeValue;
    const warningTime = Math.floor(totalTime * 0.8);
    let remainingTime = endTimeValue - currentTimeValue;

    let seconds = Math.floor(remainingTime % 60);
    let minutes = Math.floor((remainingTime / 60) % 60);
    let hours = Math.floor((remainingTime / (60 * 60)) % 24);

    if (hours > 0) {
        minutes += hours * 60;
    }

    var timerInterval = setInterval(function () {
        if (remainingTime <= 0) {
            _this.timerContainer.classList.add('warning');
            _this.countDownContainer.innerHTML = `00:00`;
            clearInterval(timerInterval);

            return;
        }

        currentTimeValue++;
        remainingTime--;

        if (currentTimeValue >= (startTimeValue + warningTime)) {
            _this.timerContainer.classList.add('warning');
        }

        if (0 === parseInt(seconds)) {
            seconds = 59;
            minutes--;
        } else {
            seconds--;
        }

        if (seconds < 10) {
            seconds = '0' + seconds;
        }

        _this.countDownContainer.innerHTML = `${minutes}:${seconds}`;
    }, 1000);
}

module.exports = Counter;
