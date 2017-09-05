function CatalogMobileTypeFilterElement(document, element, filterId, content, count) {
    this.document = document;
    this.element = element;
    this.filterId = filterId;
    this.content = content;
    this.count = count;
    this.nodeListButton = this.document.createElement('li');
    this.nodeListButton.classList.add('catalog-mobile-filter', 'btn', 'btn-primary', 'btn-inactive');

    if (this.element !== null && this.element.checked === true) {
        this.nodeListButton.classList.remove('btn-inactive');
    }

    this.spanContent = this.document.createElement('span');
    this.spanContent.classList.add('col-xs-10', 'filter-content');
    this.spanContent.appendChild(this.document.createTextNode(this.content));

    this.spanCount = this.document.createElement('span');
    this.spanCount.classList.add('col-xs-2', 'filter-count', 'text-right');
    this.spanCount.appendChild(this.document.createTextNode('(' + this.count + ')'));

    this.nodeListButton.appendChild(this.spanContent);
    this.nodeListButton.appendChild(this.spanCount);
}

CatalogMobileTypeFilterElement.prototype.inactive = function() {
    this.nodeListButton.classList.add('btn-inactive');
};

CatalogMobileTypeFilterElement.prototype.active = function() {
    this.nodeListButton.classList.remove('btn-inactive');
};

CatalogMobileTypeFilterElement.prototype.changeCount = function (count) {
    this.count = count;
    this.spanCount.innerHTML = '(' + count + ')';
};

CatalogMobileTypeFilterElement.prototype.getListNode = function () {
    return this.nodeListButton;
};

module.exports = CatalogMobileTypeFilterElement;
