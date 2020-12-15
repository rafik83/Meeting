import TypeFilterElement from './_CatalogMobileTypeFilterElement';
import AvailableSlotFilterElement from './_CatalogMobileAvailableSlotFilterElement';
import PubSub from 'pubsub-js';
import $ from 'jquery';

import 'select2';

/**
 * @param {HTMLElement} catalogFilterZone the zone where the filters will be displayed
 * @param {HTMLElement} catalogFilter the filter given by the response
 * @param {HTMLElement} catalogForm desktop form
 */
function CatalogMobileFilters(catalogFilterZone, catalogFilter, catalogForm) {
    this.catalogFilterZone = catalogFilterZone;
    this.originalCatalogFilter = catalogFilter;
    this.catalogFilter = this.originalCatalogFilter.cloneNode(true);
    this.catalogFilter.classList.remove('hidden');
    this.originalCatalogFilter.parentNode.removeChild(this.originalCatalogFilter);
    this.typeFilterList = this.catalogFilter.querySelector('.catalog-mobile-type-filter-list');
    this.availableSlotFilterList = this.catalogFilter.querySelector('.catalog-mobile-available-slot-filter-list');
    this.catalogForm = catalogForm;

    this.typeFilterCheckboxes = this.catalogForm.querySelectorAll('input[name="type[]"]');

    if (!this.typeFilterCheckboxes.length) {
        this.typeFilterCheckboxes = this.catalogForm.querySelectorAll('input[name="categories[]"]');
    }

    this.availableSlotRadio = this.catalogForm.querySelectorAll('input[name="availableSlot"]');
    this.contentInput = this.catalogForm.querySelector('input[name="content"]');
    this.contentFilterList = this.catalogFilter.querySelector('#catalog-mobile-filter-input-content');
    this.typeFilterButtons = [];
    this.availableSlotFilterButtons = [];

    if ((this.typeFilterList !== null && this.typeFilterCheckboxes.length)
        || (this.availableSlotFilterList !== null && this.availableSlotRadio !== null)
        || (this.contentFilterList !== null && this.contentInput !== null)
    ) {
        // Empty the catalogFilterZone of the placeholder and add the value from the catalogFilter twig
        this.catalogFilterZone.innerHTML = '';
        this.catalogFilterZone.appendChild(this.catalogFilter);

        // Handle custom events

        var removeContentElement = this.catalogFilter.querySelector('#content-filter-remove');

        if (removeContentElement !== null) {
            removeContentElement.addEventListener('click', this.handleRemoveContent.bind(this));
        }

        // Component events

        [].forEach.call(this.catalogFilterZone.querySelectorAll('[data-catalog-mobile-menu-button-action]'), function (element) {
            element.addEventListener('click', function (event) {
                event.preventDefault();

                this.openMenu(event);
            }.bind(this));
        }.bind(this));

        this.catalogFilterZone
            .querySelector('.catalog-close')
            .addEventListener('click', function (event) {
                event.preventDefault();

                this.closeMenu();
            }.bind(this));

        if (this.typeFilterList !== null && this.typeFilterCheckboxes !== null) {
            [].forEach.call(this.catalogFilterZone.querySelectorAll("[data-catalog-mobile-type-filter]"), function (element) {
                var filterButton = new TypeFilterElement(
                    element,
                    element.getAttribute('data-filter-id'),
                    element.getAttribute('data-content'),
                    element.getAttribute('data-count-participant')
                );

                this.typeFilterButtons.push(filterButton);

                if (filterButton.count > 0) {
                    filterButton.element.addEventListener('click', function (event) {
                        event.preventDefault();

                        this.onTypeFilterClick(filterButton);
                    }.bind(this));
                }
            }.bind(this))
        }

        if (this.availableSlotFilterList !== null && this.availableSlotRadio !== null) {
            [].forEach.call(this.catalogFilterZone.querySelectorAll("[data-catalog-mobile-available-slot-filter]"), function (element) {
                var filterButton = new AvailableSlotFilterElement(
                    element,
                    element.getAttribute('data-filter-id'),
                    element.getAttribute('data-content')
                );

                this.availableSlotFilterButtons.push(filterButton);

                filterButton.element.addEventListener('click', function (event) {
                    event.preventDefault();

                    this.onAvailableSlotFilterClick(filterButton);
                }.bind(this));
            }.bind(this))
        }
    }

    // Custom select2 events

    PubSub.publish('build.select2', this.catalogFilterZone);
    PubSub.subscribe('mobile-input-content-change', this.closeMenu.bind(this));
}

CatalogMobileFilters.prototype.openMenu = function (event) {
    if (!this.catalogFilter.classList.contains('disabled')) {
        document.body.classList.add('menu-mobile-opened');
        this.catalogFilterZone.querySelector('.catalog-mobile-menu-summary').style.display = 'none';
        this.catalogFilterZone.querySelector('.catalog-mobile-menu-filters').style.display = 'block';

        // autofocus content input
        if (event.currentTarget.getAttribute('data-catalog-mobile-menu-button-action')
            === 'catalog-mobile-menu-button-action-content-filter'
        ) {
            var inputContent = this.catalogFilterZone.querySelector('#catalog-mobile-filter-input-content');
            $(inputContent).select2('focus');
        }
    }
};

CatalogMobileFilters.prototype.closeMenu = function () {
    document.body.classList.remove('menu-mobile-opened');
    this.catalogFilterZone.querySelector('.catalog-mobile-menu-summary').style.display = 'block';
    this.catalogFilterZone.querySelector('.catalog-mobile-menu-filters').style.display = 'none';
};

CatalogMobileFilters.prototype.onTypeFilterClick = function (typeFilterElementClicked) {
    var buttonChecked = null;
    this.typeFilterButtons.forEach(function (typeFilterElement) {
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

    [].forEach.call(this.catalogFilter.querySelectorAll('[data-catalog-mobile-menu-button-action]'), function (button) {
        if (button.getAttribute('data-catalog-mobile-menu-button-action') === 'catalog-mobile-menu-button-action-type-filter') {
            this.rebuildActionButton(button, typeFilterElementClicked.content, typeFilterElementClicked.count);
        }
    }.bind(this));

    if (buttonChecked !== null) {
        this.dispatchChangeAndCloseMenu(buttonChecked);
    } else {
        this.dispatchChangeAndCloseMenu(this.typeFilterCheckboxes[0]);
    }
};

CatalogMobileFilters.prototype.onAvailableSlotFilterClick = function (availableSlotFilterElementClicked) {
    this.availableSlotFilterButtons.forEach(function (availableSlotFilterElement) {
        availableSlotFilterElement.inactive();
    });

    availableSlotFilterElementClicked.active();

    for (var i = 0; i < this.availableSlotRadio.length; i++) {
        this.availableSlotRadio[i].checked = this.availableSlotRadio[i].value === availableSlotFilterElementClicked.filterId;
    }

    [].forEach.call(this.catalogFilter.querySelectorAll('[data-catalog-mobile-menu-button-action]'), function (button) {
        if (button.getAttribute('data-catalog-mobile-menu-button-action') === 'catalog-mobile-menu-button-action-available-slot-filter') {
            this.rebuildActionButton(button, availableSlotFilterElementClicked.content);
        }
    }.bind(this));

    this.dispatchChangeAndCloseMenu(this.availableSlotRadio[0]);
};

CatalogMobileFilters.prototype.rebuildActionButton = function (button, content, count) {
    var countElement = '';

    if (typeof count !== 'undefined') {
        countElement = '(' + count + ')';
    }

    button.innerHTML =
        '<span class="button-count total-participants">' + countElement +
        ' <i class="glyphicon glyphicon-chevron-down"></i>' +
        '</span>' +
        '<span>' + content + ' </span>'
    ;
};

CatalogMobileFilters.prototype.dispatchChangeAndCloseMenu = function (button) {
    var event = document.createEvent("HTMLEvents");

    event.initEvent("change", false, true);
    button.dispatchEvent(event);

    this.closeMenu();
    [].forEach.call(this.catalogFilter.getElementsByTagName('A'), function (aElement) {
        aElement.classList.add('disabled');
    });
};

CatalogMobileFilters.prototype.handleRemoveContent = function (event) {
    event.stopPropagation();
    PubSub.publish('mobile-input-content-purge');
};

export default CatalogMobileFilters;
