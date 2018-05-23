
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
    this.typesBlock.style = 'display: none;';
    this.duplicateButton.style = 'display: none;';
    this.buildEventsOptions();
};

DuplicationSheetsModal.prototype.buildEventsOptions = function()
{
    let options = '<option></option>';

    Object.values(this.typesByEventData).map(object => {
        options += `<option value="${object.id}">${object.title}</option>`;
    });

    let select = this.eventsBlock.querySelector('.form-control');
    select.addEventListener('change', this.buildTypesOptions.bind(this));
    select.innerHTML = options;
};

DuplicationSheetsModal.prototype.buildTypesOptions = function(event)
{
    let options = '<option></option>';

    Object.values(this.typesByEventData)
        .filter(object => {
            return object.id === parseInt(event.target.value);
        })
        .map(object => {
            object.types.map(type => {
                options += `<option value="${type.id}">${type.title}</option>`;
            })
        });

    let select = this.typesBlock.querySelector('#sheet_batch_duplicateToType');
    select.addEventListener('change', () => this.duplicateButton.style = 'display: inline-block;');
    select.innerHTML = options;

    this.typesBlock.style = 'display: block;';
};

module.exports = DuplicationSheetsModal;
