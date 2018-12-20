function AssignAccommodationStay(element)
{
    this.element = element;
    this.roommateBlock = element.querySelector('[id="admin_assign_accommodation_type_roommate-group"]');

    [].forEach.call(element.querySelectorAll('input[name="admin_assign_accommodation_type[roomType]"]'), function (roomType) {
        roomType.addEventListener('change', this.onSelectRoomType.bind(this));
    }.bind(this));

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

module.exports = AssignAccommodationStay;
