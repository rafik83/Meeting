var $ = require('jquery'),
    axios = require('axios'),
    DateTimeManipulation = require('./_DateTimeManipulation')
;

function AssignAccommodationStay(element)
{
    this.element = element;
    this.roommatePlaceholder = element.getAttribute('data-roommate-placeholder');
    this.url = element.getAttribute('data-availability-url');
    this.accommodationInput = element.querySelector('[id="admin_assign_accommodation_type_accommodation"]');
    this.roommateInput = element.querySelector('[id="admin_assign_accommodation_type_roommate"]');
    this.roommateBlock = element.querySelector('[id="admin_assign_accommodation_type_roommate-group"]');

    this.dateTimeManipulation = new DateTimeManipulation();

    [].forEach.call(element.querySelectorAll('input[name="admin_assign_accommodation_type[roomType]"]'), function (roomType) {
        roomType.addEventListener('change', this.onSelectRoomType.bind(this));
    }.bind(this));

    this.arrivalDateInput = element.querySelector('[id="admin_assign_accommodation_type_arrival"]');
    this.departureDateInput = element.querySelector('[id="admin_assign_accommodation_type_departure"]');

    $('#admin_assign_accommodation_type_arrival').on('dp.change', this.onChangePeriod.bind(this));
    $('#admin_assign_accommodation_type_departure').on('dp.change', this.onChangePeriod.bind(this));

    this.init();
}

AssignAccommodationStay.prototype.init = function() {
    const roomType = this.element.querySelector('input[name="admin_assign_accommodation_type[roomType]"]:checked').value;
    this.displayRoommate(this.isRoomTypeDouble(roomType));
};

AssignAccommodationStay.prototype.isRoomTypeDouble = function(roomType) {
    return 'double' === roomType;
};

AssignAccommodationStay.prototype.displayRoommate = function(state) {
    this.roommateBlock.style.display = false === state ? 'none' : 'block';
};

AssignAccommodationStay.prototype.onSelectRoomType = function(e) {
    this.displayRoommate(this.isRoomTypeDouble(e.target.value));
};

AssignAccommodationStay.prototype.onChangePeriod = function(e) {
    const arrivalString = this.arrivalDateInput.value;
    const departureString = this.departureDateInput.value;

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

module.exports = AssignAccommodationStay;
