var axio = require('axios'),
    querystring = require('querystring');

function ParticipantVisio(element)
{
    this.element = element;
    this.input = this.element.querySelector('input[name="is-visio"]');
    this.action = this.element.dataset.action;

    this.input.addEventListener('change', this.onChange.bind(this));
}

ParticipantVisio.prototype.onChange = function ()
{
    var data = this.input.checked;

    axio.post(this.action,
        querystring.stringify({
            isVisio: data
        })
    )
    .then(function (response) {})
    .catch(function (error) {
        alert(error);
    });
};

module.exports = ParticipantVisio;
