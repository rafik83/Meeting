export default class Modal {
    init(element) {
        this.element = element;
        const closeButtons = this.element.querySelectorAll('[data-modal-close]');
        closeButtons.forEach((closeButton) => {
            closeButton.addEventListener('click', () => this.element.classList.add('hide'));
        });
    }

    show() {
        this.element.classList.remove('hide');
    }

    /**
     * Hide modal after a delay
     * @param {Number} delay in ms
     */
    hideAfter(delay) {
        if (this.timerId) {
            clearTimeout(this.timerId);
        }

        this.timerId = setTimeout(() => this.element.classList.add('hide'), delay);
    }
}
