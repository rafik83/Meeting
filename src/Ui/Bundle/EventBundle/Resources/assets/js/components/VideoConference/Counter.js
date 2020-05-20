'use strict';

function Counter(
    timeRemaining,
    warningRemainingTime,
    countDownContainer,
    timerContainer,
    countDownEndCallback = null
) {
    if (!countDownContainer) {
        return;
    }

    const endTime = new Date(new Date().getTime() + timeRemaining * 1000);

    const timerInterval = setInterval(() => {
        const remainingTime = Math.round((endTime.getTime() - new Date().getTime()) / 1000);

        if (remainingTime <= 0) {
            timerContainer.classList.add('warning');
            countDownContainer.innerHTML = `00:00`;
            clearInterval(timerInterval);

            if (countDownEndCallback) {
                countDownEndCallback();
            }

            return;
        }

        if (remainingTime <= warningRemainingTime) {
            timerContainer.classList.add('warning');
        }

        const hours = Math.floor((remainingTime / (60 * 60)) % 24);
        const minutes = hours * 60 + Math.floor((remainingTime / 60) % 60);
        const seconds = Math.floor(remainingTime % 60);

        countDownContainer.innerHTML = `${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
    }, 500);
}

module.exports = Counter;
