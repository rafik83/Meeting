function ChoiceDescription(element) {
    this.element = element;
    this.target = document.querySelector(element.getAttribute('data-choice-description'));

    [].forEach.call(this.element.querySelectorAll('[data-description]'), function (input) {
        input.addEventListener('change', this.onChange.bind(this));
    }.bind(this));
}

ChoiceDescription.prototype.onChange = function (event) {
    var description = event.currentTarget.getAttribute('data-description');

    if ('' != description) {
        this.target.innerHTML = description;
        this.target.classList.remove('hide');

        return;
    }

    this.target.classList.add('hide');
};

export default ChoiceDescription;
