function Toggle(element, labels) {
    this.labels = labels || Toggle.defaultLabels;
    this.element = element;
    this.element.style.display = 'none';
    this.leftItemChecked = this.element.checked;
    this.disabled = this.element.getAttribute('disabled');

    this.element.parentNode.insertAdjacentHTML('beforeend', this.getTemplate());
    $(this.element.parentNode).find('[data-toggle="tooltip"]').tooltip();

    this.yesElement = this.element.parentNode.querySelectorAll('.toggleYes')[0];
    this.noElement = this.element.parentNode.querySelectorAll('.toggleNo')[0];
    this.yesElement.onclick = this.onYesClick.bind(this);
    this.noElement.onclick = this.onNoClick.bind(this);
}

Toggle.prototype.getTemplate = function () {
    return '<div ' + (this.disabled ? 'data-toggle="tooltip" title="' + this.labels.disabled + '"' : '') + ' class="btn-group agenda-slotToggle" role="group">' +
        '<div ' + (!this.disabled ? 'data-toggle="tooltip" title="' + this.labels.yes + '"' : '') + ' class="toggleYes btn ' + (this.leftItemChecked ? 'btn-success active' : 'btn-default') + '" ' + (this.disabled ? 'disabled="disabled"' : '') + '>' +
        '<i class="glyphicon glyphicon-ok"></i>' +
        '</div>' +
        '<div ' + (!this.disabled ? 'data-toggle="tooltip" title="' + this.labels.no + '"' : '') + ' class="toggleNo btn ' + (this.leftItemChecked ? 'btn-default' : 'btn-danger active') + '" ' + (this.disabled ? 'disabled="disabled"' : '') + '>' +
        '<i class="glyphicon glyphicon-lock"></i>' +
        '</div>' +
        '</div>';
};

Toggle.prototype.onYesClick = function () {
    if (this.disabled) {
        return;
    }

    this.selectYes();

    if (this.leftItemChecked === false) {
        this.checkElement();
    }
    this.leftItemChecked = true;
};

Toggle.prototype.onNoClick = function () {
    if (this.disabled) {
        return;
    }

    this.selectNo();

    if (this.leftItemChecked) {
        this.uncheckElement();
    }
    this.leftItemChecked = false;
};

Toggle.prototype.checkElement = function () {
    this.element.checked = true;
    this.element.onchange();
};

Toggle.prototype.uncheckElement = function () {
    this.element.checked = false;
    this.element.onchange();
};

Toggle.prototype.selectYes = function () {
    this.yesElement.classList.add('btn-success');
    this.yesElement.classList.add('active');
    this.yesElement.classList.remove('btn-default');

    this.noElement.classList.remove('btn-danger');
    this.noElement.classList.remove('active');
    this.noElement.classList.add('btn-default');
};

Toggle.prototype.selectNo = function () {
    this.yesElement.classList.remove('btn-success');
    this.yesElement.classList.remove('active');
    this.yesElement.classList.add('btn-default');

    this.noElement.classList.add('btn-danger');
    this.noElement.classList.add('active');
    this.noElement.classList.remove('btn-default');
};

Toggle.prototype.refresh = function () {
    if (this.element.checked) {
        this.selectYes();
        this.leftItemChecked = true;
    } else {
        this.selectNo();
        this.leftItemChecked = false;
    }
};

Toggle.defaultLabels = {disabled: 'disabled', yes: 'yes', no: 'no'};

export default Toggle;
