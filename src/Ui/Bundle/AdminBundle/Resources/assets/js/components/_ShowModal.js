import $ from 'jquery';

function ShowModal(element) {
    this.element = element;

    $(this.element).modal('show');
}

export default ShowModal;
