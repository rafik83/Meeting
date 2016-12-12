var $ = require('jquery');

function Happening(element)
{
    this.element = element;
    this.happeningParticipateIcon = element.querySelector('.happeningParticipateIcon');
    this.happeningParticipateAction = element.querySelector('.happeningParticipateAction');

    if (null !== this.happeningParticipateAction) {
        this.happeningParticipateAction.addEventListener('click', function (event) {
            event.preventDefault();
            this.onParticipate();
        }.bind(this), false);
    }
}

Happening.prototype.onParticipate = function ()
{
    this.happeningParticipateAction.disabled = true;
    this.happeningParticipateAction.classList.add('disabled');
    this.handleRequest();
};

Happening.prototype.handleRequest = function ()
{
    var href = this.happeningParticipateAction.getAttribute('href');

    $.get(href, function (response) {
        if ('error' === response.status) {
            alert(response.message);
            this.enableParticipateAction();
        } else {
            this.validateParticipation();
        }
    }.bind(this))
    .fail(function () {
        this.enableParticipateAction();
    }.bind(this));
};

Happening.prototype.validateParticipation = function () {
    this.happeningParticipateAction.classList.add('hide');
    this.happeningParticipateIcon.classList.remove('hide');
};

Happening.prototype.enableParticipateAction = function () {
    this.happeningParticipateAction.disabled = false;
    this.happeningParticipateAction.classList.remove('disabled');
};

module.exports = Happening;
