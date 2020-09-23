import $ from 'jquery';
import axios from 'axios';
import DateTimeManipulation from "./_DateTimeManipulation";

function AssignAccommodationStay(element)
{
    this.element = element;
    this.roommatePlaceholder = element.getAttribute('data-roommate-placeholder');
    this.url = element.getAttribute('data-availability-url');
    this.sheetsUrl = element.getAttribute('data-sheets-url');
    this.accommodationInput = element.querySelector('[id="admin_assign_accommodation_type_accommodation"]');
    this.roommateInput = element.querySelector('[id="admin_assign_accommodation_type_roommate"]');
    this.roommateBlock = element.querySelector('[id="admin_assign_accommodation_type_roommate-group"]');
    this.otherSheetsListInput = element.querySelector('[id="admin_assign_accommodation_type_otherSheet');
    this.otherSheetsButtonBlock = element.querySelector('[id="admin_assign_accommodation_type_displayOtherSheet-group"]');
    this.otherSheetsListBlock = element.querySelector('[id="admin_assign_accommodation_type_otherSheet-group"]');
    this.showOtherSheetsList = this.otherSheetsListInput.value !== '';

    this.dateTimeManipulation = new DateTimeManipulation();

    [].forEach.call(element.querySelectorAll('input[name="admin_assign_accommodation_type[roomType]"]'), function (roomType) {
        roomType.addEventListener('change', this.onSelectRoomType.bind(this));
    }.bind(this));

    this.arrivalDateInput = element.querySelector('[id="admin_assign_accommodation_type_arrival"]');
    this.departureDateInput = element.querySelector('[id="admin_assign_accommodation_type_departure"]');

    $('#admin_assign_accommodation_type_arrival').on('dp.change', this.onChangePeriod.bind(this));
    $('#admin_assign_accommodation_type_departure').on('dp.change', this.onChangePeriod.bind(this));
    $('#admin_assign_accommodation_type_otherSheet').on('change', this.onChangePeriod.bind(this));
    $('#admin_assign_accommodation_type_displayOtherSheet').on('click', this.otherSheetsAsked.bind(this));

    this.init();
}

AssignAccommodationStay.prototype.init = function() {
    const roomType = this.element.querySelector('input[name="admin_assign_accommodation_type[roomType]"]:checked').value;
    this.displayRoommateManagement(this.isRoomTypeDoubleOrTwin(roomType));
};

AssignAccommodationStay.prototype.isRoomTypeDoubleOrTwin = function(roomType) {
    return 'double' === roomType || 'twin' === roomType;
};

AssignAccommodationStay.prototype.displayRoommateManagement = function(state) {
    let roommateBlockDisplay = false === state ? 'none' : 'block';
    let otherSheetsButtonBlockDisplay = state && false === this.showOtherSheetsList ? 'block' : 'none';
    let otherSheetsListBlockDisplay = state && this.showOtherSheetsList ? 'block' : 'none';

    this.roommateBlock.style.display = roommateBlockDisplay;
    this.otherSheetsButtonBlock.style.display = otherSheetsButtonBlockDisplay;
    this.otherSheetsListBlock.style.display = otherSheetsListBlockDisplay;
};

AssignAccommodationStay.prototype.onSelectRoomType = function(e) {
    this.displayRoommateManagement(this.isRoomTypeDoubleOrTwin(e.target.value));
};

AssignAccommodationStay.prototype.otherSheetsAsked = function () {
    this.showOtherSheetsList = true;
    this.displayRoommateManagement(true);

    this.otherSheetsListInput.setAttribute('disabled','disabled');
    axios.get(this.sheetsUrl).then(function (response) {
        Object.entries(response.data.sheets).forEach(
            ([key, object]) => {
                const option = document.createElement('option');
                option.text = object.title;
                option.value = object.id;
                this.otherSheetsListInput.add(option);
            }
        );

        this.otherSheetsListInput.removeAttribute('disabled');
    }.bind(this)).catch(alert);
};

AssignAccommodationStay.prototype.onChangePeriod = function(e) {
    const arrivalString = this.arrivalDateInput.value;
    const departureString = this.departureDateInput.value;
    const sheetIdString = this.otherSheetsListInput.value;

    const accommodationInput = $(this.accommodationInput);
    accommodationInput.empty();
    accommodationInput.attr('disabled','disabled');

    const roommateInput = $(this.roommateInput);
    roommateInput.empty();
    roommateInput.attr('disabled','disabled');

    const arrivalDate = this.dateTimeManipulation.getTimestampByInternationalFormat(arrivalString);
    const departureDate = this.dateTimeManipulation.getTimestampByInternationalFormat(departureString);

    if (arrivalDate >= departureDate) {
        return;
    }

    // rewrite form action
    const action = this.element.action.split('?');
    this.element.action = action[0] + '?arrivalDate=' + arrivalString + '&departureDate=' + departureString;

    axios.get(this.url, {
        params: {
            arrivalDate: arrivalString,
            departureDate: departureString,
            sheetId: sheetIdString
        }
    }).then(function (response) {
        Object.entries(response.data.accommodations).forEach(
            ([id, label]) => accommodationInput.append(`<option value="${id}">${label}</option>`)
        );

        roommateInput.append(`<option value="">${this.roommatePlaceholder}</option>`);
        Object.entries(response.data.roommates).forEach(
            ([id, object]) => roommateInput.append(`<option value="${id}" ${object.disabled ? 'disabled="disabled"': ''}">${object.label}</option>`)
        );

        accommodationInput.removeAttr('disabled');
        roommateInput.removeAttr('disabled');
    }.bind(this)).catch(alert);
};

export default AssignAccommodationStay;
