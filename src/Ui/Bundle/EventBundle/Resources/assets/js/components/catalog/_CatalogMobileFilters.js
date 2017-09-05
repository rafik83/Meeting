var TypeFilterElement = require('./_CatalogMobileTypeFilterElement');

function CatalogMobileFilters(document, element, typeFilterList, typeFilterButton, catalogForm) {
    this.document = document;
    this.element = element;
    this.typeFilterList = typeFilterList;
    this.typeFilterButton = typeFilterButton;
    this.catalogForm = catalogForm;
    this.typeFilterCheckboxes = this.catalogForm.querySelectorAll('input[name="type[]"]');
    this.typeFilters = [];
    this.typeFilterAll = null;
    this.countTotal = 0;

    if (this.typeFilterButton !== null && this.typeFilterCheckboxes !== null) {
        var typeSelected = [];
        [].forEach.call(this.typeFilterCheckboxes, function (checkboxType) {
            if (checkboxType.checked) {
                typeSelected.push(checkboxType);
            }

            var filterId = checkboxType.value;
            var content = checkboxType.nextSibling.nodeValue;
            var count = parseInt(checkboxType.parentNode.nextElementSibling.innerText);
            this.countTotal += count;

            var filterElement = new TypeFilterElement(
                this.document,
                checkboxType,
                filterId,
                content,
                count
            );
            var listNode = filterElement.getListNode();

            this.typeFilters.push(filterElement);

            this.typeFilterList.appendChild(listNode);

            listNode.addEventListener('click', function (event) {
                this.onTypeFilterClick(filterElement);
            }.bind(this));
        }.bind(this));

        // Build filter all button
        this.typeFilterAll = new TypeFilterElement(
            this.document,
            null,
            'all',
            this.typeFilterList.getAttribute('data-type-filter-all-content'),
            this.countTotal
        );

        if (typeSelected.length === this.typeFilterCheckboxes.length) {
            this.typeFilterAll.active();
            this.typeFilters.forEach(function (element) {
                element.inactive();
            });
        }

        this.typeFilters.push(this.typeFilterAll);
        var listNodeAll = this.typeFilterAll.getListNode();
        this.typeFilterList.insertBefore(listNodeAll, this.typeFilterList.firstChild);

        listNodeAll.addEventListener('click', function (event) {
            this.onTypeFilterClick(this.typeFilterAll);
        }.bind(this));

        // Change filter type button by the type selected
        var filterSelected = [].filter.call(this.typeFilterCheckboxes, function (element) {
            return element.checked === true;
        });

        if (filterSelected.length === this.typeFilterCheckboxes.length) {
            this.fillTypeFilterButton(this.typeFilterAll.content, this.typeFilterAll.count);
            this.typeFilterAll.active();
        } else {
            this.typeFilters.forEach(function (element) {
                if (element.filterId === filterSelected[0].value) {
                    this.fillTypeFilterButton(element.content, element.count);
                    element.active();
                }
            }.bind(this))
        }
    }
}

CatalogMobileFilters.prototype.onTypeFilterClick = function(typeFilterElementClicked) {
    var buttonChecked = null;
    this.typeFilters.forEach(function (typeFilterElement) {
        typeFilterElement.inactive();
    });

    typeFilterElementClicked.active();

    for (var i = 0; i < this.typeFilterCheckboxes.length; i++) {
        if (typeFilterElementClicked.filterId === 'all') {
            this.typeFilterCheckboxes[i].checked = true;

            continue;
        }

        this.typeFilterCheckboxes[i].checked = this.typeFilterCheckboxes[i].value === typeFilterElementClicked.filterId;

        if (this.typeFilterCheckboxes[i].checked) {
            buttonChecked = this.typeFilterCheckboxes[i];
        }
    }

    if (buttonChecked !== null) {
        this.dispatchChange(buttonChecked);
    } else {
        this.dispatchChange(this.typeFilterCheckboxes[0]);
    }

    this.fillTypeFilterButton(typeFilterElementClicked.content, typeFilterElementClicked.count);
};

CatalogMobileFilters.prototype.fillTypeFilterButton = function (content, count) {
    this.typeFilterButton.innerHTML =
        '<span>' + content + ' </span><span class="button-count total-participants">(' + count + ')' +
        ' <i class="glyphicon glyphicon-chevron-down"></i></span>'
    ;
};

CatalogMobileFilters.prototype.dispatchChange = function(button) {
    var event = document.createEvent("HTMLEvents");

    event.initEvent("change", false, true);
    button.dispatchEvent(event);
};

module.exports = CatalogMobileFilters;
