function FilterByType(element, buttonsZone)
{
    // constant
    this.requestConstant     = 'request';
    this.propositionConstant = 'proposition';

    this.filterByAllConstant = 'all';
    this.filterByRequestsConstant = 'requests';
    this.filterByPropositionsConstant = 'propositions';

    this.element      = element;
    this.buttonsZone  = buttonsZone;
    this.buttons      = [];
    this.requests     = [];
    this.propositions = [];

    [].forEach.call(this.element.querySelectorAll('[data-meeting-request]'), function (element) {
        var attribute = element.getAttribute('data-meeting-request');

        if (attribute === this.requestConstant) {
            this.requests.push(element);
        } else if (attribute === this.propositionConstant) {
            this.propositions.push(element);
        }
    }.bind(this));

    [].forEach.call(this.buttonsZone.querySelectorAll('button'), function (element) {
        var attribute = element.getAttribute('data-meeting-request-filter-by');

        if (attribute === this.filterByAllConstant
            || attribute === this.filterByRequestsConstant
            || attribute === this.filterByPropositionsConstant
        ) {
            if (attribute === this.filterByRequestsConstant) {
                this.addContentToButton(element, '(' + this.requests.length + ')');

                if (this.requests.length === 0) {
                    element.disabled = true;
                }
            }

            if (attribute === this.filterByPropositionsConstant) {
                this.addContentToButton(element, '(' + this.propositions.length + ')');

                if (this.propositions.length === 0) {
                    element.disabled = true;
                }
            }

            if (element.disabled !== true) {
                element.addEventListener('click', function () {
                    this.filterByType(attribute, element);
                }.bind(this));
            }
        }

        this.buttons.push(element);
    }.bind(this));
}

FilterByType.prototype.addContentToButton = function (element, content)
{
    element.innerHTML = element.innerHTML + ' ' + content;
};

FilterByType.prototype.filterByType = function (type, buttonClicked)
{
    this.buttons.forEach(function (element) {
        element.classList.remove('btn-primary');
        element.classList.add('btn-default');
    });

    buttonClicked.classList.add('btn-primary');

    if (type === this.filterByAllConstant) {
        this.requests.forEach(function (element) {
            this.showElement(element);
        }.bind(this));
        this.propositions.forEach(function (element) {
            this.showElement(element);
        }.bind(this));
    } else if (type === this.filterByRequestsConstant) {
        this.requests.forEach(function (element) {
            this.showElement(element);
        }.bind(this));
        this.propositions.forEach(function (element) {
            this.hideElement(element);
        }.bind(this));
    } else if (type === this.filterByPropositionsConstant) {
        this.requests.forEach(function (element) {
            this.hideElement(element);
        }.bind(this));
        this.propositions.forEach(function (element) {
            this.showElement(element);
        }.bind(this));
    }
};

FilterByType.prototype.hideElement = function (element)
{
    element.style.display = 'none';
};

FilterByType.prototype.showElement = function (element)
{
    element.style.display = 'flex';
};

export default FilterByType;
