
var PubSub = require('pubsub-js');

function AjaxForm(element, callback)
{
    this.element  = element;
    this.parent   = element.parentNode;
    this.callback = callback;
    this.element.addEventListener('submit', this.onSubmit.bind(this));
}

AjaxForm.prototype.onSubmit = function (event)
{
    event.preventDefault();

    var form   = new FormData(this.element);
    var xhr    = new XMLHttpRequest();
    xhr.onload = this.onLoaded.bind(this);
    xhr.open(this.element.getAttribute('method'), this.element.getAttribute('action'));
    xhr.send(form);
};

AjaxForm.prototype.onLoaded = function (event)
{
    this.parent.innerHTML = event.target.response;
    this.element          = this.parent.querySelector('form');

    PubSub.publish('dom.added', this.element);
};

module.exports = AjaxForm;
