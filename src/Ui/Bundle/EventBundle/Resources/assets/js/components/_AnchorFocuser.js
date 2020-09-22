import $ from 'jquery';

function AnchorFocuser(element, anchor)
{
    this.element = element;
    this.anchor  = anchor;
    this.input   = null;

    this.identifyField();
}

AnchorFocuser.prototype.identifyField = function ()
{
    var formGroup = $(this.element).next('.form-group');
    var inputs    = $(formGroup).find(':input');

    if (inputs.length > 0) {
        this.input = inputs[0];
        this.focusField();
    }
};

AnchorFocuser.prototype.focusField = function ()
{
    if (this.input !== null) {
        setTimeout(function() {
            $(this.input).focus();

            // In case of select2, you have to open it in order to focus the field
            if (this.input.tagName === 'SELECT' && this.input.classList.contains('select2')) {
                $(this.input).select2('open');
            }
        }.bind(this), 100);
    }
};

export default AnchorFocuser;
