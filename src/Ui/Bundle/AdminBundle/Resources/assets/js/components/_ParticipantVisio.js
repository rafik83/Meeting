import axios from 'axios';
import querystring from 'querystring';

function ParticipantVisio(element)
{
    this.element = element;
    this.input = this.element.querySelector('input[name="is-visio"]');
    this.action = this.element.action;

    this.input.addEventListener('change', this.onChange.bind(this));
}

ParticipantVisio.prototype.onChange = function ()
{
    var data = this.input.checked;

    axios.post(this.action,
        querystring.stringify({
            isVisio: data
        })
    )
    .then(function (response) {})
    .catch(function (error) {
        alert(error);
    });
};

export default ParticipantVisio;
