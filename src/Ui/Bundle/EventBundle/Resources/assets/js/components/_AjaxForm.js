
function AjaxForm(element)
{
    this.element = element;
    this.parent  = element.parentNode;
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

    init(this.element);
};
