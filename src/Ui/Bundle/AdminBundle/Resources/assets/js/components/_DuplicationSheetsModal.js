
function DuplicationSheetsModal(element, modal)
{
    this.element = element;
    this.modal = modal;
    this.typesBlock = modal.querySelector('#types-block');
    this.eventsBlock = modal.querySelector('#events-block');
    this.typesByEventData = JSON.parse(this.element.getAttribute('data-types'));
    this.duplicateButton = this.modal.querySelector('#sheet_batch_duplicate');

    this.element.addEventListener('click', this.onOpeningModal.bind(this));
}

DuplicationSheetsModal.prototype.onOpeningModal = function()
{
    this.hideTypesBlock();
    this.hideDuplicateButton();
    this.buildEventsOptions();
};

DuplicationSheetsModal.prototype.buildEventsOptions = function()
{
    var options = '<option></option>';

    Object.values(this.typesByEventData).map(function(object) {
        options += '<option value="' + object.id + '">' + object.title + '</option>';
    });

    var select = this.eventsBlock.querySelector('.form-control');
    select.addEventListener('change', this.buildTypesOptions.bind(this));
    select.innerHTML = options;
};

DuplicationSheetsModal.prototype.buildTypesOptions = function(event)
{
    var options = '<option></option>';

    Object.values(this.typesByEventData)
        .filter(function(object) {
            return object.id === parseInt(event.target.value);
        })
        .map(function(object) {
            object.types.map(function(type) {
                options += '<option value="' + type.id + '">' + type.title + '</option>';
            })
        });

    var select = this.typesBlock.querySelector('#sheet_batch_duplicateToType');
    select.addEventListener('change', this.displayDuplicateButton.bind(this));
    select.innerHTML = options;

    this.displayTypesBlock();
};

DuplicationSheetsModal.prototype.displayTypesBlock = function() {
    this.typesBlock.style = 'display: block;';
};

DuplicationSheetsModal.prototype.hideTypesBlock = function() {
    this.typesBlock.style = 'display: none;';
};

DuplicationSheetsModal.prototype.displayDuplicateButton = function() {
    this.duplicateButton.style = 'display: inline-block;'
};

DuplicationSheetsModal.prototype.hideDuplicateButton = function() {
    this.duplicateButton.style = 'display: none;'
};

export default DuplicationSheetsModal;
