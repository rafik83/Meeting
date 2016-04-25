
var PubSub = require('pubsub-js');

function AjaxForm(element, callback)
{
    this.element  = element;
    this.target   = document.querySelector(element.getAttribute('data-ajax-form'));
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
    this.target.innerHTML = event.target.response;
    this.element          = this.target.querySelector('form');

    PubSub.publish('dom.added', this.target);
};

module.exports = AjaxForm;
