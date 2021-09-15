function MeetingRequestMobileAvailableSlotFilterElement(element, filterId, content) {
    this.element = element;
    this.filterId = filterId;
    this.content = content;
}

MeetingRequestMobileAvailableSlotFilterElement.prototype.inactive = function() {
    this.element.getElementsByTagName('a')[0].classList.add('btn-inactive');
};

MeetingRequestMobileAvailableSlotFilterElement.prototype.active = function() {
    this.element.getElementsByTagName('a')[0].classList.remove('btn-inactive');
};

export default MeetingRequestMobileAvailableSlotFilterElement;
