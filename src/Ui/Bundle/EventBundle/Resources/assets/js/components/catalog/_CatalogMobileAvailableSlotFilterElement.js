function CatalogMobileAvailableSlotFilterElement(element, filterId, content) {
    this.element = element;
    this.filterId = filterId;
    this.content = content;
}

CatalogMobileAvailableSlotFilterElement.prototype.inactive = function() {
    this.element.getElementsByTagName('a')[0].classList.add('btn-inactive');
};

CatalogMobileAvailableSlotFilterElement.prototype.active = function() {
    this.element.getElementsByTagName('a')[0].classList.remove('btn-inactive');
};

export default CatalogMobileAvailableSlotFilterElement;
