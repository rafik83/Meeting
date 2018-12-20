var $ = require('jquery'),
    axios = require('axios')
;

function AssignAccommodationStay(element)
{
    this.element = element;
    this.url = element.getAttribute('data-availability-url');
    this.accommodationInput = element.querySelector('[id="admin_assign_accommodation_type_accommodation"]');
    this.roommateInput = element.querySelector('[id="admin_assign_accommodation_type_roommate"]');
    this.roommateBlock = element.querySelector('[id="admin_assign_accommodation_type_roommate-group"]');

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
    const accommodationInput = $(this.accommodationInput);
    const roommateInput = $(this.roommateInput);

    accommodationInput.empty();
    roommateInput.empty();

    axios.get(this.url, {
        params: {
            arrivalDate: this.arrivalDateInput.value,
            departureDate: this.departureDateInput.value,
        }
    }).then(function (response) {
        Object.entries(response.data.accommodation).forEach(
            ([id, label]) => accommodationInput.append(`<option value="${id}">${label}</option>`)
        );

        Object.entries(response.data.roommate).forEach(
            ([id, object]) => roommateInput.append(`<option value="${id}" ${object.disabled ? 'disabled="disabled"': ''}">${object.label}</option>`)
        );
    }).catch(alert);
};

module.exports = AssignAccommodationStay;
