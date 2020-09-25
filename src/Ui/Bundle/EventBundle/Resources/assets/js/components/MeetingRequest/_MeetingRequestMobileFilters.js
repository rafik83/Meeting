import StateFilterElement from './_MeetingRequestMobileStateFilterElement';
import AvailableSlotFilterElement from './_MeetingRequestMobileAvailableSlotFilterElement';

/**
 * @param {HTMLElement} meetingRequestFilterZone the zone where the filters will be displayed
 * @param {HTMLElement} meetingRequestFilter the filter given by the response
 * @param {HTMLElement} meetingRequestForm
 */
function MeetingRequestMobileFilters(meetingRequestFilterZone, meetingRequestFilter, meetingRequestForm) {
    this.meetingRequestFilterZone = meetingRequestFilterZone;
    this.originalMeetingRequestFilter = meetingRequestFilter;
    this.meetingRequestFilter = this.originalMeetingRequestFilter.cloneNode(true);
    this.meetingRequestFilter.classList.remove('hidden');
    this.originalMeetingRequestFilter.parentNode.removeChild(this.originalMeetingRequestFilter);
    this.stateFilterList = this.meetingRequestFilter.querySelector('.catalog-mobile-state-filter-list');
    this.availableSlotFilterList = this.meetingRequestFilter.querySelector('.catalog-mobile-available-slot-filter-list');
    this.meetingRequestForm = meetingRequestForm;
    this.availableSlotRadio = this.meetingRequestForm.querySelectorAll('input[name="availableSlot"]');
    this.stateRadio = this.meetingRequestForm.querySelectorAll('input[name="state"]');
    this.availableSlotFilterButtons = [];
    this.stateFilterButtons = [];

    if ((this.stateFilterList !== null && this.stateRadio !== null)
        || (this.availableSlotFilterList !== null && this.availableSlotRadio !== null)
    ) {
        // Empty the catalogFilterZone of the placeholder and add the value from the catalogFilter twig
        this.meetingRequestFilterZone.innerHTML = '';
        this.meetingRequestFilterZone.appendChild(this.meetingRequestFilter);

        [].forEach.call(this.meetingRequestFilterZone.querySelectorAll('[data-catalog-mobile-menu-button-action]'), function (element) {
            element.addEventListener('click', function (event) {
                event.preventDefault();

                this.openMenu();
            }.bind(this));
        }.bind(this));

        this.meetingRequestFilterZone
            .querySelector('.catalog-close')
            .addEventListener('click', function (event) {
                event.preventDefault();

                this.closeMenu();
            }.bind(this));

        if (this.stateFilterList !== null && this.stateRadio !== null) {
            [].forEach.call(this.meetingRequestFilterZone.querySelectorAll("[data-catalog-mobile-state-filter]"), function (element) {
                if (element.classList.contains('disabled')) {
                    return false;
                }

                var filterButton = new StateFilterElement(
                    element,
                    element.getAttribute('data-filter-id'),
                    element.getAttribute('data-content'),
                    element.getAttribute('data-count')
                );

                this.stateFilterButtons.push(filterButton);

                filterButton.element.addEventListener('click', function (event) {
                    event.preventDefault();

                    this.onStateFilterClick(filterButton);
                }.bind(this));
            }.bind(this))
        }

        if (this.availableSlotFilterList !== null && this.availableSlotRadio !== null) {
            [].forEach.call(this.meetingRequestFilterZone.querySelectorAll("[data-catalog-mobile-available-slot-filter]"), function (element) {
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
}

MeetingRequestMobileFilters.prototype.openMenu = function () {
    if (!this.meetingRequestFilter.classList.contains('disabled')) {
        document.body.classList.add('menu-mobile-opened');
        this.meetingRequestFilterZone.querySelector('.catalog-mobile-menu-summary').style.display = 'none';
        this.meetingRequestFilterZone.querySelector('.catalog-mobile-menu-filters').style.display = 'block';
    }
};

MeetingRequestMobileFilters.prototype.closeMenu = function (){
    document.body.classList.remove('menu-mobile-opened');
    this.meetingRequestFilterZone.querySelector('.catalog-mobile-menu-summary').style.display = 'block';
    this.meetingRequestFilterZone.querySelector('.catalog-mobile-menu-filters').style.display = 'none';
};

MeetingRequestMobileFilters.prototype.onAvailableSlotFilterClick = function (availableSlotFilterElementClicked) {
    this.availableSlotFilterButtons.forEach(function (availableSlotFilterElement) {
        availableSlotFilterElement.inactive();
    });

    availableSlotFilterElementClicked.active();

    for (var i = 0; i < this.availableSlotRadio.length; i++) {
        this.availableSlotRadio[i].checked = this.availableSlotRadio[i].value === availableSlotFilterElementClicked.filterId;
    }

    [].forEach.call(this.meetingRequestFilter.querySelectorAll('[data-catalog-mobile-menu-button-action]'), function (button) {
        if (button.getAttribute('data-catalog-mobile-menu-button-action') === 'catalog-mobile-menu-button-action-available-slot-filter') {
            this.rebuildActionButton(button, availableSlotFilterElementClicked.content);
        }
    }.bind(this));

    this.dispatchChangeAndCloseMenu(this.availableSlotRadio[0]);
};

MeetingRequestMobileFilters.prototype.onStateFilterClick = function (stateFilterElementClicked) {
    this.stateFilterButtons.forEach(function (stateFilterElement) {
        stateFilterElement.inactive();
    });

    stateFilterElementClicked.active();

    for (var i = 0; i < this.stateRadio.length; i++) {
        this.stateRadio[i].checked = this.stateRadio[i].value === stateFilterElementClicked.filterId;
    }

    [].forEach.call(this.meetingRequestFilter.querySelectorAll('[data-catalog-mobile-menu-button-action]'), function (button) {
        if (button.getAttribute('data-catalog-mobile-menu-button-action') === 'catalog-mobile-menu-button-action-state-filter') {
            this.rebuildActionButton(button, stateFilterElementClicked.content, stateFilterElementClicked.count);
        }
    }.bind(this));

    this.dispatchChangeAndCloseMenu(this.stateRadio[0]);
};

MeetingRequestMobileFilters.prototype.rebuildActionButton = function (button, content, count) {
    var countElement = '';

    if (typeof count !== 'undefined') {
        countElement = '(' + count + ')';
    }

    button.innerHTML =
        '<span>' + content + ' </span><span class="button-count total-participants">' + countElement +
        ' <i class="glyphicon glyphicon-chevron-down"></i></span>'
    ;
};

MeetingRequestMobileFilters.prototype.dispatchChangeAndCloseMenu = function(button) {
    var event = document.createEvent("HTMLEvents");

    event.initEvent("change", false, true);
    button.dispatchEvent(event);

    this.closeMenu();
    [].forEach.call(this.meetingRequestFilter.getElementsByTagName('A'), function (aElement) {
        aElement.classList.add('disabled');
    });
};

export default MeetingRequestMobileFilters;
