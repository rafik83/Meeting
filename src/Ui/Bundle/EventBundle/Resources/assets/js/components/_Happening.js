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

    $.get(href, function() {
        this.happeningParticipateAction.classList.add('hide');
        this.happeningParticipateIcon.classList.remove('hide');
    }.bind(this));
};

module.exports = Happening;
