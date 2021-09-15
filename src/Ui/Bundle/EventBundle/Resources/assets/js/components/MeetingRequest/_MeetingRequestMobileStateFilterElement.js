function MeetingRequestMobileStateTypeFilterElement(element, filterId, content, count) {
    this.element = element;
    this.filterId = filterId;
    this.content = content;
    this.count = parseInt(count);
}

MeetingRequestMobileStateTypeFilterElement.prototype.inactive = function() {
    this.element.getElementsByTagName('a')[0].classList.add('btn-inactive');
};

MeetingRequestMobileStateTypeFilterElement.prototype.active = function() {
    this.element.getElementsByTagName('a')[0].classList.remove('btn-inactive');
};

export default MeetingRequestMobileStateTypeFilterElement;
